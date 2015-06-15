<?php
namespace Api\Controller;
use Think\Controller\RestController;
require_once 'AdminConst.php';
require_once 'Xcrypt.php';

class AdminApiController extends RestController {
    /*
//      * 返回所有管理员
//      */
//     public function getalladmin() {
//         $category =M("admin");
//         $data =$category->select();
//         if(!count($data)){
//             $data = [];
//         }
//         $this->response($data,"json");
//     }
//     /*
//      * 添加管理员
//      */
//     public function addadmin(){
//         $admin =M("admin");
//         if(I('post.name') != null )
//         {
//             $data[AdminConst::NAME]=I('post.name');
//         }
//         if(I('post.password') != null )
//         {
//             $data[AdminConst::PASSWORD]=md5(I('post.password'));
//         }
//         $admin->add($data);
//         $this->response($data,"json");
//     }
    /*
     * 更新管理员信息
     */
//     public function updateadmin(){
//         $admin =M("admin");
//         $id=intval(I('get.id',0));
//         if($id)
//         {
//             $data[AdminConst::ID]=$id;
//         }
//         if(I('post.name') != null )
//         {
//             $data[AdminConst::NAME]=I('post.name');
//         }
//         if(I('post.password') != null )
//         {
//             $data[AdminConst::PASSWORD]=I('post.password');
//         }
//         $data=$admin->save($data);
//         $this->response($data,"json");
//     }
    /*
     * 管理员登录认证
     */
public function adminlogin(){
    $key =C("CRYPT_KEY");
    $xcrpt = new Xcrypt($key, 'cbc', $key);
    $admin=M('admin');
    if(I('post.name') !=  NULL && I('post.password') != NULL)
    {
         $where[AdminConst::NAME]=I('post.name');
         $where[AdminConst::PASSWORD]=md5(I('post.password'));
         $data=$admin->where($where)->find();
         
        if (! count ( $data )) {
            $message ["msg"] = "Unauthorized";
            $this->response ( $message, 'json', '401' );
        }
        
        $name=$data[AdminConst::NAME];
        $password=$data[AdminConst::PASSWORD];
        
        $str=$name."#".$password;
        $token['utoken'] = $xcrpt->encrypt($str,'base64');
        $this->response($token,'json');
        }      
    }
}