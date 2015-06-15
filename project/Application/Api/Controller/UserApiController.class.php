<?php

namespace Api\Controller;

use Think\Controller\RestController;

require_once 'UserConst.php';
require_once 'OrderConst.php';
require_once 'Authorize.php';

class UserApiController extends RestController
{

    public function allUsers()
    {
        // 根据所有用户列出订单数量
        $authorize = new Authorize ();
        $isAdmin = $authorize->Filter("admin");
        if (!$isAdmin) {
            $message ["msg"] = "Unauthorized";
            $this->response($message, 'json', '401');
            return;
        }
        $start = intval(I('get.start', 0));
        $count = intval(I('get.count', 5));
        $days = intval(I('get.days', -1)); // $days天前开始
        if ($days == -1) {
            // 查询全部
            $sql = 'select userid, count(userid) as count from orders group by userid order by count desc limit ' . $start . ', ' . $count;
        } else {
            $sql = 'select userid, count(userid) as count from orders where to_days(createdtime) >= to_days(now()) - ' . $days . ' group by userid order by count desc limit ' . $start . ', ' . $count;
        }
        $dao = M();
        $users = [];
        $userInfo = $dao->query($sql);
        if ($userInfo) {
            for ($i = 0; $i < count($userInfo); $i++) {
                array_push($users, $this->orderUser($userInfo[$i]['userid']));
            }
        }
        $this->response($users, 'json');
    }

    public function blockedUsers()
    {
        // 查询黑名单用户
        $authorize = new Authorize ();
        $isAdmin = $authorize->Filter("admin");
        if (!$isAdmin) {
            $message ["msg"] = "Unauthorized";
            $this->response($message, 'json', '401');
            return;
        }
        $start = intval(I('get.start', 0));
        $count = intval(I('get.count', 5));
        $sql = 'select userid from user where block = 1 limit ' . $start . ', ' . $count;
        $dao = M();
        $data = $dao->query($sql);
        $users = [];
        for ($i = 0; $i < count($data); $i++) {
            array_push($users, $this->orderUser($data[$i]['userid']));
        }
        $this->response($users, 'json');
    }

    private function countUserOrderNum($userid)
    {
        $sql = 'select count(*) as count from orders where userid = ' . $userid;
        $dao = M();
        $data = $dao->query($sql);
        return $data[0]['count'];
    }

    public function userOrders()
    {
        // 根据某个用户的所有订单
        $authorize = new Authorize ();
        $isAdmin = $authorize->Filter("admin");
        if (!$isAdmin) {
            $message ["msg"] = "Unauthorized";
            $this->response($message, 'json', '401');
            return;
        }
        $userid = intval(I('get.userid'));
        $where [OrderConst::USERID] = $userid;
        $orderDao = M('orders');
        $orders = $orderDao->where($where)->order('-createdtime')->select();
        $userDao = M('user');
        $user = $userDao->where('userid=' . $userid)->find();
        $data['orders'] = $orders;
        $data['user'] = $user;
        $this->response($data, 'json');
    }

    public function blockUser()
    {
        // 加入用户到黑名单或解封
        $authorize = new Authorize ();
        $isAdmin = $authorize->Filter("admin");
        if (!$isAdmin) {
            $message ["msg"] = "Unauthorized";
            $this->response($message, 'json', '401');
            return;
        }
        $userid = intval(I('post.userid'));
        $block = intval(I('post.block'));

        $user = M('user');
        $data[UserConst::BLOCK] = $block;
        $user->where('userid=' . $userid)->save($data);
        $this->response($data, 'json');
    }

    public function search()
    {
        // 根据手机号码搜索用户
        $authorize = new Authorize ();
        $isAdmin = $authorize->Filter("admin");
        if (!$isAdmin) {
            $message ["msg"] = "Unauthorized";
            $this->response($message, 'json', '401');
            return;
        }
        $phone = I('post.phone');

        $orderDao = M('orders');
        $where['phone'] = $phone;

        $userIds = [];

        $orders = $orderDao->where($where)->select();
        for ($i = 0; $i < count($orders); $i++) {
            $order = $orders[$i];
            if (!in_array($order[OrderConst::USERID], $userIds)) {
                array_push($userIds, $order[OrderConst::USERID]);
            }
        }
        $users = [];

        for ($j = 0; $j < count($userIds); $j++) {
            array_push($users, $this->orderUser($userIds[$j]));
        }
        $this->response($users, 'json');
    }

    private function orderUser($userId)
    {
        $userDao = M('user');
        $user = $userDao->where('userid=' . $userId)->find();
        $d['order_num'] = $this->countUserOrderNum($userId);
        $d[UserConst::USERID] = $user[UserConst::USERID];
        $d[UserConst::CREATEDTIME] = $user[UserConst::CREATEDTIME];
        $d[UserConst::NICKNAME] = $user[UserConst::NICKNAME];
        $d[UserConst::HEADIMGURL] = $user[UserConst::HEADIMGURL];
        $d[UserConst::COUNTRY] = $user[UserConst::COUNTRY];
        $d[UserConst::PROVINCE] = $user[UserConst::PROVINCE];
        $d[UserConst::CITY] = $user[UserConst::CITY];
        $d[UserConst::BLOCK] = $user[UserConst::BLOCK];
        return $d;
    }

}