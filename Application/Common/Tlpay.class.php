<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/22
 * Time: 14:01
 */
namespace Common;
class Tlpay
{
    /**
     * 校验签名
     * @param array 参数
     * @param unknown_type appkey
     */
    public static function ValidSign(array $array,$appkey){
        $sign = $array['sign'];
        unset($array['sign']);
        $array['key'] = $appkey;
        $mySign = self::SignArray($array, $appkey);
        return $sign == $mySign;
    }

    /**
     * 将参数数组签名
     */
    public static function SignArray(array $array,$appkey){
        $array['key'] = $appkey;// 将key放到数组中一起进行排序和组装
        ksort($array);
        $blankStr = self::ToUrlParams($array);
        $sign = md5($blankStr);
        return $sign;
    }

    public static function ToUrlParams(array $array)
    {
        $buff = "";
        foreach ($array as $k => $v)
        {
            if($k != "sign" && $v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        return $buff;
    }
    /**
     * 交易结果码
     * @var array
     */
    // 发送Http状态信息
    protected function getResponseStatus($code) {
        static $_status = array(
            "0000"      => "处理成功",
            "1000"      => "报文内容检查错或者处理错",
            "1001"      => "报文解释错",
            "1002"      => "冲正时无此交易",
            "1999"      => "本批交易已经全部失败",
            "0001"      => "系统处理失败",
            "0002"      => "已撤销",
            "1000"      => "报文内容检查错或者处理错",
            "1001"      => "报文解释错",
            "1002"      => "无此交易",
            "2000"      => "系统正在对数据处理",
            "2001"      => "等待商户审核",
            "2002"      => "商户审核不通过",
            "2003"      => "等待 受理",
            "2004"      => "不通过受理",
            "2005"      => "等待 复核",
            "2006"      => "不通过复核",
            "2007"      => "提交银行处理",
            "2008"      => "实时交易超时",
            "4000"      => "跨行交易已发送银行",
            "0397"      => "不支持该银行的交易",
            "3001"      => "查开户方原因",
            "3002"      => "没收卡",
            "3003"      => "不予承兑",
            "3004"      => "无效卡号",
            "3005"      => "受卡方与安全保密部门联系",
            "3006"      => "已挂失卡",
            "3007"      => "被窃卡",
            "3008"      => "余额不足",
            "3009"      => "无此账户",
            "3010"      => "过期卡",
            "3011"      => "密码错",
            "3012"      => "不允许持卡人进行的交易",
            "3013"      => "超出提款限额",
            "3014"      => "原始金额不正确",
            "3015"      => "超出取款次数限制",
            "3016"      => "已挂失折",
            "3017"      => "账户已冻结",
            "3018"      => "已清户",
            "3019"      => "原交易已被取消或冲正",
            "3020"      => "账户被临时锁定",
            "3021"      => "未登折行数超限",
            "3022"      => "存折号码有误",
            "3023"      => "当日存入的金额当日不能支取",
            "3024"      => "日期切换正在处理",
            "3025"      => "PIN格式出错",
            "3026"      => "发卡方保密子系统失败",
            "3027"      => "原始交易不成功",
            "3028"      => "系统忙，请稍后再提交",
            "3029"      => "交易已被冲正",
            "3030"      => "账号错误",
            "3031"      => "账号户名不符",
            "3032"      => "账号货币不符",
            "3033"      => "无此原交易",
            "3034"      => "非活期账号",
            "3035"      => "找不到原记录",
            "3036"      => "货币错误",
            "3037"      => "磁卡未生效",
            "3038"      => "非通兑户",
            "3039"      => "账户已关户",
        );
        return $_status[$code];
    }
}