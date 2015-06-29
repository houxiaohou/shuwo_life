<?php

namespace Api\Controller;


use Think\Controller\RestController;

require_once 'Authorize.php';
require_once 'OrderProductConst.php';
require_once 'OrderConst.php';
require_once 'ProductConst.php';

class PrintApiController extends RestController
{

    /**
     * 根据订单号打印订单
     */
    public function printOrder()
    {

        $authorize = new Authorize ();
        $isAdmin = $authorize->Filter("admin");
        if (!$isAdmin) {
            $message ["msg"] = "Unauthorized";
            $this->response($message, 'json', '401');
        }
        $orderId = I('get.id');
        $order = M('orders');
        $orderProduct = M('orderproduct');
        $product = M('product');

        $where [OrderConst::ORDERID] = $orderId;
        $orderData = $order->where($where)->find();

        $where_order_product [OrderProductConst::ORDERID] = $orderData [OrderConst::ORDERID];
        $orderProductData = $orderProduct->where($where_order_product)->field('id,productid,quantity,realweight,realprice')->select();


        $message = "^B3树窝生活\r";
        $message .= "--------------------------------\r";
        $count_order_product = count($orderProductData);
        for ($j = 0; $j < $count_order_product; $j++) {
            $where_product [ProductConst::PRODUCTID] = $orderProductData [$j] [OrderProductConst::PRODUCTID];
            $productdata = $product->where($where_product)->field('productname,unit,attribute,unitweight')->find();
            $name = $productdata ['productname'];
            $quantity = $orderProductData [$j] [OrderProductConst::QUANTITY];
            $price = $orderProductData [$j] [OrderProductConst::REALPRICE];

            $message .= "" . $name . "  " . "x" . $quantity . "  ￥" . $price . "\r";
        }

        $message .= "--------------------------------\r";
        $message .= "^B2总价：￥" . $orderData[OrderConst::TOTALPRICE];
        $result = $this->add_order('kdt1023148', date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8), $message);
        $this->response($result);
    }


    function add_order($printer_sn, $order_id, $msg)
    {
        $url = "http://printer.showutech.com/api/2/service/add_order/$printer_sn/$order_id/";
        $data = array('data' => $msg);

        // use key 'http' even if you send the request to https://...
        $options = array(
            'http' => array(
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data),
            ),
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return $result;
    }

    function query_order($printer_sn, $order_id)
    {
        $url = "http://printer.showutech.com/api/2/service/query_order_status/$printer_sn/$order_id/";
        $options = array(
            'http' => array(
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'GET',
            ),
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        return $result;
    }


}