<?php

namespace Home\Controller;

define ( "TOKEN", "shuwoweixin" );

require_once 'Weixin.php';
require_once 'UserConst.php';
require_once 'BDConst.php';
require_once 'BagConst.php';
class shuwowechatcallback {
	public function valid() {
		$echoStr = $_GET ["echostr"];
		$signature = $_GET ["signature"];
		$timestamp = $_GET ["timestamp"];
		$nonce = $_GET ["nonce"];
		$token = TOKEN;
		$tmpArr = array (
				$token,
				$timestamp,
				$nonce 
		);
		sort ( $tmpArr );
		$tmpStr = implode ( $tmpArr );
		$tmpStr = sha1 ( $tmpStr );
		if ($tmpStr == $signature) {
			echo $echoStr;
			exit ();
		}
	}
	
	// 响应消息
	public function responseMsg() {
		$postStr = $GLOBALS ["HTTP_RAW_POST_DATA"];
		if (! empty ( $postStr )) {
			$postObj = simplexml_load_string ( $postStr, 'SimpleXMLElement', LIBXML_NOCDATA );
			$RX_TYPE = trim ( $postObj->MsgType );
			
			// 消息类型分离
			switch ($RX_TYPE) {
				case "event" :
					$result = $this->receiveEvent ( $postObj );
					break;
				case "text" :
					// $result = $this->receiveText ( $postObj );
					break;
				default :
					$result = "unknown msg type: " . $RX_TYPE;
					break;
			}
			echo $result;
		} else {
			echo "";
			exit ();
		}
	}
	
	// 接收事件消息
	private function receiveEvent($object) {
		$content = "";
		switch ($object->Event) {
			case "subscribe" :
				$content = "您来了，真好:)\n\n树窝是一个联系您和您身边优质水果店的平台。水果店里的所有水果您都可以在家里买到。点击菜单“我要下单”就能开始挑水果啦，每一份都是树窝为您精挑细选的优质水果~\n\n现在开始红包送不停，在家买水果更便宜。快来点击下方链接看看您获得了多少红包：\n http://www.shuwow.com/#/bag";
				$openid = trim ( $object->FromUserName );
				if (! empty ( $openid )) {
					$user = M ( "user" );
					$userinfo = $user->where ( "openid='" . $openid . "'" )->select ();
					if (! count ( $userinfo )) {
						$weixin = new Weixin ();
						$token = $weixin->getusersGlobalAccessToken ();
						$userdata = $weixin->getinfobyglobaltoken ( $openid, $token );
						if (count ( $userdata )) {
							$data [UserConst::OPENID] = $userdata [UserConst::OPENID];
							$data [UserConst::UNIOID] = $userdata [UserConst::UNIOID] ? $userinfo [UserConst::UNIOID] : "";
							$data [UserConst::NICKNAME] = $userdata [UserConst::NICKNAME];
							$data [UserConst::SEX] = $userdata [UserConst::SEX];
							$data [UserConst::PROVINCE] = $userdata [UserConst::PROVINCE];
							$data [UserConst::CITY] = $userdata [UserConst::CITY];
							$data [UserConst::COUNTRY] = $userdata [UserConst::COUNTRY];
							$data [UserConst::HEADIMGURL] = $userdata [UserConst::HEADIMGURL];
							$data [UserConst::MOBILE] = '';
							$data [UserConst::PASSWORD] = '';
							$data [UserConst::ROLES] = 0;
							$userid = $user->add ( $data );
							if ($userid) {
								$eventkey = trim ( $object->EventKey );
								$keys = explode ( "_", $eventkey );
								if ($keys [1] == 123) {
									$bags = M ( "bag" );
									$current = date ( 'Y-m-d' );
									$expirdate = date ( 'Y-m-d', strtotime ( '+6 days' ) );
									$expirdate1 = $expirdate . " 23:59:59";
									$bagitem [BagConst::START] = $current;
									$bagitem [BagConst::SHOP_ID] = 0;
									$bagitem [BagConst::TYPE] = 1;
									$bagitem [BagConst::EXPIRES] = $expirdate1;
									$bagitem [BagConst::USED] = 0;
									$bagitem [BagConst::AMOUNT] = 2;
									$bagitem [BagConst::USER_ID] = $userid;
									$bagitem [BagConst::ISEVER] = 1;
									$bagitem [BagConst::ISAUTO] = 1;
									
									$bagid = $bags->add ( $bagitem );
									$user->where("userid=".$userid)->setField("usertype",1);
									if ($bagid) {
										$content = "您来了，真好:) \n\n树窝是一个改变您生活方式的平台。您需要买什么，不用出门，树窝都能帮您买。\n\n无需运费，首次关注还有1毛钱就能买到的超值商品和树窝为你准备的优惠券。\n\n尽情享受宅在家的乐趣吧!\n\n快来点击下方链接看看您获得了多少红包：\n http://www.shuwow.com/#/bag";
									}
								} else {
									$bags = M ( "bag" );
									$current = date ( 'Y-m-d' );
									$expirdate = date ( 'Y-m-d', strtotime ( '+6 days' ) );
									$expirdate1 = $expirdate . " 23:59:59";
									$bagitem [BagConst::START] = $current;
									$bagitem [BagConst::SHOP_ID] = 0;
									$bagitem [BagConst::TYPE] = 1;
									$bagitem [BagConst::EXPIRES] = $expirdate1;
									$bagitem [BagConst::USED] = 0;
									$bagitem [BagConst::AMOUNT] = 10;
									$bagitem [BagConst::USER_ID] = $userid;
									$bagitem [BagConst::ISEVER] = 1;
									$bagitem [BagConst::ISAUTO] = 1;
									
									$bagitem2 [BagConst::START] = $current;
									$bagitem2 [BagConst::SHOP_ID] = 0;
									$bagitem2 [BagConst::TYPE] = 2;
									$bagitem2 [BagConst::EXPIRES] = $expirdate1;
									$bagitem2 [BagConst::USED] = 0;
									$bagitem2 [BagConst::AMOUNT] = 5;
									$bagitem2 [BagConst::USER_ID] = $userid;
									$bagitem2 [BagConst::ISEVER] = 1;
									$bagitem2 [BagConst::ISAUTO] = 1;
									
									$bagid = $bags->add ( $bagitem );
									$bagid2 = $bags->add ( $bagitem2 );
									if ($bagid && $bagid2) {
										$content = "您来了，真好:)\n\n树窝是一个联系您和您身边优质水果店的平台。水果店里的所有水果您都可以在家里买到。点击菜单“我要下单”就能开始挑水果啦，每一份都是树窝为您精挑细选的优质水果~\n\n现在开始红包送不停，在家买水果更便宜。快来点击下方链接看看您获得了多少红包：\n http://www.shuwow.com/#/bag";
									}
								}
							}
						}
					}
				}
				
				break;
			case "unsubscribe" :
				$content = "取消关注";
				break;
			case "SCAN" :
				$eventkey = trim ( $object->EventKey );
				if ($eventkey == 123)
				{
					$content = "您来了，真好:) \n\n树窝是一个改变您生活方式的平台。您需要买什么，不用出门，树窝都能帮您买。\n\n无需运费，首次关注还有1毛钱就能买到的超值商品和树窝为你准备的优惠券。\n\n尽情享受宅在家的乐趣吧!";
				}
				else 
				{
                    $content = "您来了，真好:)\n\n树窝是一个联系您和您身边优质水果店的平台。水果店里的所有水果您都可以在家里买到。点击菜单“我要下单”就能开始挑水果啦，每一份都是树窝为您精挑细选的优质水果~\n\n现在开始红包送不停，在家买水果更便宜。快来点击下方链接看看您获得了多少红包：\n http://www.shuwow.com/#/bag";
				}
				break;
			case "CLICK" :
				switch ($object->EventKey) {
					case "introduction" :
						$content = "1.树窝水果合作的店铺，是基于您所在位置，挑选您身边优质的店铺，以和店内相同的价格免费配送 \n2.您在下单后，水果店会根据您的订单迅速称重，并将详细的重量及价格反馈给您 \n3.水果送达后，如果有品质较差的水果，您可以挑出后再称重付款\n4.请务必填写正确的电话号码和详细的送货地址（例如：肇嘉浜路1029弄永新花苑601室），方便送水果人员及时送达 \n 5.优惠券送不停，购买时可直接使用优惠券抵现金，比进店买更便宜！后续的优惠方式以树窝公告为准";
						break;
					case "delivery" :
						$content = "配送条件 \n1.每单起送价以每个水果店内公告为准 \n2.订单达到起送价后，树窝水果平台上所有商品都免费配送 \n配送时间\n 1.水果店营业时间为8:00-22:00，具体时间以水果店内公告为准 \n2.下单成功后，送水果人员会在您选择的送货时间内快速送达";
						break;
					case "pay" :
						$content = "1.货到付款，当面验水果，若有品质较差的水果可以拒收 \n2.网上支付方式会在不久后开放使用";
						break;
					case "service" :
						$content = "1.水果当面检查验收，不满意的水果可以让送果园退回，并减去相应价格\n2.若送水果人员不予配合，可致电树窝水果客服进行投诉，我们会认真对待您的投诉与建议";
						break;
					case "advice" :
						$content = "若在下单后\n 1.水果店未能及时确认订单或长时间未送达 \n2.水果有任何品质问题 （任何造成您不满意的问题，您都可以通过以下方式投诉，我们会及时处理您的问题） \n投诉方式：\n1.可直接在本公众账号下留言，客服收到消息后会答复您\n2.您还可以直接拨打树窝客服热线：4009609670（希望您给树窝提出您宝贵的建议，帮助树窝更好得服务于您，采用的意见我们会送出代金券做奖励";
						break;
					default :
						$content = "点击菜单：" . $object->EventKey;
						break;
				}
				break;
			case "VIEW" :
				$content = "跳转链接 " . $object->EventKey;
				break;
			default :
				$content = "receive a new event: " . $object->Event;
				break;
		}
		$result = $this->transmitText ( $object, $content );
		return $result;
	}
	
	// 接收文本消息
	private function receiveText($object) {
		$keyword = trim ( $object->Content );
		$content = $keyword;
		$result = $this->transmitText ( $object, $content );
		
		return $result;
	}
	// 回复文本消息
	private function transmitText($object, $content) {
		$xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[%s]]></Content>
</xml>";
		$result = sprintf ( $xmlTpl, $object->FromUserName, $object->ToUserName, time (), $content );
		return $result;
	}
	
	// 回复图片消息
	private function transmitImage($object, $imageArray) {
		$itemTpl = "<Image>
    <MediaId><![CDATA[%s]]></MediaId>
</Image>";
		
		$item_str = sprintf ( $itemTpl, $imageArray ['MediaId'] );
		
		$xmlTpl = "<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%s</CreateTime>
		<MsgType><![CDATA[image]]></MsgType>
		$item_str
		</xml>";
		
		$result = sprintf ( $xmlTpl, $object->FromUserName, $object->ToUserName, time () );
		return $result;
	}
	
	// 回复语音消息
	private function transmitVoice($object, $voiceArray) {
		$itemTpl = "<Voice>
		<MediaId><![CDATA[%s]]></MediaId>
</Voice>";
		
		$item_str = sprintf ( $itemTpl, $voiceArray ['MediaId'] );
		
		$xmlTpl = "<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%s</CreateTime>
		<MsgType><![CDATA[voice]]></MsgType>
		$item_str
		</xml>";
		
		$result = sprintf ( $xmlTpl, $object->FromUserName, $object->ToUserName, time () );
		return $result;
	}
	
	// 回复视频消息
	private function transmitVideo($object, $videoArray) {
		$itemTpl = "<Video>
	<MediaId><![CDATA[%s]]></MediaId>
    <ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
    <Title><![CDATA[%s]]></Title>
    <Description><![CDATA[%s]]></Description>
</Video>";
		
		$item_str = sprintf ( $itemTpl, $videoArray ['MediaId'], $videoArray ['ThumbMediaId'], $videoArray ['Title'], $videoArray ['Description'] );
		
		$xmlTpl = "<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%s</CreateTime>
		<MsgType><![CDATA[video]]></MsgType>
		$item_str
		</xml>";
		
		$result = sprintf ( $xmlTpl, $object->FromUserName, $object->ToUserName, time () );
		return $result;
	}
	
	// 回复图文消息
	private function transmitNews($object, $newsArray) {
		if (! is_array ( $newsArray )) {
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
		foreach ( $newsArray as $item ) {
			$item_str .= sprintf ( $itemTpl, $item ['Title'], $item ['Description'], $item ['PicUrl'], $item ['Url'] );
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
		
		$result = sprintf ( $xmlTpl, $object->FromUserName, $object->ToUserName, time (), count ( $newsArray ) );
		return $result;
	}
	
	// 回复音乐消息
	private function transmitMusic($object, $musicArray) {
		$itemTpl = "<Music>
	<Title><![CDATA[%s]]></Title>
	<Description><![CDATA[%s]]></Description>
	<MusicUrl><![CDATA[%s]]></MusicUrl>
	<HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
</Music>";
		
		$item_str = sprintf ( $itemTpl, $musicArray ['Title'], $musicArray ['Description'], $musicArray ['MusicUrl'], $musicArray ['HQMusicUrl'] );
		
		$xmlTpl = "<xml>
	<ToUserName><![CDATA[%s]]></ToUserName>
	<FromUserName><![CDATA[%s]]></FromUserName>
	<CreateTime>%s</CreateTime>
	<MsgType><![CDATA[music]]></MsgType>
	$item_str
	</xml>";
		
		$result = sprintf ( $xmlTpl, $object->FromUserName, $object->ToUserName, time () );
		return $result;
	}
	
	// 回复多客服消息
	private function transmitService($object) {
		$xmlTpl = "<xml>
	<ToUserName><![CDATA[%s]]></ToUserName>
			<FromUserName><![CDATA[%s]]></FromUserName>
			<CreateTime>%s</CreateTime>
			<MsgType><![CDATA[transfer_customer_service]]></MsgType>
</xml>";
		$result = sprintf ( $xmlTpl, $object->FromUserName, $object->ToUserName, time () );
		return $result;
	}
}