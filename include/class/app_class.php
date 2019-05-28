<?php
defined('IN_DCR') or exit('No permission.'); 

/**
* 程序信息的类，比如获取一个编辑器 获取程序信息等全局工厂模式的类.
* 全是静态类
* @author 我不是稻草人 www.dcrcms.com
* @version 1.0
* @copyright 2006-2010
* @package class
*/
class APP{
	/**
	 * 函数GetEditor，获取一个编辑器 并且会输出这个编辑器 目前支持ckeditor kindeditor
	 * @param string $editor_name 编辑器名
	 * @param string $default_value 编辑内默认值
	 * @param string $editor_width 编辑器宽 以px为单位
	 * @param string $editor_height 编辑器高 以px为单位
	 * @param string $daohang 菜单样式 1为简单 2为全部
	 * @return true 返回true
	 */
	public static function GetEditor($editor_name,$default_value='',$editor_width='930',$editor_height='500',$daohang=1){
		global $web_url,$web_editor;
		if($web_editor=='ckeditor')
		{
			$editor_t="<script src=\"".$web_url."/include/editor/$web_editor/ckeditor.js\" type=\"text/javascript\"></script>\r\n<script type=\"text/javascript\" src=\"".$web_url."/include/editor/$web_editor/ckfinder/ckfinder.js\"></script>\r\n<textarea id=\"".$editor_name."\" name=\"".$editor_name."\">".$default_value."</textarea>\r\n<script type=\"text/javascript\">var editor = CKEDITOR.replace('".$editor_name."',{height:'".$editor_height."',width:'".$editor_width."'});CKFinder.SetupCKEditor(editor, \"".$web_url."/include/editor/$web_editor/ckfinder/\");</script>";
		}else if($web_editor=='kindeditor')
		{
			//把宽度和高度换成cols和rows
			$cols=$editor_width/7;
			$rows=$editor_height/20;
			$editor_t="<textarea cols='$cols' rows='$rows' id=\"".$editor_name."\" name=\"".$editor_name."\">".$default_value."</textarea><script charset='utf-8' src=\"".$web_url."/include/editor/$web_editor/kindeditor-min.js\"></script><script>KE.show({id : '$editor_name'});</script>";

		}
		echo $editor_t;
	}
	/**
	 * 函数GetDb，获取一个数据库连接
	 * @return DB 一个db实例
	 */
	public static function GetDb(){
		global $db_type,$host,$name,$pass,$table,$ut;
		return new DB($db_type,$host,$name,$pass,$table,$ut);
	}
	/**
	 * 函数GetArticle，获取一个Article实例
	 * @return Article 一个Article实例
	 */	
	public static function GetArticle($table_name){
		include_once(WEB_CLASS.'article_class.php');
		$art=new Article($table_name);
		return $art;
	}
	/**
	 * 函数GetA，获取一个Article实例 function GetArticle简写版
	 * @return Article 一个Article实例
	 */	
	public static function GetA($table_name){
		return self::GetArticle($table_name);
	}
	/**
	 * 函数GetNews，获取一个News实例
	 * @return News 一个News实例
	 */	
	public static function GetNews(){
		include_once(WEB_CLASS.'news_class.php');
		$news=new News();
		return $news;
	}
	/**
	 * 函数GetProduct，获取一个Product实例
	 * @return Product 一个Product实例
	 */	
	public static function GetProduct(){
		include_once(WEB_CLASS.'product_class.php');
		$o=new Product();
		return $o;
	}
	 
}
?>