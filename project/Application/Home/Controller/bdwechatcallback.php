<?php
namespace Home\Controller;

define ( "TOKEN", "bdweixin" );

require_once 'Weixin.php';
require_once 'UserConst.php';
require_once 'BDConst.php';
class bdwechatcallback {
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
					$result = $this->receiveText ( $postObj );
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
				$content = "欢迎关注树窝BD ";
				break;
			case "unsubscribe" :
				$content = "取消关注";
				break;
				// case "SCAN":
				// $content = "扫描场景 ".$object->EventKey;
				// break;
			case "CLICK" :
				switch ($object->EventKey) {
					case "income" :
						$openid = $object->FromUserName;
						if ($openid) {
							$user = M ( 'user' );
							$data = $user->where ( "openid='" . $openid . "' AND roles = 1" )->find ();
							if (count ( $data )) {
								$orders = M ( 'orders' );
								$shopid = $data ["shopid"];

								// // 当日收益
								// $today = date ( "Y-m-d" );
								// $sql = "select SUM(totalprice) as cincome from orders where DATE_FORMAT(createdtime,'%Y-%m-%d')='" . $today . "' AND shopid = {$shopid} AND orderstatus != 2";
								// $item = $orders->query ( $sql );
								// $todayincome = doubleval ( $item [0] ['cincome'] );

								// // 当月收益
								// $Month = date ( "Y-m" );
								// $sql = "select SUM(totalprice) as mincome from orders where DATE_FORMAT(createdtime,'%Y-%m')='" . $Month . "' AND shopid = {$shopid} AND orderstatus != 2";
								// $item = $orders->query ( $sql );
								// $monthincome = doubleval ( $item [0] ['mincome'] );

								// // 当前总收益
								// $sql = "select SUM(totalprice) as tincome from orders where shopid = {$shopid} AND orderstatus != 2";
								// $item = $orders->query ( $sql );
								// $totalincome = doubleval ( $item [0] ['tincome'] );
								// $content = "当日收益: " . $todayincome . "元 \n\n" . "当月收益: " . $monthincome . "元 \n\n" . "目前总收益: " . $totalincome . "元 \n\n";
								if ($shopid) {
									$shopmsg = '';
									$current = date ( 'H:i' );
									$curdate = date ( 'Y-m-d' );
									$yesterday = date ( "Y-m-d", strtotime ( "-1 day" ) );
									$msg = "截至" . $current . "订单";
									$msg_yest = "昨日订单";
									$totalorders = $orders->query ( "SELECT * FROM orders WHERE date(createdtime) = '" . $curdate . "' AND shopid=" . $shopid );
									$totalorders_yest = $orders->query ( "SELECT * FROM orders WHERE date(createdtime) = '" . $yesterday . "' AND shopid=" . $shopid );
									$unorders = $orders->query ( "SELECT * FROM orders WHERE date(createdtime) = '" . $curdate . "' AND orderstatus = 0 AND shopid=" . $shopid );
									$unorders_yest = $orders->query ( "SELECT * FROM orders WHERE date(createdtime) = '" . $yesterday . "' AND orderstatus = 0 AND shopid=" . $shopid );
									$corders = $orders->query ( "SELECT * FROM orders WHERE date(createdtime) = '" . $curdate . "' AND orderstatus = 2 AND shopid=" . $shopid );
									$corders_yest = $orders->query ( "SELECT * FROM orders WHERE date(createdtime) = '" . $yesterday . "' AND orderstatus = 2 AND shopid=" . $shopid );
									$checkorders = $orders->query ( "SELECT * FROM orders WHERE date(createdtime) = '" . $curdate . "' AND orderstatus = 1 AND shopid=" . $shopid );
									$checkorders_yest = $orders->query ( "SELECT * FROM orders WHERE date(createdtime) = '" . $yesterday . "' AND orderstatus = 1 AND shopid=" . $shopid );
									$usercheckorders = $orders->query ( "SELECT * FROM orders WHERE date(createdtime) = '" . $curdate . "' AND orderstatus = 3 AND shopid=" . $shopid );
									$usercheckorders_yest = $orders->query ( "SELECT * FROM orders WHERE date(createdtime) = '" . $yesterday . "' AND orderstatus = 3 AND shopid=" . $shopid );
										
									$first = $orders->query ( "SELECT * FROM orders WHERE date(createdtime) = '" . $curdate . "' AND isfirst = 1 AND shopid=" . $shopid." AND orderstatus=3" );
									$first_discuont = count ( $first ) * C ( 'FIRST_DISCOUNT' );
									$first_yest = $orders->query ( "SELECT * FROM orders WHERE date(createdtime) = '" . $yesterday . "' AND isfirst = 1 AND shopid=" . $shopid." AND orderstatus=3" );
									$first_yest_discount = count ( $first_yest ) * C ( 'FIRST_DISCOUNT' );
									$discount = $orders->query ( "SELECT * FROM orders WHERE date(createdtime) = '" . $curdate . "' AND isfirst = 0 AND discount > 0 AND shopid=" . $shopid." AND orderstatus=3" );
									$dis_discount = 0;
									$dis_yest_discount = 0;
									foreach ( $discount as $dis ) {
										$dis_discount += $dis ['discount'];
									}
									$discount_yest = $orders->query ( "SELECT * FROM orders WHERE date(createdtime) = '" . $yesterday . "' AND isfirst = 0 AND discount > 0 AND shopid=" . $shopid." AND orderstatus=3" );
									foreach ( $discount_yest as $dis ) {
										$dis_yest_discount += $dis ['discount'];
									}
									$shopmsg = $shopmsg . $msg . "\n";
									$msg_yest = $msg_yest . "\n";
									$shopmsg = $shopmsg . "收到" . count ( $totalorders ) . "单\n";
									$msg_yest = $msg_yest . "收到" . count ( $totalorders_yest ) . "单\n";
										
									$shopmsg = $shopmsg . "店家已确认" . count ( $checkorders ) . "单)\n";
									$shopmsg = $shopmsg."用户已确认".count($usercheckorders)."单(首购" . count ( $first ) . "单|优惠" . count ($discount ) . "单)\n";
									$msg_yest = $msg_yest . "店家已确认" . count ( $checkorders_yest )."单\n";
									$msg_yest = $msg_yest."用户已确认".count($usercheckorders_yest)."单(首购" . count ( $first_yest ) . "单|优惠" . count ($discount_yest ) . "单)\n";;
										
									$shopmsg = $shopmsg . "未确认" . count ( $unorders ) . "单\n";
									$msg_yest = $msg_yest . "未确认" . count ( $unorders_yest ) . "单\n";
										
									$mesg = '';
									$mesg_yest = '';
									$shopmsg = $shopmsg . $mesg;
									$msg_yest = $msg_yest . $mesg_yest;
									$shopmsg = $shopmsg . "已取消" . count ( $corders ) . "单\n";
									$shopmsg = $shopmsg . "实际补贴" . ($first_discuont + $dis_discount) . "元\n";
									$mesg_yest = $mesg_yest . "已取消" . count ( $corders_yest ) . "单\n";
									$msg_yest = $msg_yest . "实际补贴" . ($first_yest_discount + $dis_yest_discount) . "元\n";
										
									$content = $shopmsg . "\n" . $msg_yest;
								} else {
									$content = "请确定该账号是否授权。\n店铺授权码格式 \n(add+shop+授权码)";
								}
							} else {
								$content = "请确定该账号是否授权。\n店铺授权码格式 \n(add+shop+授权码)";
							}
						}
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
        $strarray = explode ( "+", $keyword );
        $openid = $object->FromUserName;
		if (count ( $strarray ) == 3 && $strarray [0] == 'add' && $strarray [1] == 'bd') {
			if(intval($strarray[2]))
			{
				$data['bdopenid'] = strval($openid);
				$bdid = intval($strarray[2]);
			    $bds = M('bd');
			    if($bds->where("bdid=".$bdid)->save($data))
			    {
			    	$content = "BD加入成功";
			    }
			    else 
			    {
			    	$content = "BD加入失败";
			    }
			}
		} 
		else
		{
			$content = "输入格式错误";
		}
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