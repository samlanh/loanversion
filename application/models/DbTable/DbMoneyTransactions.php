<?php

class Application_Model_DbTable_DbMoneyTransactions extends Zend_Db_Table_Abstract
{

    protected $_name = 'cs_money_transactions';
    public function init()
    {
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL') || define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
	function getTransactionLis($search){
		$db = $this->getAdapter();
		$session_user=new Zend_Session_Namespace('authloan');
		$from_date =(empty($search['start_date']))? '1': " send_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " send_date <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
		
		$sql = " SELECT `id`,`sender_name`,`sender_tel`,`reciever_tel`,
				(SELECT name_en FROM `ln_view` WHERE key_code =`tran_type` AND TYPE= 27) AS tran_type,
				(SELECT name_en FROM `ln_view` WHERE key_code =`currencty_type` AND TYPE= 15) AS currencty_type,
		        `amount_tranfer`,`commission`,`send_date`,status
		 FROM ln_transfer  ";
		
		
		if (!empty($search['adv_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['adv_search']));
			$s_where[] = " sender_tel LIKE '%{$s_search}%'";
			$s_where[] = " reciever_tel LIKE '%{$s_search}%'";
			$s_where[] = " amount_tranfer LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1){
			$where.= " AND status = ".$search['status'];
		}
		if($search['currency_type']>-1){
			$where.= " AND currencty_type = ".$search['currency_type'];
		}
       $order=" order by id desc ";
		return $db->fetchAll($sql.$where.$order);
	}
	
	function getTransactionDetailByID($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM ln_transfer WHERE `id` = ". $id;
		return $db->fetchRow($sql);
	}
	
	function insertTransfer($data)
    {    	
    	$this->_name='ln_transfer';
    	$send_date = $data['send_date'].' '.date('h:i:s');
    	$exp_date = $data['epx_date'].' '.date('h:i:s');    
    	$session_user=new Zend_Session_Namespace('authloan');   	
    	 $db = $this->getAdapter();
    	 $db->beginTransaction();
    	 try {
    	 	
    	 		$_data=array(
    	 			    'tran_type'=>$data['tran_type'],
    					'sender_name'=>$data['sender'],
						'sender_tel'=>$data['sender_tel'],
						'reciever_tel'=>$data['reciever_tel'],
						'amount_tranfer'=>$data['invoice_no'],
						'currencty_type'=>$data['type_money'],
						'send_date'=>$data['send_date'],
						'recieved_date'=>$data['type_money'],
						'expire_date'=>$data['epx_date'],
	    	 			'amount_tranfer'=>$data['amount'],
    	 				'commission'=>$data['commission'],
    	 				'total_commission'=>$data['totalcommission'],
						'user_id'=>$session_user->user_id										
    	           );
    	 		
    	 		if(!empty($data['id'])){
    	 			$where=" id= ".$data['id'];
    	 			$this->update($_data, $where);
    	 		}else{
    	 			$this->insert($_data);
    	 			
    	 		}
//     	 		print_r($_data);exit();
    	 		return  $db->commit();
    	 		
    	 } catch (Exception $e) {
    	 	$db->rollBack();
    	 
    	 }
    }
    function getTransactionReport($search){
    	$db = $this->getAdapter();
    	$session_user=new Zend_Session_Namespace('authloan');
    	$from_date =(empty($search['start_date']))? '1': " send_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " send_date <= '".$search['end_date']." 23:59:59'";
    	$where = " WHERE ".$from_date." AND ".$to_date;
    
    	$sql = " SELECT `id`,`sender_name`,`sender_tel`,`reciever_tel`,
    	(SELECT name_en FROM `ln_view` WHERE key_code =`tran_type` AND TYPE= 27) AS tran_type,
    	tran_type AS transaction_type,
    	(SELECT name_en FROM `ln_view` WHERE key_code =`currencty_type` AND TYPE= 15) AS currencty_type,
    	currencty_type As curr_type,
    	`amount_tranfer`,`commission`,
    	`total_commission`,`send_date`,status
    	FROM ln_transfer  ";
    
    	if (!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = trim(addslashes($search['adv_search']));
    		$s_where[] = " sender_tel LIKE '%{$s_search}%'";
    		$s_where[] = " reciever_tel LIKE '%{$s_search}%'";
    		$s_where[] = " amount_tranfer LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	if($search['status']>-1){
    		$where.= " AND status = ".$search['status'];
    	}
    	if($search['currency_type']>-1){
    		$where.= " AND currencty_type = ".$search['currency_type'];
    	}
    	$order=" order by id desc ";
//     	echo $sql.$where.$order;exit();
    	return $db->fetchAll($sql.$where.$order);
    }
    
    function getTranferFee($data){
    	$db = $this->getAdapter();
    	$amount=$data['amount'];
    	$currency=$data['type_money'];
    	$sql="
    	SELECT 
			tr.*,
			(SELECT CONCAT(c.curr_namekh,' ',c.symbol ) FROM `ln_currency` AS c WHERE c.id = tr.`currency_id` LIMIT 1) AS currencyKH
			FROM 
			`ln_transfercondiction` AS tr
			WHERE $amount > tr.`fromAmount` AND $amount <= tr.`toAmount`
			AND tr.`currency_id`=$currency LIMIT 1 	";
    	$row = $db->fetchRow($sql);
    	if (empty($row) && $amount > 0){
    		$sql="
	    		SELECT 
				tr.*,
				(SELECT CONCAT(c.curr_namekh,' ',c.symbol ) FROM `ln_currency` AS c WHERE c.id = tr.`currency_id` LIMIT 1) AS currencyKH
				FROM 
				`ln_transfercondiction` AS tr
				WHERE tr.`currency_id`=$currency 
				ORDER BY tr.`toAmount` DESC LIMIT 1	";
    		$row = $db->fetchRow($sql);
    	}
    	return $row;
    }
}


