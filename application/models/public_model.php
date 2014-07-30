<?php
/**
 * 简单的通用的model方法
 * @author web
 *
 */
class Public_model extends CI_Model{
	function __construct(){
		parent::__construct();
	}
	
	 /**
	  * 通用的删除
	  * @param unknown_type $tabname
	  * @param array('id'=>1) $where
	  * @return boolean
	  */
	 function del_pub($tabname,$where=array())
	 {
		if($this->db->delete($tabname,$where))
		{
			return true;
		}
		return false;
	 }
	 /**
	  * 通用的增加 可以一次加多个
	  * @param unknown_type $tabname
	  * @param unknown_type $data = array($user1=>array(),$user2=>array());
	  * @return boolean
	  */
	 function add_pub($tabname,$data=array())
	 {
		if($this->db->insert_batch($tabname, $data))
		{
			return true;
		}
		return false;
	 }

	 /**
	  * 添加后返回id
	  * @param unknown_type $tabname
	  * @param $user=>array() $data
	  * @return boolean
	  */
	 function add_pub_re_id($tabname,$data=array())
	 {
		if($this->db->insert($tabname, $data))
		{
			return $this->db->insert_id();
		}
		return false;
	 }

	 /**
	  * 通用的更新
	  * $tabname
	  * array('id'=>1) $data 需要更新的列
	  * array('id'=>1) $where 条件
	  */
	 function update_pub($tabname,$data=array(),$where=array())
	 {
		if($this->db->update($tabname, $data, $where))
		{
			return true;
		}
		return false;
	 }
	
	/**
	 *通用的单表list
	 * @param unknown_type $tabname
	 * @param array('id'=>'=1') $where
	 * @param id desc $order_by
	 * @param 每次查询15 $rows
	 * @param 页数 $pagenum
	 * @return array()
	 */
	function list_pub($tabname,$where=null,$order_by=null,$rows=null,$pagenum=null)
	{
		$sql = "SELECT * from ".$tabname." where 1=1";
		if(!empty($where))
		{
			foreach($where as $key=>$val)
	 		{
				$sql.= " AND ".$key." ".$val;
	 		}
		}
		if(!empty($order_by))
	 	{
	 		$sql.= " order by ".$order_by;
	 	}
		$limit = null;
		if(!empty($rows)&&!empty($pagenum))
		{
			$limit =" limit ".($pagenum-1)*$rows.",".$rows;
		}
		if(!empty($rows)&&empty($pagenum))
		{
			$limit =" limit ".$rows;
		}
		if(!empty($limit))
		{
			$sql .= $limit;
		}
		$query = $this->db->query($sql);
		$result =$query->result();
		return $result;
	}
	
	/**
	 * 通用的获取总数(分页的时候需要的数据)
	 * @param unknown_type $tabname
	 * @param array('id'=>'=1') $where
	 */
	function list_count_pub($tabname,$where=null)
	{
		$sql = "SELECT count(id) as sumNum FROM ".$tabname." where 1=1";
		if(!empty($where))
		{
			foreach($where as $key=>$val)
			{
				$sql.= " AND ".$key." ".$val;
			}
		}
		$query = $this->db->query($sql);
		$result =$query->row();
		return $result->sumNum;
	}
	
	/**
	 * 通用的单个查询
	 * @param unknown_type $tabname
	 * @param unknown_type $where array('id'=>1,'name'=>'jhion')
	 * @return unknown
	 */
	 function detail_pub($tabname,$where)
	 {
		$this->db->where($where);
		$query = $this->db->get($tabname);
		$result =$query->row();
		return $result;
	 }
	 
	 /**
	  * 查询最后一个
	  * @param unknown_type $tabname
	  * @param unknown_type $where
	  * @param unknown_type $order_by
	  * @return unknown
	  */
	 function detail_pub_finally($tabname,$where,$order_by=null)
	 {
	 	$sql = "SELECT * from ".$tabname." where 1=1";
		if(!empty($where))
		{
			foreach($where as $key=>$val)
	 		{
				$sql.= " AND ".$key." ".$val;
	 		}
		}
		if(!empty($order_by))
	 	{
	 		$sql.= " order by ".$order_by;
	 	}
	 	$sql .= " limit 1";
	 	$query = $this->db->query($sql);
		$result =$query->row();
		return $result;
	 }

	 /**
	  * 获取ID(1,2,3,4)格式
	  * @param unknown_type $tabname
	  * @param unknown_type $where
	  * @param unknown_type $obj
	  * @return unknown
	  */
	 function get_group_concat_obj_pub($tabname,$where,$obj='id')
	 {
	 	$sql = "SELECT GROUP_CONCAT(".$obj.") as obj from ".$tabname." where 1=1";
	 	if(!empty($where))
	 	{
	 		foreach($where as $key=>$val)
	 		{
	 			$sql.= " AND ".$key." ".$val;
	 		}
	 	}
	 	$query = $this->db->query($sql);
		$result =$query->row();
		return $result->obj;
	 }
}
?>