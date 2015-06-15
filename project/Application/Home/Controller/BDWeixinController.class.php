<?php
namespace Home\Controller;

use Think\Controller;

define("TOKEN", "bdweixin");

require_once 'bdwechatcallback.php';

class BDWeixinController extends Controller
{
	public function index()
	{
		$wechatObj = new bdwechatcallback();
		if (!isset($_GET['echostr'])) {
			$wechatObj->responseMsg();
		}else{
			$wechatObj->valid();
		}
	}
}