<?php
namespace Home\Model;
use Think\Model;

class MenuModel extends Model{
    public function getMenus($map="",$sort="",$limit="",$field="",$join=""){
        $Menu = M("menu");
        $map['m_show'] = 1;
        $map['m_status'] = 1;
        $menuList = $Menu->where($map)->limit($limit)->order($sort)->field($field)->select();
        return $menuList;
    }
}