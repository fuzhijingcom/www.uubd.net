<?php
/**
 * 
 * 基础模型
 * @author hp
 *
 */
namespace Model;
use Think\Model;

class BaseModel extends Model{

    protected $_Model = null;

	/**
	 * 直接运行SQL语句
	 * @param $sql
	 * @return mixed
	 */
	public function RunSql($sql){
		return $this->_Model->query($sql);
	}

	/**
	 * 直接运行SQL语句
	 * @param $sql
	 * @return mixed
	 */
	public function ExecuteSql($sql)
	{
		return $this->_Model->execute($sql);
	}

	/**
	 * 查询缓存
	 * @param $sql
	 * @param $timeout
	 * @return array|bool|mixed
	 */
	public function QueryCache($sql,$timeout){
		return $this->_Model->cache(true,$timeout)->query($sql);
	}

	/**
	 * 获取一条记录的某个字段值
	 * @param string $field
	 */
	public function GetField($field,$where="1")
	{
		$rs = $this->_Model->where($where)->getField($field);
		return $rs;
	}

	/**
     * 获取某个字段的数据总数
	 * @param $where
	 * @param $field
	 * @return mixed
	 */
	public function GetSum($where,$field)
	{
		$rs = $this->_Model->where($where)->sum($field);
		return $rs;
	}

	/**
	 * 
	 * 添加信息
	 * @param array $data
	 */
	public function Add($data){
		$rs = $this->_Model->data($data)->add();
		return $rs;
	}

	/**
	 * 批量添加
	 * @param $data
	 */
	public function AddAll($data){
		$rs = $this->_Model->data($data)->addAll();
		return $rs;
	}
	
	/**
	 * 
	 * 修改信息
	 * @param string $where
	 * @param array $data
	 */
	public function Edit($where = "1" , $data = array()){
		if ($where=="1"){
			return true;
		}
		if (empty($data)){
			return false;
		}
		$rs = $this->_Model->where($where)->data($data)->save();
		if ($rs === false) {
			return false;
		}
		return true;
	}
	
	/**
	 * 
	 * 删除满足条件的信息
	 * @param string $where
	 */
	public function Delete($where="1"){
		if ($where=="1"){
			return false;
		}
		$rs = $this->_Model->where($where)->delete();
		if ($rs === false){
			return false;
		}
		return true;
	}
	
	/**
	 * 
	 * 获取满足条件的单条信息
	 * @param string 	$where
	 * @param string	$field
	 */
	public function GetInfo($where="1",$field="*"){
		$rs = $this->_Model->where($where)->field($field)->find();
		return $rs;
	}
	
	/**
	 * 
	 * 获得满足条件的数据数量
	 * @param string $where
	 */
	public function GetTotal($where="1"){
		$rs = $this->_Model->where($where)->count();
		return $rs;
	}
	
	/**
	 * 
	 * 获取满足条件的信息列表
	 * @param string 	$where
	 * @param int 		$page
	 * @param int 		$rows
	 * @param array 	$order
	 * @param string	$field
	 */
	public function GetList($where="1",$page=1,$rows=12,$order=array(),$field="*"){
		$limit = $page > 1 ? ($page - 1) * $rows . "," . $rows : $rows;
        $orderby = "";
        if(!empty($order)) {
            foreach ($order as $key => $val) {
                if (!$key) {
                    continue;
                }
                $orderby .= $key . " " . $val . ",";
            }
            $orderby = substr($orderby, 0, -1);
        }
        if($orderby){
            $rs = $this->_Model->where($where)->order($orderby)->field($field)->limit($limit)->select();
        }else{
            $rs = $this->_Model->where($where)->field($field)->limit($limit)->select();
        }
		return $rs;
	}
	
	/**
	 * 
	 * 获取满足条件的所有信息列表
	 * @param string 	$where
	 * @param string	$field
	 */
	public function GetAll($where="1",$order=array(),$field="*"){
        $orderby = "";
        if(!empty($order)) {
            foreach ($order as $key => $val) {
                if (!$key) {
                    continue;
                }
                $orderby .= $key . " " . $val . ",";
            }
            $orderby = substr($orderby, 0, -1);
        }
        if($orderby){
            $rs = $this->_Model->where($where)->order($orderby)->field($field)->select();
        }else{
            $rs = $this->_Model->where($where)->field($field)->select();
        }
		return $rs;
	}
	
	/**
	 * 
	 * 连表获取单点记录信息
	 * @param array 	$jointable
	 * @param array 	$wehre
	 * @param string	$field
	 */
	public function GetJoinInfo($alias,$jointable = array(),$where = "1",$field="*"){
		if (empty($jointable)){
			return false;
		}
		$rs = $this->_Model->alias($alias)->join($jointable)->where($where)->field($field)->find();
		return $rs;
	}
	
	/**
	 * 
	 * 连表获得满足条件的数据数量
	 * @param array $jointable
	 * @param string $where
	 */
	public function GetJoinTotal($alias,$jointable = array(),$where = "1"){
		if (empty($jointable)){
			return false;
		}
		$rs = $this->_Model->alias($alias)->join($jointable)->where($where)->count();
		return $rs;
	}
	
	/**
	 * 
	 * 连表获取满足条件的信息列表
	 * @param array 	$jointable
	 * @param string 	$where
	 * @param int 		$page
	 * @param int 		$rows
	 * @param array 	$order
	 * @param string 	$field
	 */
	public function GetJoinList($alias,$jointable = array(),$where="1",$page=1,$rows=12,$order=array(),$field="*"){
		if (empty($jointable)){
			return false;
		}
		$limit = $page > 1 ? ($page - 1) * $rows . "," . $rows : $rows;
        $orderby = "";
        if(!empty($order)) {
            foreach ($order as $key => $val) {
                if (!$key) {
                    continue;
                }
                $orderby .= $key . " " . $val . ",";
            }
            $orderby = substr($orderby, 0, -1);
        }
        if($orderby) {
            $rs = $this->_Model->alias($alias)->join($jointable)->field($field)->where($where)->order($orderby)->limit($limit)->select();
        }else{
            $rs = $this->_Model->alias($alias)->join($jointable)->field($field)->where($where)->limit($limit)->select();
        }
		return $rs;
	}
	
	/**
	 * 
	 * 连表获取满足条件的所有信息列表
	 * @param array		$jointable
	 * @param string 	$where
	 * @param string 	$field
	 */
	public function GetJoinAll($alias,$jointable = array(),$where="1",$order=array(),$field="*"){
		if (empty($jointable)){
			return false;
		}
        $orderby = "";
        if(!empty($order)) {
            foreach ($order as $key => $val) {
                if (!$key) {
                    continue;
                }
                $orderby .= $key . " " . $val . ",";
            }
            $orderby = substr($orderby, 0, -1);
        }
        if($orderby){
            $rs = $this->_Model->alias($alias)->join($jointable)->field($field)->where($where)->order($orderby)->select();
        }else{
            $rs = $this->_Model->alias($alias)->join($jointable)->field($field)->where($where)->select();
        }
		return $rs;
	}
	
	/**
	 * 
	 * 启动事务
	 */
	public function startTrans(){
        $this->_Model->startTrans();
	}
	
	/**
	 * 
	 * 提交事务
	 */
	public function commitTrans(){
        $this->_Model->commit();
	}
	
	/**
	 * 
	 * 事务回滚
	 */
	public function rollbackTrans(){
        $this->_Model->rollback();
	}

    /**
     * 获取最后执行的SQL语句
     * @return string
     */
	public function getLastSql(){
        return $this->_Model->getLastSql();
    }

    /**
     * 获取最后插入的ID值
     * @return string
     */
    public function getLastInsID(){
        return $this->_Model->getLastInsID();
    }
}