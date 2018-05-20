<?php

class Installment_Model_DbTable_DbInstallment extends Zend_Db_Table_Abstract
{
protected $_name = 'ln_ins_sales_install';
public function getUserId(){
	$session_user=new Zend_Session_Namespace('authloan');
	return $session_user->user_id;
}
// public function getClient($type){
// 	$this->_name='ln_client';
// 	$sql ="SELECT
// 	client_number ,
// 	name_en,
// 	client_id
// 	FROM $this->_name lm WHERE status=1 AND name_en !='' AND is_group=0  "; ///just and is_group =0;
// 	$db = $this->getAdapter();
// 	$rows = $db->fetchAll($sql);
// 	$options=array(0=>'------Select------');
// 	if(!empty($rows))foreach($rows AS $row){
// 		if($type==1){
// 			$lable = $row['client_number'];
// 		}elseif($type==2){
// 			$lable = $row['name_en'];
// 		}
// 		$options[$row['client_id']]=$lable;
// 	}
// 	return $options;
// }
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
public function getAllSale($search,$reschedule =null){
	$from_date =(empty($search['start_date']))? '1': "l.date_sold >= '".$search['start_date']." 00:00:00'";
	$to_date = (empty($search['end_date']))? '1': "l.date_sold <= '".$search['end_date']." 23:59:59'";
	$where = " AND ".$from_date." AND ".$to_date;
	 
		$db = $this->getAdapter();
		$sql=" SELECT l.id,
			(SELECT branch_namekh FROM `ln_branch` WHERE br_id =l.branch_id LIMIT 1) AS branch,
			l.sale_no,
			(SELECT name_kh FROM `ln_ins_client` WHERE client_id = l.customer_id LIMIT 1) AS client_name_kh,
			(SELECT c.name FROM `ln_ins_category` AS  c WHERE c.id=p.`cate_id` LIMIT 1) AS cat_name,
			p.item_name,
			l.selling_price AS total_capital,
			l.date_sold,
			l.invoice_no,
			(SELECT name_en FROM `ln_view` WHERE TYPE = 29 AND key_code =l.selling_type LIMIT 1) AS selling_type,
			(SELECT payment_nameen FROM `ln_payment_method` WHERE id = l.payment_method LIMIT 1) AS payment_method,
			l.duration,l.status  
			FROM 
			`ln_ins_sales_install` AS l,
			`ln_ins_product` AS p 
			WHERE 
		    	l.product_id = p.id ";
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
			$s_where[] = "REPLACE(l.sale_no,' ','')  	LIKE '%{$s_search}%'";
			$s_where[] = "REPLACE(l.invoice_no,' ','')  LIKE '%{$s_search}%'";
			
			$s_where[] = "REPLACE(l.power,' ','')  LIKE '%{$s_search}%'";
			$s_where[] = "REPLACE(l.engine,' ','')  LIKE '%{$s_search}%'";
			$s_where[] = "REPLACE(l.frame,' ','')  LIKE '%{$s_search}%'";
			$s_where[] = "REPLACE(l.sell_remark,' ','')  LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1){
			$where.= " AND l.status = ".$search['status'];
		}
		if(($search['member'])>0){
			$where.= " AND l.customer_id=".$search['member'];
		}
		if(($search['repayment_method'])>0){
			$where.= " AND l.payment_method = ".$search['repayment_method'];
		}
		if(($search['branch_id'])>0){
			$where.= " AND l.branch_id = ".$search['branch_id'];
		}
		if(($search['category_id'])>0){
			$where.= " AND l.cate_id=".$search['category_id'];
		}
		if(($search['selling_type'])>0){
			$where.= " AND l.selling_type=".$search['selling_type'];
		}
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission('l.branch_id');
		
		$order = " ORDER BY l.id DESC";
		$db = $this->getAdapter();
		return $db->fetchAll($sql.$where.$order);
}
public function addSaleInstallment($data){
	$db = $this->getAdapter();
	$db->beginTransaction();
	try{
		$dbtable = new Application_Model_DbTable_DbGlobal();
		$loan_number = $data['sale_no'];
		$dbpro = new Installment_Model_DbTable_DbProduct();
		$rsproduct = $dbpro->getProductById($data['product_name']);

		$datagroup = array(
				'branch_id'=>$data['branch_id'],
				'sale_no'=>$loan_number,
				'customer_id'=>$data['member'],
				'invoice_no'=>$data['invoice_no'],
				'cate_id'=>$data['category_id'],
				'product_id'=>$data['product_name'],
				'power'=>$data['power'],
				'color'=>$data['color'],
				'engine'=>$data['engine'],
				'frame'=>$data['frame'],
				'frame_no'=>$data['frame_no'],
				'cost_price'=>$rsproduct['cost_price'],
				'selling_price'=>$data['selling_price'],
				'paid'=>$data['paid'],
				'balance'=>$data['balance'],
				'sell_remark'=>$data['note'],
				'user_id'=>$this->getUserId(),
				'status'=>1,
				'is_completed'=>($data['selling_type']==1)?1:0,
				'selling_type'=>$data['selling_type'],
				'payment_method'=>$data['repayment_method'],
				'interest_rate'=>$data['interest_rate'],
				'first_payment'=>$data['first_payment'],
				'date_line'=>$data['date_line'],
				'duration'=>$data['duration'],
				'user_id'=>$this->getUserId(),
				'date_sold'=>$data['date_sold'],
				'paid_date'=>$data['paid_date'],
		);
		$this->_name='ln_ins_sales_install';
		$loan_id = $this->insert($datagroup);//add group loan
		$dbpo = new Installment_Model_DbTable_DbPurchase();
		$dbpo->updateStock($data['product_name'],$data['branch_id'],-1);
		
		
		//បង់បង្កើតតារាងទីមួយករណីបានបង់ដាច់
		if($data['paid']>0){
			$this->_name='ln_ins_sales_installdetail';
			$datapayment = array(
					'sale_id'=>$loan_id,
					'outstanding'=>$data['paid'],//good
					'outstanding_after'=>$data['paid'],//good
					'principal_permonth'=>$data['paid'],//good
					'principle_after'=> 0,//good
					'total_interest'=>0,//good
					'total_interest_after'=>0,//good
					'total_payment'=>$data['paid'],//good
					'total_payment_after'=>0,//good
					'date_payment'=>$data['date_sold'],//good
					'is_completed'=>1,
					'status'=>1,
					'amount_day'=>0,
					'installment_amount'=>1
			);
			$this->insert($datapayment);
			
			$dbc = new Installment_Model_DbTable_DbInstallmentPayment();
			$reciept_no=$dbc->getIlPaymentNumber();
			$arr_client_pay = array(
					'client_id'		=> 	$data['member'],
					'receipt_no'		=> $reciept_no,
					'branch_id'			=> $data['branch_id'],
					'date_pay'			=> $data['date_sold'],
					'date_input'		=> $data['date_sold'],
					'paid_times'        => 1,
					'loan_id'			=> $loan_id,
					'date_payment'    	=> $data['date_sold'],
					'begining_balance'	=> $data['selling_price'],
					'principal_amount'	=> $data['paid'],
					'principal_paid'	=> $data['paid'],
					'interest_amount'	=> 0,
					'interest_paid'		=> 0,
					'total_payment'		=> $data['paid'],
					'penalize_amount'	=> 0,
					'penalize_paid'     => 0,
					'recieve_amount'	=> $data['paid'],
					'total_paymentpaid'	=> $data['paid'],//check
					'return_amount'		=> 0,
					'note'				=> '',
					'status'			=> 1,
					'user_id'			=> $this->getUserId(),
					'late_day'			=> 0,
					'is_completed'		=> 1,
					'payment_option'	=> 1,
			);
			
			$this->_name = "ln_ins_receipt_money";
			$receipt_id = $this->insert($arr_client_pay);
			
			$arr_money_detail = array(
					'receipt_id'		=> $receipt_id,
					'lfd_id'			=> 1,
					'date_payment'		=> $data['date_sold'],
					'capital'			=> $data['selling_price'],
					'remain_capital'	=> $data['balance'],
					'principal_permonth'=> $data['paid'],
					'total_interest'	=> $data['paid'],
					'total_payment'		=> $data['paid'],
					'penelize_amount'	=> 0,
			);
			$db->insert("ln_ins_receipt_money_detail", $arr_money_detail);
	    } 
		if($data['selling_type']==2){
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
			$ispay_principal=2;//for payment type = 5;
			$is_subremain = 2;
			$curr_type = 2;
			
				
			$payment_method = $data['repayment_method'];
			$str_next = $dbtable->getNextDateById(3,$data['amount_collect']);//for month;
			$loop_payment = $data['duration'];
			$borrow_term = 30;
				
			for($i=1;$i<=$loop_payment;$i++){
				$amount_collect = $data['amount_collect'];
				if($payment_method==1){//decline//completed
					$pri_permonth = $data['total_amount']/($data['duration']);
					$pri_permonth = $this->round_up_currency($curr_type, $pri_permonth);
					if($i!=1){
						$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា}
						if($i==$loop_payment){//check condition here//for end of record only
							$pri_permonth = $data['total_amount']-($pri_permonth*($i-1));//code error here
						}
						$start_date = $next_payment;
						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, 1,2,$data['first_payment']);

						$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
						$interest_paymonth = $remain_principal*($data['interest_rate']/100/$borrow_term)*$amount_day;//here
						$penelize_service=0;
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
						'installment_amount'=>$i+1
				);
				$this->insert($datapayment);
				$amount_collect=0;
				$old_remain_principal = 0;
				$old_pri_permonth = 0;
				$old_interest_paymonth = 0;
				$old_amount_day = 0;

				$from_date=$next_payment;
				if($i!=1){
					//for moth
					$next_payment = $dbtable->checkDefaultDate($str_next, $start_date,1,2,$data['first_payment']);
				}
				$amount_collect++;
	  		}//end loop
		}
		$db->commit();
		return $loan_id;
	}catch (Exception $e){
		$db->rollBack();
		//Application_Form_FrmMessage::message("INSERT_FAIL");
		echo $e->getMessage();exit();
		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	}
}

function updateInstallmentById($data){
	$db = $this->getAdapter();
	$db->beginTransaction();
	try{
		$dbpo = new Installment_Model_DbTable_DbPurchase();
		if($data['status_using']==0){
			if($data['old_status']==1){
					$arr_update = array(
							'status'=>0
					);
					$where = ' status = 1 AND id = '.$data['id'];
					$this->update($arr_update, $where);
					
					$dbpo->updateStock($data['old_prodcut'],$data['branch_id'],1);
					
					$this->_name='ln_ins_sales_installdetail';
					$where = ' sale_id = '.$data['id'];
					$this->update($arr_update, $where);
					$db->commit();
					
			}
			return 1;
		}
		
		$dbtable = new Application_Model_DbTable_DbGlobal();
		$dbpro = new Installment_Model_DbTable_DbProduct();
		$rsproduct = $dbpro->getProductById($data['product_name']);
		$datagroup = array(
				'branch_id'=>$data['branch_id'],
// 				'sale_no'=>$loan_number,
				'customer_id'=>$data['member'],
				'invoice_no'=>$data['invoice_no'],
				'cate_id'=>$data['category_id'],
				'product_id'=>$data['product_name'],
				'power'=>$data['power'],
				'color'=>$data['color'],
				'engine'=>$data['engine'],
				'frame'=>$data['frame'],
				'frame_no'=>$data['frame_no'],
				'cost_price'=>$rsproduct['cost_price'],
				'selling_price'=>$data['selling_price'],
				'paid'=>$data['paid'],
				'balance'=>$data['balance'],
				'sell_remark'=>$data['note'],
				'user_id'=>$this->getUserId(),
				'status'=>1,
				'selling_type'=>$data['selling_type'],
				'is_completed'=>($data['selling_type']==1)?1:0,
				'payment_method'=>$data['repayment_method'],
				'interest_rate'=>$data['interest_rate'],
				'first_payment'=>$data['first_payment'],
				'date_line'=>$data['date_line'],
				'duration'=>$data['duration'],
				'user_id'=>$this->getUserId(),
				'date_sold'=>$data['date_sold'],
		);
		$loan_id =  $data['id'];
		$where = $db->quoteInto('id=?', $loan_id);
		$this->update($datagroup, $where);
		unset($datagroup);
		
		
		$dbpo->updateStock($data['old_prodcut'],$data['branch_id'],-1);
		$dbpo->updateStock($data['product_name'],$data['branch_id'],1);
		
		$this->_name = "ln_ins_receipt_money";
		$arr = array("status"=>1);
		$where = "loan_id = ".$loan_id;
		$receipt_id = $this->update($arr, $where);

		$this->_name='ln_ins_sales_installdetail';
		$where="sale_id =".$loan_id;
		$this->delete($where);
		
		//បង់បង្កើតតារាងទីមួយករណីបានបង់ដាច់
		if($data['paid']>0){
			$this->_name='ln_ins_sales_installdetail';
			$datapayment = array(
					'sale_id'=>$loan_id,
					'outstanding'=>$data['paid'],//good
					'outstanding_after'=>$data['paid'],//good
					'principal_permonth'=>$data['paid'],//good
					'principle_after'=> 0,//good
					'total_interest'=>0,//good
					'total_interest_after'=>0,//good
					'total_payment'=>$data['paid'],//good
					'total_payment_after'=>0,//good
					'date_payment'=>$data['date_sold'],//good
					'is_completed'=>1,
					'status'=>1,
					'amount_day'=>0,
					'installment_amount'=>1
			);
			$this->insert($datapayment);
				
			$dbc = new Installment_Model_DbTable_DbInstallmentPayment();
			$reciept_no=$dbc->getIlPaymentNumber();
			$arr_client_pay = array(
					'client_id'		=> 	$data['member'],
					'receipt_no'		=> $reciept_no,
					'branch_id'			=> $data['branch_id'],
					'date_pay'			=> $data['date_sold'],
					'date_input'		=> $data['date_sold'],
					'paid_times'        => 1,
					'loan_id'			=> $loan_id,
					'date_payment'    	=> $data['date_sold'],
					'begining_balance'	=> $data['selling_price'],
					'principal_amount'	=> $data['paid'],
					'principal_paid'	=> $data['paid'],
					'interest_amount'	=> 0,
					'interest_paid'		=> 0,
					'total_payment'		=> $data['paid'],
					'penalize_amount'	=> 0,
					'penalize_paid'     => 0,
					'recieve_amount'	=> $data['paid'],
					'total_paymentpaid'	=> $data['paid'],//check
					'return_amount'		=> 0,
					'note'				=> '',
					'status'			=> 1,
					'user_id'			=> $this->getUserId(),
					'late_day'			=> 0,
					'is_completed'		=> 1,
					'payment_option'	=> 1,
			);
				
			$this->_name = "ln_ins_receipt_money";
			$receipt_id = $this->insert($arr_client_pay);
				
			$arr_money_detail = array(
					'receipt_id'		=> $receipt_id,
					'lfd_id'			=> 1,
					'date_payment'		=> $data['date_sold'],
					'capital'			=> $data['selling_price'],
					'remain_capital'	=> $data['balance'],
					'principal_permonth'=> $data['paid'],
					'total_interest'	=> $data['paid'],
					'total_payment'		=> $data['paid'],
					'penelize_amount'	=> 0,
			);
			$db->insert("ln_ins_receipt_money_detail", $arr_money_detail);
				
		}
		if($data['selling_type']==2){
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
			$ispay_principal=2;//for payment type = 5;
			$is_subremain = 2;
			$curr_type = 2;
		
			$payment_method = $data['repayment_method'];
			$str_next = $dbtable->getNextDateById(3,$data['amount_collect']);//for month;
			$loop_payment = $data['duration'];
			$borrow_term = 30;
		
			for($i=1;$i<=$loop_payment;$i++){
				$amount_collect = $data['amount_collect'];
				if($payment_method==1){//decline//completed
					$pri_permonth = $data['total_amount']/($data['duration']);
					$pri_permonth = $this->round_up_currency($curr_type, $pri_permonth);
					if($i!=1){
						$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា}
						if($i==$loop_payment){//check condition here//for end of record only
							$pri_permonth = $data['total_amount']-($pri_permonth*($i-1));//code error here
						}
						$start_date = $next_payment;
						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, 1,2,$data['first_payment']);
		
						$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
						$interest_paymonth = $remain_principal*($data['interest_rate']/100/$borrow_term)*$amount_day;//here
						$penelize_service=0;
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
						'installment_amount'=>$i+1
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
		}
		$db->commit();
		return $loan_id;
			
	}catch (Exception $e){
		$db->rollBack();
		Application_Form_FrmMessage::message("INSERT_FAIL");
		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	}
}
public function previewschedule($data){
	$db = $this->getAdapter();
	try{
		$sql=" TRUNCATE TABLE ln_ins_testdetail ";
		$db->query($sql);
		$dbtable = new Application_Model_DbTable_DbGlobal();
		if($data['selling_type']==2){
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
			$ispay_principal=2;//for payment type = 5;
			$is_subremain = 2;
			$curr_type = 2;
			$this->_name='ln_ins_testdetail';
			
			$payment_method = $data['repayment_method'];
			$str_next = $dbtable->getNextDateById(3,$data['amount_collect']);//for month;
			$loop_payment = $data['duration'];
			$borrow_term = 30;
			
			for($i=1;$i<=$loop_payment;$i++){
				$amount_collect = $data['amount_collect'];
				if($payment_method==1){//decline//completed
					$pri_permonth = $data['total_amount']/($data['duration']);
					$pri_permonth = $this->round_up_currency($curr_type, $pri_permonth);
					if($i!=1){
							$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា}
						if($i==$loop_payment){//check condition here//for end of record only
							$pri_permonth = $data['total_amount']-($pri_permonth*($i-1));//code error here
						}
						$start_date = $next_payment;
						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, 1,2,$data['first_payment']);
				
						$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
						$interest_paymonth = $remain_principal*($data['interest_rate']/100/$borrow_term)*$amount_day;//here
						$penelize_service=0;
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
				
				$this->_name='ln_ins_testdetail';
				$datapayment = array(
					'sale_id'=>1,
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
				$next_payment = $dbtable->checkDefaultDate($str_next, $start_date,1,2,$data['first_payment']);
			}
		$amount_collect++;
	  }//end loop
	}
		$sql="SELECT s.*,s.date_payment,
		DATE_FORMAT(s.date_payment, '%d-%m-%Y') AS date_payments,
		DATE_FORMAT(s.date_payment, '%Y-%m-%d') AS date_name FROM ln_ins_testdetail as s WHERE s.sale_id=1";
		return $db->fetchAll($sql);
		
    }catch (Exception $e){
		$db->rollBack();
		echo $e->getMessage();exit();
		Application_Form_FrmMessage::message("INSERT_FAIL");
		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	}
}
function getTranLoanByIdWithBranch($id,$loan_type =1,$is_newschedule=null){//group id
	$sql = " SELECT * FROM `ln_ins_sales_install` AS l
	WHERE l.status=1  ";
// 	if($is_newschedule!=null){
// 	$where=" AND l.is_reschedule = 2 ";
// }
	$where = " AND l.id = $id ";
	$where.=" LIMIT 1 ";
	return $this->getAdapter()->fetchRow($sql.$where);
}
public function getLoanviewById($id){
	$sql = "SELECT
				l.id
				,(SELECT branch_nameen FROM `ln_branch` WHERE br_id =l.branch_id LIMIT 1) AS branch_name
				,l.level,
				(SELECT name_kh FROM `ln_view` WHERE STATUS =1 AND TYPE=24 AND key_code=l.for_loantype) AS for_loantype
				,(SELECT co_firstname FROM `ln_co` WHERE co_id =l.co_id LIMIT 1) AS co_firstname
				,(SELECT CONCAT(zone_name,'-',zone_num)AS dd FROM `ln_zone` WHERE zone_id = l.zone_id ) AS zone_name
				,(SELECT name_en FROM `ln_view` WHERE STATUS =1 AND TYPE=14 AND key_code=l.pay_term) AS pay_term
				,(SELECT name_en FROM `ln_view` WHERE STATUS =1 AND TYPE=14 AND key_code=l.collect_typeterm) AS collect_typeterm
				,l.date_release
				,l.total_duration
				,l.first_payment
				,l.time_collect
				,(SELECT name_en FROM `ln_view` WHERE STATUS =1 AND TYPE=2 AND key_code=l.holiday) AS holiday
				,l.date_line
				,l.holiday
				,(SELECT payment_nameen FROM `ln_payment_method` WHERE id =l.payment_method ) AS payment_nameen
				,(SELECT curr_nameen FROM `ln_currency` WHERE id=l.currency_type) AS currency_type
				,l.graice_period,
				l.loan_number,
				interest_rate,
				l.amount_collect_principal,
				l.customer_id,l.admin_fee,
				l.other_fee
				,(SELECT name_kh FROM `ln_client` WHERE client_id = l.customer_id LIMIT 1) AS client_name_kh,
				(SELECT name_en FROM `ln_client` WHERE client_id = l.customer_id LIMIT 1) AS client_name_en,
				(SELECT group_code FROM `ln_client` WHERE client_id = l.customer_id LIMIT 1) AS group_code,
				(SELECT client_number FROM `ln_client` WHERE client_id = l.customer_id LIMIT 1) AS client_number,
				l.loan_amount,l.payment_method,
				l.time_collect,
				l.zone_id,
				(SELECT co_firstname FROM `ln_co` WHERE co_id =l.co_id LIMIT 1) AS co_enname,
				l.status AS str ,l.status FROM `ln_loan` AS l
			WHERE  l.id = $id LIMIT 1 ";
		return $this->getAdapter()->fetchRow($sql);
}
// function getLoannumberbyCustomer($client_id){
// 		$db  = $this->getAdapter();
// 		$sql = " SELECT level
// 		FROM `ln_loan` WHERE status =1 AND customer_id = $client_id  ORDER BY level DESC LIMIT 1 ";
// 		$level  = $db->fetchOne($sql);

// 		$sql = "SELECT client_number FROM `ln_client` WHERE ln_client.client_id=$client_id  LIMIT 1 ";
// 	$client_number  = $db->fetchOne($sql);
// 	return $client_number."-".($level+1);
// }
	function getLoanPaymentByLoanNumber($data){
		$db = $this->getAdapter();
		$loan_number= $data['loan_number'];
		if($data['type']!=2){
					$where =($data['type']==1)?'loan_number = '.$loan_number:'client_id='.$loan_number;
					$sql=" SELECT *,
					(SELECT co_id FROM `ln_loan_group` WHERE g_id =
					(SELECT lm.member_id FROM `ln_loan_member` AS lm WHERE lm.member_id = member_id LIMIT 1)) AS co_id,
					(SELECT lm.client_id FROM `ln_loan_member` AS lm WHERE lm.member_id = member_id LIMIT 1) AS client_id
					,(SELECT currency_type FROM `ln_loan_member` WHERE $where LIMIT 1  ) AS curr_type
					FROM `ln_loanmember_funddetail` WHERE member_id =
					(SELECT  member_id FROM `ln_loan_member` WHERE $where AND status=1 LIMIT 1)
					AND status = 1 ";
					}elseif($data['type']==2){
					$sql=" SELECT *,
					(SELECT co_id FROM `ln_loan_group` WHERE g_id =
					(SELECT lm.member_id FROM `ln_loan_member` AS lm WHERE lm.member_id = member_id LIMIT 1)) AS co_id,
					(SELECT lm.client_id FROM `ln_loan_member` AS lm WHERE lm.member_id = member_id LIMIT 1) AS client_id
					,(SELECT currency_type FROM `ln_loan_member` WHERE $where LIMIT 1  ) AS curr_type
					FROM `ln_loanmember_funddetail` WHERE status = 1 AND member_id =
					(SELECT member_id FROM `ln_loan_member` WHERE client_id =
					(SELECT client_id FROM `ln_client` WHERE client_number = ".$loan_number." LIMIT 1) LIMIT 1) ";
		}
		return $db->fetchAll($sql);
	}
	function getLoanLevelByClient($client_id){
		$db  = $this->getAdapter();
		$sql = "SELECT count(id) FROM `ln_ins_sales_install` WHERE status =1 AND customer_id = $client_id ORDER BY id DESC LIMIT 1 ";
		$level  = $db->fetchOne($sql);
		return ($level+1);
	}			 
	public function getLoanInfo($id){//when repayment shedule
		$db=$this->getAdapter();
		$sql="SELECT  (SELECT d.outstanding_after FROM `ln_loan_detail` AS d
			WHERE  d.STATUS=1 AND d.is_completed=0 LIMIT 1)  AS total_principal
			,l.currency_type FROM `ln_loan` AS l WHERE l.customer_id=$id AND STATUS=1 AND l.is_completed=0
			";
			return $db->fetchRow($sql);
	}
	public function getAllMemberLoanById($member_id){//for get id fund detail for update
		$db = $this->getAdapter();
		$sql = "SELECT l.id ,l.customer_id,l.loan_number,
		(SELECT name_kh FROM `ln_client` WHERE client_id = l.customer_id LIMIT 1) AS client_name_kh,
		(SELECT name_en FROM `ln_client` WHERE client_id = l.customer_id LIMIT 1) AS client_name_en,
		(SELECT client_number FROM `ln_client` WHERE client_id = l.customer_id LIMIT 1) AS client_number,
		l.loan_amount AS total_capital,l.admin_fee
		FROM `ln_loan` AS l
		WHERE l.status =1 AND l.id= $member_id ";
		return $db->fetchAll($sql);
	}
	public function getLastPayDate($data){
		$loanNumber = $data['loan_numbers'];
		$db = $this->getAdapter();
		$sql ="SELECT cd.`date_payment`
		FROM `ln_client_receipt_money_detail` AS cd,
		`ln_client_receipt_money` AS c
		WHERE
		c.status=1
		AND c.`id` = cd.`crm_id`
		AND c.`loan_number`='$loanNumber' ORDER BY cd.`id` DESC";
		return $db->fetchOne($sql);
	}
}