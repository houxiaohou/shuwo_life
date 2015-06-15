<?php
namespace Api\Controller;

Class BagConst
{
    const ID = 'id';
    const USER_ID = 'user_id'; // 用户id
    const USED = 'used'; // 是否已使用，默认 - 0，使用后为1
    const AMOUNT = 'amount'; // 金额
    const TYPE = 'type'; // 使用限制类型，1 - 外送可用，2 - 自提可用
    const SHOP_ID = 'shop_id'; // 店铺id，默认为0，全部可用，如果设置为某个id，则仅限该店铺可用
    const EXPIRES = 'expires'; // 过期时间，超过该时间则无法使用
    const START = 'start';//起始时间
    const ISEVER =  "isever";
    const ISAOUT = "isauto";
}