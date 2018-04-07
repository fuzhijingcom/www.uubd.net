<?php
namespace Tools;
/**
	--------------------------------------------------
	环信PHP REST示例代码
	--------------------------------------------------
	Copyright(c) 2015 环信即时通信云 www.easemob.com
	--------------------------------------------------
	Author: 神之爱 <fengpei@easemob.com>
	--------------------------------------------------
*/
include "Curl.class.php";

class Easemob{
	private $client_id='YXA6pkUGANa6EeSUKBtg-ak7UQ';
	private $client_secret='YXA6ZzJ5AzFGPAgYVuYFTtJs_bZnmNI';
	private $org_name='easemob-demo';
	private $app_name='chatdemoui';
	private $url='https://a1.easemob.com/easemob-demo/chatdemoui/';
	
	private static $debug = False;
//------------------------------------------------------用户体系	
		/**
	 * 初始化参数
	 *
	 * @param array $options   
	 * @param $options['client_id']    	
	 * @param $options['client_secret'] 
	 * @param $options['org_name']    	
	 * @param $options['app_name']   		
	 */
	public function __construct($options) {
		$this->client_id = isset ( $options ['client_id'] ) ? $options ['client_id'] : '';
		$this->client_secret = isset ( $options ['client_secret'] ) ? $options ['client_secret'] : '';
		$this->org_name = isset ( $options ['org_name'] ) ? $options ['org_name'] : '';
		$this->app_name = isset ( $options ['app_name'] ) ? $options ['app_name'] : '';
		if (! empty ( $this->org_name ) && ! empty ( $this->app_name )) {
			$this->url = 'https://a1.easemob.com/' . $this->org_name . '/' . $this->app_name . '/';
		}
	}	
	
	/**
	* 获取header
	*/
	function getHeader(){
		$headers = array(
            array('key' => 'Content-Type', 'value'=>'application/json'),
        );
        if($this->getToken()){
			 $headers[] = array('key' => 'Authorization', 'value'=>'Bearer '.$this->getToken());
		}
		return $headers;
	}
	/**
	*获取token 
	*/
	function getToken()
	{
		$options=array(
			"grant_type"=>"client_credentials",
			"client_id"=>$this->client_id,
			"client_secret"=>$this->client_secret
		);
		//json_encode()函数，可将PHP数组或对象转成json字符串，使用json_decode()函数，可以将json字符串转换为PHP数组或对象
		$body=json_encode($options);
		//使用 $GLOBALS 替代 global
		$url=$this->url.'token';
		$tokenResult = json_decode($this->postCurl($url,$body,$header=array()),true);
		return $tokenResult['access_token'];
	}
	/**
	  授权注册
	*/
	function createUser($username,$password,$nickname){
		$url=$this->url.'users';
		$options=array(
			"username"=>$username,
			"password"=>$password,
            'nickname' => $nickname
        );
		$body=json_encode($options);
		$header=$this->getHeader();
		$result=$this->postCurl($url,$body,$header);
		return $result;
	}
	/*
		批量注册用户
	*/
	function createUsers($options){
		$url=$this->url.'users';
	
		$body=json_encode($options);
		$header=$this->getHeader();
		$result=$this->postCurl($url,$body,$header);
		return $result;
	}
	/*
		重置用户密码
	*/
	function resetPassword($username,$newpassword){
		$url=$this->url.'users/'.$username.'/password';
		$options=array(
			"newpassword"=>$newpassword
		);
		$body=json_encode($options);
		$header=$this->getHeader();
		$result=$this->postCurl($url,$body,$header,"PUT");
		return $result;
	}
	
	/*
		获取单个用户
	*/
	function getUser($username){
		$url=$this->url.'users/'.$username;
		$header=$this->getHeader();
		$result=$this->postCurl($url,'',$header,"GET");
		return $result;
	}
	/*
		获取批量用户----不分页
	*/
	function getUsers($limit=0){
		if(!empty($limit)){
			$url=$this->url.'users?limit='.$limit;
		}else{
			$url=$this->url.'users';
		}
		$header=$this->getHeader();
		$result=$this->postCurl($url,'',$header,"GET");
		return $result;
	}
	/*
		获取批量用户---分页
	*/
	function getUsersForPage($limit=0,$cursor=''){
		$url=$this->url.'users?limit='.$limit.'&cursor='.$cursor;
		
		$header=$this->getHeader();
		$result=$this->postCurl($url,'',$header,"GET");
		if(!empty($result["cursor"])){
			$cursor=$result["cursor"];
			$this->writeCursor("userfile.txt",$cursor);
		}
		//var_dump($GLOBALS['cursor'].'00000000000000');
		return $result;
	}
	
	//创建文件夹
	function mkdirs($dir, $mode = 0777)
	 {
		 if (is_dir($dir) || @mkdir($dir, $mode)) return TRUE;
		 if (!mkdirs(dirname($dir), $mode)) return FALSE;
		 return @mkdir($dir, $mode);
	 } 
	 //写入cursor
	function writeCursor($filename,$content){
		//判断文件夹是否存在，不存在的话创建
		if(!file_exists("resource/txtfile")){
			mkdirs("resource/txtfile");
		}
		$myfile=@fopen("resource/txtfile/".$filename,"w+") or die("Unable to open file!");
		@fwrite($myfile,$content);
		fclose($myfile);	
	}
	 //读取cursor
	function readCursor($filename){
		//判断文件夹是否存在，不存在的话创建
		if(!file_exists("resource/txtfile")){
			mkdirs("resource/txtfile");
		}
		$file="resource/txtfile/".$filename;
		$fp=fopen($file,"a+");//这里这设置成a+
		if($fp){
			while(!feof($fp)){
				//第二个参数为读取的长度
				$data=fread($fp,1000);	
			}	
			fclose($fp);
		}	 
		return $data;	
	}
	/*
		删除单个用户
	*/
	function deleteUser($username){
		$url=$this->url.'users/'.$username;
		$header=$this->getHeader();
		$result=$this->postCurl($url,'',$header,'DELETE');
		return $result;
	}
	/*
		删除批量用户
		limit:建议在100-500之间，、
		注：具体删除哪些并没有指定, 可以在返回值中查看。
	*/
	function deleteUsers($limit){
		$url=$this->url.'users?limit='.$limit;
		$header=$this->getHeader();
		$result=$this->postCurl($url,'',$header,'DELETE');
		return $result;
		
	}
	/*
		修改用户昵称
	*/
	function editNickname($username,$nickname){
		$url=$this->url.'users/'.$username;
		$options=array(
			"nickname"=>$nickname
		);
		$body=json_encode($options);
		$header=$this->getHeader();
		$result=$this->postCurl($url,$body,$header,'PUT');
		return $result;
	}
	/*
		添加好友---400
	*/
	function addFriend($username,$friend_name){
		$url=$this->url.'users/'.$username.'/contacts/users/'.$friend_name;
		$header=$this->getHeader();
		$result=$this->postCurl($url,'',$header,'POST');
		return $result;	
		
			
	}
	
	
	/*
		删除好友
	*/
	function deleteFriend($username,$friend_name){
		$url=$this->url.'users/'.$username.'/contacts/users/'.$friend_name;
		$header=$this->getHeader();
		$result=$this->postCurl($url,'',$header,'DELETE');
		return $result;	
		
	}
	/*
		查看好友
	*/
	function showFriends($username){
		$url=$this->url.'users/'.$username.'/contacts/users';
		$header=$this->getHeader();
		$result=$this->postCurl($url,'',$header,'GET');
		return $result;	
		
	}
	/*
		查看用户黑名单
	*/
	function getBlacklist($username){
		$url=$this->url.'users/'.$username.'/blocks/users';
		$header=$this->getHeader();
		$result=$this->postCurl($url,'',$header,'GET');
		return $result;
		
	}
	/*
		往黑名单中加人
	*/
	function addUserForBlacklist($username,$usernames){
		$url=$this->url.'users/'.$username.'/blocks/users';
		$body=json_encode($usernames);
		$header=$this->getHeader();
		$result=$this->postCurl($url,$body,$header,'POST');
		return $result;	
		
	}
	/*
		从黑名单中减人
	*/
	function deleteUserFromBlacklist($username,$blocked_name){
		$url=$this->url.'users/'.$username.'/blocks/users/'.$blocked_name;
		$header=$this->getHeader();
		$result=$this->postCurl($url,'',$header,'DELETE');
		return $result;	
		
	}
	 /*
		查看用户是否在线
	 */
	function isOnline($username){
		$url=$this->url.'users/'.$username.'/status';
		$header=$this->getHeader();
		$result=$this->postCurl($url,'',$header,'GET');
		return $result;	
		
	}
	/*
		查看用户离线消息数
	*/
	function getOfflineMessages($username){
		$url=$this->url.'users/'.$username.'/offline_msg_count';
		$header=$this->getHeader();
		$result=$this->postCurl($url,'',$header,'GET');
		return $result;	
			
	}
	/*
		查看某条消息的离线状态
		----deliverd 表示此用户的该条离线消息已经收到
	*/
	function getOfflineMessageStatus($username,$msg_id){
		$url=$this->url.'users/'.$username.'/offline_msg_status/'.$msg_id;
		$header=$this->getHeader();
		$result=$this->postCurl($url,'',$header,'GET');
		return $result;	
		
	}
	/*
		禁用用户账号
	*/ 
	function deactiveUser($username){
		$url=$this->url.'users/'.$username.'/deactivate';
		$header=$this->getHeader();
		$result=$this->postCurl($url,'',$header);
		return $result;
	}
	/*
		解禁用户账号
	*/ 
	function activeUser($username){
		$url=$this->url.'users/'.$username.'/activate';
		$header=$this->getHeader();
		$result=$this->postCurl($url,'',$header);
		return $result;
	} 
	
	/*
		强制用户下线
	*/ 
	function disconnectUser($username){
		$url=$this->url.'users/'.$username.'/disconnect';
		$header=$this->getHeader();
		$result=$this->postCurl($url,'',$header,'GET');
		return $result;
	}
	//--------------------------------------------------------上传下载
	/*
		上传图片或文件
	*/
	function uploadFile($filePath){
		$url=$this->url.'chatfiles';
		$file=file_get_contents($filePath);
		$body['file']=$file;
		$header=array('enctype:multipart/form-data Authorization:Bearer ',$this->getToken(),"restrict-access:true");
		$result=$this->postCurl($url,$body,$header,'XXX');
		return $result;
			
	}
	/*
		下载文件或图片
	*/
	function downloadFile($uuid,$shareSecret){
		$url=$this->url.'chatfiles/'.$uuid;
		$header = array("share-secret:".$shareSecret,"Accept:application/octet-stream Authorization:Bearer ",$this->getToken());
		$result=$this->postCurl($url,'',$header,'GET');
		$filename = md5(time().mt_rand(10, 99)).".png"; //新图片名称
		if(!file_exists("resource/down")){
			//mkdir("../image/down");
			mkdirs("resource/down/");
		}
		
		$file = @fopen("resource/down/".$filename,"w+");//打开文件准备写入
		@fwrite($file,$result);//写入
		fclose($file);//关闭
		return $filename;
		
	}
	/*
		下载图片缩略图
	*/
	function downloadThumbnail($uuid,$shareSecret){
		$url=$this->url.'chatfiles/'.$uuid;
		$header = array("share-secret:".$shareSecret,"Accept:application/octet-stream Authorization:Bearer ",$this->getToken(),"thumbnail:true");
		$result=$this->postCurl($url,'',$header,'GET');
		$filename = md5(time().mt_rand(10, 99))."th.png"; //新图片名称
		if(!file_exists("resource/down")){
			//mkdir("../image/down");
			mkdirs("resource/down/");
		}
		
		$file = @fopen("resource/down/".$filename,"w+");//打开文件准备写入
		@fwrite($file,$result);//写入
		fclose($file);//关闭
		return $filename;
	}
	 
	
	
	//--------------------------------------------------------发送消息
	/*
		发送文本消息
	*/
	function sendText($from="admin",$target_type,$target,$content,$ext){
		$url=$this->url.'messages';
		$body['target_type']=$target_type;
		$body['target']=$target;
		$options['type']="txt";
		$options['msg']=$content;
		$body['msg']=$options;
		$body['from']=$from;
		$body['ext']=$ext;
		//$b=json_encode($body);
		$header=$this->getHeader();
		$result=$this->postCurl($url,$body,$header);
		return $result;
	}
	/*
		发送透传消息
	*/
	function sendCmd($from="admin",$target_type,$target,$action,$ext){
		$url=$this->url.'messages';
		$body['target_type']=$target_type;
		$body['target']=$target;
		$options['type']="cmd";
		$options['action']=$action;
		$body['msg']=$options;
		$body['from']=$from;
		$body['ext']=$ext;
		//$b=json_encode($body);
		$header=$this->getHeader();	
		//$b=json_encode($body,true);
		$result=$this->postCurl($url,$body,$header);
		return $result;
	}
	/*
		发图片消息
	*/ 
	function sendImage($filePath,$from="admin",$target_type,$target,$filename,$ext){
		$result=$this->uploadFile($filePath);
		$uri=$result['uri'];
		$uuid=$result['entities'][0]['uuid'];
		$shareSecret=$result['entities'][0]['share-secret'];
		$url=$this->url.'messages';
		$body['target_type']=$target_type;
		$body['target']=$target;
		$options['type']="img";
		$options['url']=$uri.'/'.$uuid;
		$options['filename']=$filename;
		$options['secret']=$shareSecret;
		$options['size']=array(
			"width"=>480,
			"height"=>720
		);
		$body['msg']=$options;
		$body['from']=$from;
		$body['ext']=$ext;
		//$b=json_encode($body);
		$header=$this->getHeader();	
		//$b=json_encode($body,true);
		$result=$this->postCurl($url,$body,$header);
		return $result;
	}
	/*
		发语音消息
	*/
	function sendAudio($filePath,$from="admin",$target_type,$target,$filename,$length,$ext){
		$result=$this->uploadFile($filePath);
		$uri=$result['uri'];
		$uuid=$result['entities'][0]['uuid'];
		$shareSecret=$result['entities'][0]['share-secret'];
		$url=$this->url.'messages';
		$body['target_type']=$target_type;
		$body['target']=$target;
		$options['type']="audio";
		$options['url']=$uri.'/'.$uuid;
		$options['filename']=$filename;
		$options['length']=$length;
		$options['secret']=$shareSecret;
		$body['msg']=$options;
		$body['from']=$from;
		$body['ext']=$ext;
		//$b=json_encode($body);
		$header=$this->getHeader();	
		//$b=json_encode($body,true);
		$result=$this->postCurl($url,$body,$header);
		return $result;}
	/*
		发视频消息
	*/
	function sendVedio($filePath,$from="admin",$target_type,$target,$filename,$length,$thumb,$thumb_secret,$ext){
	$result=$this->uploadFile($filePath);
		$uri=$result['uri'];
		$uuid=$result['entities'][0]['uuid'];
		$shareSecret=$result['entities'][0]['share-secret'];
		$url=$this->url.'messages';
		$body['target_type']=$target_type;
		$body['target']=$target;
		$options['type']="video";
		$options['url']=$uri.'/'.$uuid;
		$options['filename']=$filename;
		$options['thumb']=$thumb;
		$options['length']=$length;
		$options['secret']=$shareSecret;
		$options['thumb_secret']=$thumb_secret;
		$body['msg']=$options;
		$body['from']=$from;
		$body['ext']=$ext;
		//$b=json_encode($body);
		$header=$this->getHeader();	
		//$b=json_encode($body,true);
		$result=$this->postCurl($url,$body,$header);
		return $result;
	}
	/*
	发文件消息
	*/
	function sendFile($filePath,$from="admin",$target_type,$target,$filename,$length,$ext){
		$result=$this->uploadFile($filePath);
		$uri=$result['uri'];
		$uuid=$result['entities'][0]['uuid'];
		$shareSecret=$result['entities'][0]['share-secret'];
		$url=$GLOBALS['base_url'].'messages';
		$body['target_type']=$target_type;
		$body['target']=$target;
		$options['type']="file";
		$options['url']=$uri.'/'.$uuid;
		$options['filename']=$filename;
		$options['length']=$length;
		$options['secret']=$shareSecret;
		$body['msg']=$options;
		$body['from']=$from;
		$body['ext']=$ext;
		//$b=json_encode($body);
		$header=$this->getHeader();	
		//$b=json_encode($body,true);
		$result=postCurl($url,$body,$header);
		return $result;
	}
	//-------------------------------------------------------------群组操作
	
	/*
		获取app中的所有群组----不分页
	*/
	function getGroups($limit=0){
		if(!empty($limit)){
			$url=$this->url.'chatgroups?limit='.$limit;
		}else{
			$url=$this->url.'chatgroups';
		}
		
		$header=$this->getHeader();
		$result=$this->postCurl($url,'',$header,"GET");
		return $result;
	}
	/*
		获取app中的所有群组---分页
	*/
	function getGroupsForPage($limit=0,$cursor=''){
		$url=$this->url.'chatgroups?limit='.$limit.'&cursor='.$cursor;
		$header=$this->getHeader();
		$result=$this->postCurl($url,'',$header,"GET");
		
		if(!empty($result["cursor"])){
			$cursor=$result["cursor"];
			$this->writeCursor("groupfile.txt",$cursor);
		}
		//var_dump($GLOBALS['cursor'].'00000000000000');
		return $result;
	}
	/*
		获取一个或多个群组的详情
	*/
	function getGroupDetail($group_ids){
		$g_ids=implode(',',$group_ids);
		$url=$this->url.'chatgroups/'.$g_ids;
		$header=$this->getHeader();
		$result=$this->postCurl($url,'',$header,'GET');
		return $result;
	}
	/*
		创建一个群组
	*/
	function createGroup($options){
		$url=$this->url.'chatgroups';
		$header=$this->getHeader();
		$body=json_encode($options);
		$result=$this->postCurl($url,$body,$header);
		return $result;
	}
	/*
		修改群组信息
	*/
	function modifyGroupInfo($group_id,$options){
		$url=$this->url.'chatgroups/'.$group_id;
		$body=json_encode($options);
		$header=$this->getHeader();
		$result=$this->postCurl($url,$body,$header,'PUT');
		return $result;	
	}
	/*
		删除群组
	*/
	function deleteGroup($group_id){
		$url=$this->url.'chatgroups/'.$group_id;
		$header=$this->getHeader();
		$result=$this->postCurl($url,'',$header,'DELETE');
		return $result;
	}
	/*
		获取群组中的成员
	*/
	function getGroupUsers($group_id){
		$url=$this->url.'chatgroups/'.$group_id.'/users';	
		$header=$this->getHeader();
		$result=$this->postCurl($url,'',$header,'GET');
		return $result;
	}
	/*
		群组单个加人
	*/
	function addGroupMember($group_id,$username){
		$url=$this->url.'chatgroups/'.$group_id.'/users/'.$username;
		$header=$this->getHeader();
		$result=$this->postCurl($url,'',$header);
		return $result;
	}
	/*
		群组批量加人
	*/
	function addGroupMembers($group_id,$usernames){
		$url=$this->url.'chatgroups/'.$group_id.'/users';
		$body=json_encode($usernames);
		$header=$this->getHeader();
		$result=$this->postCurl($url,$body,$header);
		return $result;
	}
	/*
		群组单个减人
	*/
	function deleteGroupMember($group_id,$username){
		$url=$this->url.'chatgroups/'.$group_id.'/users/'.$username;
		$header=$this->getHeader();
		$result=$this->postCurl($url,'',$header,'DELETE');
		return $result;
	}
	/*
		群组批量减人
	*/
	function deleteGroupMembers($group_id,$usernames){
		$url=$this->url.'chatgroups/'.$group_id.'/users';
		$body=json_encode($usernames);
		$header=$this->getHeader();
		$result=$this->postCurl($url,$body,$header,'DELETE');
		return $result;
	}
	/*
		获取一个用户参与的所有群组
	*/
	function getGroupsForUser($username){
		$url=$this->url.'users/'.$username.'/joined_chatgroups';
		$header=$this->getHeader();
		$result=$this->postCurl($url,'',$header,'GET');
		return $result;
	}
	/*
		群组转让
	*/
	function changeGroupOwner($group_id,$options){
		$url=$this->url.'chatgroups/'.$group_id;
		$body=json_encode($options);
		$header=$this->getHeader();
		$result=$this->postCurl($url,$body,$header,'PUT');
		return $result;
	}
	/*
		查询一个群组黑名单用户名列表
	*/
	function getGroupBlackList($group_id){
		$url=$this->url.'chatgroups/'.$group_id.'/blocks/users';
		$header=$this->getHeader();
		$result=$this->postCurl($url,'',$header,'GET');
		return $result;
	}
	/*
		群组黑名单单个加人
	*/
	function addGroupBlackMember($group_id,$username){
		$url=$this->url.'chatgroups/'.$group_id.'/blocks/users/'.$username;
		$header=$this->getHeader();
		$result=$this->postCurl($url,'',$header);
		return $result;
	}
	/*
		群组黑名单批量加人
	*/
	function addGroupBlackMembers($group_id,$usernames){
		$url=$this->url.'chatgroups/'.$group_id.'/blocks/users';
		$body=json_encode($usernames);
		$header=$this->getHeader();
		$result=$this->postCurl($url,$body,$header);
		return $result;
	}
	/*
		群组黑名单单个减人
	*/
	function deleteGroupBlackMember($group_id,$username){
		$url=$this->url.'chatgroups/'.$group_id.'/blocks/users/'.$username;
		$header=$this->getHeader();
		$result=$this->postCurl($url,'',$header,'DELETE');
		return $result;
	}
	/*
		群组黑名单批量减人
	*/
	function deleteGroupBlackMembers($group_id,$usernames){
		$url=$this->url.'chatgroups/'.$group_id.'/blocks/users';
		$body=json_encode($usernames);
		$header=$this->getHeader();
		$result=$this->postCurl($url,$body,$header,'DELETE');
		return $result;
	}
	//-------------------------------------------------------------聊天室操作
	/*
		创建聊天室
	*/
	function createChatRoom($options){
		$url=$this->url.'chatrooms';
		$header=$this->getHeader();
		$body=json_encode($options);
		$result=$this->postCurl($url,$body,$header);
		return $result;
	}
	/*
		修改聊天室信息
	*/
	function modifyChatRoom($chatroom_id,$options){
		$url=$this->url.'chatrooms/'.$chatroom_id;
		$body=json_encode($options);
		$header=$this->getHeader();
		$result=$this->postCurl($url,$body,$header,'PUT');
		return $result;	
	}
	/*
		删除聊天室
	*/
	function deleteChatRoom($chatroom_id){
		$url=$this->url.'chatrooms/'.$chatroom_id;
		$header=$this->getHeader();
		$result=$this->postCurl($url,'',$header,'DELETE');
		return $result;
	}
	/*
		获取app中所有的聊天室
	*/
	function getChatRooms(){
		$url=$this->url.'chatrooms';
		$header=$this->getHeader();
		$result=$this->postCurl($url,'',$header,"GET");
		return $result;
	}
	
	/*
		获取一个聊天室的详情
	*/
	function getChatRoomDetail($chatroom_id){
		$url=$this->url.'chatrooms/'.$chatroom_id;
		$header=$this->getHeader();
		$result=$this->postCurl($url,'',$header,'GET');
		return $result;
	}
	/*
		获取一个用户加入的所有聊天室
	*/
	function getChatRoomJoined($username){
		$url=$this->url.'users/'.$username.'/joined_chatrooms';
		$header=$this->getHeader();
		$result=$this->postCurl($url,'',$header,'GET');
		return $result;
	}
	/*
		聊天室单个成员添加
	*/
	function addChatRoomMember($chatroom_id,$username){
		$url=$this->url.'chatrooms/'.$chatroom_id.'/users/'.$username;
		$header=$this->getHeader();
		$result=$this->postCurl($url,'',$header);
		return $result;
	}
	/*
		聊天室批量成员添加
	*/
	function addChatRoomMembers($chatroom_id,$usernames){
		$url=$this->url.'chatrooms/'.$chatroom_id.'/users';
		$body=json_encode($usernames);
		$header=$this->getHeader();
		$result=$this->postCurl($url,$body,$header);
		return $result;
	}
	/*
		聊天室单个成员删除
	*/
	function deleteChatRoomMember($chatroom_id,$username){
		$url=$this->url.'chatrooms/'.$chatroom_id.'/users/'.$username;
		$header=$this->getHeader();
		$result=$this->postCurl($url,'',$header,'DELETE');
		return $result;
	}
	/*
		聊天室批量成员删除
	*/
	function deleteChatRoomMembers($chatroom_id,$usernames){
		$url=$this->url.'chatrooms/'.$chatroom_id.'/users';
		$body=json_encode($usernames);
		$header=$this->getHeader();
		$result=$this->postCurl($url,$body,$header,'DELETE');
		return $result;
	}
	//-------------------------------------------------------------聊天记录
	
	/*
		导出聊天记录----不分页
	*/
	function getChatRecord($ql){
		if(!empty($ql)){
			$url=$this->url.'chatmessages?ql='.$ql;
		}else{
			$url=$this->url.'chatmessages';
		}
		$header=$this->getHeader();
		$result=$this->postCurl($url,'',$header,"GET");
		return $result;
	}
	/*
		导出聊天记录---分页
	*/
	function getChatRecordForPage($ql,$limit=0,$cursor){
		if(!empty($ql)){
			$url=$this->url.'chatmessages?ql='.$ql.'&limit='.$limit.'&cursor='.$cursor;
		}
		$header=$this->getHeader();
		$result=$this->postCurl($url,'',$header,"GET");
		$cursor=$result["cursor"];
		$this->writeCursor("chatfile.txt",$cursor);
		//var_dump($GLOBALS['cursor'].'00000000000000');
		return $result;
	}
	
	/**
	 *$this->postCurl方法
	 */
	function postCurl($url,$postData,$headers,$type="POST"){
		$curl = new Curl();
        $curl->setUserAgent('curl/7.35.0');
        $curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);
        $curl->setOpt(CURLOPT_SSL_VERIFYHOST, false);
        $curl->setOpt(CURLOPT_RETURNTRANSFER, 1);
        foreach ($headers as $header) {
            $curl->setHeader($header['key'], $header['value']);
        }
        switch ($type) {
        case 'POST': {
            $curl->post($url, $postData);
            break;
        }
        case 'GET': {
            $curl->get($url);
            break;
        }
        case 'PUT': {
            $curl->put($url,$postData);
            break;
        }
        case 'DELETE': {
            $curl->delete($url);
            break;
        }
        }
        $curl->close();
        if (self::$debug) {
            echo "return: {$curl->rawResponse} \n";
        }
		$rawResponse = json_decode($curl->rawResponse,true);
		$rawResponse['errorCode'] = $curl->errorCode;
        return json_encode($rawResponse);
	}
}
?>