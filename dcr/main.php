<?php
include "../include/common.inc.php";
session_start();
include "adminyz.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<HEAD>
<META http-equiv=Content-Type content="text/html; charset=utf-8">
<LINK href="css/admin.css" type="text/css" rel="stylesheet">
</HEAD>
<BODY>
<TABLE cellSpacing=0 cellPadding=0 width="100%" align=center border=0>
  <TR height=28>
    <TD background=images/title_bg1.jpg>当前位置: 后台首页</TD>
  </TR>
  <TR>
    <TD bgColor=#b1ceef height=1></TD>
  </TR>
  <TR height=20>
    <TD background=images/shadow_bg.jpg></TD>
  </TR>
</TABLE>
<TABLE cellSpacing=0 cellPadding=0 width="90%" align=center border=0>
  <TR height=100>
    <TD align=middle width=100><IMG height=100 src="images/admin_p.gif" 
      width=90></TD>
    <TD width=60>&nbsp;</TD>
    <TD><TABLE height=100 cellSpacing=0 cellPadding=0 width="100%" border=0>
        <TR>
          <TD>当前时间：<?php echo date('Y-m-d H:i:s'); ?></TD>
        </TR>
        <TR>
          <TD style="FONT-WEIGHT: bold; FONT-SIZE: 16px"><?php echo $admin_u; ?></TD>
        </TR>
        <TR>
          <TD>欢迎进入网站管理中心！</TD>
        </TR>
      </TABLE></TD>
  </TR>
  <TR>
    <TD colSpan=3 height=10></TD>
  </TR>
</TABLE>
<TABLE cellSpacing=0 cellPadding=0 width="95%" align=center border=0>
  <TR height=20>
    <TD></TD>
  </TR>
  <TR height=22>
    <TD style="PADDING-LEFT: 20px; FONT-WEIGHT: bold; COLOR: #ffffff" background=images/title_bg2.jpg>快捷操作</TD>
  </TR>
  <TR bgColor=#ecf4fc height=12>
    <TD></TD>
  </TR>
</TABLE>
<TABLE cellSpacing=2 cellPadding=5 width="95%" align=center border=0 bgcolor="#ecf4fc">
  <TR>
    <TD width=100 align=right bgcolor="#FFFFFF">导航条：</TD>
    <TD bgcolor="#FFFFFF" style="COLOR: #880000"><a href="menu_list.php">导航条列表</a> <a href="menu_edit.php?action=add">添加导航条</a></TD>
  </TR>
  <TR>
    <TD align=right bgcolor="#FFFFFF">产品中心：</TD>
    <TD bgcolor="#FFFFFF" style="COLOR: #880000"><a href="product_edit.php?action=add">添加产品</a> <a href="product_list.php">产品列表</a> <a href="product_class_edit.php?action=add">添加产品类</a> <a href="product_class_list.php">产品类列表</a></TD>
  </TR>
  <TR>
    <TD align=right bgcolor="#FFFFFF">新闻中心：</TD>
    <TD bgcolor="#FFFFFF" style="COLOR: #880000"><a href="news_edit.php?action=add">添加新闻</a> <a href="news_list.php">新闻列表</a> <a href="news_class_edit.php?action=add">添加新闻类</a> <a href="news_class_edit.php?action=add">新闻类列表</a></TD>
  </TR>
</TABLE>
<TABLE cellSpacing=0 cellPadding=0 width="95%" align=center border=0>
  <TR height=20>
    <TD></TD>
  </TR>
  <TR height=22>
    <TD style="PADDING-LEFT: 20px; FONT-WEIGHT: bold; COLOR: #ffffff" background=images/title_bg2.jpg>您的相关信息</TD>
  </TR>
  <TR bgColor=#ecf4fc height=12>
    <TD></TD>
  </TR>
</TABLE>
<?php
	$my_info = $cls_member_admin->get_info();
?>
<TABLE cellSpacing=2 cellPadding=5 width="95%" align=center border=0 bgcolor="#ecf4fc">
  <TR>
    <TD width=100 align=right bgcolor="#FFFFFF">登陆帐号：</TD>
    <TD bgcolor="#FFFFFF" style="COLOR: #880000"><?php echo $my_info['username']; ?></TD>
  </TR>
  <TR>
    <TD align=right bgcolor="#FFFFFF">登陆次数：</TD>
    <TD bgcolor="#FFFFFF" style="COLOR: #880000"><?php echo $my_info['logincount']; ?></TD>
  </TR>
  <TR>
    <TD align=right bgcolor="#FFFFFF">上线时间：</TD>
    <TD bgcolor="#FFFFFF" style="COLOR: #880000"><?php echo $my_info['logintime']; ?></TD>
  </TR>
  <TR>
    <TD align=right bgcolor="#FFFFFF">IP地址：</TD>
    <TD bgcolor="#FFFFFF" style="COLOR: #880000"><?php echo $my_info['loginip']; ?></TD>
  </TR>
  <TR>
    <TD align=right bgcolor="#FFFFFF">网站开发QQ：</TD>
    <TD bgcolor="#FFFFFF" style="COLOR: #880000">335759285(模板开发，程序开发)</TD>
  </TR>
</TABLE>
<TABLE cellSpacing=0 cellPadding=0 width="95%" align=center border=0>
  <TR height=20>
    <TD></TD>
  </TR>
  <TR height=22>
    <TD style="PADDING-LEFT: 20px; FONT-WEIGHT: bold; COLOR: #ffffff" background=images/title_bg2.jpg>程序相关信息</TD>
  </TR>
  <TR bgColor=#ecf4fc height=12>
    <TD></TD>
  </TR>
</TABLE>
<TABLE cellSpacing=2 cellPadding=5 width="95%" align=center border=0 bgcolor="#ecf4fc">
  <TR>
    <TD width=100 align=right bgcolor="#FFFFFF">程序名：</TD>
    <TD bgcolor="#FFFFFF" style="COLOR: #880000"><?php echo $app_name; ?></TD>
  </TR>
  <TR>
    <TD align=right bgcolor="#FFFFFF">当前版本号：</TD>
    <TD bgcolor="#FFFFFF" style="COLOR: #880000"><?php echo $app_version; ?></TD>
  </TR>
</TABLE>
<TABLE cellSpacing=0 cellPadding=0 width="95%" align=center border=0>
  <TR height=20>
    <TD></TD>
  </TR>
  <TR height=22>
    <TD style="PADDING-LEFT: 20px; FONT-WEIGHT: bold; COLOR: #ffffff" background=images/title_bg2.jpg>程序最新动态</TD>
  </TR>
  <TR bgColor=#ecf4fc height=12>
    <TD></TD>
  </TR>
</TABLE>
<TABLE cellSpacing=2 cellPadding=5 width="95%" align=center border=0 bgcolor="#ecf4fc">
  <TR>
    <TD colspan="2" bgcolor="#FFFFFF"><iframe frameborder="0" scrolling="no"  src="http://www.dcrcms.com/dcr_qy.php?dbtype=<?php echo $db_type; ?>&version=<?php echo $app_version; ?>" style="width:100%; height:35px;"></iframe></TD>
  </TR>
</TABLE>
</BODY>
</HTML>