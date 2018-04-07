<?php
namespace Home\Controller;

use Common\Controller\HomebaseController;
use Think\Exception;

class ImageController extends HomebaseController {

    public function test() {
        $obj = M('image_res');
        $res = $this->saveImg($obj, '轰轰', 'uiuiuiu', 'hkhkhk', 'ksdjfksd', 'ksdjfkdsf');

        dump($res);
        dump($this->createImgName());
    }

    public function image() {
        $this->assign('SITE_URL', C('SITE_URL'));
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
     * 获取图片列表接口
     */
    public function imgList() {
        $group_name = I('get.group_name', '', 'trim,strip_tags');
        $search_val = I('get.search_val', '', 'trim,strip_tags');

        $ImageDb = M('image_res');
        
        $map = array();
        if ($search_val !== '') {
            $where['img_name'] = array('like', '%' . $search_val . '%');
            $where['group_name'] = array('like', '%' . $search_val . '%');
            $where['origin_name'] = array('like', '%' . $search_val . '%');

            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }

        if ($group_name !== '') {
            $map['group_name'] = array('like', '%' . $group_name . '%');
        }
        
        $field = 'id,img_name,relative_url,absolute_url,group_name,updated_at';
        
        $res = $ImageDb->where($map)->field($field)->order('updated_at desc')->select();

        if ($res === false) {
            $this->returnJson(-1, '请求失败');
        } else {
            $this->returnJson(1, '请求成功', $res, true);
        }
    }

    /**
     * 上传图片接口
     */
    public function uploadImg() {
        $group_name = I('post.group_name', '', 'trim,strip_tags');
        define('OUTPUT_LOG_FLAG', true);
        outputDebugLog($_FILES, 8, 'img.log');

        // 设置附件上传的根目录
        $path = './Public/Uploads/images/';
        if (! is_dir($path)) {
            mkdir($path, 0777);
        }

        // $file_name = $this->createImgName();

        $upload = new \Think\Upload();
        $upload->maxSize   = 3145728;  // 设置附件大小
        $upload->exts      = array('jpg', 'gif', 'png', 'jpeg');
        $upload->rootPath  = $path;
        $upload->savePath  = '';
        $upload->autoSub   = false;
        $upload->saveName  = array('createImgName', '');
        $upload->replace   = true;

        // 上传文件
        $info = $upload->upload();
        if (! $info) {
            outputDebugLog($upload->getError(), 8);
            $this->returnJson(-1, $upload->getError());
        } else {
            $ImageDb = M('image_res');

            foreach ($info as $file) {

                $relative_url = 'Public/Uploads/images/' . $file['savename'];
                $absolute_url = 'http://www.66mjyj.com/' . $relative_url;
                $this->saveImg($ImageDb, $file['savename'], $relative_url, $absolute_url, $group_name, $file['name']);
            }

            $this->returnJson(1, '上传成功');
        }
    }

    /**
     * 将图片信息保存到数据库
     *
     * @param $obj
     * @param $img_name
     * @param $relative_url
     * @param $absolute_url
     * @param string $group_name
     * @param string $origin_name
     * @return mixed
     */
    private function saveImg($obj, $img_name, $relative_url, $absolute_url, $group_name = '', $origin_name = '') {
        $data = array(
            'img_name'     => $img_name,
            'relative_url' => $relative_url,
            'absolute_url' => $absolute_url,
            'group_name'   => $group_name,
            'origin_name'  => $origin_name,
            'created_at'   => date('Y-m-d H:i:s'),
        );

        return $obj->add($data);
    }

    /**
     * 删除图片接口
     */
    public function removeImg() {
        $id = I('post.id', '', 'trim,strip_tags');

        if ($id == '') {
            $this->returnJson(-1, '主键 id 不存在');
        }

        $map = array(
            'id' => $id,
        );
        $ImageDb = M('image_res');

        $find_res = $ImageDb->where($map)->field('id,relative_url')->find();

        if ($find_res == null) {
            $this->returnJson(-1, '图片不存在');
        } else {

            $img_path = './' . $find_res['relative_url'];

            $error = '';
            if (! unlink($img_path)) {
                $error .= '硬盘中没有图片；';
            }

            $delete_res = $ImageDb->where($map)->delete();

            if ($delete_res !== false) {
                $this->returnJson(1, $error . '图片数据库信息删除成功');
            } else {
                $this->returnJson(-1, '图片（数据库信息）删除失败');
            }

        }
    }

    /**
     * 替换图片接口
     */
    public function replaceImg() {
        $id = I('post.id', '', 'trim,strip_tags');

        if ($id == '') {
            $this->returnJson(-1, '主键 id 不存在');
        }

        $map = array(
            'id' => $id,
        );

        $ImageDb = M('image_res');

        $find_res = $ImageDb->where($map)->field('id,img_name,relative_url')->find();

        if ($find_res == null) {
            $this->returnJson(-1, '原图不存在，不能替换');
        } else {
            // 设置附件上传的根目录
            $path = './Public/Uploads/images/';
            if (! is_dir($path)) {
                mkdir($path, 0777);
            }
            $file_name = substr($find_res['img_name'], 0, strrpos($find_res['img_name'], '.'));
            
            $upload = new \Think\Upload();
            $upload->maxSize   = 3145728;  // 设置附件大小
            $upload->exts      = array('jpg', 'gif', 'png', 'jpeg');
            $upload->rootPath  = $path;
            $upload->savePath  = '';
            $upload->autoSub   = false;
            $upload->saveName  = $file_name;
            $upload->replace = true;

            // 上传文件
            $info = $upload->upload();
            if (! $info) {
                $this->returnJson(-1, $upload->getError());
            } else {
                $ImageDb = M('image_res');

                $data = array();
                foreach ($info as $file) {
                    $relative_url = 'Public/Uploads/images/' . $file['savename'];
                    $absolute_url = 'http://www.66mjyj.com/' . $relative_url;
                    $data = array(
                        'img_name' => $file['savename'],
                        'origin_name' => $file['name'],
                        'absolute_url' => $absolute_url,
                        'relative_url' => $relative_url,
                    );

                    $ImageDb->where($map)->setField($data);
                }

                $this->returnJson(1, '上传成功', $data);
            }
        }
    }

    /**
     * 获取图片分组名列表
     */
    public function getImageGroupNames() {
        try {
            $ImageDb = M('image_res');

            $res = $ImageDb->group('group_name')->field('group_name')->order('group_name')->select();

            if ($res !== false) {
                $data = [];

                foreach ($res as $re) {
                    if (! empty($re['group_name'])) {
                        $data[] = $re['group_name'];
                    }
                }

                $this->returnJson(1, '获取图片分组名成功', $data);
            } else {
                $this->returnJson(-1, '获取图片分组名失败');
            }
        } catch (Exception $e) {
            outputDebugLog($e->getMessage(), 8, 'image-error-info.log');
            $this->returnJson(-1, '网络出错');
        }
    }
}