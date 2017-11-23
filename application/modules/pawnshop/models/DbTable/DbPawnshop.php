<?php

class Pawnshop_Model_DbTable_DbPawnshop extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_pawnshop';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    	 
    }
    public function getAllIndividuleLoan($search,$reschedule =null){
    	$from_date =(empty($search['start_date']))? '1': " saving_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " saving_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	
    	$db = $this->getAdapter();
    	$sql = " SELECT id,
    	(SELECT branch_namekh FROM `ln_branch` WHERE br_id =branch_id LIMIT 1) AS branch,
    	saving_number,
    	(SELECT name_kh FROM `ln_clientsaving` WHERE client_id = ln_pawnshop.client_id LIMIT 1) AS client_name_kh,
  		(SELECT name_en FROM `ln_clientsaving` WHERE client_id = ln_pawnshop.client_id LIMIT 1) AS client_name_en,
    	reciept_no,CONCAT(release_amount,
    	(SELECT symbol FROM `ln_currency` WHERE id =ln_pawnshop.currency_type)) AS currency_type,
    	CONCAT(period,(SELECT name_en FROM `ln_view` WHERE type = 14 AND key_code = term_type )) term_type,
		interest_rate,total_interest,saving_date,saving_close, 
		total_interest,status FROM `ln_pawnshop` WHERE 1 ";

    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = $search['adv_search'];
    		$s_where[] = " saving_number LIKE '%{$s_search}%'";
    		$s_where[] = " reciept_no LIKE '%{$s_search}%'";
    		$s_where[] = " release_amount LIKE '%{$s_search}%'";
    		$s_where[] = " interest_rate LIKE '%{$s_search}%'";
    		$s_where[] = " total_interest LIKE '%{$s_search}%'";
    		$s_where[] = " total_interest LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}

    	if(($search['customer_code'])>0){
    		$where.= " AND client_id=".$search['customer_code'];
    	}

    	if(($search['branch_id'])>0){
    		$where.= " AND branch_id = ".$search['branch_id'];
    	}
    	//     	if($search['status']>1){
    	//     		$where.= " lm.status = ".$search['status'];
    	//     	}
    	//     	if(($search['repayment_method'])>0){
    	//     		$where.= " AND lm.payment_method = ".$search['repayment_method'];
    	//     	}
//     	if(($search['co_id'])>0){
//     		$where.= " AND lg.co_id=".$search['co_id'];
//     	}
    	//     	if($reschedule!=null){
    	//     		$where.= ' AND lg.is_reschedule=2 ';
    	//     	}else{
    	//     		$where.= ' AND lg.is_reschedule !=2 ';
    	//     	}
    	if(($search['currency_type'])>0){
    		$where.= " AND currency_type=".$search['currency_type'];
    	}
    	if(($search['pay_every'])>0){
    		$where.= " AND term_type=".$search['pay_every'];
    	}

    		
//     	$order = " ORDER BY lg.g_id DESC";
    	$db = $this->getAdapter();    
    	return $db->fetchAll($sql.$where);
    	//`stGetAllIndividuleLoan`(IN txt_search VARCHAR(30),IN client_id INT,IN method INT,IN branch INT,IN co INT,IN s_status INT,IN from_d VARCHAR(70),IN to_d VARCHAR(70))
    }
    function getPawnshopById($id){//group id
    	$sql = " SELECT * FROM ln_pawnshop WHERE id =  $id ";
    	$where=" LIMIT 1 ";
    	return $this->getAdapter()->fetchRow($sql.$where);
    }
    public function addPawnshop($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$dbtable = new Application_Model_DbTable_DbGlobal();
    		
//     		$loan_number = $dbtable->getLoanNumber($data);
				$day_amount = 1;
				if($data['pay_every']==3){//month
					$day_amount=30;
				}elseif($data['pay_every']==2){
					$day_amount=7;
				}

				$total_interest = $data['total_amount']*($day_amount*$data['period'])*($data['interest_rate']/$day_amount/100);
    			$arr = array(
    					'branch_id'=>$data['branch_id'],
    					'saving_number'=>$data['loan_code'],
    					'level'=>$data['level'],
    					'client_id'=>$data['member'],
    					'release_amount'=>$data['total_amount'],
    					'period'=>$data['period'],
    					'currency_type'=>$data['currency_type'],
    					'term_type'=>$data['pay_every'],
    					'interest_rate'=>$data['interest_rate'],
    					'saving_date'=>$data['release_date'],
    					'saving_close'=>$data['date_line'],
    					'reciept_no'=>$data['receipt_num'],
    					'product_id'=>$data['product_id'],
						'est_amount'=>$data['estimatevalue'],
    					'product_description'=>$data['description'],
    					'total_interest'=>$total_interest,
    			);
    			$record_id = $this->insert($arr);//add member loan
    			unset($datamember);
    			$sql="SELECT * FROM $this->_name WHERE id = $record_id LIMIT 1";
    			$db->commit();
    			return $db->fetchRow($sql);
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Form_FrmMessage::message("INSERT_FAIL");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
	public function addPawnshoptest($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
		$sql=" TRUNCATE TABLE ln_pawnshoptest";
    	$db->query($sql);
			
		$this->_name='ln_pawnshoptest';
    	try{
    		$dbtable = new Application_Model_DbTable_DbGlobal();
    		
//     		$loan_number = $dbtable->getLoanNumber($data);
				$day_amount = 1;
				if($data['pay_every']==3){//month
					$day_amount=30;
				}elseif($data['pay_every']==2){
					$day_amount=7;
				}

				$total_interest = $data['total_amount']*($day_amount*$data['period'])*($data['interest_rate']/$day_amount/100);
    			$arr = array(
    					'branch_id'=>$data['branch_id'],
    					'saving_number'=>$data['loan_code'],
    					'level'=>$data['level'],
    					'client_id'=>$data['member'],
    					'release_amount'=>$data['total_amount'],
    					'period'=>$data['period'],
    					'currency_type'=>$data['currency_type'],
    					'term_type'=>$data['pay_every'],
    					'interest_rate'=>$data['interest_rate'],
    					'saving_date'=>$data['release_date'],
    					'saving_close'=>$data['date_line'],
    					'reciept_no'=>$data['receipt_num'],
    					'product_id'=>$data['product_id'],
    					'est_amount'=>$data['receipt_num'],
    					'product_description'=>$data['description'],
    					'total_interest'=>$total_interest,
    			);
    			$record_id = $this->insert($arr);//add member loan
    			unset($datamember);
    			$sql="SELECT * FROM ln_pawnshoptest WHERE id = $record_id LIMIT 1";
    			$db->commit();
    			return $db->fetchRow($sql);
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Form_FrmMessage::message("INSERT_FAIL");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    function updateLoanById($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		
    	
    		$db->commit();
    		return 1;
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Form_FrmMessage::message("INSERT_FAIL");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    public function getrptpawnshop($search,$reschedule =null){
    	$from_date =(empty($search['start_date']))? '1': " saving_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " saving_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	$db = $this->getAdapter();
    	$sql = " SELECT id,
    	(SELECT branch_namekh FROM `ln_branch` WHERE br_id =branch_id LIMIT 1) AS branch_name,
    	saving_number,
    	(SELECT name_kh FROM `ln_clientsaving` WHERE client_id = ln_pawnshop.client_id LIMIT 1) AS client_name_kh,
    	(SELECT name_en FROM `ln_clientsaving` WHERE client_id = ln_pawnshop.client_id LIMIT 1) AS client_name_en,
    	reciept_no,CONCAT(release_amount,
    	(SELECT symbol FROM `ln_currency` WHERE id =ln_pawnshop.currency_type)) AS currency_type,release_amount,
    	est_amount,product_id,product_description,
    	(SELECT symbol FROM `ln_currency` WHERE id =ln_pawnshop.currency_type) AS curr_type,
    	CONCAT(period,(SELECT name_en FROM `ln_view` WHERE type = 14 AND key_code = term_type )) term_type,
    	interest_rate,total_interest,saving_date,saving_close,
    	total_interest,status FROM `ln_pawnshop` WHERE 1 ";
    
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = $search['adv_search'];
    		$s_where[] = " saving_number LIKE '%{$s_search}%'";
    		$s_where[] = " reciept_no LIKE '%{$s_search}%'";
    		$s_where[] = " release_amount LIKE '%{$s_search}%'";
    		$s_where[] = " interest_rate LIKE '%{$s_search}%'";
    		$s_where[] = " total_interest LIKE '%{$s_search}%'";
    		$s_where[] = " total_interest LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	if(($search['client_name'])>0){
    		$where.= " AND client_id=".$search['client_name'];
    	}
    
    	if(($search['branch_id'])>0){
    		$where.= " AND branch_id = ".$search['branch_id'];
    	}
    	if(($search['currency_type'])>0){
    		$where.= " AND currency_type=".$search['currency_type'];
    	}
    	if(($search['pay_every'])>0){
    		$where.= " AND term_type=".$search['pay_every'];
    	}
    	$db = $this->getAdapter();
    	return $db->fetchAll($sql.$where);
    }
}