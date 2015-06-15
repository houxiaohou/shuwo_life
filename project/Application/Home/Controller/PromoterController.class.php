<?php

namespace Home\Controller;

use Think\Controller;

require_once 'PromoterWechatCallback.php';

class PromoterController extends Controller
{

    /**
     * 树窝推广员的微信接口
     */
    public function index()
    {
        $wechat = new PromoterWechatCallback();
        $wechat->responseMsg();
    }

}