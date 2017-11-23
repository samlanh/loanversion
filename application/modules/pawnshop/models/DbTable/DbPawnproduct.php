<?php

class Pawnshop_Model_DbTable_DbPawnproduct extends Zend_Db_Table_Abstract
{
    protected $_name = 'ln_pawnshopproduct';
    function addProduct($data){
    	try{
    	$db = $this->getAdapter();
    	$arr = array(
    			'product_en'=>$data['title_en'],
    			'product_kh'=>$data['title_kh'],
    			'status'=>$data['status'],
    			'date'=>$data['date'],
    			'description'=>$data['description'],
    			
    			);
         return $this->insert($arr);
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    function updatProduct($data){
    	$arr = array(
    			'product_en'=>$data['title_en'],
    			'product_kh'=>$data['title_kh'],
    			'status'=>$data['status'],
    			'date'=>$data['date'],
    			'description'=>$data['description'],
    			);
    	
    	$where=" id = ".$data['id'];
    	$this->update($arr, $where);
    }
    function getListViewById($id){
    	$db = $this->getAdapter();
    	$sql="SELECT id,product_en AS title_en,product_kh AS title_kh,description ,`date`,status FROM $this->_name where id=$id ";
    	return $db->fetchRow($sql);
    }
    function getAllviewBYType($search=null,$type=null){
    	$db = $this->getAdapter();
    	$sql=" SELECT v.id,v.product_en,v.product_kh,v.description,
    	v.status FROM $this->_name AS v WHERE 1";
    	
    	$Other=" ORDER BY  v.id desc ";
    	$where = '';
    	 
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = $search['adv_search'];
    		$s_where[] = " v.description LIKE '%{$s_search}%'";
    		$s_where[] = " v.product_en LIKE '%{$s_search}%'";
    		$s_where[]=" v.product_kh LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	if($search['status_search']>-1){
    		$where.= " AND v.status = ".$search['status_search'];
    	}
    	return $db->fetchAll($sql.$where.$Other);
    	
    }
}

