<?php
return array(
					//'配置项'=>'配置值'
//     'TMPL_EXCEPTION_FILE'   => './Shuwo/Tpl/Public/error.html', // 定义公共错误模板
	'URL_PATHINFO_DEPR'     => '/',	// PATHINFO模式下，各参数之间的分割符号
	'TMPL_L_DELIM'			=> '{',//修改左定界符
	'TMPL_R_DELIM'			=> '}',//修改右定界符
    'URL_HTML_SUFFIX'=>'',
	'DB_TYPE'               => 'mysql',     // 数据库类型
	'DB_HOST'               => 'localhost', // 服务器地址
	'DB_NAME'               => 'shuwo',          // 数据库名
	'DB_USER'               => 'root',      // 用户名
	'DB_PWD'                => '',          // 密码
	'DB_PORT'               => '3306',        // 端口
	'DB_PREFIX'             => '',    // 数据库表前缀
	'CRYPT_KEY'             =>'1234567812345678',
	'SHOW_PAGE_TRACE'       => false,//开启页面TRACE
	'SHOW_ERROR_MSG'        => false,
	'SHUWO_APPID'           =>'wx17a029b44c383634',
	'SHUWO_APPSECRET'       =>'0f0d1caf3e84edbf61fc28fecf0c74b9',
	'SHUWO_CALLBACK'        =>'http://www.shuwow.com/Home/Index/authorize',
	'SHOP_APPID'            =>'wx17a8c83f5b2f6540',
	'SHOP_APPSECRET'        =>'432fb4827de4de9c2038ebcd2c5f064a',
     'SHOP_CALLBACK'        =>'http://www.shuwow.com/Home/Index/shopauthorize',
     'NEWORDER_TEMPID'      => 'XrQbgdZeQEiXhwg48Scghcv0iKERogNroEgZTsnNj2c',
	'ORDERSTATUS_TEMPID'   	=>  'HH96xFdaloMYuz0nM7dBfPWHgPfXeEWP4LdiQAeQEpM',
	'BDORDERSTATUS_TEMPID'   	=> 'ZJtevMl47WddbH3htejpeACRmJQL26hj67x3VA4wLpk',
  //'DEFAULT_THEME'         => '',	// 默认模板主题名称
  //'TMPL_DETECT_THEME'     => true,       // 自动侦测模板主题
  //'TMPL_TEMPLATE_SUFFIX'  => '.html',     // 默认模板文件后缀
  //'TMPL_FILE_DEPR'        =>  '/', //模板文件MODULE_NAME与ACTION_NAME之间的分割符
  //'THEME_LIST'            => '',//支持的模板文件的名称
    'TMPL_PARSE_STRING'     =>array(//添加自己的模板变量规则
        '__CSS__'             =>__ROOT__.'/public/css',
        '__JS__'              =>__ROOT__.'/public/js',
        '__IMAGES__'          =>__ROOT__.'/public/images',
    		),	
);