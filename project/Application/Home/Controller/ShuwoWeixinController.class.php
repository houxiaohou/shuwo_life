<?php
namespace Home\Controller;

use Think\Controller;

define("TOKEN", "shuwoweixin");

require_once 'shuwowechatcallback.php';

class ShuwoWeixinController extends Controller
{
	public function index()
	{
		$wechatObj = new shuwowechatcallback();
		if (!isset($_GET['echostr'])) {
			$wechatObj->responseMsg();
		}else{
			$wechatObj->valid();
		}
	}
}