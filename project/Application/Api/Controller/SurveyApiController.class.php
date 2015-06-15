<?php

namespace Api\Controller;

use Think\Controller\RestController;
use Think\Model;

require_once 'UserConst.php';
require_once 'Authorize.php';
class SurveyApiController  extends RestController {
    /*
     * 查询每日的用户数
     */
    public function allUserbydaytime(){
        $new = new Authorize();
        $id = $new->Filter('admin');
        if($id){
        $user  = M('user');
        $starttime = date("Y-m-d");
        $endtime  = date("Y-m-d",strtotime("-31 day"));
        $sql = "date(createdtime) >= '{$endtime}' and date(createdtime) <= '{$starttime}'";
        $data = $user->where($sql)->select(); 
        if(!$data){
            $array[] = '';
        }
        $count = count($data);
        $array['今日到前31天的用户数为'] = $count;
        $this->response($array,'json');
        }else{
            $message ["msg"] = "Unauthorized";
            $this->response ( $message, 'json', '401' );
        }
    }
    /*
     * 查询每月的用户量
     */
    public function getUserbymothtime(){
        $new = new Authorize();
        $id = $new->Filter('admin');
        if($id){
        $user  = M('user');
        $year = I('post.year');
        $month  = I('post.month');
        $data = $user->where("year(createdtime) = '{$year}' and month(createdtime) = '{$month}'")->select(); 
        if(!$data){
            $array[] = '';
        }
        $count = count($data);
        $array['本月用户量为'] = $count;
        $this->response($array,'json');
        }else{
            $message ["msg"] = "Unauthorized";
            $this->response ( $message, 'json', '401' );
        }
    }
    /*
     * 查询每年的用户量
     */
    public function getUserbyyeartime(){      
        $new = new Authorize();
        $id = $new->Filter('admin');
        if($id){
        $user  = M('user');
        $year = I('post.year');
        $data = $user->where("year(createdtime) = '{$year}'")->select(); 
        if(!$data){
         $array[] = '';
        }
        $count = count($data);
        $array['今年用户量为'] = $count;
        $this->response($array,'json');
            }else{
                $message ["msg"] = "Unauthorized";
                $this->response ( $message, 'json', '401' );
            }
    }
    /*
     * 每日订单查询
     */
    public function allOrderbydaytime(){
        $new = new Authorize();
        $id = $new->Filter('admin');
        if($id){
        $user  = M('orders');
        $starttime = date("Y-m-d");
        $endtime  = date("Y-m-d",strtotime("-31 day"));
        $sql = "date(createdtime) >= '{$endtime}' and date(createdtime) <= '{$starttime}'";
        $data = $user->where($sql)->select();
        if(!$data){
        $array[] = '';
        }
        $count = count($data);      
        $array['今日到前31天的用户数为'] = $count;
        $this->response($array,'json');            
        }else{
            $message ["msg"] = "Unauthorized";
            $this->response ( $message, 'json', '401' );
        }
    }
    /*
     * 查询每月的订单量
     */
    public function getOrderbymothtime(){
        $new = new Authorize();
        $id = $new->Filter('admin');
        if($id){
        $user  = M('orders');
        $year = I('post.year');
        $month  = I('post.month');
        $data = $user->where("year(createdtime) = '{$year}' and month(createdtime) = '{$month}'")->select();
        if(!$data){
            $array[] = '';
        }
        $count = count($data);
        $array['本月用户量为'] = $count;
        $this->response($array,'json');
        }else{
            $message ["msg"] = "Unauthorized";
            $this->response ( $message, 'json', '401' );
        }
    }
    public function getOrderbyyeartime(){
        $new = new Authorize();
        $id = $new->Filter('admin');
        if($id){
        $user  = M('orders');
        $year = I('post.year');
        $data = $user->where("year(createdtime) = '{$year}'")->select();
        if(!$data){
            $array[] = '';
        }
        $count = count($data);
        $array['今年用户量为'] = $count;
        $this->response($array,'json');
        }else{
            $message ["msg"] = "Unauthorized";
            $this->response ( $message, 'json', '401' );
        }
    } 
}