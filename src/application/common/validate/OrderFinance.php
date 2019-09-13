<?php
namespace app\common\validate;

use think\Validate;

class OrderFinance extends Validate
{
    protected $rule = [
        'orderid'   => 'require|number',
        'payment_date'  => 'require|date',
        'payment_type' => 'require',
        'amount' => 'require|min:1',
        'cashier' => 'require',
        'refund_orderid'   => 'require|number',
        'refund_payment_date'  => 'require|date',
        'refund_payment_type' => 'require',
        'refund_amount' => 'require|min:1',
    ];

    protected $message  =   [
        'orderid.require' => '请选择收款合同',
        'orderid.number'     => '收款合同id只能是数字',
        'payment_date.require'  => '请选择收款日期',
        'payment_date.date'  => '收款日期格式有误',
        'payment_type.require'  => '请选择收款方式',
        'amount.require'    => '收款金额必须填写',
        'amount.min'        => '收款金额不能低于1元',
        'cashier.require'   => '请选择收款人',
        'refund_orderid.require' => '请选择退款合同',
        'refund_orderid.number'     => '退款合同id只能是数字',
        'refund_payment_date.require'  => '请选择退款日期',
        'refund_payment_date.date'  => '退款日期格式有误',
        'refund_payment_type.require'  => '请选择退款方式',
        'refund_amount.require'    => '退款金额必须填写',
        'refund_amount.min'        => '退款金额不能低于1元',
    ];

    protected $scene = [
        'add' => ['orderid', 'contract', 'payment_date', 'payment_type', 'amount'],
        'edit' => ['orderid', 'contract', 'payment_date', 'payment_type', 'amount', 'cashier'],
        'refund' => ['refund_orderid', 'refund_contract', 'refund_payment_date', 'refund_payment_type', 'refund_amount'],
        'editrefund' => ['refund_orderid', 'refund_contract', 'refund_payment_date', 'refund_payment_type', 'refund_amount']
    ];
}
