<?php

class Other_Model_DbTable_DbPosition extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_position';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authloan');
    	return $session_user->user_id;
    	 
    }
	public function getPostionById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM $this->_name WHERE co_id = ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
	function getAllStaffPosition($search=null){
		$db = $this->getAdapter();
		$sql=" SELECT id,position_kh,position_en,
		
		status
		FROM `ln_position` WHERE 1 ";
		$order=" order by id DESC";
		$where = '';
		
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
			$s_where[] = "REPLACE(position_kh,' ','')   LIKE '%{$s_search}%'";
			$s_where[] = "REPLACE(position_en,' ','')   LIKE '%{$s_search}%'";
			$s_where[] = "REPLACE(status,' ','')  		LIKE '%{$s_search}%'";
			$s_where[] = "REPLACE(displayby,' ','')  	LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['status_search']>-1){
			$where.= " AND status = ".$search['status_search'];
		}
		return $db->fetchAll($sql.$where.$order);	
	}	
}

