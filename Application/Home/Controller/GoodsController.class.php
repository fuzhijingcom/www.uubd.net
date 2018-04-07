<?php
namespace Home\Controller;

use Common\Controller\HomebaseController;
use Think\Exception;

class GoodsController extends HomebaseController {
    private $stockObj;

    public function __construct() {
        parent::__construct();

        $this->stockObj = new \Home\Model\StockModel();
    }

    /**
     * 代码测试
     */
    public function test() {
        $this->assign('site',C('SITE_URL'));
        $this->display();
    }

    /**
     * 图片上传测试 demo
     */
    public function img() {
        $this->display();
    }

    /**
     * 商品管理的后台默认入口
     */
    public function goods() {
        // 获取所有商品分类并赋值给前端
        $categoryData = $this->stockObj->getCategoryData();
        $this->assign('categoryData', json_encode($categoryData));
        $this->assign('SITE_URL', C('SITE_URL'));

        $this->display();
    }


    /**
     * 商品的图片编辑与修改
     */
    public function editImage() {
        $selling_id = I('selling_id', '', 'trim,strip_tags');

        $goods_name = $this->stockObj->getGoodsNameSpec($selling_id);

        $this->assign('selling_id', $selling_id);
        $this->assign('goods_name', $goods_name);
        $this->assign('SITE_URL', C('SITE_URL'));
        // 商品购买页预览
        // http://www.66mj.com/index.php/Home/Goods/preview?selling_id=K1001
        $preview_url = C('SITE_URL') . '/index.php/Home/Goods/preview?selling_id=' . $selling_id;
        $this->assign('preview_url', $preview_url);
        $true_url = C('SITE_URL') . '/index.php/Weixin/Shop/item?selling_id=' . $selling_id;
        $this->assign('true_url', $true_url);

        $this->display();
    }

    /**
     * 商品购买预览输出
     */
    public function preview() {
        $selling_id = I('selling_id', '', 'trim,strip_tags');

        $Goods = D('Goods');

        // 获取商品的相关信息
        $goods_info = $Goods->getInfoBySellingId($selling_id);

        $this->assign('price', $goods_info['price']);
        $this->assign('goods_name', $goods_info['goods_name']);

        $this->assign('current_style', $goods_info['style']);

        $styleList = $Goods->getStyleList($selling_id);
        $this->assign('styleList', $styleList);

        $pageImgList = $this->getPageImgList($selling_id, true);
        $this->assign('pageImgList', $pageImgList);

        $slideImgList = $this->getSlideList($selling_id, true);
        $this->assign('slideImgList', $slideImgList);

        $attr_name = $Goods->getAttrName(substr($selling_id, 0, 1));
        $this->assign('attr_name', $attr_name);

        $this->display();
    }

    /**
     * 返回数据到前端
     * @param int    $code       状态码，1表示成功，-1表示失败，也可以拓展成其他的状态码
     * @param string $msg        返回报告消息
     * @param array  $result     返回数据主体内容
     * @param bool   $can_empty  是否允许数据串为空
     */
    private function returnJson($code, $msg, $result = array(), $can_empty = false) {
        $data = array(
            'sign' => $code,
            'msg' => $msg,
        );

        if (! empty($result) || $can_empty) {
            $data['result'] = $result;
        }

        $this->ajaxReturn($data, 'JSON');
    }

    /**
     * 处理商品列表，此方法里面会对检索条件进行筛选
     *
     * @param $cat_id
     * @param $search_value
     * @return array
     */
    private function getGoods($cat_id, $search_value) {
        if ($cat_id === '') {
            $cat_arr = $this->stockObj->getCategoryArr();
        } else {
            $cat_arr = array($cat_id);
        }

        $map = array();
        if ($search_value != '') {
            $where['selling_id'] = array('like', '%' . $search_value . '%');
            $where['goods_name'] = array('like', '%' . $search_value . '%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }

        $data = array();
        foreach ($cat_arr as $cat_val) {
            $model_name = $this->stockObj->getGoodsModelByCat($cat_val);

            $Goods = M($model_name);

            if ($cat_val === 'U') {
                $group = 'style';
                $field = 'concat(selling_id, \'-\', style) id_str,sku_id selling_id,goods_name,style';
            } else {
                $group = 'selling_id';
                $field = 'selling_id id_str,selling_id,goods_name';
            }

            $result = $Goods->where($map)->field($field)->group($group)->select();

            if ($result) {
                $data = array_merge($data, $result);
            }
        }
        unset($cat_val);

        return $data;
    }

    /**
     * 将商品的列表数据构成 JSON 数据返回到前端
     */
    public function goodsList() {
        $cat_id = I('get.cat_id', '', 'trim,strip_tags');
        $search_value = I('get.search_value', '', 'trim,strip_tags');

        $result =  $this->getGoods($cat_id, $search_value);

        if ($result !== false) {
            $this->returnJson(1, '请求成功', $result);
        } else {
            $this->returnJson(-1, '请求失败', $result);
        }
    }

    /**
     * 轮播图的上传接收处理方法
     */
    public function uploadSlideImg() {
        $sku_id = I('post.sku_id', '', 'trim,strip_tags');
        $cnt = I('post.order', 1, 'trim,strip_tags');

        $cat_id = substr($sku_id, 0, 1);

        $path = './Public/Uploads/slide/' . $cat_id . '/'; // 设置附件上传根目录
        $thumb_path = './Public/Uploads/thumb/slide/' . $cat_id . '/'; // 设置附件上传根目录
        // 移除 sku_id 里面可能存在的特殊字符

        $file_name_prefix = preg_replace('/[\x{1000}-\x{ffff}]+/u', '', $sku_id) . '-';
        $file_name = uniqid($file_name_prefix);

        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath  =      $path;
        $upload->savePath  =      ''; // 设置附件上传（子）目录
        $upload->autoSub = false;  // 关闭子目录
        $upload->saveName = $file_name;


        if (! is_dir($path)) {
            mkdir($path, 0777, true);
        }
        if (! is_dir($thumb_path)) {
            mkdir($thumb_path, 0777, true);
        }

        // 上传文件
        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息
            $this->error($upload->getError());
        }else{// 上传成功 获取上传文件信息
            $GoodsPage = M('goods_page_res');

            $pathArr = array();
            $thumbArr = array();
            foreach($info as $file){
                $save_url = "Public/Uploads/slide/" . $cat_id . '/' . $file['savepath'] . $file['savename'];
                $save_thumb_url = "Public/Uploads/thumb/slide/" . $cat_id . '/' . $file['savepath'] . $file['savename'];

                $image = new \Think\Image();
                $image->open('./' . $save_url);
                // 按照原图的比例生成一个最大为110*110的缩略图
                $image->thumb(110, 110)->save('./' . $save_thumb_url);

                $save_url_to_db = "slide/" . $cat_id . '/' . $file['savepath'] . $file['savename'];
                $this->saveSlideImg($GoodsPage, $sku_id, $save_url_to_db, $cnt, $file['name']);

                array_push($pathArr, $save_url);
                $thumbArr[] = $save_thumb_url;
            }

            $return_thumb_url = C('SITE_URL') . '/' . $thumbArr[0];
            $data = array(
                'thumb_url' => $return_thumb_url,
            );
            $this->returnJson(1, '提交成功', $data);
        }
    }

    /**
     * 删除轮播图（单张）
     */
    public function removeSlideImg() {
        $sku_id = I('post.sku_id', '', 'trim,strip_tags');
        $cnt = I('post.order', 1, 'trim,strip_tags');

        if ($sku_id === '') {
            $this->returnJson(-1, '商品不明');
        }

        $map = array(
            'sku_id' => $sku_id,
            'category' => 1,
            'cnt_order' => $cnt,
        );

        $GoodsPage = M('goods_page_res');
        $res = $GoodsPage->where($map)->field('url')->select();

        $del_res = $GoodsPage->where($map)->delete();
        if ($del_res !== false) {
            foreach ($res as $val) {
                $file_name = './Public/Uploads/' . $val['url'];
                $thumb_file_name = './Public/Uploads/thumb/' . $val['url'];
                if (file_exists($file_name)) {

                    // 删除原图和缩略图
                    @unlink($file_name);
                    @unlink($thumb_file_name);
                }
            }
            unset($val);
        }

        $this->returnJson(1, '图片删除成功');
    }

    /**
     * 保存轮播图到数据库
     *
     * @param $obj
     * @param $sku_id
     * @param $url
     * @param $cnt
     * @param $origin_name
     * @return mixed
     */
    private function saveSlideImg($obj, $sku_id, $url, $cnt = 1, $origin_name = '') {
        $map = array(
            'sku_id' => $sku_id,
            'cnt_order' => $cnt
        );

        $find_result = $obj->where($map)->find();

        $data = array(
            'sku_id' => $sku_id,
            'url' => $url,
            'category' => 1,
            'is_main' => ($cnt === 1) ? 1 : 0,
            'cnt_order' => $cnt,
            'origin_name' => $origin_name,
        );
        if ($find_result != null) {
            return $obj->where($map)->save($data);
        } else {
            return $obj->add($data);
        }
    }

    /**
     * 详情图上传接收处理方法
     *
     * @param bool $inner       是否为类内使用，默认为 false（非类内使用，对外接口）
     * @param null $selling_id
     * @param null $cnt
     */
    public function uploadPageImg($selling_id = null, $cnt = null, $inner = false) {
        if ($inner == false) {
            $selling_id = I('post.selling_id', '', 'strip_tags');
            $cnt = I('post.order', 1, 'strip_tags');
        }

        $cat_id = substr($selling_id, 0, 1);
        $path = './Public/Uploads/page/' . $selling_id . '/'; // 设置附件上传根目录
        $prefix = C('SITE_URL') . '/Public/Uploads/';
        $file_name = uniqid($selling_id . '-' . $cnt . '-');
        if (! is_dir($path)) {
            mkdir($path, 0777, true);
        }

        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath  =      $path;
        $upload->savePath  =      ''; // 设置附件上传（子）目录
        $upload->autoSub = false;  // 关闭子目录
        $upload->saveName = $file_name;

        // 上传文件
        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息
            $this->error($upload->getError());
        }else{// 上传成功 获取上传文件信息
            $GoodsPage = M('goods_page_res');
            $pathArr = array();
            $pageImgUrlArr = array();

            foreach($info as $file){
                $save_url = "Public/Uploads/page/" . $selling_id . '/' .$file['savepath'].$file['savename'];
                $save_url_to_db = "page/" . $selling_id . '/' . $file['savepath'] . $file['savename'];
                $this->savePageImg($GoodsPage, $selling_id, $save_url_to_db, $cnt, $file['name']);

                array_push($pathArr, $save_url);
                $pageImgUrlArr[] = $save_url;
            }
            $pageImgUrl = C('SITE_URL') . '/' . $pageImgUrlArr[0];

            $data = $this->getPageImgList($selling_id);

            $this->returnJson(1, '提交成功', $data);
        }
    }

    /**
     * 接收传多张图片的方法
     */
    public function uploadMultiplePageImg() {
        $selling_id = I('post.selling_id', '', 'trim,strip_tags');

        $GoodsPage = M('goods_page_res');
        $map = array(
            'sku_id' => $selling_id,
            'category' => 2,
        );

        $max_cnt = $GoodsPage->where($map)->field('cnt_order')->max('cnt_order');

        $next_cnt = intval($max_cnt) + 1;

        $this->uploadPageImg($selling_id, $next_cnt, true);
    }

    public function uploadMutiplePageImgVerstion2() {
        outputDebugLog($_FILES, 8, 'page-img.log');

        $selling_id = I('post.selling_id', '', 'strip_tags');
        
        $GoodsPage = M('goods_page_res');
        $map = array(
            'sku_id' => $selling_id,
            'category' => 2,
        );
        $max_cnt = $GoodsPage->where($map)->field('cnt_order')->max('cnt_order');
        $cnt = intval($max_cnt) + 1;

        $cat_id = substr($selling_id, 0, 1);
        $path = './Public/Uploads/page/' . $selling_id . '/'; // 设置附件上传根目录
        $prefix = C('SITE_URL') . '/Public/Uploads/';
        if (! is_dir($path)) {
            mkdir($path, 0777, true);
        }

        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath  =      $path;
        $upload->savePath  =      ''; // 设置附件上传（子）目录
        $upload->autoSub = false;  // 关闭子目录
        // $file_name = uniqid($selling_id . '-' . $cnt . '-');
        $upload->saveName = array('createPageImgName',array($selling_id, $cnt));

        // 上传文件
        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息
            $this->error($upload->getError());
        }else{// 上传成功 获取上传文件信息
            $GoodsPage = M('goods_page_res');
            $pathArr = array();
            $pageImgUrlArr = array();

            foreach($info as $file){
                $save_url = "Public/Uploads/page/" . $selling_id . '/' .$file['savepath'].$file['savename'];
                $save_url_to_db = "page/" . $selling_id . '/' . $file['savepath'] . $file['savename'];
                $this->savePageImg($GoodsPage, $selling_id, $save_url_to_db, $cnt, $file['name']);

                ++ $cnt;

                array_push($pathArr, $save_url);
                $pageImgUrlArr[] = $save_url;
            }

            $data = $this->getPageImgList($selling_id);

            $this->returnJson(1, '提交成功', $data);
        }
    }


    /**
     * 保存详情页图片
     *
     * @param $obj
     * @param $selling_id
     * @param $url
     * @param $cnt
     * @param $origin_name string 上传图片的商品的原命名
     * @return mixed
     */
    private function savePageImg($obj, $selling_id, $url, $cnt, $origin_name = '') {
        $map = array(
            'sku_id' => $selling_id,
            'cnt_order' => $cnt,
        );

        $find_result = $obj->where($map)->find();

        $data = array(
            'sku_id' => $selling_id,
            'url' => $url,
            'category' => 2,
            'is_main' => 1,
            'cnt_order' => $cnt,
            'origin_name' => $origin_name,
        );
        if ($find_result != null) {
            return $obj->where($map)->save($data);
        } else {
            return $obj->add($data);
        }
    }

    /**
     * 删除详情页图片
     */
    public function removePageImg() {
        $selling_id = I('post.selling_id', '', 'strip_tags');
        $cnt = I('post.order', 0, 'strip_tags');

        $map = array(
            'sku_id' => $selling_id,
            'cnt_order' => $cnt,
        );

        $GoodsPage = M('goods_page_res');
        $result = $GoodsPage->where($map)->delete();

        if ($result !== false) {
            $data = $this->getPageImgList($selling_id);
            $this->returnJson(1, '删除成功', $data);
        } else {
            $this->returnJson(0, '删除失败');
        }
    }

    /**
     * 获取轮播图的属性和图片信息
     */
    public function getSlideList($selling_id = '', $inner = false) {
        if (IS_GET && ! $inner) {
            $selling_id = I('selling_id', '', 'trim,strip_tags');
        }

        $Goods = D('Goods');
        $data = $Goods->getSlideList($selling_id, $inner);

        if (IS_GET && ! $inner) {
            $this->returnJson(1, '提交成功', $data);
        } else {
            return $data;
        }
    }

    /**
     * 获取商品的详情页的图片
     *
     * @param string $selling_id
     * @param bool   $inner  是否为类内部调用，若是的话，则返回数组类型数据，否则返回 JSON 数据
     * @return array
     */
    public function getPageImgList($selling_id = '', $inner = false) {
        if (IS_GET && ! $inner) {
            $selling_id = I('get.selling_id', '', 'trim,strip_tags');
        }

        $Goods = D('Goods');

        $data = $Goods->getPageImgList($selling_id);

        if (IS_GET && ! $inner) {
            $this->returnJson(1, '获取数据成功', $data, true);
        } else {
            return $data;
        }
    }

    /**
     * 获取商品图片的地址
     *
     * @param $sku_id
     * @param $category
     * @param $cnt_order
     * @param bool $thumb
     * @param bool $full_path
     * @return string
     */
    private function getImgUrl($sku_id, $category, $cnt_order = 1, $thumb = true, $full_path = true) {
        $Goods = M('goods_page_res');

        $map = array(
            'sku_id' => $sku_id,
            'category' => $category,
        );

        if ($cnt_order !== 1) {
            $map['cnt_order'] = $cnt_order;
        }

        $result = $Goods->where($map)->field('url')->find();

        $prefix = '';
        if ($thumb) {
            $prefix .= 'thumb/';
        }

        if ($full_path) {
            $prefix = C('SITE_URL') . '/Public/Uploads/' . $prefix;
        }

        if ($result) {
            return $prefix . $result['url'];
        } else {
            return '';
        }
    }

    /**
     * 调整商品详情图顺序对外接口
     */
    public function changeImgOrder() {
        $selling_id = I('post.selling_id', '', 'trim,strip_tags');
        $origin_orders = I('post.origin_orders', '', 'trim,strip_tags');
        $new_orders = I('post.new_orders', '', 'trim,strip_tags');

        // 判断是否为不合理的排序，如将图片位置拖到了非法位置
        $last_flag = I('post.last_flag', '', 'trim,strip_tags');
        if ($last_flag == 1) {
            $data = $this->getPageImgList($selling_id);
            $this->returnJson(1, '图片不能拉到最后空白位置', $data);
        }

        if ($origin_orders === $new_orders) {
            $this->returnJson(-1, '图片位置没有改变');
        }

        if ($new_orders === '' || $origin_orders === '') {
            $this->returnJson(-1, '没有图片位置需要更改');
        }

        $origin_orders = rtrim($origin_orders, ',');
        $origin_orders_arr = explode(',', $origin_orders);
        $origin_orders_arr_flip = array_flip($origin_orders_arr);

        $new_orders = rtrim($new_orders, ',');
        $new_orders_arr = explode(',', $new_orders);

        $Goods = M('goods_page_res');

        $ids_arr = $this->getPageImgIds($Goods, $selling_id);

        if (count($ids_arr) != count($origin_orders_arr_flip)) {
            $this->returnJson(-1, '传参中图片次序(数量)有问题');
        }

        outputDebugLog('-----');
        foreach ($new_orders_arr as $key => $val) {

            $order = $origin_orders_arr[$key];
            outputDebugLog($order, 8);
            $id = $ids_arr[$origin_orders_arr_flip[$val]]['id'];
            outputDebugLog($id, 8);

            $this->changeOrderById($Goods, $id, $order);
        }


        $data = $this->getPageImgList($selling_id);
        $this->returnJson(1, '图片顺序调整成功', $data);
    }

    /**
     * 获取商品详情图主键值（多个）
     *
     * @param $Goods_Obj
     * @param $selling_id
     * @return mixed
     */
    private function getPageImgIds($Goods_Obj, $selling_id) {
        $field = 'id';
        $map = array(
            'sku_id' => $selling_id,
        );


        return $Goods_Obj->where($map)->field($field)->order('cnt_order asc')->select();
    }

    /**
     * 修改商品详情图排序（根据 id 值来修改）
     *
     * @param $Goods_Obj
     * @param $id
     * @param $order
     * @return mixed
     */
    private function changeOrderById($Goods_Obj, $id, $order) {
        $map = array(
            'id' => $id,
        );
        $data = array(
            'cnt_order' => $order,
        );
        return $Goods_Obj->where($map)->setField($data);
    }

    public function removeAllPageImg() {
        $selling_id = I('post.selling_id', '', 'trim,strip_tags');

        if ($selling_id === '') {
            $this->returnJson(-1, '商品不明');
        }

        $map = array(
            'sku_id' => $selling_id,
            'category' => 2,
        );

        $GoodsPage = M('goods_page_res');

        $res = $GoodsPage->where($map)->field('url')->select();

        $del_res = $GoodsPage->where($map)->delete();

        if ($del_res !== false) {
            foreach ($res as $val) {
                $file_name = './Public/Uploads/' . $val['url'];

                if (file_exists($file_name)) {
                    @unlink($file_name);
                }
            }
            unset($val);
        }

        $data = $this->getPageImgList($selling_id);
        $this->returnJson(1, '图片顺序调整成功', $data);
    }


    /**
     * PageManage部分(锦星写)
     */

    //获取页面信息
    public function getPageInfo() {

        //遍历获取title，并整合数据返回前端
        $pageList = M('pageList');
        $field = 'title,name,id';
        $map['name'] = array('neq','bot_nav');
        $pageInfo = $pageList->where($map)->field($field)->select();

        $this->ajaxReturn(json_encode($pageInfo));
    }

    //创建或复制页面
    public function CreateOrCopy() {
        $func = I('post.func','','trim,strip_tags');
        $id = I('post.id','','trim,strip_tags');

        $pageList = M('pageList');
        if($func == 'create') {
            $data['title'] = '空白页';
            $data['name'] = 'blank' . rand();

            if($pageList->add($data)) {
                $res = [
                    'sign' => 1,
                ];
            }else {
                $res = [
                    'sign' => 0,
                    'msg' => '创建失败！请重试'
                ];
            }
        }elseif ($func == 'copy') {
            $field = 'title,name,content';
            $data = $pageList->where(array('id'=>$id))->field($field)->find();
            $data['name'] = $data['name'] . rand();

            if($pageList->add($data)) {
                $res = [
                    'sign' => 1,
                ];
            }else {
                $res = [
                    'sign' => 0,
                    'msg' => '复制失败！请重试'
                ];
            }
        }else {
            exit('不知道出现什么问题');
        }

        $this->ajaxReturn($res);
    }

    //删除页面
    public function deletePage() {
        $id = I('post.id','','trim,strip_tags');

        $pageList = M('pageList');
        $res = $pageList->where(array('id' => $id))->delete();

        $this->ajaxReturn($res);
    }

    public function editPage() {
        $id = I('get.id','','trim,strip_tags');

        $pageList = M('pageList');
        $field = 'title,bgcolor,content,name,id';
        $pageInfo = $pageList->where(array('id' => $id))->field($field)->find();

        $Filter = new \Lib\Tools\FilterItem($pageInfo['content']);
        $pageInfo['ctrlData'] = $Filter->genOutputHtml();

        $Generator = new \Lib\Tools\HtmlCtrlGenerator($pageInfo['content']);
        $pageInfo['content'] = $Generator->genOutputHtml();

        $this->assign('pageInfo',json_encode($pageInfo));
        $this->assign('pageTitle',$pageInfo['title']);
        $this->display('editpage');
    }

    public function editCommonPart() {
        $pageList = M('pageList');
        $field = 'content,name,id';
        $commonInfo = $pageList->where(array('name' => 'bot_nav'))->field($field)->find();

        $Filter = new \Lib\Tools\FilterItem($commonInfo['content']);
        $commonInfo['ctrlData'] = $Filter->genOutputHtml();

        $this->assign('commonInfo',json_encode($commonInfo));
        $this->display("editcommon");
    }

    public function changeContent() {
        $pageInfo['id'] = I('post.id','','trim,strip_tags');
        $pageInfo['name'] = I('post.name','','trim,strip_tags');
        $pageInfo['title'] = I('post.title','','trim,strip_tags');
        $pageInfo['bgcolor'] = I('post.bgcolor','','trim,strip_tags');
        if($pageInfo['name'] == 'bot_nav') {
            $pageInfo['content'] = I('post.content','','trim,strip_tags');
        }else {
            $pageInfo['content'] = '<div class="main-content"> ' . I('post.content','','trim,strip_tags') . ' </div>';
        }

        $pageList = M('pageList');
        $res = $pageList->save($pageInfo);
        if($res){
            $res = [
                'sign' => 1,
            ];
        }else {
            $res = [
                'sign' => -1,
                'msg' => '不知道出什么问题了，请重试',
            ];
        }

        $this->ajaxReturn($res);
    }

    public function getImgList() {
        $image_res = M('imageRes');

        $map['group_name'] = array('like','商城%');
        $img_number = $image_res->where($map)->group('group_name')->select();

        $img_list = [];
        foreach ($img_number as $v) {
            $img_group = $image_res->where(array('group_name'=>$v['group_name']))->field('relative_url')->select();
            $img_list[] = [
                'group_name' => explode('-',$v['group_name'],2)[1],
                'img_group' => $img_group,
            ];
        }

        $this->ajaxReturn(json_encode($img_list));
    }

    //////////////////////////////////////////////////////////////////////////////////
    //                                                                              //
    // 以下为管理优惠券的相关内容                                                      //
    //                                                                              //
    //////////////////////////////////////////////////////////////////////////////////

    /**
     * 优惠券列表
     */
    public function couponList() {
        $res = D('Coupon')->getcoupon();

        $data = [];
        foreach ($res as $re) {
            $data[] = [
                'coupon_id' => $re['coupon_id'],
                'coupon_name' => $re['coupon_name'],
                'available_time' => $re['available_time'],
                'invalid_time' => $re['invalid_time'],
                'is_valid' => ($re['is_valid'] == 1) ? '有效' : '无效',
                'coupon_type' => $re['coupon_type'],
                'coupon_price' => $re['coupon_price'],
                'coupon_condition' => $re['coupon_condition'],
                'goods_type' => D('coupon')->goodsType2Str($re['goods_type']),
                'discount' => $re['discount'],
                'num_per_user' => $re['num_per_user'],
                'shareable' => $re['shareable'],
                'superimposed' => ($re['superimposed'] == 1) ? '能' : '否',
            ];
        }
        unset($re);

        $this->returnJson(1, '成功获取数据', $data);
    }

    /**
     * 获取单个优惠券
     */
    public function getSpecCoupon() {
        $coupon_id = I('get.coupon_id', '', 'trim,strip_tags');

        if ($coupon_id === '') {
            $this->returnJson(-1, '优惠券信息不明');
        }

        $map['coupon_id'] = $coupon_id;

        try {
            $res = D('Coupon')->getcoupon($map);

            $res['available_time'] = date('Y-m-d\TH:i:s', strtotime($res['available_time']));
            $res['invalid_time'] = date('Y-m-d\TH:i:s', strtotime($res['invalid_time']));

            if ($res !== false) {
                $this->returnJson(1, '成功获取数据', $res);
            } else {
                $this->returnJson(1, '数据获取失败');
            }
        } catch (Exception $e) {
            $this->returnJson(1, $e->getMessage());
        }


    }

    /**
     * 编辑优惠券信息
     */
    public function editCoupon() {
        $coupon_id = I('post.coupon_id', '', 'trim,strip_tags');

        if ($coupon_id === '') {
            $this->returnJson(-1, '优惠券信息不明');
        }

        $coupon_name = I('post.coupon_name', '', 'trim,strip_tags');
        $available_time = I('post.available_time', '', 'trim,strip_tags');
        $invalid_time = I('post.invalid_time', '', 'trim,strip_tags');
        $is_valid = I('post.is_valid', 0, 'trim,strip_tags');
        $coupon_type = I('post.coupon_type', 0, 'trim,strip_tags');
        $coupon_price = I('post.coupon_price', 0.00, 'trim,strip_tags');
        $coupon_condition = I('post.coupon_condition', 0.00, 'trim,strip_tags');
        $goods_type = I('post.goods_type', '', 'trim,strip_tags');
        $discount = I('post.discount', -1, 'trim,strip_tags');
        $num_per_user = I('post.num_per_user', 1, 'trim,strip_tags');
        $shareable = I('post.shareable', 0, 'trim,strip_tags');
        $superimposed = I('post.superimposed', 0, 'trim,strip_tags');

        // 部分信息需要检验
        if ($coupon_name === '') {
            $this->returnJson(-1, '优惠券名不能为空！');
        }

        $data = [
            'coupon_name' => $coupon_name,
            'available_time' => $available_time,
            'invalid_time' => $invalid_time,
            'is_valid' => $is_valid,
            'coupon_type' => $coupon_type,
            'coupon_price' => $coupon_price,
            'coupon_condition' => $coupon_condition,
            'goods_type' => $goods_type,
            'discount' => $discount,
            'num_per_user' => $num_per_user,
            'shareable' => $shareable,
            'superimposed' => $superimposed,
        ];

        try {
            $res = M('coupon')->where(['coupon_id' => $coupon_id])->setfield($data);

            if ($res === false) {
                $this->returnJson(-1, '编辑失败');
            } else {
                $this->returnJson(1, '修改成功');
            }
        } catch (Exception $e) {
            $this->returnJson(-1, $e->getMessage());
        }
    }

    /**
     * 添加优惠券信息
     */
    public function addCoupon() {
        $coupon_name = I('post.coupon_name', '', 'trim,strip_tags');
        $available_time = I('post.available_time', '', 'trim,strip_tags');
        $invalid_time = I('post.invalid_time', '', 'trim,strip_tags');
        $is_valid = I('post.is_valid', 0, 'trim,strip_tags');
        $coupon_type = I('post.coupon_type', 0, 'trim,strip_tags');
        $coupon_price = I('post.coupon_price', 0.00, 'trim,strip_tags');
        $coupon_condition = I('post.coupon_condition', 0.00, 'trim,strip_tags');
        $goods_type = I('post.goods_type', '', 'trim,strip_tags');
        $discount = I('post.discount', -1, 'trim,strip_tags');
        $num_per_user = I('post.num_per_user', 1, 'trim,strip_tags');
        $shareable = I('post.shareable', 0, 'trim,strip_tags');
        $superimposed = I('post.superimposed', 0, 'trim,strip_tags');

        // 部分信息需要检验
        if ($coupon_name === '') {
            $this->returnJson(-1, '优惠券名不能为空！');
        }

        $data = [
            'coupon_id' => D('coupon')->generatePrimaryKey(),
            'coupon_name' => $coupon_name,
            'available_time' => $available_time,
            'invalid_time' => $invalid_time,
            'is_valid' => $is_valid,
            'coupon_type' => $coupon_type,
            'coupon_price' => $coupon_price,
            'coupon_condition' => $coupon_condition,
            'goods_type' => $goods_type,
            'discount' => $discount,
            'num_per_user' => $num_per_user,
            'shareable' => $shareable,
            'superimposed' => $superimposed,
        ];

        try {
            $res = D('coupon')->addCoupon($data);

            if ($res === false) {
                $this->returnJson(-1, '添加失败');
            } else {
                $this->returnJson(1, '添加成功');
            }
        } catch (Exception $e) {
            $this->returnJson(-1, $e->getMessage());
        }
    }

    /**
     * 删除优惠券
     */
    public function removeCoupon() {
        $coupon_id = I('post.coupon_id', '', 'trim,strip_tags');

        if ($coupon_id === '') {
            $this->returnJson(-1, '优惠券信息不明');
        }

        try {
            $res = D('coupon')->removeCoupon($coupon_id);

            if ($res === false) {
                $this->returnJson(-1, '删除失败');
            } else {
                $this->returnJson(1, '删除成功');
            }
        } catch (Exception $e) {
            $this->returnJson(-1, $e->getMessage());
        }
    }

    /**
     * 用户用过优惠券的情况
     */
    public function couponUsedByUser() {

    }

    /**
     * 优惠券活动发放
     *
     * 将优惠券投放到商城首页中
     */
    public function makeCoupon() {

    }

}
