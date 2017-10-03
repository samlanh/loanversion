<?php

class Saving_Model_DbTable_DbSavingaccount extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_savingaccount';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    	 
    }
    
   
    public function getAllIndividuleLoan($search,$reschedule =null){
    	$from_date =(empty($search['start_date']))? '1': " saving_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " saving_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	
    	$db = $this->getAdapter();
    	$sql="
    	SELECT id,
    	(SELECT branch_namekh FROM `ln_branch` WHERE br_id =ln_savingaccount.branch_id LIMIT 1) AS branch,
    	saving_number,
    	  name_kh AS client_name_kh,
  		 name_en AS client_name_en,
  		 reciept_no,(SELECT symbol FROM `ln_currency` WHERE id = currency_type LIMIT 1) currency_type,
  		 deposit_amount,interest_rate,
  		 (SELECT name_en FROM `ln_view` WHERE type=28 AND key_code=saving_method LIMIT 1) saving_method  ,
  		(SELECT name_en FROM `ln_view` WHERE type=1 AND key_code=term_type LIMIT 1) term_type, withdrawing,level,
    	ln_savingaccount.status FROM 
		`ln_savingaccount`,ln_clientsaving where ln_clientsaving.client_id = ln_savingaccount.client_id ";
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = trim(addslashes($search['adv_search']));
    		$s_where[] = " saving_number LIKE '%{$s_search}%'";
    		$s_where[] = " deposit_amount LIKE '%{$s_search}%'";
    		$s_where[] = " interest_rate LIKE '%{$s_search}%'";
    		$s_where[] = " reciept_no LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}

//     	if(($search['customer_code'])>0){
//     		$where.= " AND client_id=".$search['customer_code'];
//     	}
    	if(($search['account_type'])>0){
    		$where.= " AND saving_method = ".$search['account_type'];
    	}
    	if(($search['branch_id'])>0){
    		$where.= " AND ln_savingaccount.branch_id = ".$search['branch_id'];
    	}
    	if(($search['currency_type'])>0){
    		$where.= " AND ln_savingaccount.currency_type=".$search['currency_type'];
    	}
//     	if(($search['pay_every'])>0){
//     		$where.= " AND lg.pay_term=".$search['pay_every'];
//     	}

    		
    	$order = " ORDER BY id DESC";
    	$db = $this->getAdapter();    
//      	echo $sql.$where.$order;	
    	return $db->fetchAll($sql.$where.$order);
    	//`stGetAllIndividuleLoan`(IN txt_search VARCHAR(30),IN client_id INT,IN method INT,IN branch INT,IN co INT,IN s_status INT,IN from_d VARCHAR(70),IN to_d VARCHAR(70))
    }
    function getSavingById($id){//group id
    	$sql = " SELECT * FROM $this->_name WHERE id= $id ";
     	$sql.=" LIMIT 1 ";
    	return $this->getAdapter()->fetchRow($sql);
    }
//     public function getLoanviewById($id){
//     	$sql = "SELECT
//     	lg.g_id
//     	,(SELECT branch_nameen FROM `ln_branch` WHERE br_id =lg.branch_id LIMIT 1) AS branch_name
//     	,lg.level,
//     	(SELECT name_en FROM `ln_view` WHERE status =1 and type=24 and key_code=lg.for_loantype) AS for_loantype
//     	,(SELECT co_firstname FROM `ln_co` WHERE co_id =lg.co_id LIMIT 1) AS co_firstname
//     	,(select concat(zone_name,'-',zone_num)as dd from `ln_zone` where zone_id = lg.zone_id ) AS zone_name
//     	,(SELECT name_en FROM `ln_view` WHERE status =1 and type=14 and key_code=lg.pay_term) AS pay_term
//     	,(SELECT name_en FROM `ln_view` WHERE status =1 and type=14 and key_code=lg.collect_typeterm) AS collect_typeterm
//     	,lg.date_release
//     	,lg.total_duration
//     	,lg.first_payment
//     	,lg.time_collect
//     	,(SELECT name_en FROM `ln_view` WHERE status =1 and type=2 and key_code=lg.holiday) AS holiday
//     	,lg.date_line
//     	,lm.pay_after, lm.pay_before
//     	,(SELECT payment_nameen FROM `ln_payment_method` WHERE id =lm.payment_method ) AS payment_nameen
//     	,(SELECT curr_nameen FROM `ln_currency` WHERE id=lm.currency_type) AS currency_type
//     	,lm.graice_period,
//     	lm.loan_number,lm.interest_rate,lm.amount_collect_principal,lm.semi,
//     	lm.client_id,lm.admin_fee,
//     	lm.pay_after,lm.pay_before,lm.other_fee
//     	,(SELECT name_kh FROM `ln_client` WHERE client_id = lm.client_id LIMIT 1) AS client_name_kh,
//     	(SELECT name_en FROM `ln_client` WHERE client_id = lm.client_id LIMIT 1) AS client_name_en,
//     	(SELECT group_code FROM `ln_client` WHERE client_id = lm.client_id LIMIT 1) AS group_code,
//     	(SELECT client_number FROM `ln_client` WHERE client_id = lm.client_id LIMIT 1) AS client_number,
//     	lm.total_capital,lm.interest_rate,lm.payment_method,
//     	lg.time_collect,
//     	lg.zone_id,
//     	(SELECT co_firstname FROM `ln_co` WHERE co_id =lg.co_id LIMIT 1) AS co_enname,
//     	lg.status AS str ,lg.status FROM `ln_loan_group` AS lg,`ln_loan_member` AS lm
//     	WHERE lg.g_id = lm.group_id AND lm.member_id = $id LIMIT 1 ";
//     	return $this->getAdapter()->fetchRow($sql);
//     }
    function round_up($value, $places)
    {
    	$mult = pow(10, abs($places));
    	return $places < 0 ?
    	ceil($value / $mult) * $mult :
    	ceil($value * $mult) / $mult;
    }
    function round_up_currency($curr_id, $value,$places=-2){
//     	return (($curr_id==1)? $this->round_up($value, $places):$value);
    	if ($curr_id==1){
    		return $this->round_up($value, $places);
    	}
    	else{
    		return round($value,2);
    	}
    }
//     function calCulateIRR($total_loan_amount,$loan_amount,$term,$curr){
//     	$array =array();//array(-1000,107,103,103,103,103,103,103,103,103,103,103,103);
//     	for($j=0; $j<= $term;$j++){
//     		if($j==0){
//     			$array[]=-$loan_amount;
//     		}elseif($j==1){
//     			$fixed_principal = round($total_loan_amount/$term,0, PHP_ROUND_HALF_DOWN);
//     			$post_fiexed = $total_loan_amount/$term-$fixed_principal;
//     			$total_add_first = $this->round_up_currency($curr,$post_fiexed*$term);
    			 
//     			$array[]=($total_add_first+$fixed_principal);
//     		}else{
//     			$array[]=round($total_loan_amount/$term,0, PHP_ROUND_HALF_DOWN);
//     		}
    
//     	}
//     	$array = array_values($array);
//     	return Loan_Model_DbTable_DbIRRFunction::IRR($array);
//     }
    
    public function addSavingAccount($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$dbtable = new Application_Model_DbTable_DbGlobal();
    		
//     		$loan_number = $dbtable->getLoanNumber($data);
    		$dbs = new Application_Model_DbTable_DbGlobal();
    		$saving_code = $dbs->getSavingNumber($data);
    			$arr = array(
    					'branch_id'=>$data['branch_id'],
    					'saving_number'=>$saving_code,
    					'level'=>$data['level'],
    					'client_id'=>$data['member'],
    					'deposit_amount'=>$data['total_amount'],
    					'currency_type'=>$data['currency_type'],
    					'saving_method'=>$data['account_type'],
    					'term_type'=>$data['pay_every'],
    					'withdrawing'=>$data['period'],
    					'interest_rate'=>$data['interest_rate'],
    					'saving_date'=>$data['release_date'],
    					'saving_close'=>$data['date_line'],
    					'reciept_no'=>$data['receipt_num'],
    			);
    			$saving_id = $this->insert($arr);//add member loan
    			unset($datamember);
    			
    			$remain_principal = $data['total_amount'];
    			$start_date = $data['release_date'];//loan release;
    			$from_date =  $data['release_date'];
    			
    			$borrow_term = $dbtable->getSubDaysByPaymentTerm($data['pay_every'],null);//return amount day for payterm
    			$amount_borrow_term = $borrow_term*$data['period'];//amount of borrow
    			
    			$fund_term = $dbtable->getSubDaysByPaymentTerm($data['pay_every'],null);//return amount day for payterm
    			$amount_fund_term = $fund_term*$data['withdrawal'];
    			
    			$loop_payment = ($amount_borrow_term)/($amount_fund_term);
    			$payment_method = $data['account_type'];
	            $str_next = $dbtable->getNextDateById($data['pay_every'],$data['withdrawal']);//for next,day,week,month;
    			
	            $this->_name='ln_savingaccountdetail';
				for($i=1;$i<=$loop_payment;$i++){
    				$amount_collect = $data['withdrawal'];
    				if($payment_method==1){//decline//completed
    					$next_payment = $data['date_line'];
    					$next_payment = $dbtable->checkFirstHoliday($next_payment,2);
    					$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    					$interest_paymonth = $remain_principal*($data['interest_rate']/100)*$data['period']/12;
    					$interest_paymonth = $this->round_up_currency($data['currency_type'],($interest_paymonth));
    					$arr = array(
    							'saving_id'=>$saving_id,
    							'interest_amount'=>$interest_paymonth,
    							'outstanding_balance'=>$remain_principal,
    							'date_withdraw'=>$next_payment,
    					);
    					$member_id = $this->insert($arr);//add member loan
    			    }else{//other of saving account   	  
    			    	if($i!=1){
    			    		$start_date = $next_payment;
    			    		$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['withdrawal'],2,$data['release_date']);
    			    		$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    			    		$interest_paymonth = $data['total_amount']*(($data['interest_rate']/12/30)/100)*$amount_day;
    			    	}else{
    			    		$next_payment = $data['first_payment'];
    			    		$next_payment = $dbtable->checkFirstHoliday($next_payment,2);
    			    		$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    			    		$interest_paymonth = $data['total_amount']*(($data['interest_rate']/12/30)/100) *$amount_day;
    			    	}
    			    	$interest_paymonth = $this->round_up_currency($data['currency_type'],($interest_paymonth));
    			    	$arr = array(
    			    			'saving_id'=>$saving_id,
    			    			'interest_amount'=>$interest_paymonth,
    			    			'amount_day'=>$amount_day,
    			    			'outstanding_balance'=>$remain_principal,
    			    			'date_withdraw'=>$next_payment,
    			    	
    			    	);
    			    	$this->insert($arr);//add member loan
    			    	$from_date=$next_payment;
    			    	if($i!=1){
    			    		$next_payment = $dbtable->checkDefaultDate($str_next, $start_date, $data['withdrawal'],2,$data['release_date']);
    			    	}
    			    }
    			}
    		$db->commit();
    		return 1;
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Form_FrmMessage::message("INSERT_FAIL");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    function updateLoanById($data,$id){
	    	$db = $this->getAdapter();
	    	$db->beginTransaction();
	    	try{
	    		$dbtable = new Application_Model_DbTable_DbGlobal();
	    			$arr = array(
	    					'branch_id'=>$data['branch_id'],
// 	    					'saving_number'=>$data['loan_code'],
	    					'level'=>$data['level'],
	    					'client_id'=>$data['member'],
	    					'deposit_amount'=>$data['total_amount'],
	    					'currency_type'=>$data['currency_type'],
	    					'saving_method'=>$data['account_type'],
	    					'term_type'=>$data['pay_every'],
	    					'withdrawing'=>$data['period'],
	    					'interest_rate'=>$data['interest_rate'],
	    					'saving_date'=>$data['release_date'],
	    					'saving_close'=>$data['date_line'],
	    					'reciept_no'=>$data['receipt_num'],
	    			);
	    			$where=" id = ".$id;
	    			$saving_id = $this->update($arr, $where);//add member loan
	    			unset($datamember);
	    			
	    			$remain_principal = $data['total_amount'];
	    			$start_date = $data['release_date'];//loan release;
	    			$from_date =  $data['release_date'];
	    			
	    			$borrow_term = $dbtable->getSubDaysByPaymentTerm($data['pay_every'],null);//return amount day for payterm
	    			$amount_borrow_term = $borrow_term*$data['period'];//amount of borrow
	    			
	    			$fund_term = $dbtable->getSubDaysByPaymentTerm($data['pay_every'],null);//return amount day for payterm
	    			$amount_fund_term = $fund_term*$data['withdrawal'];
	    			
	    			$loop_payment = ($amount_borrow_term)/($amount_fund_term);
	    			$payment_method = $data['account_type'];
		            $str_next = $dbtable->getNextDateById($data['pay_every'],$data['withdrawal']);//for next,day,week,month;
	    			
		            $this->_name='ln_savingaccountdetail';
		            $where=" saving_id = ".$id;
		            $this->delete($where);
		            
					for($i=1;$i<=$loop_payment;$i++){
	    				$amount_collect = $data['withdrawal'];
	    				if($payment_method==1){//decline//completed
	    					$next_payment = $data['date_line'];
	    					$next_payment = $dbtable->checkFirstHoliday($next_payment,2);
	    					$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
	    					$interest_paymonth = $remain_principal*($data['interest_rate']/100)*$data['period']/12;
	    					$interest_paymonth = $this->round_up_currency($data['currency_type'],($interest_paymonth));
	    					$arr = array(
	    							'saving_id'=>$saving_id,
	    							'interest_amount'=>$interest_paymonth,
	    							'outstanding_balance'=>$remain_principal,
	    							'date_withdraw'=>$next_payment,
	    					);
	    					$member_id = $this->insert($arr);//add member loan
	    			    }else{//other of saving account   	  
	    			    	if($i!=1){
	    			    		$start_date = $next_payment;
	    			    		$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['withdrawal'],2,$data['release_date']);
	    			    		$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
	    			    		$interest_paymonth = $data['total_amount']*(($data['interest_rate']/12/30)/100)*$amount_day;
	    			    	}else{
	    			    		$next_payment = $data['first_payment'];
	    			    		$next_payment = $dbtable->checkFirstHoliday($next_payment,2);
	    			    		$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
	    			    		$interest_paymonth = $data['total_amount']*(($data['interest_rate']/12/30)/100) *$amount_day;
	    			    	}
	    			    	$interest_paymonth = $this->round_up_currency($data['currency_type'],($interest_paymonth));
	    			    	$arr = array(
	    			    			'saving_id'=>$saving_id,
	    			    			'interest_amount'=>$interest_paymonth,
	    			    			'amount_day'=>$amount_day,
	    			    			'outstanding_balance'=>$remain_principal,
	    			    			'date_withdraw'=>$next_payment,
	    			    	
	    			    	);
	    			    	$this->insert($arr);//add member loan
	    			    	$from_date=$next_payment;
	    			    	if($i!=1){
	    			    		$next_payment = $dbtable->checkDefaultDate($str_next, $start_date, $data['withdrawal'],2,$data['release_date']);
	    			    	}
	    			    }
	    			}
	    		$db->commit();
	    		return 1;
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Form_FrmMessage::message("INSERT_FAIL");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
 

    public function addSavingAccounttest($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$dbtable = new Application_Model_DbTable_DbGlobal();
            $this->_name='ln_savingaccounttest';
    		//     		$loan_number = $dbtable->getLoanNumber($data);
    		$arr = array(
    				'branch_id'=>$data['branch_id'],
    				'saving_number'=>$data['loan_code'],
    				'level'=>$data['level'],
    				'client_id'=>$data['member'],
    				'deposit_amount'=>$data['total_amount'],
    				'currency_type'=>$data['currency_type'],
    				'saving_method'=>$data['account_type'],
    				'term_type'=>$data['pay_every'],
    				//'withdrawing'=>$data['withdrawal'],
    				'interest_rate'=>$data['interest_rate'],
    				'saving_date'=>$data['release_date'],
    				'saving_close'=>$data['date_line'],
    				'reciept_no'=>$data['receipt_num'],
    		);
    		$saving_id = $this->insert($arr);//add member loan
    		unset($datamember);
    		 
    		$remain_principal = $data['total_amount'];
    		$start_date = $data['release_date'];//loan release;
    		$from_date =  $data['release_date'];
    		 
    		$borrow_term = $dbtable->getSubDaysByPaymentTerm($data['pay_every'],null);//return amount day for payterm
    		$amount_borrow_term = $borrow_term*$data['period'];//amount of borrow
    		 
    		$fund_term = $dbtable->getSubDaysByPaymentTerm($data['pay_every'],null);//return amount day for payterm
    		$amount_fund_term = $fund_term*$data['withdrawal'];
    		 
    		$loop_payment = ($amount_borrow_term)/($amount_fund_term);
    		$payment_method = $data['account_type'];
    		$str_next = $dbtable->getNextDateById($data['pay_every'],$data['withdrawal']);//for next,day,week,month;
    		 
    		$this->_name='ln_savingaccountdetailtest';
    		for($i=1;$i<=$loop_payment;$i++){
    			$amount_collect = $data['withdrawal'];
    			if($payment_method==1){//decline//completed
    				$next_payment = $data['date_line'];
    				$next_payment = $dbtable->checkFirstHoliday($next_payment,2);
    				$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    				$interest_paymonth = $remain_principal*($data['interest_rate']/100)*$data['period']/12;
//     				$interest_paymonth = $this->round_up_currency($data['currency_type'],($interest_paymonth));
    				$arr = array(
    						'saving_id'=>$saving_id,
    						'interest_amount'=>$interest_paymonth,
    						'outstanding_balance'=>$remain_principal,
    						'date_withdraw'=>$next_payment,
    				);
    				$member_id = $this->insert($arr);//add member loan
    			}else{//other of saving account
    				if($i!=1){
    					$start_date = $next_payment;
    					$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['withdrawal'],2,$data['release_date']);
    					$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    					//$interest_paymonth = $data['total_amount']*(($data['interest_rate']/12/30)/100)*$amount_day;
    				}else{
    					$next_payment = $data['first_payment'];
    					$next_payment = $dbtable->checkFirstHoliday($next_payment,2);
    					$amount_day = $dbtable->CountDayByDate($from_date,$next_payment)-1;
    					//$interest_paymonth = $data['total_amount']*(($data['interest_rate']/12/30)/100) *$amount_day;
    				}
    				$dialy_amount = ($data['interest_rate']/12/100/30)*$data['total_amount'];
    				$interest_paymonth = ($dialy_amount)*$amount_day;
//     				$interest_paymonth = $this->round_up_currency($data['currency_type'],($interest_paymonth));
    				
    				$arr = array(
    						'saving_id'=>$saving_id,
    						'interest_amount'=>$interest_paymonth,
    						'amount_day'=>$amount_day,
    						'dialy_amount'=>$dialy_amount,
    						'outstanding_balance'=>$remain_principal,
    						'date_withdraw'=>$next_payment,
    				);
    				$this->insert($arr);//add member loan
    				$from_date=$next_payment;
    				if($i!=1){
    					$next_payment = $dbtable->checkDefaultDate($str_next, $start_date, $data['withdrawal'],2,$data['release_date']);
    				}
    			}
    		}
    		$sql = "SELECT d.* , DATE_FORMAT(d.date_withdraw, '%d-%m-%Y') AS date_payments,DATE_FORMAT(d.date_withdraw, '%Y-%m-%d') AS date_name FROM ln_savingaccountdetailtest AS d  WHERE d.saving_id = ".$saving_id;
    		$rows =  $db->fetchAll($sql);
    		$db->commit();
    		return $rows;
    	}catch (Exception $e){
    		$db->rollBack();
    		print_r($e->getMessage());exit();
    		Application_Form_FrmMessage::message("INSERT_FAIL");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
  
}