<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/9/5
 * Time: 22:05
 */
namespace Admin\Controller;
use Admin\Common\ACPopedom;
use Think\Controller\MasterController;
use Org\Net\Http;

class UploadController extends MasterController {

    public function index(){
        if(IS_POST){
            $fn = I("action");
            $label = I("label");
            $tmpname = $_FILES['postfile']['name'];
            $tmpfile = $_FILES['postfile']['tmp_name'];
            $name= $_FILES['postfile']['name'];
            $size =$_FILES['postfile']['size'];
            foreach($_FILES as $key => $val){
                $files = array("upfile"=>$val);
            }
            $response = UploadLocalImage("","/Attachment/","/upload/",$files);
            $newfile = $response['picpath'];
            echo "<script>
				if(self==top){
					window.opener.$fn('$newfile','$name','$size','$label');
					window.self.close();
				}else{
					window.parent.$fn('$newfile','$name','$size','$label');
				}
		        </script>";
        }else{
            $this->assign("fn",I("fn"));
            $this->assign("label",I("label"));
            $this->display();
        }
    }

    /**
     *
     * 上传图片
     */
    public function upload(){
        foreach($_FILES as $key => $val){
            $files = array("upfile"=>$val);
        }
        $rs = UploadLocalImage("","/Attachment/","/upload/",$files);
        exit(json_encode($rs));
    }
}