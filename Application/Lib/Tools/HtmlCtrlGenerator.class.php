<?php
/**
 * 用途
 */
namespace Lib\Tools;
use Think\Controller;

// $Obj = new \Lib\Tools\Html($v1, $v2);
// $Obj->fn()

class HtmlCtrlGenerator extends Controller {
    private $mInputHtml = "";
    private $mModelHtml = "";
    private $goods_url_prefix;

    public function __construct($inputhtml = null, $modelhtml = null) {
        parent::__construct();

        $this->goods_url_prefix = C('SITE_URL') . '/index.php/Weixin/Shop/item?selling_id=';

        $this->mInputHtml = $inputhtml;
        $this->mModelHtml = $modelhtml;

        $this->preludeInputHtml();
    }

    private function preludeInputHtml() {
        $inputText = $this->mInputHtml;
        if (strpos($inputText, 'item_url') !== false) {
            $pattern = '/item_url\(([A-Za-z]\d+.*?)\)/i';
            $this->mInputHtml = preg_replace_callback($pattern,
                function ($matches) {
                    return $this->goods_url_prefix . $matches[1];
                }, $inputText);
        }

        if (strpos($inputText, 'item_image_url') !== false) {
            $pattern = '/item_image_url\(([A-Za-z]\d+.*?)\)/i';
            $this->mInputHtml = preg_replace_callback($pattern,
                function ($matches) {
                    return $this->getGoodsInfo($matches[1]);
                }, $inputText);
        }

        if (strpos($inputText, 'other_image_url') !== false) {
            $pattern = '/other_image_url\(([A-Za-z0-9]*?)\.((jpg)|(png)|(gif)|(jpeg))\)/i';
            $this->mInputHtml = preg_replace_callback($pattern,
                function ($matches) {
                    return C('SITE_URL') . '/Public/Uploads/images/' . $matches[1];
                }, $inputText);
        }
    }

    public function genOutputHtml() {
        /* 对输入的html文本进行模板替换,生成最终的结果HTML文本 */

        $last_end_idx = 0;
        $temp_last_end_idx = 0;
        //$inputText = "ABCDEF %%{type:Single-Col-Ctrl; value:[K1001_C1, K1002_C2] }%% HIJKL";
        $inputText = $this->mInputHtml;
        $modelText = $this->mModelHtml;
//        $txt_array = [$inputText,$modelText];
        $output_strs = array();

        $html_txt = $this->mergeModelContent($inputText,$modelText);

        $STRAT_STR_LEN = strlen("%%{");
        $END_STR_LEN = strlen("}%%");

        while (TRUE) {
            $start_pos = strpos($html_txt, "%%{", $last_end_idx);
            if ($start_pos === false) {
                //找不到下一个模板的起始位置,跳出循环
                break;
            }

            $start_pos += $STRAT_STR_LEN;
            $next_pos = strpos($html_txt, "}%%", $start_pos);
            if ($next_pos === false) {
                //找不到下一个模板的结束位置,跳出循环
                break;
            }

            $templ_text = substr($html_txt, $start_pos, $next_pos - $start_pos);
            $ret_text = $this->processTemplateTxt($templ_text);


            //将转换后的结果文本,追加到已处理的文本结尾
            $last_remain_str = substr($html_txt, $last_end_idx, $start_pos - $STRAT_STR_LEN - $last_end_idx);
            if (strlen($last_remain_str) > 0) {
                array_push($output_strs, $last_remain_str);
            }


            array_push($output_strs, $ret_text);

            //修正要搜索的文本的起始偏移量
            $last_end_idx = $next_pos + $END_STR_LEN + 1;

        }

        //将剩余没有处理的文本追加到结果数组的结尾
        if ($last_end_idx < strlen($html_txt)) {
            $str1 = substr($html_txt, $last_end_idx, strlen($html_txt) - $last_end_idx);
            array_push($output_strs, $str1);
        }

        //重新合并数组的元素为结果字符串
        $outputHtml = implode("", $output_strs);
        return $outputHtml;
    }

    public function mergeModelContent($input_txt,$model_txt) {
        $html_arr = [];

        $STRAT_STR_LEN = strlen("<body>");
        $END_STR_LEN = strlen("</body>");

        $start_pos = strpos($input_txt, "body", 0);
        $next_pos = strpos($input_txt, "/body", 0);

        $html_arr[] = substr($input_txt , 0, $start_pos + 4);
        $html_arr[] = substr($input_txt , $start_pos + 4, ($next_pos -$start_pos -5));
        $html_arr[] = str_replace('{$site_url}',C('SITE_URL'),$model_txt);
        $html_arr[] = substr($input_txt , $next_pos-1 );

        $res_txt = implode("", $html_arr);

        return $res_txt;
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
        $ret_text = ""; //返回的结果文本

        switch ($ctrl_type) {
            case "img-item":
                $ret_text = $this->genImgItem($ctrl_val);
                break;
            case "swiper-item":
                $ret_text = $this->genSwiperItem($ctrl_val);
                break;
            case "tab-item":
                $ret_text = $this->genTabItem($ctrl_val);
                break;
            case "navigation-bar-item":
                $ret_text = $this->genNavigationBar($ctrl_val);
                break;
            case "single-col-list":
                $ret_text = $this->genSingleColCtrl($ctrl_val);
                break;
            case "two-col-list":
                $ret_text = $this->genTwoColCtrl($ctrl_val);
                break;
            case "more-good-list":
                $ret_text = $this->genMoreGoodCtrl($ctrl_val);
                break;
            case "title-item":
                $ret_text = $this->genTitleItem($ctrl_val);
                break;
            case "empty-item":
                $ret_text = $this->genEmptyItem($ctrl_val);
                break;
        }

        return $ret_text;
    }

    /*根据输入的模板文本,替换成对应要输出的标题的html文本 */
    private function genTitleItem($tempateText) {
        $val_str = substr($tempateText, 1, strlen($tempateText) - 2);
        $val_array = explode(":",$val_str);
        $val_array1 = explode("-",$val_array[1]);

        if(count($val_array)>1){
            switch ($val_array1[0]) {
                case "b" :
                    $out_str = '<h3 class="page-title text-'. $val_array1[1] .'">' . $val_array[0] . '</h3>';
                    break;
                case "s" :
                    $out_str = '<h4 class="text-'. $val_array1[1] .'">' . $val_array[0] . '</h4>';
                    break;
            }
        }else {
            $out_str = '<h4>' . $val_array[0] . '</h4>';
        }


        return $out_str;
    }

    /*根据输入的模板文本,替换成对应要输出的占位空白的html文本 */
    private function genEmptyItem($tempateText) {
        $val_str = substr($tempateText, 1, strlen($tempateText) - 2);
        $val_arr = explode(":",$val_str);
        $color = $val_arr[1]?$val_arr[1]:"";
        $out_str = '<div style="height: ' . $val_arr[0] . 'px;background-color:#' . $color . '"></div>';
        return $out_str;
    }

    /*根据输入的模板文本,替换成对应要输出的导航栏的html文本 */
    private function genImgItem($tempateText) {
        $val_str = substr($tempateText, 1, strlen($tempateText) - 2);
        $val_array = explode(",",$val_str);
        $width = (100/count($val_array)) . "%";

        $out_str = "<ul class='img-item'>";
        foreach ($val_array as $str_item) {
            $str_item = trim($str_item);
            $str_array = explode(":",$str_item,2);
            $img_src = $this->getImgUrl($str_array[0]);
//            $str_array[1] = (strpos($str_array[1],'http') ? $
            $out_str = $out_str . "<li style = 'width:" . $width . "'>
            <a href='" . ($str_array[1] ? (strstr($str_array[1],'http')?$str_array[1]:(C('SITE_URL').'/'.$str_array[1])):"javascript:void(0)") . "'><img src='" . $img_src . "'></a>
            </li>";
        }
        $out_str = $out_str . "<div class='clearfix'></div>
        </ul>";
        return $out_str;
    }

    /*根据输入的模板文本,替换成对应要输出的轮播图的html文本 */
    private function genSwiperItem($tempateText) {
        $val_str = substr($tempateText, 1, strlen($tempateText) - 2);
        $val_array = explode(",",$val_str);

        $out_str = "<div class='swiper-container swiper-item'>
        <div class='swiper-wrapper'>";
        foreach ($val_array as $str_item) {
            $str_item = trim($str_item);
            $str_array = explode(":",$str_item,2);

            $img_url = $this->getImgUrl($str_array[0]);

            $out_str = $out_str . "<div class='swiper-slide'><a href = '" . ($str_array[1] ? (strstr($str_array[1],'http')?$str_array[1]:(C('SITE_URL').'/'.$str_array[1])):"javascript:void(0)") . "'><img src='" . $img_url . "'></a></div>";
        }
        $out_str = $out_str . "</div>
        <div class='swiper-pagination swiper-pagination-clickable swiper-pagination-bullets'><span
                class='swiper-pagination-bullet'></span><span
                class='swiper-pagination-bullet swiper-pagination-bullet-active'></span><span
                class='swiper-pagination-bullet'></span></div>
    </div>
    <script src='" . C('SITE_URL') . "/Public/Weixin/shop/js/swiper.min.js'></script>
    <script>
        var swiper = new Swiper('.swiper-item', {
            pagination: '.swiper-pagination',
            nextButton: '.swiper-button-next',
            prevButton: '.swiper-button-prev',
            paginationClickable: true,
            spaceBetween: 30,
            centeredSlides: true,
            autoplay: 2500,
            autoplayDisableOnInteraction: false
        });
    </script>";

        return $out_str;
    }

    /*根据输入的模板文本,替换成对应要输出的选项导航条的html文本 */
    private function  genTabItem($tempateText) {
        $val_str = substr($tempateText, 1, strlen($tempateText) - 2);
        $val_array = explode(",",$val_str);

        $out_str = "<div class='swiper-container tab-item'>
        <div class='swiper-wrapper'>";
        foreach ($val_array as $str_item) {
            $str_item = trim($str_item);
            $str_array = explode(":",$str_item,2);

            $out_str = $out_str . "<a class='swiper-slide' href='" . ($str_array[1] ? (strstr($str_array[1],'http')?$str_array[1]:(C('SITE_URL').'/'.$str_array[1])):"javascript:void(0)") . "'>" . $str_array[0] . "</a>";

        }

        $search_url = C('SITE_URL') . '/index.php/Weixin/Shop/search';

        $out_str = $out_str . "</div>
        </div>
        <a href = '{$search_url}' class = 'search-item'><span class='search-icon'></span></a>
        <script src='" . C('SITE_URL') . "/Public/Weixin/shop/js/swiper.min.js'></script>
        <script>
            var swiper = new Swiper('.swiper-container', {
            slidesPerView: 'auto',
            spaceBetween: 5,
            slidesOffsetBefore: 5,
            freeMode: true,
            });
            
            var tab_items = $('.tab-item a');
            var tab_items_width = 0;
            var tab_items_height = $('.tab-item a').eq(0).innerHeight();
            var current_url = this.location.href;
            for(var i = 0; i< tab_items.length; i++) {
                tab_items_width += parseInt(tab_items.eq(i).css('width'))+parseInt(tab_items.eq(i).css('margin'));
                if(current_url == tab_items.eq(i).attr('href')) {
                    tab_items.eq(i).addClass('current');
                }
            }
            if($('.tab-item').width() < tab_items_width) {
                $('.tab-item').css('width',(tab_items_width + 'px'));
            }
            var tab_items_border = $('.tab-item a.current').get(0);
            $('.main-content').css('padding-top',(tab_items_height + (tab_items_border?3:0) + 'px'));
        </script>";

        return $out_str;

    }

    /*根据输入的模板文本,替换成对应要输出的单列商品列表的html文本 */
    private function genSingleColCtrl($tempateText) {
        //截取掉开头的"["和结尾的"]"
        $val_str = substr($tempateText, 1, strlen($tempateText) - 2);
        $val_arr = explode(':',$val_str);
        $val_array = explode(",", $val_arr[0]);
        $is_cut = (count($val_arr) < 2) ? 1 : $val_arr[1];

        $out_str = "<div class='single-col-list " . ($is_cut?"cut-img":"") . "'>
        <ul>";
        foreach ($val_array as $str_item) {
            $str_item = trim($str_item);
            $out_str = $out_str . "<li>
                <a href='" . $this->goods_url_prefix . $str_item . "'>
                <div class='good-img'>
                    <img src='" . $this->getGoodsImg($str_item) . "'>
                </div>
                <p class='good-name'>" . $this->getGoodsInfo($str_item)['goods_name'] . "</p>
                <p><em class='price'>￥" . $this->getGoodsInfo($str_item)['price'] . "</em></p></a>
                <div class='clearfix'></div>
            </li>";
        }
        $out_str = $out_str . "<div class='clearfix'></div>
    </div>";

        return $out_str;
    }

    /*根据输入的模板文本,替换成对应要输出的双列商品列表的html文本 */
    private function genTwoColCtrl($tempateText) {
        //截取掉开头的"["和结尾的"]"
        $val_str = substr($tempateText, 1, strlen($tempateText) - 2);
        $val_arr = explode(':',$val_str);
        $val_array = explode(",", $val_arr[0]);
        $is_cut = (count($val_arr) < 2) ? 1 : $val_arr[1];

        $out_str = "<div class='two-col-list " . ($is_cut?"cut-img":"") . "'>
        <ul>";
        foreach ($val_array as $str_item) {
            $str_item = trim($str_item);
            $out_str = $out_str . "<li>
                <a href='" . $this->goods_url_prefix . $str_item . "'>
                <div class='good-img'>
                    <img src='" . $this->getGoodsImg($str_item) . "'>
                </div>
                <p class='good-name'>" . $this->getGoodsInfo($str_item)['goods_name'] . "</p>
                <p class='text-center'><em class='price'>￥" . $this->getGoodsInfo($str_item)['price'] . "</em></p></a>
                <div class='clearfix'></div>
            </li>";
        }
        $out_str = $out_str . "</ul>
        <div class='clearfix'></div>
    </div>";

        return $out_str;
    }

    /*根据输入的模板文本,替换成对应要输出的更多商品列表的html文本 */
    private function genMoreGoodCtrl($tempateText) {
        //截取掉开头的"["和结尾的"]"
        $val_str = substr($tempateText, 1, strlen($tempateText) - 2);
        $val_arr = explode(':',$val_str);
        $val_array = explode(",", $val_arr[0]);
        $is_cut = (count($val_arr) < 2) ? 1 : $val_arr[1];

        $out_str = "<div class='two-col-list more-good-list " . ($is_cut?"cut-img":"") . "'>
        <ul class='good-container'>";
        foreach ($val_array as $str_item) {
            $str_item = trim($str_item);
            $out_str = $out_str . "<li class = 'good-item' title = '" . $str_item . "'>
                <a href='" . $this->goods_url_prefix . $str_item . "'>
                <div class='good-img'>
                    <img src='" . $this->getGoodsImg($str_item) . "'>
                </div>
                <p class='good-name'>" . $this->getGoodsInfo($str_item)['goods_name'] . "</p>
                <p class='text-center'><em class='price'>￥" . $this->getGoodsInfo($str_item)['price'] . "</em></p></a>
                <div class='clearfix'></div>
            </li>";
        }
        $out_str = $out_str . "</ul>
        <div class='clearfix'></div>
        <a href='javascript:void(0)' class='more-btn' onclick='getMoreGoods(this)'>查看更多</a>
    </div>
    <script src='" . C('SITE_URL') . "/Public/Weixin/shop/js/components/" . "more-good-list.js'></script>";

        return $out_str;
    }

    /*根据输入的模板文本,替换成对应要输出的导航栏的html文本 */
    private function genNavigationBar($tempateText) {
        //截取掉开头的"["和结尾的"]"
        $val_str = substr($tempateText, 1, strlen($tempateText) - 2);
        $val_array = explode(";",$val_str);
        $width = (100/count($val_array));
        $img_arr = [['home_normal.png','home_selected.png'],['classify_normal.png','classify_selected.png'],['shoppingcar_normal.png','shoppingcar_selected.png'],['mine_normal.png','mine_selected.png']];

        $out_str = "<ul class='navigation-bar-item'>";
        foreach ($val_array as $str_key => $str_item) {
            $str_item = trim($str_item);
            $str_array = explode("~",$str_item);

            $out_str = $out_str . "<li class='navigation-item' style='width:" . $width . "%'>";
            if(count($str_array) <= 1) {
                $spec_array = explode(":",$str_item,2);
                $out_str = $out_str . "<a href='" . ($spec_array[1] ? (strstr($spec_array[1],'http')?$spec_array[1]:(C('SITE_URL').'/'.$spec_array[1])):"javascript:void(0)") . "'>
                <img src='" . C('SITE_URL') . "/Public/Weixin/shop/img/navigation_img/" . $img_arr[$str_key][1] . "'><p>" . $spec_array[0] . "</p></a></li>";
                continue;
            }else {
                $str_second_array = explode(",",$str_array[1]);
                $out_str = $out_str . "<a href='javascript:void(0)'>
                <img src='" . C('SITE_URL') . "/Public/Weixin/shop/img/navigation_img/" . $img_arr[$str_key][1] . "'><p>" . $str_array[0] . "</p></a>
                <ul class='pop-box' style='display: none'>";

                foreach ($str_second_array as $str_second_item) {
                    $str_third_array = explode(":",$str_second_item,2);
                    $out_str = $out_str . "<li><a href='" . ($str_third_array[1] ? (strstr($str_third_array[1],'http')?$str_third_array[1]:(C('SITE_URL').'/'.$str_third_array[1])):"javascript:void(0)") . "'>" . $str_third_array[0] . "</a></li>";
                }
                $out_str = $out_str . "<div class='triangle-before'></div><div class='triangle-after'></div>
            </ul>
        </li>";
            }
        }
        $out_str = $out_str . "<div class='clearfix'></div>
    </ul>
    <script src='" . C('SITE_URL') . "/Public/Weixin/shop/js/components/" . "navigation-item.js'></script>";

        return $out_str;
    }

    /*
     * 获取页面所需信息
     */

    //获取商品图片
    public function getGoodsImg($sku_id) {
        $Goods = new \Home\Model\GoodsModel();


        $res = $Goods->getImgUrl($sku_id, 1, 1, false);

        if ('' === $res) {
            $pattern = '#^([A-Za-z]\d+).*$#';

            preg_match($pattern, $sku_id, $match);

            if (isset($match[1])) {
                return $Goods->getImgUrl(($match[1]), 1, 1, false);
            } else {
                return '';
            }
        } else {
            return $res;
        }
    }

    //获取商品信息
    private function getGoodsInfo($sku_id) {

        $Stock = new \Home\Model\StockModel();
        $goodsInfo = $Stock->getInfoBySkuId($sku_id);

        return $goodsInfo;

    }

    // 根据模板名称来获取图片 URL 地址
    private function getImgUrl($name) {
        if (strpos($name, '.') !== false) {
            $img_src = C('SITE_URL') . "/" . $name;

//            if (! file_exists("./Public/Weixin/shop/img/" . $name)) {
//                if (C('REMOTE_IMG_URL') === false) {
//                    $img_src = C('SITE_URL') . '/Public/Uploads/images/' . $name;
//                } else {
//                    $img_src = 'http://www.66mjyj.com/Public/Uploads/images/' . $name;
//                }
//            }
        } else {
            $img_src = $this->getGoodsImg($name);
        }

        return $img_src;
    }
}