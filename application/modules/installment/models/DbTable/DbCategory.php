<?php

class Installment_Model_DbTable_DbCategory extends Zend_Db_Table_Abstract
{
	protected $_name = "ln_ins_category";
	
	public function getUserId(){
		return Application_Model_DbTable_DbGlobal::GlobalgetUserId();
	}
	public function add($data){
		$db = $this->getAdapter();
		$arr = array(
				'name'			=>	$data["cat_name"],
				'user_id'		=>	$this->getUserId(),
				'date'			=>	new Zend_Date(),
				'status'		=>	$data["status"],
		);
		$this->_name = "ln_ins_category";
		$this->insert($arr);
	}
	public function edit($data){
		$db = $this->getAdapter();
		$arr = array(
				'name'			=>	$data["cat_name"],
				'date'			=>	new Zend_Date(),
				'status'		=>	$data["status"],
				'user_id'		=>	$this->getUserId(),
		);
		$this->_name = "ln_ins_category";
		$where = $db->quoteInto("id=?", $data["id"]);
		$this->update($arr, $where);
	}
	
	//Insert Popup=============================
	public function addNew($data){
		$db = $this->getAdapter();
		$arr = array(
				'name'			=>	$data['cat_name'],
				'date'			=>	new Zend_Date(),
				'status'		=>	1,
		);
		$this->_name = "ln_ins_category";
		return $this->insert($arr);
	}
	public function getAllCategory(){
		$db = $this->getAdapter();
		$sql = "SELECT 
				c.id,c.`name`,
				c.`status`,
				(SELECT first_name FROM `rms_users` WHERE id=user_id ) AS user_id
			FROM `ln_ins_category` 
			AS c WHERE c.`status` =1 ORDER BY id desc ";
		return $db->fetchAll($sql);
	}
	public function getCategory($id){
		$db = $this->getAdapter();
		$sql = "SELECT c.id,c.`name`,c.`status` FROM `ln_ins_category` AS c WHERE c.`id`= $id";
		return $db->fetchRow($sql);
	}
}