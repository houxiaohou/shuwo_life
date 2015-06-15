<?php
namespace Api\Controller;

class OrderConst
{
    const ORDERID = "orderid";
    const ORDERSTATUS = "orderstatus";
    const USERID = "userid";
    const SHOPID = "shopid";
    const PAYSTATUS = "paystatus";
    const TOTALPRICE = "totalprice"; // 预估减折扣，减红包
    const USERNAME = "username";
    const ADDRESS = "address";
    const PHONE = "phone";
    const CREATEDTIME = "createdtime";
    const RTOTALPRICE = "rtotalprice"; // 减去折扣，需要支付的金额， 减红包
    const DLTIME = "dltime";
    const NOTES = "notes";
    const ORDERNOTES = "ordernotes";
    const ISFIRST = "isfirst";
    const DISCOUNT = 'discount'; // 折扣
    const TOTALPRICEBEFORE = "totalpricebefore"; // 下单价钱
    const RTOTALPRICEBEFORE = "rtotalpricebefore"; // 确认之后的总价
    const LATITUDE = 'lat';
    const LONGITUDE = 'lng';
    const ISPICKUP = 'ispickup';
    const ISDELIVERY = 'isdelivery';
    const DISTANCE = 'distance';
    const COUNT = 10;
    const BAG_ID = 'bag_id';
    const BAG_AMOUNT = 'bag_amount';
    const CONFIRM_TIME = 'confirm_time';
    const USER_CONFIRM_TIME = 'user_confirm_time';
    const POSTAGE = 'postage';
}