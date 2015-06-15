<?php

namespace Home\Controller;

use Models\PromoterConst;
use Utils\StringUtil;

define ("TOKEN", "weixin");

require_once "Weixin.php";

class PromoterWechatCallback
{

    public function valid()
    {
        $echoStr = $_GET ["echostr"];
        $signature = $_GET ["signature"];
        $timestamp = $_GET ["timestamp"];
        $nonce = $_GET ["nonce"];
        $token = TOKEN;
        $tmpArr = array(
            $token,
            $timestamp,
            $nonce
        );
        sort($tmpArr);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if ($tmpStr == $signature) {
            echo $echoStr;
            exit ();
        }
    }

    // 响应消息
    public function responseMsg()
    {
        $postStr = $GLOBALS ["HTTP_RAW_POST_DATA"];
        if (!empty ($postStr)) {
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $RX_TYPE = trim($postObj->MsgType);

            // 消息类型分离
            switch ($RX_TYPE) {
                case "event" :
                    $result = $this->receiveEvent($postObj);
                    break;
                case "text" :
                    $result = $this->receiveText($postObj);
                    break;
                default :
                    $result = "unknown msg type: " . $RX_TYPE;
                    break;
            }
            echo $result;
        } else {
            echo "None string";
            exit ();
        }
    }

    // 接收事件消息
    private function receiveEvent($object)
    {
        $content = "";
        switch ($object->Event) {
            case "subscribe" :
                // 关注公众号
                $openid = $object->FromUserName;
                $dao = M('promoter');
                $promoter = $dao->where(PromoterConst::OPENID . "=" . $openid)->find();
                if ($promoter) {
                    // 如果已经关注过
                    $code = $promoter[PromoterConst::CODE];
                } else {
                    // 新关注的
                    $code = StringUtil::randStr(6, "NUMBER");;
                    $promoter[PromoterConst::OPENID] = $openid;
                    $promoter[PromoterConst::CODE] = $code;
                    $dao->save($promoter);
                }
                $content = "您的推荐码为：" . $code . "，推荐用户使用可以获取现金返现";
                break;
        }
        $result = $this->transmitText($object, $content);
        return $result;
    }

    // 接收文本消息
    private function receiveText($object)
    {
        $keyword = trim($object->Content);

        return null;
    }

    // 回复文本消息
    private function transmitText($object, $content)
    {
        $xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[%s]]></Content>
</xml>";
        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), $content);
        return $result;
    }

    // 回复图片消息
    private function transmitImage($object, $imageArray)
    {
        $itemTpl = "<Image>
    <MediaId><![CDATA[%s]]></MediaId>
</Image>";

        $item_str = sprintf($itemTpl, $imageArray ['MediaId']);

        $xmlTpl = "<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%s</CreateTime>
		<MsgType><![CDATA[image]]></MsgType>
		$item_str
		</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    // 回复语音消息
    private function transmitVoice($object, $voiceArray)
    {
        $itemTpl = "<Voice>
		<MediaId><![CDATA[%s]]></MediaId>
</Voice>";

        $item_str = sprintf($itemTpl, $voiceArray ['MediaId']);

        $xmlTpl = "<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%s</CreateTime>
		<MsgType><![CDATA[voice]]></MsgType>
		$item_str
		</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    // 回复视频消息
    private function transmitVideo($object, $videoArray)
    {
        $itemTpl = "<Video>
	<MediaId><![CDATA[%s]]></MediaId>
    <ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
    <Title><![CDATA[%s]]></Title>
    <Description><![CDATA[%s]]></Description>
</Video>";

        $item_str = sprintf($itemTpl, $videoArray ['MediaId'], $videoArray ['ThumbMediaId'], $videoArray ['Title'], $videoArray ['Description']);

        $xmlTpl = "<xml>
	<ToUserName><![CDATA[%s]]></ToUserName>
	<FromUserName><![CDATA[%s]]></FromUserName>
	<CreateTime>%s</CreateTime>
	<MsgType><![CDATA[video]]></MsgType>
	$item_str
	</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    // 回复图文消息
    private function transmitNews($object, $newsArray)
    {
        if (!is_array($newsArray)) {
            return;
        }
        $itemTpl = "    <item>
	<Title><![CDATA[%s]]></Title>
	<Description><![CDATA[%s]]></Description>
        <PicUrl><![CDATA[%s]]></PicUrl>
        <Url><![CDATA[%s]]></Url>
    </item>
";
        $item_str = "";
        foreach ($newsArray as $item) {
            $item_str .= sprintf($itemTpl, $item ['Title'], $item ['Description'], $item ['PicUrl'], $item ['Url']);
        }
        $xmlTpl = "<xml>
	<ToUserName><![CDATA[%s]]></ToUserName>
	<FromUserName><![CDATA[%s]]></FromUserName>
	<CreateTime>%s</CreateTime>
	<MsgType><![CDATA[news]]></MsgType>
	<ArticleCount>%s</ArticleCount>
	<Articles>
	$item_str</Articles>
	</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), count($newsArray));
        return $result;
    }

    // 回复音乐消息
    private function transmitMusic($object, $musicArray)
    {
        $itemTpl = "<Music>
		<Title><![CDATA[%s]]></Title>
	<Description><![CDATA[%s]]></Description>
	<MusicUrl><![CDATA[%s]]></MusicUrl>
	<HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
</Music>";

        $item_str = sprintf($itemTpl, $musicArray ['Title'], $musicArray ['Description'], $musicArray ['MusicUrl'], $musicArray ['HQMusicUrl']);

        $xmlTpl = "<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%s</CreateTime>
		<MsgType><![CDATA[music]]></MsgType>
		$item_str
		</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    // 回复多客服消息
    private function transmitService($object)
    {
        $xmlTpl = "<xml>
	<ToUserName><![CDATA[%s]]></ToUserName>
			<FromUserName><![CDATA[%s]]></FromUserName>
			<CreateTime>%s</CreateTime>
			<MsgType><![CDATA[transfer_customer_service]]></MsgType>
</xml>";
        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }
}