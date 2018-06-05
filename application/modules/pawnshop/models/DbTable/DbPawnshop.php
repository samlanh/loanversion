<?php

class Pawnshop_Model_DbTable_DbPawnshop extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_pawnshop';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authloan');
    	return $session_user->user_id;
    	 
    }
    public function getAllPawnshop($search,$reschedule =null){
    	$from_date =(empty($search['start_date']))? '1': " date_release >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " date_release <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	$tr= Application_Form_FrmLanguages::getCurrentlanguage();
    	$complete = $tr->translate("COMPLETED");
    	$not_complete = $tr->translate("Not Complete");
    	$db = $this->getAdapter();
    	$sql = " SELECT id,
	    		(SELECT branch_namekh FROM `ln_branch` WHERE br_id =branch_id LIMIT 1) AS branch,
	    		loan_number,
	    		(SELECT name_kh FROM `ln_clientsaving` WHERE client_id = ln_pawnshop.customer_id LIMIT 1) AS client_name_kh,
	    		receipt_num,CONCAT(release_amount,
	    		(SELECT symbol FROM `ln_currency` WHERE id =ln_pawnshop.currency_type LIMIT 1)) AS currency_type,
	    		admin_fee,
	    		CONCAT(total_duration,(SELECT name_kh FROM `ln_view` WHERE type = 14 AND key_code = term_type )) term_type,
				interest_rate,
				(SELECT product_kh FROM `ln_pawnshopproduct` WHERE id=ln_pawnshop.product_id limit 1) as product_name,
				date_release,date_line,
    			(SELECT first_name FROM `rms_users` WHERE id=user_id LIMIT 1) As user_name,
    			CASE    
					WHEN  is_completed = 0 THEN '$not_complete'
					WHEN  is_completed = 1 THEN '$complete'
				END AS completed_status,
    			status
    		 FROM `ln_pawnshop` WHERE 1 ";
    	
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_where[] = " loan_number LIKE '%{$s_search}%'";
    		$s_where[] = " receipt_num LIKE '%{$s_search}%'";
    		$s_where[] = " release_amount LIKE '%{$s_search}%'";
    		$s_where[] = " interest_rate LIKE '%{$s_search}%'";
    		$s_where[] = " total_duration LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}

    	if(($search['members'])>0){
    		$where.= " AND customer_id=".$search['members'];
    	}

    	if(($search['branch_id'])>0){
    		$where.= " AND branch_id = ".$search['branch_id'];
    	}
    	if(($search['currency_type'])>0){
    		$where.= " AND currency_type=".$search['currency_type'];
    	}
    	if(($search['product_id'])>0){
    		$where.= " AND product_id=".$search['product_id'];
    	}
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission('branch_id');
 	   $order=" ORDER BY id DESC";
    	$db = $this->getAdapter();    
    	return $db->fetchAll($sql.$where.$order);
    }
    function getPawnshopById($id){//group id
    	$sql = " SELECT * FROM ln_pawnshop WHERE id =  $id ";
    	$where=" LIMIT 1 ";
    	return $this->getAdapter()->fetchRow($sql.$where);
    }
    function getPawnshopDetailByIdHaspaid($id){
    	$sql = " SELECT pd.*  FROM `ln_pawnshop_detail` AS pd WHERE pd.`is_completed`=1 AND pd.`pawn_id`= $id ";
    	$where=" ORDER BY pd.`id` DESC LIMIT 1 ";
    	return $this->getAdapter()->fetchRow($sql.$where);
    }
    public function addPawnshop($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$part= PUBLIC_PATH.'/images/pawnshop/';
    		if (!file_exists($part)) {
    			mkdir($part, 0777, true);
    		}
    		$photo ="";
    		if (!empty($_FILES['photo']['name'])){
    			$ss =   explode(".", $_FILES['photo']['name']);
    			$image_name = "pawn".date("Y").date("m").date("d").time().".".end($ss);
    			$tmp = $_FILES['photo']['tmp_name'];
    			if(move_uploaded_file($tmp, $part.$image_name)){
    				$photo = $image_name;
    			}
    			else{
    				$string = "Image Upload failed";
    			}
    		}
    		$dbtable = new Application_Model_DbTable_DbGlobal();
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
						'term_type'=>$data['payment_term'],
						'admin_fee'=>$data['admin_fee'],
						'interest_type'=>$data['interest_type'],
						'product_id'=>$data['product_id'],
						'est_amount'=>$data['estimatevalue'],
						'product_description'=>$data['description'],
						'receipt_num'=>$data['receipt_num'],
						'images'=>$photo,
				);
				$loan_id = $this->insert($datagroup);//add group loan
				
				$remain_principal = $data['total_amount'];
				$old_pri_permonth = 0;
				$old_interest_paymonth = 0;
				$old_amount_day = 0;
				$next_payment = $data['first_payment'];
				$curr_type = $data['currency_type'];
				
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
// 					$interest_paymonth = $data['total_amount']*($data['interest_rate']/100/$borrow_term)*30;
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
							'installment_amount'=>$i
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
    		Application_Form_FrmMessage::message("INSERT_FAIL");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    public function updatePawnshop($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$dbtable = new Application_Model_DbTable_DbGlobal();
    		$day_amount = 1;
    		$day_amount=30;
    		$datagroup = array(
    				'branch_id'=>$data['branch_id'],
    				'level'=>$data['level'],
    				'customer_id'=>$data['member'],
    				'release_amount'=>$data['total_amount'],
    				'date_release'=>$data['release_date'],
    				'date_line'=>$data['date_line'],
    				'create_date'=>date("Y-m-d"),
    				'total_duration'=>$data['period'],
    				'first_payment'=>$data['first_payment'],
    				'payment_method'=>1,
    				'term_type'=>$data['payment_term'],
    				'admin_fee'=>$data['admin_fee'],
    				'interest_type'=>$data['interest_type'],
    				'holiday'=>2,
    				'user_id'=>$this->getUserId(),
    				'currency_type'=>$data['currency_type'],
    				'release_amount'=>$data['total_amount'],//$data[''],
    				'interest_rate'=>$data['interest_rate'],
    				'status'=>$data['status_using'],
    				'is_completed'=>0,
    				'product_id'=>$data['product_id'],
    				'est_amount'=>$data['estimatevalue'],
    				'product_description'=>$data['description'],
    		);
    		
    		$part= PUBLIC_PATH.'/images/pawnshop/';
    		if (!file_exists($part)) {
    			mkdir($part, 0777, true);
    		}
    		$photo ="";
    		if (!empty($_FILES['photo']['name'])){
    			$ss =   explode(".", $_FILES['photo']['name']);
    			$image_name = "pawnshop".date("Y").date("m").date("d").time().".".end($ss);
    			$tmp = $_FILES['photo']['tmp_name'];
    			if(move_uploaded_file($tmp, $part.$image_name)){
    				$photo = $image_name;
    			}
    			else{
    				$string = "Image Upload failed";
    			}
    			$datagroup['images']=$photo;
    		}
    		
    		$loan_id = $data['id'];
    		$where="id=".$loan_id;
    		$this->update($datagroup, $where);//add group loan
    		
    		$where="pawn_id=".$loan_id;
    		$this->_name="ln_pawnshop_detail";
    		$this->delete($where);
    
    		$remain_principal = $data['total_amount'];
    		$old_pri_permonth = 0;
    		$old_interest_paymonth = 0;
    		$old_amount_day = 0;
    		$next_payment = $data['first_payment'];
    		$curr_type = $data['currency_type'];
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
    					'installment_amount'=>$i
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
    	try{
    		$dbtable = new Application_Model_DbTable_DbGlobal();
				$day_amount=30;
				$remain_principal = $data['total_amount'];
				$old_pri_permonth = 0;
				$old_interest_paymonth = 0;
				$old_amount_day = 0;
				$next_payment = $data['first_payment'];
				$curr_type = $data['currency_type'];
				
				$str_next = $dbtable->getNextDateById($data['payment_term'],2);
				$start_date = $data['release_date'];
				$from_date =  $data['release_date'];
// 				$borrow_term=30;//=1month
				$borrow_term = $dbtable->getSubDaysByPaymentTerm($data['payment_term'],null);//return amount day for payterm
				$this->_name='ln_pawnshoptest';
				for($i=1;$i<=$data['period'];$i++){
					$pri_permonth=0;
					if($i==$data['period']){//check here
						$old_pri_permonth = ($curr_type==1)?round($data['total_amount'],-2):$data['total_amount'];
						$remain_principal = $pri_permonth;
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
							'pawn_id'=>1,
							'total_principal'=>$remain_principal,//good
							'principal_permonth'=> $old_pri_permonth,//good
							'total_interest'=>$interest_paymonth,//good
							'total_payment'=>$old_pri_permonth+$interest_paymonth,//good
							'date_payment'=>$next_payment,//good
							'is_completed'=>0,
							'status'=>1,
							'amount_day'=>$amount_day,
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
			unset($datamember);
			
			$sql="SELECT f.*,
				DATE_FORMAT(f.date_payment, '%d-%m-%Y') AS date_payments,
				DATE_FORMAT(f.date_payment, '%Y-%m-%d') AS date_name
			FROM ln_pawnshoptest AS f WHERE f.pawn_id = 1 ";
			return $db->fetchAll($sql);
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("INSERT_FAIL");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    function updatePaymentStatus($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$this->_name='ln_pawnshop';
    		$arr = array(
    				'loan_number'=>$data['loan_code']);
    		$where=" id =".$data['id'];
    		$this->update($arr, $where);
    
    		$tranlist = explode(',',$data['indentity']);
    		$this->_name='ln_pawnshop_detail';
    		foreach ($tranlist as $i) {
    			$arr = array(
    					'is_completed'=>$data['payment_option'.$i],
    					'principle_after'=>$data['principal_after'.$i],
    					'total_interest_after'=>$data['interest_after'.$i],
    					'total_payment_after'=>$data['interest_after'.$i]+$data['principal_after'.$i],
    			);
    			$where="id = ".$data['fundid_'.$i];
    			$this->update($arr, $where);
    		}
    		$db->commit();
    		return 1;
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
    function getLoanLevelByClient($client_id,$type){
    	$db  = $this->getAdapter();
    	$sql = " SELECT level FROM `ln_pawnshop` WHERE status =1 AND customer_id = $client_id ORDER BY level DESC LIMIT 1 ";
    	$level  = $db->fetchOne($sql);
    	return ($level+1);
    }
    function getLoanRescheduleLevel($client_id,$type){
    	$db  = $this->getAdapter();
    	$sql = " SELECT level FROM `ln_pawnshop_reschedule` WHERE status =1 AND pawnshop_id = $client_id ORDER BY level DESC LIMIT 1 ";
    	$level  = $db->fetchOne($sql);
    	$level = empty($level)?0:$level;
    	return ($level+1);
    }
    function getPawnIdByBranch($branch_id){
    	$db=$this->getAdapter();
    	$sql="select
    	id,
    	CONCAT(
    	(select product_en from ln_pawnshopproduct as psp where psp.id = ps.product_id),
    	'(',loan_number,')',
    	'(',(select name_kh from ln_clientsaving where client_id = customer_id),')'
    	) as name
    	from
    	ln_pawnshop	as ps
    	where
    	is_sold=0
    	and is_dach = 0
    	and branch_id = $branch_id ";
    	return $db->fetchAll($sql);
    }
}