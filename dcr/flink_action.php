<?php
require_once("../include/common.inc.php");
session_start();
require_once("adminyz.php");

require_once(WEB_CLASS . '/class.data.php');
$flink_data = new cls_data('{tablepre}flink');

$error_msg = array();//错误信息
$back = array('友情链接列表'=>'flink_list.php');

//本页为操作新闻的页面
if($action == 'addflink')
{
		require_once(WEB_CLASS . "/class.upload.php");
		$cls_upload = new cls_upload('logo');
		$file_info = $cls_upload->upload(WEB_DR . "/uploads/flink/", '', array('width'=>$flinklogowidth,'height'=>$flinklogoheight),array());
		
		$logo = $file_info['sl_filename'];
		$info = array('webname'=>$webname,
					   		'logo'=>$logo,
							'weburl'=>$weburl,
							'addtime'=>time(),
							'updatetime'=>time()
							);
		$aid = $flink_data->insert($info);
		if(!$aid)
		{
			show_msg('添加友情链接失败'.mysql_error(), 2, $back);	
		}else{
			$back['继续添加'] = 'flink_edit.php?action=add';
			show_msg('添加友情链接成功', 1, $back);
		}
		//echo mysql_error();
}else if($action == 'editflink')
{
		$info = array('webname'=>$webname,
						'weburl'=>$weburl,
						'updatetime'=>time()
						);
		require_once(WEB_CLASS . "/class.upload.php");
		$cls_upload = new cls_upload('logo');
		$file_info = $cls_upload->upload(WEB_DR . "/uploads/flink/", '', array('width'=>$flinklogowidth,'height'=>$flinklogoheight),array());
		$logo = $file_info['sl_filename'];
		if(strlen($logo)>0)
		{
			$old_info = $flink_data->select_one(array('col'=> 'logo', 'where'=> "id=$id"));
			$old_info = current($old_info);
			@unlink('../uploads/flink/' . $old_info['logo']);
			
			$info['logo'] = basename($logo);
		}
		if($flink_data-> update($info, "id=$id"))
		{
			show_msg('更新链接成功', 1, $back);
		}else
		{
			show_msg('更新链接失败', 2, $back);
		}
}
else if($action=='delflink')
{
	$info = $flink_data->select_one(array('col'=> 'logo', 'where'=> "id=$id"));
	$info = current($info);
	@unlink('../uploads/flink/'.$info['logo']);
	if($flink_data-> delete($id))
	{
		show_msg('删除数据成功', 1, $back);
	}else
	{
		show_msg('删除数据失败', 2, $back);
	}
}else
{
	show_msg('非法操作', 2, $back);
}
?>