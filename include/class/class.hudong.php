<?php

defined('IN_DCR') or exit('No permission.'); 

/**
 * 互动信息处理类
 * ===========================================================
 * 版权所有 (C) 2006-2020 我不是稻草人，并保留所有权利。
 * 网站地址: http://www.dcrcms.com
 * ----------------------------------------------------------
 * 这是免费开源的软件；您可以在不用于商业目的的前提下对程序代码
 * 进行修改、使用和再发布。
 * 不允许对程序修改后再进行发布。
 * ==========================================================
 * @author:     我不是稻草人 <junqing124@126.com>
 * @version:    v1.0
 * @package class
 * @since 1.0.8
*/

require_once(WEB_CLASS . '/class.data.php');

class cls_hudong extends cls_data
{
	
	/**
	 * 无需传参数
	 */
	function __construct()
	{
		parent::__construct('{tablepre}hudong');
	}	
	
	/**
	 * 设置类要操作的表
	 * @param string $table 表名
	 * @return true
	 */
	function set_table($table)
	{
		parent:: set_table($table);
	}
	
	/**
	 * 添加互动信息
	 * @param array $info 插入的数据 用数组表示,用$key=>$value来表示列名=>值 如array('title'=>'标题') 表示插入title的值为 标题
	 * @return int 返回值为文档的ID,失败返回0
	 */
	function add($info)
	{
		$this->set_table('{tablepre}hudong');
		
		return parent::insert($info);
	}
	
	 /**
	 * 用来获取互动信息
	 * @param string $type 互动信息类型 0为全部 1为未读 2为已读
	 * @param array $canshu 参数列表：
	 * @param array $canshu['col']        要返回的字段列 以,分隔
	 * @param array $canshu['where']      条件
	 * @param array $canshu['limit']      返回的limit
	 * @param array $canshu['group']      分组grop
	 * @param array $canshu['order']      排序 默认是istop desc,id desc
	 * @return array 返回这个列表数据
	 */
	function get_list($type = 0, $canshu = array())
	{
		$type = (int)$type;
		$where_arr = array();
		if( $type != 0 )
		{
			array_push($where_arr, "type=$type");
		}
		if(!empty($canshu['where']))
		{
			array_push($where_arr, $canshu['where']);
		}
		$where = implode(' and ', $where_arr);
		$canshu['where'] = $where;
		
		return parent::select_ex($canshu);
	}
	
	/**
	 * 删除指定ID数组的所有文章
	 * @param array $idarr 删除的ID数组 比如要删除ID为1，2，3的文档 则为：array(1,2,3)
	 * @return boolean 成功返回true 失败返回false
	 */
	function delete($id_list)
	{
		
		return parent::delete($id_list);		
	}
	
	/**
	 * 获取指定ID的互动信息
	 * @param string|int $id 互动信息的ID
	 * @return array 成功返回id为$id的互动信息的内容 失败返回false
	 */ 
	function get_info($id)
	{
		$canshu['where'] = "id=$id";
		$info = parent::select_one($canshu);
		
		return current($info);
	}
	
	/**
	 * 更新互动信息的类型 1为已读 2为未读
	 * @param string|int $id 互动信息的ID
	 * @param int $type 信息设置为的类型
	 * @return boolean 成功返回true 失败返回false
	 */
	function update_type($id, $type)
	{
		
		return parent::update(array('type'=>$type), "id=$id");
	}
	
	/**
	 * 返回指定类型的信息的数量
	 * @param int $type 信息设置为的类型
	 * @return boolean 成功返回数量 失败返回false
	 */
	function get_num($type = 0)
	{
		$type = (int)$type;
		if( $type != 0 )
		{
			$where="type=$type";
		}
		$info_sum = parent::select_one_ex(array('col'=>'count(id) as sum', 'where'=>$where));
		
		return $info_sum['sum'];
	}
	
	/**
	 * 为互动表单增加字段
	 * @param array $field_info 插入的数据 用数组表示,用$key=>$value来表示列名=>值 如array('title'=>'标题') 表示插入title的值为 标题
	 * @return boolean 成功返回true 失败返回false
	 */
	function add_field($field_info)
	{
		
		if($field_info['dtype'] == 'multitext')
		{
			$add_col_sql = "column `" . $field_info['fieldname'] . "` MEDIUMTEXT";
		}else
		{
			$add_col_sql = "column `" . $field_info['fieldname'] . "` varchar(" . $field_info['maxlength'] . ") not null default ''";
		}
		
		$sql_alter = "alter table {tablepre}hudong add $add_col_sql";
		if(parent::execute_none_query($sql_alter))
		{
			//return true;
			$this->set_table('{tablepre}hudong_field');
			
			return parent::insert($field_info);
		}else
		{
			
			return false;
		}
	}
	
	/**
	 * 更新互动表单字段
	 * @param int|string $id 在hudong_field的ID
	 * @param array $field_info 更新的数据 用数组表示,用$key=>$value来表示列名=>值 如array('title'=>'标题') 表示插入title的值为 标题
	 * @return boolean 成功返回true 失败返回false
	 */
	function update_field($id, $field_info)
	{
		//修改字段
		$field_name = $this->get_field_name($id);
		if(empty($field_name))
		{
			return false;
		}else
		{
			if($field_info['dtype'] == 'multitext')
			{
				$add_col_sql = "column `" . $field_name . "` MEDIUMTEXT";
			}else
			{
				$add_col_sql = "column `" . $field_name . "` varchar(".$field_info['maxlength'].") not null default ''";
			}
			$sql_alter = "ALTER TABLE {tablepre}hudong MODIFY $add_col_sql";
			//echo $sql_alter;
			parent::execute_none_query($sql_alter);

			$this->set_table('{tablepre}hudong_field');
				
			return parent::update($field_info, "id=$id");
		}
	}
	
	/**
	 * 删除互动表单字段
	 * @param int|string $id 在hudong_field的ID
	 * @return boolean 成功返回true 失败返回false
	 */
	function delete_field($id)
	{
		$field_name = $this->get_field_name($id);
		
		if( empty($field_name) )
		{
			return false;
		}else
		{
			$sql_alter = "ALTER TABLE {tablepre}hudong DROP COLUMN `".$field_name."`";
			parent::execute_none_query($sql_alter);
			
			$this->set_table('{tablepre}hudong_field');
			parent::delete($id);
			//echo parent::get_last_sql();
			
			return parent::delete($id);
		}
	}
	
	/**
	 * 返回互动表单的自字义列
	 * @param array $canshu 参数列表：
	 * @param array $canshu['col']        要返回的字段列 以,分隔
	 * @param array $canshu['where']      条件
	 * @param array $canshu['limit']      返回的limit
	 * @param array $canshu['group']      分组grop
	 * @param array $canshu['order']      排序 默认是istop desc,id desc
	 * @return array 成功返回互动表单的自字义列 失败返回false
	 */
	function get_filed_list( $canshu = array() )
	{
		if(empty($canshu['order']))
		{
			$canshu['order'] = 'orderid';
		}
		$this->set_table('{tablepre}hudong_field');
		
		return parent::select_ex($canshu);
	}
	
	/**
	 * 返回格式化后的互动表单
	 * @param array $canshu 参数列表：
	 * @param array $canshu['col']        要返回的字段列 以,分隔
	 * @param array $canshu['where']      条件
	 * @param array $canshu['limit']      返回的limit
	 * @param array $canshu['group']      分组grop
	 * @param array $canshu['order']      排序 默认是orderid
	 * @return array 返回一个数组 其中arr['itemname']为表单提示文字 inputtxt为生成的input字段HTML
	 */
	function get_format_filed_list($canshu = array())
	{
		if(empty($canshu['order']))
		{
			$canshu['order'] = 'orderid';
		}
		
		$field_list = $this->get_filed_list($canshu);
		$field_format_list = array();
		//加上默认的title
		foreach($field_list as $key=> $value)
		{
			if($value['dtype']=='text')
			{
				$str_t = "<input class='txtbox' name='" . $value['fieldname'] . "' id='" . $value['fieldname'] . "' type='text' maxlength='" . $value['maxlength'] . "' value='" . $value['vdefault'] . "' />";
				$arr_t = array('itemname'=> $value['itemname'], 'inputtxt'=>$str_t);
				$field_format_list[] = $arr_t;
			}
			if($value['dtype'] == 'multitext')
			{
				$str_t = "<textarea name='" . $value['fieldname'] . "' id='" . $value['fieldname'] . "'>" . $value['vdefault'] . "</textarea>";
				$arr_t = array('itemname'=>$value['itemname'], 'inputtxt'=>$str_t);
				$field_format_list[] = $arr_t;
			}
			if($value['dtype'] == 'select')
			{
				$str_t = "<select name='" . $value['fieldname'] . "' id='" . $value['fieldname'] . "'>";
				$v_a = explode(',', $value['vdefault']);
				foreach($v_a as $v_v)
				{
					$str_t .= "<option value='$v_v'>$v_v</option>";
				}
				$str_t .= "</select>";
				$arr_t = array('itemname'=>$value['itemname'], 'inputtxt'=>$str_t);
				$field_format_list[] = $arr_t;
			}
			if($value['dtype'] == 'checkbox')
			{
				$v_a = explode(',', $value['vdefault']);
				$str_t = '';
				foreach($v_a as $v_v)
				{
					$str_t .= " <input type='checkbox' name='" . $value['fieldname'] . "[]' id='" . $value['fieldname'] . "[]' value='$v_v' />$v_v";
				}
				$arr_t = array('itemname'=>$value['itemname'], 'inputtxt'=>$str_t);
				$field_format_list[] = $arr_t;
			}
			if($value['dtype'] == 'radio')
			{
				$v_a = explode(',', $value['vdefault']);
				$str_t = '';
				$set_default = false;//是不是设置了默认值
				foreach($v_a as $v_v)
				{
					if($set_default)
					{
						$str_t .= "<input type='radio' name='" . $value['fieldname'] . "' id='" . $value['fieldname'] . "' value='$v_v' />$v_v";
					}else
					{
						$str_t .= "<input checked type='radio' name='" . $value['fieldname'] . "' id='" . $value['fieldname'] . "' value='$v_v' />$v_v";
						$set_default = true;
					}
				}
				$arr_t = array('itemname'=>$value['itemname'], 'inputtxt'=>$str_t);
				$field_format_list[] = $arr_t;
			}
		}
		
		return $field_format_list;
	}
	
	/**
	 * 根据ID返回指定的互动表单的filename[表单名]
	 * @param int|string $id 在hudong_field的ID
	 * @return string 根据ID返回指定的互动表单的filename[表单名]
	 */
	function get_field_name($id)
	{
		//获取ID为$id的字段名field_name
		parent::set_table('{tablepre}hudong_field');
		$canshu = array('col'=>'fieldname', 'where'=>"id=$id");
		$info = parent::select_one_ex($canshu);
		
		return $info['fieldname'];
	}
	
	/**
	 * 返回单个表单的数据
	 * @param int|string $id 在hudong_field的ID
	 * @param array $col 要返回的字段列 如你要返回id,title为：array('id','title') 如果为array()时返回全部字段
	 * @return array 返回值为这个文档的信息(Array)
	 */
	function get_field_info($id, $col = '*')
	{
		$canshu['where'] = "id=$id";
		$canshu['col'] = $col;
		parent::set_table('{tablepre}hudong_field');
		$info = parent::select_one_ex($canshu);
		
		return $info;
	}
	
	/**
	 * 生成提交的表单 <form></form>这类的
	 * 返回值为生成的form的HTML代码
	 * @return string 生成提交的表单信息
	 */
	function get_field_form()
	{
		$form_info = $this->get_format_filed_list();
		$form_txt = '';
		$form_txt .= "<form method=\"post\" action=\"hudong.php?action=addorder\">\n";
		foreach( $form_info as $key=> $value )
		{
			$form_txt .= $value['itemname'] . ":" . $value['inputtxt'] . "\n";
		}
		$form_txt .= "</form>\n";
		
		return $form_txt;
	}
}

?>