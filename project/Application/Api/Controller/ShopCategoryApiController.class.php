<?php
namespace Api\Controller;

use Think\Controller\RestController;

require_once 'ShopCategoryConst.php';
require_once 'Authorize.php';

class ShopCategoryApiController extends RestController {
	
	// 返回所有种类
	public function getallshopcategorys() {
		$authorize = new Authorize ();
		$auid = $authorize->Filter ( 'admin' );
		if ($auid) {
			$shopcategory = M ( "shopcategory" );
			$data = $shopcategory->select ();
			if (! count ( $data )) {
				$data = [ ];
			}
			$this->response ( $data, "json" );
		} else {
			$message ["msg"] = "Unauthorized";
			$this->response ( $message, 'json', '401' );
		}
	}
	// 通过ID查询种类
	public function getshopcategorybyid() {
		$authorize = new Authorize ();
		$auid = $authorize->Filter ( 'admin' );
		if ($auid) {
			$shopcategory = M ( "shopcategory" );
			$shopcategoryid = intval ( I ( 'get.id', 0 ) );
			if (! $shopcategoryid) {
				$data = [ ];
			}
			$sql = ShopCategoryConst::ID. '=' . $shopcategoryid;
			$data = $shopcategory->where ( $sql )->find ();
			if (! count ( $data )) {
				$data = [ ];
			}
			$this->response ( $data, "json" );
		}
		else {
			$message ["msg"] = "Unauthorized";
			$this->response ( $message, 'json', '401' );
		}
	}
	// 添加种类
	public function addshopcategory() {
		$authorize = new Authorize ();
		$auid = $authorize->Filter ( 'admin' );
		if ($auid) {
			$shopcategory = M ( "shopcategory" );
			$name = I('post.name');
			$data[ShopCategoryConst::NAME] = $name;
			if ($name != null) {
				 $shopcategory->add ( $data );
			}
			$this->response ( $data, "json" );
		} else {
			$message ["msg"] = "Unauthorized";
			$this->response ( $message, 'json', '401' );
		}
	}
	// 更新种类
	public function updateshopcategory() {
		$authorize = new Authorize ();
		$auid = $authorize->Filter ( 'admin' );
		if ($auid) {
			$shopcategory = M ( 'shopcategory' );
			$id = intval ( I ( 'get.id', 0 ) );
			$name = I('post.name');
			if ($id) {
				$data [ShopCategoryConst::ID] = $id;
				$data[ShopCategoryConst::NAME] = $name;
			}
			$data = $shopcategory->save ( $data );
	
			$this->response ( $data, "json" );
		} else {
			$message ["msg"] = "Unauthorized";
			$this->response ( $message, 'json', '401' );
		}
	}
	// 删除种类
	public function deletecategory() {
		$authorize = new Authorize ();
		$auid = $authorize->Filter ( 'admin' );
		if ($auid) {
			$id = intval ( I ( 'get.id', 0 ) );
			if ($id) {
				$shopcategory = M ( 'shopcategory' );
				$sql = ShopCategoryConst::ID. '=' . $id;
	            $shopcategory->where($sql)->delete();
			}
		} else {
			$message ["msg"] = "Unauthorized";
			$this->response ( $message, 'json', '401' );
		}
	}
}