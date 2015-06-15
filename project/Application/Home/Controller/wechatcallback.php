<?php

namespace Home\Controller;

define ( "TOKEN", "weixin" );

require_once 'Weixin.php';
require_once 'UserConst.php';
require_once 'BDConst.php';
class wechatcallback {
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
				// case "image":
				// $result = $this->receiveImage($postObj);
				// break;
				// case "location":
				// $result = $this->receiveLocation($postObj);
				// break;
				// case "voice":
				// $result = $this->receiveVoice($postObj);
				// break;
				// case "video":
				// $result = $this->receiveVideo($postObj);
				// break;
				// case "link":
				// $result = $this->receiveLink($postObj);
				// break;
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
				$content = "欢迎关注树窝小店 ";
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
									$deliveryoders= $orders->query ( "SELECT * FROM orders WHERE date(createdtime) = '" . $curdate . "' AND orderstatus = 3 AND ispickup =0 AND shopid=" . $shopid );
									$deliveryoders_yest= $orders->query ( "SELECT * FROM orders WHERE date(createdtime) = '" . $yesterday . "' AND orderstatus = 3 AND ispickup =0 AND shopid=" . $shopid );
									$discount = $orders->query ( "SELECT * FROM orders WHERE date(createdtime) = '" . $curdate . "' AND bag_id > 0 AND discount > 0 AND shopid=" . $shopid." AND orderstatus=3" );
									$dis_discount = 0;	
									$dis_yest_discount = 0;
									$dis_delivery_discount = count($deliveryoders)*5;
									$dis_yest_delivery_discount = count($deliveryoders_yest)*5;
									foreach ( $discount as $dis ) {
										$dis_discount += $dis ['discount'];
									}
									$dis_discount +=$dis_delivery_discount;
									$discount_yest = $orders->query ( "SELECT * FROM orders WHERE date(createdtime) = '" . $yesterday . "' AND bag_id > 0 AND discount > 0 AND shopid=" . $shopid." AND orderstatus=3" );
									foreach ( $discount_yest as $dis ) {
										$dis_yest_discount += $dis ['discount'];
									} 
									$dis_yest_discount += $dis_yest_delivery_discount;
									$shopmsg = $shopmsg . $msg . "\n";
									$msg_yest = $msg_yest . "\n";
									$shopmsg = $shopmsg . "收到" . count ( $totalorders ) . "单\n";
									$msg_yest = $msg_yest . "收到" . count ( $totalorders_yest ) . "单\n";
									
									$shopmsg = $shopmsg . "店家已确认" . count ( $checkorders ) . "单\n";
									$shopmsg = $shopmsg."用户已确认".count($usercheckorders)."单(红包优惠" . count ($discount ) . "单)\n";
									$shopmsg = $shopmsg."用户已确认外送单".count($deliveryoders)."单\n";
									$msg_yest = $msg_yest . "店家已确认" . count ( $checkorders_yest )."单\n";
									$msg_yest = $msg_yest."用户已确认".count($usercheckorders_yest)."单(红包优惠".count ($discount_yest ) . "单)\n";
									$msg_yest = $msg_yest."用户已确认外送单".count($deliveryoders_yest)."单\n";
									
									$shopmsg = $shopmsg . "未确认" . count ( $unorders ) . "单\n";
									$msg_yest = $msg_yest . "未确认" . count ( $unorders_yest ) . "单\n";
									
									$mesg = '';
									$mesg_yest = '';
									$shopmsg = $shopmsg . $mesg;
									$msg_yest = $msg_yest . $mesg_yest;
									$shopmsg = $shopmsg . "已取消" . count ( $corders ) . "单\n";
									$shopmsg = $shopmsg . "实际补贴" . ($dis_discount) . "元\n";
									$mesg_yest = $mesg_yest . "已取消" . count ( $corders_yest ) . "单\n";
									$msg_yest = $msg_yest . "实际补贴" . $dis_yest_discount . "元\n";
									
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
			// }
			
			// case "LOCATION":
			// $content = "上传位置：纬度 ".$object->Latitude.";经度 ".$object->Longitude;
			// break;
			case "VIEW" :
				$content = "跳转链接 " . $object->EventKey;
				break;
			// case "MASSSENDJOBFINISH":
			// $content = "消息ID：".$object->MsgID."，结果：".$object->Status."，粉丝数：".$object->TotalCount."，过滤：".$object->FilterCount."，发送成功：".$object->SentCount."，发送失败：".$object->ErrorCount;
			// break;
			default :
				$content = "receive a new event: " . $object->Event;
				break;
		}
		// if(is_array($content)){
		// if (isset($content[0])){
		// $result = $this->transmitNews($object, $content);
		// }
		// }else{
		// $result = $this->transmitText($object, $content);
		// }
		
		$result = $this->transmitText ( $object, $content );
		return $result;
	}
	
	// 接收文本消息
	private function receiveText($object) {
		$keyword = trim ( $object->Content );
		// //多客服人工回复模式
		// if (strstr($keyword, "您好") || strstr($keyword, "你好") || strstr($keyword, "在吗")){
		// $result = $this->transmitService($object);
		// }
		// //自动回复模式
		// else{
		// if (strstr($keyword, "文本")){
		// $content = "这是个文本消息";
		// }else if (strstr($keyword, "单图文")){
		// $content = array();
		// $content[] = array("Title"=>"单图文标题", "Description"=>"单图文内容", "PicUrl"=>"http://discuz.comli.com/weixin/weather/icon/cartoon.jpg", "Url" =>"http://m.cnblogs.com/?u=txw1958");
		// }else if (strstr($keyword, "图文") || strstr($keyword, "多图文")){
		// $content = array();
		// $content[] = array("Title"=>"多图文1标题", "Description"=>"", "PicUrl"=>"http://discuz.comli.com/weixin/weather/icon/cartoon.jpg", "Url" =>"http://m.cnblogs.com/?u=txw1958");
		// $content[] = array("Title"=>"多图文2标题", "Description"=>"", "PicUrl"=>"http://d.hiphotos.bdimg.com/wisegame/pic/item/f3529822720e0cf3ac9f1ada0846f21fbe09aaa3.jpg", "Url" =>"http://m.cnblogs.com/?u=txw1958");
		// $content[] = array("Title"=>"多图文3标题", "Description"=>"", "PicUrl"=>"http://g.hiphotos.bdimg.com/wisegame/pic/item/18cb0a46f21fbe090d338acc6a600c338644adfd.jpg", "Url" =>"http://m.cnblogs.com/?u=txw1958");
		// }else if (strstr($keyword, "音乐")){
		// $content = array();
		// $content = array("Title"=>"最炫民族风", "Description"=>"歌手：凤凰传奇", "MusicUrl"=>"http://121.199.4.61/music/zxmzf.mp3", "HQMusicUrl"=>"http://121.199.4.61/music/zxmzf.mp3");
		// }else{
		// $content = date("Y-m-d H:i:s",time())."\n".$object->FromUserName."\n技术支持 方倍工作室";
		// }
		
		// if(is_array($content)){
		// if (isset($content[0]['PicUrl'])){
		// $result = $this->transmitNews($object, $content);
		// }else if (isset($content['MusicUrl'])){
		// $result = $this->transmitMusic($object, $content);
		// }
		// }else{
		// $result = $this->transmitText($object, $content);
		// }
		// }
		
		// 写逻辑 当从小店树窝小店 add+shop+“授权码”；
		// $content = '';
		// $keyword = aad+shop+12323
		// 获取店铺的授权码
		$strarray = explode ( "+", $keyword );
		$shopsn = '';
		if (count ( $strarray ) == 3 && $strarray [0] == 'add' && $strarray [1] == 'shop') {
			$shop = M ( "shop" );
			$where ['shopsn'] = $strarray [2];
			$data = $shop->where ( $where )->getField ( "shopid" );
			if (intval ( $data )) {
				$weixin = new Weixin ();
				$token = $weixin->getshopGlobalAccessToken ();
				$userInfo = $weixin->getinfobyglobaltoken( $object->FromUserName, $token );
				if (count ( $userInfo )) {
					$user = M ( 'user' );
					// $userid = $user->where ( "shopid=" . $data )->getField ( "userid" );
					$data_user [UserConst::OPENID] = $userInfo [UserConst::OPENID];
					$data_user [UserConst::UNIOID] = $userInfo [UserConst::UNIOID] ? $userInfo [UserConst::UNIOID] : "";
					$data_user [UserConst::NICKNAME] = $userInfo [UserConst::NICKNAME];
					$data_user [UserConst::SEX] = $userInfo [UserConst::SEX];
					$data_user [UserConst::PROVINCE] = $userInfo [UserConst::PROVINCE];
					$data_user [UserConst::CITY] = $userInfo [UserConst::CITY];
					$data_user [UserConst::COUNTRY] = $userInfo [UserConst::COUNTRY];
					$data_user [UserConst::HEADIMGURL] = $userInfo [UserConst::HEADIMGURL];
					$data_user [UserConst::MOBILE] = '';
					$data_user [UserConst::PASSWORD] = '';
					$data_user [UserConst::ROLES] = 1;
					$data_user [UserConst::SHOPID] = $data;
					$userid = $user->where ( "openid='" . trim ( $data_user [UserConst::OPENID] ) . "'" )->getField ( "userid" );
					if ($userid) {
						$data_user [UserConst::USERID] = $userid;
						$shopid = $user->where ( "openid='" . trim ( $data_user [UserConst::OPENID] ) . "'" )->getField ( "shopid" );
						if (intval ( $shopid ) == intval ( $data )) {
							if ($user->save ( $data_user ) !== false) {
								$content = "授权成功";
							} else {
								$content = "授权未成功";
							}
						} else if (intval ( $shopid ) == 0) {
							if ($user->save ( $data_user ) !== false) {
								$content = "授权成功";
							} else {
								$content = "授权未成功";
							}
						} else {
							$content = "该账号已被授权。若要取消授权或获得新的授权，请联系商务经理。";
						}
					} else {
						// if (! $user->where ( "openid='" . trim ( $data_user [UserConst::OPENID] ) . "'" )->find ()) {
						if ($user->add ( $data_user )) {
							$content = "授权成功";
						} else {
							$content = "授权未成功";
						}
						// } else {
						// $content = "该账号已被授权。若要取消授权或获得新的授权，请联系商务经理。";
						// }
					}
				} else {
					$content = "授权未成功";
				}
			} else {
				$content = "授权未成功";
			}
		} else if (count ( $strarray ) == 3 && $strarray [0] == 'cancel' && $strarray [1] == 'shop') {
			$where ['shopsn'] = $strarray [2];
			$shop = M ( "shop" );
			$user = M ( 'user' );
			$data = $shop->where ( $where )->getField ( "shopid" );
			if (intval ( $data )) {
				$openid = trim ( $object->FromUserName );
				$userinfos = $user->where ( "openid = '" . $openid . "'" . " AND shopid =" . $data )->find ();
				if (count ( $userinfos )) {
					$updatedata = array (
							'roles' => 0,
							'shopid' => 0 
					);
					if ($user->where ( 'userid = ' . $userinfos [UserConst::USERID] )->setField ( $updatedata ) !== false) {
						$content = "取消授权成功";
					} else {
						$content = "取消授权失败";
					}
				}
			} else {
				$content = "取消授权失败";
			}
		} else if (count ( $strarray ) == 3 && $strarray [0] == 'add' && $strarray [1] == 'bd') {
			$weixin = new Weixin ();
			$token = $weixin->getshopGlobalAccessToken ();
			$userInfo = $weixin->getinfobyglobaltoken($object->FromUserName,  $token);
			if (count ( $userInfo )) {
				$openid = trim ( $userInfo [BDConst::OPENID] );
				$bd = M ( "bd" );
				$data_bd [BDConst::OPENID] = $openid;
				$data_bd [BDConst::UNIOID] = $userInfo [BDConst::UNIOID] ? $userInfo [BDConst::UNIOID] : "";
				$data_bd [BDConst::NICKNAME] = $userInfo [BDConst::NICKNAME];
				$data_bd [BDConst::SEX] = $userInfo [BDConst::SEX];
				$data_bd [BDConst::PROVINCE] = $userInfo [BDConst::PROVINCE];
				$data_bd [BDConst::CITY] = $userInfo [BDConst::CITY];
				$data_bd [BDConst::COUNTRY] = $userInfo [BDConst::COUNTRY];
				$data_bd [BDConst::HEADIMGURL] = $userInfo [BDConst::HEADIMGURL];
				$data_bd [BDConst::MOBILE] = '';
				$data_bd [BDConst::PASSWORD] = '';
				$data_bd [BDConst::BDNAME] = $strarray [2];
				$bdinfo = $bd->where ( "openid ='" . $openid . "'" )->find ();
				if (count ( $bdinfo )) {
					$data_bd [BDConst::BDID] = $bdinfo [BDConst::BDID];
					if ($bd->save ( $data_bd ) !== false) {
						$content = "BD授权成功";
					} else {
						$content = "BD授权未成功";
					}
				} else {
					if ($bd->add ( $data_bd )) {
						$content = "BD授权成功";
					} else {
						$content = "BD授权未成功";
					}
				}
			} else {
				$content = "BD授权未成功";
			}
		} else if (count ( $strarray ) <= 4 && $strarray [0] == 'check' && $strarray [1] == 'orders') {
			$bd = M ( "bd" );
			$openid = trim ( $object->FromUserName );
			$bdinfos = $bd->where ( "openid = '" . $openid . "'" )->find ();
			if (count ( $bdinfos )) {
				$bdid = $bdinfos [BDConst::BDID];
				$bdshop = M ( 'bdshop' );
				$shops = $bdshop->where ( 'bdid =' . $bdid )->select ();
				$num = count ( $shops );
				$shopmsg = '';
				if ($num && ! empty ( $bdinfos [BDConst::OPENID] )) {
					$current = date ( 'H:i' );
					$curdate = date ( 'Y-m-d' );
					$msg = "截至" . $current . "订单";
					$flag = true;
					if (! empty ( $strarray [2] )) {
						if (preg_match ( "/^\d{4}$/", $strarray [2] )) {
							$strdate = $strarray [2];
							$curM = $strdate [0] . $strdate [1];
							$curD = $strdate [2] . $strdate [3];
							$curyear = date ( 'Y' );
							$curdate = $curyear . '-' . $curM . '-' . $curD;
							$msg = $curM . '-' . $curD . '日订单';
							$flag = true;
						} else {
							$content = '输入的日期格式不正确！';
							$flag = false;
						}
						
						if (! empty ( $strarray [3] )) {
							$found = false;
							for($k = 0; $k < $num; $k ++) {
								if ($shops [$k] ['shopid'] == intval ( $strarray [3] )) {
									$found = true;
									$num = 1;
									$shops [0] = $shops [$k];
									break;
								}
							}
							if (! $found) {
								$content = '输入的店铺id不正确！';
								$flag = false;
							}
						}
					}
					
					if ($flag) {
						$shopinfo = M ( 'shop' );
						$order = M ( 'orders' );
						$totals = 0;
						$totalrefunds  = 0;
						for($i = 0; $i < $num; $i ++) {
							$shopdata = $shopinfo->where ( "shopid =" . $shops [$i] ['shopid'] )->find ();
							$totalorders = $order->query ( "SELECT * FROM orders WHERE date(createdtime) = '" . $curdate . "' AND shopid=" . $shops [$i] ['shopid'] );
							$unorders = $order->query ( "SELECT * FROM orders WHERE date(createdtime) = '" . $curdate . "' AND orderstatus = 0 AND shopid=" . $shops [$i] ['shopid'] );
							$corders = $order->query ( "SELECT * FROM orders WHERE date(createdtime) = '" . $curdate . "' AND orderstatus = 2 AND shopid=" . $shops [$i] ['shopid'] );
							$checkorders = $order->query ( "SELECT * FROM orders WHERE date(createdtime) = '" . $curdate . "' AND orderstatus = 1 AND shopid=" . $shops [$i] ['shopid'] );
							$usercheckorders = $order->query ( "SELECT * FROM orders WHERE date(createdtime) = '" . $curdate . "' AND orderstatus = 3 AND shopid=" . $shops [$i] ['shopid'] );
							$shopmsg = $shopmsg . $shopdata ['spn'] . $msg . "\n";
							$shopmsg = $shopmsg . "电话:" . $shopdata ['phone'] . "\n";
							$shopmsg = $shopmsg . count ( $totalorders ) . "-(" . count ( $checkorders )."/".count($usercheckorders).")-" . count ( $unorders ). "-" . count ( $corders ) . "\n";
							$totals += count ( $totalorders );
							$countfirst = 0;
							$countdiscount = 0;
							$totalfirst = 0;
							$totaldiscount = 0;
							if (count ( $usercheckorders )) {
									foreach ( $usercheckorders as $itemorder ) {

                                       if($itemorder ["discount"]>0 && $itemorder ["bag_id"]>0)
                                       {
                                       	    $countdiscount ++;
                                        	$totaldiscount = $totaldiscount + $itemorder ["discount"];
                                       }
									}
								}
								$refunds = $totaldiscount;
								$totalrefunds += $refunds;
								$shopmsg = $shopmsg.$countdiscount."-".$refunds."\n\n";
							
							
						}
					}
					if (! empty ( $shopmsg )) {
						$shopmsg = $shopmsg . "总数" . $totals."\n";
						$shopmsg = $shopmsg.'总补贴'.$totalrefunds;
						if (strlen ( $shopmsg ) < 2047) {
							$content = "总-已(店/用户)-未-取\n红包数-补贴数\n" . $shopmsg;
						} else {
						}
					} else {
						$content = "暂无消息";
					}
				} else {
					$content = "BD未授权";
				}
			} else {
				$content = "BD未授权";
			}
		} else {
			$content = "请输入正确的店铺授权码格式 (add+shop+授权码)";
		}
		
		// if(count($data))
		// {
		// $user =M('user');
		// $objecct->FromUserName;
		
		// $weixin = new Weixn();
		// $weixin->appid = C('SHOP_APPID');
		// $weixin->appsecret = C('SHOP_APPSECRET');
		// $userInfo = $weixin->getUserbyglobaltoken($objecct->FromUserName);
		// $data [UserConst::OPENID] = $userinfo [UserConst::OPENID];
		// $data [UserConst::UNIOID] = $userinfo [UserConst::UNIOID] ? $userinfo [UserConst::UNIOID] : "";
		// $data [UserConst::NICKNAME] = $userinfo [UserConst::NICKNAME];
		// $data [UserConst::SEX] = $userinfo [UserConst::SEX];
		// $data [UserConst::PROVINCE] = $userinfo [UserConst::PROVINCE];
		// $data [UserConst::CITY] = $userinfo [UserConst::CITY];
		// $data [UserConst::COUNTRY] = $userinfo [UserConst::COUNTRY];
		// $data [UserConst::HEADIMGURL] = $userinfo [UserConst::HEADIMGURL];
		// $data [UserConst::MOBILE] = '';
		// $data [UserConst::PASSWORD] = '';
		// $data [UserConst::ROLES] = 1;
		// $data[UserConst::SHOPID] =$shopid;
		// $userid = $user->add($data);
		// if($userid)
		// {
		// $content = ""
		// }
		// }
		
		$result = $this->transmitText ( $object, $content );
		
		return $result;
	}
	
	// 接收图片消息
	// private function receiveImage($object)
	// {
	// $content = array("MediaId"=>$object->MediaId);
	// $result = $this->transmitImage($object, $content);
	// return $result;
	// }
	
	// //接收位置消息
	// private function receiveLocation($object)
	// {
	// $content = "你发送的是位置，纬度为：".$object->Location_X."；经度为：".$object->Location_Y."；缩放级别为：".$object->Scale."；位置为：".$object->Label;
	// $result = $this->transmitText($object, $content);
	// return $result;
	// }
	
	// //接收语音消息
	// private function receiveVoice($object)
	// {
	// if (isset($object->Recognition) && !empty($object->Recognition)){
	// $content = "你刚才说的是：".$object->Recognition;
	// $result = $this->transmitText($object, $content);
	// }else{
	// $content = array("MediaId"=>$object->MediaId);
	// $result = $this->transmitVoice($object, $content);
	// }
	
	// return $result;
	// }
	
	// //接收视频消息
	// private function receiveVideo($object)
	// {
	// $content = array("MediaId"=>$object->MediaId, "ThumbMediaId"=>$object->ThumbMediaId, "Title"=>"", "Description"=>"");
	// $result = $this->transmitVideo($object, $content);
	// return $result;
	// }
	
	// //接收链接消息
	// private function receiveLink($object)
	// {
	// $content = "你发送的是链接，标题为：".$object->Title."；内容为：".$object->Description."；链接地址为：".$object->Url;
	// $result = $this->transmitText($object, $content);
	// return $result;
	// }
	
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