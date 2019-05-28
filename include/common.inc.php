<?php

define('IN_DCR', TRUE); 
define('WEB_INCLUDE', str_replace("\\", '/', dirname(__FILE__) ) );
define('WEB_DR', str_replace("\\", '/', substr(WEB_INCLUDE, 0, -8) ) );
define('WEB_CLASS', WEB_INCLUDE . '/class');
define('WEB_T', WEB_INCLUDE . '/tplengine');
define('WEB_Tpl', WEB_DR . '/templets');
define('WEB_TplPath', '/templets');
define('WEB_DATA', WEB_DR . '/data');
define('WEB_CACHE', WEB_INCLUDE . '/cache');
define('WEB_MYSQL_BAKDATA_DIR', WEB_DR . '/data/databak');

@set_magic_quotes_runtime(0);
$magic_quotes = get_magic_quotes_gpc();

/* 初始化设置 */
@ini_set('memory_limit', '64M');
@ini_set('session.cache_expire', 180);
@ini_set('session.use_trans_sid', 0);
@ini_set('session.use_cookies', 1);
@ini_set('session.auto_start', 0);
@ini_set('display_errors', 1);

//配置文件
require_once(WEB_INCLUDE . '/app.info.php');
require_once(WEB_INCLUDE . '/config.common.php');

if($web_tiaoshi == '0')
{
	error_reporting(0);
}

//sqlite的sqlite_escape_string
function my_sqlite_escape_string($str)
{
	if(!empty($str))
	{
		return str_replace("'", "''", $str);
	}else{
		return '';
	}
}

//检查和注册外部提交的变量
foreach($_REQUEST as $_k=>$_v)
{
	if( strlen($_k)>0 && preg_match('/^(GLOBALS)/i',$_k) )
	{
		exit('Request var not allow!');
	}
}

function _GetRequest(&$svar)
{
	global $db_type, $magic_quotes;
	if(!$magic_quotes)
	{
		//开了转义
		if(is_array($svar))
		{
			foreach($svar as $_k => $_v) $svar[$_k] = _GetRequest($_v);
		}else
		{
			if($db_type == 1)
			{
				$svar = my_sqlite_escape_string($svar);
			}elseif($db_type == 2)
			{
				$svar = addslashes($svar);
			}
		}
	}else
	{
		//没有开转义..兼容sqlite
		if(is_array($svar))
		{
			foreach($svar as $_k => $_v) $svar[$_k] = _GetRequest($_v);
		}else
		{
			if($db_type == 1)
			{
				$svar = stripslashes($svar);
				$svar = my_sqlite_escape_string($svar);
			}
		}
	}
	return $svar;
}

foreach( array('_GET', '_POST', '_COOKIE') as $_request )
{
	foreach($$_request as $_k => $_v) ${$_k} = _GetRequest($_v);
}
unset($_GET, $_POST);

//时区
if(PHP_VERSION > '5.1')
{
	@date_default_timezone_set('PRC');
}

//Session保存路径 不建议手动修改
$session_path = WEB_INCLUDE . "/session";
if(is_writeable($session_path) && is_readable($session_path))
{
	//如果要手动修改session_save_path且后台编辑器用ckeditor的话 请修改include/editor/ckeditor/ckfinder/config.php下的session_save_path;
	session_save_path($session_path);
}

require_once(WEB_INCLUDE . '/config.fields.php');

//用户访问的网站host
$web_clihost = 'http://'.$_SERVER['HTTP_HOST'];

//引入数据库类
require_once(WEB_CLASS . '/class.db.php');

//引入全站程序静态类
require_once(WEB_CLASS . '/class.app.php');

//全局常用函数
require_once(WEB_INCLUDE . '/common.func.php');

//连接数据库
$db = new cls_db($db_type, $db_host, $db_name, $db_pass, $db_table, $db_ut);
$db_host = $db_user = $db_pass = $db_name = NULL;

header('Content-type:text/html;charset=' . $web_code);
//程序版本
$version = $app_version;

?>