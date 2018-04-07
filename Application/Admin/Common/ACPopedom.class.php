<?php
namespace Admin\Common;
/**
 * 
 * 权限功能检测类
 * @author hp
 *
 */
final class ACPopedom {
	
	/**
	 * 
	 * 功能:判断是否登陆
	 */
	static public function isLogin(){
        if (session(SESSION) &&
            session(SESSION_HASH) == md5 ( session(SESSION) . SESSION_AUTH . session(SESSION_PASS))) {
            return true;
        }
        return false;
	}
	
	/**
	 * 
	 * 功能:得到账号
	 */
	static public function isSuper() {
		if (self::isLogin ()) {
            return session(SESSION_SUPERMASTER) ? true : false;
		} else {
			return false;
		}
	}
	
	/**
	 * 
	 * 功能:退出
	 */
	static public function logout(){
        session(SESSION,null);
        session(SESSION_ID,null);
        session(SESSION_HASH,null);
        session(SESSION_PASS,null);
        session(SESSION_KEYFIELD,null);
        session(SESSION_SUPERMASTER,null);
		return true;
	}
	
	/**
	 * 
	 * 功能:登陆
	 * @param $master
	 * @param $pwd
	 */
	static public function login($master,$pwd){//账号，密码
        $rs = D("User")->GetInfo("`username`='{$master}' AND `role_type`=0");
        if($rs) {
            if ($rs ["passwd"] == self::mixPass($pwd . $rs['salt'])) {
                if($rs['status']==0){
                    return array("status"=>false,"msg"=>"该账户已被锁定，登陆失败");
                }
                $gs = D("Group")->GetInfo("`groupid`=" . $rs['groupid']);
                session(array("name" => SESSION, 'path' => "/", "expire" =>C("EXPIRE_TIME")));
                session(SESSION, $master);
                session(array("name" => SESSION_ID, 'path' => "/", "expire" =>C("EXPIRE_TIME")));
                session(SESSION_ID, $rs ['userid']);
                session(array("name" => SESSION_HASH, 'path' => "/", "expire" =>C("EXPIRE_TIME")));
                session(SESSION_HASH, md5($master . SESSION_AUTH . $pwd));
                session(array("name" => SESSION_PASS, 'path' => "/", "expire" =>C("EXPIRE_TIME")));
                session(SESSION_PASS, $pwd);
                session(array("name" => SESSION_KEYFIELD, 'path' => "/", "expire" =>C("EXPIRE_TIME")));
                session(SESSION_KEYFIELD, $rs ['superMaster'] ? "superMaster" : unserialize($gs ['keyField']));
                session(array("name" => SESSION_SUPERMASTER, 'path' => "/", "expire" =>C("EXPIRE_TIME")));
                session(SESSION_SUPERMASTER, $rs ['superMaster']);
                return array("status"=>true,"msg"=>"登陆成功！");
            } else {

                return array("status"=>false,"msg"=>"密码错误，登陆失败");
            }
        }else{
            return array("status"=>false,"msg"=>"账号不存在，登陆失败");
        }
	}
	
	/**
	 * 
	 * 功能:得到账号
	 */
	static public function getMaster(){
		if (self::isLogin ()) {
            return session(SESSION);
		} else {
			return false;
		}
	}
	
	/**
	 * 
	 * 功能：得到账号ID
	 */
	static public function getID(){
		if (self::isLogin ()) {
            return session(SESSION_ID);
		} else {
			return 0;
		}
	}

    /**
     *
     * 功能:得到管理员权限组
     */
    static public function getPopedomGroup($master = ""){
        $popedom = array ();
        $master = $master ? $master : self::getMaster ();
        //第一级权限
        foreach ( C("MENU") as $k => $v ) {
            if ($v ['role'] || self::getPopedom ( $k, $master )) {
                //第二级权限
                foreach ( $v ['sub'] as $kk => $vv ) {
                    if (is_array($vv['sub'])){
                        if ($vv ['role'] || self::getPopedom ( "{$k}@{$kk}", $master )) {
                            $popedom [$k] ['sub'] [$kk] ['name'] = $vv ['name'];
                            $popedom [$k] ['sub'] [$kk] ['key'] = $vv ['key'];
                            $popedom [$k] ['sub'] [$kk] ['url'] = $vv ['url'];
                            $popedom [$k] ['sub'] [$kk] ['role'] = $vv ['role'];
                            //三级权限
                            foreach ( $vv ['sub'] as $kkk => $vvv )	{
                                if ($vvv['role'] || self::getPopedom ( "{$k}@{$kk}@{$kkk}", $master )){
                                    $popedom [$k] ['sub'] [$kk] ['sub'] [$kkk] ['name'] = $vvv ['name'];
                                    $popedom [$k] ['sub'] [$kk] ['sub'] [$kkk] ['sub'] = $vvv ['sub'];
                                    $popedom [$k] ['sub'] [$kk] ['sub'] [$kkk] ['key'] = $vvv ['key'];
                                    $popedom [$k] ['sub'] [$kk] ['sub'] [$kkk] ['url'] = $vvv ['url'];
                                    $popedom [$k] ['sub'] [$kk] ['sub'] [$kkk] ['role'] = $vvv ['role'];
                                }
                            }
                        }
                        /*if (empty ( $popedom [$k] ['sub'] [$kk] ['sub'] ) || count ( $popedom [$k] ['sub'] [$kk] ['sub'] ) < 1)
                            unset ( $popedom [$k] ['sub'] [$kk] );*/
                    }else {
                        if ($vv ['role'] || self::getPopedom ( "{$k}@{$kk}", $master )) {
                            $popedom [$k] ['sub'] [$kk] = $vv;
                        }
                    }
                }
                $popedom [$k] ['name'] = $v ['name'];
                $popedom [$k] ['key'] = $v ['key'];
                $popedom [$k] ['url'] = $v ['url'];
                $popedom [$k] ['role'] = $v ['role'];
            }
            if (empty ( $popedom [$k] ['sub'] ) || count ( $popedom [$k] ['sub'] ) < 1)
                unset ( $popedom [$k] );
        }
        return $popedom;
    }
	
	/**
	 * 
	 * 功能:权限检测
	 */
	static public function getPopedom($rows = "", $master = ""){
        if (empty ( $rows ) || ! count ( $rows ))
            return true;
        if (! is_array ( $rows )) {
            $tmp1 = explode ( "|", $rows );
            $rows = array ();
            foreach ( $tmp1 as &$v ) {
                $tmp2 = explode ( "@", $v );
                if (isset($tmp2[2])){
                    $rows [$tmp2 [0]."_".$tmp2 [1] . "_" . $tmp2[2]] = explode ( ",", $tmp2 [0]."_".$tmp2 [1] . "_" . $tmp2[2] );
                }elseif (isset($tmp2[1])) {
                    $rows [$tmp2 [0]."_".$tmp2 [1]] = explode ( ",", $tmp2 [0]."_".$tmp2 [1] );
                }else {
                    $rows [$tmp2 [0]] = explode ( ",", $tmp2 [1] );
                }
            }
        }
        if (! $master) {
            $master = self::getMaster ();
            if (empty ( $_SESSION [SESSION_KEYFIELD] )) {
                $rs = D("User")->GetInfo("`username`='{$master}'");
                $gs = D("Group")->GetInfo("`groupid`=".$rs['groupid']);
                $_SESSION [SESSION_KEYFIELD] = $rs ['issuper'] ? "superMaster" : unserialize ( $gs ['popedom'] );
            }
            $chk = $_SESSION [SESSION_KEYFIELD];
        } else {
            if (empty ( $_SESSION [SESSION_KEYFIELD . "_" . $master] )) {
                $rs = D("User")->GetInfo("`username`='{$master}'");
                $gs = D("Group")->GetInfo("`groupid`=".$rs['groupid']);
                $_SESSION [SESSION_KEYFIELD . "_" . $master] = $rs ['issuper'] ? "superMaster" : unserialize ( $gs ['popedom'] );
            }
            $chk = $_SESSION [SESSION_KEYFIELD . "_" . $master];
        }
        if ($chk == "superMaster") {
            return true;
        }
        foreach ($rows as $k => $v){
            $rows[$k] = $k;
        }
        foreach ( $rows as $k => $v ) {
            $tmp1 = $chk [$k];
            $tmp2 = $v;
            if ($tmp1) {
                if (is_array ( $tmp2 ) && $tmp2 [0]) {
                    foreach ( $tmp2 as $vv ) {
                        if (in_array ( $vv, $tmp1 )) {
                            return true;
                        }
                    }
                } else {
                    return true;
                }
            }
        }
        return false;
	}
	
	/**
	 * 
	 * 功能:清理
	 */
	static public function cleanPopedom($masterid = 0){
		if (! $masterid) {
            session(SESSION_KEYFIELD,"");
		} else {
            session(SESSION_KEYFIELD . "_" . $masterid,"");
		}
	}
	
	
	/**
	 * 
	 * 功能:检测
	 */
	static public function check($rows = ""){
		if (! self::isLogin ()) {
			header("location:/Admin/Login/index");
			exit ();
		} elseif (self::getPopedom ( $rows )) {
			if (LOG_ACT) {
				self::actLog ();
			}
		} else {
			if (LOG_ERROR_POPEDOM) {
				self::popedomLog ();
			}
			//echo msg ( '错误!对不起，你没有足够的操作权限![code:001]' );
			header("location:".U("/Admin/Index/info/"));
		}
	}
	
	/**
	 * 
	 * 功能:写日志
	 */
	static public function pageLog(){
		
	}
	
	/**
	 * 功能:写日志
	 */
	static public function popedomLog() {
		
	}
	
	static public function actLog(){

	}
	
	/**
	 * 功能:写日志
	 */
	static public function loginLog($detail,$master="",$type="登录") {
		$data['type'] = $type;
		$data['username'] = $master ? $master : self::getMaster();
		$data['detail'] = $detail;
		$data['ip'] = get_client_ip();
		$data['posttime'] = time();
		D("Log")->Add($data);
	}
	
	/**
	 * MD5加密
	 * 参数:$str
	 * 返回:密文
	 */
	static public function mixPass($str) {
		return substr ( md5 ( $str ), 5, 16 );
	}
	
}
?>