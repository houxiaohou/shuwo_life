<?php
namespace Api\Controller;

use Think\Controller\RestController;

require_once 'OrderConst.php';
require_once 'BagConst.php';
require_once 'UserConst.php';
require_once 'Weixin.php';
require_once 'BDConst.php';
class WeixinqueueApiController extends RestController {

	public  function  sendorderinfotouser()
	{
        $poststr = 'post.';
        $address = I($poststr.OrderConst::ADDRESS);
        $template = array (
        									'touser' => 'oR-0TuJI-tC6c33qgzf2ifIrudB8',
        									'template_id' => C ( 'NEWORDER_TEMPID' ),
        									'topcolor' => "#009900",
        									'data' => array (
        													'first' => array (
        																	'value' => urlencode ( $address),
        																	'color' => "#FF0000"
        															),
        													'tradeDateTime' => array (
        																	'value' => urlencode ( "test" ),
        																	'color' => "#009900"
        															),
        													'orderType' => array (
        																	'value' => urlencode ("test"),
        																	'color' => "#009900"
        															),
        													'customerInfo' => array (
        																	'value' => urlencode ( "test" ),
        																	'color' => "#009900"
        															),
        													'orderItemName' => array (
        																	'value' => urlencode ( "发货地址&配送时间" )
        															),
        													'orderItemData' => array (
        																	'value' => urlencode ( "test" ),
        																	'color' => "#009900"
        															),
        													'remark' => array (
        																	'value' => urlencode ( "\\n信息来自树窝小店" ),
        																	'color' => "#cccccc"
        															)
        											)
        											);
        $weixin = new Weixin ();
        $token = $weixin->getshopGlobalAccessToken ();
        $weixin->sendtemplatemsg ( urldecode ( json_encode ( $template ) ), $token );
	}
	
	public function sendorderinfotobd()
	{
		$current = date ( 'y年m月d日 H:i' );
		$shop = M("shop");
		$order = M("orders");
		$poststr = 'post.';
		$shopid =  I($poststr.OrderConst::SHOPID);
		$orderid = I($poststr.OrderConst::ORDERID);
		$orderNum = "订单编号：".$orderid;
		$data = $order->where("orderid ='".$orderid."'")->find();
		$orderstype = "(外送)";
		if($data[OrderConst::ISPICKUP]==1 || $data[OrderConst::DISTANCE] <50)
		{
			$orderstype="(自提)";
		}
			
		$contact = $data [OrderConst::USERNAME] . " 电话" . $data [OrderConst::PHONE];
		$address = "发货地址: " . $data [OrderConst::ADDRESS] . "   配送时间: " . $data [OrderConst::DLTIME];
		$shopname = $shop->where ( "shopid=" . $shopid )->getField ( "spn" );
		$bdshop = M ( 'bdshop' );
		$bdshops = $bdshop->where ( "shopid=" . $shopid )->select ();
		if (count ( $bdshops )) {
			$bd = M ( 'bd' );
			for($i = 0; $i < count ( $bdshops ); $i ++) {
				$bddata = $bd->where ( "bdid=" . $bdshops [$i] [BDConst::BDID] )->find ();
				if (count ( $bddata ) && ! empty ( $bddata [BDConst::OPENID] )) {
					$msgstr = $shopname . "收到新的订单".$orderstype;
// 					if ($data [OrderConst::ISFIRST] == 0 && $data [OrderConst::DISCOUNT] > 0) {
// 						$msgstr = $msgstr . '--优惠订单减免' . $data [OrderConst::DISCOUNT] . '元';
// 					} else if ($data [OrderConst::ISFIRST] == 1) {
// 						$msgstr = $msgstr . '--首购订单减免' . $data [OrderConst::DISCOUNT] . '元';
// 					}
					if ($data [OrderConst::BAG_ID] >0 && $data [OrderConst::DISCOUNT] > 0)
					{
						$msgstr = $msgstr . '--红包减免' . $data [OrderConst::DISCOUNT] . '元';
					}
                   
					$bdtemplate = array (
							'touser' => $bddata [BDConst::OPENID],
							'template_id' => C ( 'NEWORDER_TEMPID' ),
							'topcolor' => "#009900",
							'data' => array (
									'first' => array (
											'value' => urlencode ( $orderNum ),
											'color' => "#FF0000"
									),
									'tradeDateTime' => array (
											'value' => urlencode ( $current ),
											'color' => "#009900"
									),
									'orderType' => array (
											'value' => urlencode ( $msgstr ),
											'color' => "#FF0000"
									),
									'customerInfo' => array (
											'value' => urlencode ( $contact ),
											'color' => "#009900"
									),
									'orderItemName' => array (
											'value' => urlencode ( "发货地址&配送时间" )
									),
									'orderItemData' => array (
											'value' => urlencode ( $address ),
											'color' => "#009900"
									),
									'remark' => array (
											'value' => urlencode ( "\\n信息来自树窝小店" ),
											'color' => "#cccccc"
									)
							)
					);
					$weixin = new Weixin ();
					$token = $weixin->getshopGlobalAccessToken ();
					$weixin->sendtemplatemsg ( urldecode ( json_encode ( $bdtemplate ) ), $token );
				}
			}
		}
	}
	
	public function sendbagtouser()
	{
		$user = M('user');
		$bag =M('bag');
		$userid = I("post.userid");
		$bagid = I("post.bagid");
		$userinfo = $user->where("userid=".$userid)->find();
		$baginfo = $bag->where("id=".$bagid)->find();
		if(count($userinfo) && count($userinfo) && !empty($userinfo['openid']))
		{
			$start =  date('Y-m-d',strtotime($baginfo[BagConst::START]));
			$expire =  date('Y-m-d',strtotime($baginfo[BagConst::EXPIRES]));
			$content = '恭喜您获得'.$baginfo[BagConst::AMOUNT].'元红包，可使用日期'.$start.'至'.$expire;
			
			$template = array (
					'touser' => $userinfo['openid'],
					'template_id' =>'NjDObh6wXHfh4scgh29gxtmao5dYu-dtGEvR2sDk_-8',
					'url' => "http://www.shuwow.com/Home/Index/index/#/bag",
					'data' => array (
							'first' => array (
									'value' => urlencode ($content),
									'color' => "#FF0000"
							),
							'orderTicketStore' => array (
									'value' => urlencode ( "树窝水果商城购买水果" ),
									'color' => "#009900"
							),
							'orderTicketRule' => array (
									'value' => urlencode ("外送订单即可使用红包"),
									'color' => "#009900"
							),
							'remark' => array (
									'value' => urlencode ( "\\n信息来自树窝小店" ),
									'color' => "#cccccc"
							)
					)
			);
			$weixin = new Weixin ();
			$token = $weixin->getusersGlobalAccessToken();
			$weixin->sendtemplatemsg ( urldecode ( json_encode ( $template ) ), $token );
		}
		
	}
        
	
   public function sendorderinfotoshop()
   {
   	
   	  
   	
   }
   
   
   public  function cancelorder()
   {
   	$order = M('orders');
   	$id=I("post.orderid");
   	$userid = $order->where("orderid=" . $id)->getField("userid");
   	if (intval($userid)) {
   		$user = M("user");
   		$userinfo = $user->where('userid=' . $userid)->find();
   		if (count($userinfo) && !empty ($userinfo ['openid'])) {
   			$current = date('y年m月d日H:i');
   			$msg =  "树窝生活已于" . $current . "取消订单";
   			$errormsg = "商家电话:4009609670";
   			if (count($userinfo) && !empty ($userinfo ["openid"])) {
   				$template = array(
   						'touser' => trim($userinfo ["openid"]),
   						'template_id' => C('ORDERSTATUS_TEMPID'),
   						'url' => "http://www.shuwolife.com/Home/Index/index/#/order",
   						'topcolor' => "#009900",
   						'data' => array(
   								'first' => array(
   										'value' => urlencode($msg),
   										'color' => "#FF0000"
   								),
   								'OrderSn' => array(
   										'value' => urlencode($id),
   										'color' => "#009900"
   								),
   								'OrderStatus' => array(
   										'value' => urlencode($errormsg),
   										'color' => "#009900"
   								),
   								'remark' => array(
   										'value' => urlencode("\\n信息来自树窝小店"),
   										'color' => "#cccccc"
   								)
   						)
   				);
   				$weixin = new Weixin ();
   				$token = $weixin->getusersGlobalAccessToken();
   				$weixin->sendtemplatemsg(urldecode(json_encode($template)), $token);
   			}
   		}
   	}
   }
        
		
// 	private  function curl_request_async($url, $params, $type='POST')
// 	{
// 		foreach ($params as $key => &$val) {
// 			if (is_array($val)) $val = implode(',', $val);
// 			$post_params[] = $key.'='.urlencode($val);
// 		}
// 		$post_string = implode('&', $post_params);
	
// 		$parts=parse_url($url);
	
// 		$fp = fsockopen($parts['host'],
// 				isset($parts['port'])?$parts['port']:80,
// 				$errno, $errstr, 30);
	
// 		// Data goes in the path for a GET request
// 		if('GET' == $type) $parts['path'] .= '?'.$post_string;
	
// 		$out = "$type ".$parts['path']." HTTP/1.1\r\n";
// 		$out.= "Host: ".$parts['host']."\r\n";
// 		$out.= "Content-Type: application/x-www-form-urlencoded\r\n";
// 		$out.= "Content-Length: ".strlen($post_string)."\r\n";
// 		$out.= "Connection: Close\r\n\r\n";
// 		// Data goes in the request body for a POST request
// 		if ('POST' == $type && isset($post_string)) $out.= $post_string;
	
// 		fwrite($fp, $out);
// 		fclose($fp);
// 	}
	
	
	
// 		if (! empty ( $address ) && ! empty ( $phone ) && ! empty ($username )) {
				
// 			$order->add ( $data );
// 			$data2 ['orderid'] = $orderid;
				
// 			//构造模板消息	
// 			$shopid = intval ( $data [OrderConst::SHOPID] );
// 			if ($shopid) {
// 				$user = M ( "user" );
		
// 				$userinfo = $user->where ( 'shopid=' . $shopid )->select ();
		
// 				$current = date ( 'y年m月d日 H:i' );
// 				$contact = $data [OrderConst::USERNAME] . " 电话" . $data [OrderConst::PHONE];
// 				$address = "发货地址: " . $data [OrderConst::ADDRESS] . "   配送时间: " . $data [OrderConst::DLTIME];
// 				$orderNum = "订单编号：" . $orderid;
		
// 				$ordertype="新的订单";
// 				if ($data [OrderConst::ISFIRST] == 0 && $data [OrderConst::DISCOUNT] >0) {
// 					$ordertype = "优惠订单减免".$data[OrderConst::DISCOUNT]."元";
// 				}else if($data [OrderConst::ISFIRST] == 1 ){
// 					$ordertype= "首购订单减免".$data [OrderConst::DISCOUNT]."元";
// 				}
// 				// if (count ( $userinfo ) && ! empty ( $userinfo ["openid"] )) {
// 				if (count ( $userinfo )) {
// 					for($i = 0; $i < count ( $userinfo ); $i ++) {
// 						if (! empty ( $userinfo [$i] ["openid"] )) {
// 							$template = array (
// 									'touser' => $userinfo [$i] ["openid"],
// 									'template_id' => C ( 'NEWORDER_TEMPID' ),
// 									'url' => "http://www.shuwow.com/Home/Index/shop",
// 									'topcolor' => "#009900",
// 									'data' => array (
// 											'first' => array (
// 													'value' => urlencode ( $orderNum ),
// 													'color' => "#FF0000"
// 											),
// 											'tradeDateTime' => array (
// 													'value' => urlencode ( $current ),
// 													'color' => "#009900"
// 											),
// 											'orderType' => array (
// 													'value' => urlencode ($ordertype),
// 													'color' => "#009900"
// 											),
// 											'customerInfo' => array (
// 													'value' => urlencode ( $contact ),
// 													'color' => "#009900"
// 											),
// 											'orderItemName' => array (
// 													'value' => urlencode ( "发货地址&配送时间" )
// 											),
// 											'orderItemData' => array (
// 													'value' => urlencode ( $address ),
// 													'color' => "#009900"
// 											),
// 											'remark' => array (
// 													'value' => urlencode ( "\\n信息来自树窝小店" ),
// 													'color' => "#cccccc"
// 											)
// 									)
// 							);
// 							$weixin = new Weixin ();
// 							$token = $weixin->getshopGlobalAccessToken ();
// 							$weixin->sendtemplatemsg ( urldecode ( json_encode ( $template ) ), $token );
// 						}
// 					}
// 				}
// 			}
		//}
	
	
	//$url = U("WeixinqueueApi/sendorderinfotouser/",'','',true);
	//$params = ["address"=>"123"];
	//$this->curl_request_async($url,$params);
}