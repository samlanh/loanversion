<?php

class Pawnshop_Model_DbTable_DbPawnshop extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_pawnshop';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    	 
    }
    public function getAllPawnshop($search,$reschedule =null){
    	$from_date =(empty($search['start_date']))? '1': " date_release >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " date_release <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	
    	$db = $this->getAdapter();
    	$sql = " SELECT id,
    	(SELECT branch_namekh FROM `ln_branch` WHERE br_id =branch_id LIMIT 1) AS branch,
    	loan_number,
    	(SELECT name_kh FROM `ln_clientsaving` WHERE client_id = ln_pawnshop.customer_id LIMIT 1) AS client_name_kh,
    	receipt_num,CONCAT(release_amount,
    	(SELECT symbol FROM `ln_currency` WHERE id =ln_pawnshop.currency_type LIMIT 1)) AS currency_type,
    	CONCAT(total_duration,(SELECT name_en FROM `ln_view` WHERE TYPE = 14 AND key_code = term_type )) term_type,
		interest_rate,
		(SELECT product_kh FROM `ln_pawnshopproduct` WHERE id=ln_pawnshop.product_id) as product_name,
		date_release,date_line, 
		status FROM `ln_pawnshop` WHERE 1 ";

    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = $search['adv_search'];
    		$s_where[] = " reciept_no LIKE '%{$s_search}%'";
    		$s_where[] = " release_amount LIKE '%{$s_search}%'";
    		$s_where[] = " interest_rate LIKE '%{$s_search}%'";
    		$s_where[] = " total_interest LIKE '%{$s_search}%'";
    		$s_where[] = " total_interest LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}

    	if(($search['member'])>0){
    		$where.= " AND client_id=".$search['customer_code'];
    	}

    	if(($search['branch_id'])>0){
    		$where.= " AND branch_id = ".$search['branch_id'];
    	}
    	if(($search['currency_type'])>0){
    		$where.= " AND currency_type=".$search['currency_type'];
    	}
//     	if(($search['pay_every'])>0){
//     		$where.= " AND term_type=".$search['pay_every'];
//     	}
//     	echo $sql;exit();
 	
    	$db = $this->getAdapter();    
    	return $db->fetchAll($sql.$where);
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
			    $day_amount=30;
				$datagroup = array(
						'branch_id'=>$data['branch_id'],
						'loan_number'=>$data['loan_code'],
						'level'=>$data['level'],
						'customer_id'=>$data['member'],
						'release_amount'=>$data['total_amount'],
						'date_release'=>$data['release_date'],
						'date_line'=>$data['date_line'],
						'create_date'=>date("Y-m-d"),
						'total_duration'=>$data['period'],
						'first_payment'=>$data['first_payment'],
						'payment_method'=>1,
						'holiday'=>2,
						'user_id'=>$this->getUserId(),
						'currency_type'=>$data['currency_type'],
						'release_amount'=>$data['total_amount'],//$data[''],
						'interest_rate'=>$data['interest_rate'],
						'status'=>1,
						'is_completed'=>0,
						'product_id'=>$data['product_id'],
						'est_amount'=>$data['estimatevalue'],
						'product_description'=>$data['description'],
				);
				$loan_id = $this->insert($datagroup);//add group loan
				
				$remain_principal = $data['total_amount'];
				$old_pri_permonth = 0;
				$old_interest_paymonth = 0;
				$old_amount_day = 0;
				$next_payment = $data['first_payment'];
				$curr_type = $data['currency_type'];
				$str_next=" +1 month";
				$start_date = $data['release_date'];
				$from_date =  $data['release_date'];
				$borrow_term=30;//=1month
				
				$this->_name='ln_pawnshop_detail';
				for($i=1;$i<=$data['period'];$i++){
					$pri_permonth=0;
					if($i==$data['period']){//check here
						$old_pri_permonth = ($curr_type==1)?round($data['total_amount'],-2):$data['total_amount'];
						$remain_principal = $pri_permonth;//$remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
					}
					if($i!=1){
						$start_date = $next_payment;
						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, 1,2,$data['first_payment']);
						$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
					}else{
						$next_payment = $data['first_payment'];
						$next_payment = $dbtable->checkFirstHoliday($next_payment,2);
						$amount_day = $dbtable->CountDayByDate($start_date,$next_payment);
					}
					$interest_paymonth = $data['total_amount']*($data['interest_rate']/100/$borrow_term)*30;
					
					$datapayment = array(
							'pawn_id'=>$loan_id,
							'outstanding'=>$remain_principal,
							'outstanding_after'=>$remain_principal,
							'principal_permonth'=>$old_pri_permonth,//good
							'principle_after'=> $old_pri_permonth,//good
							'total_interest'=>$interest_paymonth,//good
							'total_interest_after'=>$interest_paymonth,//good
							'total_payment'=>$old_pri_permonth+$interest_paymonth,//good
							'total_payment_after'=>$old_pri_permonth+$interest_paymonth,//good
							'date_payment'=>$next_payment,//good
							'is_completed'=>0,
							'status'=>1,
							'amount_day'=>$amount_day,
							'installment_amount'=>$i
					);
					
					$this->insert($datapayment);
					
					$amount_collect=0;
					$old_remain_principal = 0;
					$old_pri_permonth = 0;
					$old_interest_paymonth = 0;
					$old_amount_day = 0;
					$from_date=$next_payment;
				}
				
    			$db->commit();
//     			return $db->fetchAll($sql);
    			
    	}catch (Exception $e){
    		$db->rollBack();
//     		echo $e->getMessage();exit();
    		Application_Form_FrmMessage::message("INSERT_FAIL");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    function round_up($value, $places)
    {
    	$mult = pow(10, abs($places));
    	return $places < 0 ?
    	ceil($value / $mult) * $mult :
    	ceil($value * $mult) / $mult;
    }
    function round_up_currency($curr_id, $value,$places=-2){
    	if ($curr_id==1){
    		return $this->round_up($value, $places);
    	}
    	else{
    		return round($value,2);
    	}
    }
	public function addPawnshoptest($data){
    	$db = $this->getAdapter();
		$sql=" TRUNCATE TABLE ln_pawnshoptest";
    	$db->query($sql);
// 		$this->_name='ln_pawnshoptest';
    	try{
    		$dbtable = new Application_Model_DbTable_DbGlobal();
//     		$loan_number = $dbtable->getLoanNumber($data);
				$day_amount=30;
				$remain_principal = $data['total_amount'];
				$old_pri_permonth = 0;
				$old_interest_paymonth = 0;
				$old_amount_day = 0;
				$next_payment = $data['first_payment'];
				$curr_type = $data['currency_type'];
				$str_next=" +1 month";
				$start_date = $data['release_date'];
				$from_date =  $data['release_date'];
				$borrow_term=30;//=1month
				$this->_name='ln_pawnshoptest';
				for($i=1;$i<=$data['period'];$i++){
					$pri_permonth=0;
					if($i==$data['period']){//check here
						$old_pri_permonth = ($curr_type==1)?round($data['total_amount'],-2):$data['total_amount'];
						$remain_principal = $pri_permonth;//$remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
					}
					if($i!=1){
						$start_date = $next_payment;
						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, 1,2,$data['first_payment']);
						$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
					}else{
						$next_payment = $data['first_payment'];
						$next_payment = $dbtable->checkFirstHoliday($next_payment,2);
						$amount_day = $dbtable->CountDayByDate($start_date,$next_payment);
					}
					$interest_paymonth = $data['total_amount']*($data['interest_rate']/100/$borrow_term)*30;
					$datapayment = array(
							'pawn_id'=>1,
							'total_principal'=>$remain_principal,//good
							'principal_permonth'=> $old_pri_permonth,//good
							'total_interest'=>$interest_paymonth,//good
							'total_payment'=>$old_pri_permonth+$interest_paymonth,//good
							'date_payment'=>$next_payment,//good
							'is_completed'=>0,
							'status'=>1,
							'amount_day'=>$old_amount_day,
					);
					
					$this->insert($datapayment);
					$amount_collect=0;
					$old_remain_principal = 0;
					$old_pri_permonth = 0;
					$old_interest_paymonth = 0;
					$old_amount_day = 0;
					$from_date=$next_payment;
			  }
			unset($datamember);
			
			$sql="SELECT f.*,
				DATE_FORMAT(f.date_payment, '%d-%m-%Y') AS date_payments,
				DATE_FORMAT(f.date_payment, '%Y-%m-%d') AS date_name
			FROM ln_pawnshoptest AS f WHERE f.pawn_id = 1 ";
			return $db->fetchAll($sql);
    	}catch (Exception $e){
    		echo $e->getMessage();exit();
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