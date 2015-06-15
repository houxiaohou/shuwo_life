<?php
namespace Api\Controller;

use Think\Controller\RestController;

require_once 'BagConst.php';
require_once 'Authorize.php';
require_once 'UserConst.php';
require_once 'OrderConst.php';
require_once 'Weixin.php';

class BagApiController extends RestController
{
    /**
     * admin列出所有红包
     */
    public function listAllBagsByAdmin()
    {
        $authorize = new Authorize ();
        $isAdmin = $authorize->Filter('admin');
        if (!$isAdmin) {
            $message ["msg"] = "Unauthorized";
            $this->response($message, 'json', '401');
            return;
        }

        $start = intval(I('get.start', 0));
        $count = intval(I('get.count', 10));
        // 查询全部
        $sql = 'select user_id, count(user_id) as count from bag group by user_id order by count desc limit ' . $start . ', ' . $count;
        $dao = M();
        $users = [];
        $userInfo = $dao->query($sql);
        if ($userInfo) {
            for ($i = 0; $i < count($userInfo); $i++) {
                array_push($users, $this->bagUser($userInfo[$i]['user_id']));
            }
        }
        $this->response($users, 'json');
    }

    /**
     * 根据已用数量和可用数量count用户
     */
    public function listAllBagUsersByAdminByAvailableAndUsed()
    {
        $authorize = new Authorize ();
        $isAdmin = $authorize->Filter('admin');
        if (!$isAdmin) {
            $message ["msg"] = "Unauthorized";
            $this->response($message, 'json', '401');
            return;
        }

        $start = intval(I('get.start', 0));
        $count = intval(I('get.count', 10));
        $used = intval(I('get.used'));
        $available = intval(I('get.available'));

        $users = [];

        if ($used == -1) {
            // 已使用不限
            if ($available == -1) {
                // 查询所有
                $countSql = 'select count(*) as count from user';
                $userIdSql = 'select userid as user_id from user order by userid desc limit ' . $start . ', ' . $count;

                $countResult = M()->query($countSql);
                $userIdResult = M()->query($userIdSql);
                for ($i = 0; $i < count($userIdResult); $i++) {
                    array_push($users, $this->bagUser($userIdResult[$i]['user_id']));
                }
                $total = $countResult[0]['count'];
                if (!$total) {
                    $total = 0;
                }
                $this->response(array('count' => $total, 'users' => $users), 'json');
                return;

            } else {
                // 查询可用数量为$available的
                if ($available == 0) {
                    // 可用数量为0
                    // 可用不为0
                    $notSql = 'select user_id from bag where (used = 0 and to_days(expires) >= to_days(now())) or (used = 0 and isever = 1) group by user_id having count(*) > 0';
                    $allUserSql = 'select userid as user_id from user order by userid desc';

                    $notResult = M()->query($notSql);
                    $allResult = M()->query($allUserSql);

                    $notArray = [];
                    for ($i = 0; $i < count($notResult); $i++) {
                        array_push($notArray, $notResult[$i]['user_id']);
                    }
                    $all = [];
                    for ($i = 0; $i < count($allResult); $i++) {
                        array_push($all, $allResult[$i]['user_id']);
                    }
                    $notArray = array_values(array_filter($notArray));
                    $all = array_values(array_filter($all));
                    $allUsers = array_diff($all, $notArray);
                    $allUsers = array_values(array_filter($allUsers));
                    $total = count($allUsers);
                    if (!$total) {
                        $total = 0;
                    }
                    if ($total >= $start + $count) {
                        for ($i = $start; $i < $start + $count; $i++) {
                            $userId = $allUsers[$start + $i];
                            array_push($users, $this->bagUser($userId));
                        }
                    } elseif ($total >= $start && $total < $start + $count) {
                        for ($i = $start; $i < $total; $i++) {
                            $userId = $allUsers[$start + $i];
                            array_push($users, $this->bagUser($userId));
                        }
                    } else {
                        $users = [];
                    }

                    $this->response(array('count' => $total, 'users' => $users), 'json');
                    return;

                }
                if ($available < 5) {
                    $countSql = 'select count(user_id) as count from bag where (used = 0 and to_days(expires) >= to_days(now())) or (used = 0 and isever = 1) group by user_id having count(*) = ' . $available . ';';
                    $userIdSql = 'select user_id from bag where (used = 0 and to_days(expires) >= to_days(now())) or (used = 0 and isever = 1) group by user_id having count(*) = ' . $available . ' order by user_id desc limit ' . $start . ', ' . $count;
                } else {
                    $countSql = 'select count(user_id) as count from bag where (used = 0 and to_days(expires) >= to_days(now())) or (used = 0 and isever = 1) group by user_id having count(*) >= ' . $available . ';';
                    $userIdSql = 'select user_id from bag where (used = 0 and to_days(expires) >= to_days(now())) or (used = 0 and isever = 1) group by user_id having count(*) >= ' . $available . ' order by user_id desc limit ' . $start . ', ' . $count;
                }

                $countResult = M()->query($countSql);
                $userIdResult = M()->query($userIdSql);
                for ($i = 0; $i < count($userIdResult); $i++) {
                    array_push($users, $this->bagUser($userIdResult[$i]['user_id']));
                }
                $total = count($countResult);
                if (!$total) {
                    $total = 0;
                }
                $this->response(array('count' => $total, 'users' => $users), 'json');
                return;
            }
        }
        // 已用数量为$used的用户id
        if ($available == -1 && $used == 0) {
            // 可用数量不限，已用为0（查询已用不为0的）
            $notSql = 'select user_id from bag where used = 1 group by user_id having count(*) > 0';
            $allUser = 'select userid as user_id from user order by userid desc';

            $notResult = M()->query($notSql);
            $allResult = M()->query($allUser);

            $notArray = [];
            for ($i = 0; $i < count($notResult); $i++) {
                array_push($notArray, $notResult[$i]['user_id']);
            }
            $notArray = array_values(array_filter($notArray));
            $all = [];
            for ($i = 0; $i < count($allResult); $i++) {
                array_push($all, $allResult[$i]['user_id']);
            }
            $all = array_values(array_filter($all));
            $allUsers = array_diff($all, $notArray);
            $allUsers = array_values(array_filter($allUsers));
            $total = count($allUsers);
            if (!$total) {
                $total = 0;
            }
            if ($total >= $start + $count) {
                for ($i = $start; $i < $start + $count; $i++) {
                    $userId = $allUsers[$start + $i];
                    array_push($users, $this->bagUser($userId));
                }
            } elseif ($total >= $start && $total < $start + $count) {
                for ($i = $start; $i < $total; $i++) {
                    $userId = $allUsers[$start + $i];
                    array_push($users, $this->bagUser($userId));
                }
            } else {
                $users = [];
            }

            $this->response(array('count' => $total, 'users' => $users), 'json');
            return;
        }
        if ($available == -1) {
            // 可用不限制，筛选已用数量

            if ($used < 5) {
                $countSql = 'select count(*) as count from bag where used = 1 group by user_id having count(*) = ' . $used;
                $userIdSql = 'select user_id from bag where used = 1 group by user_id having count(*) = ' . $used . ' order by user_id desc limit ' . $start . ', ' . $count;
            } else {
                $countSql = 'select count(*) as count from bag where used = 1 group by user_id having count(*) >= ' . $used;
                $userIdSql = 'select user_id from bag where used = 1 group by user_id having count(*) >= ' . $used . ' order by user_id desc limit ' . $start . ', ' . $count;
            }
            $countResult = M()->query($countSql);
            $userIdResult = M()->query($userIdSql);
            for ($i = 0; $i < count($userIdResult); $i++) {
                array_push($users, $this->bagUser($userIdResult[$i]['user_id']));
            }
            $total = count($countResult);
            if (!$total) {
                $total = 0;
            }
            $this->response(array('count' => $total, 'users' => $users), 'json');
            return;
        }
        if ($used == 0 && $available == 0) {
            // 已用为0，可用为0
            $userIds_u_not_0_raw = M()->query('select user_id from bag where used = 1 group by user_id having count(*) >  0');
            $userIds_all_raw = M()->query('select userid as user_id from user order by userid desc');

            $userIds_u_not_0 = [];
            for ($i = 0; $i < count($userIds_u_not_0_raw); $i++) {
                array_push($userIds_u_not_0, $userIds_u_not_0_raw[$i]['user_id']);
            }
            $userIds_all = [];
            for ($i = 0; $i < count($userIds_all_raw); $i++) {
                array_push($userIds_all, $userIds_all_raw[$i]['user_id']);
            }
            $userIds_u_not_0 = array_values(array_filter($userIds_u_not_0));
            $userIds_all = array_values(array_filter($userIds_all));
            $userIds_u = array_diff($userIds_all, $userIds_u_not_0);
            $userIds_u = array_values(array_filter($userIds_u));


            $userIds_a_not_0_raw = M()->query('select user_id from bag where (used = 0 and to_days(expires) >= to_days(now())) or (used = 0 and isever = 1) group by user_id having count(*) > 0');

            $userIds_a_not_0 = [];
            for ($i = 0; $i < count($userIds_a_not_0_raw); $i++) {
                array_push($userIds_a_not_0, $userIds_a_not_0_raw[$i]['user_id']);
            }

            $userIds_a_not_0 = array_values(array_filter($userIds_a_not_0));
            $userIds_a = array_diff($userIds_all, $userIds_a_not_0);

        } else if ($used == 0 && $available != 0) {
            // 已用为0，可用不为0
            // 先筛选已用不为0的用户id
            $userIds_u_not_0_raw = M()->query('select user_id from bag where used = 1 group by user_id having count(*) >  0');
            $userIds_all_raw = M()->query('select userid as user_id from user order by userid desc');

            $userIds_u_not_0 = [];
            for ($i = 0; $i < count($userIds_u_not_0_raw); $i++) {
                array_push($userIds_u_not_0, $userIds_u_not_0_raw[$i]['user_id']);
            }
            $userIds_all = [];
            for ($i = 0; $i < count($userIds_all_raw); $i++) {
                array_push($userIds_all, $userIds_all_raw[$i]['user_id']);
            }
            $userIds_u_not_0 = array_values(array_filter($userIds_u_not_0));
            $userIds_all = array_values(array_filter($userIds_all));
            $userIds_u = array_diff($userIds_all, $userIds_u_not_0);
            $userIds_u = array_values(array_filter($userIds_u));

            if ($available < 5) {
                $userIds_a_raw = M()->query('select user_id from bag where ((used = 0 and to_days(expires) >= to_days(now())) or (used = 0 and isever = 1)) group by user_id having count(*) = ' . $available . ' order by user_id desc');
            } else {
                $userIds_a_raw = M()->query('select user_id from bag where ((used = 0 and to_days(expires) >= to_days(now())) or (used = 0 and isever = 1)) group by user_id having count(*) >= ' . $available . ' order by user_id desc');
            }
            $userIds_a = [];
            for ($i = 0; $i < count($userIds_a_raw); $i++) {
                array_push($userIds_a, $userIds_a_raw[$i]['user_id']);
            }
        } else if ($used != 0 && $available == 0) {
            // 已用不为0，可用为0
            if ($used < 5) {
                $userIds_u_raw = M()->query('select user_id from bag where used = 1 group by user_id having count(*) = ' . $used . ' order by user_id desc');
            } else {
                $userIds_u_raw = M()->query('select user_id from bag where used = 1 group by user_id having count(*) >= ' . $used . ' order by user_id desc');
            }
            $userIds_u = [];
            for ($i = 0; $i < count($userIds_u_raw); $i++) {
                array_push($userIds_u, $userIds_u_raw[$i]['user_id']);
            }

            // 先查可用不为0的
            // 总用户 - 可用不为0的 = 可用为0
            $userIds_a_not_0_raw = M()->query('select user_id from bag where (used = 0 and to_days(expires) >= to_days(now())) or (used = 0 and isever = 1) group by user_id having count(*) > 0');
            $userIds_all_raw = M()->query('select userid as user_id from user order by userid desc');

            $userIds_a_not_0 = [];
            for ($i = 0; $i < count($userIds_a_not_0_raw); $i++) {
                array_push($userIds_a_not_0, $userIds_a_not_0_raw[$i]['user_id']);
            }

            $userIds_all = [];
            for ($i = 0; $i < count($userIds_all_raw); $i++) {
                array_push($userIds_all, $userIds_all_raw[$i]['user_id']);
            }

            $userIds_a_not_0 = array_values(array_filter($userIds_a_not_0));
            $userIds_all = array_values(array_filter($userIds_all));
            $userIds_a = array_diff($userIds_all, $userIds_a_not_0);

        } else {
            // 先查询已使用的数量为$used的用户id
            if ($used < 5) {
                $userIds_u_raw = M()->query('select user_id from bag where used = 1 group by user_id having count(*) = ' . $used . ' order by user_id desc');
            } else {
                $userIds_u_raw = M()->query('select user_id from bag where used = 1 group by user_id having count(*) >= ' . $used . ' order by user_id desc');
            }
            $userIds_u = [];
            for ($i = 0; $i < count($userIds_u_raw); $i++) {
                array_push($userIds_u, $userIds_u_raw[$i]['user_id']);
            }

            // 再查询未使用的数量为$available的用户id
            if ($available < 5) {
                $userIds_a_raw = M()->query('select user_id from bag where ((used = 0 and to_days(expires) >= to_days(now())) or (used = 0 and isever = 1)) group by user_id having count(*) = ' . $available . ' order by user_id desc');
            } else {
                $userIds_a_raw = M()->query('select user_id from bag where ((used = 0 and to_days(expires) >= to_days(now())) or (used = 0 and isever = 1)) group by user_id having count(*) >= ' . $available . ' order by user_id desc');
            }
            $userIds_a = [];
            for ($i = 0; $i < count($userIds_a_raw); $i++) {
                array_push($userIds_a, $userIds_a_raw[$i]['user_id']);
            }
        }

        $userIds_a = array_values(array_filter($userIds_a));
        $userIds_u = array_values(array_filter($userIds_u));

        $userIds = array_values(array_filter(array_intersect($userIds_a, $userIds_u)));
        $total = count($userIds);
        if ($total == 0) {
            $this->response(array('count' => 0, 'users' => []), 'json');
            return;
        }
        if ($total >= $start + $count) {
            for ($i = $start; $i < $start + $count; $i++) {
                $userId = $userIds[$start + $i];
                array_push($users, $this->bagUser($userId));
            }
        } elseif ($total >= $start && $total < $start + $count) {
            for ($i = $start; $i < $total; $i++) {
                $userId = $userIds[$start + $i];
                array_push($users, $this->bagUser($userId));
            }
        } else {
            $users = [];
        }
        if (!$total) {
            $total = 0;
        }
        $this->response(array('count' => $total, 'users' => $users), 'json');
        return;

    }

    /**
     * 用户id获取用户信息
     * @param $userId
     * @return mixed
     */
    private function bagUser($userId)
    {
        $userDao = M('user');
        $user = $userDao->where('userid=' . $userId)->find();
        $d['bag_num'] = $this->countUserBagNum($userId);
        $d['used_num'] = $this->countUserUsedBagNum($userId);
        $d['available_num'] = $this->countUserAvailableBagNum($userId);
        $d[UserConst::USERID] = $user[UserConst::USERID];
        $d[UserConst::NICKNAME] = $user[UserConst::NICKNAME];
        $d[UserConst::HEADIMGURL] = $user[UserConst::HEADIMGURL];
        return $d;
    }

    /**
     * 用户红包总数
     * @param $userId
     * @return mixed
     */
    private function countUserBagNum($userId)
    {
        $sql = 'select count(*) as count from bag where user_id = ' . $userId;
        $dao = M();
        $data = $dao->query($sql);
        return $data[0]['count'];
    }

    /**
     * 用户使用过的红包数
     * @param $userId
     * @return mixed
     */
    private function countUserUsedBagNum($userId)
    {
        $sql = 'select count(*) as count from bag where used = 1 and user_id = ' . $userId;
        $dao = M();
        $data = $dao->query($sql);
        return $data[0]['count'];
    }

    /**
     * 用户可用红包数
     * @param $userId
     * @return mixed
     */
    private function countUserAvailableBagNum($userId)
    {
        $sql = 'select count(*) as count from bag where ((used = 0 and to_days(expires) >= to_days(now())) or (used = 0 and isever = 1)) and user_id = ' . $userId;
        $dao = M();
        $data = $dao->query($sql);
        return $data[0]['count'];
    }

    /**
     * 根据条件列出用户所有可用红包
     */
    public function listUserAvailableBags()
    {
        $get = "get.";
        $authorize = new Authorize ();
        $userId = $authorize->Filter('user');
        if (!intval($userId)) {
            $message ["msg"] = "Unauthorized";
            $this->response($message, 'json', '401');
            return;
        }

        $currenttime = date('Y-m-d');
        $bagDao = M('bag');
        $type = intval(I($get . BagConst::TYPE, 0)); // 1 - 外送，2 - 自提

        if ($type != 0) {
            // 区分类型，筛选可用红包
            $data = $bagDao->where("((date(expires) >='" . $currenttime . "' and date(start)<='" . $currenttime . "' and used=0) or (isever =1 and used = 0)) and user_id = " . $userId . " and type = " . $type)->order('expires')->select();
        } else {
            // 不区分类型
            $data = $bagDao->where("((date(expires) >='" . $currenttime . "' and used=0) or (isever =1 and used = 0)) and user_id = " . $userId)->order('expires')->select();
        }
        if (!count($data)) {
            $data = [];
        } else {
            for ($i = 0; $i < count($data); $i++) {
                $data[$i]['expired'] = 0;
            }
        }
        $this->response($data, 'json');

    }

    /**
     * 根据手机号码获取用户红包列表
     */
    public function queryBagsByPhone()
    {
        $authorize = new Authorize ();
        $isAdmin = $authorize->Filter('admin');
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
        $results = [];
        for ($j = 0; $j < count($userIds); $j++) {
            array_push($results, $this->bagUser($userIds[$j]));
        }
        $this->response($results, 'json');
    }

    /**
     * 列出用户过期的红包
     */
    public function listUserExpiredBags()
    {
        $get = "get.";
        $authorize = new Authorize ();
        $userId = $authorize->Filter("user");
        if (!intval($userId)) {
            $message ["msg"] = "Unauthorized";
            $this->response($message, 'json', '401');
            return;
        }
        $currenttime = date('Y-m-d');
        $bagDao = M('bag');
        $data = $bagDao->where("date(expires) <'" . $currenttime . "' and user_id =" . $userId)->order('expires desc')->select();
        if (!count($data)) {
            $data = [];
        }
        $this->response($data, 'json');
    }

    /**
     * 获取红包信息
     * @param $bag
     * @return mixed
     */
    private function bagInfo($bag)
    {
        $userDao = M('user');
        $user = $userDao->where('userid=' . $bag['user_id'])->find();
        $d[BagConst::USER_ID] = $user[UserConst::USERID];
        $d[UserConst::NICKNAME] = $user[UserConst::NICKNAME];
        $d[UserConst::HEADIMGURL] = $user[UserConst::HEADIMGURL];
        $d[UserConst::BLOCK] = $user[UserConst::BLOCK];

        $d[BagConst::ID] = $bag[BagConst::ID];
        $d[BagConst::USED] = $bag[BagConst::USED];
        $d[BagConst::AMOUNT] = $bag[BagConst::AMOUNT];
        $d[BagConst::EXPIRES] = $bag[BagConst::EXPIRES];
        $d[BagConst::TYPE] = $bag[BagConst::TYPE];
        $d[BagConst::START] = $bag[BagConst::START];

        return $d;
    }

    /**
     * 根据使用情况列出用户使用过的红包
     */
    public function listUserUsedBags()
    {
        $authorize = new Authorize ();
        $userId = $authorize->Filter("user");
        if (!intval($userId)) {
            $message ["msg"] = "Unauthorized";
            $this->response($message, 'json', '401');
            return;
        }
        $currenttime = date('Y-m-d');
        $bagDao = M('bag');
        $data = $bagDao->where("(date(expires) <'" . $currenttime . "' or used = 1) and user_id =" . $userId)->order('expires desc')->select();
        if (!count($data)) {
            $data = [];
        } else {
            for ($i = 0; $i < count($data); $i++) {
                if ($data[$i]['expires'] < $currenttime) {
                    // 过期的
                    $data[$i]['expired'] = 1;
                } else {
                    $data[$i]['expired'] = 0;
                }
            }
        }
        $this->response($data, 'json');
    }

    /**
     * 群发红包给用户
     */
    public function groupSendBag()
    {
        $authorize = new Authorize ();
        $isAdmin = $authorize->Filter('admin');
        if (!$isAdmin) {
            $message ["msg"] = "Unauthorized";
            $this->response($message, 'json', '401');
            return;
        }

        $used = intval(I('post.used'));
        $available = intval(I('post.available'));
        $amount = intval(I('post.amount'));

        if ($available != 0) {
            $this->response(array('success' => 0, 'error' => '可用数量必须为0'), 'json');
            return;
        }
        if ($used == -1) {
            $this->response(array('success' => 0, 'error' => '已使用数量不能为不限'), 'json');
            return;
        }

        if ($used == 0 && $available == 0) {
            // 已用为0，可用为0
            $userIds_u_not_0_raw = M()->query('select user_id from bag where used = 1 group by user_id having count(*) >  0');
            $userIds_all_raw = M()->query('select userid as user_id from user order by userid desc');

            $userIds_u_not_0 = [];
            for ($i = 0; $i < count($userIds_u_not_0_raw); $i++) {
                array_push($userIds_u_not_0, $userIds_u_not_0_raw[$i]['user_id']);
            }
            $userIds_all = [];
            for ($i = 0; $i < count($userIds_all_raw); $i++) {
                array_push($userIds_all, $userIds_all_raw[$i]['user_id']);
            }
            $userIds_u_not_0 = array_values(array_filter($userIds_u_not_0));
            $userIds_all = array_values(array_filter($userIds_all));
            $userIds_u = array_diff($userIds_all, $userIds_u_not_0);
            $userIds_u = array_values(array_filter($userIds_u));


            $userIds_a_not_0_raw = M()->query('select user_id from bag where (used = 0 and to_days(expires) >= to_days(now())) or (used = 0 and isever = 1) group by user_id having count(*) > 0');

            $userIds_a_not_0 = [];
            for ($i = 0; $i < count($userIds_a_not_0_raw); $i++) {
                array_push($userIds_a_not_0, $userIds_a_not_0_raw[$i]['user_id']);
            }

            $userIds_a_not_0 = array_values(array_filter($userIds_a_not_0));
            $userIds_a = array_diff($userIds_all, $userIds_a_not_0);

        } else if ($used != 0 && $available == 0) {
            // 已用不为0，可用为0
            if ($used < 5) {
                $userIds_u_raw = M()->query('select user_id from bag where used = 1 group by user_id having count(*) = ' . $used);
            } else {
                $userIds_u_raw = M()->query('select user_id from bag where used = 1 group by user_id having count(*) >= ' . $used);
            }
            $userIds_u = [];
            for ($i = 0; $i < count($userIds_u_raw); $i++) {
                array_push($userIds_u, $userIds_u_raw[$i]['user_id']);
            }

            // 先查可用不为0的
            // 总用户 - 可用不为0的 = 可用为0
            $userIds_a_not_0_raw = M()->query('select user_id from bag where (used = 0 and to_days(expires) >= to_days(now())) or (used = 0 and isever = 1) group by user_id having count(*) > 0');
            $userIds_all_raw = M()->query('select userid as user_id from user');

            $userIds_a_not_0 = [];
            for ($i = 0; $i < count($userIds_a_not_0_raw); $i++) {
                array_push($userIds_a_not_0, $userIds_a_not_0_raw[$i]['user_id']);
            }

            $userIds_all = [];
            for ($i = 0; $i < count($userIds_all_raw); $i++) {
                array_push($userIds_all, $userIds_all_raw[$i]['user_id']);
            }

            $userIds_a_not_0 = array_values(array_filter($userIds_a_not_0));
            $userIds_all = array_values(array_filter($userIds_all));
            $userIds_a = array_diff($userIds_all, $userIds_a_not_0);

        } else {
            // 先查询已使用的数量为$used的用户id
            if ($used < 5) {
                $userIds_u_raw = M()->query('select user_id from bag where used = 1 group by user_id having count(*) = ' . $used);
            } else {
                $userIds_u_raw = M()->query('select user_id from bag where used = 1 group by user_id having count(*) >= ' . $used);
            }
            $userIds_u = [];
            for ($i = 0; $i < count($userIds_u_raw); $i++) {
                array_push($userIds_u, $userIds_u_raw[$i]['user_id']);
            }

            // 再查询未使用的数量为$available的用户id
            if ($available < 5) {
                $userIds_a_raw = M()->query('select user_id from bag where ((used = 0 and to_days(expires) >= to_days(now())) or (used = 0 and isever = 1)) group by user_id having count(*) = ' . $available . ' order by user_id desc');
            } else {
                $userIds_a_raw = M()->query('select user_id from bag where ((used = 0 and to_days(expires) >= to_days(now())) or (used = 0 and isever = 1)) group by user_id having count(*) >= ' . $available . ' order by user_id desc');
            }
            $userIds_a = [];
            for ($i = 0; $i < count($userIds_a_raw); $i++) {
                array_push($userIds_a, $userIds_a_raw[$i]['user_id']);
            }
        }

        $userIds_a = array_values(array_filter($userIds_a));
        $userIds_u = array_values(array_filter($userIds_u));

        $userIds = array_values(array_filter(array_intersect($userIds_a, $userIds_u)));

        for ($i = 0; $i < count($userIds); $i++) {
            $userId = $userIds[$i];
            $this->sendBagToSingleUser($userId, $amount);
        }
        return;
    }

    /**
     * 给用户发红包
     */
    public function sendbagtouser()
    {
        $post = "post.";
        $authorize = new Authorize ();
        $auid = $authorize->Filter("admin");
        if ($auid) {
            $userid = I("post.userids");
            $amount = I("post.amount");
            $userids = explode(".", $userid);
            for ($i = 0; $i < count($userids); $i++) {
                $userId = $userids[$i];
                $this->sendBagToSingleUser($userId, $amount);
            }
            $this->response("success", 'json');
        } else {
            $message ["msg"] = "Unauthorized";
            $this->response($message, 'json', '401');
            return;
        }
    }

    private function sendBagToSingleUser($userId, $amount)
    {
        $bag = M("bag");
        $current = date('Y-m-d');
        $expirdate = date('Y-m-d', strtotime('+6 days'));
        $expirdate = $expirdate . " 23:59:59";
        $bagitem [BagConst::START] = $current;
        $bagitem [BagConst::SHOP_ID] = 0;
        $bagitem [BagConst::TYPE] = 1;
        $bagitem [BagConst::EXPIRES] = $expirdate;
        $bagitem [BagConst::USED] = 0;
        $bagitem [BagConst::AMOUNT] = $amount;
        $bagitem [BagConst::USER_ID] = $userId;
        $bagitem [BagConst::ISEVER] = 0;
        $bagitem [BagConst::ISAOUT] = 0;
        $bagid = $bag->add($bagitem);
        if (intval($bagid)) {
            $user = M('user');
            $userinfo = $user->where("userid=" . $userId)->find();
            $baginfo = $bag->where("id=" . $bagid)->find();
            if (count($userinfo) && count($userinfo) && !empty($userinfo['openid'])) {
                $start = date('Y-m-d', strtotime($baginfo[BagConst::START]));
                $expire = date('Y-m-d', strtotime($baginfo[BagConst::EXPIRES]));
                $content = '恭喜您获得' . $baginfo[BagConst::AMOUNT] . '元红包，可使用日期' . $start . '至' . $expire;

                $template = array(
                    'touser' => $userinfo['openid'],
                    'template_id' => 'NjDObh6wXHfh4scgh29gxtmao5dYu-dtGEvR2sDk_-8',
                    'url' => "http://www.shuwow.com/Home/Index/index/#/bag",
                    'data' => array(
                        'first' => array(
                            'value' => urlencode($content),
                            'color' => "#FF0000"
                        ),
                        'orderTicketStore' => array(
                            'value' => urlencode("树窝水果商城购买水果"),
                            'color' => "#009900"
                        ),
                        'orderTicketRule' => array(
                            'value' => urlencode("外送订单即可使用红包"),
                            'color' => "#009900"
                        ),
                        'remark' => array(
                            'value' => urlencode("\\n信息来自树窝小店"),
                            'color' => "#cccccc"
                        )
                    )
                );
                $weixin = new Weixin ();
                $token = $weixin->getusersGlobalAccessToken();
                $weixin->sendtemplatemsg(urldecode(json_encode($template)), $token);
            }
        }

    }

}