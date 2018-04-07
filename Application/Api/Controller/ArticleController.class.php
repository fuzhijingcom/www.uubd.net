<?php

/**
 * 资讯管理模块
 */
namespace Api\Controller;
use Think\Controller\MapiController;

class ArticleController extends MapiController {

    /**
     * 系统通知列表
     */
    public function get_notice_list(){
        switch($this->_method){
            case "get":
                $where = "istop=1";
                $infolist = D("Article")->GetAll($where,array("toptime"=>"DESC"));
                $this->response(array("obj" => array(),"list" => $infolist, "status_code" => 0, "status_msg" => "获取信息成功"), "json", 200);
                break;
            case "post":
                break;
            case "put":
                break;
            case "delete":
                break;
        }
    }

    /**
     * 常见问题
     */
    public function get_question_info(){
        switch($this->_method){
            case "get":
                $where = "a.articleid=2";
                $info = D("Article")->GetJoinInfo("a",array('LEFT JOIN __ARTICLE_INFO__ ai ON ai.articleid = a.articleid'), $where,"a.*,ai.detail,ai.views,ai.digs,ai.comments");
                if (empty($info)) {
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10001, "status_msg" => "信息不存在"), "json", 200);
                }
                $this->response(array("obj" => $info,"list" => array(), "status_code" => 0, "status_msg" => "获取信息成功"), "json", 200);
                break;
            case "post":
                break;
            case "put":
                break;
            case "delete":
                break;
        }
    }

    /**
     * 获取资讯详情
     */
    public function get_info(){
        switch($this->_method){
            case "get":
                $article_id = intval($_GET['article_id']);
                $where = "a.articleid=" . $article_id;
                $info = D("Article")->GetJoinInfo("a",array('LEFT JOIN __ARTICLE_INFO__ ai ON ai.articleid = a.articleid'), $where,"a.*,ai.detail,ai.views,ai.digs,ai.comments");
                if (empty($info)) {
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10001, "status_msg" => "信息不存在"), "json", 200);
                }
                $info['post_date'] = date("Y.m.d",$info['posttime']);
                $this->response(array("obj" => $info,"list" => array(), "status_code" => 0, "status_msg" => "获取信息成功"), "json", 200);
                break;
            case "post":
                break;
            case "put":
                break;
            case "delete":
                break;
        }
    }
}