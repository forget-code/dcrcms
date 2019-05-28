<?php
session_start();
include "../include/common.inc.php";
include WEB_CLASS."/product_class.php";
include "adminyz.php";

$pro=new Product(0);

//提示信息开始
$errormsg=array();//错误信息
$back=array('产品列表'=>'product_list.php');
//提示信息结束

//本页为操作新闻的页面
if($action=='add'){
	$iserror=false;
	if(empty($title)){		
		$errormsg[]='请填写产品名称';
		$iserror=true;
	}
	if($classid==0){
		$errormsg[]='请选择产品类别';
		$iserror=true;
	}
	if($iserror){
		ShowMsg($errormsg,2);
	}else{
		//没有错误
		//上传产品图片
		include_once(WEB_CLASS."/upload_class.php");
		$upload=new Upload();
		$fileInfo=$upload->UploadFile("logo",WEB_DR."/uploads/product/",'',array('width'=>$prologowidth,'height'=>$prologoheight,'newpic'=>1));
		$logo=basename($fileInfo['sl_filename']);
		$biglogo=basename($fileInfo['filename']);
		$rid=$pro->Add(array('title'=>$title,
					   		 'logo'=>$logo,
					   		 'biglogo'=>$biglogo,
					   		 'classid'=>intval($classid),
							 'istop'=>intval($istop),
					   		 'keywords'=>$keywords,
					   		 'description'=>$description,
					   		 'content'=>$content
							 )
					   );
		if(!$rid){
			$errormsg[]='添加产品失败';
			ShowMsg($errormsg,2);	
		}else{
			$errormsg[]='添加产品成功';
			$back['继续添加']='product_edit.php?action=add';
			ShowMsg($errormsg,1,$back);
		}
	}
}elseif($action=='modify'){	$iserror=false;
	if(empty($title)){		
		$errormsg[]='请填写产品名称';
		$iserror=true;
	}
	if($classid==0){
		$errormsg[]='请选择产品类别';
		$iserror=true;
	}
	if($iserror){
		ShowMsg($errormsg,2);
	}
	$productinfo=array('title'=>$title,
					   'classid'=>intval($classid),
					   'istop'=>intval($istop),
					   'keywords'=>$keywords,
					   'description'=>$description,
					   'content'=>$content
					  );
	include_once(WEB_CLASS."/upload_class.php");
		$upload=new Upload();
	$fileInfo=$upload->UploadFile("logo",WEB_DR."/uploads/product/",$pro->GetLogo($id,false),array('width'=>$prologowidth,'height'=>$prologoheight,'newpic'=>1));
	
	if(strlen($fileInfo)>0){
		$logo=basename($fileInfo['sl_filename']);
		$biglogo=basename($fileInfo['filename']);
		$productinfo['logo']=basename($logo);
		$productinfo['biglogo']=basename($biglogo);
	}
	if($pro->Update($id,$productinfo)){
		$errormsg[]='更新产品成功';
		ShowMsg($errormsg,1,$back);
	}else{
		$errormsg[]='更新产品失败';
		ShowMsg($errormsg,2);
	}	
}elseif($action=='delproduct'){
	$r=$pro->Delete($id);
	if($r==1){
		$errormsg[]='删除数据成功';
		ShowMsg($errormsg,1,$back);
	}elseif($r==3){
		$errormsg[]='删除数据失败:没有选择要删除的产品';
		ShowMsg($errormsg,2,$back);
	}elseif($r==2){
		$errormsg[]='删除数据失败:处理数据库数据时失败';
		ShowMsg($errormsg,2,$back);
	}
}elseif($action=='top'){
	$info=array(
				'istop'=>1
				);
	if($pro->Update($id,$info)){
		$errormsg[]='置顶成功';
		ShowMsg($errormsg,1,$back);
	}else{
		$errormsg[]='置顶失败'.mysql_error();
		ShowMsg($errormsg,2,$back);
	}
}elseif($action=='top_no'){
	$info=array(
				'istop'=>0
				);
	if($pro->Update($id,$info)){
		$errormsg[]='取消置顶成功';
		ShowMsg($errormsg,1,$back);
	}else{
		$errormsg[]='取消置顶失败'.mysql_error();
		ShowMsg($errormsg,2,$back);
	}
}else{
	echo '非法操作？';
}
?>