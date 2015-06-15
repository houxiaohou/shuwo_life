<?php
namespace Api\Controller;
use Think\Controller\RestController;
require_once("qiniu/rs.php");
class ImgApiController extends RestController
{
	public  function getuptoken()
	{
		$bucket = 'shuwo';
		$accessKey = 'cPFlOFgcJIsgQVjhEH9AIPwjlQ8YI05aLHFWeHfD';
		$secretKey = 'FOGl8RpKFJUaYkJAJgXWbnsRGgO2JrUk44o4UuFo';
		$data=[];
	    \Qiniu_SetKeys($accessKey, $secretKey);
	    $putPolicy = new \Qiniu_RS_PutPolicy($bucket);
	    $upToken = $putPolicy->Token(null);
	    if($upToken!=null)
	    {
	    	$data["uptoken"] = $upToken;
	    }
	    $this->response($data,'json');
	}
}