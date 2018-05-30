<?php

class Pawnshop_Model_DbTable_DbReschedule extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_pawnshop';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authloan');
    	return $session_user->user_id;
    	 
    }
//     public function getAllPawnshop($search,$reschedule =null){
//     	$from_date =(empty($search['start_date']))? '1': " date_release >= '".$search['start_date']." 00:00:00'";
//     	$to_date = (empty($search['end_date']))? '1': " date_release <= '".$search['end_date']." 23:59:59'";
//     	$where = " AND ".$from_date." AND ".$to_date;
    	
//     	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
//     	$dach = $tr->translate("DACH_PRODUCT");
//     	$receipt = $tr->translate("PAYMENT_RECEIPT");
//     	$db = $this->getAdapter();
//     	$sql = " SELECT id,
// 	    	(SELECT branch_namekh FROM `ln_branch` WHERE br_id =branch_id LIMIT 1) AS branch,
// 	    	loan_number,
// 	    		(SELECT name_kh FROM `ln_clientsaving` WHERE client_id = ln_pawnshop.customer_id LIMIT 1) AS client_name_kh,
// 	    		receipt_num,CONCAT(release_amount,
// 	    		(SELECT symbol FROM `ln_currency` WHERE id =ln_pawnshop.currency_type LIMIT 1)) AS currency_type,
// 	    		CONCAT(total_duration,(SELECT name_en FROM `ln_view` WHERE TYPE = 14 AND key_code = term_type )) term_type,
// 				interest_rate,
// 				(SELECT product_kh FROM `ln_pawnshopproduct` WHERE id=ln_pawnshop.product_id limit 1) as product_name,
// 				date_release,date_line,'".$dach."' ,
// 	    		'$receipt','Click Here',
//     			(SELECT first_name FROM `rms_users` WHERE id=user_id LIMIT 1) As user_name,
//     			status
//     		 FROM `ln_pawnshop` WHERE 1 ";
//     	if(!empty($search['adv_search'])){
//     		$s_where = array();
//     		$s_search = addslashes(trim($search['adv_search']));
//     		$s_where[] = " receipt_num LIKE '%{$s_search}%'";
//     		$s_where[] = " release_amount LIKE '%{$s_search}%'";
//     		$s_where[] = " interest_rate LIKE '%{$s_search}%'";
//     		$where .=' AND ('.implode(' OR ',$s_where).')';
//     	}

//     	if(($search['members'])>0){
//     		$where.= " AND customer_id=".$search['members'];
//     	}

//     	if(($search['branch_id'])>0){
//     		$where.= " AND branch_id = ".$search['branch_id'];
//     	}
//     	if(($search['currency_type'])>0){
//     		$where.= " AND currency_type=".$search['currency_type'];
//     	}
//     	if(($search['product_id'])>0){
//     		$where.= " AND product_id=".$search['product_id'];
//     	}
//  	   $order=" ORDER BY id DESC";
//     	$db = $this->getAdapter();    
//     	return $db->fetchAll($sql.$where.$order);
//     }
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
    		$arr_update = array(
    				'is_reschedule'=>1
    		);
    		$where = ' id = '.$loan_id;
    		$this->_name="ln_pawnshop";
    		$this->update($arr_update, $where);
    		
//     		$datagroup = array(
//     				'branch_id'=>$data['branch_id'],
//     				'pawnshop_id'=>$loan_id,
//     				'loan_number'=>$data['loan_code'],
//     				'level'=>$data['level'],
//     				'customer_id'=>$data['member'],
//     				'release_amount'=>$data['total_amount'],
//     				'date_release'=>$data['release_date'],
//     				'date_line'=>$data['date_line'],
//     				'create_date'=>date("Y-m-d"),
//     				'total_duration'=>$data['period'],
//     				'first_payment'=>$data['first_payment'],
//     				'payment_method'=>1,
//     				'holiday'=>2,
//     				'user_id'=>$this->getUserId(),
//     				'currency_type'=>$data['currency_type'],
//     				'release_amount'=>$data['total_amount'],//$data[''],
//     				'interest_rate'=>$data['interest_rate'],
//     				'status'=>1,
//     				'is_completed'=>0,
//     				'product_id'=>$data['product_id'],
//     				'est_amount'=>$data['estimatevalue'],
//     				'product_description'=>$data['description'],
// //     				'receipt_num'=>$data['receipt_num'],
    				
//     		);
//     		$this->_name = 'ln_pawnshop';
//     		$this->insert($datagroup);//add group loan
    		
    		
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
    		$str_next=" +1 month";
    		$start_date = $data['release_date'];
    		$from_date =  $data['release_date'];
    		$borrow_term=30;//=1month
    		$dbtable = new Application_Model_DbTable_DbGlobal();
    		
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
    				$next_payment = $dbtable->checkDefaultDate($str_next, $start_date, 1,2,$data['first_payment']);
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