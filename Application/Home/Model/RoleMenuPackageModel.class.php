<?php
namespace Home\Model;
use Think\Model;
class RoleMenuPackageModel extends Model{

    /**
     * 获取用户的菜单权限
     * @param string $map
     * @param string $sort
     * @param string $limit
     * @param string $field
     * @param string $join
     * @return mixed
     */
    public function getRoleMenuPackages($map="", $sort="", $limit="", $field="", $join=""){
        $RoleMenuPackages = M('roleMenuPackage');
        $roleMenupackagesList = $RoleMenuPackages->where($map)->order($sort)->field($field)->limit($limit)->join($join)->select();
        return $roleMenupackagesList;
    }
}