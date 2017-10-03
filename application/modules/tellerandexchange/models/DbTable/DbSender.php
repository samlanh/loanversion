<?php
class Tellerandexchange_Model_DbTable_DbSender extends Zend_Db_Table_Abstract
{
	protected $_name = 'ln_sender';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->user_id;
	
	}
	function addKeeping($data){
		$data = array(
				'branch_id'=>$data['branch_id'],
				'customer'=>$data['customer'],
				'total_amount'=>$data['total_amount'],
				'invoice'=>$data['invoice'],
				'curr_type'=>$data['currency_type'],
				'tran_type'=>1,
				'disc'=>$data['Description'],
				'date'=>$data['Date'],
				'status'=>$data['Stutas'],
				'user_id'=>$this->getUserId()
		);
		$this->insert($data);

 }
 function updatExpense($data){
	$arr = array(
			    'branch_id'=>$data['branch_id'],
				'customer'=>$data['customer'],
				'total_amount'=>$data['total_amount'],
				'invoice'=>$data['invoice'],
				'curr_type'=>$data['currency_type'],
				'tran_type'=>1,
				'disc'=>$data['Description'],
				'date'=>$data['Date'],
				'status'=>$data['Stutas'],
				'user_id'=>$this->getUserId()
				
		);
	$where=" id = ".$data['id'];
	$this->update($arr, $where);
}
function getexpensebyid($id){
	$db = $this->getAdapter();
	$sql=" SELECT * FROM $this->_name where id=$id ";
	return $db->fetchRow($sql);
}

function getAllExpense($search=null){
	$db = $this->getAdapter();
	$session_user=new Zend_Session_Namespace('auth');
	$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
	$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
	$where = " WHERE ".$from_date." AND ".$to_date;
	
	$sql=" SELECT id,
	(SELECT branch_namekh FROM `ln_branch` WHERE ln_branch.br_id =branch_id LIMIT 1) AS branch_name,
	customer,
	(SELECT symbol FROM `ln_currency` WHERE ln_currency.id =curr_type) AS currency_type,
	invoice,total_amount,disc
	,date,status FROM $this->_name ";
	
	if (!empty($search['adv_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['adv_search']));
			$s_where[] = " customer LIKE '%{$s_search}%'";
			$s_where[] = " total_amount LIKE '%{$s_search}%'";
			$s_where[] = " invoice LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1){
			$where.= " AND status = ".$search['status'];
		}
		if($search['currency_type']>-1){
			$where.= " AND curr_type = ".$search['currency_type'];
		}
       $order=" order by id desc ";
		return $db->fetchAll($sql.$where.$order);
}
function getAllExpenseReport($search=null){
	$db = $this->getAdapter();
	$session_user=new Zend_Session_Namespace('auth');
	$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
	$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
	$where = " WHERE ".$from_date." AND ".$to_date;

	$sql=" SELECT id,
	(SELECT branch_namekh FROM `ln_branch` WHERE ln_branch.br_id =branch_id LIMIT 1) AS branch_name,
	account_id,
	(SELECT symbol FROM `ln_currency` WHERE ln_currency.id =curr_type) AS currency_type,invoice,
	curr_type,
	total_amount,disc,date,status FROM $this->_name ";

	if (!empty($search['adv_search'])){
		$s_where = array();
		$s_search = trim(addslashes($search['adv_search']));
		$s_where[] = " account_id LIKE '%{$s_search}%'";
		$s_where[] = " title LIKE '%{$s_search}%'";
		$s_where[] = " total_amount LIKE '%{$s_search}%'";
		$s_where[] = " invoice LIKE '%{$s_search}%'";
		
		$where .=' AND ('.implode(' OR ',$s_where).')';
	}
	if($search['status']>-1){
		$where.= " AND status = ".$search['status'];
	}
	if($search['currency_type']>-1){
		$where.= " AND curr_type = ".$search['currency_type'];
	}
	$order=" order by id desc ";
	return $db->fetchAll($sql.$where.$order);
}







}