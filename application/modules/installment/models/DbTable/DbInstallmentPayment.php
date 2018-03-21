<?php

class Installment_Model_DbTable_DbInstallmentPayment extends Zend_Db_Table_Abstract
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
    	$sql ="SELECT lcrm.`id`,
    				(SELECT b.`branch_namekh` FROM `ln_branch` AS b WHERE b.`br_id`=lcrm.`branch_id` LIMIT 	1) AS branch,
					(SELECT sale_no FROM ln_ins_sales_install l WHERE l.id=lcrm.loan_id LIMIT 1) AS loan_number,
					(SELECT c.`name_kh` FROM `ln_ins_client` AS c WHERE c.`client_id`=lcrm.`client_id` LIMIT 1) AS team_group ,
					lcrm.`receipt_no`,
					lcrm.`principal_paid`,
					lcrm.`interest_paid`,
					lcrm.`penalize_paid`,
					lcrm.`recieve_amount`,
					lcrmd.`date_payment`,
					lcrm.`date_input`,
				    'delete'
				FROM 
					`ln_ins_receipt_money` AS lcrm,
					`ln_ins_receipt_money_detail` AS lcrmd
    				 WHERE  lcrm.id=lcrmd.`receipt_id` 
    				AND lcrm.status=1";
    	$where ='';
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
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
    	if($search['member']>0){
    		$where.=" AND lcrm.`client_id`= ".$search['member'];
    	}
    	if($search['branch_id']>0){
    		$where.=" AND lcrm.`branch_id`= ".$search['branch_id'];
    	}
    	$group_by = " GROUP BY lcrm.id";
    	$order = " ORDER BY receipt_no DESC";
    	return $db->fetchAll($sql.$where.$group_by.$order);
}
function getAllRemainSchedule($loan_id){
	$sql="
		SELECT * 
			FROM `ln_ins_sales_installdetail`
			WHERE sale_id=$loan_id AND status=1 
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
    	$sql="SELECT id  FROM ln_ins_receipt_money WHERE receipt_no='$reciept_no' ORDER BY id DESC LIMIT 1 ";
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
    				'recieve_amount'	=> $data["amount_receive"],
    				'total_paymentpaid'	=> $data["amount_receive"],//check
    				'return_amount'		=> $return,
    				'note'				=> $data['note'],
    				'status'			=> 1,
    				'user_id'			=> $user_id,
    				'late_day'			=> $data['amount_late'],
    				'is_completed'		=> $is_compleated,
    				'payment_option'	=> $data["option_pay"],
    				);
    		
		$this->_name = "ln_ins_receipt_money";
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
	    			$after_penalty = 0;//$data["penelize_".$i];
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
	    			$record_id = $rsloan['id'];
	    			if($record_id!=""){
	    				if($option_pay==1 OR $option_pay==2 OR $option_pay==3 OR $option_pay==4){//បង់ធម្មតា
	    					if($option_pay==1){
	    						$total_principal =$after_principal;
	    					}elseif($option_pay==3){
	    						$total_interest=0;
	    						$total_principal = $after_principal;
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
		    						'penelize_amount'	=> 0,
		    					);
		    				$db->insert("ln_ins_receipt_money_detail", $arr_money_detail);
		    				
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
		    				$this->_name="ln_ins_sales_installdetail";
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
//     			'service_paid'  => 0,//echeck
	    	);
	    	$this->_name="ln_ins_receipt_money";
	    	$where = $db->quoteInto("id=?", $receipt_id);
	    	$this->update($arr, $where);
	    	
	    	$rs = $this->getRemainSchedule($data['loan_number']);
	    	if(empty($rs)){//update ករណីបង់ចុងក្រោយ គឺ updatE ទៅជាដាច់
	    		$arr = array(
	    				'is_payoff'=> 1,//check here
	    				'payment_option'=>4
	    		);
	    		
	    		$this->_name="ln_ins_receipt_money";
	    		$where = $db->quoteInto("id=?", $receipt_id);
	    		$this->update($arr, $where);
	    		
	    		$arr = array('is_completed'=>1);
	    		$this->_name="ln_ins_sales_install";
	    		$where = $db->quoteInto("id=?", $data['loan_number']);
	    		$this->update($arr, $where);
	    	}
    		$db->commit();
    	}catch (Exception $e){
    		echo $e->getMessage();exit();
    		$db->rollBack();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
public function getIlPaymentNumber(){
    $this->_name='ln_ins_receipt_money';
	    $db = $this->getAdapter();
	    $sql=" SELECT id  FROM $this->_name ORDER BY id DESC LIMIT 1 ";
	    $acc_no = $db->fetchOne($sql);
	    $new_acc_no= (int)$acc_no+1;
	    $acc_no= strlen((int)$acc_no+1);
	    $pre = "";
	    $pre_fix="IP-";
	    for($i = $acc_no;$i<5;$i++){
	    	$pre.='0';
	    }
    return $pre_fix.$pre.$new_acc_no;
}
   function getSaleinstallbyid($id){//group id
    	$sql ="SELECT s.* FROM 
			    	`ln_ins_sales_install` AS s
			    	WHERE  s.id = $id ";
    	return $this->getAdapter()->fetchRow($sql);
   }
    function deleteRecord($id){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$sql = "SELECT
    		crm.loan_id
    		FROM ln_ins_receipt_money AS crm
    		WHERE  crm.`id` = $id ";
    		$loan_id = $db->fetchOne($sql);

    		$arr = array(
			'is_completed'=>0
			);
    		$this->_name="ln_ins_sales_install";
    		$where="id=".$loan_id;
    		$this->update($arr, $where);
    		   
    		$sql = "SELECT
    		crmd.*
    		FROM
    		`ln_ins_receipt_money_detail` AS crmd,
    		ln_client_receipt_money as crm
    		WHERE
    		crm.id = crmd.`receipt_id`
    		and crmd.`receipt_id` = $id ";
    		$receipt_money_detail = $db->fetchAll($sql);

    		$this->_name = "ln_ins_sales_installdetail";
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
    
    		$this->_name = "ln_ins_receipt_money";
    		$where = " id = $id ";
    		$this->delete($where);
    
    		$this->_name = "ln_ins_receipt_money_detail";
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
   function getLoanPaymentByLoanNumber($data){
    	$db = $this->getAdapter();
    	$loan_number= $data['loan_number'];
    		$sql="SELECT
			   	lc.`client_id`,
			   	lc.`client_number`,
			   	lc.`name_kh`,
			   	l.`sale_no`,
   				l.`branch_id`,
   				l.`payment_method`,
   				l.interest_rate,
   				l.balance AS loan_amount,
   				l.duration AS total_duration,
   				DATE_FORMAT(l.date_sold, '%d/%m/%Y') AS `date_release`,
   				l.date_sold AS loan_releate,
   				(SELECT date_pay FROM `ln_ins_receipt_money` WHERE loan_id=$loan_number AND status=1 ORDER BY date_pay DESC LIMIT 1) AS last_pay_date,
   				(SELECT date_payment FROM `ln_ins_receipt_money` WHERE loan_id=$loan_number AND status=1 ORDER BY date_payment DESC LIMIT 1) AS prev_paymentdate,
   				 ld.*,
   				DATE_FORMAT(ld.date_payment, '%d-%m-%Y') AS `date_payments`,
   				(SELECT SUM(principal_paid) FROM `ln_ins_receipt_money` WHERE loan_id=l.id AND status=1) AS principal_paid
   			   FROM
   					  `ln_ins_client` AS lc,
   					  `ln_ins_sales_install` AS l ,
   					  `ln_ins_sales_installdetail` AS ld
   				WHERE 
   					 l.status=1
   					 AND l.is_completed=0
   					 AND l.`customer_id`=lc.`client_id`
   					 AND l.`id` = ld.`sale_id`	  
   					 AND ld.`status`=1
   					 AND ld.is_completed=0
   					 AND l.`id` = ".$loan_number;
    		
    	return $db->fetchAll($sql);
   }
   
   function getAllLoanPaymentByLoanNumber($data){
//    	$db = $this->getAdapter();
//    	$loan_number= $data['loan_numbers'];
   	
//    	$where = 'l.`id`='."'".$loan_number."'";
//    	$sql ="SELECT
// 				   	l.`id`,
// 				   	lc.`client_number`,
// 				   	lc.`name_kh`,
// 				   	l.`loan_number`,
// 					l.`currency_type`,
//    					l.`branch_id`,
//    					l.`collect_typeterm`,
//    					l.`co_id`,
//    					l.`payment_method`,
//    					d.*,
//    					DATE_FORMAT(d.date_payment, '%d-%m-%Y') AS `date_payments`
//    					FROM
//    					 `ln_client` AS lc,
//    					 `ln_loan` AS l,
//    					 `ln_loan_detail` AS d
//    					  WHERE 
//    					  l.`id`=d.`loan_id`
//    					  AND l.`customer_id`=lc.`client_id`
//    					  AND l.`status`=1
//    					  AND l.`loan_type`=1
//    					  AND d.`status`=1
//    					  AND $where";   
//    		return $db->fetchAll($sql);
}
   
//    public function getLastPayDate($data){
//    	$loanNumber = $data['loan_numbers'];
//    	$db = $this->getAdapter();
//    	$sql ="SELECT 
// 			  lf.`date_payment`
// 			FROM
// 			  `ln_loanmember_funddetail` AS lf,
// 			  `ln_client_receipt_money` AS c,
// 			  `ln_loan_member` AS lm
// 			WHERE c.`loan_number` = lm.`loan_number`
// 			  AND lm.`member_id` = lf.`member_id`
// 			  AND c.`loan_number` = '$loanNumber' 
// 			  AND lf.`is_completed`=1
// 			ORDER BY lf.`id` DESC LIMIT 1";
//    	//return $sql;
//    	return $db->fetchOne($sql);
//    }
//    public function getLastPaymentDate($data){
//    	$loanNumber = $data['loan_numbers'];
//    	$fn_id = $data["fn_id"];
//    	$db = $this->getAdapter();
//    	$sql = "SELECT 
// 			  c.`date_input` 
// 			FROM
// 			  `ln_client_receipt_money` AS c,
// 			  `ln_client_receipt_money_detail` AS cr 
// 			WHERE c.`loan_number` = '$loanNumber' 
// 			  AND c.`id` = cr.`crm_id` 
// 			  AND cr.`lfd_id` = $fn_id 
// 			ORDER BY c.`receipt_no` DESC 
// 			LIMIT 1";
//    	//return $sql;
//    	return $db->fetchOne($sql);
//    }
//    public function getLaonHasPayByLoanNumber($loan_number){
//    	$db= $this->getAdapter();
// 	$sql="SELECT 
// 			  (SELECT c.`name_kh` FROM `ln_client` AS c WHERE c.`client_id`=crm.`client_id` LIMIT 1) AS client_name,
// 			  (SELECT c.`client_number` FROM `ln_client` AS c WHERE c.`client_id`=crm.`client_id` LIMIT 1) AS client_code,
// 			  crm.`receipt_no`,crm.paid_times,
// 			  DATE_FORMAT(crm.date_pay, '%d-%m-%Y') AS `date_input`,
// 			  (crm.`principal_paid`) AS principal_paid,
// 			  (crm.`interest_paid`) AS interest_paid,
// 			  (crm.`penalize_paid`) AS penalize_paid,
// 			  (crm.`service_paid`) AS service_paid,
// 			  (crm.`total_paymentpaid`) AS total_paymentpaid,
// 			  crm.`currency_type`,
// 			  crm.is_completed,
// 			  DATE_FORMAT(crmd.date_payment, '%d-%m-%Y') AS `date_payment`
// 			FROM
// 			  `ln_client_receipt_money` AS crm,
// 			  `ln_client_receipt_money_detail` AS crmd 
// 			WHERE 
// 			  crm.status=1
// 			  AND crm.`id` = crmd.`receipt_id` 
// 			  AND crm.`loan_id` = '$loan_number' 
// 			  GROUP BY crm.`id` ORDER BY crm.`id` DESC";
//    	return $db->fetchAll($sql);
//    }
//    function getFunByGroupId($id){
//    		$db = $this->getAdapter();
//    		$sql="SELECT lf.`id` FROM `ln_loanmember_funddetail` AS lf, `ln_loan_member` AS lm WHERE lm.`member_id` = lf.`member_id` AND lm.`group_id` = $id AND lf.`is_completed`=0";
//    		return $db->fetchAll($sql);
//    }
//    public function getFunDetail($id){
//    	$db = $this->getAdapter();
//    	$sql="SELECT f.`id`,f.`penelize`,f.`principle_after`,f.`service_charge`,f.`total_interest_after`,f.`total_payment_after`,f.`is_completed` FROM `ln_loanmember_funddetail` AS f WHERE f.`id`=$id";
//    	return $db->fetchAll($sql);
//    }
   
//    public function getReceiptMoneyById($id){
//    	$db = $this->getAdapter();
//    	$sql = "SELECT lc.id,lc.`service_charge`,lc.`penalize_amount`,lc.`payment_option`,lc.`recieve_amount`,lc.`total_interest`,lc.`total_payment` FROM `ln_client_receipt_money` AS lc WHERE lc.`id`=$id";
//    	return $db->fetchRow($sql);
//    }
    
//    public function getReceiptMoneyDetailByID($id){
//    	$db = $this->getAdapter();
//    	$sql = "SELECT lc.`crm_id`,lc.`lfd_id`,lc.`loan_number`,lc.`service_charge`,lc.`penelize_amount`,lc.`total_interest`,lc.`total_payment`,lc.`total_recieve`,lc.`principal_permonth`,old_penelize,old_service_charge FROM `ln_client_receipt_money_detail` AS lc WHERE lc.`crm_id`=$id";
//    	return $db->fetchAll($sql);
//    }
}