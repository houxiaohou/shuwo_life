<?php

namespace Api\Controller;

use Think\Controller\RestController;

require_once 'CategorypicConst.php';
require_once 'Authorize.php';
class CategorypicApiController extends RestController {
	// 添加种类
	public function addcategorypic() {
		$authorize = new Authorize ();
		$auid = $authorize->Filter ( 'admin' );
		if ($auid) {
			$categorypic = M ( "categorypic" );
			$categorypicid =I ( 'post.categorypicid' );
			$imgurl = I('post.imgurl');
			$des = I('post.des');
			 if (!empty($categorypicid) && !empty($imgurl) && !empty($des)){
			 		$data [CategorypicConst::CATEGORYID] = $categorypicid;
			 		$data [CategorypicConst::IMGURL] = $imgurl;
			 		$data [CategorypicConst::DES] = $des;
			 		$categorypic->add ( $data );
			 		$this->response ( $data, "json" );
			}
		} else {
			$message ["msg"] = "Unauthorized";
			$this->response ( $message, 'json', '401' );
		}
	}
	// 删除种类
	public function deletecategorypic() {
	    $authorize = new Authorize ();
	    $auid = $authorize->Filter ( 'admin' );
	    if ($auid) {
	        $id = intval ( I ( 'get.id', 0 ) );
	        if ($id) {
	            $categorypic = M ( 'categorypic' );
	            $categoryid = $categorypic->where ( "id  =  {$id}" )->delete ();
	        }
	    } else {
	        $message ["msg"] = "Unauthorized";
	        $this->response ( $message, 'json', '401' );
	    }
	}
	//根据CATEGORIID 查询对应的信息
	public function getcategorypicbycategoryid() {
	    $authorize = new Authorize ();
	    $auid = $authorize->Filter ( "admin,shop");
	    if ($auid) {
	    $category = M ( "categorypic" );
	    $categoryid = intval ( I ( 'get.id', 0 ) );
	    if (! $categoryid) {
	        $data = [ ];
	    }
	    $sql = CategorypicConst::CATEGORYID . '="' . $categoryid . '"';
	    $data = $category->where ( $sql )->select ();
	    if (! count ( $data )) {
	        $data = [ ];
	    }
	    $this->response ( $data, "json" );
	    }else{
	        $message ["msg"] = "Unauthorized";
	        $this->response ( $message, 'json', '401' );
	    }
	}
	//更新
	public function updatecategorypic(){
	    $authorize = new Authorize ();
	    $auid = $authorize->Filter ( "admin,shop");
	    if ($auid) {
	        $category = M ( 'categorypic' );
	        $id = intval ( I ( 'get.id', 0 ) );
	        $imgurl = I('post.imgurl');
	        $des = I('post.des');
	        if(!empty($id) && !empty($imgurl) && !empty($des)){
	            $data[CategorypicConst::ID] = $id;
	            $data[CategorypicConst::IMGURL] = $imgurl;
	            $data[CategorypicConst::DES] = $des;
	            $data = $category->save ( $data );
	            $this->response ( $data, "json" );
	        }
	    } else {
	        $message ["msg"] = "Unauthorized";
	        $this->response ( $message, 'json', '401' );
	    }
	}
}