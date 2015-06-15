<?php
namespace Api\Controller;
require_once 'Xcrypt.php';
class Authorize
{
	public function Filter($type)
	{
		$header = [];
		$utoken= null;
		if(function_exists('getallheaders'))
		{
			$header = getallheaders();
		}
		else
		{
			$header = $this->getallheaders();
		}
		foreach ($header as $name => $value)
		{
			if ($name == "Authorization" || $name == "authorization")
			{
				$utoken = $value;
				break;
			}
		}
		if(!$utoken)
		{
// 			return false;
		}
		$key =C("CRYPT_KEY");
		$xcrpt = new Xcrypt($key, 'cbc', $key);
		
		//测试数据

        //$data = "40#15#shopopenid";
       //$data = "41#15";
        $data = $xcrpt->decrypt($utoken,'base64');
		if($data)
		{
			$str = explode("#", $data);
			if($str&&count($str)>=2)
			{
				$id = $str[0];
				$model = null;
				$sql ='';
				$info =[];
				if($id)
				{
                    $types = explode(",", $type);
                    foreach ($types as $item)
                    {
                    	if($item == 'user')
                    	{
                    		$model = M('user');
                    		$sql = "userid=".$id." AND roles=0";
                    		$info = $model->where($sql)->select();
                    	}
                    	if($item == 'shop')
                    	{
                    		$model = M('user');
                    		$sql = "userid=".$id." AND shopid =".$str[1]." AND openid='".trim($str[2])."' AND roles=1";
                    		$id = $str[1];
                    		$info = $model->where($sql)->select();
                        }
                        if($item =='admin')
                        {	
                             $model = M('admin');
                             $sql = "name='".$id."' AND password='".$str[1]."'";
                         	$info = $model->where($sql)->select();
                         	if($info)
                         	{
                         		return "admin";
                         	}
                        	
//                        	return "admin";
                    	}
                    	if(count($info))
                    	{
                    		break;
                    	}
                    }
					if(!count($info))
					{ 
						return  false;
					}
					return $id;
				}
				else
				{
					return  false;
				}
			}
			else
			{
				return  false;;
			}
		}
		else
		{
			return  false;
		}
	} 
	
	private function getallheaders()
	{
		$headers = '';
		foreach ($_SERVER as $name => $value)
		{
			if (substr($name, 0, 5) == 'HTTP_')
			{
				$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
			}
		}
		return $headers;
	}
}