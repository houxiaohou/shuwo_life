<?php
return array(
    'URL_ROUTER_ON' => true, // 是否开启URL路由
    'URL_ROUTE_RULES' => array(
        //店铺URL配置
        array('allshops', 'ShopApi/getallshops', '', array('method' => 'GET')),
        array('shops', 'ShopApi/getshops', '', array('method' => 'GET')),
        array('usershop', 'ShopApi/usergetshops', '', array('method' => 'GET')),
        array('shop/:id\d/allproducts', 'ShopApi/admingetallproducts', '', array('method' => 'GET')),
        array('shop/allproducts', 'ShopApi/usergetallproducts', '', array('method' => 'GET')),
        array('shop/:id\d/products', 'ShopApi/getsaleproducts', '', array('method' => 'GET')),
        array('shop/:id\d', 'ShopApi/getshopbyid', '', array('method' => 'GET')),
        array('shop/:id\d/isopen', 'ShopApi/adminupdateshopisopen', '', array('method' => 'POST')),
        array('shop/isopen', 'ShopApi/userupdateshopisopen', '', array('method' => 'POST')),
        array('shop/:id\d', 'ShopApi/adminupdateshop', '', array('method' => 'POST')),
        array('usershop', 'ShopApi/userupdateshop', '', array('method' => 'POST')),
        array('shop', 'ShopApi/addshop', '', array('method' => 'POST')),
        array('shop/:id\d', 'ShopApi/deleteshop', '', array('method' => 'DELETE')),

        //请求上传图片七牛token
        array('uptoken', 'ImgApi/getuptoken', '', array('method' => 'GET')),

        //产品URL配置
        array('allproducts', 'ProductApi/getallproducts', '', array('method' => 'GET')),
        array('product/:id\d', 'ProductApi/getproductbyid', '', array('method' => 'GET')),
        array('product/:id\d/issale', 'ProductApi/updateproductissale', '', array('method' => 'POST')),
        array('product/:id\d', 'ProductApi/updateproduct', '', array('method' => 'POST')),
        array('product', 'ProductApi/addproduct', '', array('method' => 'POST')),
        array('product/:id\d', 'ProductApi/deleteproduct', '', array('method' => 'DELETE')),


        //产品类别URL配置
        array('allcategory', 'CategoryApi/getallcategorys', '', array('method' => 'GET')),//查询所有的种类
        array('category/:id\d', 'CategoryApi/getcategorybyid', '', array('method' => 'GET')),//根据GET传的id查询对应的水果种类信息
        array('category/:id\d', 'CategoryApi/updatecategory', '', array('method' => 'POST')),//更新水果类型，以（POST）传参
        array('category', 'CategoryApi/addcategory', '', array('method' => 'POST')),//添加水果类型，以（POST）传参
        array('category/:id\d', 'CategoryApi/deletecategory', '', array('method' => 'DELETE')),   //删除水果类型
    	array('categoryimg/:id\d','CategoryApi/deletecategoryimgbyurl', '', array('method' => 'DELETE')),   //删除水果种类图片

        //用户地址URL配置
        array('address/default', 'ShippingaddressApi/useraddress', '', array('method' => 'GET')),//获取用户的默认地址
        array('address/:id\d/default', 'ShippingaddressApi/updateisdefault', '', array('method' => 'POST')),//局部更新地址，以（POST）传参
        array('address/:id\d', 'ShippingaddressApi/getaddressbyid', '', array('method' => 'GET')),//根据GET传的id查询对应的地址信息
        array('address/:id\d', 'ShippingaddressApi/updateaddress', '', array('method' => 'POST')),//更新地址，以（POST）传参
        array('address', 'ShippingaddressApi/getalluseraddress', '', array('method' => 'GET')),//获取用户的全部地址
        array('address', 'ShippingaddressApi/addaddress', '', array('method' => 'POST')),//添加地址，以（POST）传参
        array('address/:id\d', 'ShippingaddressApi/deleteaddress', '', array('method' => 'DELETE')),   //删除地址

        //用户账号密码URL配置
        array('login', 'AdminApi/adminlogin', '', array('method' => 'POST')),//管理员登录认证
        // array('alladmin','AdminApi/getalladmin','',array('method'=>'GET')),//查询所有管理员
        // array('admin/:id','AdminApi/updateadmin', '', array('method' => 'POST')),//更新管理员信息
        // array('admin','AdminApi/addadmin', '', array('method' => 'POST')),//添加管理员

        //订单URL配置
        array('order/:id', 'OrderApi/getorderbyid', '', array('method' => 'GET')),//根据GET传的id查询对应的订单
        array('ordershop/:id', 'OrderApi/getorderbyshopid', '', array('method' => 'GET')),//根据GET传的shopid查询对应店铺的的订单
        array('user/orders', 'OrderApi/getordersbyuser', '', array('method' => 'GET')),//获取当前用户的订单
        array('shop/orders', 'OrderApi/getordersbyshop', '', array('method' => 'GET')),//获取当前店铺的订单
        array('order/:id', 'OrderApi/deleteorder', '', array('method' => 'DELETE')),   //删除订单
        array('orderconfirm', 'OrderApi/orderconfirm', '', array('method' => 'POST')),//用户确认订单
        array('ordercancel', 'OrderApi/cancelorder', '', array('method' => 'POST')),//撤销订单
        array('order', 'OrderApi/createorder', '', array('method' => 'POST')),//生成新订单
        array('weight', 'OrderApi/getweight', '', array('method' => 'POST')),//称重
        array('allorder', 'OrderApi/filterOrder', '', array('method' => 'GET')),//称重
        array('deliveryorder', 'OrderApi/deliveryorder', '', array('method' => 'POST')),//更改订单配送方式
        array('acancelorder', 'OrderApi/admincancelorder', '', array('method' => 'POST')),//更改订单配送方式

        //BD URL配置
        array('queryorders', 'BDApi/queryorders', '', array('method' => 'GET')),//得到所有的BD和店铺
        array('allbds', 'BDApi/getallbds', '', array('method' => 'GET')),//得到所有的BD和店铺
        array('bd/:id\d/shops', 'BDApi/getshopbybdid', '', array('method' => 'GET')),//根据bdid查询所属店铺信息
        array('bd/:id\d$', 'BDApi/deletebd', '', array('method' => 'DELETE')),//删除bd
        array('bd/:bdid\d/shop/:shopid\d$', 'BDApi/deletebdshop', '', array('method' => 'DELETE')),//删除bdshop
        array('bd/:bdid\d/shop/:shopid\d$', 'BDApi/addBdShop', '', array('method' => 'POST')),//添加bdshop

        //CATEGORYPIC URL配置
        array('categorypic/:id\d', 'CategorypicApi/updatecategorypic', '', array('method' => 'POST')),//更新
        array('categorypic/:id\d', 'CategorypicApi/getcategorypicbycategoryid', '', array('method' => 'GET')),//根据GET传的categoryid查询对应的信息
        array('categorypic/:id\d', 'CategorypicApi/deletecategorypic', '', array('method' => 'DELETE')),//删除
        array('categorypic', 'CategorypicApi/addcategorypic', '', array('method' => 'POST')),//添加

        //用户日数以及订单数期查询URL配置
        array('alluser', 'SurveyApi/allUserbydaytime', '', array('method' => 'GET')),//查询每日的用户数
        array('getuser', 'SurveyApi/getUserbymothtime', '', array('method' => 'POST')),//查询每月的用户量
        array('yearuser', 'SurveyApi/getUserbyyeartime', '', array('method' => 'POST')),//查询每年的用户量
        array('allorders', 'SurveyApi/allOrderbydaytime', '', array('method' => 'GET')),//查询每日的订单数
        array('getorders', 'SurveyApi/getOrderbymothtime', '', array('method' => 'POST')),//查询每月的订单量
        array('yearorders', 'SurveyApi/getOrderbyyeartime', '', array('method' => 'POST')),//查询每年的订单量

        //查询URL配置
        array('searchorderbyadmin', 'SearchApi/searchorderbyadmin', '', array('method' => 'POST')),//综合查询
        array('searchorderbyshop', 'SearchApi/searchorderbyshop', '', array('method' => 'POST')),//综合查询

        // 数据统计
        array('analytics/users', 'UserApi/allUsers', '', array('method' => 'GET')),
        array('analytics/user/orders', 'UserApi/userOrders', '', array('method' => 'GET')),
        array('user/block', 'UserApi/blockUser', '', array('method' => 'POST')),
        array('user/blocked', 'UserApi/blockedUsers', '', array('method' => 'GET')),
        array('user/search', 'UserApi/search', '', array('method' => 'POST')),
    		
    	// 红包
    	array('baglist', 'BagApi/listUserAvailableBags', '', array('method' => 'GET')),
    	array('expiredbags', 'BagApi/listUserExpiredBags', '', array('method' => 'GET')),
        array('usedbags', 'BagApi/listUserUsedBags', '', array('method' => 'GET')),
    	array('sendbags', 'BagApi/sendbagtouser', '', array('method' => 'POST')),
    	array('sendGroupBags', 'BagApi/groupSendBag', '', array('method' => 'POST')),
        array('bags/search$', 'BagApi/queryBagsByPhone', '', array('method' => 'POST')),
        array('bags/all$', 'BagApi/listAllBagsByAdmin', '', array('method' => 'GET')),
        array('finance/shops', 'FinanceApi/shopFinance', '', array('method' => 'GET')),
    	array('finance/jiuting', 'FinanceApi/jiutinguserFinance', '', array('method' => 'GET')),
        array('bag/bagUserCount', 'BagApi/listAllBagUsersByAdminByAvailableAndUsed', '', array('method' => 'GET')),
    )
);