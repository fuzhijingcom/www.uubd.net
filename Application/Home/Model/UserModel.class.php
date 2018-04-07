<?php
namespace Home\Model;
use Think\Model;

class UserModel extends Model {


	//获取用户_角色表数据
	public function getUserInfo($map) {
		$users = M("user");
		$userinfo = $users->where($map)->find();
		return $userinfo;
    }

    /*
     * 检查用户是否存在，存在就返回密码，不存在就返回空
     */
    public function hasUser($name){
        $map['name'] = $name;
        $pwd = $this->where($map)->getField('password');
        if($pwd){
            return $pwd;
        }else{
            return false;
        }
    }


    /*
     * 查出用户
     */
    public function getUser($name){
        $map['name'] = $name;
        $info = $this->find();
        return $info;
    }

    //查找用户
    public function findUser($map, $field, $join = '', $jointype = '')
    {
        $user = $this->where($map)->field($field)->join($join, $jointype)->find();
        return $user;
    }
}