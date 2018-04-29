<?php

class Installment_Model_DbTable_DbReschedule extends Zend_Db_Table_Abstract
{
protected $_name = 'ln_ins_sales_install';
public function getUserId(){
	$session_user=new Zend_Session_Namespace('authloan');
	return $session_user->user_id;
}

function round_up($value, $places)
{
	$mult = pow(10, abs($places));
	return $places < 0 ?
	ceil($value / $mult) * $mult :
	ceil($value * $mult) / $mult;
}
function round_up_currency($curr_id, $value,$places=-2){
	if ($curr_id==1){//for riel
		$value_array = explode(".", $value);
		return $this->round_up($value, $places);
	}
	else{
		return round($value,2);
	}
}
// public function getAllSale($search,$reschedule =null){
// 	$from_date =(empty($search['start_date']))? '1': "l.date_sold >= '".$search['start_date']." 00:00:00'";
// 	$to_date = (empty($search['end_date']))? '1': "l.date_sold <= '".$search['end_date']." 23:59:59'";
// 	$where = " AND ".$from_date." AND ".$to_date;
	 
// 		$db = $this->getAdapter();
// 		$sql=" SELECT l.id,
// 			(SELECT branch_namekh FROM `ln_branch` WHERE br_id =l.branch_id LIMIT 1) AS branch,
// 			l.sale_no,
// 			(SELECT name_kh FROM `ln_ins_client` WHERE client_id = l.customer_id LIMIT 1) AS client_name_kh,
// 			(SELECT c.name FROM `ln_ins_category` AS  c WHERE c.id=p.`cate_id` LIMIT 1) AS cat_name,
// 			p.item_name,
// 			l.selling_price AS total_capital,
// 			l.date_sold,
// 			l.invoice_no,
// 			(SELECT name_en FROM `ln_view` WHERE TYPE = 29 AND key_code =l.selling_type LIMIT 1) AS selling_type,
// 			(SELECT payment_nameen FROM `ln_payment_method` WHERE id = l.payment_method LIMIT 1) AS payment_method,
// 			l.duration,l.status  
// 			FROM 
// 			`ln_ins_sales_install` AS l,
// 			`ln_ins_product` AS p 
// 			WHERE 
// 		    	l.product_id = p.id ";
// 		if(!empty($search['adv_search'])){
// 			$s_where = array();
// 			$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
// 			$s_where[] = "REPLACE(l.sale_no,' ','')  	LIKE '%{$s_search}%'";
// 			$s_where[] = "REPLACE(l.invoice_no,' ','')  LIKE '%{$s_search}%'";
			
// 			$s_where[] = "REPLACE(l.power,' ','')  LIKE '%{$s_search}%'";
// 			$s_where[] = "REPLACE(l.engine,' ','')  LIKE '%{$s_search}%'";
// 			$s_where[] = "REPLACE(l.frame,' ','')  LIKE '%{$s_search}%'";
// 			$s_where[] = "REPLACE(l.sell_remark,' ','')  LIKE '%{$s_search}%'";
// 			$where .=' AND ('.implode(' OR ',$s_where).')';
// 		}
// 		if($search['status']>-1){
// 			$where.= " AND l.status = ".$search['status'];
// 		}
// 		if(($search['member'])>0){
// 			$where.= " AND l.customer_id=".$search['member'];
// 		}
// 		if(($search['repayment_method'])>0){
// 			$where.= " AND l.payment_method = ".$search['repayment_method'];
// 		}
// 		if(($search['branch_id'])>0){
// 			$where.= " AND l.branch_id = ".$search['branch_id'];
// 		}
// 		if(($search['category_id'])>0){
// 			$where.= " AND l.cate_id=".$search['category_id'];
// 		}
// 		if(($search['selling_type'])>0){
// 			$where.= " AND l.selling_type=".$search['selling_type'];
// 		}
// 		$order = " ORDER BY l.id DESC";
// 		$db = $this->getAdapter();
// 		return $db->fetchAll($sql.$where.$order);
// }
public function addRescheduleInstallment($data){
	$db = $this->getAdapter();
	$db->beginTransaction();
	try{
		$loan_id = $data['id'];
		$dbp = new Installment_Model_DbTable_DbInstallmentPayment();
		$row = $dbp->getSaleinstallbyid($loan_id);
		
		if( !empty($row) AND $data['extra_loan']>0){
			$this->_name='ln_ins_sales_install';
			$arr = array(
					'selling_price'=>$row['selling_price']+$data['extra_loan'],
					'balance'=>$row['balance']+$data['extra_loan'],
				);
			$where = "id =".$loan_id;
			$this->update($arr, $where);
		}
		
		$this->_name="ln_ins_sales_installdetail";
		$where = " status=1 AND is_completed=0 AND sale_id =".$loan_id;
		$this->delete($where);
		
		$sql="SELECT COUNT(id) FROM ln_ins_sales_installdetail WHERE status=1 AND sale_id= ".$loan_id;
		$start_id = $db->fetchOne($sql);
		
		if($data['paid_amount']>0){//សម្គាល់រំលស់ប្រាក់ដើម
			$datapayment = array(
					'sale_id'=>$loan_id,
					'outstanding'=>$data['balance'],//good
					'outstanding_after'=>$data['balance'],//good
					'principal_permonth'=> $data['paid_amount'],//good
					'principle_after'=> $data['paid_amount'],//good
					'total_interest'=>0,
					'total_interest_after'=>0,
					'total_payment'=>$data['paid_amount'],//good
					'total_payment_after'=>$data['paid_amount'],//good
					'date_payment'=>$data['paid_date'],//good
					'is_completed'=>1,
					'status'=>0,
					'amount_day'=>0,
					'installment_amount'=>$start_id+1
			);
			$this->insert($datapayment);
		}
		
		if($data['extra_loan']>0){//សម្គាល់ខ្ចីថែម
			$datapayment = array(
					'sale_id'=>$loan_id,
					'outstanding'=>$data['balance'],//good
					'outstanding_after'=>$data['balance'],//good
					'principal_permonth'=> $data['paid_amount'],//good
					'principle_after'=> $data['paid_amount'],//good
					'total_interest'=>0,
					'total_interest_after'=>0,
					'total_payment'=>$data['paid_amount'],//good
					'total_payment_after'=>$data['paid_amount'],//good
					'date_payment'=>$data['paid_date'],//good
					'noted'=>$data['noted'],
					'is_completed'=>1,
					'status'=>0,
					'amount_day'=>0,
					'installment_amount'=>$start_id+1
			);
			$this->insert($datapayment);
		}
		
		$data['total_amount'] = $data['balance'];
		$remain_principal = $data['total_amount'];
		$data['amount_collect']=1;
		$next_payment = $data['first_payment'];
		$start_date = $data['date_sold'];//loan release;
		$from_date =  $data['date_sold'];
		$old_remain_principal = 0;
		$old_pri_permonth = 0;
		$old_interest_paymonth = 0;
		$old_amount_day = 0;
		$amount_collect = 1;
		$curr_type = 2;
		$payment_method = $data['repayment_method'];
		$loop_payment = $data['duration'];
		$borrow_term = 30;
		
		$dbtable = new Application_Model_DbTable_DbGlobal();
		$str_next = $dbtable->getNextDateById(3,$data['amount_collect']);//for month;
				
			for($i=1;$i<=$loop_payment;$i++){
				$amount_collect = $data['amount_collect'];
				if($payment_method==1){
					$pri_permonth = $data['total_amount']/($data['duration']);
					$pri_permonth = $this->round_up_currency($curr_type, $pri_permonth);
					if($i!=1){
						$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា}
						if($i==$loop_payment){
							$pri_permonth = $data['total_amount']-($pri_permonth*($i-1));
						}
						$start_date = $next_payment;
						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, 1,2,$data['first_payment']);

						$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
						$interest_paymonth = $remain_principal*($data['interest_rate']/100/$borrow_term)*$amount_day;//here
					}else{
						$next_payment = $data['first_payment'];
						$next_payment = $dbtable->checkFirstHoliday($next_payment,2);
						$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
						$interest_paymonth = $remain_principal*($data['interest_rate']/100/$borrow_term)*$amount_day;
					}
				}elseif($payment_method==2){//baloon
					$pri_permonth=0;
					if($i==$loop_payment){//check here
						$pri_permonth =$data['total_amount'];
						$remain_principal = $pri_permonth;
					}
					if($i!=1){
						$start_date = $next_payment;
						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, 1,2,$data['first_payment']);
						$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
					}else{
						$next_payment = $data['first_payment'];
						$next_payment = $dbtable->checkFirstHoliday($next_payment,2);
						$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
					}
					$interest_paymonth = $data['total_amount']*($data['interest_rate']/100/$borrow_term)*$amount_day;
				}elseif($payment_method==4){//fixed payment full last period yes
					$total_day=0;
					if($i!=1){
						$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
						$start_date = $next_payment;
						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, 1,2,$data['first_payment']);
					}else{
						$next_payment = $data['first_payment'];
						$next_payment = $dbtable->checkFirstHoliday($next_payment,2);
					}
					$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
					$total_day = $amount_day;
						
					$interest_paymonth = $remain_principal*($data['interest_rate']/100/$borrow_term)*$amount_day;
					$interest_paymonth = $this->round_up_currency($curr_type, $interest_paymonth);

					$pri_permonth = $data['fixed_payment']-$interest_paymonth;
					if($i==$loop_payment){//for end of record only
						$pri_permonth = $remain_principal;
					}
				}
				$old_remain_principal =$old_remain_principal+$remain_principal;
				$old_pri_permonth = $old_pri_permonth+$pri_permonth;
				$old_interest_paymonth = $this->round_up_currency($curr_type,($old_interest_paymonth+$interest_paymonth));
				$old_amount_day =$old_amount_day+ $amount_day;

				if($i==$loop_payment){
					$this->_name='ln_ins_sales_install';
					$datagroup = array('date_line'=>$next_payment);
					$where =" id= ".$loan_id;
					$this->update($datagroup, $where);//add group loan
				}
				$this->_name='ln_ins_sales_installdetail';
				$datapayment = array(
						'sale_id'=>$loan_id,
						'outstanding'=>$remain_principal,//good
						'outstanding_after'=>$remain_principal,//good
						'principal_permonth'=> $old_pri_permonth,//good
						'principle_after'=> $old_pri_permonth,//good
						'total_interest'=>$old_interest_paymonth,//good
						'total_interest_after'=>$old_interest_paymonth,//good
						'total_payment'=>$old_pri_permonth+$old_interest_paymonth,//good
						'total_payment_after'=>$old_pri_permonth+$old_interest_paymonth,//good
						'date_payment'=>$next_payment,//good
						'is_completed'=>0,
						'status'=>1,
						'amount_day'=>$old_amount_day,
						'installment_amount'=>$start_id+$i+1
				);
				$this->insert($datapayment);
				$amount_collect=0;
				$old_remain_principal = 0;
				$old_pri_permonth = 0;
				$old_interest_paymonth = 0;
				$old_amount_day = 0;

				$from_date=$next_payment;
				if($i!=1){
					$next_payment = $dbtable->checkDefaultDate($str_next, $start_date,1,2,$data['first_payment']);
				}
				$amount_collect++;
	  		}//end loop
// 	  		echo 333;exit();
		$db->commit();
		return 1;
	}catch (Exception $e){
		$db->rollBack();
		//Application_Form_FrmMessage::message("INSERT_FAIL");
		echo $e->getMessage();exit();
		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	}
}
}