<?php

namespace Api\Controller;

use Think\Controller\RestController;

require_once 'OrderConst.php';
require_once 'Authorize.php';
require_once 'OrderProductConst.php';
require_once 'ProductConst.php';
class SearchApiController extends RestController {
	/*
	 * 模糊查询
	 */
	public function searchorderbyadmin() {
		$authorize = new Authorize ();
        $auid = $authorize->Filter ( "admin" );
		if ($auid) {
			$orders = M ( 'orders' );
			$search = I ( 'post.search'.'');
			
			$where = "orderid like '%" . $search . "%' or phone like '%" . $search . "%'";
			$data = $orders->where ( $where )->order('-createdtime')->select ();
			if(!count($data))
			{
			   $data = [];
			}
			$this->response ( $data, 'json' );
		} else 
		{
			$message ["msg"] = "Unauthorized";
			$this->response ( $message, 'json', '401' );
            
		}
    }
    
    public function searchorderbyshop()
    {
    	$authorize = new Authorize ();
    	$auid = $authorize->Filter ( "shop" );
    	if (intval($auid)>0) {
            $shopid =  $auid;
    		$orders = M ( 'orders' );
    		$search = I ( 'post.search'.'');	
    		$where = "orderid =" . $search . " or phone = ". $search ." AND shopid =".$shopid;
    		$orderdata = $orders->where ( $where )->order('-createdtime')->select ();
    		$count = count($orderdata);
    		if(!$count)
    		{
    			$data = [];
    		}
    		
    		$data = $this->orderdetail ( $orderdata, $count );
    		$this->response ( $data, 'json' );
    		
    	} else
    	{
    		$message ["msg"] = "Unauthorized";
    		$this->response ( $message, 'json', '401' );
    	
    	}
    }
    
    
    private function orderdetail($orderdata, $count) {
    	$orderproduct = M ( 'orderproduct' );
    	$product = M ( 'product' );
    	$data = [ ];
    	for($i = 0; $i < $count; $i ++) {
    		$data_order_product = [ ];
    		if ($orderdata [$i] [OrderConst::ORDERID] == null) {
    			break;
    		} else {
    			$data [$i] [OrderConst::ORDERID] = $orderdata [$i] [OrderConst::ORDERID];
    			$data [$i] [OrderConst::CREATEDTIME] = $orderdata [$i] [OrderConst::CREATEDTIME];
    			$data [$i] [OrderConst::ORDERSTATUS] = $orderdata [$i] [OrderConst::ORDERSTATUS];
    			$data [$i] [OrderConst::USERNAME] = $orderdata [$i] [OrderConst::USERNAME];
    			$data [$i] [OrderConst::ADDRESS] = $orderdata [$i] [OrderConst::ADDRESS];
    			$data [$i] [OrderConst::PHONE] = $orderdata [$i] [OrderConst::PHONE];
    			$data [$i] [OrderConst::NOTES] = $orderdata [$i] [OrderConst::NOTES];
    			$data [$i] [OrderConst::SHOPID] = $orderdata [$i] [OrderConst::SHOPID];
    			$data [$i] [OrderConst::DLTIME] = $orderdata [$i] [OrderConst::DLTIME];
    			$data [$i] [OrderConst::ISFIRST] = $orderdata [$i] [OrderConst::ISFIRST];
    			$data [$i] [OrderConst::DISCOUNT] = $orderdata [$i] [OrderConst::DISCOUNT];
    
    			if ($orderdata [$i] [OrderConst::RTOTALPRICE] >= 0 && $orderdata [$i] [OrderConst::ORDERSTATUS] == 1) {
    				$data [$i] ['price'] = $orderdata [$i] [OrderConst::RTOTALPRICE];
    			} else {
    				$data [$i] ['price'] = $orderdata [$i] [OrderConst::TOTALPRICE];
    			}
    			if ($orderdata [$i] [OrderConst::RTOTALPRICEBEFORE] >= 0 && $orderdata [$i] [OrderConst::ORDERSTATUS] == 1) {
    				$data [$i] ['beforeprice'] = $orderdata [$i] [OrderConst::RTOTALPRICEBEFORE];
    			} else {
    				$data [$i] ['beforeprice'] = $orderdata [$i] [OrderConst::TOTALPRICEBEFORE];
    			}
    
    			if ($orderdata [$i] [OrderConst::ORDERNOTES] != null) {
    				$data [$i] [OrderConst::ORDERNOTES] = $orderdata [$i] [OrderConst::ORDERNOTES];
    			}
    			$where_order_product [OrderProductConst::ORDERID] = $orderdata [$i] [OrderConst::ORDERID];
    			$orderproductdata = $orderproduct->where ( $where_order_product )->field ( 'id,productid,quantity,realweight,realprice' )->select ();
    			$count2 = count ( $orderproductdata );
    			for($j = 0; $j < $count2; $j ++) {
    				$data_order_product [$j] ['orderproductid'] = $orderproductdata [$j] [OrderProductConst::ID];
    				$data_order_product [$j] ['quantity'] = $orderproductdata [$j] [OrderProductConst::QUANTITY];
    				$data_order_product [$j] ['realprice'] = $orderproductdata [$j] [OrderProductConst::REALPRICE];
    				$data_order_product [$j] ['realweight'] = $orderproductdata [$j] [OrderProductConst::REALWEIGHT];
    				$where_product [ProductConst::PRODUCTID] = $orderproductdata [$j] [OrderProductConst::PRODUCTID];
    				$productdata = $product->where ( $where_product )->field ( 'productname,unit,attribute,unitweight,price,discount' )->find ();
    				$data_order_product [$j] ['productname'] = $productdata ['productname'];
    				$data_order_product [$j] ['price'] = $productdata ['price'];
    				$data_order_product [$j] ['unit'] = $productdata ['unit'];
    				$data_order_product [$j] ['attribute'] = $productdata ['attribute'];
    				$data_order_product [$j] ['unitweight'] = $productdata ['unitweight'];
    				if (intval ( $data_order_product [$j] ['discount'] )) {
    					$data_order_product [$j] ['discount'] = $productdata ['discount'];
    				} else {
    					$data_order_product [$j] ['discount'] = $productdata ['price'];
    				}
    			}
    		}
    		$data [$i] ['productdetail'] = $data_order_product;
    	}
    	return $data;
    }
    
    
}