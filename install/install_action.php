<?php

define('IN_DCR', TRUE);
include "../include/common.func.php";
include "../include/app.info.php";

define('WEB_INSTALL', str_replace("\\", '/', dirname(__FILE__) ) . '/');
define('WEB_DR', str_replace("\\", '/', substr(WEB_INSTALL, 0, -8) ) );
define('WEB_CLASS', WEB_DR . '/include/class/');

error_reporting(E_ALL || ~E_NOTICE);

header('Content-type:text/html;charset=' . $web_code);
header('cache-control:no-cache;must-revalidate');
?>
<?php
//提示信息开始
$errormsg = array();//错误信息
$back = array('管理后台' => '../dcr/login.htm', '首页' => '../index.php');
//提示信息结束
$db_type = $_POST['db_type'];
$sqlite_table = $_POST['sqlite_table'];
$sqlite_name = $_POST['sqlite_name'];
$sqlite_pass = $_POST['sqlite_pass'];
$action = $_POST['action'];
$db_host = $_POST['host'];
$db_name = $_POST['name'];
$db_pass = $_POST['pass'];
$db_table = $_POST['table'];
$auto_creat_db = $_POST['auto_creat_db'];
$db_ut = $_POST['ut'];
$db_tablepre = $_POST['tablepre'];
$adminuser = $_POST['adminuser'];
$adminpas = $_POST['adminpas'];
$web_url = $_POST['web_url'];
$web_dir = $_POST['web_dir'];
$web_name = $_POST['web_name'];
$web_url_module = $_POST['web_url_module'];

//初始化数据
//web_dir前面加上/
if(!empty($web_dir) && substr($web_dir,0,1) != '/')
{
	$web_dir = '/' . $web_dir;
}
if($action=='install')
{
	//开始安装
	if($db_type==2){
		$conn=mysql_connect($db_host,$db_name,$db_pass);
		if($conn){
			//如果数据库不存在就创建
			if($auto_creat_db)
			{
				$sql_db_exists = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME='$db_table'";
				$re = mysql_query($sql_db_exists, $conn);
				if(mysql_num_rows ($re) > 0)
				{
				}else
				{
					//不存在 创建
					$creat_db_sql = "create database $db_table";
					if(!mysql_query($creat_db_sql, $conn))
					{
						show_msg('自动创建数据库失败，可能您这个用户没有权限或者这个数据库已经存在',2);
					}
				}
			}
			if(mysql_select_db($db_table)){
				mysql_query("SET NAMES '$db_code'");
				//没有错误 开始安装
				//安装表
				$fp = fopen(dirname(__FILE__).DIRECTORY_SEPARATOR.'sql_table.txt','r');
				if(!$fp){
					$msg[]='读取安装文件(sql_table.txt)错误！';
					show_msg($msg,2);
				}
				while(!feof($fp)){
					 $line = rtrim(fgets($fp,1024));
					 if(ereg(";$",$line)){
						 $query .= $line."\n";
						 $query = str_replace('{tablepre}',$db_tablepre,$query);
						 $query=str_replace('[db_code]',$db_code,$query);
						 $rs = mysql_query($query,$conn);						 
						 if(mysql_errno())
						 {
							 echo $query . '发现错误:<br>' . mysql_error() . '<hr>';
						 }
						 $query='';
					 }
					 else if(!ereg("^(//|--)",$line)){
						   $query .= $line;
					 }
				}
				fclose($fp);
				
				//初始化数据
				init_data();				
				
				//写数据库配置
				write_db_config($db_type, $db_host, $db_name, $db_pass, $db_table, $db_tablepre);
				$rs = write_common_config();
				
				//安装收尾
				install_end();
				
				if($rs == 'r2'){
					$msg[] = '程序安装成功，请选择下面的链接进入到相关页面！';
					show_msg($msg, 1, $back);
				}
				if($rs == 'r3'){
					$msg[] = '更新配置失败：写配置文件时出错,请检查相关文件(include/config.common.php include/config.db.php)的写入权限！';
					show_msg($msg, 2);
				}
			}else{
				$msg[] = '数据库信息有误！';
				show_msg($msg, 2);
			}
		}else
		{
			$msg[] = '数据库信息有误！';
			show_msg($msg, 2);	
		}
	}elseif($db_type == 1)
	{
		$db_path = dirname(__FILE__) . '/../data/' . $sqlite_table;
		$pdo = new PDO("sqlite:$db_path");
		if($pdo)
		{
			//没有错误 开始安装
			//安装表
			$pdo->exec("create table '<?php'(a)");
			$fp = fopen(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'sql_table.txt', 'r');
			if(!$fp)
			{
				$msg[] = '读取安装文件(sql_table.txt)错误！';
					show_msg($msg, 2);
				}
				while(!feof($fp)){
					 $line = rtrim(fgets($fp, 1024));
					 if(ereg(";$",$line))
					 {
						 $query .= $line."\n";
						 $query = str_replace('{tablepre}', $db_tablepre, $query);
						 $query = str_ireplace(' unsigned', '', $query);
						 $query = str_ireplace(' ON UPDATE CURRENT_TIMESTAMP', '', $query);
						 $query = str_ireplace(' NOT NULL auto_increment', '', $query);
						 //$query = str_replace(' PRIMARY KEY  (`id`)','',$query);
						 $query = str_ireplace(' ENGINE=MyISAM DEFAULT CHARSET=[db_code]', '', $query);
						 $query = str_ireplace(' ENGINE=MyISAM', '', $query);
						 $query = str_ireplace(' DEFAULT CHARSET=[db_code]', '', $query);
						 //echo $query.'<hr>';
						 //echo $query;
						 $rs = $pdo->exec($query);
						 //echo $pdo->
						 //p_r($pdo->errorInfo());
						 //exit;
						 $query = '';
					 }
					 else if(!ereg("^(//|--)", $line)){
						   $query .= $line;
					 }
				}
				fclose($fp);
				
				//初始化数据
				init_data();
				
				//写配置
				write_db_config($db_type, $sqlite_table, $db_name, $db_pass, $db_table, $db_tablepre);
				$rs = write_common_config();
				
				//收尾
				install_end();
				
				if($rs=='r2'){
					$msg[]='程序安装成功，请选择下面的链接进入到相关页面！';
					show_msg($msg,1,$back);
				}
				if($rs=='r3'){
					$msg[]='更新配置失败：写配置文件时出错,请检查相关文件(include/config.common.php include/config.db.php)的写入权限！';
					show_msg($msg,2);
				}
		}else{
			$msg[]='数据库信息有误！';
			show_msg($msg,2);	
		}
	}
}elseif($action=='checkconnect_ajax'){
	$conn=mysql_connect($db_host,$db_name,$db_pass);
	if($conn){
		echo '数据库信息正确！';
	}else{
		echo '数据库信息有误！';
	}
}else if('get_db_list' == $action)
{
	$conn = mysql_connect($db_host,$db_name,$db_pass);
	if($conn){
		echo '<select id="dbs" name="dbs" onChange="show_db_name()">';
		$sql = 'show databases';
		$result = mysql_query($sql,$conn);
		while($rs = mysql_fetch_array($result))
		{
			echo "<option value='" . $rs['0'] . "'>" . $rs['0'] . "</option>";
		}
		echo '</select>';
	}else{
		echo '数据库信息有误！';
	}
}
function init_data()
{	
	//初始化数据
	global $pdo, $db_type, $db_tablepre, $adminuser, $adminpas;
	$fp = fopen(dirname(__FILE__).DIRECTORY_SEPARATOR.'sql_data.txt','r');
	if(!$fp){
		$msg[]='读取安装文件(sql_data.txt)错误！';
		show_msg($msg,2);
	}
	while(!feof($fp))
	{
		$line = rtrim(fgets($fp, 1024));
		if(ereg(";$", $line))
		{
			$query .= $line. "\n";
			$query = str_replace('{tablepre}', $db_tablepre, $query);
			$query = str_replace('[db_code]', $db_code, $query);
			$query = trim($query);
			if('1' == $db_type)
			{
				$rs = $pdo->exec($query);
			}else if('2' == $db_type)
			{
				$rs = mysql_query($query);
				if(mysql_errno())
				{
					echo $query . '发现错误:<br>' . mysql_error() . '<hr>';
				}
			}
			$query = '';
		}
		else if(!ereg("^(//|--)", $line))
		{
			$query .= $line;
		}
	}
	fclose($fp);
	//插入管理员
	$sql="insert into $db_tablepre" . "admin(username,password) values('$adminuser','" . jiami($adminpas) . "')";
	
	if('1' == $db_type)
	{
		$rs = $pdo->exec($sql);
	}else if('2' == $db_type)
	{
		$rs = mysql_query($sql);
		if(mysql_errno())
		{
			echo $query . '发现错误:<br>' . mysql_error() . '<hr>';
		}
		
	}
}
function write_common_config()
{
	//写入通用配置
	global $web_url, $web_dir, $web_url_module, $web_name;
	require_once("../include/class/class.config.php");
	$config = new cls_config();
	$config_info = array(
					  'web_url'=> $web_url,
					  'web_dir'=> $web_dir,
					  'web_url_module'=> $web_url_module,
					  'web_tiaoshi'=> '0',
					  'web_name'=> $web_name
					  );
	return $config->modify($config_info, '../include/config.common.php');
}
function write_db_config($db_type, $db_host, $db_name, $db_pass, $db_table, $db_tablepre)
{	
	//写入数据库配置
	global $db_code;
	$db_config = "";
	$db_config .= "<?php\n\n";
	$db_config .= "\$db_type = '" . $db_type . "';\n";
	$db_config .= "\$db_host = '" . $db_host . "';\n";
	$db_config .= "\$db_name = '" . $db_name . "';\n";
	$db_config .= "\$db_pass = '" . $db_pass . "';\n";
	$db_config .= "\$db_table = '" . $db_table . "';\n";
	$db_config .= "\$db_ut = '" . $db_code . "';\n";
	$db_config .= "\$db_tablepre = '" . $db_tablepre . "';\n\n";
	$db_config .= "?>";
	require_once("../include/class/class.file.php");
	$cls_file = new cls_file('../include/config.db.php');
	$cls_file-> set_text($db_config);
	
	return $cls_file-> write();
}
function install_end()
{
	//安装收尾
				
	//把安装文件的名字换了
	@rename('index.php', 'index.php_bak');
}

?>