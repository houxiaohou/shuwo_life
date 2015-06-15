<?php


namespace Home\Controller;

use Think\Controller;

define("TOKEN", "weixin");

require_once 'wechatcallback.php';

class WeixinController extends Controller
{
	public  function  index()
	{
		$wechatObj = new wechatcallback();
		if (!isset($_GET['echostr'])) {
			$wechatObj->responseMsg();
		}else{
			$wechatObj->valid();
		}
	}
}








