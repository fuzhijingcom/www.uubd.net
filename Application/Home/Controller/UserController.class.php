<?php
namespace Home\Controller;
use Common\Controller\HomebaseController;

class UserController extends HomebaseController
{
    //用户页
    public function User()
    {
        $Store = D('Store');
        $stores = $Store->getStores();
        $Role = D('Role');
        $roles = $Role->getRoles();
        $this->assign('stores', $stores);
        $this->assign('roles', $roles);
        $this->display();
    }

    //获取所有用户
    public function getUsers()
    {
        if (IS_GET) {
            $filed = "id,name,nickname,phone,point,user.s_id,store.s_name,user.r_id,role.r_name";
            $User = D('User');
            $users = $User->getJoinUsers('', '', '', $filed);
            $this->ajaxReturn($users);
        }
    }

    //添加用户
    public function addUser()
    {
        if (IS_POST) {
            $username = I('post.name', '', 'trim,strip_tags');
            if ($username === '') {
                $this->ajaxReturn([
                    'result' => 0,
                    'msg' => '用户名不能为空',
                ]);
            }

            $User = D('User');
            if ($User->userExists($username)) {
                $this->ajaxReturn([
                    'result' => 0,
                    'msg' => '用户名已经存在，请选一个新的用户名',
                ]);
            }

            $password = I('post.pwd', '', 'trim,strip_tags');
            if ($password === '') {
                $this->ajaxReturn([
                    'result' => 0,
                    'msg'    => '密码不能为空',
                ]);
            }

            if (strlen($password) < 6) {
                $this->ajaxReturn([
                    'result' => 0,
                    'msg'    => '密码长度不能少于6位',
                ]);
            }

            $phone = I('post.phone', '', 'trim,strip_tags');
            $weixin_openid = '';
            if ($phone != '') {
                $cmap['phone'] = $phone;
                $Customer = D('customer');
                $customer = $Customer->findCustomer($cmap);

                $weixin_openid = isset($customer['weixin_openid']) ? $customer['weixin_openid'] : '';
            }

            $nickname = I('post.nickname', '', 'trim,strip_tags');
            $point    = I('post.point', 0.0, 'trim,strip_tags');
            $s_id     = I('post.s_id', null, 'trim,strip_tags');
            $r_id     = I('post.r_id', null, 'trim,strip_tags');

            $now = date('Y-m-d H:i:s');
            $data = [
                'name'          => $username,
                'password'      => password_hash($password, PASSWORD_DEFAULT),  // bcrypt 加密密码
                'phone'         => $phone,
                'weixin_openid' => $weixin_openid,
                'nickname'      => $nickname,
                'point'         => $point,
                's_id'          => $s_id,
                'r_id'          => $r_id,
                'ctime'         => $now, // 添加时间
                'utime'         => $now, // 更新时间
            ];

            $result = $User->addUser($data);

            if ($result === false) {
                $this->ajaxReturn([
                    'result' => 0,
                    'msg'    => '用户添加失败'
                ]);
            }

            $this->ajaxReturn([
                'result' => 1,
                'msg'    => '用户添加成功',
            ]);
        }
    }

    //更新用户数据
    public function updateUser()
    {
        if (IS_POST) {
            $id = I('post.id', '', 'trim,strip_tags');
            if ($id === '') {
                $this->ajaxReturn([
                    'result' => 0,
                    'msg'    => '没有选中用户，不能进行更新操作',
                ]);
            }
            $map['id'] = $id;

            $username = I('post.name', '', 'trim,strip_tags');
            if ($username === '') {
                $this->ajaxReturn([
                    'result' => 0,
                    'msg' => '用户名不能为空',
                ]);
            }

            $phone = I('post.phone', '', 'trim,strip_tags');
            $weixin_openid = '';
            if ($phone != '') {
                $cmap['phone'] = $phone;
                $Customer = D('customer');
                $customer = $Customer->findCustomer($cmap);

                $weixin_openid = isset($customer['weixin_openid']) ? $customer['weixin_openid'] : '';
            }

            $nickname = I('post.nickname', '', 'trim,strip_tags');
            $point = I('post.point', 0.0, 'trim,strip_tags');
            $s_id = I('post.s_id', null, 'trim,strip_tags');
            $r_id = I('post.r_id', null, 'trim,strip_tags');

            $now = date('Y-m-d H:i:s');
            $data = [
                'name' => $username,
                'phone' => $phone,
                'weixin_openid' => $weixin_openid,
                'nickname' => $nickname,
                'point' => $point,
                's_id' => $s_id,
                'r_id' => $r_id,
                'utime' => $now, // 更新时间
            ];

            $password = I('post.pwd', '', 'trim,strip_tags');
            if ($password != '') {
                if (strlen($password) < 6) {
                    $this->ajaxReturn([
                        'result' => 0,
                        'msg'    => '新密码长度不能少于6位',
                    ]);
                }
                $data['password'] = password_hash($password, PASSWORD_DEFAULT);  // bcrypt 加密密码
            }

            $User = D('User');
            $result = $User->updateUser($map, $data); //先更新用户表数据

            if ($result === false) {
                $this->ajaxReturn([
                    'result' => 0,
                    'msg' => '更新失败'
                ]);
            }

            $this->ajaxReturn([
                'result' => 1,
                'msg' => '更新成功',
            ]);
        }
    }

    //删除用户
    public function deleteUser()
    {
        if (IS_POST) {
            $map['id'] = $_POST['id'];
            $User = D('User');
            $result = $User->deleteUser($map); //删除用户表的数据
            /*if ($result > 0) {
                $UserRole = D('UserRole');
                $rmap['u_id'] = $map['id'];
                $UserRole->deleteUserRole($rmap); //删除用户_角色表的数据
            }*/
            $this->ajaxReturn(array('result' => $result));
        }
    }

    //获取用户的角色
    public function getUserRoles()
    {
        if (IS_POST) {
            $map['u_id'] = $_POST['id'];
            $field = 'r_id';
            $UserRole = D('UserRole');
            $roles = $UserRole->getUserRoles($map, '', '', $field);
            $this->ajaxReturn($roles);
        }
    }
}