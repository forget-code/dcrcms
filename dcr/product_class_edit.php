<?php
require_once("../include/common.inc.php");
session_start();
require_once("adminyz.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><HEAD>
<META http-equiv=Content-Type content="text/html; charset=utf-8">
<LINK href="css/admin.css" type="text/css" rel="stylesheet">
<?php include "admin_common.php"; ?>
<script type='text/javascript'>
function check(){
	if($("#classname").val().length==0){
		show_msg('请输入产品分类名');
		return false;
	}
}
</script>
</HEAD>
<BODY>
<TABLE cellSpacing=0 cellPadding=0 width="100%" align=center border=0>
  <TR height=28>
    <TD background=images/title_bg1.jpg>当前位置: <a href="main.php">后台首页</a>&gt;&gt;添加产品分类</TD></TR>
  <TR>
    <TD bgColor=#b1ceef height=1></TD></TR></TABLE>
<TABLE cellSpacing=0 cellPadding=0 width="95%" align=center border=0>
  <TR height=20>
    <TD></TD></TR>
  <TR height=22>
    <TD style="PADDING-LEFT: 20px; FONT-WEIGHT: bold; COLOR: #ffffff" 
    align=middle background=images/title_bg2.jpg>添加产品分类</TD></TR>
  <TR bgColor=#ecf4fc height=12>
    <TD></TD></TR>
  </TABLE>
<?php
	require_once(WEB_CLASS . "/class.product.php");
	$cls_pro = new cls_product();
	if($action=='add')
	{
	}else
	{
		$action = 'modify';
		$id = isset($id) ? (int)$id : 0;
		if($id!=0)
		{
			$product_class_info = $cls_pro-> get_class_info($id);
		}else{
			show_msg('您没有选择要修改的文档');
		}
	}
?>
<form action="product_class_action.php" method="post" onsubmit="return check();">
<input type="hidden" name="action" id="action" value="<?php echo $action; ?>">
<input type="hidden" name="id" id="id" value="<?php echo $product_class_info['id']; ?>">
<table width="95%" align="center" border="0" cellspacing="1" cellpadding="5" bgcolor="#4776BE" class="itemtable">
    <tr>
      <td width="18%" align="right" bgcolor="#FFFFFF">分类名：</td>
      <td width="82%" bgcolor="#FFFFFF"><input name="classname" type="text" id="classname" value="<?php echo $product_class_info['classname']; ?>" />
        <span class="txtRed">*</span>(20个字以内)</td>
      </tr>
    <tr>
      <td align="right" bgcolor="#FFFFFF">上级分类：</td>
      <td bgcolor="#FFFFFF">
      <select name="parentid" id="parentid">
      <option>顶级分类</option>
    <?php
		$product_class_list=$cls_pro->get_class_list();
		$cls_pro-> get_class_list_select($product_class_list, $product_class_info['parentid']);
	?></select></td>
    </tr>
    <tr>
      <td align="right" bgcolor="#FFFFFF">排序：</td>
      <td bgcolor="#FFFFFF"><input name="orderid" type="text" id="orderid" value="<?php echo $product_class_info['orderid']; ?>" /></td>
    </tr>
    <tr>
      <td align="right" bgcolor="#FFFFFF">分类描述：</td>
      <td bgcolor="#FFFFFF"><textarea name="classdescription" cols="80" rows="5" id="classdescription"><?php echo $product_class_info['classdescription']; ?></textarea></td>
    </tr>
    <tr>
      <td colspan="2" align="center" bgcolor="#FFFFFF"><input type="submit" name="button" id="button" value="<?php if($action=='add'){echo '添加分类';}else{echo '编辑分类';} ?>" /></td>
    </tr>
  </table>
 </form>
 </BODY></HTML>