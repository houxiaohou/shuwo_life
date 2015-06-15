<?php

namespace Api\Controller;

use Think\Controller\RestController;

require_once 'CategorypicConst.php';
require_once 'CategoryConst.php';
require_once 'Authorize.php';
class CategoryApiController extends RestController {

	// 返回所有种类
	public function getallcategorys() {
		$authorize = new Authorize ();
		$auid = $authorize->Filter ( 'admin' );
		if ($auid) {
			$category = M ( "category" );
			$data = $category->select ();
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
	public function getcategorybyid() {
		$authorize = new Authorize ();
		$auid = $authorize->Filter ( 'admin' );
		if ($auid) {
		$category = M ( "category" );
		$categorypic = M("categorypic");
		$categoryid = intval ( I ( 'get.id', 0 ) );
		if (! $categoryid) {
			$data = [ ];
		}
		$sql = CategoryConst::CATEGORYID . '="' . $categoryid . '"';
		$data['category'] = $category->where ( $sql )->find ();
		$data['categorypic'] = $categorypic->where($sql)->select();
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
	public function addcategory() {
		$authorize = new Authorize ();
		$auid = $authorize->Filter ( 'admin' );
		if ($auid) {
			$category = M ( "category" );
			$categorypic = M ("categorypic");
			// 			if (I ( 'post.categoryname' ) != null) {
			// 				$data [CategoryConst::CATEGORYNAME] = I ( 'post.categoryname' );
			// 			}
			$categoryImg = I('post.categoryImg');
			if ($categoryImg != null) {
				$data [CategoryConst::CATEGORYNAME] = $categoryImg[0][0];
				$categoryid = $category->add ( $data );
				$datapic[CategorypicConst::CATEGORYID] = $categoryid;
				for ($i=0;$i<count($categoryImg);$i++){
					$datapic [CategorypicConst::IMGURL] = $categoryImg[$i][1];
					$datapic [CategorypicConst::DES] = $categoryImg[$i][2];
					$categorypic->add($datapic);
				}
			}
			// 			$category->add ( $data );
			$this->response ( $data, "json" );
		} else {
			$message ["msg"] = "Unauthorized";
			$this->response ( $message, 'json', '401' );
		}
	}
	// 更新种类
	public function updatecategory() {
		$authorize = new Authorize ();
		$auid = $authorize->Filter ( 'admin' );
		if ($auid) {
			$category = M ( 'category' );
			$categorypic = M('categorypic');
			$id = intval ( I ( 'get.id', 0 ) );
			$img = I('post.img');
			if ($id) {
				$data [CategoryConst::CATEGORYID] = $id;
				$where [CategorypicConst::CATEGORYID] = $id;
			}
			if (I ( 'post.categoryname' ) != null) {
				$data [CategoryConst::CATEGORYNAME] = I ( 'post.categoryname' );
			}
			$data = $category->save ( $data );
				
			if($img['imgurl'] != null){
				$data_pic[CategorypicConst::IMGURL]=$img['imgurl'];
				$data_pic[CategorypicConst::DES]=$img['desc'];
				$data_pic[CategorypicConst::CATEGORYID]=$id;
				$categorypic->where($where)->add($data_pic);
			}
				
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
				$category = M ( 'category' );
				$categorypic = M('categorypic');
				$categoryid = $category->where ( 'categoryid  =' . $id )->delete ();

				if ($categoryid) {
					$product = M ( 'product' );
					$product->where ( 'categoryid  =' . $id )->delete ();
					$categorypic->where ( 'categoryid  =' . $id )->delete ();
				}
			}
		} else {
			$message ["msg"] = "Unauthorized";
			$this->response ( $message, 'json', '401' );
		}
	}
	// 根据图片URL删除相应种类的图片
	public function deletecategoryimgbyurl() {
		$authorize = new Authorize ();
		$auid = $authorize->Filter ( 'admin' );
		if ($auid) {
			$id =  I ( 'get.id' ,0) ;
			var_dump($id);
			if ($id) {
				$categorypic = M('categorypic');
				$categoryid = $categorypic->where ( 'id  =' . $id )->delete ();
			}
		} else {
			$message ["msg"] = "Unauthorized";
			$this->response ( $message, 'json', '401' );
		}
	}

}