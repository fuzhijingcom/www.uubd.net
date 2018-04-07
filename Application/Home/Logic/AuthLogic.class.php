<?php

/**
 * 用户验证相关的逻辑
 */
namespace Home\Logic;

class AuthLogic {
    private $related_ctl;  // 有关联关系的控制器

    public function __construct() {
        // 注意：以下键值为合并后的保留显示控制器
        //
        $this->related_ctl = [
            '' => [],
            '' => [],
        ];
    }

    /**
     * 获取指定角色下所能访问的控制器名
     *
     * @param $role_id int 用户角色 id
     * @return array       控制器名构成的数组
     */
    public static function getControllerList($role_id) {
        $cache_name = 'controller_auth_cache';
        $ctl_list = S($cache_name);
        $ctl_list = false;

        if ($ctl_list != false && isset($ctl_list[$role_id]) && $ctl_list[$role_id] != null) {
            return $ctl_list[$role_id];
        } else {
            $RolePackage = new \Home\Model\RoleMenuPackageModel();
            $map = ['r_id' => $role_id];
            $field = [
                'm.m_flow' => 'raw_ctl',
            ];
            $join = 'menu m ON m.m_id = role_menu_package.m_id';
            $ctl_result = $RolePackage->getRoleMenuPackages($map, '', '', $field, $join);

            $data = [];
            foreach ($ctl_result as $value) {
                $data[] = substr($value['raw_ctl'], 1, strpos($value['raw_ctl'], '/', 1) - 1);
            }

            if (is_array($ctl_list)) {
                $ctl_list[$role_id] = $data;
                $s_val = $ctl_list;
            } else {
                $s_val = [$role_id => $data];
            }
            S($cache_name, $s_val, 3600);

            return $data;
        }
    }

    // public relatedController
}