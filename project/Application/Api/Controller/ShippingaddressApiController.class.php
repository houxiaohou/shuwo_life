<?php

namespace Api\Controller;

use Think\Controller\RestController;

require_once 'ShippingaddressConst.php';
require_once 'Authorize.php';
class ShippingaddressApiController extends RestController {
	// 根据id查询对应的用户地址
	public function getaddressbyid() {
			$address = M ( 'shippingaddress' );
			$said = intval ( I ( 'get.id', 0 ) );
			if ($said) {
				$sql = ShippingaddressConst::SAID . '="' . $said . '"';
				$data = $address->where ( $sql )->find ();
				if (! count ( $data )) {
					$data = [ ];
				} else {
					if ($data [ShippingaddressConst::USERID] != $userid) {
						$message ["msg"] = "Unauthorized";
						$this->response ( $message, 'json', '401' );
					}
					$this->response ( $data, "json" );
				}
			}
	}
	// 更新用户地址
	public function updateaddress() {
		$authorize = new Authorize ();
		$userid = $authorize->Filter ( "user" );
		if ($userid) {
			$address = M ( 'shippingaddress' );
			$said = intval ( I ( 'get.id', 0 ) );
			if ($said) {
				$data [ShippingaddressConst::SAID] = $said;
				$data [ShippingaddressConst::USERID] = $userid;
				$username = I ( 'post.username','');
				$province = I ( 'post.province','');
				$city    =  I ( 'post.city','');
				$district = I ( 'post.district','');
				$addres = I ( 'post.address','');
				$mobile = I ( 'post.mobile','');				
				if (!empty($username) && !empty($province) && !empty($city)  && !empty($district) && !empty($addres) && !empty($mobile))
			     {
					$data [ShippingaddressConst::USERNAME] = $username;			
				    $data [ShippingaddressConst::PROVINCE] = $province;			
				    $data [ShippingaddressConst::CITY] = $city;			
				    $data [ShippingaddressConst::DISTRICT] = $district;		
				    $data [ShippingaddressConst::ADDRESS] = $addres;			
				    $data [ShippingaddressConst::MOBILE] = $mobile;
					$data [ShippingaddressConst::ISDEFAULT] = 1;
					if ($address->save ( $data )) {
					    $address->where ( "userid = {$userid}  and said !=" . $said )->setField ( 'isdefault', 0 );
					}
					$this->response ( $data, 'json' );
					}
				}				
		} else {
			$message ["msg"] = "Unauthorized";
			$this->response ( $message, 'json', '401' );
		}
	}
	// 添加用户地址
	public function addaddress() {
		$authorize = new Authorize ();
		$userid = $authorize->Filter ( "user" );
		if ($userid) {
			$address = M ( 'shippingaddress' );
			$data [ShippingaddressConst::USERID] = $userid;
			$username = I ( 'post.username','');
			$province = I ( 'post.province','');
			$city    =  I ( 'post.city','');
			$district = I ( 'post.district','');
			$addres = I ( 'post.address','');
			$mobile = I ( 'post.mobile','');
			if (!empty($username) && !empty($province) && !empty($city)  && !empty($district) && !empty($addres) && !empty($mobile))
			{
				$data [ShippingaddressConst::USERNAME] = $username;			
				$data [ShippingaddressConst::PROVINCE] = $province;			
				$data [ShippingaddressConst::CITY] = $city;			
				$data [ShippingaddressConst::DISTRICT] = $district;		
				$data [ShippingaddressConst::ADDRESS] = $addres;			
				$data [ShippingaddressConst::MOBILE] = $mobile;			
			    $data [ShippingaddressConst::ISDEFAULT] = 1;			    
			    $said = $address->add ( $data );
			    if ($said) {
			        $address->where ( "userid = {$userid}  and said !=" . $said )->setField ( 'isdefault', 0 );
			    }
			    $this->response ( $data, 'json' );
			}
		} else {
			$message ["msg"] = "Unauthorized";
			$this->response ( $message, 'json', '401' );
		}
	}
	// 删除用户地址
	public function deleteaddress() {
		$authorize = new Authorize ();
		$userid = $authorize->Filter ( "user" );
		if ($userid) {
			$address = M ( 'shippingaddress' );
			$said = intval ( I ( 'get.id', 0 ) );
			if ($said) {
				$address->where ( 'said  =' . $said )->delete ();
			}
		} else {
			$message ["msg"] = "Unauthorized";
			$this->response ( $message, 'json', '401' );
		}
	}
	/*
	 * 局部更新
	 */
	public function updateisdefault() {
		$authorize = new Authorize ();
		$userid = $authorize->Filter ( "user" );
		if ($userid) {
			$said = intval ( I ( 'get.id', 0 ) );
			if ($said) {
				$address = M ( 'shippingaddress' );
				$data [ShippingaddressConst::SAID] = $said;
				$data [ShippingaddressConst::USERID] = $userid;
				$data [ShippingaddressConst::ISDEFAULT] = 1;
				if ($address->save ( $data )) {
					$address->where ( "userid = {$userid}  and said !=" . $said )->setField ( 'isdefault', 0 );
				}
				$this->response ( $data, 'json' );
			}
		} else {
			$message ["msg"] = "Unauthorized";
			$this->response ( $message, 'json', '401' );
		}
	}
	/*
	 * 根据userid获取用户的全部地址
	 */
	public function getalluseraddress() {
		$authorize = new Authorize ();
		$userid = $authorize->Filter ( "user" );
		if ($userid) {
			$address = M ( 'shippingaddress' );
			if ($userid) {
				$where [ShippingaddressConst::USERID] = $userid;
				$data = $address->where ( $where )->select ();
			}
			$this->response ( $data, 'json' );
		} else {
			$message ["msg"] = "Unauthorized";
			$this->response ( $message, 'json', '401' );
		}
	}
	/*
	 * 获取用户的默认地址
	 */
	public function useraddress() {
		$authorize = new Authorize ();
		$userid = $authorize->Filter ( "user" );
		if ($userid) {
			$address = M ( 'shippingaddress' );
			if ($userid) {
				$where [ShippingaddressConst::USERID] = $userid;
				$where [ShippingaddressConst::ISDEFAULT] = 1;
				$data = $address->where ( $where )->find ();
			}
			$this->response ( $data, 'json' );
		} else {
			$message ["msg"] = "Unauthorized";
			$this->response ( $message, 'json', '401' );
		}
	}
}
