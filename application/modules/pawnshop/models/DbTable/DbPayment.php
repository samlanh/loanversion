<?php

class Pawnshop_Model_DbTable_DbPayment extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_pawn_receipt_money';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authloan');
    	return $session_user->user_id;
    }
    public function getAllPawnPayment($search){
		$start_date = $search['start_date'];
    	$end_date = $search['end_date'];
    	
    	$from_date =(empty($search['start_date']))? '1': " cm.date_input >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " cm.date_input <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
//     	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
//     	$reciept = $tr->translate("PAYMENT_RECEIPT");
    	$db = $this->getAdapter(); 
    	$sql = " SELECT cm.`id`,
					(SELECT b.`branch_namekh` FROM `ln_branch` AS b WHERE b.`br_id`=cm.`branch_id` LIMIT 1) AS branch,
					pp.`loan_number`,
					(SELECT c.`name_kh` FROM `ln_clientsaving` AS c WHERE c.`client_id`=cm.`client_id` AND client_type=1 LIMIT 1) AS client_name ,
					cm.`receipt_no`,
					cm.`principal_paid`,
					cm.`interest_paid`,
					cm.`penalize_paid`,
					cm.`recieve_amount`,
					cm.`date_pay`,
					cm.`date_input`
				FROM 
					`ln_pawn_receipt_money` AS cm,
					ln_pawnshop AS pp 
    			WHERE pp.id=cm.loan_id ";
//     	,'$reciept',
//     	'delete'
    	if(!empty($search['advance_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['advance_search']));
    		$s_where[] = " cm.`receipt_no` LIKE '%{$s_search}%'";
    		$s_where[] = " pp.`loan_number` LIKE '%{$s_search}%'";
    		
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}

    	if($search['client_name']>0){
    		$where.=" AND cm.`client_id`= ".$search['client_name'];
    	}
    	if($search['branch_id']>0){
    		$where.=" AND cm.`branch_id`= ".$search['branch_id'];
    	}
    	
    	$order = " ORDER BY id DESC ";
    	return $db->fetchAll($sql.$where.$order);
    }
    public function addPawnpayment($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	$session_user=new Zend_Session_Namespace('authloan');
    	$user_id = $session_user->user_id;
    	try{
    		$reciept_no = $data['reciept_no'];
    		$sql="SELECT id  FROM ln_pawn_receipt_money WHERE receipt_no='$reciept_no' ORDER BY id DESC LIMIT 1 ";
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
    				'service_chargeamount'=>$data["service_charge"],
    				'recieve_amount'	=> $data["amount_receive"],
    				'total_paymentpaid'	=> $data["amount_receive"],//check
    				'return_amount'		=> $return,
    				'note'				=> $data['note'],
    				'status'			=> 1,
    				'user_id'			=> $user_id,
    				'late_day'			=> $data['amount_late'],
    				'is_completed'		=> $is_compleated,
    				'payment_option'	=> $data["option_pay"],
    				'currency_type'		=> $data["currency_type"],
    		);
    
    		$this->_name = "ln_pawn_receipt_money";
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
    			if($remain_money<=0){
    				break;
    			}
    			$after_outstanding= $rsloan['outstanding_after'];
    			$after_payment_after= $rsloan['total_payment_after'];
    			$after_principal= $rsloan['principle_after'];//$data["principal_permonth_".$i];
    			$total_principal=$after_principal;
    			$after_interest = $rsloan['total_interest_after'];//$data["interest_".$i];
    			if($option_pay!=4){
    				$total_interest = $after_interest;
    			}
    			$after_penalty = 0;
    			$date_payment = $rsloan['date_payment'];
    
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
    					if($option_pay==1){//បង់ធម្មតា
    						$total_principal = $after_principal;
    					}elseif($option_pay==3){//បង់រំលស់ប្រាក់ដើម
    						$total_interest = 0;
    						$total_principal = $after_outstanding;//$data["principal_permonth_".$i];
    					}elseif($option_pay==2){//បង់មុន ដើម្បីអោយគណនា១ Record ម្តងៗរក completed=1
    						$total_interest = $after_interest;//$data["interest_".$i];
    						$total_principal = $after_principal;// $data["principal_permonth_".$i];
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
    							'penelize_amount'	=> 0,//$rsloan['outstanding_after'],
    					);
    					$db->insert("ln_pawn_receipt_money_detail", $arr_money_detail);
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
    					$this->_name="ln_pawnshop_detail";
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
    		$this->_name="ln_pawn_receipt_money";
    		$where = $db->quoteInto("id=?", $receipt_id);
    		$this->update($arr, $where);
    
    		$rs = $this->getRemainSchedule($data['loan_number']);
    		if(empty($rs)){//update ករណីបង់ចុងក្រោយ គឺ updatE ទៅជាដាច់
    			$arr = array(
    					'is_payoff'=> 1,//check here
    					'payment_option'=>4);
    
    			$this->_name="ln_pawn_receipt_money";
    			$where = $db->quoteInto("id=?", $receipt_id);
    			$this->update($arr, $where);
    
    			$arr = array('is_completed'=>1);
    			$this->_name="ln_pawnshop";
    			$where = $db->quoteInto("id=?", $data['loan_number']);
    			$this->update($arr, $where);
    		}
    		$db->commit();
    		return $receipt_id;
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    function deletePawnpayment($id){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$sql = "SELECT
    			crm.loan_id
    		FROM ln_pawn_receipt_money AS crm
    			WHERE  crm.`id` = $id ";
    		$loan_id = $db->fetchOne($sql);
    
    		$arr = array(
    				'is_completed'=>0
    		);
    		$this->_name="ln_pawnshop";
    		$where="id=".$loan_id;
    		$this->update($arr, $where);
    			
    		$sql = "SELECT
    			crmd.*
    		FROM
    			`ln_pawn_receipt_money_detail` AS crmd,
    			ln_pawn_receipt_money as crm
    		WHERE
    			crm.id = crmd.`receipt_id`
    			and crmd.`receipt_id` = $id ";
    		$receipt_money_detail = $db->fetchAll($sql);
    
    		$this->_name = "ln_pawnshop_detail";
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
    
    		$this->_name = "ln_pawn_receipt_money";
    		$where = " id = $id ";
    		$this->delete($where);
    
    		$this->_name = "ln_pawn_receipt_money_detail";
    		$where = " receipt_id = $id ";
    		$this->delete($where);
    		$db->commit();
    
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		Application_Form_FrmMessage::message("Delete Failed");
    	}
    }
    
    public function getIlPaymentNumber(){
    	$this->_name='ln_pawn_receipt_money';
    	$db = $this->getAdapter();
    	$sql=" SELECT id  FROM $this->_name ORDER BY id DESC LIMIT 1 ";
    	$acc_no = $db->fetchOne($sql);
    	$new_acc_no= (int)$acc_no+1;
    	$acc_no= strlen((int)$acc_no+1);
    	$pre = "";
    	$pre_fix="PS-";
    	for($i = $acc_no;$i<5;$i++){
    		$pre.='0';
    	}
    	return $pre_fix.$pre.$new_acc_no;
    }
    function getRemainSchedule($loan_id){
    	$db = $this->getAdapter();
    	$sql="SELECT *
    	FROM
    	`ln_pawnshop_detail` AS d
    	WHERE
    	d.amount_paidprincipal=0
    	AND d.`is_completed` = 0
    	AND d.status=1
    	AND d.`pawn_id` = $loan_id ORDER BY date_payment ASC ";
    	return $db->fetchRow($sql);
    }
    function getAllRemainSchedule($loan_id){
    	$sql="
    	SELECT *
    	FROM `ln_pawnshop_detail`
    	WHERE pawn_id=$loan_id AND status=1
    	AND is_completed=0
    	ORDER BY date_payment ASC ";
    	return $this->getAdapter()->fetchAll($sql);
    }
    function getPawnAccountNumber(){//type ==1 is ilPayment, type==2 is group payment
    	$db = $this->getAdapter();
    	$sql ="SELECT 
    				id,
    				CONCAT((SELECT name_kh FROM `ln_clientsaving` WHERE 
    			   ln_clientsaving.client_id = ln_pawnshop.customer_id LIMIT 1),'-',`loan_number`) AS `name`,
    			`branch_id`
    		 FROM `ln_pawnshop` WHERE 
    	`is_completed` = 0 AND status=1 ";
    	return $db->fetchAll($sql);
    }
    public function getClientNamebyBranch($type=null,$client_id=null ,$row=null){
    	$this->_name='ln_client';
    	$where='';
    	$sql = " SELECT client_id AS id,name_kh AS name,branch_id,client_number 
    		From ln_clientsaving
    		WHERE 	
    		status=1 AND (name_kh!='' OR name_en!='')
    		ORDER BY client_id DESC ";
    	$db = $this->getAdapter();
    	return $db->fetchAll($sql.$where);
    }
    public function getClientCodebyBranch($type=null,$client_id=null ,$row=null){
    	$this->_name='ln_client';
    	$where='';
    	$sql = " SELECT client_id AS id,client_number AS name,branch_id,client_number 
    		From ln_clientsaving
    		WHERE status=1 
    		AND (name_en!='' OR name_kh!='') 
    		ORDER BY client_id DESC ";
    	$db = $this->getAdapter();
    	return $db->fetchAll($sql.$where);
    }
	function getPawnPaymentByID($loan_number){//tab1
		$db = $this->getAdapter();
		$sql="SELECT
			   	lc.`client_id`,
			   	lc.`client_number`,
			   	lc.`name_kh`,
			   	l.`loan_number`,
				l.`currency_type`,
   				l.`branch_id`,
   				l.`payment_method`,
   				l.interest_rate,
   				l.release_amount AS loan_amount,
   				l.level,
   				l.total_duration,
   				DATE_FORMAT(l.date_release, '%d/%m/%Y') AS `date_release`,
   				l.date_release AS loan_releate,
   				(SELECT date_pay FROM `ln_pawn_receipt_money` WHERE loan_id=$loan_number AND STATUS=1 ORDER BY date_pay DESC LIMIT 1) AS last_pay_date,
   				(SELECT date_payment FROM `ln_pawn_receipt_money` WHERE loan_id=$loan_number AND STATUS=1 ORDER BY date_payment DESC LIMIT 1) AS prev_paymentdate,
   				 ld.*,
   				DATE_FORMAT(ld.date_payment, '%d-%m-%Y') AS `date_payments`,
   				l.first_payment AS `first_payment`,
   				(SELECT SUM(principal_paid) FROM `ln_pawn_receipt_money` WHERE loan_id=l.id AND STATUS=1) AS principal_paid
   			   FROM
   					  `ln_clientsaving` AS lc,
   					  `ln_pawnshop` AS l ,
   					  `ln_pawnshop_detail` AS ld
   				WHERE 
   					 l.status=1
   					 AND l.is_completed=0
   					 AND l.is_dach=0
   					 AND l.`customer_id`=lc.`client_id`
   					 AND l.`id` = ld.`pawn_id`	  
   					 AND ld.`status`=1
   					 AND ld.is_completed=0
   					 AND l.`id` = ".$loan_number;
		return $db->fetchAll($sql);
	}
	function getAllLoanPaymentByLoanNumber($data){//tab2
		$db = $this->getAdapter();
		$pawnid= $data['pawnid'];
	
		$sql ="SELECT
		l.`id`,
		lc.`client_number`,
		lc.`name_kh`,
		l.`loan_number`,
		l.`currency_type`,
		l.`branch_id`,
		l.`payment_method`,
		d.*,
		DATE_FORMAT(d.date_payment, '%d-%m-%Y') AS `date_payments`
		FROM
		`ln_client` AS lc,
		`ln_pawnshop` AS l,
		`ln_pawnshop_detail` AS d
		WHERE
		l.`id`=d.`pawn_id`
		AND l.`customer_id`=lc.`client_id`
		AND l.`status`=1
		AND d.`status`=1
		AND l.id=".$pawnid;
		return $db->fetchAll($sql);
	}
	public function getschedulepaid($loan_number){//tab3
		$db= $this->getAdapter();
		$sql="SELECT
		(SELECT c.`name_kh` FROM `ln_clientsaving` AS c WHERE c.`client_id`=crm.`client_id` LIMIT 1) AS client_name,
		(SELECT c.`client_number` FROM `ln_clientsaving` AS c WHERE c.`client_id`=crm.`client_id` LIMIT 1) AS client_code,
		crm.`receipt_no`,
		crm.paid_times,
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
		`ln_pawn_receipt_money` AS crm,
		`ln_pawn_receipt_money_detail` AS crmd
		WHERE
		crm.status=1
		AND crm.`id` = crmd.`receipt_id`
		AND crm.`loan_id` = $loan_number
		GROUP BY crm.`id` ORDER BY crm.`id` DESC";
		return $db->fetchAll($sql);
	}
	public function getPawnPaymentByIdForPrint($id){ //for add payment reciept get payment by reciept id
		$db = $this->getAdapter();
      	$sql="SELECT
      	(SELECT
      	`ln_branch`.`branch_namekh`
      	FROM `ln_branch` WHERE (`ln_branch`.`br_id` = `crm`.`branch_id`)
      	LIMIT 1) AS `branch_name`,
      	(SELECT `ln_currency`.`symbol`
      	FROM `ln_currency`
      	WHERE (`ln_currency`.`id` = `crm`.`currency_type`) LIMIT 1) AS `currency_typeshow`,
      	(SELECT `l`.`loan_number` FROM `ln_pawnshop` `l` WHERE (`l`.`id` = `crm`.`loan_id`) LIMIT 1) AS `loan_number`,
      	(SELECT `c`.`name_kh` FROM `ln_clientsaving` `c` WHERE (`c`.`client_id` = `crm`.`client_id`) LIMIT 1) AS `client_name`,
      	(SELECT  `c`.`client_number` FROM `ln_clientsaving` `c` WHERE (`c`.`client_id` = `crm`.`client_id`) LIMIT 1) AS `client_number`,
      	(SELECT `u`.`first_name` FROM `rms_users` `u` WHERE (`u`.`id` = `crm`.`user_id`)) AS `user_name`,
      	`crm`.`id`                   AS `id`,
      	`crm`.`receipt_no`           AS `receipt_no`,
      	`crm`.`branch_id`            AS `branch_id`,
      	`crm`.`date_pay`             AS `date_pay`,
      	`crm`.`date_payment`         AS `date_payment`,
      	`crm`.`date_input`           AS `date_input`,
      	`crm`.`note`                 AS `note`,
      	`crm`.`user_id`              AS `user_id`,
      	`crm`.`status`               AS `status`,
      	`crm`.`payment_option`       AS `payment_option`,
      	`crm`.`currency_type`        AS `currency_type`,
      	`crm`.`is_payoff`            AS `is_payoff`,
      	`crm`.`total_payment`        AS `total_payment`,
      	`crm`.`principal_amount`     AS `principal_amount`,
      	`crm`.`interest_amount`      AS `interest_amount`,
      	`crm`.`principal_paid`       AS `principal_paid`,
      	`crm`.`interest_paid`        AS `interest_paid`,
      	`crm`.`service_paid`         AS `service_paid`,
      	`crm`.`penalize_paid`        AS `penalize_paid`,
      	`crm`.`total_paymentpaid`    AS `total_paymentpaid`,
      	`crm`.`recieve_amount`       AS `amount_recieve`,
      	`crm`.`return_amount`        AS `return_amount`,
      	`crm`.`penalize_amount`      AS `penelize`,
      	`crm`.`service_chargeamount` AS `service_charge`,
      	`crm`.`client_id`            AS `client_id`,
      	`crm`.`paid_times`           AS `paid_times`,
      	ps.`product_id`         AS `product_id`,
      	(SELECT p.product_en FROM `ln_pawnshopproduct` AS p WHERE p.id = ps.`product_id` LIMIT 1) AS proTitle,
      	(SELECT p.product_kh FROM `ln_pawnshopproduct` AS p WHERE p.id = ps.`product_id` LIMIT 1) AS proTitleKh,
      	ps.`product_description`        AS `product_description`
      	FROM `ln_pawn_receipt_money` `crm`,
      	`ln_pawn_receipt_money_detail` `d`,
		`ln_pawnshop` `ps`
      	WHERE (`crm`.`status` = 1)
      	AND (`crm`.`id` = `d`.`receipt_id`)
      	AND (`crm`.`loan_id` = ps.id)
      	AND (`crm`.`status` = 1)
      	AND crm.id = $id
      	GROUP BY `crm`.`id` ";
      
      	$sql.=" ORDER BY `crm`.`id` DESC LIMIT 1";
      	return $db->fetchRow($sql);
	}
}