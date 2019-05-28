<?php
defined('IN_DCR') or exit('No permission.'); 

include_once(WEB_CLASS . 'article_class.php');
/**
* 产品处理类 更新、插入、删除产品等方法
* @author 我不是稻草人 www.cntaiyn.cn
* @version 1.0
* @copyright 2006-2010
* @package class
*/
class Product extends Article{
	/**
	* Product的构造函数 无需传参数
	*/
	private $nopic;
	private $class_list_ul;//记录ul型产品分类列表 记录<ul></ul>之间的li内容.主要用于前台的产品列表
	private $class_list_id;//记录产品分类id列表 以,分隔 用于记录GetSubClassIDFromList结果
	
	function __construct(){
		global $web_url;
		parent::__construct('{tablepre}product');
		$this->nopic=$web_url.'/include/images/nopic.png';
	}
	/**
	 * 函数setTable,设置类要操作的表
	 * @param string $table 表名
	 * @return true
	 */
	function setTable($table){
		parent::setTable($table);
	}
	/**
	 * 函数AddClass,添加产品分类
	 * @param array $proclassinfo 插入的产品数据 用数组表示,用$key=>$value来表示列名=>值 如array('title'=>'标题') 表示插入title的值为 标题
	 * @return boolean 成功返回true 失败返回false
	 */
	function AddClass($proclassinfo){
		$this->setTable('{tablepre}product_class');
		return parent::Add($proclassinfo);
	}
	
	/**
	 * 函数GetClassList,返回产品类列表 version>=1.0.6
	 * @param array $parent_id 父类ID 如果为0则返回全部
	 * @param string $level 本参数不建议外部传入 主要用于标记当前为几级分类
	 * @return array 返回产品分类列表
	 */
	function GetClassList($parent_id = 0 , $level = 1)
	{
		global $web_url_module,$web_url_surfix;
		$where = 'parentid=' . $parent_id;
		parent::setTable('@#@product_class');
		$class_list = parent::GetList($col,$start,$listnum,$where,$order);
		
		if( $class_list )
		{
			foreach( $class_list as $class_key=>$class )
			{
				$class_sub = $this->GetClassList($class['id'] , $level+1);
				$class_list[$class_key]['class_level'] = $level;
				
				if( $web_url_module == '1' ){
					$class_list[$class_key]['url'] = 'product_list.php?classid='.$class['id'];
				}elseif( $web_url_module == '2' )
				{
					$class_list[$class_key]['url'] = 'product_list_'.$class['id'].'.'.$web_url_surfix;
				}
				
				if( $class_sub )
				{
					$class_list[$class_key]['sub_class'] = $class_sub;
				}
			}
		}
		return $class_list;
	}
	
	/**
	 * 函数GetClassListSelect,返回产品类列表 输出<select></select>之间的option内容 version>=1.0.6
	 * <code>
	 * <?php
	 * //前台ul型产品分类菜单列表
	 * include WEB_CLASS."/product_class.php";
	 * $pro=new Product();
	 * echo '<select name="parentid" id="parentid">';
	 * $productClassList = $pro->GetClassList();
	 * $pro->GetClassListSelect($productClassList,$parentid);
	 * echo '</select>';
	 * ?>
 	 * </code>
	 * @param array $class_list 产品分类 可以用本类的GetClassList来获取
	 * @param array $cur_id 当前的产品分类选择ID
	 * @return true 输出select里的option
	 */	
	function GetClassListSelect( $class_list , $cur_id=0 )
	{
		if($class_list)
		{
			foreach($class_list as $value)
			{
				echo '<option value="'.$value['id'].'"';
				if($cur_id == $value['id'] && $cur_id)
				{
					echo 'selected="selected"';
				}
				echo '>'.str_repeat("----",$value['class_level']-1).$value['classname'].'</option>';
				if($value['sub_class'] && count($value['sub_class']))
				{
					$this->GetClassListSelect($value['sub_class'],$cur_id);
				}
			}
		}else
		{
			echo '<option value="0">当前没有产品分类</option>';
		}
	}
	
	/**
	 * 函数GetClassListUl,返回产品类ul下的li 主要用于前台的菜单输出 输出<ul></ul>之间的li内容 version>=1.0.6
	 * <code>
	 * <?php
	 * //前台ul型产品分类菜单列表
	 * include WEB_CLASS."/product_class.php";
	 * $pc=new Product(0);
	 * $productClassList=$pc->GetClassList();
	 * $tpl->assign('productClassList',$productClassList);
	 * $pro_class_list_txt=$pc->GetClassListUl($productClassList);
	 * $pro_class_list_txt = $pc->GetClassListUlHtml();
	 * ?>
 	 * </code>
	 * @param array $class_list 产品分类 可以用本类的GetClassList来获取
	 * @return true 没有返回值 主要把值存在了$this->class_list_ul 用GetClassListUlHtml获取就OK了
	 */	
	function GetClassListUl( $class_list )
	{
		if( $class_list )
		{
			$this->class_list_ul .= '<ul>';
			foreach( $class_list as $value )
			{
				$this->class_list_ul .= '<li><a href="'.$value['url'].'">'.$value['classname'].'</a>';
				if( $value['sub_class'] && count($value['sub_class']) )
				{
					$this->GetClassListUl( $value['sub_class'] );
				}
				$this->class_list_ul .= '</li>';
			}
			$this->class_list_ul .= '</ul>';
		}
	}
	
	/**
	 * 函数GetClassListUlHtml,获取由GetClassListUl产生的HTML version>=1.0.6
	 * @return string 获取由GetClassListUl产生的HTML
	 */		
	function GetClassListUlHtml()
	{
		//echo $this->class_list_ul;
		//exit;
		$count = 1;
		$pro_class_list_txt = str_replace('<ul>','<ul id="navigation">',$this->class_list_ul,$count);
		return $pro_class_list_txt;
	}
	
	/**
	 * 函数GetClassInfo,返回指定产品分类的数据信息
	 * @param string|int $id 产品类的ID
	 * @param array $col 要返回的字段列 如你要返回id,title为：array('id','title') 如果为array()时返回全部字段
	 * @return array 返回值为这个产品分类的信息(Array)
	 */
	function GetClassInfo($id,$col=array()){
		global $db,$web_url_module,$web_url_surfix;
		$this->setTable('{tablepre}product_class');
		$info=parent::GetInfo($col,"id=$id");
		//这里返回类别的路径
		$parent_info=$this->GetParentClassInfo($id,array('classname','id'));
		if($parent_info){
			if($web_url_module=='1'){
				$position='<a href="'.$web_url.'/">首页</a>>><a href="'.$web_url.'/product_list.php?id='.$parent_info['id'].'">'.$parent_info['classname'].'</a>>>'.$info['classname'].'';
			}elseif($web_url_module=='2'){
				$position='<a href="'.$web_url.'/">首页</a>>><a href="'.$web_url.'/product_list_'.$parent_info['id'].'.'.$web_url_surfix.'">'.$parent_info['classname'].'</a>>>'.$info['classname'].'';
			}
			$info['position']=$position;
		}else{
			$position='<a href="'.$web_url.'/">首页</a>>>'.$info['classname'].'';
			$info['position']=$position;
		}
		return $info;
	}
	/**
	 * 函数UpdateClass,更新产品类信息
	 * @param string|int $id 产品类的ID
	 * @param array $productClassinfo 更新的数据 用数组表示,用$key=>$value来表示列名=>值 如array('title'=>'标题') 表示插入title的值为 标题
	 * @return boolean 成功返回true 失败返回false
	 */
	function UpdateClass($id,$productClassinfo=array()){
		$this->setTable('{tablepre}product_class');
		if(parent::Update($productClassinfo,"id=$id")){
			return true;
		}else{
			return false;
		}
	}
	/**
	 * 函数DeleteClass,删除指定ID数组的产品分类
	 * @param array $idarr 删除的文档ID 可以是数组 比如array('1','2') 也可以用是字符串 但要以,分隔 比如1,2,3
	 * @return boolean 1成功 2有子类不能被删除 3删除失败mysql错误
	 */
	function DeleteClass($idarr){
		$this->setTable('{tablepre}product_class');
		if($this->HasSubClass($idarr[0])){
			return 2;
		}else{
			if(parent::Del($idarr)){
				return 1;
			}else{
				return 3;
			}
		}
	}
	
	/**
	 * 函数HasSubClass,判断类是不是有子类
	 * @param $classid 类ID
	 * @return boolean 成功返回true 失败返回false
	 */
	function HasSubClass($id){
		$this->setTable('{tablepre}product_class');
		$sub_where='parentid='.$id;
			//echo $sub_where;
		$sub_info=parent::GetList($col,'','',$sub_where,$order);
		return is_array($sub_info) && count($sub_info);
	}
	
	/**
	 * 函数GetSubClassIDList,获取一个类所有子类ID列表 以,分隔 version>=1.0.6
	 * @param $class_id 分类ID
	 * @param $is_contain_self 如果为true的话,结果里包含本$class_id
	 * @return array 获取子类ID列表
	 */
	function GetSubClassIDList( $class_id = 0 , $is_contain_self = false){
		$class_list = $this->GetClassList($class_id);
		$this->GetSubClassIDFromResultList( $class_list );
		$str = $this->class_list_id;
		if( ''==trim($str) )
		{
			if( $is_contain_self )
			{
				$str = $class_id;
			}else
			{
				$str = '';
			}
		}else
		{
			$str = substr($str , 0 , strlen($str)-1);
			if( $is_contain_self )
			{
				$str = $class_id . ',' .$str;
			}else
			{
			}
		}
		return $str;
	}
	
	/**
	 * 函数GetSubClassIDFromResultList,从一个分类List Array里获取所有的ID以,分隔 主要配合GetSubClassIDList使用 version>=1.0.6
	 * @param $class_list 分类
	 * @return array 获取子类ID列表 以,分隔
	 */
	private function GetSubClassIDFromResultList( $class_list )
	{
		if( $class_list )
		{
			foreach( $class_list as $value )
			{
				$this->class_list_id .= $value['id'] . ',';
				if( $value['sub_class'] && count($value['sub_class']) )
				{
					$this->GetSubClassIDFromResultList( $value['sub_class'] );
				}
			}
		}
	}
	
	/**
	 * 函数GetParentClass,获取父类名
	 * @param $classid 类ID
	 * @return string|boolean(false) 成功返回父类名 失败返回false
	 */
	function GetParentClassInfo($id,$col=array()){
		global $db;
		$this->setTable('{tablepre}product_class');
		$parent_info=parent::GetInfo(array('parentid'),"id=$id");
		$parentid=$parent_info['parentid'];
		if($parentid!=0){
			return parent::GetInfo($col,"id=".$parentid);
		}else{
			return false;
		}
	}
	/**
	 * 函数GetClassName,返回产品类别名
	 * @param string|int $pid 产品类别ID
	 * @return string 成功返回产品分类ID类名，失败返回false
	 */
	function GetClassName($pid){
		global $db;
		return $db->GetFieldValue('{tablepre}product_class','classname',"id=$pid");
	}
	/**
	 * 函数AddProduct,添加产品
	 * @param string $table 表名
	 * @param array $proinfo 产品数据 用数组表示,用$key=>$value来表示列名=>值 如array('title'=>'标题') 表示插入title的值为 标题
	 * @return int 返回值为文档的ID,失败返回0
	 */
	function Add($proinfo){
		//把主表和附加表分开来
		$this->setTable('{tablepre}product');
		foreach($proinfo as $key=>$value){
			if($key=='content' || $key=='keywords' ||  $key=='description'){
				$col_addon[$key]=$value;
			}else{
				$col_main[$key]=$value;
			}
		}
		$aid=parent::Add($col_main);
		
		if($aid){
			$col_addon['aid']=$aid;
			$this->setTable('{tablepre}product_addon');
			parent::Add($col_addon);
			return $aid;
		}else{
			return false;
		}
		exit;
	}
	/**
	 * 函数GetList,返回产品列表
	 * @param string|int $classid 产品类别ID
	 * @param array $col 要返回的字段列 如你要返回id,title为：array('id','title') 如果为arrya()时返回全部字段
	 * @param string $where 条件，不要带where 如id=1
	 * @param string $start 开始ID
	 * @param string $listnum 返回记录数
	 * @param string $order 排序，不要带order 如updatetime desc
	 * @param string $where_option 附加查询条件
	 * @param boolean $issub 是不是也要返回下级的产品
	 * @return array 返回产品列表
	 */
	function GetList($classid,$col=array(),$start='',$listnum='',$order='istop desc,id desc',$where_option='',$issub=0){
		$this->setTable('{tablepre}product');
		global $web_url,$web_url_module,$web_url_surfix;
		$classid=(int)$classid;	
		if($issub && $classid!=0){
			if(is_array($col) && count($col)>0){
				$cols=implode(',',$col);
			}else{
				$cols='*';
			}
			if(!empty($order)){
				$order='order by '.$order;
			}
			if(strlen($start)>0 && strlen($listnum)>0){
				$limit="limit $start,$listnum";
			}
			if($classid!=0){
				$where_arr[]="classid=$classid";
			}
			if(!empty($where_option)){
				$where_arr[]=$where_option;
			}
			$where=implode(' and ',$where_arr);
			if(!empty($where)){
				$where.=" or classid in (".$this->GetSubClassIDList($classid , true).")";
				$where=' where '.$where;
			}else{
			}
			//echo $where;
			$sql="select $cols from {tablepre}product $where $order $limit";
			$proinfo=parent::GetListBySql($sql);
		}else{
			if($classid!=0){
				$where.="classid=$classid";
			}
			if(!empty($where_option)){
				if(!empty($where)){
					$where.=' and '.$where_option;
				}else{
					$where=$where_option;
				}
			}
			//设置各个属性的默认值
			if(empty($order)){
				$order='istop desc,id desc';
			}
			$proinfo=parent::GetList($col,$start,$listnum,$where,$order);
		}
		$proCount=count($proinfo);
		for($i=0;$i<$proCount;$i++){
			if(empty($proinfo[$i]['logo'])){
				$proinfo[$i]['logo']=$this->nopic;
			}else{
				$proinfo[$i]['logo']=$web_url.'/uploads/product/'.$proinfo[$i]['logo'];
				//echo $proinfo['logo'];
			}
			if(empty($proinfo[$i]['biglogo'])){
				$proinfo[$i]['biglogo']=$this->nopic;
			}else{
				$proinfo[$i]['biglogo']=$web_url.'/uploads/product/'.$proinfo[$i]['biglogo'];
				//echo $proinfo['logo'];
			}			
			if($web_url_module=='1'){
				$proinfo[$i]['url']="product.php?id=".$proinfo[$i]['id'];
			}elseif($web_url_module=='2'){
				$proinfo[$i]['url']="product_".$proinfo[$i]['id'].".".$web_url_surfix;
			}
		}
		return $proinfo;
	}
	/**
	 * 函数GetInfo,返回产品信息
	 * @param string|int $aid 产品ID
	 * @param array $col 要返回的字段列 如你要返回id,title为：array('id','title') 如果为arrya()时返回全部字段
	 * @param array $option_col 要系统处理的col 比如logo会返回他的真实地址 默认处理的有：logo,biglogo,position,prev,next,tags_list 为了效率 最好明确指定这个处理
	 * @return array 产品信息
	 */
	function GetInfo($aid,$col=array(),$option_col=array('logo','biglogo','position','prev','next','tags_list')){
		global $db,$web_url;
		//要返回栏目ID
		if(!in_array('classid',$col)){
			array_push($col,'classid');
		}
		if(in_array('content',$col) || in_array('keywords',$col) || in_array('description',$col)){
			//如果要返回内容 内容在不同的表 所以要另外处理
			foreach($col as $key=>$colName){
				if($col[$key]=='content' || $col[$key]=='keywords' ||  $col[$key]=='description'){
					if($col[$key]=='content'){
						$col_another[]='content';
					}elseif($col[$key]=='keywords'){
						$col_another[]='keywords';
					}elseif($col[$key]=='description'){
						$col_another[]='description';
					}
					unset($col[$key]);
				}
			}
			 
			$newsInfo_1=parent::GetInfo($col,"id=$aid");
			$this->setTable("{tablepre}product_addon");
			$newsInfo_2=parent::GetInfo($col_another,"aid=$aid");
			if(!is_array($newsInfo_1) ||!is_array($newsInfo_2)){
				return false;
			}else{
				$newsInfo=array_merge($newsInfo_1,$newsInfo_2);
			}
		}else{
			parent::setTable('{tablepre}product');//1.0.6修改
			$newsInfo=parent::GetInfo($col,"id=$aid");//1.0.6修改
		}
		//返回当前路径
		//获得栏目信息
		global $web_url_module,$web_url_surfix;
		if(in_array('position',$option_col))
		{
			$parent_info=$this->GetParentClassInfo($newsInfo['classid'],array('classname','id'));
			if($parent_info){
				if($web_url_module=='1'){
					$parent_path='<a href="'.$web_url.'/product_list.php?id='.$parent_info['id'].'">'.$parent_info['classname'].'</a>>>';
				}elseif($web_url_module=='2'){
					$parent_path='<a href="'.$web_url.'/product_list_'.$parent_info['id'].'.'.$web_url_surfix.'">'.$parent_info['classname'].'</a>>>';
				}
			}
			
			$proclassname=$this->GetClassName($newsInfo['classid']);
			if($web_url_module=='1'){
				$class_path=$web_url.'/product_list.php?id='.$newsInfo['classid'];
			}elseif($web_url_module=='2'){
				$class_path=$web_url.'/product_list_'.$newsInfo['classid'].'.'.$web_url_surfix;
			}
			$position='<a href="'.$web_url.'">首页</a>>>'.$parent_path.'<a href="'.$class_path.'">'.$proclassname.'</a>>>'.$newsInfo['title'];			
			$newsInfo['position']=$position;
		}
		
		if(in_array('logo',$option_col))
		{
			if(empty($newsInfo['logo'])){
				$newsInfo['logo']=$this->nopic;
			}else{
				$newsInfo['logo']=$web_url.'/uploads/product/'.$newsInfo['logo'];
			}
		}
		
		if(in_array('biglogo',$option_col))
		{
			if(empty($newsInfo['biglogo'])){
				$newsInfo['biglogo']=$this->nopic;
			}else{
				$newsInfo['biglogo']=$web_url.'/uploads/product/'.$newsInfo['biglogo'];
			}
		}
		
		if(in_array('tags_list',$option_col))
		{
			//得出tagslist
			$tags_list='';
			if(!empty($newsInfo['tags']))
			{
				$tag_arr=explode(',',$newsInfo['tags']);
				if(is_array($tag_arr))
				{
					foreach($tag_arr as $tagname)
					{
						$tags_list .= '<a href="search.php?s_type=1&tag=' . urlencode($tagname) . '" target="_blank">'.$tagname.'</a> ';
					}
				}
				$newsInfo['tags_list']=$tags_list;
				//echo $newsInfo['tags'];
			}
		}
		
		//上一篇下一篇		
		if(in_array('prev',$option_col))
		{
			parent::setTable('{tablepre}product');
			$prev_info=parent::GetInfo(array('id','title'),"id<$aid and classid=".$newsInfo['classid'],'id desc');
			if($prev_info)
			{
				if($web_url_module=='1'){
					$prev_url=$web_url.'/product.php?id='.$prev_info['id'];
				}elseif($web_url_module=='2'){
					$prev_url=$web_url.'/product_'.$prev_info['id'].'.'.$web_url_surfix;
				}
				$newsInfo['prev']="<a href='$prev_url'>".$prev_info['title']."</a>";
			}else
			{
				$newsInfo['prev']='没有了';
			}
		}
		
		if(in_array('next',$option_col))
		{		
			parent::setTable('{tablepre}product');
			$next_info=parent::GetInfo(array('id','title'),"id>$aid and classid=".$newsInfo['classid']);
			if($next_info)
			{
				if($web_url_module=='1'){
					$next_url=$web_url.'/product.php?id='.$next_info['id'];
				}elseif($web_url_module=='2'){
					$next_url=$web_url.'/product_'.$next_info['id'].'.'.$web_url_surfix;
				}
				$newsInfo['next']="<a href='$next_url'>".$next_info['title']."</a>";
			}else
			{
				$newsInfo['next']='没有了';
			}
		}
		return $newsInfo;
	}
	/**
	 * 函数UpdateProduct,更新产品信息
	 * @param string|int $id 产品ID
	 * @param array $productinfo 产品数据 用数组表示,用$key=>$value来表示列名=>值 如array('title'=>'标题') 表示插入title的值为 标题
	 * @return boolean 成功返回true 失败返回false
	 */
	function Update($id,$productinfo){
		//把主表和附加表分开来
		foreach($productinfo as $key=>$value){
			if($key=='content' || $key=='keywords' ||  $key=='description'){
				$col_addon[$key]=$value;
			}else{
				$col_main[$key]=$value;
			}
		}
		
		if(parent::Update($col_main,"id=$id")){
			if(is_array($col_addon) && count($col_addon)>0){
				$this->setTable('{tablepre}product_addon');
				if(parent::Update($col_addon,"aid=$id")){
					return true;
				}else{
					return false;
				}
			}else{
				return true;
			}
		}else{
			return false;
		}
	}
	/**
	 * 函数Delete,删除产品
	 * 成功返回true 失败返回false
	 * @param array $idarr 删除的文档ID 可以是数组 比如array('1','2') 也可以用是字符串 但要以,分隔 比如1,2,3
	 * @return int 3表示没有选择要删除的数据 2表示删除数据库中的数据时出错 1表示成功
	 */
	function Delete($idarr){
		if(empty($idarr)){
			//数组为空
			return 3;
		}
		//这里的idarr是个数组
		//先删除缩略图
		foreach($idarr as $value){
			//存在图片 就删除
			$info=$this->GetInfo($value,array('logo','biglogo'),array());
			
			if(strlen($info['logo'])>0){
				$file=WEB_DR."/uploads/product/".$info['logo'];
				@unlink($file);
			}
			
			if(strlen($info['biglogo'])>0){
				$file=WEB_DR."/uploads/product/".$info['biglogo'];
				@unlink($file);
			}
		}
		if(parent::Del($idarr)){
			$this->setTable('{tablepre}product_addon');
			if(parent::Del($idarr,'aid')){
				return 1;
			}else{
				return 2;
			}
		}else{
			return 2;
		}
	}
	/**
	 * 函数DeleteProduct,返回产品的LOGO
	 * @param string|int $id 产品ID
	 * @param boolean $emptyFillDefault 当返回的LOGO为空时 是不是返回缩略图
	 * @return string 成功返回产品LOGO文件名 失败返回''
	 */
	function GetLogo($id,$emptyFillDefault=true){
		$pro_info = $this->GetInfo($id , array('logo') , array());
		if($pro_info)
		{
			$logo=$pro_info['logo'];
			if(strlen($logo)==0 && $emptyFillDefault){
				$logo=$this->nopic;
			}			
		}
		return $logo;
	}
}
?>