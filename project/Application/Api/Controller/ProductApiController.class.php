<?php

namespace Api\Controller;

use Think\Controller\RestController;

require_once 'ProductConst.php';
require_once 'GeoHash.php';
require_once 'Authorize.php';
class ProductApiController extends RestController {
	// 返回所有产品
	public function getallproducts() {
		$authorize = new Authorize ();
		if ($authorize->Filter ( "admin" )) {
			$products = M ( 'product' );
			$data = $products->select ();
			if (! count ( $data )) {
				$data = [ ];
			}
			$this->response ( $data, 'json' );
		} else {
			$message ["msg"] = "Unauthorized";
			$this->response ( $message, 'json', '401' );
		}
	}
	
	// 通过id查询产品
	public function getproductbyid() {
		$products = M ( 'product' );
		$id = intval ( I ( 'get.id', 0 ) );
		if ($id) {
			$sql = ProductConst::PRODUCTID . '="' . $id . '"';
			$data = $products->where ( $sql )->find ();
			if (! count ( $data )) {
				$data = [ ];
			}
		} else {
			$data = [ ];
		}
		$this->response ( $data, 'json' );
	}
	
	// 通过给定id号更新产品上下架
	public function updateproductissale() {
		$authorize = new Authorize ();
		$auid = $authorize->Filter ( "admin,shop" );
		if ($auid) {
			$product = M ( 'Product' );
			$post = 'post.';
			$id = intval ( I ( 'get.id', 0 ) );
			$auid = intval ( $auid );
			if ($auid) {
				if ($auid != $product->where ( "productid=" . $id )->getField ( "shopid" )) {
					$message ["msg"] = "Unauthorized";
					$this->response ( $message, 'json', '401' );
				}
			}
			$issale = intval ( I ( $post . ProductConst::ISSALE ) );
			if ($id) {
				$data [ProductConst::PRODUCTID] = $id;
				if ($issale) {
					$data [ProductConst::ISSALE] = 1;
				} else {
					$data [ProductConst::ISSALE] = 0;
				}
				$product->save ( $data );
			}
		} else {
			$message ["msg"] = "Unauthorized";
			$this->response ( $message, 'json', '401' );
		}
	}
	
	// 增加产品
	public function addproduct() {
		$post = "post.";
		$authorize = new Authorize ();
		$auid = $authorize->Filter ( "admin,shop" );
		if ($auid) {
			$shopid = 0;
			if (intval($auid))
			{
				$shopid = $auid;
			}
			else
			{
				$shopid = I($post.ProductConst::SHOPID);
			}
			$product = M ( 'product' );
			$data [ProductConst::PRODUCTNAME] = I ( $post.ProductConst::PRODUCTNAME );
			$data [ProductConst::PIMGURL] = I ( $post.ProductConst::PIMGURL );
			$data [ProductConst::ISSALE] = I ( $post.ProductConst::ISSALE );
			$data [ProductConst::NUM] = I ( $post.ProductConst::NUM );
			$data [ProductConst::PRICE] = I ( $post.ProductConst::PRICE );
			$data [ProductConst::DISCOUNT] = I ( $post.ProductConst::DISCOUNT );
			
			$data [ProductConst::ATTRIBUTE] = I ( $post.ProductConst::ATTRIBUTE );
			$data [ProductConst::CATEGORYID] = I ( $post.ProductConst::CATEGORYID);
			$data [ProductConst::UNIT] = I ( $post.ProductConst::UNIT );
			$data [ProductConst::UNITWEIGHT] = I ( $post.ProductConst::UNITWEIGHT );
			$data[ProductConst::SHOPID] = $shopid;
			// 插入product表值
			$productid = $product->add ( $data );
			
		} else {
			$message ["msg"] = "Unauthorized";
			$this->response ( $message, 'json', '401' );
		}
	}
	
	// 根据指定id删除产品
	public function deleteproduct() {
		$authorize = new Authorize ();
		$auid = $authorize->Filter ( "admin,shop" );
		if ($auid) {
			$products = M ( 'product' );
			$id = intval ( I ( 'get.id', 0 ) );
			if(intval($auid))
			{
				if ($auid != $products->where ( "productid=" . $id )->getField ( "shopid" )) {
					$message ["msg"] = "Unauthorized";
					$this->response ( $message, 'json', '401' );
				}
			}
			if ($id) {
				$where ['productid'] = $id;
				$products->where ( $where )->delete ();
			}
		} else {
			$message ["msg"] = "Unauthorized";
			$this->response ( $message, 'json', '401' );
		}
	}
	
	// 根据指定id修改产品信息
	public function updateproduct() {
		$authorize = new Authorize ();
		$auid = $authorize->Filter ( "admin,shop" );
		
		if ($auid) {
			$products = M ( 'product' );
			$id = intval ( I ( 'get.id', 0 ) );
			if(intval($auid))
			{
				if ($auid != $products->where ( "productid=" . $id )->getField ( "shopid" )) {
					$message ["msg"] = "Unauthorized";
					$this->response ( $message, 'json', '401' );
				}
			}
			if ($id) {
				$data [ProductConst::PRODUCTID] = $id;
				if (I ( 'post.productname' ) != null) {
					$data [ProductConst::PRODUCTNAME] = I ( 'post.productname' );
				}
				if (I ( 'post.pimgurl' ) != null) {
					$data [ProductConst::PIMGURL] = I ( 'post.pimgurl' );
				}
				if (I ( 'post.issale' ) != null) {
					$data [ProductConst::ISSALE] = I ( 'post.issale' );
				}
				if (I ( 'post.num' ) != null) {
					$data [ProductConst::NUM] = I ( 'post.num' );
				}
				if (I ( 'post.price' ) != null) {
					$data [ProductConst::PRICE] = I ( 'post.price' );
				}
				if (I ( 'post.discount' ) != null) {
					$data [ProductConst::DISCOUNT] = I ( 'post.discount' );
				}
				if (I ( 'post.attribute' ) != null) {
					$data [ProductConst::ATTRIBUTE] = I ( 'post.attribute' );
				}
				if (I ( 'post.categoryid' ) != null) {
					$data [ProductConst::CATEGORYID] = I ( 'post.categoryid' );
				}
				if (I ( 'post.unit' ) != null) {
					$data [ProductConst::UNIT] = I('post.unit');
			}
			if (I('post.unitweight') != null) {
				$data[ProductConst::UNITWEIGHT] = I('post.unitweight');
			}
		}
		$products->save($data);		
	   }else{
	       $message ["msg"] = "Unauthorized";
	       $this->response ( $message, 'json', '401' );
	   }
	}
}