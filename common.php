<?php
//使用smarty模板的通用文件
include WEB_T."/Smarty.class.php";
$tpl =new Smarty();
$tpl->template_dir = WEB_Tpl.'/'.$tpl_dir;
$tpl->compile_dir = WEB_T . "templates_c/";
$tpl->config_dir = WEB_T . "/configs/";
$tpl->cache_dir =WEB_T ."/caches/";
if($web_cache_time>0){
	$tpl->caching=true;
	$web_cache_time=(int)$web_cache_time;
	$tpl->cache_lifetime =$web_cache_time;
}else{
	$tpl->caching=false;
	$tpl->cache_lifetime =0;
}
$tpl->left_delimiter = '<{';
$tpl->right_delimiter = '}>';

$templeturl=$web_url.WEB_TplPath; //模板url
$templetdir=$tpl_dir; //模板目录名
$templetpath=WEB_TplPath.'/'.$tpl_dir; //模板路径

$tpl->assign('web_templeturl',$templeturl);
$tpl->assign('web_templetdir',$templetdir);
$tpl->assign('web_templetpath',$templetpath);
$tpl->assign('web_url_surfix',$web_url_surfix);
$tpl->assign('web_url',$web_url);
$tpl->assign('web_name',$web_name);
$tpl->assign('web_code',$web_code);

//产品类别
include WEB_CLASS."/product_class.php";
$pc=new Product(0);
$productClassList=$pc->GetClassList();
$tpl->assign('productClassList',$productClassList);
$pro_class_list_txt=$pc->GetClassListUl($productClassList);
$pro_class_list_txt = $pc->GetClassListUlHtml();
$tpl->assign('pro_class_list_txt',$pro_class_list_txt);
//p_r($productClassList);

include_once WEB_CLASS."/news_class.php";
$news=new News();
$news_class_list=$news->GetClassList(array('id','classname'));
$tpl->assign('news_class_list',$news_class_list);
//p_r($productClassList);
unset($productClassList,$news_class_list);

//友情链接
$flink_art=new Article('{tablepre}flink');
$flink_list=$flink_art->GetList(array(),'','','','id desc');
$tpl->assign('flink_list',$flink_list);
?>