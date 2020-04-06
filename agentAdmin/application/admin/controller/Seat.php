<?php
namespace app\admin\controller;

use app\admin\model\api\ApiModel;
use app\admin\model\api\Bar;
use app\admin\model\api\BarNotice;
use app\admin\model\api\BarWaterLog;
use app\admin\model\api\Kv;
use app\admin\model\api\OrderGoods;
use app\admin\model\api\OrderGoodsDetail;
use app\admin\model\api\OrderSeat;
use app\admin\model\api\User;
use app\admin\model\api\UserRefundCount;
use app\admin\model\api\UserWaterLog;
use app\common\controller\Backend;
use fast\Http;
use think\Config;
use think\Db;
use think\Exception;
use think\Session;

class Seat extends Backend {

    /**
     * 查看
     */
    public function index() {
        $orderDate = $this->request->param("orderDate");
        if (empty($orderDate)) {
            $orderDate = date("Y-m-d");
        }
        $barId  = Session::get("admin.id");
        $notice = BarNotice::where("bar_id", ":barId")->bind(["barId" => $barId])->limit(30)->order("id desc")->select()->toArray();
        $url    = "http://api.yingqubar.com/H5/field";
        $row    = Http::sendRequest($url, [
            "barId"     => $barId,
            "orderDate" => $orderDate
        ]);
        if (!$row['ret']) {
            $this->error("获取数据失败");
        }
        $this->assign("row", $row['msg']);
        $this->assign("notice", $notice);
        return $this->view->fetch();
    }

    public function handle() {
        $barId   = $this->request->param("barId");
        $orderId = $this->request->param("orderId");
        $status  = $this->request->param("status");
        switch ($status) {
            case -1://取消预约
                list($code, $msg) = $this->cancel($orderId, $barId);
                if (!$code) {
                    $this->error($msg);
                }
                break;
            case -2://确认离坐
                $this->earnings($orderId, $barId);
                break;
            case 2://入座
                $row = OrderSeat::get($orderId);
                if (empty($row)) {
                    $this->error("订单不存在");
                }
                if ($barId != $row->bar_id) {
                    $this->error("没有权限操作");
                }
                $row->order_status = $status;
                if (!$row->save()) {
                    $this->error("操作失败");
                }
                break;
        }
        $this->success("操作成功");
    }

    private function cancel($orderId, $barId) {
        $msg  = '';
        $data = OrderSeat::get($orderId)->toArray();
        if (empty($data)) {
            return [
                false,
                '不存在这个酒吧座位订单'
            ];
        }
        $uid = $data['uid'];
        if ($barId != $data['bar_id']) {
            return [
                false,
                '不允许该操作'
            ];
        }
        $order = OrderGoods::where("seat_order_id", ":orderId")->where("order_status = 1")->bind(["orderId" => $data['id']])->select();
        $order = $order->toArray();
        $money = 0;
        foreach ($order as $key => &$val) {
            $money += $val['over_pay'];
        }
        $drinks['type']   = $data['pay_type'];
        $drinks['money']  = $money;
        $drinks['mold']   = 'drinks';
        $order_r['money'] = $data['minimum_price'];
        $order_r['type']  = empty($order['over_pay_type']) ? 'local' : $order['over_pay_type'];
        $order_r['mold']  = 'order';
        $refund           = [
            $drinks,
            $order_r
        ];
        $push             = [
            "type"  => $data['pay_type'],
            "barId" => $data['bar_id'],
            "money" => bcadd($drinks['money'], $order_r['money'], 2),
        ];
        $userLastMoney    = 0;
        $totalMoney       = 0;
        OrderSeat::startTrans();
        $user = User::get($uid);
        foreach ($refund as $val) {
            switch ($val['type']) {
                case 'alipay':
                    $this->localRefund($uid, $orderId, $val, $userLastMoney, $user->wallet);
                    //$this->aliRefund($connection, $uid, $orderId, $val);
                    break;
                case 'wechat':
                    $this->localRefund($uid, $orderId, $val, $userLastMoney, $user->wallet);
                    //$this->wxRefund($connection, $uid, $orderId, $val);
                    break;
                default:
                    $this->localRefund($uid, $orderId, $val, $userLastMoney, $user->wallet);
            }
            $totalMoney += $val['money'];
            $userLastMoney += $val['money'];
        }
        $user->wallet = bcadd($totalMoney, $user->wallet, 2);
        if (!$user->save()) {
            OrderSeat::rollback();
            $this->error("用户金额修改失败");
        }
        $UserRefundCountModel               = new UserRefundCount();
        $UserRefundCountModel->order_id     = $orderId;
        $UserRefundCountModel->uid          = $uid;
        $UserRefundCountModel->bar_id       = $barId;
        $UserRefundCountModel->c_time       = time();
        $UserRefundCountModel->refund_money = $totalMoney;
        $UserRefundCountModel->fee_money    = 0;
        if (!$UserRefundCountModel->save()) {
            OrderSeat::rollback();
            $this->error("酒吧退款统计出错");
        }
        OrderSeat::commit();
        Http::sendRequest("http://api.yingqubar.com/H5/sendMsg", [
            "barId"   => $barId,
            "orderId" => $orderId,
            "type"    => $push['type'],
            "money"   => $push['money'],
            "uid"     => $uid
        ]);
        return [
            true,
            $msg
        ];
    }

    /**
     * @param $uid
     * @param $orderId
     * @param $val
     * 本地退款
     */
    private function localRefund($uid, $orderId, $val, $user_last_money = 0, $wallet) {
        try {
            $type         = 2;
            $refund_money = $val['money'];
            if (!$this->addUserWaterLog($uid, $type, $refund_money, $user_last_money, $val['mold'], $orderId, $wallet)) {
                OrderSeat::rollback();
                $this->error("添加流水失败");
            }
            /*加钱*/
            if ($val['money'] > 0) {
                /*再修改订单*/
                if ($val['mold'] == 'drinks') {
                    $sql   = "update order_goods set order_status = '-1' where seat_order_id = :id  AND order_status = '1'  ";
                    $rows  = OrderGoods::execute($sql, [
                        'id' => $orderId,
                    ]);
                    $debug = 'order_goods';
                } elseif ($val['mold'] == 'order') {
                    $sql   = "update order_seat set order_status = '-1' where id = :id AND order_status = '1' ";
                    $rows  = OrderSeat::execute($sql, [
                        'id' => $orderId,
                    ]);
                    $debug = 'order_seat';
                }
                if (!$rows) {
                    OrderSeat::rollback();
                    $this->error($debug . "操作失败");
                }
            }
        } catch (\Exception $e) {
            OrderSeat::rollback();
            $this->error($e->getMessage());
            exit;
        }
    }

    private function addUserWaterLog($uid, $type, $money, $user_last_money = 0, $mold, $orderId, $wallet) {
        if (empty($money)) {
            return true;
        }
        $user_money        = bcadd($wallet, $user_last_money, 2);
        $log               = new UserWaterLog();
        $log->uid          = $uid;
        $log->type         = $type;
        $log->mold         = $mold;
        $log->order_id     = $orderId;
        $log->add_time     = time();
        $log->money        = $money;
        $log->last_money   = $user_money;
        $log->change_money = bcadd($user_money, $money, 2);
        if ($log->save()) {
            return true;
        }
        return false;
    }

    private function earnings($orderId, $barId) {
        try {
            ApiModel::startTrans();
            $order       = OrderSeat::get($orderId);
            // 生成座位订单的时候 没有给 total_price 加上消费
            $total_price = abs(bcsub($order->minimum_price,$order->total_price));
            // 订单设置为离坐 酒水通通设为已领取
            $order->order_status = -2;
            if (!$order->save()) {
                ApiModel::rollback();
                $this->error("订单状态操作失败");
            };

            $bar = Bar::get($barId);
            // 酒吧流水 酒吧金额增加
            $last_money   = $bar->bar_money;
            $change_money = bcadd($last_money, $total_price, 3);
            $bar->bar_money = bcadd($bar->bar_money,$total_price);
            if (!$bar->save()) {
                ApiModel::rollback();
                $this->error("酒吧可提现金额增加失败");
            }

            // 如果更新成功 代表有酒水订单
            if (OrderGoods::where("seat_order_id", ":id")->bind(["id" => $orderId])->update(["order_status" => 2])) {
                $orderGoods = OrderGoods::where("seat_order_id", ":id")->bind(["id" => $orderId])->find();
                OrderGoodsDetail::where("goods_order_id", ":goods_id")->bind(["goods_id" => $orderGoods->id])->update(["status" => 1]);
            }

            $time = time();
            $sql  = "INSERT INTO bar_water_log (bar_id,add_time,money,last_money,change_money,order_id) VALUES (:bar_id,:add_time,:money,:last_money,:change_money,:order_id)";
            $row  = BarWaterLog::execute($sql, [
                "bar_id"       => $barId,
                "add_time"     => $time,
                "money"        => $total_price,
                "last_money"   => $last_money,
                "change_money" => $change_money,
                "order_id"     => $orderId
            ]);
            if (!$row) {
                ApiModel::rollback();
                $this->error("酒吧流水添加失败");
            }
            // 运营流水
            $run_money = bcmul($bar->profit_rate, $total_price, 3);
            $sql       = "INSERT INTO run_income_log (order_id,add_time,money,bar_id) VALUES (:order_id,:add_time,:money,:bar_id)";
            $row       = ApiModel::execute($sql, [
                "order_id" => $orderId,
                "add_time" => $time,
                "money"    => $run_money,
                "bar_id"   => $barId
            ]);
            if (!$row) {
                ApiModel::rollback();
                $this->error("运营流水添加失败");
            }
            ApiModel::commit();
        } catch (Exception $e) {
            ApiModel::rollback();
            $this->error("服务器出错" . $e->getMessage());
        }
    }
}
