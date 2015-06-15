<?php

namespace Api\Controller;

use Think\Controller\RestController;

require_once 'BDConst.php';
require_once 'Authorize.php';

class BDApiController extends RestController
{

    // 返回所有bd
    public function getallbds()
    {
        $authorize = new Authorize ();
        $auid = $authorize->Filter('admin');
        if ($auid) {
            $bd = M("bd");
            $data = $bd->where(true)->order('bdid')->select();
            if (!count($data)) {
                $data = [];
            } else {
                for ($i = 0; $i < count($data); $i++) {
                    $bdshop = M("bdshop");
                    $shops = $bdshop->join('shop ON bdshop.shopid = shop.shopid')->where("bdid=" . $data[$i][BDConst::BDID])->field("bdshop.shopid,spn")->select();
                    if (count($shops)) {
                        $data[$i]['shops'] = $shops;
                    }
                }
            }

            $this->response($data, "json");
        } else {
            $message ["msg"] = "Unauthorized";
            $this->response($message, 'json', '401');
        }
    }

    public function deletebd()
    {
        $authorize = new Authorize ();
        $id = intval(I('get.id', 0));
        $auid = $authorize->Filter('admin');
        if ($auid) {
            if ($id) {
                $bd = M("bd");
                if ($bd->where('bdid=' . $id)->delete()) {
                    $bdshop = M('bdshop');
                    $where[BDConst::BDID] = $id;
                    $bdshop->where($where)->delete();
                }
            }
        } else {
            $message ["msg"] = "Unauthorized";
            $this->response($message, 'json', '401');
        }
    }

    public function deletebdshop()
    {
        $authorize = new Authorize ();
        $bdid = intval(I('get.bdid', 0));
        $shopid = intval(I('get.shopid', 0));
        $auid = $authorize->Filter('admin');
        if ($auid) {
            if ($bdid && $shopid) {
                $bdshop = M("bdshop");
                $bdshop->where("bdid=" . $bdid . ' And shopid=' . $shopid)->delete();
            }
        } else {
            $message ["msg"] = "Unauthorized";
            $this->response($message, 'json', '401');
        }
    }

    public function getshopbybdid()
    {
        $authorize = new Authorize ();
        $isAdmin = $authorize->Filter('admin');
        if (!$isAdmin) {
            $message ["msg"] = "Unauthorized";
            $this->response($message, 'json', '401');
            return;
        }
        $bdid = I('get.id');
        $bdShopDao = M('bdshop');
        $shopDao = M('shop');
        $bdDao = M('bd');
        $bdShops = $bdShopDao->where('bdid=' . $bdid)->select();
        $bd = $bdDao->where('bdid=' . $bdid)->find();
        $shops = [];
        for ($i = 0; $i < count($bdShops); $i++) {
            $s = $shopDao->where('shopid=' . $bdShops[$i]['shopid'])->find();
            array_push($shops, $s);
        }
        $data['bd'] = $bd;
        $data['shops'] = $shops;
        $this->response($data, 'json');
    }

    public function addBdShop()
    {
        $authorize = new Authorize ();
        $isAdmin = $authorize->Filter('admin');
        if (!$isAdmin) {
            $message ["msg"] = "Unauthorized";
            $this->response($message, 'json', '401');
            return;
        }
        $bdid = I('get.bdid');
        $shopid = I('get.shopid');

        $bdShopDao = M('bdshop');
        $where['bdid'] = $bdid;
        $where['shopid'] = $shopid;

        $data = $bdShopDao->where($where)->select();
        if (!count($data)) {
            $d['shopid'] = $shopid;
            $d['bdid'] = $bdid;
            $bdShopDao->add($d);
            $this->response($d, 'json');
        }
    }
}
