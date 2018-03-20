<?php

class Installment_Model_DbTable_DbLoanILPayment extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_client_receipt_money';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authloan');
    	return $session_user->user_id;
    	 
    }
    public function getAllinstallmentpayment($search){
		$start_date = $search['start_date'];
    	$end_date = $search['end_date'];
    	
    	$db = $this->getAdapter();
    	$sql = "SELECT lcrm.`id`,
    				(SELECT b.`branch_namekh` FROM `ln_branch` AS b WHERE b.`br_id`=lcrm.`branch_id` LIMIT 	1) AS branch,
					(SELECT sale_no FROM ln_ins_sales_install l WHERE l.id=lcrm.loan_id LIMIT 1) AS loan_number,
					(SELECT c.`name_kh` FROM `ln_client` AS c WHERE c.`client_id`=lcrm.`client_id` LIMIT 1) AS team_group ,
					lcrm.`receipt_no`,
					lcrm.`principal_paid`,
					lcrm.`interest_paid`,
					lcrm.`penalize_paid`,
					lcrm.`recieve_amount`,
					lcrmd.`date_payment`,
					lcrm.`date_input`,
				    'delete'
				FROM `ln_ins_receipt_money` AS lcrm,
				`ln_ins_receipt_money_detail` AS lcrmd
    	 WHERE  lcrm.id=lcrmd.`receipt_id` and lcrm.status=1";
    	$where ='';
    	if(!empty($search['advance_search'])){
    		$s_where = array();
    		$s_search = str_replace(' ', '', addslashes(trim($search['advance_search'])));
    		$s_where[] = "REPLACE(lcrm.`receipt_no`,' ','')    LIKE '%{$s_search}%'";
    		$s_where[] = " (SELECT sale_no FROM `ln_ins_sales_install` WHERE ln_ins_sales_install.id=lcrm.loan_id AND sale_no LIMIT 1 ) LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	if($search['status']!=""){
    		$where.= " AND status = ".$search['status'];
    	}
    	if(!empty($search['start_date']) or !empty($search['end_date'])){
    		$where.=" AND lcrm.`date_input` BETWEEN '$start_date' AND '$end_date'";
    	}
    	if($search['client_name']>0){
    		$where.=" AND lcrm.`client_id`= ".$search['client_name'];
    	}
    	if($search['branch_id']>0){
    		$where.=" AND lcrm.`branch_id`= ".$search['branch_id'];
    	}
    	
//     	if($search['paymnet_type']>0){
//     		$where.=" AND lcrm.`payment_option`= ".$search['paymnet_type'];
//     	}
    	$group_by = " GROUP BY lcrm.id";
    	$order = " ORDER BY receipt_no DESC";
    	return $db->fetchAll($sql.$where.$group_by.$order);
    }
	function getIlPaymentByID($id){
		$db = $this->getAdapter();
		$sql="SELECT 
				(SELECT d.`date_payment` FROM `ln_client_receipt_money_detail` AS d WHERE d.`loan_number`=(SELECT c.loan_number FROM ln_loan_member AS c WHERE c.member_id=(SELECT g.member_id FROM ln_loanmember_funddetail AS g WHERE g.id = d.lfd_id) LIMIT 1) AND d.crm_id NOT IN($id) ORDER BY d.`date_payment` DESC LIMIT 1) AS installment_date ,
				(SELECT lcrm.`date_input` FROM `ln_client_receipt_money` AS lcrm,`ln_client_receipt_money_detail` AS lcrmd WHERE lcrm.`id`!=$id AND lcrmd.`crm_id`=lcrm.`id` AND lcrm.`loan_number`=(SELECT `loan_number` FROM `ln_client_receipt_money_detail` WHERE `crm_id`=$id LIMIT 1) ORDER BY lcrm.`date_input` DESC LIMIT 1) AS last_pay_date,
				  (SELECT crmd.`loan_number` FROM `ln_client_receipt_money_detail` AS crmd WHERE crm.`id`=crmd.`crm_id` LIMIT 1) AS loan_numbers,
				  crm.*,
				  (SELECT lm.amount_collect_principal FROM `ln_loan_member` AS lm WHERE lm.`loan_number`=(SELECT `loan_number` FROM `ln_client_receipt_money_detail` WHERE `crm_id`=$id LIMIT 1) LIMIT 1) AS amount_term,
				  (SELECT lm.`collect_typeterm` FROM `ln_loan_member` AS lm WHERE lm.`loan_number`=(SELECT `loan_number` FROM `ln_client_receipt_money_detail` WHERE `crm_id`=$id LIMIT 1) LIMIT 1) AS collect_typeterm,
				  (SELECT lm.`interest_rate` FROM `ln_loan_member` AS lm WHERE lm.`loan_number`=(SELECT `loan_number` FROM `ln_client_receipt_money_detail` WHERE `crm_id`=$id LIMIT 1) LIMIT 1) AS `interest_rate`,
				  (SELECT lm.`total_capital` FROM `ln_loan_member` AS lm WHERE lm.`loan_number`=(SELECT `loan_number` FROM `ln_client_receipt_money_detail` WHERE `crm_id`=$id LIMIT 1) LIMIT 1) AS `total_capital`,
				  (SELECT g.`date_release` FROM `ln_loan_group` AS g,`ln_loan_member` AS lm WHERE g.`g_id`=lm.`group_id` AND lm.`loan_number` = (SELECT `loan_number` FROM `ln_client_receipt_money_detail` WHERE `crm_id`=$id LIMIT 1) LIMIT 1) AS date_release,
				  (SELECT g.`level` FROM `ln_loan_group` AS g,`ln_loan_member` AS lm WHERE g.`g_id`=lm.`group_id` AND lm.`loan_number` = (SELECT `loan_number` FROM `ln_client_receipt_money_detail` WHERE `crm_id`=$id LIMIT 1) LIMIT 1) AS level,
				  (SELECT g.`total_duration` FROM `ln_loan_group` AS g,`ln_loan_member` AS lm WHERE g.`g_id`=lm.`group_id` AND lm.`loan_number` = (SELECT `loan_number` FROM `ln_client_receipt_money_detail` WHERE `crm_id`=$id LIMIT 1) LIMIT 1) AS total_duration,
				  (SELECT g.`payment_method` FROM `ln_loan_group` AS g,`ln_loan_member` AS lm WHERE g.`g_id`=lm.`group_id` AND lm.`loan_number` = (SELECT `loan_number` FROM `ln_client_receipt_money_detail` WHERE `crm_id`=$id LIMIT 1) LIMIT 1) AS payment_method
				FROM
				  `ln_client_receipt_money` AS crm
				  
				WHERE crm.id =$id";
		//echo $sql;
		return $db->fetchRow($sql);
	}
	public function getIlDetail($id){
		$db = $this->getAdapter();
		$sql="SELECT
		(SELECT d.`date_payment` FROM `ln_client_receipt_money_detail` AS d WHERE d.`loan_number`=crmd.loan_number AND d.id NOT IN($id) ORDER BY d.`date_payment` DESC LIMIT 1) AS installment_date ,
		(SELECT crm.`date_input` FROM `ln_client_receipt_money` AS crm,`ln_client_receipt_money_detail` AS crmd WHERE crm.`id`=crmd.`crm_id` AND crm.`id` != $id
		AND crmd.`lfd_id` = (SELECT c.`lfd_id` FROM `ln_client_receipt_money_detail` AS c WHERE c.`crm_id`=$id LIMIT 1) ORDER BY crm.`id` DESC LIMIT 1)  AS last_pay_date ,
		(SELECT `currency_id` FROM `ln_client_receipt_money_detail` WHERE crm_id = $id LIMIT 1) AS `currency_type`,
		(SELECT crm.`recieve_amount` FROM `ln_client_receipt_money` AS crm WHERE crm.`id`=$id ) AS recieve_amount,
		(SELECT crm.`receiver_id` FROM `ln_client_receipt_money` AS crm WHERE crm.`id`=$id ) AS receiver_id,
		(SELECT c.`client_number` FROM `ln_client` AS c WHERE crmd.`client_id`=c.`client_id` LIMIT 1) AS client_number,
		(SELECT c.`name_kh` FROM `ln_client` AS c WHERE crmd.`client_id`=c.`client_id` LIMIT 1) AS name_kh,
		crmd.*
		FROM
		`ln_client_receipt_money_detail` AS crmd
		WHERE crmd.`crm_id` =$id";
		return $db->fetchAll($sql);
	}
	
	public function getAllIlDetail($id){
		$db = $this->getAdapter();
		$sql="SELECT
			(SELECT `currency_id` FROM `ln_client_receipt_money_detail` WHERE crm_id = crmd.`crm_id` LIMIT 1) AS `currency_type`,
			(SELECT c.`client_number` FROM `ln_client` AS c WHERE crmd.`client_id`=c.`client_id`) AS client_number,
			(SELECT c.`name_kh` FROM `ln_client` AS c WHERE crmd.`client_id`=c.`client_id`) AS name_kh,
			crmd.*
		FROM `ln_client_receipt_money_detail` AS crmd WHERE crmd.`crm_id` = $id";
		return $db->fetchAll($sql);
	}

    public function getIlPaymentNumber(){
    	$this->_name='ln_client_receipt_money';
    	$db = $this->getAdapter();
    	$sql=" SELECT id  FROM $this->_name ORDER BY id DESC LIMIT 1 ";
    	$acc_no = $db->fetchOne($sql);
    	$new_acc_no= (int)$acc_no+1;
    	$acc_no= strlen((int)$acc_no+1);
    	$pre = "";
    	$pre_fix="PM-";
    	for($i = $acc_no;$i<5;$i++){
    		$pre.='0';
    	}
    	return $pre_fix.$pre.$new_acc_no;
    }
	
	public function getIlPaymentRPNumber($co_id){
    	$this->_name='ln_client_receipt_money';
    	$db = $this->getAdapter();
    	$sql=" SELECT id,`receipt_no` FROM `ln_client_receipt_money` WHERE `co_id`= $co_id ORDER BY id DESC LIMIT 1";
    	$acc_no = $db->fetchRow($sql);
    	$new_acc_no= $acc_no["receipt_no"]+1;
    	/*$sql=" SELECT id  FROM `ln_client_receipt_money` ORDER BY id DESC LIMIT 1";
    	$acc_no = $db->fetchOne($sql);
		$new_acc_no= (int)$acc_no+1;
		$acc_no= strlen((int)$acc_no+1);
    	$pre = "";
    	$pre_fix="PM-";
    	for($i = $acc_no;$i<5;$i++){
    		$pre.='0';
    	}
    	return $pre_fix.$pre.$new_acc_no;*/
		return $new_acc_no;
    }
	function cancelPayment($id){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
    	$session_user=new Zend_Session_Namespace('authloan');
    	$user_id = $session_user->user_id;
		$rows= $this->getIlPaymentByID($id);
		$option_pay= $rows["payment_option"];
		$rows_detail = $this->getAllIlDetail($id);
		$loan_number = $rows_detail[0]["loan_number"];
		foreach($rows_detail as $row){
			if($option_pay==1){
				
					$arr = array(
						'principle_after'		=>	$row["principal_permonth"],
						'total_interest_after'	=>	$row["old_interest"],
						'penelize'				=>	$row["old_penelize"],
						'service_charge'		=>	$row["service_charge"],
						'total_payment_after'	=>	$row["principal_permonth"]+$row["old_interest"]+$row["old_penelize"]+$row["service_charge"],
						'is_completed'	=>	0,
					);
					$this->_name = "ln_loanmember_funddetail";
				$where = $db->quoteInto("id=?", $row['lfd_id']);
				$db->getProfiler()->setEnabled(true);
				$this->update($arr,$where);
				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
 		   				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
 		   				$db->getProfiler()->setEnabled(false);
				
			}else{
				$arr = array(
					'is_completed'	=>	0,
				);
				
				$this->_name = "ln_loanmember_funddetail";
				$where = $db->quoteInto("id=?", $row['lfd_id']);
				$db->getProfiler()->setEnabled(true);
				$this->update($arr,$where);
				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
 		   				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
 		   				$db->getProfiler()->setEnabled(false);
				
				$sql_payment ="SELECT
						   					l.*
						   				FROM
							   				`ln_loanmember_funddetail` AS l,
							   				`ln_loan_member` AS m
						   				WHERE l.`member_id` = m.`member_id`
							   				AND m.`loan_number` = '$loan_number'
							   				AND l.`is_completed` = 0 ";
 		   				$db->getProfiler()->setEnabled(true);
		   				$rs_payment = $db->fetchRow($sql_payment);
		   				
 		   				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
 		   				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
 		   				$db->getProfiler()->setEnabled(false);

						$group_id = $rows_detail[0]["client_id"];
		   				if(empty($rs_payment)){
			   				$sql ="UPDATE
					   					`ln_loan_group` AS l
					   				SET l.`status` = 1
					   				WHERE l.`g_id`= (SELECT m.`group_id` FROM `ln_loan_member` AS m WHERE m.`loan_number`='$loan_number' LIMIT 1)
					   					AND l.`group_id`= $group_id AND l.`loan_type`=1 ";
			   				$db->getProfiler()->setEnabled(true);
			   				
			   				$db->query($sql);
			   				
			   				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
			   				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
			   				$db->getProfiler()->setEnabled(false);
			   				
			   				$sql_loan_memeber ="UPDATE `ln_loan_member` AS m SET m.`is_completed`=0 WHERE m.`loan_number`= '$loan_number'";
			   				
			   				$db->getProfiler()->setEnabled(true);
			   				$db->query($sql_loan_memeber);
			   				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
			   				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
			   				$db->getProfiler()->setEnabled(false);
		   				}
			}
		}
			$sql_r = "DELETE FROM ln_client_receipt_money WHERE id="."'".$id."'";
			$db->query($sql_r);
			
			$sql_d = "DELETE FROM ln_client_receipt_money_detail WHERE crm_id="."'".$id."'";
			$db->commit();
		}catch (Exception $e){
    		$db->rollBack();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
}
function getAllRemainSchedule($loan_id){
	$sql="
		SELECT * 
			FROM `ln_loan_detail`
				WHERE loan_id=$loan_id AND status=1 
					AND is_completed=0 
						ORDER BY date_payment ASC ";
	return $this->getAdapter()->fetchAll($sql);
	
}
public function addILPayment($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	$session_user=new Zend_Session_Namespace('authloan');
    	$user_id = $session_user->user_id;
    	try{
    	$reciept_no = $data['reciept_no'];
    	$sql="SELECT id  FROM ln_client_receipt_money WHERE receipt_no='$reciept_no' ORDER BY id DESC LIMIT 1 ";
    	$acc_no = $db->fetchOne($sql);    	
    	if($acc_no){
    		$reciept_no=$this->getIlPaymentNumber();
    	}else{
    		$reciept_no = $data['reciept_no'];
    	}			
		$amount_receive = $data["amount_receive"];//ប្រាក់ទទួលបាន
		$remain_money = $amount_receive;
		$total_payment = $data["total_payment"];//ត្រូវបង់សរុប
		$total_interest = $data["total_interest"];//ត្រូវបង់សរុប
		$total_principal = $data["os_amount"];//ត្រូវបង់សរុប
		
    	$return = $data["amount_return"];//ប្រាក់អាប់
    	$option_pay = $data["option_pay"];//បង់ជាអ្វី ដើម,ធម្មតា និង 
    	$service_charge= $data["service_charge"];//សេវាផ្សេងៗ
    	$penalize = $data["penalize_amount"];//ផាកពិន័យ
    	
	    	if($amount_receive>=$total_payment){
	    		$is_compleated = 1;
	    	}elseif($amount_receive<$total_payment){
	    		$is_compleated = 0;
	    	}
    	
    		$arr_client_pay = array(
	    			'co_id'				=> $data['co_id'],
					'client_id'		=> $data['client_id'],
	    			'receipt_no'		=> $reciept_no,
	    			'branch_id'			=> $data['branch_id'],
	    			'date_pay'			=> $data['collect_date'],
	    			'date_input'		=> $data['collect_date'],
    				'paid_times'        => $data['installment_no'],
    				'loan_id'			=> $data['loan_number'],
    				'date_payment'    	=> $data['payment_date'],
    				'begining_balance'	=> $data['priciple_amount'],
    				'principal_amount'	=> $data['os_amount'],
    				'interest_amount'	=> $data['total_interest'],
    				'total_payment'		=> $total_payment,
    				'penalize_amount'	=> $data["penalize_amount"],
    				'service_chargeamount'=>$data["service_charge"],
    				
    				'recieve_amount'	=> $data["amount_receive"],
    				'total_paymentpaid'	=> $data["amount_receive"],//check
//     				'principal_paid'	=> $data['os_amount'],//check here
//     				'interest_paid'		=> $data['total_interest'],//check
//     				'penalize_paid'		=> 0,//check
//     				'service_paid'      => 0,//echeck
    				'return_amount'		=> $return,
    				
    				'note'				=> $data['note'],
    				'status'			=> 1,
    				'user_id'			=> $user_id,
    				'late_day'			=> $data['amount_late'],
    				'is_completed'		=> $is_compleated,
    				'payment_option'	=> $data["option_pay"],
    				'currency_type'		=> $data["currency_type"],
//     				'is_payoff'=>0
    				);
    		
		$this->_name = "ln_client_receipt_money";
    	$receipt_id = $this->insert($arr_client_pay);
    		
    	$date_collect = $data["collect_date"];
    	$identify = explode(',',$data['identity']);
		$count_d = count($identify);
		
		$paid_principalall = 0;// ត្រូវទាំងអស់ព្រោះការពារបង់២ record ទី៣ វាបូកបញ្ចូល Paid អោយដែ
		$paid_interestall = 0;
		$paid_penaltyall = 0;
		$paid_serviceall = 0;
		
		$set_service=0;//សម្រាប់បង់ថ្លៃសេវាកម្មតែម្តង
		$set_penalty=0;//សម្រាប់បង់ថ្លៃផាគពិន័យតែម្តង
		
		$after_service = $data['service_charge'];
		$resultloan = $this->getAllRemainSchedule($data['loan_number']);
		if(!empty($resultloan))foreach($resultloan AS $key => $rsloan){
	    	if($remain_money<=0){break;}
					$after_outstanding= $rsloan['outstanding_after'];
					$after_payment_after= $rsloan['total_payment_after'];
	    			$after_principal= $rsloan['principle_after'];//$data["principal_permonth_".$i];
	    			$total_principal=$after_principal;
	    			$after_interest = $rsloan['total_interest_after'];//$data["interest_".$i];
		    		if($option_pay!=4){
	    				$total_interest = $after_interest;
		    		}
	    			$after_penalty = $rsloan['penelize_service'];//$data["penelize_".$i];
	    			$date_payment = $rsloan['date_payment'];//$data["date_payment_".$i];
	    			
	    			$paid_principal = 0;
	    			$paid_interest = 0;
	    			$paid_penalty = 0;
	    			$paid_service = 0;
	    			$is_compleated_d=0;
	    			if($key!=0){
	    				$penalize = 0;//ធ្លាប់បងហើយម្តង អោយ =0
	    				$service_charge=0;
	    				if($option_pay==4){
	    					$total_interest=0;
	    				}
	    			}
	    			
	    			$record_id = $rsloan['id'];//$data["mfdid_".$i];
	    			if($record_id!=""){
	    				if($option_pay==1 OR $option_pay==2 OR $option_pay==3 OR $option_pay==4){//បង់ធម្មតា
	    					if($option_pay==1){
	    						$total_principal =$after_principal;
	    					}elseif($option_pay==3){
	    						$total_interest=0;
	    						$total_principal = $after_principal;//$data["principal_permonth_".$i];
	    					}elseif($option_pay==2){//ដើម្បីអោយគណនា១ Record ម្តងៗរក completed=1
	    						$total_interest=$after_interest;//$data["interest_".$i];
	    						$total_principal =$after_principal;// $data["principal_permonth_".$i];
	    					}
	    					$remain_money = $remain_money-$service_charge;
	    					if($remain_money>=0){//ដកសេវាកម្ម
	    						$paid_service=$service_charge;
	    						$after_service=0;
	    						
	    						$remain_money = $remain_money - $penalize;
	    						
	    						if($remain_money>=0){//ដកផាគពិន័យ
	    							$paid_penalty = $penalize;
	    							$principle_after=0;
	    							
	    							$remain_money = $remain_money - $total_interest;	 
	    										
	    							if($remain_money>=0){
	    								$paid_interest = $total_interest;
	    								$after_interest = 0;
	    								
	    								$remain_money = ($remain_money)-($total_principal);
	    								if($remain_money>=0){//check here of គេបង់លើសខ្លះ
	    									$paid_principal = $total_principal;
	    									$after_principal =0;
	    									$is_compleated_d=1;
	    								}else{
	    									$paid_principal = $total_principal-abs($remain_money);
	    									$after_principal = abs($remain_money);
	    									$is_compleated_d=0;
	    								}
	    							}else{
	    								$paid_interest = $total_interest-abs($remain_money);
	    								$after_interest =abs($remain_money);
	    							}
	    						}else{
	    							$paid_penalty =$penalize -abs($remain_money);
	    							$after_penalty = abs($remain_money);
	    						}
	    					}else{
	    						$paid_service=$service_charge-abs($remain_money);
	    						$after_service = abs($remain_money);
	    					}
	    					
		    					$arr_money_detail = array(
		    						'receipt_id'		=> $receipt_id,
		    						'lfd_id'			=> $record_id,
		    						'date_payment'		=> $date_payment,
		    						'capital'			=> $rsloan['outstanding_after'],
		    						'remain_capital'	=> $rsloan['outstanding_after'],
		    						'principal_permonth'=> $rsloan['principle_after'],
		    						'total_interest'	=> $rsloan['total_interest_after'],
		    						'total_payment'		=> $rsloan['total_payment_after'],
// 		    						'total_recieve'		=> $rsloan['outstanding_after'],
		    						'penelize_amount'	=> 0,//$rsloan['outstanding_after'],
		    					);
		    				
		    				$db->insert("ln_client_receipt_money_detail", $arr_money_detail);
		    				
		    				if($after_principal==0){
		    					$is_compleated_d=1;
		    				}
		    				$load_detail = array(
	    						'outstanding_after'   => $after_outstanding-$paid_principal,
	    						'principle_after'     => $after_principal,
	    						'total_interest_after'=> $after_interest,
	    						'total_payment_after' => $after_principal+$after_interest,
	    						'is_completed'		  => $is_compleated_d,
		    				);
		    				
		    				$this->_name="ln_loan_detail";
		    				$where = $db->quoteInto("id=?", $record_id);
		    				$this->update($load_detail, $where);
		    				
	    				$paid_principalall =$paid_principalall+$paid_principal;
	    				$paid_interestall =$paid_interestall+$paid_interest;
	    				$paid_penaltyall =$paid_penaltyall+$paid_penalty;
	    				$paid_serviceall =$paid_serviceall+$paid_service;
		    				
	    				}
	    			}
	    	}
	    	$arr = array(
    			'principal_paid'=> $paid_principalall,//check here
    			'interest_paid'	=> $paid_interestall,//check
    			'penalize_paid'	=> $paid_penaltyall,//check
    			'service_paid'  => $paid_serviceall,//echeck
	    	);
	    	$this->_name="ln_client_receipt_money";
	    	$where = $db->quoteInto("id=?", $receipt_id);
	    	$this->update($arr, $where);
	    	
	    	$rs = $this->getRemainSchedule($data['loan_number']);
	    	if(empty($rs)){//update ករណីបង់ចុងក្រោយ គឺ updatE ទៅជាដាច់
	    		$arr = array(
	    				'is_payoff'=> 1,//check here
	    				'payment_option'=>4
	    		);
	    		
	    		$this->_name="ln_client_receipt_money";
	    		$where = $db->quoteInto("id=?", $receipt_id);
	    		$this->update($arr, $where);
	    		
	    		$arr = array('is_completed'=>1);
	    		$this->_name="ln_loan";
	    		$where = $db->quoteInto("id=?", $data['loan_number']);
	    		$this->update($arr, $where);
	    	}

    		$db->commit();
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    function deleteRecord($id){
    	
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$sql = "SELECT
    		crm.loan_id
    		FROM ln_client_receipt_money AS crm
    		WHERE  crm.`id` = $id ";
    		$loan_id = $db->fetchOne($sql);

    		$arr = array(
			'is_completed'=>0
			);
    		$this->_name="ln_loan";
    		$where="id=".$loan_id;
    		$this->update($arr, $where);
    		   
    		$sql = "SELECT
    		crmd.*
    		FROM
    		`ln_client_receipt_money_detail` AS crmd,
    		ln_client_receipt_money as crm
    		WHERE
    		crm.id = crmd.`receipt_id`
    		and crmd.`receipt_id` = $id ";
    		$receipt_money_detail = $db->fetchAll($sql);

    		$this->_name = "ln_loan_detail";
    		if(!empty($receipt_money_detail)){
    			foreach ($receipt_money_detail as $rs){
    				$arra = array(
    						'outstanding_after'=>$rs['capital'],
    						'principle_after'=>$rs['principal_permonth'],
    						'total_interest_after'=>$rs['total_interest'],
    						'total_payment_after'=>$rs['total_payment'],
    						'is_completed'=>0,
    						'status'=>1,
    				);
    				$where = "id=".$rs['lfd_id'];
    				$this->update($arra, $where);
    			}
    		}
    
    		$this->_name = "ln_client_receipt_money";
    		$where = " id = $id ";
    		$this->delete($where);
    
    		$this->_name = "ln_client_receipt_money_detail";
    		$where = " receipt_id = $id ";
    		$this->delete($where);
    		$db->commit();
    
    	}catch (Exception $e){
			
    		$db->rollBack();
    
    	}
    }
    function getRemainSchedule($loan_id){
    	$db = $this->getAdapter();
    	$sql="SELECT *
   				FROM
	   				`ln_loan_detail` AS d
   				WHERE 
   					d.amount_paidprincipal=0
   					AND d.`is_completed` = 0
   					AND d.status=1
   				 	AND d.`loan_id` = $loan_id ORDER BY date_payment ASC ";
    	return $db->fetchRow($sql);
    	
    }
    function updateIlPayment($data){
    	$db = $this->getAdapter();
    	$session_user=new Zend_Session_Namespace('authloan');
    	$user_id = $session_user->user_id;
    	$db_loan = new Loan_Model_DbTable_DbLoanILPayment();
    	$db_group = new Loan_Model_DbTable_DbGroupPayment();
    	$db->beginTransaction();
    	$is_set = 0;
    	try{
	    	$id= $data["id"];
	    	$loan_number = $data['loan_number'];
	    	$amount_receive = $data["amount_receive"];
	    	$total_payment = $data["total_payment"];
	    	$return = $data["amount_return"];
	    	$option_pay = $data["option_pay"];
	    	
	    	if($amount_receive>$total_payment){
	    		$amount_payment = $amount_receive - $return;
	    		$total_pay = $amount_receive - $data["total_payment"];
	    		$is_completed = 1;
	    	}elseif($amount_receive<$total_payment){
	    		$amount_payment = $amount_receive;
	    		$total_pay = $data["total_payment"]-$amount_receive;
	    		$is_completed = 0;
	    	}else{
	    		$amount_payment = $total_payment;
	    		$is_completed = 1;
	    	}
	    	
	    	$service_charge= $data["service_charge"];
	    	$penalize = $data["penalize_amount"];
	    	
    		$arr_client_pay = array(
    			'co_id'							=>		$data['co_id'],
    			'group_id'						=>		$data["client_id"],
    			'receiver_id'					=>		$data['reciever'],
    			'branch_id'						=>		$data['branch_id'],
    			'date_input'					=>		$data['collect_date'],
    			'principal_amount'				=>		$data["priciple_amount"],
    			'total_principal_permonth'		=>		$data["os_amount"],
    			'total_payment'					=>		$data["total_payment"],
    			'total_interest'				=>		$data["total_interest"],
    			'recieve_amount'				=>		$data["amount_receive"],
    			'penalize_amount'				=>		$data['penalize_amount'],
    			'return_amount'					=>		$return,
    			'service_charge'				=>		$data["service_charge"],
    			'note'							=>		$data['note'],
    			'user_id'						=>		$user_id,
    			'is_group'						=>		0,
    			'payment_option'				=>		$data["option_pay"],
    			'currency_type'					=>		$data["currency_type"],
    			'status'						=>		1,
    			'amount_payment'				=>		$amount_payment,
    			'is_completed'					=>		$is_completed
    		);
    		$this->_name = "ln_client_receipt_money";
    		$where = $db->quoteInto("id=?", $data["id"]);
    		$client_pay = $this->update($arr_client_pay, $where);
    		
    		// Update Loan Fund detail to ild before  
    		$recipt_money = $db_loan->getReceiptMoneyById($id);
			$recipt_money_detail = $db_loan->getReceiptMoneyDetailByID($id);
			foreach ($recipt_money_detail as $row_recipt_detail){
				$fun_id = $row_recipt_detail["lfd_id"];
				if($is_set!=1){
					$loan_number = $row_recipt_detail["loan_number"];
					$rs_fun = $db_group->getFunDetailByLoanNumber($loan_number);
					$rs_group = "SELECT l.id FROM ln_loan AS l WHERE l.loan_number = '$loan_number' GROUP BY l.loan_number";
					$group_id = $db->fetchOne($rs_group);
					if(empty($rs_fun) or $rs_fun=="" or $rs_fun==null){
						$arr_member = array(
							'is_completed'	=>	0,
						);
						$this->_name = "ln_loan";
						$where = $db->quoteInto("loan_number=?", $loan_number);
						$this->update($arr_member, $where);
						
						$arr_group = array(
							'status'	=>	1,
						);
						$this->_name = "ln_loan_group";
						$where = $db->quoteInto("g_id=?", $group_id);
						$this->update($arr_group, $where);
					}
					$is_set=1;
				}
				if($recipt_money["payment_option"]==1){
					$is_completed = $db_loan->getFunDetailById($fun_id);
					if($is_completed==1){
						$arr_update = array(
							'is_completed' =>	0,
						);
						$this->_name = "ln_loanmember_funddetail";
						$where = $db->quoteInto("id=?", $fun_id);
						
						$this->update($arr_update, $where);
					}else{
						$arr_update = array(
							'principle_after'		=> 	$row_recipt_detail["principal_permonth"],
							'total_interest_after'	=>	$row_recipt_detail["total_interest"],
							'penelize'				=>	$row_recipt_detail["old_penelize"],
							'service_charge'		=>	$row_recipt_detail["old_service_charge"],
							'total_payment_after'	=>	$row_recipt_detail["principal_permonth"]+$row_recipt_detail["total_interest"]+$row_recipt_detail["old_penelize"]+$row_recipt_detail["old_service_charge"],	
							'is_completed' 			=>	0,
						);
						$this->_name = "ln_loanmember_funddetail";
						$where = $db->quoteInto("id=?", $fun_id);
						$this->update($arr_update, $where);
					}
				}else{
					$arr_update = array(
							'is_completed' =>	0,
					);
					$this->_name = "ln_loanmember_funddetail";
					$where = $db->quoteInto("id=?", $fun_id);
					$this->update($arr_update, $where);
				}
			}
			//End of Update to old value
			//Delete Reciept money detail before insert new record
			$sql_cmd = "DELETE FROM `ln_client_receipt_money_detail` WHERE `crm_id` = $id";
			$db->query($sql_cmd);
    		//End of delete record before insert new record
			//insert new record for reciept money
			 
    		$identify = explode(',',$data['identity']);
    	foreach($identify as $i){
    			if($option_pay==1){
    				$total_recieve = $data["amount_receive"];
    			}else{
    				$total_recieve=$data["payment_".$i];
    			}
    			$client_detail = $data["mfdid_".$i];
    			$sub_recieve_amount = $data["amount_receive"];
    			$sub_service_charge = $data["service_".$i];
    			$sub_peneline_amount = $data["penelize_".$i];
    			$sub_interest_amount = $data["interest_".$i];
    			$sub_principle= $data["principal_permonth_".$i];
    			$sub_total_payment = $data["payment_".$i];
    			$date_payment = $data["date_payment_".$i];
    			if($client_detail!=""){
    				$arr_money_detail = array(
    						'crm_id'				=>		$id,
    						'loan_number'			=>		$data['loan_number'],
    						'lfd_id'				=>		$data["mfdid_".$i],
    						'client_id'				=>		$data["client_id_".$i],
    						'date_payment'			=>		$data["date_payment_".$i],
    						'capital'				=>		$data["total_priciple_".$i],
    						'remain_capital'		=>		$data["total_priciple_".$i] - $data["principal_permonth_".$i],
    						'principal_permonth'	=>		$data["principal_permonth_".$i],
    						'total_interest'		=>		$data["interest_".$i],
    						'total_payment'			=>		$data["payment_".$i],
    						'total_recieve'			=>		$total_recieve,
    						'currency_id'			=>		$data["currency_type"],
    						'pay_after'				=>		$data['multiplier_'.$i],
    						'penelize_amount'		=>		$data['penalize_amount'],
    						'service_charge'		=>		$data['service_charge'],
    						'old_penelize'			=>		$data['old_penelize_'.$i],
    						'old_service_charge'	=>		$data['service_'.$i],
    						'is_completed'			=>		1,
    						'is_verify'				=>		0,
    						'verify_by'				=>		0,
    						'is_closingentry'		=>		0,
    						'status'				=>		1
    				);
    				
    				$db->insert("ln_client_receipt_money_detail", $arr_money_detail);
    				
    				//update loan fund detail for is_completed =1  if reciept amount is more than total payment and calculate amount if if reciept amount is less than total payment
    				if($option_pay==1){
	    				if($sub_recieve_amount>=$total_payment){
		    				 
		    				$arr_update_fun_detail = array(
		    					'is_completed'		=> 	1,
		    				);
		    				$this->_name="ln_loanmember_funddetail";
		    				$where = $db->quoteInto("id=?", $data["mfdid_".$i]);
		    				
		    				$this->update($arr_update_fun_detail, $where);
	    				}else{
			   					$new_sub_interest_amount = $data["interest_".$i];
			   					$new_sub_penelize = $data["penelize_".$i];
			   					$new_sub_service_charge = $data["service_".$i];
			   					$principle_after = $data["principal_permonth_".$i];
			   					$pyament_after = $total_payment-$amount_receive;
				   				if($sub_recieve_amount>0){
				   					$new_amount_after_service = $sub_recieve_amount-$new_sub_service_charge;
				   					if($new_amount_after_service>=0){
				   						$new_sub_service_charge = 0;
				   						$new_amount_after_penelize = $new_amount_after_service - $new_sub_penelize;
				   						if($new_amount_after_penelize>=0){
				   							$new_sub_penelize = 0;
				   							$new_amount_after_interest = $new_amount_after_penelize - $sub_interest_amount;
				   							if($new_amount_after_interest>=0){
				   								$new_sub_interest_amount = 0;
				   								$principle_after = $principle_after - $new_amount_after_interest;
				   							}else{
				   								$new_sub_interest_amount = abs($new_amount_after_interest);
				   							}
				   						}else{
				   							$new_sub_penelize = abs($new_amount_after_penelize);
				   						}
				   					}else{
				   						$new_sub_service_charge = abs($new_amount_after_service);
				   					}
				   				}
				   				
				   				$arr_update_fun_detail = array(
				   						'is_completed'			=> 	0,
				   						'total_interest_after'	=>  $new_sub_interest_amount,
				   						'total_payment_after'	=>	$pyament_after,
				   						'penelize'				=>	$new_sub_penelize,
				   						'principle_after'		=>	$principle_after,
				   						'service_charge'		=>	$new_sub_service_charge,
				   						'payment_option'		=>	1
				   				);
				   				$this->_name="ln_loanmember_funddetail";
				   				$where = $db->quoteInto("id=?", $data["mfdid_".$i]);
				   				$this->update($arr_update_fun_detail, $where);
						}
    					}else{
    						
    						$sql_loan_fun = "SELECT 
											  d.`id` ,
											  d.`total_principal`,
											  d.`principle_after`,
											  d.`total_interest_after`,
											  d.`service_charge`,
											  d.`penelize`
											FROM
											  `ln_loan_detail` AS d,
											  `ln_loan` AS l
											WHERE 
												l.id= d.loan_id
  												AND l.`loan_number` = '$loan_number' 
    											AND d.`date_payment` = '$date_payment'";
    						$row_fun = $db->fetchAll($sql_loan_fun);
    						$is_set=0;
    						foreach ($row_fun as $rs_fun){
    							if($is_set!=1){
    								$penelize=$data["penelize_".$i];
    								$is_set=1;
    							}else{
    								$penelize = $rs_fun['penelize'];
    							}
    							$total_pay=$rs_fun["principle_after"]+$rs_fun["total_interest_after"]+$penelize; 
    							
    							$arr_update_fun_detail = array(
    									'is_completed'			=> 	1,
    									'payment_option'		=>	$data["option_pay"]
    							);
    							$this->_name="ln_loanmember_funddetail";
    							$where = $db->quoteInto("id=?", $rs_fun['id']);
    							
    							$this->update($arr_update_fun_detail, $where);
    						}
    					}
		   				// end of update is_completed in loan fund detail
    					// update loan member and loan group
		   				$sql_payment ="SELECT
						   					l.*
						   				FROM
							   				`ln_loanmember_funddetail` AS l,
							   				`ln_loan_member` AS m
						   				WHERE l.`member_id` = m.`member_id`
							   				AND m.`loan_number` = '$loan_number'
							   				AND l.`is_completed` = 0 ";
		   				$rs_payment = $db->fetchRow($sql_payment);
		   				$group_id = $data["client_code"];
		   				if(empty($rs_payment)){
			   				$sql ="UPDATE
					   					`ln_loan_group` AS l
					   				SET l.`status` = 2
					   				WHERE l.`g_id`= (SELECT m.`group_id` FROM `ln_loan_member` AS m WHERE m.`loan_number`='$loan_number' LIMIT 1)
					   					AND l.`group_id`= $group_id AND l.`loan_type`=2";
			   				$db->query($sql);
			   				
			   				$sql_loan_memeber ="UPDATE `ln_loan_member` AS m SET m.`is_completed`=1 WHERE m.`loan_number`= '$loan_number'";
			   				
			   				$db->query($sql_loan_memeber);
		   				}
    			}
    		}
//     		exit();
    		$db->commit();
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
 
   function getAllPaymentListBySender($client_id){
   		$db = $this->getAdapter();
   		$sql = " CALL `stgetAllPaymentById`($client_id)";
   		return $db->fetchAll($sql);
   }

   function getLoanPaymentByLoanNumber($data){
    	$db = $this->getAdapter();
    	$loan_number= $data['loan_number'];
    	
    		$sql="
    			SELECT
			   	lc.`client_id`,
			   	lc.`client_number`,
			   	lc.`name_kh`,
			   	l.`loan_number`,
				l.`currency_type`,
   				l.`branch_id`,
   				l.`co_id`,
   				l.`payment_method`,
   				l.collect_typeterm,
   				l.amount_collect_principal,
   				l.interest_rate,
   				l.loan_amount,
   				l.level,
   				l.total_duration,
   				DATE_FORMAT(l.date_release, '%d/%m/%Y') AS `date_release`,
   				l.date_release AS loan_releate,
   				(SELECT date_pay FROM `ln_client_receipt_money` WHERE loan_id=$loan_number AND status=1 ORDER BY date_pay DESC LIMIT 1) AS last_pay_date,
   				(SELECT date_payment FROM `ln_client_receipt_money` WHERE loan_id=$loan_number AND status=1 ORDER BY date_payment DESC LIMIT 1) AS prev_paymentdate,
   				 ld.*,
   				DATE_FORMAT(ld.date_payment, '%d-%m-%Y') AS `date_payments`,
   				(SELECT SUM(principal_paid) FROM `ln_client_receipt_money` WHERE loan_id=l.id AND status=1) AS principal_paid
   			   FROM
   					  `ln_client` AS lc,
   					  `ln_loan` AS l ,
   					  `ln_loan_detail` AS ld
   				WHERE 
   					 l.status=1
   					 AND l.is_completed=0
   					 AND l.is_badloan=0
   					 AND l.`customer_id`=lc.`client_id`
   					 AND l.`id` = ld.`loan_id`	  
   					 AND ld.`status`=1
   					 AND ld.is_completed=0
   					 AND l.`loan_type`=1   					     					   
   					 AND l.`id` = ".$loan_number;
    		
    	return $db->fetchAll($sql);
   }
   
   function getAllLoanPaymentByLoanNumber($data){
   	$db = $this->getAdapter();
   	$loan_number= $data['loan_numbers'];
   	
   	$where = 'l.`id`='."'".$loan_number."'";
   	$sql ="SELECT
				   	l.`id`,
				   	lc.`client_number`,
				   	lc.`name_kh`,
				   	l.`loan_number`,
					l.`currency_type`,
   					l.`branch_id`,
   					l.`collect_typeterm`,
   					l.`co_id`,
   					l.`payment_method`,
   					d.*,
   					DATE_FORMAT(d.date_payment, '%d-%m-%Y') AS `date_payments`
   					FROM
   					 `ln_client` AS lc,
   					 `ln_loan` AS l,
   					 `ln_loan_detail` AS d
   					  WHERE 
   					  l.`id`=d.`loan_id`
   					  AND l.`customer_id`=lc.`client_id`
   					  AND l.`status`=1
   					  AND l.`loan_type`=1
   					  AND d.`status`=1
   					  AND $where";   
   		return $db->fetchAll($sql);
}

   function getAllCo(){
   			$db = $this->getAdapter();
   			$sql="SELECT `co_id` AS id,CONCAT(`co_firstname`,' ',`co_lastname`,'- ',`co_khname`) AS `name`,`branch_id` FROM `ln_co` WHERE `position_id`=1 AND (`co_khname`!=''  OR `co_firstname`!='')" ;
   			return $db->fetchAll($sql);
   		
   }
   function getAllClient(){
   	$db = $this->getAdapter();
   	$sql = "SELECT c.`client_id` AS id ,c.`name_kh` AS name ,c.`branch_id` FROM `ln_client` AS c WHERE c.`name_kh`!='' " ;
   	return $db->fetchAll($sql);
   }
   
   function getAllClientCode(){
   	$db = $this->getAdapter();
   	$sql = "SELECT c.`client_id` AS id ,c.`client_number` AS name ,c.`branch_id` FROM `ln_client` AS c WHERE c.`name_kh`!='' " ;
   	return $db->fetchAll($sql);
   }
   
   public function getLastPayDate($data){
   	$loanNumber = $data['loan_numbers'];
   	$db = $this->getAdapter();
   	//$sql = "SELECT c.`date_input` FROM `ln_client_receipt_money` AS c WHERE c.`loan_number`='$loanNumber' ORDER BY c.`date_input` DESC LIMIT 1";
   	$sql ="SELECT 
			  lf.`date_payment`
			FROM
			  `ln_loanmember_funddetail` AS lf,
			  `ln_client_receipt_money` AS c,
			  `ln_loan_member` AS lm
			WHERE c.`loan_number` = lm.`loan_number`
			  AND lm.`member_id` = lf.`member_id`
			  AND c.`loan_number` = '$loanNumber' 
			  AND lf.`is_completed`=1
			ORDER BY lf.`id` DESC LIMIT 1";
   	//return $sql;
   	return $db->fetchOne($sql);
   }
   
   public function getLastPaymentDate($data){
   	$loanNumber = $data['loan_numbers'];
   	$fn_id = $data["fn_id"];
   	$db = $this->getAdapter();
   	$sql = "SELECT 
			  c.`date_input` 
			FROM
			  `ln_client_receipt_money` AS c,
			  `ln_client_receipt_money_detail` AS cr 
			WHERE c.`loan_number` = '$loanNumber' 
			  AND c.`id` = cr.`crm_id` 
			  AND cr.`lfd_id` = $fn_id 
			ORDER BY c.`receipt_no` DESC 
			LIMIT 1";
   	//return $sql;
   	return $db->fetchOne($sql);
   }
   public function getLaonHasPayByLoanNumber($loan_number){
   	$db= $this->getAdapter();
	$sql="SELECT 
			  (SELECT c.`name_kh` FROM `ln_client` AS c WHERE c.`client_id`=crm.`client_id` LIMIT 1) AS client_name,
			  (SELECT c.`client_number` FROM `ln_client` AS c WHERE c.`client_id`=crm.`client_id` LIMIT 1) AS client_code,
			  crm.`receipt_no`,crm.paid_times,
			  DATE_FORMAT(crm.date_pay, '%d-%m-%Y') AS `date_input`,
			  (crm.`principal_paid`) AS principal_paid,
			  (crm.`interest_paid`) AS interest_paid,
			  (crm.`penalize_paid`) AS penalize_paid,
			  (crm.`service_paid`) AS service_paid,
			  (crm.`total_paymentpaid`) AS total_paymentpaid,
			  crm.`currency_type`,
			  crm.is_completed,
			  DATE_FORMAT(crmd.date_payment, '%d-%m-%Y') AS `date_payment`
			FROM
			  `ln_client_receipt_money` AS crm,
			  `ln_client_receipt_money_detail` AS crmd 
			WHERE 
			  crm.status=1
			  AND crm.`id` = crmd.`receipt_id` 
			  AND crm.`loan_id` = '$loan_number' 
			  GROUP BY crm.`id` ORDER BY crm.`id` DESC";
   	return $db->fetchAll($sql);
   }
   
   function getAllLoanByCoId($data){ //quick Il Payment
   	$db = $this->getAdapter();
   	$co_id = $data["co_id"];
   	$cu_id = $data["currency"];
   	$date = $data["date_collect"];
   	$sql="SELECT 
			  (SELECT CONCAT(co.`co_firstname`,`co_lastname`,',',`co_khname`) FROM `ln_co` AS co WHERE co.`co_id`=lg.`co_id`) AS co_name,
			  (SELECT b.`branch_namekh` FROM `ln_branch` AS b WHERE b.`br_id`=lm.`branch_id`) AS branch,
			  (SELECT c.`name_kh` FROM `ln_client` AS c WHERE c.`client_id`=lm.`client_id` ) AS `client`,
  			  (SELECT c.`client_number` FROM `ln_client` AS c WHERE c.`client_id`=lm.`client_id` ) AS `client_number`,
			  (SELECT crm.`date_input` FROM `ln_client_receipt_money` AS crm , `ln_client_receipt_money_detail` AS crmd WHERE crm.`id`=(SELECT c.`crm_id` FROM `ln_client_receipt_money_detail` AS c WHERE c.`lfd_id`=lf.`id` ORDER BY c.`id` DESC LIMIT 1) ORDER BY `crm`.`date_input` DESC LIMIT 1) AS last_pay_date,
			  lm.`loan_number`,
			  lm.`client_id`,
			  lm.`branch_id`,
			  lm.`interest_rate`,
			  lm.`payment_method`,
			  lm.`pay_after`,
			  lm.`collect_typeterm`,
			  lm.`currency_type`,
			  SUM(lf.`total_principal`) AS total_principal,
			  SUM(lf.`principle_after`) AS principle_after,
			  SUM(lf.`total_interest_after`) AS total_interest_after,
			  SUM(lf.`penelize`) AS penelize,
			  SUM(lf.`service_charge`) AS service_charge,
			  SUM(lf.`total_payment_after`) AS total_payment_after,
			  lf.`date_payment`,
  			  lf.`id`,
  			  lg.`g_id`,
  			  lg.`loan_type`
			FROM
			  `ln_loanmember_funddetail` AS lf,
			  `ln_loan_member` AS lm,
			  `ln_loan_group` AS lg 
			WHERE lf.`is_completed` = 0 
			  AND lf.`member_id` = lm.`member_id` 
			  AND lm.`status` = 1 
			  AND lg.`status` = 1 
			  AND lf.status = 1
			  AND lm.`is_completed` = 0
			  AND lm.`group_id`=lg.`g_id`
			  AND lm.`is_reschedule` !=1
			  AND lf.`collect_by` = $co_id
			  AND lm.`currency_type`=$cu_id
			  AND lf.`date_payment`<='$date'
			  
			  ";

   		$order = " GROUP BY lm.`group_id`,lf.`date_payment`";
   	//return $sql.$order;
   	return $db->fetchAll($sql.$order);
   }
   function getFunByGroupId($id){
   		$db = $this->getAdapter();
   		$sql="SELECT lf.`id` FROM `ln_loanmember_funddetail` AS lf, `ln_loan_member` AS lm WHERE lm.`member_id` = lf.`member_id` AND lm.`group_id` = $id AND lf.`is_completed`=0";
   		return $db->fetchAll($sql);
   }
   public function quickPayment($data){
   		$db = $this->getAdapter();
   		$db->beginTransaction();
   		$session_user=new Zend_Session_Namespace('authloan');
   		$user_id = $session_user->user_id;
   		$reciept_no=$this->getIlPaymentNumber();
   		$db_loan_fun = new Loan_Model_DbTable_DbLoanILPayment();
   		try {
   			$is_set =0;
   			$identify = explode(',',$data['identity']);
   			if(!empty($identify)){
   				$arr_reciept_money = array(
   						'co_id'							=>		$data['co_id'],
   						'receiver_id'					=>		$data['reciever'],
   						'receipt_no'					=>		$reciept_no,
   						'branch_id'						=>		$data['branch_id'],
   						'date_input'					=>		$data["collect_date"],
   						'principal_amount'				=>		$data["priciple_amount"],
   						'total_principal_permonth'		=>		$data["os_amount"],
   						'total_payment'					=>		$data["total_payment"],
   						'total_interest'				=>		$data["total_interest"],
   						'recieve_amount'				=>		$data["amount_receive"],
   						'penalize_amount'				=>		$data['penalize_amount'],
   						'return_amount'					=>		$data["amount_return"],
   						'service_charge'				=>		$data["service_charge"],
   						'note'							=>		$data['note'],
   						'user_id'						=>		$user_id,
   						'is_group'						=>		2,
   						'payment_option'				=>		1,
   						'currency_type'					=>		$data["currency_type"],
   						'status'						=>		1,
   						'amount_payment'				=>		$data["amount_receive"]-$data["amount_return"],
   						'is_completed'					=>		1
   				);
   				
   				$this->_name="ln_client_receipt_money";
   				$client_recipt_money = $this->insert($arr_reciept_money);
   				
	   			foreach ($identify as $i){
	   				$client_detail = $data["mfdid_".$i];
	   				$sub_recieve_amount = $data["sub_recive_amount_".$i];
	   				$sub_service_charge = $data["sub_service_charge_".$i];
	   				$sub_peneline_amount = $data["sub_penelize_".$i];
	   				$sub_interest_amount = $data["sub_interest_".$i];
	   				$sub_principle= $data["sub_principal_permonth_".$i];
	   				$sub_total_payment = $data["sub_payment_".$i];
	   				if($client_detail!=""){
	   					$loan_type = $data["loan_type_".$i];
	   					$group_id = $data["group_id_".$i];
	   					$pay_date = $data["date_payment_".$i];
	   					$loanFun = $db_loan_fun->getLoanFunByGroupId($group_id,$pay_date);
	   					if($loan_type==2){
	   						if(!empty($loanFun) or $loanFun!="" or $loanFun!=null){
	   							foreach ($loanFun as $rowFun){
	   								if($is_set!=1){
// 	   									$total_pene = $data["old_penelize_".$i];
	   									$total_pay = $rowFun["total_payment_after"]+$data["sub_penelize_".$i];
	   									$is_set=1;
	   								}else{
// 	   									$total_pene = $rowFun["penelize"];
	   									$total_pay = $rowFun["total_payment_after"];
	   								}
	   								
	   								$arr_money_detail = array(
	   										'crm_id'				=>		$client_recipt_money,
	   										'loan_number'			=>		$rowFun["loan_number"],
	   										'lfd_id'				=>		$rowFun["id"],
	   										'client_id'				=>		$rowFun["client_id"],
	   										'date_payment'			=>		$rowFun["date_payment"],
	   										'capital'				=>		$rowFun["total_capital"],
	   										'remain_capital'		=>		$rowFun["total_capital"] - $rowFun["principle_after"],
	   										'principal_permonth'	=>		$rowFun["principle_after"],
	   										'total_interest'		=>		$rowFun["total_interest_after"],
	   										'total_payment'			=>		$total_pay,
	   										'total_recieve'			=>		$total_pay,
	   										'currency_id'			=>		$data["cu_id_".$i],
	   										'pay_after'				=>		$data['multiplier_'.$i],
	   										'penelize_amount'		=>		$rowFun["penelize"],
	   										'service_charge'		=>		$rowFun["service_charge"],
	   										'is_completed'			=>		1,
	   										'is_verify'				=>		0,
	   										'verify_by'				=>		0,
	   										'is_closingentry'		=>		0,
	   										'status'				=>		1
	   								);
// 	   								$db->getProfiler()->setEnabled(true);
	   								$db->insert("ln_client_receipt_money_detail", $arr_money_detail);
// 	   								Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
// 	   								Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
// 	   								$db->getProfiler()->setEnabled(false);
	   								
	   								$arr_fu = array(
	   										'is_completed'	=> 1,
	   								);
	   								$this->_name="ln_loanmember_funddetail";
	   								$where = $db->quoteInto("id=?", $rowFun["id"]);
// 	   								$db->getProfiler()->setEnabled(true);
	   								$this->update($arr_fu, $where);
// 	   								Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
// 	   								Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
// 	   								$db->getProfiler()->setEnabled(false);
	   							}
	   						}
	   					
	   					}else{
	   						echo "This individule loan";
			   				if($sub_recieve_amount>=$sub_total_payment){
			   					$arr_money_detail = array(
			   							'crm_id'				=>		$client_recipt_money,
			   							'loan_number'			=>		$data["loan_number_".$i],
			   							'lfd_id'				=>		$data["mfdid_".$i],
			   							'client_id'				=>		$data["client_id_".$i],
			   							'date_payment'			=>		$data["date_payment_".$i],
			   							'capital'				=>		$data["sub_total_priciple_".$i],
			   							'remain_capital'		=>		$data["sub_total_priciple_".$i] - $data["sub_principal_permonth_".$i],
			   							'principal_permonth'	=>		$data["sub_principal_permonth_".$i],
			   							'total_interest'		=>		$data["sub_interest_".$i],
			   							'total_payment'			=>		$data["sub_payment_".$i],
			   							'total_recieve'			=>		$data["sub_recive_amount_".$i],
			   							'currency_id'			=>		$data["cu_id_".$i],
			   							'pay_after'				=>		$data['multiplier_'.$i],
			   							'penelize_amount'		=>		$data["sub_penelize_".$i],
			   							'service_charge'		=>		$data["sub_service_charge_".$i],
			   							'old_penelize'			=>		$data["old_sub_penelize_".$i],
			   							'old_service_charge'	=>		$data["old_sub_service_charge_".$i],
			   							'old_interest'			=>		$data["sub_interest_".$i],
			   							'is_completed'			=>		0,
			   							'is_verify'				=>		0,
			   							'verify_by'				=>		0,
			   							'is_closingentry'		=>		0,
			   							'status'				=>		1
			   					);
			   					
// 			   					$db->getProfiler()->setEnabled(true);
			   					$db->insert("ln_client_receipt_money_detail", $arr_money_detail);
// 			   					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
// 			   					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
// 			   					$db->getProfiler()->setEnabled(false);
			   						
			   					$arr_update_fun_detail = array(
			   							'is_completed'		=> 	1,
			   							'payment_option'	=>	1
			   					);
			   					$this->_name="ln_loanmember_funddetail";
			   					$where = $db->quoteInto("id=?", $data["mfdid_".$i]);
			   					
// 			   					$db->getProfiler()->setEnabled(true);
			   					$this->update($arr_update_fun_detail, $where);
// 			   					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
// 			   					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
// 			   					$db->getProfiler()->setEnabled(false);
			   					
			   				}else{
			   					print_r("ede");
			   					$new_sub_interest_amount = $data["sub_interest_".$i];
			   					$new_sub_penelize = $data["sub_penelize_".$i];
			   					$new_sub_service_charge = $data["sub_service_charge_".$i];
			   					$new_sub_principle = $data["sub_principal_permonth_".$i];
				   				if($sub_recieve_amount>0){
				   					$new_amount_after_service = $sub_recieve_amount-$sub_service_charge;
				   					if($new_amount_after_service>=0){
				   						$new_sub_service_charge = 0;
				   						$new_amount_after_penelize = $new_amount_after_service - $sub_peneline_amount;
				   						if($new_amount_after_penelize>=0){
				   							$new_sub_penelize = 0;
				   							$new_amount_after_interest = $new_amount_after_penelize - $sub_interest_amount;
				   							if($new_amount_after_interest>=0){
				   								$new_sub_interest_amount = 0;
				   								$new_sub_principle = $sub_principle - $new_amount_after_interest;
				   							}else{
				   								$new_sub_interest_amount = abs($new_amount_after_interest);
				   							}
				   						}else{
				   							$new_sub_penelize = abs($new_amount_after_penelize);
				   						}
				   					}else{
				   						$new_sub_service_charge = abs($new_amount_after_service);
				   					}
				   				}
				   				$arr_money_detail = array(
				   						'crm_id'				=>		$client_recipt_money,
			   							'loan_number'			=>		$data["loan_number_".$i],
			   							'lfd_id'				=>		$data["mfdid_".$i],
			   							'client_id'				=>		$data["client_id_".$i],
			   							'date_payment'			=>		$data["date_payment_".$i],
			   							'capital'				=>		$data["sub_total_priciple_".$i],
			   							'remain_capital'		=>		$data["sub_total_priciple_".$i] - $data["sub_principal_permonth_".$i],
			   							'principal_permonth'	=>		$data["sub_principal_permonth_".$i],
			   							'total_interest'		=>		$data["sub_interest_".$i],
			   							'total_payment'			=>		$data["sub_payment_".$i],
			   							'total_recieve'			=>		$data["sub_recive_amount_".$i],
			   							'currency_id'			=>		$data["cu_id_".$i],
			   							'pay_after'				=>		$data['multiplier_'.$i],
			   							'penelize_amount'		=>		$data["sub_penelize_".$i],
			   							'service_charge'		=>		$data["sub_service_charge_".$i],
			   							'old_penelize'			=>		$data["old_sub_penelize_".$i],
			   							'old_service_charge'	=>		$data["old_sub_service_charge_".$i],
			   							'old_interest'			=>		$data["sub_interest_".$i],
			   							'is_completed'			=>		0,
			   							'is_verify'				=>		0,
			   							'verify_by'				=>		0,
			   							'is_closingentry'		=>		0,
			   							'status'				=>		1
				   				);
				   				
// 				   				$db->getProfiler()->setEnabled(true);
				   				$db->insert("ln_client_receipt_money_detail", $arr_money_detail);
// 				   				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
// 				   				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
// 				   				$db->getProfiler()->setEnabled(false);
				   				
				   				$arr_update_fun_detail = array(
				   						'is_completed'			=> 	0,
				   						'total_interest_after'	=>  $new_sub_interest_amount,
				   						'total_payment_after'	=>	$new_sub_principle,
				   						'penelize'				=>	$new_sub_penelize,
				   						'principle_after'		=>	$new_sub_principle,
				   						'service_charge'		=>	$new_sub_service_charge,
				   						'payment_option'		=>	1
				   				);
				   				$this->_name="ln_loanmember_funddetail";
				   				$where = $db->quoteInto("id=?", $data["mfdid_".$i]);
				   				
// 				   				$db->getProfiler()->setEnabled(true);
				   				$this->update($arr_update_fun_detail, $where);
// 				   				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
// 				   				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
// 				   				$db->getProfiler()->setEnabled(false);
			   				}
	   					}
	   					$loan_fun = $db_loan_fun->getFunByGroupId($group_id);
	   					if(empty($loan_fun) or $loan_fun="" or $loan_fun=null){
	   						$sql_mem = "SELECT lm.`member_id` FROM `ln_loan_member` AS lm WHERE lm.`group_id`=$group_id";
	   						$row_mem = $db->fetchAll($sql_mem);
	   						foreach ($row_mem as $rs_mem){
		   						$arr_member = array(
		   							'is_completed'	=> 1,
		   						);
		   						$this->_name ="ln_loan_member";
		   						$where = $db->quoteInto("member_id=?", $rs_mem["member_id"]);
		   						
// 		   						$db->getProfiler()->setEnabled(true);
		   						$this->update($arr_member, $where);
// 		   						Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
// 		   						Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
// 		   						$db->getProfiler()->setEnabled(false);
		   						
		   						$arr_group = array(
		   							'status'	=> 2,
		   						);
		   						$this->_name ="ln_loan_group";
		   						$where = $db->quoteInto("g_id=?", $group_id);
		   						
// 		   						$db->getProfiler()->setEnabled(true);
		   						$this->update($arr_group, $where);
// 		   						Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
// 		   						Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
// 		   						$db->getProfiler()->setEnabled(false);
	   						}
	   					}
	   				}
	   			}
   			}
   			//exit();
   			$db->commit();
   		}catch (Exception $e){
   			$db->rollBack();
   			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
   		}
   }
   
   public function editQuickPayment($id,$data){
   	$db = $this->getAdapter();
   	$db->beginTransaction();
   	$session_user=new Zend_Session_Namespace('authloan');
   	$user_id = $session_user->user_id;
   	$reciept_no=$this->getIlPaymentNumber();
   	$db_loanFun = new Loan_Model_DbTable_DbLoanILPayment();
   	$db_group = new Loan_Model_DbTable_DbGroupPayment();
   	
   	try {
   		// Update Reciept Money
   		$is_set=0;
   		$quick_fun = $this->getIlQuickPaymentDetailById($id);
   		$identify = explode(',',$data['identity']);
   		if(!empty($identify)){
   			$arr_reciept_money = array(
   					'co_id'							=>		$data['co_id'],
   					'receiver_id'					=>		$data['reciever'],
   					'branch_id'						=>		$data['branch_id'],
   					'date_input'					=>		$data["collect_date"],
   					'principal_amount'				=>		$data["priciple_amount"],
   					'total_principal_permonth'		=>		$data["os_amount"],
   					'total_payment'					=>		$data["total_payment"],
   					'total_interest'				=>		$data["total_interest"],
   					'recieve_amount'				=>		$data["amount_receive"],
   					'penalize_amount'				=>		$data['penalize_amount'],
   					'return_amount'					=>		$data["amount_return"],
   					'service_charge'				=>		$data["service_charge"],
   					'note'							=>		$data['note'],
   					'user_id'						=>		$user_id,
   					'is_group'						=>		2,
   					'payment_option'				=>		1,
   					'currency_type'					=>		$data["currency_type"],
   					'status'						=>		1,
   					'amount_payment'				=>		$data["amount_receive"]-$data["amount_return"],
   					'is_completed'					=>		1
   			);
   				
   			$this->_name="ln_client_receipt_money";
   			$where = $db->quoteInto("id=?", $id);
   			$this->update($arr_reciept_money, $where);
   			
   			// Update Reciept Money Detail
   			$recipt_money = $db_loanFun->getReceiptMoneyById($id);
			$recipt_money_detail = $db_loanFun->getReceiptMoneyDetailByID($id);
// 			foreach ($recipt_money_detail as $row_recipt_detail){
// 				$fun_id = $row_recipt_detail["lfd_id"];
// 				if($is_set!=1){
// 					$loan_number = $row_recipt_detail["loan_number"];
// 					$rs_fun = $db_group->getFunDetailByLoanNumber($loan_number);
// 					$rs_group = "SELECT m.group_id FROM ln_loan_member AS m WHERE m.loan_number = '$loan_number' GROUP BY m.loan_number";
// 					$group_id = $db->fetchOne($rs_group);
// 					if(empty($rs_fun) or $rs_fun=="" or $rs_fun==null){
// 						$arr_member = array(
// 							'is_completed'	=>	0,
// 						);
// 						$this->_name = "ln_loan_member";
// 						$where = $db->quoteInto("loan_number=?", $loan_number);
						
// 						$db->getProfiler()->setEnabled(true);
						
// 						$this->update($arr_member, $where);
						
// 						Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
// 						Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
// 						$db->getProfiler()->setEnabled(false);
						
// 						$arr_group = array(
// 							'status'	=>	1,
// 						);
// 						$this->_name = "ln_loan_group";
// 						$where = $db->quoteInto("g_id=?", $group_id);
						
// 						$db->getProfiler()->setEnabled(true);
						
// 						$this->update($arr_group, $where);
						
// 						Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
// 						Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
// 						$db->getProfiler()->setEnabled(false);
// 					}
// 					$is_set=1;
// 				}
// 				if($recipt_money["payment_option"]==1){
// 					$is_completed = $db_loanFun->getFunDetailById($fun_id);
// 					if($is_completed==1){
// 						$arr_update = array(
// 							'is_completed' =>	0,
// 						);
// 						$this->_name = "ln_loanmember_funddetail";
// 						$where = $db->quoteInto("id=?", $fun_id);
						
// 						$db->getProfiler()->setEnabled(true);
						
// 						$this->update($arr_update, $where);
						
// 						Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
// 						Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
// 						$db->getProfiler()->setEnabled(false);
// 					}else{
// 						echo "wewe";
// 						$arr_update = array(
// 							'principle_after'		=> 	$row_recipt_detail["principal_permonth"],
// 							'total_interest_after'	=>	$row_recipt_detail["total_interest"],
// 							'penelize'				=>	$row_recipt_detail["old_penelize"],
// 							'service_charge'		=>	$row_recipt_detail["old_service_charge"],
// 							'total_payment_after'	=>	$row_recipt_detail["total_payment"],	
// 							'is_completed' 			=>	0,
// 						);
// 						$this->_name = "ln_loanmember_funddetail";
// 						$where = $db->quoteInto("id=?", $fun_id);
						
// 						$db->getProfiler()->setEnabled(true);
						
// 						$this->update($arr_update, $where);
						
// 						Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
// 						Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
// 						$db->getProfiler()->setEnabled(false);
// 					}
// 				}else{
// 					$arr_update = array(
// 							'is_completed' =>	0,
// 					);
// 					$this->_name = "ln_loanmember_funddetail";
// 					$where = $db->quoteInto("id=?", $fun_id);
					
// 					$db->getProfiler()->setEnabled(true);
					
// 					$this->update($arr_update, $where);
					
// 					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
// 					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
// 					$db->getProfiler()->setEnabled(false);
// 				}
// 			}
			
			if(!empty($quick_fun)){
				foreach ($quick_fun as $rs){
					$principle = $rs["principal_permonth"];
					$interest = $rs["total_interest"];
					$recieve_amount = $rs["total_recieve"];
					$total_pay = $rs["principal_permonth"]+$rs["old_interest"]+$rs["old_penelize"]+$rs["old_service_charge"];
					$penelize = $rs["old_penelize"];
					$service_charge = $rs["old_service_charge"];
			
					$old_group_id = $rs["group_id"];
					$old_datepayment = $rs["date_payment"];
			
					$fun = $db_loanFun->getLoanFunByGroupId($old_group_id, $old_datepayment);
					
					// Update ln_loanmember_funddetail
					foreach ($fun as $row_fun){
							
						if($row_fun["is_completed"]==1){
							$arr_fun = array(
									'is_completed'	=>	0,
							);
							$this->_name= "ln_loanmember_funddetail";
							$where = $db->quoteInto("id=?", $row_fun["id"]);
			
// 							$db->getProfiler()->setEnabled(true);
							$this->update($arr_fun, $where);
// 							Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
// 							Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
// 							$db->getProfiler()->setEnabled(false);
						}else{
							$arr_fun = array(
									'principle_after'		=>	$principle,
									'total_interest_after'	=> $interest,
									'total_payment_after'	=>	$total_pay,
									'penelize'				=>	$penelize,
									'service_charge'		=>	$service_charge,
							);
							$this->_name= "ln_loanmember_funddetail";
							$where = $db->quoteInto("id=?", $row_fun["id"]);
// 							$db->getProfiler()->setEnabled(true);
							$this->update($arr_fun, $where);
// 							Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
// 							Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
// 							$db->getProfiler()->setEnabled(false);
						}
					}
				}
			}
			
			$sql_cmd = "DELETE FROM `ln_client_receipt_money_detail` WHERE `crm_id` = $id";
			$db->query($sql_cmd);
			
			// Add New Recipt Money Detail
   			foreach ($identify as $i){
   				$client_detail = $data["mfdid_".$i];
   				$sub_recieve_amount = $data["sub_recive_amount_".$i];
   				$sub_service_charge = $data["sub_service_charge_".$i];
   				$sub_peneline_amount = $data["sub_penelize_".$i];
   				$sub_interest_amount = $data["sub_interest_".$i];
   				$sub_principle= $data["sub_principal_permonth_".$i];
   				$sub_total_payment = $data["sub_payment_".$i];
   				if($client_detail!=""){
   					$loan_type = $data["loan_type_".$i];
   					$group_id = $data["group_id_".$i];
   					$pay_date = $data["date_payment_".$i];
   						
   					$loanFun = $db_loanFun->getLoanFunByGroupId($group_id,$pay_date);
   					if($loan_type==2){
   						if(!empty($loanFun) or $loanFun!="" or $loanFun!=null){
   							foreach ($loanFun as $rowFun){
   								if($is_set!=1){
   									$total_pene = $data["sub_penelize_".$i];
   									$total_pay = $rowFun["total_payment_after"]+$data["sub_penelize_".$i];
   									$is_set=1;
   								}else{
   									$total_pene = $rowFun["penelize"];
   									$total_pay = $rowFun["total_payment_after"];
   								}
   			
   								$arr_money_detail = array(
   										'crm_id'				=>		$id,
   										'loan_number'			=>		$rowFun["loan_number"],
   										'lfd_id'				=>		$rowFun["id"],
   										'client_id'				=>		$rowFun["client_id"],
   										'date_payment'			=>		$rowFun["date_payment"],
   										'capital'				=>		$rowFun["total_capital"],
   										'remain_capital'		=>		$rowFun["total_capital"] - $rowFun["principle_after"],
   										'principal_permonth'	=>		$rowFun["principle_after"],
   										'total_interest'		=>		$rowFun["total_interest_after"],
   										'total_payment'			=>		$total_pay,
   										'total_recieve'			=>		$total_pay,
   										'currency_id'			=>		$data["cu_id_".$i],
   										'pay_after'				=>		$data['multiplier_'.$i],
   										'penelize_amount'		=>		$total_pene,
   										'service_charge'		=>		$data["sub_service_charge_".$i],
   										'is_completed'			=>		1,
   										'is_verify'				=>		0,
   										'verify_by'				=>		0,
   										'is_closingentry'		=>		0,
   										'status'				=>		1
   								);
//    								$db->getProfiler()->setEnabled(true);
   								$db->insert("ln_client_receipt_money_detail", $arr_money_detail);
//    								Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
//    								Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
//    								$db->getProfiler()->setEnabled(false);
   			
   								$arr_fu = array(
   										'is_completed'	=> 1,
   								);
   								$this->_name="ln_loanmember_funddetail";
   								$where = $db->quoteInto("id=?", $rowFun["id"]);
//    								$db->getProfiler()->setEnabled(true);
   								$this->update($arr_fu, $where);
//    								Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
//    								Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
//    								$db->getProfiler()->setEnabled(false);
   							}
   						}
   							
   					}else{
   						if($sub_recieve_amount>=$sub_total_payment){
   							$arr_money_detail = array(
   									'crm_id'				=>		$id,
   									'loan_number'			=>		$data["loan_number_".$i],
   									'lfd_id'				=>		$data["mfdid_".$i],
   									'client_id'				=>		$data["client_id_".$i],
   									'date_payment'			=>		$data["date_payment_".$i],
   									'capital'				=>		$data["sub_total_priciple_".$i],
   									'remain_capital'		=>		$data["sub_total_priciple_".$i] - $data["sub_principal_permonth_".$i],
   									'principal_permonth'	=>		$data["sub_principal_permonth_".$i],
   									'total_interest'		=>		$data["sub_interest_".$i],
   									'total_payment'			=>		$data["sub_payment_".$i],
   									'total_recieve'			=>		$data["sub_recive_amount_".$i],
   									'currency_id'			=>		$data["cu_id_".$i],
   									'pay_after'				=>		$data['multiplier_'.$i],
   									'penelize_amount'		=>		$data["old_sub_penelize_".$i],
			   						'service_charge'		=>		$data["old_sub_service_charge_".$i],
   									'is_completed'			=>		0,
   									'is_verify'				=>		0,
   									'verify_by'				=>		0,
   									'is_closingentry'		=>		0,
   									'status'				=>		1
   							);
   							$db->insert("ln_client_receipt_money_detail", $arr_money_detail);
   			
   							$arr_update_fun_detail = array(
   									'is_completed'		=> 	1,
   									'payment_option'	=>	1
   							);
   							$this->_name="ln_loanmember_funddetail";
   							$where = $db->quoteInto("id=?", $data["mfdid_".$i]);
   							 
   							$this->update($arr_update_fun_detail, $where);
   							 
   						}else{
   							
   							// Update Loan Fun Detail If not full Payment
   							$new_sub_interest_amount = $data["sub_interest_".$i];
   							$new_sub_penelize = $data["sub_penelize_".$i];
   							$new_sub_service_charge = $data["sub_service_charge_".$i];
   							$new_sub_principle = $data["sub_principal_permonth_".$i];
   							if($sub_recieve_amount>0){
   								$new_amount_after_service = $sub_recieve_amount-$sub_service_charge;
   								if($new_amount_after_service>=0){
   									$new_sub_service_charge = 0;
   									$new_amount_after_penelize = $new_amount_after_service - $sub_peneline_amount;
   									if($new_amount_after_penelize>=0){
   										$new_sub_penelize = 0;
   										$new_amount_after_interest = $new_amount_after_penelize - $sub_interest_amount;
   										if($new_amount_after_interest>=0){
   											$new_sub_interest_amount = 0;
   											$new_sub_principle = $sub_principle - $new_amount_after_interest;
   										}else{
   											$new_sub_interest_amount = abs($new_amount_after_interest);
   										}
   									}else{
   										$new_sub_penelize = abs($new_amount_after_penelize);
   									}
   								}else{
   									$new_sub_service_charge = abs($new_amount_after_service);
   								}
   							}
   							$arr_money_detail = array(
   									'crm_id'				=>		$id,
   									'loan_number'			=>		$data["loan_number_".$i],
   									'lfd_id'				=>		$data["mfdid_".$i],
   									'client_id'				=>		$data["client_id_".$i],
   									'date_payment'			=>		$data["date_payment_".$i],
   									'capital'				=>		$data["sub_total_priciple_".$i],
   									'remain_capital'		=>		$data["sub_total_priciple_".$i] - $data["sub_principal_permonth_".$i],
   									'principal_permonth'	=>		$sub_principle,
   									'total_interest'		=>		$sub_interest_amount,
   									'total_payment'			=>		$sub_total_payment,
   									'total_recieve'			=>		$data["sub_recive_amount_".$i],
   									'currency_id'			=>		$data["cu_id_".$i],
   									'pay_after'				=>		$data['multiplier_'.$i],
   									'penelize_amount'		=>		$data["sub_penelize_".$i],
   									'service_charge'		=>		$data["sub_service_charge_".$i],
   									'is_completed'			=>		0,
   									'is_verify'				=>		0,
   									'verify_by'				=>		0,
   									'is_closingentry'		=>		0,
   									'status'				=>		1
   							);
   							$db->insert("ln_client_receipt_money_detail", $arr_money_detail);
   							 
   							$arr_update_fun_detail = array(
   									'is_completed'			=> 	0,
   									'total_interest_after'	=>  $new_sub_interest_amount,
   									'total_payment_after'	=>	$new_sub_principle,
   									'penelize'				=>	$new_sub_penelize,
   									'principle_after'		=>	$new_sub_principle,
   									'service_charge'		=>	$new_sub_service_charge,
   									'payment_option'		=>	1
   							);
   							$this->_name="ln_loanmember_funddetail";
   							$where = $db->quoteInto("id=?", $data["mfdid_".$i]);
   							$this->update($arr_update_fun_detail, $where);
   						}
   					}
   					
   					// Update ln_loan_member & ln_loan_group if Full Payment
   					$loan_fun = $db_loanFun->getFunByGroupId($group_id);
   					if(empty($loan_fun) or $loan_fun="" or $loan_fun=null){
   						$sql_mem = "SELECT lm.`member_id` FROM `ln_loan_member` AS lm WHERE lm.`group_id`=$group_id";
   						$row_mem = $db->fetchAll($sql_mem);
   						foreach ($row_mem as $rs_mem){
   							$arr_member = array(
   									'is_completed'	=> 1,
   							);
   							$this->_name ="ln_loan_member";
   							$where = $db->quoteInto("member_id=?", $rs_mem["member_id"]);
   							$this->update($arr_member, $where);
   								
   							$arr_group = array(
   									'status'	=> 2,
   							);
   							$this->_name ="ln_loan_group";
   							$where = $db->quoteInto("g_id=?", $group_id);
   							$this->update($arr_group, $where);
   						}
   					}
   				}
   			}
   		}
//    		exit();
   		$db->commit();
   	}catch (Exception $e){
   		$db->rollBack();
   		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
   	}
   }
   
   function getIlQuickPaymentById($id){
   	$db = $this->getAdapter();
   	$sql = "SELECT lc.id,lc.`co_id`,lc.`receiver_id`,lc.`receipt_no`,lc.`branch_id`,lc.`date_input`,lc.`principal_amount`,lc.`total_principal_permonth`,
   			lc.`total_payment`,lc.`total_interest`,lc.`recieve_amount`,lc.`return_amount`,lc.`service_charge`,lc.`penalize_amount`,lc.`currency_type`, lc.`note` 
   			FROM `ln_client_receipt_money` AS lc WHERE lc.`id`=$id";
   	return $db->fetchRow($sql);
   }
   
   function getIlQuickPaymentDetailById($id){
   	$db = $this->getAdapter();
//    	$sql = "SELECT 
// 			  lcd.`lfd_id`,
// 			  lcd.`loan_number`,
// 			  lcd.`client_id`,
// 			  lcd.`date_payment`,
// 			  lcd.`capital`,
// 			  lcd.`principal_permonth`,
// 			  lcd.`total_interest`,
// 			  lcd.`total_payment`,
// 			  lcd.`total_recieve`,
// 			  lcd.`service_charge`,
// 			  lcd.`penelize_amount`,
// 			  lcd.`pay_after`,
// 			  (SELECT c.`name_kh` FROM `ln_client` AS c WHERE c.`client_id`=lcd.`client_id`) AS client_name,
// 			  (SELECT c.`client_number` FROM `ln_client` AS c WHERE c.`client_id`=lcd.`client_id`) AS client_code,
// 			  (SELECT lm.`collect_typeterm` FROM `ln_loan_member` AS lm WHERE lm.`loan_number`=lcd.`loan_number`) AS payTearm,
// 			  (SELECT lm.`interest_rate` FROM `ln_loan_member` AS lm WHERE lm.`loan_number`=lcd.`loan_number`) AS interest_rate,
// 			  (SELECT lm.`currency_type` FROM `ln_loan_member` AS lm WHERE lm.`loan_number`=lcd.`loan_number`) AS currency_type,
// 			  (SELECT lcrm.`date_input` FROM `ln_client_receipt_money` AS lcrm,`ln_client_receipt_money_detail` AS lcrmd WHERE lcrm.`id`!=$id AND lcrmd.`crm_id`=lcrm.`id` AND lcrm.`loan_number`=(SELECT `loan_number` FROM `ln_client_receipt_money_detail` WHERE `crm_id`=$id LIMIT 1) ORDER BY lcrm.`date_input` DESC LIMIT 1) AS last_paydate 
// 			FROM
// 			  `ln_client_receipt_money_detail` AS lcd 
// 			WHERE lcd.`crm_id` =$id";
		$sql ="SELECT 
				  lcd.`lfd_id`,
				  lcd.`loan_number`,
				  lcd.`client_id`,
				  lcd.`date_payment`,
				  lcd.`capital`,
				  SUM(lcd.`principal_permonth`) AS principal_permonth,
				  SUM(lcd.`total_interest`) AS total_interest,
				  SUM(lcd.`total_payment`) AS total_payment,
				  SUM(lcd.`total_recieve`) AS total_recieve,
				  SUM(lcd.`service_charge`) AS service_charge,
				  SUM(lcd.`penelize_amount`) AS penelize_amount,
				  SUM(lcd.`old_penelize`) AS old_penelize,
				  SUM(lcd.`old_service_charge`) AS old_service_charge,
				  SUM(lcd.`old_interest`) AS old_interest,
				  lcd.`pay_after`,
				  (SELECT c.`name_kh` FROM `ln_client` AS c WHERE c.`client_id`=lcd.`client_id`) AS client_name,
				  (SELECT c.`client_number` FROM `ln_client` AS c WHERE c.`client_id`=lcd.`client_id`) AS client_code,
				  (SELECT c.`date_input` FROM `ln_client_receipt_money` AS c WHERE c.`id` = (SELECT cd.`crm_id` FROM `ln_client_receipt_money_detail` AS cd WHERE cd.`lfd_id` = (SELECT lfr.`id` FROM `ln_loanmember_funddetail` AS lfr WHERE lfr.id=lf.id) AND cd.`crm_id` != $id ORDER BY cd.crm_id DESC LIMIT 1)) AS last_paydate ,
				  m.`group_id` ,
				  m.`collect_typeterm` as payTearm,
				  m.`interest_rate`,
				  m.`currency_type`,
				  lg.`loan_type`
				FROM
				  `ln_client_receipt_money_detail` AS lcd,
				  `ln_loan_member` AS m,
				  `ln_loanmember_funddetail` AS lf,
				  `ln_loan_group` AS lg 
				WHERE lcd.`lfd_id` = lf.`id` 
				  AND lf.`member_id` = m.`member_id` 
				  AND m.`group_id`=lg.`g_id`
				  AND lcd.`crm_id`=$id
				  GROUP BY m.`group_id`,lcd.`date_payment`";
   //	return $sql;
   	return $db->fetchAll($sql);
   }
   
   public function getFunDetail($id){
   	$db = $this->getAdapter();
   	$sql="SELECT f.`id`,f.`penelize`,f.`principle_after`,f.`service_charge`,f.`total_interest_after`,f.`total_payment_after`,f.`is_completed` FROM `ln_loanmember_funddetail` AS f WHERE f.`id`=$id";
   	return $db->fetchAll($sql);
   }
   
   public function getLoanFunByGroupId($id,$payDate){
   	$db = $this->getAdapter();
   	$sql = "SELECT 
			  lf.`id`,
			  lf.`principle_after`,
			  lf.`total_interest_after`,
			  lf.`total_principal`,
			  lf.`penelize`,
			  lf.`service_charge`,
			  lf.`date_payment`,
			  lf.`total_payment_after`,
			  lm.`client_id`,
			  lm.`loan_number`,
			  lm.`total_capital`,
			  lm.`group_id`,
			  lf.`is_completed`
			FROM
			  `ln_loanmember_funddetail` AS lf,
			  `ln_loan_member` AS lm 
			WHERE lm.`member_id` = lf.`member_id` 
			   	AND lm.`group_id` = $id 
			   	AND lf.`date_payment` ='$payDate'";
   	$rs_fun = $db->fetchAll($sql);
   	return $rs_fun;
   }
   
   function cancelPaymnet($data){
   	$db = $this->getAdapter();
   	$db_il = new Loan_Model_DbTable_DbLoanILPayment();
   	$db_recipt= new Loan_Model_DbTable_DbGroupPayment();
   	$db->beginTransaction();
   	try{
   		$id = $data["id"];
   		$sql_option = "SELECT cr.`payment_option`,cr.`recieve_amount`,cr.`total_payment` FROM `ln_client_receipt_money` AS cr WHERE cr.`id`=$id";
   		$rs = $db->fetchRow($sql_option);
   		$reciept_detail = $db_il->getRecieptMoneyDetailById($id);
   		if(!empty($reciept_detail)){
   			foreach ($reciept_detail as $row_rd){
   				$old_loan_number = $row_rd["loan_number"];
   				$rs_fun = $db_recipt->getFunDetailByLoanNumber($old_loan_number);
   				$rs_recipt = $db_recipt->getReciptDetailById($id);
   				if($rs_fun=="" or $rs_fun==null){
   					$arr_member = array(
   							'is_completed'	=> 0,
   					);
   					$this->_name = "ln_loan_member";
   					$where = $db->quoteInto("loan_number=?", $loan_number);
   					 
   					$db->getProfiler()->setEnabled(true);
   					$this->update($arr_member, $where);
   					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
   					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
   					$db->getProfiler()->setEnabled(false);
   					 
   					$sql_group = "SELECT m.`group_id` FROM `ln_loan_member` AS m WHERE m.`loan_number`='$loan_number' LIMIT 1";
   					$rs_group = $db->fetchOne($sql);
   					 
   					$arr_group = array(
   							'status'	=> 1,
   					);
   					$this->_name = "ln_loan_group";
   					$where = $db->quoteInto("g_id=?", $rs_group["group_id"]);
   					 
   					$db->getProfiler()->setEnabled(true);
   					$this->update($arr_group, $where);
   					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
   					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
   					$db->getProfiler()->setEnabled(false);
   					 
   					foreach ($rs_recipt as $row_recipt){
   						$arr_fun = array(
   								'is_completed'	=>0,
   						);
   						$this->_name="`ln_loanmember_funddetail`";
   						$where = $db->quoteInto("id=?", $row_recipt["lfd_id"]);
   				
   						$db->getProfiler()->setEnabled(true);
   						$this->update($arr_fun, $where);
   						Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
   						Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
   						$db->getProfiler()->setEnabled(false);
   					}
   				}else{
   					if($rs["payment_option"]==1){
   						foreach ($rs_recipt as $row_recipt){
   							$fun_id = $row_recipt['lfd_id'];
   							$sql = "SELECT * FROM ln_loanmember_funddetail WHERE id= $fun_id";
   							$result_fun = $db->fetchAll($sql);
   							$fun = $db_recipt->getFunHasPayedByLoanNumber($row_recipt["lfd_id"]);
   							foreach ($result_fun as $result_row){
   								$fun_penelize = $result_row["penelize"];
   								$fun_service = $result_row["service_charge"];
   								if($fun["is_completed"]==0){
   									$total_pay = $row_recipt["total_payment"];
   									$total_recieve = $row_recipt["total_recieve"];
   									$principle = $row_recipt["principal_permonth"];
   									$interest = $row_recipt["old_interest"];
   									$penelize = $row_recipt["old_penelize"];
   									$service =$row_recipt["old_service_charge"];
   				
   									$new_recieve = $total_recieve-$service;
   									if($new_recieve>0){
   										$old_service =0;
   										$new_recieve = $new_recieve-$penelize;
   										if($new_recieve>0){
   											$old_penelize =0;
   											//$new_recieve = $new_recieve - $interest;
   											//if($new_recieve>0){
   											// 	 $old_interest = 0;
   											// 	 $new_recieve = $new_recieve - $penelize;
   											// 	 if($new_recieve>0){
   											// 	    	$old_principle = 0;
   											// 	 }
   											//}else{
   											// 	 $old_interest = abs($new_recieve);
   											//}
   										}else{
   											$old_penelize = abs($new_recieve);
   										}
   									}else{
   										$old_service = abs($new_recieve);
   					    		
   									}
   									$arr_fun = array(
   											"principle_after" 		=> $principle,
   											'total_interest_after'	=>	$interest,
   											'penelize'				=> $fun_penelize-$old_penelize,
   											'service_charge'		=>	$fun_service-$old_service,
   											'total_payment_after'	=> $principle+$interest+($fun_penelize-$old_penelize)+($fun_service-$old_service),
   									);
   									$this->_name = "ln_loanmember_funddetail";
   									$where = $db->quoteInto("id=?", $row_recipt["lfd_id"]);
   				
   									$db->getProfiler()->setEnabled(true);
   				
   									$this->update($arr_fun, $where);
   				
   									Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
   									Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
   									$db->getProfiler()->setEnabled(false);
   								}else{
   									foreach ($rs_recipt as $row_recipt){
   										$arr_fun = array(
   												'is_completed'			=>	0,
   										);
   										$this->_name = "ln_loanmember_funddetail";
   										$where = $db->quoteInto("id=?", $row_recipt["lfd_id"]);
   				
   										$db->getProfiler()->setEnabled(true);
   				
   										$this->update($arr_fun, $where);
   				
   										Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
   										Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
   										$db->getProfiler()->setEnabled(false);
   									}
   								}
   							}
   						}
   					}
   				}
   			}
   		}
   		$sql = "DELETE FROM `ln_client_receipt_money` WHERE `id`=$id";
   		
   		$db->getProfiler()->setEnabled(true);
   		
   		$db->query($sql);
   		
   		Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
   		Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
   		$db->getProfiler()->setEnabled(false);
   		
   		$sql_cmd = "DELETE FROM `ln_client_receipt_money_detail` WHERE `crm_id` = $id";
   		
   		$db->getProfiler()->setEnabled(true);
   		
   		$db->query($sql_cmd);
   		
   		Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
   		Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
   		$db->getProfiler()->setEnabled(false);
//    		exit();
   		$db->commit();
   	}catch (Exception $e){
   		$db->rollBack();
   		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
   	}
   }
public function cancelIlPayment($data){
	$db = $this->getAdapter();
	$db_loan = new Loan_Model_DbTable_DbLoanILPayment();
	$db_group = new Loan_Model_DbTable_DbGroupPayment();
	$db->beginTransaction();
	$is_set = 0;
	try{
		$id = $data["id"];
		$recipt_money = $db_loan->getReceiptMoneyById($id);
		$recipt_money_detail = $db_loan->getReceiptMoneyDetailByID($id);
			foreach ($recipt_money_detail as $row_recipt_detail){
				$fun_id = $row_recipt_detail["lfd_id"];
				if($is_set!=1){
					$loan_number = $row_recipt_detail["loan_number"];
					$rs_fun = $db_group->getFunDetailByLoanNumber($loan_number);
					$rs_group = "SELECT m.group_id FROM ln_loan_member AS m WHERE m.loan_number = '$loan_number' GROUP BY m.loan_number";
					$group_id = $db->fetchOne($rs_group);
					if(empty($rs_fun) or $rs_fun=="" or $rs_fun==null){
						$arr_member = array(
							'is_completed'	=>	0,
						);
						$this->_name = "ln_loan_member";
						$where = $db->quoteInto("loan_number=?", $loan_number);
						$this->update($arr_member, $where);
						
						$arr_group = array(
							'status'	=>	1,
						);
						$this->_name = "ln_loan_group";
						$where = $db->quoteInto("g_id=?", $group_id);
						$this->update($arr_group, $where);
					}
					$is_set=1;
				}
				if($recipt_money["payment_option"]==1){
					//echo 1111;
					
					$is_completed = $db_loan->getFunDetailById($fun_id);
					if($is_completed==1){
						$arr_update = array(
							'is_completed' =>	0,
						);
						$this->_name = "ln_loanmember_funddetail";
						$where = $db->quoteInto("id=?", $fun_id);
						$this->update($arr_update, $where);
					}else{
						$arr_update = array(
							'principle_after'		=> 	$row_recipt_detail["principal_permonth"],
							'total_interest_after'	=>	$row_recipt_detail["total_interest"],
							'penelize'				=>	$row_recipt_detail["penelize_amount"],
							'service_charge'		=>	$row_recipt_detail["service_charge"],
							'total_payment_after'	=>	$row_recipt_detail["total_payment"],	
							'is_completed' 			=>	0,
						);
						$this->_name = "ln_loanmember_funddetail";
						$where = $db->quoteInto("id=?", $fun_id);
						$this->update($arr_update, $where);
					}
				}else{
					$arr_update = array(
							'is_completed' =>	0,
					);
					$this->_name = "ln_loanmember_funddetail";
					$where = $db->quoteInto("id=?", $fun_id);
					$this->update($arr_update, $where);
				}
			}
			
			$sql = "DELETE FROM `ln_client_receipt_money` WHERE `id`=$id";
			$db->query($sql);
			
			$sql_cmd = "DELETE FROM `ln_client_receipt_money_detail` WHERE `crm_id` = $id";
			$db->query($sql_cmd);
		//exit();
 		$db->commit();
	}catch (Exception $e){
		$db->rollBack();
		echo $e->getMessage();
		
	}
}   
   public function getReceiptMoneyById($id){
   	$db = $this->getAdapter();
   	$sql = "SELECT lc.id,lc.`service_charge`,lc.`penalize_amount`,lc.`payment_option`,lc.`recieve_amount`,lc.`total_interest`,lc.`total_payment` FROM `ln_client_receipt_money` AS lc WHERE lc.`id`=$id";
   	return $db->fetchRow($sql);
   }
    
   public function getReceiptMoneyDetailByID($id){
   	$db = $this->getAdapter();
   	$sql = "SELECT lc.`crm_id`,lc.`lfd_id`,lc.`loan_number`,lc.`service_charge`,lc.`penelize_amount`,lc.`total_interest`,lc.`total_payment`,lc.`total_recieve`,lc.`principal_permonth`,old_penelize,old_service_charge FROM `ln_client_receipt_money_detail` AS lc WHERE lc.`crm_id`=$id";
   	return $db->fetchAll($sql);
   }
   public function getFunDetailById($id){
   	$db = $this->getAdapter();
   	$sql = "SELECT lf.`is_completed` FROM `ln_loanmember_funddetail` AS lf WHERE lf.id =$id";
   	return $db->fetchOne($sql);
   }
	public function getAllLoanNumberByBranch($type){
		$db = $this->getAdapter();
		if($type==1){
			$sql="SELECT m.`loan_number` AS id,m.`loan_number` AS `name`,g.`branch_id` FROM `ln_loan_member` AS m,`ln_loan_group` AS g WHERE m.`group_id`= g.`g_id` AND g.`loan_type`=1 AND m.status=1 AND m.is_reschedule!=1 ";
			return $db->fetchAll($sql);
		}else{
			$sql="SELECT m.`loan_number` AS id,m.`loan_number` AS `name`,g.`branch_id` FROM `ln_loan_member` AS m,`ln_loan_group` AS g WHERE m.`group_id`= g.`g_id` AND g.`loan_type`=2 AND m.status=1 AND m.is_reschedule!=1 GROUP BY m.`loan_number` ";
			return $db->fetchAll($sql);
		}
	}
}

