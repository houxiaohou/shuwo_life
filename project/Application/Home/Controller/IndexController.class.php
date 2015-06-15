<?php

namespace Home\Controller;

use Think\Controller;

require_once 'Weixin.php';
require_once 'Xcrypt.php';
require_once 'UserConst.php';
require_once 'BagConst.php';
class IndexController extends Controller {
	public function index() {

		$appid = C ( 'SHUWO_APPID' );
		$appsecret = C ( 'SHUWO_APPSECRET' );
		$weixin = new Weixin ();
		$weixin->appid = $appid;
		$weixin->appsecret = $appsecret;
		$url = $weixin->getwxurl(C('SHUWO_CALLBACK'));
		$key = C ( "CRYPT_KEY" );
		$xcrpt = new Xcrypt ( $key, 'cbc', $key );
		if (cookie ( 'utoken' )) {
			$value = cookie ( 'utoken' );
            $data = $xcrpt->decrypt ( $value, 'base64' );
			if ($data) {
				$str = explode ( "#", $data );
				if ($str && count ( $str ) == 3)
				{
					$userid = intval ( $str [0] );
					if ($userid) {
						$user = M ( 'user' );
						$sql = "userid=" . $userid;
						$userinfo = $user->where ( $sql )->find ();
						if (! count ( $userinfo )) {
							cookie ( 'utoken',null );
							$redircturl = "Location:".$url;
							header($redircturl);
							exit;
						}
						else 
						{
							$this->display ();
						}

					}
			    }
			    else {
			    	$redircturl = "Location:".$url;
			    	header($redircturl);
			    	exit;
			    }
			} else {
				$redircturl = "Location:".$url;
				header($redircturl);
				exit;
				
				// 测试模拟代码
				//$this->redirect ( "authorize" );
			}
		} else {
    		$redircturl = "Location:".$url;
            header($redircturl);
            exit;
			
			// 测试模拟代码
			//$this->redirect ( "authorize" );
		}
	}
	public function authorize() {
		$weixin = new Weixin ();
		$appid = C ( 'SHUWO_APPID' );
		$appsecret = C ( 'SHUWO_APPSECRET' );
		$weixin->appid = $appid;
		$weixin->appsecret = $appsecret;
		$key = C ( "CRYPT_KEY" );
		$xcrpt = new Xcrypt ( $key, 'cbc', $key );		
		$code = I('get.code');
		// 测试代码
// 		$code = "testcode";
// 		$token ['openid'] = "openid";
// 		$token ['access_token'] = "access_token";
// 		$userinfo ["openid"] = "openid";
// 		$userinfo ["nickname"] = "test";
// 		$userinfo ["sex"] = "1";
// 		$userinfo ["province"] = "上海";
// 		$userinfo ["city"] = "上海";
// 		$userinfo ["country"] = "中国";
// 		$userinfo ["headimgurl"] = "http://wx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ4eMsv84eavHiaiceqxibJxCfHe/46";
// 		$userinfo ["unionid"] = "uninonid";
		
		if ($code) {
			$token = $weixin->getTokenWithCode ( $code );
			if ($token) {
				$openid = $token ['openid'];
				$accessToken = $token ['access_token'];
				$userinfo = $weixin->getUser($openid, $accessToken);
				if ($userinfo) {
					$useropenid = $userinfo ['openid'];
					$user = M ( 'user' );
					$sql = "openid='" . $useropenid . "'";
					$data = $user->where ( $sql )->find ();
					if (count ( $data )) {
						$userid = $data [UserConst::USERID];
						$datetime = date ( 'Ymd', strtotime ( '+14 day' ) );
						$str = $userid . "#" . $datetime . "#new";
						$xcrptstr = $xcrpt->encrypt ( $str, 'base64' );
						cookie ( 'utoken', $xcrptstr, 1209600 );
					} else {
						$data [UserConst::OPENID] = $userinfo [UserConst::OPENID];
						$data [UserConst::UNIOID] = $userinfo [UserConst::UNIOID] ? $userinfo [UserConst::UNIOID] : "";
						$data [UserConst::NICKNAME] = $userinfo [UserConst::NICKNAME];
						$data [UserConst::SEX] = $userinfo [UserConst::SEX];
						$data [UserConst::PROVINCE] = $userinfo [UserConst::PROVINCE];
						$data [UserConst::CITY] = $userinfo [UserConst::CITY];
						$data [UserConst::COUNTRY] = $userinfo [UserConst::COUNTRY];
						$data [UserConst::HEADIMGURL] = $userinfo [UserConst::HEADIMGURL];
						$data [UserConst::MOBILE] = '';
						$data [UserConst::PASSWORD] = '';
						$data [UserConst::ROLES] = 0;
						$userid = $user->add ( $data );
						$datetime = date ( 'Ymd', strtotime ( '+14 day' ) );
						$str = $userid . "#" . $datetime . "#new";
						$xcrptstr = $xcrpt->encrypt ( $str, 'base64' );
						cookie ( 'utoken', $xcrptstr, 1209600 );
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
					}
					$this->redirect ( "index" );
				} else {
					E ( '获得微信用户信息异常' );
				}
			} else {
				E ( '获得access_token异常' );
			}
		} else {
			E ( '获得code异常' );
		}
	}
	
	public function shop() {
	    $appid = C ( 'SHOP_APPID' );
	    $appsecret = C ( 'SHOP_APPSECRET' );
	    $weixin = new Weixin ();
	    $weixin->appid = $appid;
	    $weixin->appsecret = $appsecret;
	    $url = $weixin->getwxurl ( C('SHOP_CALLBACK') );
	    $key = C ( "CRYPT_KEY" );
	    $xcrpt = new Xcrypt ( $key, 'cbc', $key );
	    if (cookie ( 'stoken' )) {
	        $value = cookie ( 'stoken' );
	        $data = $xcrpt->decrypt ( $value, 'base64' );
	        if ($data) {
	            $str = explode ( "#", $data );
	            if ($str && count ( $str ) >= 2) {
	                $userid = intval ( $str [0] );
	                if ($userid) {
	 					    $model = M('user');
                    		$sql = "userid=".$userid." AND shopid =".$str[1]." AND openid='".trim($str[2])."' AND roles=1" ;
                    		$info = $model->where($sql)->select();
	                    if (! count ( $info )) {
	                       	cookie ( 'stoken',null );
							$redircturl = "Location:".$url;
							header($redircturl);
							exit;
	                    }
	                    else 
	                    {
	                      $this->display ();
	                    }
	                }
	            }
	            else {
	            	$redircturl = "Location:".$url;
	            	header($redircturl);
	            	exit;
	            }
	        } else {
	            $redircturl = "Location:".$url;
	            header($redircturl);
	            exit;
	
	            // 测试模拟代码
               //$this->redirect ( "shopauthorize" );
	        }
	    } else {
	        $redircturl = "Location:".$url;
	        header($redircturl);
	        exit;
	        	
	        // 测试模拟代码
	        //$this->redirect ( "shopauthorize" );
	    }
	}
	public function shopauthorize() {
	    $weixin = new Weixin ();
	    $appid = C ( 'SHOP_APPID' );
	    $appsecret = C ( 'SHOP_APPSECRET' );
	    $weixin->appid = $appid;
	    $weixin->appsecret = $appsecret;
	    $key = C ( "CRYPT_KEY" );
	    $xcrpt = new Xcrypt ( $key, 'cbc', $key );
	    $code = I('get.code');
	    // 测试代码
// 	    $code = "testcode";
// 	    $token ['openid'] = "openid";
// 	    $token ['access_token'] = "access_token";
// 	    $userinfo ["openid"] = "shopopenid";
// 	    $userinfo ["nickname"] = "testshop";
// 	    $userinfo ["sex"] = "1";
// 	    $userinfo ["province"] = "上海";
// 	    $userinfo ["city"] = "上海";
// 	    $userinfo ["country"] = "中国";
// 	    $userinfo ["headimgurl"] = "http://wx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ4eMsv84eavHiaiceqxibJxCfHe/46";
// 	    $userinfo ["unionid"] = "shopuninonid";
	
	    if ($code) {
	        $token = $weixin->getTokenWithCode ( $code );
	        	
	        if ($token) {
	            $openid = $token ['openid'];
	            $accessToken = $token ['access_token'];
	            if (isset($openid)) {
	                $user = M ( 'user' );
	                $sql = "openid='" . $openid . "'";
	                $data = $user->where ( $sql )->find ();
	                if (count ( $data )) {
                        $userid = $data [UserConst::USERID];
	                    $shopid = $data [UserConst::SHOPID];
	                    $str = $userid . "#" . $shopid."#".$openid;
	                    $xcrptstr = $xcrpt->encrypt ( $str, 'base64' );
	                    cookie ( 'stoken', $xcrptstr, 1209600 );
	                    $this->redirect ( "shop" );
	                } else {
	                	E("未授权商户信息");
	                }
	            } else {
	                E ( '获得微信用户信息异常' );
	            }
	        } else {
	            E ( '获得access_token异常' );
	        }
	    } else {
	        E ( '获得code异常' );
	    }
	}
	public function admin(){
	    $this->display();
	}
}