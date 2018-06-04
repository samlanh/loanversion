<?php

class Pawnshop_Model_DbTable_DbReschedule extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_pawnshop';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authloan');
    	return $session_user->user_id;
    	 
    }
    public function getAllPawnshop($search,$reschedule =null){
    	$from_date =(empty($search['start_date']))? '1': " c.create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " c.create_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	$db = $this->getAdapter();
    	$sql = "SELECT
		c.id,
		(SELECT branch_namekh FROM `ln_branch` WHERE br_id =c.`branch_id` LIMIT 1) AS branch,
		s.`loan_number`,
		(SELECT name_kh FROM `ln_clientsaving` WHERE client_id = s.customer_id LIMIT 1) AS client_name_kh,
		CONCAT ((s.`release_amount`-c.`extra_loan`),(SELECT symbol FROM `ln_currency` WHERE id =c.currency_type LIMIT 1)) AS pawnbalancce,
		CONCAT (c.`extra_loan`,(SELECT symbol FROM `ln_currency` WHERE id =c.currency_type LIMIT 1)) AS extra_loan,
		CONCAT (c.`release_amount`,(SELECT symbol FROM `ln_currency` WHERE id =c.currency_type LIMIT 1))  AS new_release,
		c.`level` AS reschdule_time,
		(SELECT product_kh FROM `ln_pawnshopproduct` WHERE id=s.product_id LIMIT 1) AS product_name,
		CONCAT(c.total_duration,' ',(SELECT name_kh FROM `ln_view` WHERE TYPE = 14 AND key_code = s.term_type )) term_type,c.date_release,c.date_line,
		c.create_date,
		c.status
		 FROM `ln_pawnshop_reschedule` AS c,
		`ln_pawnshop` AS s 
		  WHERE s.`id` = c.`pawnshop_id`
		    	";
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_where[] = " c.`extra_loan` LIKE '%{$s_search}%'";
    		$s_where[] = " c.`release_amount` LIKE '%{$s_search}%'";
    		$s_where[] = " c.`level` LIKE '%{$s_search}%'";
    		$s_where[] = " c.total_duration LIKE '%{$s_search}%'";
    		$s_where[] = " (s.`release_amount`-c.`extra_loan`) LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}

    	if(($search['members'])>0){
    		$where.= " AND s.customer_id=".$search['members'];
    	}

    	if(($search['branch_id'])>0){
    		$where.= " AND s.branch_id = ".$search['branch_id'];
    	}
    	if(($search['currency_type'])>0){
    		$where.= " AND s.currency_type=".$search['currency_type'];
    	}
    	if(($search['product_id'])>0){
    		$where.= " AND s.product_id=".$search['product_id'];
    	}
 	   $order=" ORDER BY c.id DESC";
    	$db = $this->getAdapter();    
    	return $db->fetchAll($sql.$where.$order);
    }
//     function getPawnshopById($id){//group id
//     	$sql = " SELECT * FROM ln_pawnshop WHERE id =  $id ";
//     	$where=" LIMIT 1 ";
//     	return $this->getAdapter()->fetchRow($sql.$where);
//     }
    public function addReschedulePawnshop($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$loan_id = $data['loan_code'];
    		
    		$dbpawn = new Pawnshop_Model_DbTable_DbPawnshop();
    		$row = $dbpawn->getPawnshopById($loan_id);
    		
    		$newReleaseAmount = $row['release_amount']+$data['extra_loan'];
    		
    		$arr_update = array(
    				'release_amount'=>$newReleaseAmount,
    				'is_reschedule'=>1
    		);
    		$where = ' id = '.$loan_id;
    		$this->_name="ln_pawnshop";
    		$this->update($arr_update, $where);
    		
    		$datagroup = array(
    				'branch_id'=>$data['branch_id'],
    				'pawnshop_id'=>$loan_id,
    				'loan_number'=>$data['loan_code'],
    				'level'=>$data['level'],
    				'customer_id'=>$data['member'],
    				'outstandingloan'=>$data['outstandingloan'],
    				'extra_loan'=>$data['extra_loan'],
    				
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
    				
    				'term_type'=>$data['payment_term'],
    				'interest_type'=>$data['interest_type'],
//     				'receipt_num'=>$data['receipt_num'],
    				
    		);
    		$this->_name = 'ln_pawnshop_reschedule';
    		$this->insert($datagroup);//add group loan
    		
    		
    		$session_transfer=new Zend_Session_Namespace();
    		$session_user=new Zend_Session_Namespace('authloan');
    		$user_id = $session_user->user_id;
    		
    		$this->_name="ln_pawnshop_detail";
    		$where = " status=1 AND is_completed=0 AND pawn_id =".$loan_id;
    		$this->delete($where);
    		
    		$sql="SELECT COUNT(id) FROM ln_pawnshop_detail WHERE status=1 AND pawn_id= ".$loan_id;
    		$start_id = $db->fetchOne($sql);
    		
    		
    		$this->_name="ln_pawnshop_detail";
    		if($data['extra_loan']>0){//ខ្ចីបន្ថែម
    			$datapayment = array(
    					'pawn_id'=>$loan_id,
    					'noted'=>$data['noted'],//store note only
    					'outstanding'=>$data['total_amount'],//good
    					'outstanding_after'=>$data['total_amount'],//good
    					'principal_permonth'=> $data['extra_loan'],//good
    					'amount_paidprincipal'=> $data['extra_loan'],//good
    					'principle_after'=> $data['extra_loan'],//good
    					'total_interest'=>0,
    					'total_interest_after'=>0,
    					'total_payment'=>$data['total_amount'],
    					'total_payment_after'=>$data['total_amount'],
    					'date_payment'=>$data['release_date'],//good
    					'is_completed'=>1,
    					'status'=>0,
    					'amount_day'=>0,
    					'is_extraloan'=>1,
    					'installment_amount'=>$start_id+1,
    			);
    			$this->insert($datapayment);
    		}
    		
    		$remain_principal = $data['total_amount'];
    		$old_pri_permonth = 0;
    		$old_interest_paymonth = 0;
    		$old_amount_day = 0;
    		$next_payment = $data['first_payment'];
    		$curr_type = $data['currency_type'];
    		
    		
    		$dbtable = new Application_Model_DbTable_DbGlobal();
    		$str_next = $dbtable->getNextDateById($data['payment_term'],2);
			$start_date = $data['release_date'];
			$from_date =  $data['release_date'];
			$borrow_term = $dbtable->getSubDaysByPaymentTerm($data['payment_term'],null);//return amount day for payterm
    		
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
    			
//     			$interest_paymonth = $data['total_amount']*($data['interest_rate']/100/$borrow_term)*30;
    			if($data['interest_type']==1){//interest by percentage
    				$interest_paymonth = $data['total_amount']*($data['interest_rate']/100/$borrow_term)*$amount_day;//by day or month check 30
    				if($data['payment_term']==3){//for month
    					$interest_paymonth = $data['total_amount']*($data['interest_rate']/100/$borrow_term)*30;//by day or month check 30
    				}
    			}else{//interest by fixed
    				if($data['payment_term']==3){//for month
    					$interest_paymonth = $data['interest_rate'];//*$amount_day/$borrow_term;//បើចង់គិតតាមថ្ងៃត្រូវបើកចំនុចនេះ
    				}else{//day
    					$interest_paymonth = $data['interest_rate']*$amount_day;//បើចង់គិតទាំងថ្ងៃឈប់ដែរ
    				}
    			}
    			
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
    					'installment_amount'=>$i+$start_id
    			);
    			$this->insert($datapayment);

    			$amount_collect=0;
    			$old_remain_principal = 0;
    			$old_pri_permonth = 0;
    			$old_interest_paymonth = 0;
    			$old_amount_day = 0;
    			$from_date=$next_payment;
    			if($i!=1){
    				if($data['payment_term']!=1){//for loan day
						$next_payment = $dbtable->checkDefaultDate($str_next, $start_date, 1,2,$data['first_payment']);
					}
    			}
    		}
    		$db->commit();
    		
    	}catch (Exception $e){
    		    $db->rollBack();
    		    echo $e->getMessage();exit();
    		    Application_Form_FrmMessage::message("INSERT_FAIL");
    		    Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}

    }
}