<?php
/**
 * 用途:筛选出页面的控件内容
 */
namespace Lib\Tools;
use Think\Controller;

// $Obj = new \Lib\Tools\Html($v1, $v2);
// $Obj->fn()

class FilterItem extends Controller {
    private $mInputHtml = "";

    public function __construct($inputhtml = null, $modelhtml = null) {
        parent::__construct();

        $this->mInputHtml = $inputhtml;

    }

    public function genOutputHtml() {
        /* 对输入的html文本进行模板替换,生成最终的结果HTML文本 */

        $last_end_idx = 0;
        $temp_last_end_idx = 0;
        //$inputText = "ABCDEF %%{type:Single-Col-Ctrl; value:[K1001_C1, K1002_C2] }%% HIJKL";
        $inputText = $this->mInputHtml;
        $output_strs = array();

        $STRAT_STR_LEN = strlen("%%{");
        $END_STR_LEN = strlen("}%%");

        while (TRUE) {
            $start_pos = strpos($inputText, "%%{", $last_end_idx);
            if ($start_pos === false) {
                //找不到下一个模板的起始位置,跳出循环
                break;
            }

            $start_pos += $STRAT_STR_LEN;
            $next_pos = strpos($inputText, "}%%", $start_pos);
            if ($next_pos === false) {
                //找不到下一个模板的结束位置,跳出循环
                break;
            }

            $templ_text = substr($inputText, $start_pos, $next_pos - $start_pos);
            $ret_text = $this->processTemplateTxt($templ_text);


            array_push($output_strs, $ret_text);

            //修正要搜索的文本的起始偏移量
            $last_end_idx = $next_pos + $END_STR_LEN + 1;
        }

        //重新合并数组的元素为结果字符串
        return $output_strs;
    }

    /*抽取模板中所定义的控件类型和控件的具体内容取值 */
    private function getCtrlTypeAndVal($tempText) {

        $begin_idx = strpos($tempText, "type:");
        if ($begin_idx === false) {
            return array("", "");
        }
        $begin_idx += strlen("type:");
        $end_idx = strpos($tempText, ";", $begin_idx);
        if ($end_idx === false) {
            return array("", "");
        }
        $type_str = trim(substr($tempText, $begin_idx, $end_idx - $begin_idx));
        $type_str = strtolower($type_str);

        $begin_idx = strpos($tempText, "value:", $end_idx + 1);
        if ($begin_idx === false) {
            return array($type_str, "");
        }
        $begin_idx += strlen("value:");
        $end_idx = strlen($tempText);
        $val_str = trim(substr($tempText, $begin_idx, $end_idx - $begin_idx));

        return array($type_str, $val_str);
    }

    /*分析处理对应的模板文本,并调用对应的转换函数进行转换 */
    function processTemplateTxt($tempText) {
        $ret_val = $this->getCtrlTypeAndVal($tempText);
        $ctrl_type = $ret_val[0];
        $ctrl_val = $ret_val[1];
        $ret_arr = array(); //返回的结果数组

        switch ($ctrl_type) {
            case "img-item":
            case "swiper-item":
            case "tab-item":
                $ret_arr = [
                    'type' => $ctrl_type,
                    'value' => $this->getNameUrlArr($ctrl_val),
                ];
                break;
            case "empty-item":
            case "title-item":
            case "single-col-list":
            case "two-col-list":
            case "more-good-list":
                $ret_arr = [
                    'type' => $ctrl_type,
                    'value' => substr($ctrl_val, 1, strlen($ctrl_val) - 2),
                ];
                break;
            case "navigation-bar-item":
                $ret_arr = [
                    'type' => $ctrl_type,
                    'value' => $this->arrangeNavigation($ctrl_val)
                ];
                break;
        }

        return $ret_arr;
    }

    private function getNameUrlArr($tempateText) {
        $val_str = substr($tempateText, 1, strlen($tempateText) - 2);
        $val_arr = explode(',',$val_str);

        $res_arr = [];
        foreach ($val_arr as $item) {
            $item_arr = trim($item);
            $res_arr[] = $item_arr;
        }

        return $res_arr;
    }

    private function arrangeNavigation($tempateText) {
        $val_str = substr($tempateText, 1, strlen($tempateText) - 2);
        $val_arr = explode(';',$val_str);

        $res_arr = [];
        foreach ($val_arr as $k => $item) {
            $item = trim($item);
            if(strpos($item,"~")) {
                $item1_arr = explode("~",$item);
                $item2_arr = explode(",",$item1_arr[1]);

                foreach ($item2_arr as $k1 => $v) {
                    $v = trim($v);
                    $v_arr = explode(":",$v);
                    $item2_arr[$k1] = [
                        'name' => $v_arr[0],
                        'url' => $v_arr[1],
                    ];
                }

                $res_arr[] = [
                    'name' => $item1_arr[0],
                    'sub_nav' => $item2_arr,
                ];
            }else {
                $item1_arr = explode(":",$item);
                $res_arr[] = [
                    'name' => $item1_arr[0],
                    'url' => $item1_arr[1],
                ];
            }
        }

        return $res_arr;
    }

}