<?php

class Loan_Model_DbTable_DbLoandisburse extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_loan';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authloan');
    	return $session_user->user_id;
    	 
    }
    
    public function getClient($type){
    	$this->_name='ln_client';
    	$sql ="SELECT
    	client_number ,
    	name_en,
    	client_id
    	FROM $this->_name lm WHERE status=1 AND name_en !='' AND is_group=0  "; ///just and is_group =0;
    	$db = $this->getAdapter();
    	$rows = $db->fetchAll($sql);
    	$options=array(0=>'------Select------');
    	if(!empty($rows))foreach($rows AS $row){
    		if($type==1){
    			$lable = $row['client_number'];
    		}elseif($type==2){
    			$lable = $row['name_en'];
    		}
    		$options[$row['client_id']]=$lable;
    	}
    	return $options;
    }
    public function getAllIndividuleLoan($search,$reschedule =null){
    	$from_date =(empty($search['start_date']))? '1': "lg.date_release >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "lg.date_release <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	
    	$db = $this->getAdapter();
    	$sql=" SELECT l.id,
    	 (SELECT branch_namekh FROM `ln_branch` WHERE br_id =l.branch_id LIMIT 1) AS branch,
    	l.loan_number,
    	(SELECT name_kh FROM `ln_client` WHERE client_id = l.customer_id LIMIT 1) AS client_name_kh,
  		CONCAT((SELECT symbol FROM `ln_currency` WHERE id =l.currency_type)  ,l.loan_amount) AS total_capital ,
  		l.interest_rate AS interest_rate,
  	    (SELECT payment_nameen FROM `ln_payment_method` WHERE id = l.payment_method LIMIT 1) AS payment_method,
  	    CONCAT( l.total_duration,' ',(SELECT name_en FROM `ln_view` WHERE TYPE = 14 AND key_code =l.pay_term )),
        (SELECT zone_name FROM `ln_zone` WHERE zone_id=l.zone_id LIMIT 1) AS zone_name,
        (SELECT co_firstname FROM `ln_co` WHERE co_id =l.co_id LIMIT 1) AS co_name,
         l.status  FROM `ln_loan` AS l
				WHERE l.status=1 AND loan_type =1 ";
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
    		$s_where[] = "REPLACE(lm.loan_number,' ','')  	LIKE '%{$s_search}%'";
    		$s_where[] = "REPLACE(lm.total_capital,' ','')  LIKE '%{$s_search}%'";
    		$s_where[] = "REPLACE(lm.interest_rate,' ','')  LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	if($search['status']>-1){
    		$where.= " AND l.status = ".$search['status'];
    	}
    	if(($search['client_name'])>0){
    		$where.= " AND l.customer_id=".$search['client_name'];
    	}
    	if(($search['repayment_method'])>0){
    		$where.= " AND l.payment_method = ".$search['repayment_method'];
    	}
    	if(($search['branch_id'])>0){
    		$where.= " AND l.branch_id = ".$search['branch_id'];
    	}
    	if(($search['co_id'])>0){
    		$where.= " AND l.co_id=".$search['co_id'];
    	}
    	if(($search['currency_type'])>0){
    		$where.= " AND l.currency_type=".$search['currency_type'];
    	}
    	if(($search['pay_every'])>0){
    		$where.= " AND l.pay_term=".$search['pay_every'];
    	}
    	if($reschedule!=null){
    		$where.= ' AND l.is_reschedule=1 ';
    	}else{
    		$where.= ' AND l.is_reschedule !=2 ';
    	}
    		
    	$order = " ORDER BY l.id DESC";
    	$db = $this->getAdapter();    
    	return $db->fetchAll($sql.$where.$order); 	
    }
function getTranLoanByIdWithBranch($id,$loan_type =1,$is_newschedule=null){//group id
    	$sql = " SELECT 
    		l.id,l.branch_id,l.level,l.co_id,l.zone_id,l.pay_term,l.date_release,l.status,
    		l.total_duration,l.first_payment,l.time_collect,l.holiday,l.date_line,
    		l.other_fee,l.holiday,l.collect_typeterm,l.currency_type,l.graice_period,
    		l.loan_number,l.interest_rate,l.amount_collect_principal,
    		l.customer_id,(l.admin_fee) AS admin_fee,
	    	(SELECT name_kh FROM `ln_client` WHERE client_id = l.customer_id LIMIT 1) AS client_name_kh,
	  		(SELECT name_en FROM `ln_client` WHERE client_id = l.customer_id LIMIT 1) AS client_name_en,
	  		(l.loan_amount) AS total_capital,l.interest_rate,l.payment_method,
	    	l.time_collect,
	    	l.zone_id,
	    	(SELECT co_firstname FROM `ln_co` WHERE co_id =l.co_id LIMIT 1) AS co_enname,
	    	l.status AS str ,l.status FROM `ln_loan` AS l
			WHERE l.loan_type = $loan_type AND l.status=1 AND is_badloan=0 ";
    	if($is_newschedule!=null){
    		$where=" AND l.is_reschedule = 2 ";
    	}
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
    function round_up($value, $places)
    {
    	$mult = pow(10, abs($places));
    	return $places < 0 ?
    	ceil($value / $mult) * $mult :
    	ceil($value * $mult) / $mult;
    }
    function round_up_currency($curr_id, $value,$places=-2){
    	if ($curr_id==1){
    		$value_array = explode(".", $value);
    		if(!empty($value_array[1])){//last array
    			return $this->round_up($value, $places);
    		}else{
    			return $value;
    		}
    	}
    	else{
    		return round($value,2);
    	}
    }
    function calCulateIRR($total_loan_amount,$loan_amount,$term,$curr){
    	$array =array();//array(-1000,107,103,103,103,103,103,103,103,103,103,103,103);
    	for($j=0; $j<= $term;$j++){
    		if($j==0){
    			$array[]=-$loan_amount;
    		}elseif($j==1){
    			$fixed_principal = round($total_loan_amount/$term,0, PHP_ROUND_HALF_DOWN);
    			$post_fiexed = $total_loan_amount/$term-$fixed_principal;
    			$total_add_first = $this->round_up_currency($curr,$post_fiexed*$term);
    			 
    			$array[]=($total_add_first+$fixed_principal);
    		}else{
    			$array[]=round($total_loan_amount/$term,0, PHP_ROUND_HALF_DOWN);
    		}
    
    	}
    	$array = array_values($array);
    	return Loan_Model_DbTable_DbIRRFunction::IRR($array);
    }
    function updatePaymentStatus($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$this->_name='ln_loan';
    		$arr = array(
    				'loan_number'=>$data['loan_code']);
    		$where=" member_id =".$data['id'];
    		$this->update($arr, $where);
    		
    		$tranlist = explode(',',$data['indentity']);
			$this->_name='ln_loanmember_funddetail';
    		foreach ($tranlist as $i) {
    			$arr = array(
    					'is_completed'=>$data['payment_option'.$i],
    					'date_payment'=>$data['datepayment_'.$i],
    					'principal_permonth'=>$data['principal_'.$i],
    					'principle_after'=>$data['principal_after'.$i],
    					'total_interest'=>$data['interest_'.$i],
    					'total_interest_after'=>$data['interest_after'.$i],
    					'total_payment'=>$data['interest_after'.$i]+$data['principal_after'.$i]+$data['saving_amount'.$i],
    					'total_payment_after'=>$data['interest_after'.$i]+$data['principal_after'.$i]+$data['saving_amount'.$i],
    					'saving_amount'=>$data['saving_amount'.$i]
    					);
    			$where="id = ".$data['fundid_'.$i];
//     			echo $data['fundid_'.$i];
//     			exit();
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
    
    public function addNewLoanIL($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$dbtable = new Application_Model_DbTable_DbGlobal();
    		$loan_number = $dbtable->getLoanNumber($data);
    		$datagroup = array(
    				'branch_id'=>$data['branch_id'],
    				'loan_number'=>$loan_number,
    				'level'=>$data['level'],
    				'customer_id'=>$data['member'],
    				'co_id'=>$data['co_id'],
    				'zone_id'=>$data['zone'],
    				'date_release'=>$data['release_date'],
    				'date_line'=>$data['date_line'],
    				'create_date'=>date("Y-m-d"),
    				'total_duration'=>$data['period'],
    				'first_payment'=>$data['first_payment'],
    				'pay_term'=>$data['pay_every'],
    				'payment_method'=>$data['repayment_method'],
    				'loan_type'=>1,
    				'for_loantype'=>$data['loan_type'],
    				'time_collect'=>$data['time'],
    				'holiday'=>$data['every_payamount'],
//     				'is_renew'=>0,
    				'collect_typeterm'=>$data['collect_termtype'],
    				'user_id'=>$this->getUserId(),
    				'currency_type'=>$data['currency_type'],
    				'loan_amount'=>$data['total_amount'],//$data[''],
    				'admin_fee'=>$data['loan_fee'],
    				'other_fee'=>$data['other_fee'],
    				'interest_rate'=>$data['interest_rate'],
    				'status'=>1,
    				'is_completed'=>0,
    				'graice_period'=>$data['graice_pariod'],
    				'amount_collect_principal'=>$data['amount_collect'],
    				'payment_fixed'=>$data['amount_collect_pricipal']
    				//'deposit'=>$data['deposit'],
    				//'pay_after'=>$data['pay_late'],// not use
    				);
    			$loan_id = $this->insert($datagroup);//add group loan
    			
    			
    			$remain_principal = $data['total_amount'];
    			$remain_principalirr = $data['total_amount'];
    			$pri_permonthirr=0;
    			$next_payment = $data['first_payment'];
    			$start_date = $data['release_date'];//loan release;
    			$from_date =  $data['release_date'];
				
    			$old_remain_principal = 0;
    			$old_pri_permonth = 0;
    			$old_interest_paymonth = 0;
    			$old_amount_day = 0;
    			$amount_collect = 1;
    			$ispay_principal=2;//for payment type = 5;
    			$is_subremain = 2;
    			$curr_type = $data['currency_type'];
    			$penelize_service = 39500;
    			$panelize_descreas =1500;
    			//$saving=empty($data['deposit'])?0:$data['deposit'];
    			//for IRR method
    			if($data['repayment_method']==6 OR $data['repayment_method']==7){
    				$term_install = $data['period']/$data['amount_collect'];
    				$loan_amount = $data['total_amount'];
    				$total_loan_amount = $loan_amount+($loan_amount*($data['interest_rate']*$data['amount_collect'])/100*$term_install);
    				$irr_interest = $this->calCulateIRR($total_loan_amount,$loan_amount,($term_install),$curr_type);
    			}
    			//end IRR method
    			$this->_name='ln_loan_detail';
    			$borrow_term = $dbtable->getSubDaysByPaymentTerm($data['pay_every'],null);//return amount day for payterm
    			$amount_borrow_term = $borrow_term*$data['period'];//amount of borrow
    			
    			$fund_term = $dbtable->getSubDaysByPaymentTerm($data['collect_termtype'],null);//return amount day for payterm
    			$amount_fund_term = $fund_term*$data['amount_collect'];
    			$loop_payment = ($amount_borrow_term)/($amount_fund_term);
    			$payment_method = $data['repayment_method'];
	            $str_next = $dbtable->getNextDateById($data['collect_termtype'],$data['amount_collect']);//for next,day,week,month;
    			
				for($i=1;$i<=$loop_payment;$i++){
    				$amount_collect = $data['amount_collect'];
    				
    				if($payment_method==1){//decline//completed
//     					$pri_permonth = ($data['total_amount']/($data['period']-$data['graice_pariod'])*$amount_collect);
    					$pri_permonth = $data['total_amount']/(($amount_borrow_term-($data['graice_pariod']*$borrow_term))/$amount_fund_term);
    					$pri_permonth = $this->round_up_currency($curr_type, $pri_permonth);
    					if($i*$amount_collect<=$data['graice_pariod']){//check here//for graice period
    						$pri_permonth = 0;
    					}
    					if($i!=1){
    						if($data['graice_pariod']!=0){//if collect =1 not other check
    							if($i*$amount_collect>$data['graice_pariod']+$amount_collect){//not wright
    								$remain_principal = $remain_principal-$pri_permonth;
    							}else{
    							}
    						}else{
    							$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា}
    						}
    						if($i==$loop_payment){//check condition here//for end of record only
    							$pri_permonth = $data['total_amount']-$pri_permonth*($i-(($data['graice_pariod']/$amount_collect)+1));//code error here
    						}
    						$start_date = $next_payment;
    						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
    						
							$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    						$interest_paymonth = $remain_principal*($data['interest_rate']/100/$borrow_term)*$amount_day;
    						$penelize_service = $penelize_service-$panelize_descreas;
    						if($i>11){
    							$penelize_service=0;
    						}
    					}else{
    						$next_payment = $data['first_payment'];
    						$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
    						$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    						$interest_paymonth = $remain_principal*($data['interest_rate']/100/$borrow_term)*$amount_day;
    					}
    				}elseif($payment_method==2){//baloon
    					$pri_permonth=0;
    					if(($i*$amount_fund_term)==$amount_borrow_term){//check here
    						$pri_permonth = ($curr_type==1)?round($data['total_amount'],-2):$data['total_amount'];
    						$pri_permonth =$this->round_up_currency($curr_type, $pri_permonth);
    						$remain_principal = $pri_permonth;//$remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    					}
    					if($i!=1){
    						$start_date = $next_payment;
    						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
    						$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    						
    						$penelize_service = $penelize_service-$panelize_descreas;
    						if($i>11){
    							$penelize_service=0;
    						}
    					}else{
    						$next_payment = $data['first_payment'];
    						$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
    						$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    					}
    					$interest_paymonth = $data['total_amount']*($data['interest_rate']/100/$borrow_term)*$amount_day;
    					
    				}elseif($payment_method==3){//fixed rate
    					$pri_permonth = ($data['total_amount']/($amount_borrow_term/$amount_fund_term));
    					$pri_permonth =$this->round_up_currency($curr_type,$pri_permonth);
    					if($i!=1){
    						$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    						if($i==$loop_payment){//for end of record only
    							$pri_permonth = $remain_principal;
    						}
    						$start_date = $next_payment;
    						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
    					
    						$penelize_service = $penelize_service-$panelize_descreas;
    						if($i>11){
    							$penelize_service=0;
    						}
    					}else{
    						$next_payment = $data['first_payment'];
    						$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
    					}
    					    $amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    					    $interest_paymonth = $data['total_amount']*($data['interest_rate']/100/$borrow_term)*$amount_fund_term;//$amount_day;
    					    
    				}elseif($payment_method==4){//fixed payment full last period yes
    					$total_day=0;
    					if($i!=1){
    						$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    						$start_date = $next_payment;
    						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
    						
    						$penelize_service = $penelize_service-$panelize_descreas;
    						if($i>11){
    							$penelize_service=0;
    						}
    					}else{
    						$next_payment = $data['first_payment'];
    						$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
    					}
    					$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    					$total_day = $amount_day;
    					if($data['collect_termtype']==1){
    						$amount_day=$data['amount_collect'];
    					}
    					$interest_paymonth = $remain_principal*($data['interest_rate']/100/$borrow_term)*$amount_day;
    					$interest_paymonth = $this->round_up_currency($curr_type, $interest_paymonth);
    					if($data['collect_termtype']==1){
    						$amount_day= $total_day ;
    					}
    					$pri_permonth = $data['amount_collect_pricipal']-$interest_paymonth;
    					if($i==$loop_payment){//for end of record only
    						$pri_permonth = $remain_principal;
    					}
    				}elseif($payment_method==5){//semi baloon//ok
    					if($i!=1){
    						$ispay_principal++;
    						$is_subremain++;
    						$pri_permonth=0;
								if(($is_subremain-1)==$data['amount_collect_pricipal']){
    								$pri_permonth = ($data['total_amount']/$data['period'])*$data['amount_collect_pricipal'];
    								$pri_permonth = $this->round_up_currency($curr_type, $pri_permonth);
    								$is_subremain=1;
    							}
    							if(($ispay_principal-1)==$data['amount_collect_pricipal']+1){
    								$remain_principal = $remain_principal-($data['total_amount']/$data['period'])*$data['amount_collect_pricipal'];
    								$ispay_principal=2;
    							}
    							if($i==$loop_payment){//check condition here//for end of record only
    								$pri_permonth = $remain_principal;
    							}
    							$start_date = $next_payment;
    							$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
    							$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    							$interest_paymonth = $remain_principal*($data['interest_rate']/100/$borrow_term)*$amount_day;
    					
    							$penelize_service = $penelize_service-$panelize_descreas;
    							if($i>11){
    								$penelize_service=0;
    							}
    					}else{
    						$pri_permonth = 0;//check if get pri first too much change;
    						$next_payment = $data['first_payment'];
    						$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
    						$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    						$interest_paymonth = $data['total_amount']*($data['interest_rate']/100/$borrow_term)*$amount_day;
    					}
    				}elseif($payment_method==7){
	    				if($i!=1){
	    					$start_date = $next_payment;
	    					$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
	    					$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
	    					$remain_principalirr = $remain_principalirr-$pri_permonthirr;
	    					$interest_paymonth = $this->round_up_currency($curr_type,$remain_principalirr*$irr_interest);
	    					$fixed_principal = intval($total_loan_amount/$term_install);
	    					$fixed_principal= $this->round_up_currency($curr_type,$fixed_principal);
	    					$pri_permonthirr = $fixed_principal-$interest_paymonth;
	    					
	    					if($i==$loop_payment){//for end of record only
	    						   $pri_permonthirr = $remain_principalirr;
	    						   $fixed_principal = intval($total_loan_amount/$term_install);
	    						   $fixed_principal= $this->round_up_currency($curr_type,$fixed_principal);
	    						   $interest_paymonth = $fixed_principal-$remain_principalirr;
	    					}
	    					
	    					$penelize_service = $penelize_service-$panelize_descreas;
	    					if($i>11){
	    						$penelize_service=0;
	    					}
	    				}else{
	    					$fixed_principal = intval($total_loan_amount/$term_install);//fixed 'ex: 100.70=>100
	    					$fixed_principal= $this->round_up_currency($curr_type,$fixed_principal);
	    					$post_fiexed = $total_loan_amount/$term_install-$fixed_principal;
	    					$total_payment_first = $this->round_up_currency($curr_type,$post_fiexed*$term_install);
	    					$pri_permonthirr = $fixed_principal+$total_payment_first;
	    					$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
	    					$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
	    					$interest_paymonth = $this->round_up_currency($curr_type,$loan_amount*($irr_interest));
	    					$pri_permonthirr = ($fixed_principal+$total_payment_first)-$interest_paymonth;
	    				}
	    				
	    				$pri_permonth = $data['total_amount']/(($amount_borrow_term)/$amount_fund_term);
	    				$pri_permonth = $this->round_up_currency($curr_type, $pri_permonth);
	    				if($i!=1){
	    					$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា}
	    					if($i==$loop_payment){//check condition here//for end of record only
	    						$pri_permonth = $remain_principal;//$data['total_amount']-$pri_permonth*$i;//code error here
	    					}
	    				}
    				}///
    				elseif($payment_method==8){
    					$pri_permonth = ($data['total_amount']/($amount_borrow_term/$amount_fund_term));
    					$pri_permonth =$this->round_up_currency($curr_type,$pri_permonth);
    					if($i!=1){
    						if($data['period']<=15){//if period<18;ok
    							$ispay_principal++;
    							$is_subremain++;
    							if(($is_subremain-1)==2){
    								$is_subremain=1;
    							}
    							if(($ispay_principal-1)==2+1){
    								$ispay_principal=2;
    								$interest_rate = $interest_rate-0.1;
    							}
    								
    						}elseif($data['period']<=20){
    							if($i>5){//top record
    								$interest_rate=$data['interest_rate']-0.1;
    							}
    							if($loop_payment-$i<5){//5 last record
    								$interest_rate=$data['interest_rate']-0.2;
    							}
    						}else{//>20week or = 24
    				
    							if($i>5){//top record
    								$interest_rate=$data['interest_rate']-0.1;
    							}
    							if($loop_payment-$i<5){//5 last record
    								$interest_rate=$data['interest_rate']-0.2;
    							}
    						}
    						$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    						if($i==$loop_payment){//for end of record only
    							$pri_permonth = $remain_principal;
    						}
    						$start_date = $next_payment;
    						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
    							
    						$penelize_service = $penelize_service-$panelize_descreas;
    						if($i>11){
    							$penelize_service=0;
    						}
    					}else{
    						$interest_rate = $data['interest_rate'];
    						$next_payment = $data['first_payment'];
    						$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
    					}
    					$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    					$amount_peroid = $dbtable->getAmountDayByTerm($data['pay_every']);
    					$interest_paymonth = $data['total_amount']*($interest_rate/100/$borrow_term)*$amount_peroid;
    					$interest_paymonth = $this->round_up_currency($curr_type, $interest_paymonth);
    				}
    				
    				else{//    fixed payment with fixed rate
    					if($i!=1){
    						$start_date = $next_payment;
    						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
    						$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    						$remain_principal = $remain_principal-$pri_permonth;
    						$interest_paymonth = $this->round_up_currency($curr_type,$remain_principal*$irr_interest);
    						//$fixed_principal = round($total_loan_amount/$term_install,0, PHP_ROUND_HALF_DOWN);
    						$fixed_principal = intval($total_loan_amount/$term_install);
    						$pri_permonth = $fixed_principal-$interest_paymonth;
    						
    						if($i==$loop_payment){//for end of record only
    							$pri_permonth = $remain_principal;
    							//$fixed_principal = round($total_loan_amount/$term_install,0, PHP_ROUND_HALF_DOWN);
    							$fixed_principal = intval($total_loan_amount/$term_install);
    							$fixed_principal= $this->round_up_currency($curr_type,$fixed_principal);
    							$interest_paymonth = $fixed_principal-$remain_principal;
    						}
    						$penelize_service = $penelize_service-$panelize_descreas;
    						if($i>11){
    							$penelize_service=0;
    						}
    							
    					}else{
    						$fixed_principal = intval($total_loan_amount/$term_install);//fixed 'ex: 100.70=>100
    						$fixed_principal= $this->round_up_currency($curr_type,$fixed_principal);
    						$post_fiexed = $total_loan_amount/$term_install-$fixed_principal;
    						$total_payment_first = $this->round_up_currency($curr_type,$post_fiexed*$term_install);
    						$pri_permonth = $fixed_principal+$total_payment_first;
    						$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
    						$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    						$interest_paymonth = $this->round_up_currency($curr_type,$loan_amount*($irr_interest));
    						$pri_permonth = ($fixed_principal+$total_payment_first)-$interest_paymonth;
    					}	   
    			    }
    				$old_remain_principal =$old_remain_principal+$remain_principal;
    				$old_pri_permonth = $old_pri_permonth+$pri_permonth;
    				$old_interest_paymonth = $this->round_up_currency($curr_type,($old_interest_paymonth+$interest_paymonth));
    				$old_amount_day =$old_amount_day+ $amount_day;
    				if($i==$loop_payment){
    					$this->_name='ln_loan';
	    				$datagroup = array('date_line'=>$next_payment);
	    				$where =" id= ".$loan_id;
	    				
	    				$this->update($datagroup, $where);//add group loan
	    				$this->_name='ln_loan_detail';
    				}
    				if($data['amount_collect']==$amount_collect){
    					$datapayment = array(
    							'loan_id'=>$loan_id,
    							'outstanding'=>$remain_principal,//good
    							'outstanding_after'=>$remain_principal,//good
    							'principal_permonth'=> $old_pri_permonth,//good
    							'principle_after'=> $old_pri_permonth,//good
    							'total_interest'=>$old_interest_paymonth,//good
    							'total_interest_after'=>$old_interest_paymonth,//good
    							'total_payment'=>$old_pri_permonth+$old_interest_paymonth,//good
    							'total_payment_after'=>$old_pri_permonth+$old_interest_paymonth,//good
    							//'penelize_service'=>$penelize_service,
//     							'saving_amount'=>$saving,
    							'date_payment'=>$next_payment,//good
    							'is_completed'=>0,
    							'status'=>1,
    							'amount_day'=>$old_amount_day,
    							'collect_by'=>$data['co_id'],
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
	     					if($data['collect_termtype']!=1){//for loan day
	     						$next_payment = $dbtable->checkDefaultDate($str_next, $start_date, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
	     					}	
	     				}
    				}else{
    				}
    				$amount_collect++;
    			}
    			if(($amount_borrow_term)%($amount_fund_term)!=0){///end for record odd number only
    				$start_date = $next_payment;//$dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount']);
    				$next_payment = $dbtable->checkFirstHoliday($data['date_line'],$data['every_payamount']);
    				$amount_day = $amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    				if($payment_method==1){
    					$pri_permonth = $remain_principal-$pri_permonth; // $pri_permonth*($amount_day/$amount_fund_term);//check it if khmer currency
    					$interest_paymonth = $pri_permonth*($data['interest_rate']/100/$borrow_term)*$amount_day;
    					$interest_paymonth = $this->round_up_currency($curr_type,$interest_paymonth);
    				}elseif($payment_method==2){
    					$pri_permonth = $this->round_up_currency($curr_type, $pri_permonth);
    					$remain_principal = $pri_permonth;//$remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    					$interest_paymonth = $pri_permonth*($data['interest_rate']/100/$borrow_term)*$amount_day;
    					$interest_paymonth = $this->round_up_currency($curr_type,$interest_paymonth);
    				}elseif($payment_method==3){
    					$pri_permonth = $remain_principal-$pri_permonth;
    					$interest_paymonth = $data['total_amount']*($data['interest_rate']/100/$borrow_term)*$amount_day;
//     					$interest_paymonth = ($data['total_amount']*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($day_perterm/$day_perterm));
    					$interest_paymonth = $this->round_up_currency($curr_type,$interest_paymonth);
//     					$pri_permonth = $this->round_up_currency($curr_type,$pri_permonth);
    				}elseif($payment_method==4){
    					$interest_paymonth = $data['total_amount']*($data['interest_rate']/100/$borrow_term)*$amount_day;
//     					$interest_paymonth = ($data['total_amount']*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($amount_day/$day_perterm));
    					$interest_paymonth = $this->round_up_currency($curr_type,$interest_paymonth);
    					$pri_permonth = $remain_principal-$pri_permonth;
    				}elseif($payment_method==5){
    					$pri_permonth = $remain_principal;
    					$interest_paymonth = $remain_principal*($data['interest_rate']/100/$borrow_term)*$amount_day;
    					$interest_paymonth = $this->round_up_currency($curr_type,$interest_paymonth);
    				}elseif($payment_method==6){
    					$interest_paymonth = $this->round_up_currency($curr_type,$remain_principal*$irr_interest);
    					$fixed_principal = round($total_loan_amount/$term_install,0, PHP_ROUND_HALF_DOWN);
    					$pri_permonth = $remain_principal;
    				}
    				
    				$datapayment = array(
    						'loan_id'=>$loan_id,
    						'outstanding'=>$pri_permonth,//good
    						'outstanding_after'=>$remain_principal,//good
    						'principal_permonth'=> $pri_permonth,//good
    						'principle_after'=> $old_pri_permonth,//good
    						'total_interest'=>$interest_paymonth,//good
    						'total_interest_after'=>$interest_paymonth,//good
    						'total_payment'=>$interest_paymonth+$pri_permonth,//good
    						'total_payment_after'=>$old_pri_permonth+$old_interest_paymonth,//good
    						'date_payment'=>$next_payment,//good
    						'is_completed'=>0,
    						'status'=>1,
    						'amount_day'=>$amount_day,
    						'collect_by'=>$data['co_id'],
    						//'penelize_service'=>$penelize_service,
//     						'saving_amount'=>$saving,
    						'installment_amount'=>$i
    				);
    				$this->insert($datapayment);
    				
    				$this->_name='ln_loan';
    				$datagroup = array('date_line'=>$next_payment);
    				$where =" id= ".$loan_id;
    				$this->update($datagroup, $where);//add group loan
    			}
    		$db->commit();
    		return 1;
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Form_FrmMessage::message("INSERT_FAIL");
    		echo $e->getMessage();exit();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    function updateLoanById($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		if($data['status_using']==0){
    			$arr_update = array(
    					'status'=>0
    			);
    			$where = ' status = 1 AND id = '.$data['id'];
    			$this->update($arr_update, $where);
    			
    			$this->_name='ln_loan_detail';
    			$where = ' loan_id = '.$data['id'];
    			$this->update($arr_update, $where);
    			$db->commit();
    			return 1;
    		}
    		$datagroup = array(
    				'branch_id'=>$data['branch_id'],
    				'level'=>$data['level'],
    				'customer_id'=>$data['member'],
    				'co_id'=>$data['co_id'],
    				'zone_id'=>$data['zone'],
    				'date_release'=>$data['release_date'],
    				'date_line'=>$data['date_line'],
    				'create_date'=>date("Y-m-d"),
    				'total_duration'=>$data['period'],
    				'first_payment'=>$data['first_payment'],
    				'pay_term'=>$data['pay_every'],
    				'payment_method'=>$data['repayment_method'],
    				'loan_type'=>1,
    				'for_loantype'=>$data['loan_type'],
    				'time_collect'=>$data['time'],
    				'holiday'=>$data['every_payamount'],
    				'collect_typeterm'=>$data['collect_termtype'],
    				'user_id'=>$this->getUserId(),
    				'currency_type'=>$data['currency_type'],
    				'loan_amount'=>$data['total_amount'],//$data[''],
    				'admin_fee'=>$data['loan_fee'],
    				'other_fee'=>$data['other_fee'],
    				'interest_rate'=>$data['interest_rate'],
    				'status'=>1,
    				'is_completed'=>0,
    				'graice_period'=>$data['graice_pariod'],
    				'amount_collect_principal'=>$data['amount_collect'],
    				'payment_fixed'=>$data['amount_collect_pricipal']
    		);
    		$loan_id =  $data['id'];
    		$where = $db->quoteInto('id=?', $loan_id);
    		$this->update($datagroup, $where);
    		unset($datagroup);
    		
    		$this->_name='ln_loan_detail';
    		$where="loan_id =".$loan_id;
    		$this->delete($where);
//     		echo $loan_id;exit();
    		
    		$dbtable = new Application_Model_DbTable_DbGlobal();
    		$remain_principal = $data['total_amount'];
    			$remain_principalirr = $data['total_amount'];
    			$pri_permonthirr=0;
    			$next_payment = $data['first_payment'];
    			$start_date = $data['release_date'];//loan release;
    			$from_date =  $data['release_date'];
				
    			$old_remain_principal = 0;
    			$old_pri_permonth = 0;
    			$old_interest_paymonth = 0;
    			$old_amount_day = 0;
    			$amount_collect = 1;
    			$ispay_principal=2;//for payment type = 5;
    			$is_subremain = 2;
    			$curr_type = $data['currency_type'];
    			$penelize_service = 39500;
    			$panelize_descreas =1500;
    			//$saving=empty($data['deposit'])?0:$data['deposit'];
    			//for IRR method
    			if($data['repayment_method']==6 OR $data['repayment_method']==7){
    				$term_install = $data['period']/$data['amount_collect'];
    				$loan_amount = $data['total_amount'];
    				$total_loan_amount = $loan_amount+($loan_amount*($data['interest_rate']*$data['amount_collect'])/100*$term_install);
    				$irr_interest = $this->calCulateIRR($total_loan_amount,$loan_amount,($term_install),$curr_type);
    			}
    			//end IRR method
    			$this->_name='ln_loan_detail';
    			$borrow_term = $dbtable->getSubDaysByPaymentTerm($data['pay_every'],null);//return amount day for payterm
    			$amount_borrow_term = $borrow_term*$data['period'];//amount of borrow
    			
    			$fund_term = $dbtable->getSubDaysByPaymentTerm($data['collect_termtype'],null);//return amount day for payterm
    			$amount_fund_term = $fund_term*$data['amount_collect'];
    			$loop_payment = ($amount_borrow_term)/($amount_fund_term);
    			$payment_method = $data['repayment_method'];
	            $str_next = $dbtable->getNextDateById($data['collect_termtype'],$data['amount_collect']);//for next,day,week,month;
    			
				for($i=1;$i<=$loop_payment;$i++){
    				$amount_collect = $data['amount_collect'];
    				
    				if($payment_method==1){//decline//completed
//     					$pri_permonth = ($data['total_amount']/($data['period']-$data['graice_pariod'])*$amount_collect);
    					$pri_permonth = $data['total_amount']/(($amount_borrow_term-($data['graice_pariod']*$borrow_term))/$amount_fund_term);
    					$pri_permonth = $this->round_up_currency($curr_type, $pri_permonth);
    					if($i*$amount_collect<=$data['graice_pariod']){//check here//for graice period
    						$pri_permonth = 0;
    					}
    					if($i!=1){
    						if($data['graice_pariod']!=0){//if collect =1 not other check
    							if($i*$amount_collect>$data['graice_pariod']+$amount_collect){//not wright
    								$remain_principal = $remain_principal-$pri_permonth;
    							}else{
    							}
    						}else{
    							$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា}
    						}
    						if($i==$loop_payment){//check condition here//for end of record only
    							$pri_permonth = $data['total_amount']-$pri_permonth*($i-(($data['graice_pariod']/$amount_collect)+1));//code error here
    						}
    						$start_date = $next_payment;
    						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
    						
							$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    						$interest_paymonth = $remain_principal*($data['interest_rate']/100/$borrow_term)*$amount_day;
    						$penelize_service = $penelize_service-$panelize_descreas;
    						if($i>11){
    							$penelize_service=0;
    						}
    					}else{
    						$next_payment = $data['first_payment'];
    						$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
    						$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    						$interest_paymonth = $remain_principal*($data['interest_rate']/100/$borrow_term)*$amount_day;
    					}
    				}elseif($payment_method==2){//baloon
    					$pri_permonth=0;
    					if(($i*$amount_fund_term)==$amount_borrow_term){//check here
    						$pri_permonth = ($curr_type==1)?round($data['total_amount'],-2):$data['total_amount'];
    						$pri_permonth =$this->round_up_currency($curr_type, $pri_permonth);
    						$remain_principal = $pri_permonth;//$remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    					}
    					if($i!=1){
    						$start_date = $next_payment;
    						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
    						$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    						
    						$penelize_service = $penelize_service-$panelize_descreas;
    						if($i>11){
    							$penelize_service=0;
    						}
    					}else{
    						$next_payment = $data['first_payment'];
    						$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
    						$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    					}
    					$interest_paymonth = $data['total_amount']*($data['interest_rate']/100/$borrow_term)*$amount_day;
    					
    				}elseif($payment_method==3){//fixed rate
    					$pri_permonth = ($data['total_amount']/($amount_borrow_term/$amount_fund_term));
    					$pri_permonth =$this->round_up_currency($curr_type,$pri_permonth);
    					if($i!=1){
    						$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    						if($i==$loop_payment){//for end of record only
    							$pri_permonth = $remain_principal;
    						}
    						$start_date = $next_payment;
    						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
    					
    						$penelize_service = $penelize_service-$panelize_descreas;
    						if($i>11){
    							$penelize_service=0;
    						}
    					}else{
    						$next_payment = $data['first_payment'];
    						$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
    					}
    					    $amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    					    $interest_paymonth = $data['total_amount']*($data['interest_rate']/100/$borrow_term)*$amount_fund_term;//$amount_day;
    					    
    				}elseif($payment_method==4){//fixed payment full last period yes
    					$total_day=0;
    					if($i!=1){
    						$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    						$start_date = $next_payment;
    						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
    						
    						$penelize_service = $penelize_service-$panelize_descreas;
    						if($i>11){
    							$penelize_service=0;
    						}
    					}else{
    						$next_payment = $data['first_payment'];
    						$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
    					}
    					$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    					$total_day = $amount_day;
    					if($data['collect_termtype']==1){
    						$amount_day=$data['amount_collect'];
    					}
    					$interest_paymonth = $remain_principal*($data['interest_rate']/100/$borrow_term)*$amount_day;
    					$interest_paymonth = $this->round_up_currency($curr_type, $interest_paymonth);
    					if($data['collect_termtype']==1){
    						$amount_day= $total_day ;
    					}
    					$pri_permonth = $data['amount_collect_pricipal']-$interest_paymonth;
    					if($i==$loop_payment){//for end of record only
    						$pri_permonth = $remain_principal;
    					}
    				}elseif($payment_method==5){//semi baloon//ok
    					if($i!=1){
    						$ispay_principal++;
    						$is_subremain++;
    						$pri_permonth=0;
								if(($is_subremain-1)==$data['amount_collect_pricipal']){
    								$pri_permonth = ($data['total_amount']/$data['period'])*$data['amount_collect_pricipal'];
    								$pri_permonth = $this->round_up_currency($curr_type, $pri_permonth);
    								$is_subremain=1;
    							}
    							if(($ispay_principal-1)==$data['amount_collect_pricipal']+1){
    								$remain_principal = $remain_principal-($data['total_amount']/$data['period'])*$data['amount_collect_pricipal'];
    								$ispay_principal=2;
    							}
    							if($i==$loop_payment){//check condition here//for end of record only
    								$pri_permonth = $remain_principal;
    							}
    							$start_date = $next_payment;
    							$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
    							$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    							$interest_paymonth = $remain_principal*($data['interest_rate']/100/$borrow_term)*$amount_day;
    					
    							$penelize_service = $penelize_service-$panelize_descreas;
    							if($i>11){
    								$penelize_service=0;
    							}
    					}else{
    						$pri_permonth = 0;//check if get pri first too much change;
    						$next_payment = $data['first_payment'];
    						$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
    						$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    						$interest_paymonth = $data['total_amount']*($data['interest_rate']/100/$borrow_term)*$amount_day;
    					}
    				}elseif($payment_method==7){
	    				if($i!=1){
	    					$start_date = $next_payment;
	    					$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
	    					$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
	    					$remain_principalirr = $remain_principalirr-$pri_permonthirr;
	    					$interest_paymonth = $this->round_up_currency($curr_type,$remain_principalirr*$irr_interest);
	    					$fixed_principal = intval($total_loan_amount/$term_install);
	    					$fixed_principal= $this->round_up_currency($curr_type,$fixed_principal);
	    					$pri_permonthirr = $fixed_principal-$interest_paymonth;
	    					
	    					if($i==$loop_payment){//for end of record only
	    						   $pri_permonthirr = $remain_principalirr;
	    						   $fixed_principal = intval($total_loan_amount/$term_install);
	    						   $fixed_principal= $this->round_up_currency($curr_type,$fixed_principal);
	    						   $interest_paymonth = $fixed_principal-$remain_principalirr;
	    					}
	    					
	    					$penelize_service = $penelize_service-$panelize_descreas;
	    					if($i>11){
	    						$penelize_service=0;
	    					}
	    				}else{
	    					$fixed_principal = intval($total_loan_amount/$term_install);//fixed 'ex: 100.70=>100
	    					$fixed_principal= $this->round_up_currency($curr_type,$fixed_principal);
	    					$post_fiexed = $total_loan_amount/$term_install-$fixed_principal;
	    					$total_payment_first = $this->round_up_currency($curr_type,$post_fiexed*$term_install);
	    					$pri_permonthirr = $fixed_principal+$total_payment_first;
	    					$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
	    					$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
	    					$interest_paymonth = $this->round_up_currency($curr_type,$loan_amount*($irr_interest));
	    					$pri_permonthirr = ($fixed_principal+$total_payment_first)-$interest_paymonth;
	    				}
	    				
	    				$pri_permonth = $data['total_amount']/(($amount_borrow_term)/$amount_fund_term);
	    				$pri_permonth = $this->round_up_currency($curr_type, $pri_permonth);
	    				if($i!=1){
	    					$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា}
	    					if($i==$loop_payment){//check condition here//for end of record only
	    						$pri_permonth = $remain_principal;//$data['total_amount']-$pri_permonth*$i;//code error here
	    					}
	    				}
    				}///
    				elseif($payment_method==8){
    					$pri_permonth = ($data['total_amount']/($amount_borrow_term/$amount_fund_term));
    					$pri_permonth =$this->round_up_currency($curr_type,$pri_permonth);
    					if($i!=1){
    						if($data['period']<=15){//if period<18;ok
    							$ispay_principal++;
    							$is_subremain++;
    							if(($is_subremain-1)==2){
    								$is_subremain=1;
    							}
    							if(($ispay_principal-1)==2+1){
    								$ispay_principal=2;
    								$interest_rate = $interest_rate-0.1;
    							}
    								
    						}elseif($data['period']<=20){
    							if($i>5){//top record
    								$interest_rate=$data['interest_rate']-0.1;
    							}
    							if($loop_payment-$i<5){//5 last record
    								$interest_rate=$data['interest_rate']-0.2;
    							}
    						}else{//>20week or = 24
    				
    							if($i>5){//top record
    								$interest_rate=$data['interest_rate']-0.1;
    							}
    							if($loop_payment-$i<5){//5 last record
    								$interest_rate=$data['interest_rate']-0.2;
    							}
    						}
    						$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    						if($i==$loop_payment){//for end of record only
    							$pri_permonth = $remain_principal;
    						}
    						$start_date = $next_payment;
    						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
    							
    						$penelize_service = $penelize_service-$panelize_descreas;
    						if($i>11){
    							$penelize_service=0;
    						}
    					}else{
    						$interest_rate = $data['interest_rate'];
    						$next_payment = $data['first_payment'];
    						$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
    					}
    					$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    					$amount_peroid = $dbtable->getAmountDayByTerm($data['pay_every']);
    					$interest_paymonth = $data['total_amount']*($interest_rate/100/$borrow_term)*$amount_peroid;
    					$interest_paymonth = $this->round_up_currency($curr_type, $interest_paymonth);
    				}
    				
    				else{//    fixed payment with fixed rate
    					if($i!=1){
    						$start_date = $next_payment;
    						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
    						$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    						$remain_principal = $remain_principal-$pri_permonth;
    						$interest_paymonth = $this->round_up_currency($curr_type,$remain_principal*$irr_interest);
    						//$fixed_principal = round($total_loan_amount/$term_install,0, PHP_ROUND_HALF_DOWN);
    						$fixed_principal = intval($total_loan_amount/$term_install);
    						$pri_permonth = $fixed_principal-$interest_paymonth;
    						
    						if($i==$loop_payment){//for end of record only
    							$pri_permonth = $remain_principal;
    							//$fixed_principal = round($total_loan_amount/$term_install,0, PHP_ROUND_HALF_DOWN);
    							$fixed_principal = intval($total_loan_amount/$term_install);
    							$fixed_principal= $this->round_up_currency($curr_type,$fixed_principal);
    							$interest_paymonth = $fixed_principal-$remain_principal;
    						}
    						$penelize_service = $penelize_service-$panelize_descreas;
    						if($i>11){
    							$penelize_service=0;
    						}
    							
    					}else{
    						$fixed_principal = intval($total_loan_amount/$term_install);//fixed 'ex: 100.70=>100
    						$fixed_principal= $this->round_up_currency($curr_type,$fixed_principal);
    						$post_fiexed = $total_loan_amount/$term_install-$fixed_principal;
    						$total_payment_first = $this->round_up_currency($curr_type,$post_fiexed*$term_install);
    						$pri_permonth = $fixed_principal+$total_payment_first;
    						$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
    						$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    						$interest_paymonth = $this->round_up_currency($curr_type,$loan_amount*($irr_interest));
    						$pri_permonth = ($fixed_principal+$total_payment_first)-$interest_paymonth;
    					}	   
    			    }
    				$old_remain_principal =$old_remain_principal+$remain_principal;
    				$old_pri_permonth = $old_pri_permonth+$pri_permonth;
    				$old_interest_paymonth = $this->round_up_currency($curr_type,($old_interest_paymonth+$interest_paymonth));
    				$old_amount_day =$old_amount_day+ $amount_day;
    				if($i==$loop_payment){
    					$this->_name='ln_loan';
	    				$datagroup = array('date_line'=>$next_payment);
	    				$where =" id= ".$loan_id;
	    				
	    				$this->update($datagroup, $where);//add group loan
	    				$this->_name='ln_loan_detail';
    				}
    				if($data['amount_collect']==$amount_collect){
    					$datapayment = array(
    							'loan_id'=>$loan_id,
    							'outstanding'=>$remain_principal,//good
    							'outstanding_after'=>$remain_principal,//good
    							'principal_permonth'=> $old_pri_permonth,//good
    							'principle_after'=> $old_pri_permonth,//good
    							'total_interest'=>$old_interest_paymonth,//good
    							'total_interest_after'=>$old_interest_paymonth,//good
    							'total_payment'=>$old_pri_permonth+$old_interest_paymonth,//good
    							'total_payment_after'=>$old_pri_permonth+$old_interest_paymonth,//good
    							//'penelize_service'=>$penelize_service,
//     							'saving_amount'=>$saving,
    							'date_payment'=>$next_payment,//good
    							'is_completed'=>0,
    							'status'=>1,
    							'amount_day'=>$old_amount_day,
    							'collect_by'=>$data['co_id'],
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
	     					if($data['collect_termtype']!=1){//for loan day
	     						$next_payment = $dbtable->checkDefaultDate($str_next, $start_date, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
	     					}	
	     				}
    				}else{
    				}
    				$amount_collect++;
    			}
    			if(($amount_borrow_term)%($amount_fund_term)!=0){///end for record odd number only
    				$start_date = $next_payment;//$dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount']);
    				$next_payment = $dbtable->checkFirstHoliday($data['date_line'],$data['every_payamount']);
    				$amount_day = $amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    				if($payment_method==1){
    					$pri_permonth = $remain_principal-$pri_permonth; // $pri_permonth*($amount_day/$amount_fund_term);//check it if khmer currency
    					$interest_paymonth = $pri_permonth*($data['interest_rate']/100/$borrow_term)*$amount_day;
    					$interest_paymonth = $this->round_up_currency($curr_type,$interest_paymonth);
    				}elseif($payment_method==2){
    					$pri_permonth = $this->round_up_currency($curr_type, $pri_permonth);
    					$remain_principal = $pri_permonth;//$remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    					$interest_paymonth = $pri_permonth*($data['interest_rate']/100/$borrow_term)*$amount_day;
    					$interest_paymonth = $this->round_up_currency($curr_type,$interest_paymonth);
    				}elseif($payment_method==3){
    					$pri_permonth = $remain_principal-$pri_permonth;
    					$interest_paymonth = $data['total_amount']*($data['interest_rate']/100/$borrow_term)*$amount_day;
//     					$interest_paymonth = ($data['total_amount']*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($day_perterm/$day_perterm));
    					$interest_paymonth = $this->round_up_currency($curr_type,$interest_paymonth);
//     					$pri_permonth = $this->round_up_currency($curr_type,$pri_permonth);
    				}elseif($payment_method==4){
    					$interest_paymonth = $data['total_amount']*($data['interest_rate']/100/$borrow_term)*$amount_day;
//     					$interest_paymonth = ($data['total_amount']*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($amount_day/$day_perterm));
    					$interest_paymonth = $this->round_up_currency($curr_type,$interest_paymonth);
    					$pri_permonth = $remain_principal-$pri_permonth;
    				}elseif($payment_method==5){
    					$pri_permonth = $remain_principal;
    					$interest_paymonth = $remain_principal*($data['interest_rate']/100/$borrow_term)*$amount_day;
    					$interest_paymonth = $this->round_up_currency($curr_type,$interest_paymonth);
    				}elseif($payment_method==6){
    					$interest_paymonth = $this->round_up_currency($curr_type,$remain_principal*$irr_interest);
    					$fixed_principal = round($total_loan_amount/$term_install,0, PHP_ROUND_HALF_DOWN);
    					$pri_permonth = $remain_principal;
    				}
    				
    				$datapayment = array(
    						'loan_id'=>$loan_id,
    						'outstanding'=>$pri_permonth,//good
    						'outstanding_after'=>$remain_principal,//good
    						'principal_permonth'=> $pri_permonth,//good
    						'principle_after'=> $old_pri_permonth,//good
    						'total_interest'=>$interest_paymonth,//good
    						'total_interest_after'=>$interest_paymonth,//good
    						'total_payment'=>$interest_paymonth+$pri_permonth,//good
    						'total_payment_after'=>$old_pri_permonth+$old_interest_paymonth,//good
    						'date_payment'=>$next_payment,//good
    						'is_completed'=>0,
    						'status'=>1,
    						'amount_day'=>$amount_day,
    						'collect_by'=>$data['co_id'],
    						//'penelize_service'=>$penelize_service,
//     						'saving_amount'=>$saving,
    						'installment_amount'=>$i
    				);
    				$this->insert($datapayment);
    				
    				$this->_name='ln_loan';
    				$datagroup = array('date_line'=>$next_payment);
    				$where =" id= ".$loan_id;
    				$this->update($datagroup, $where);//add group loan
    			}
    		$db->commit();
    		return 1;
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Form_FrmMessage::message("INSERT_FAIL");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
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
function getLoanLevelByClient($client_id,$type){
    	$db  = $this->getAdapter();
    	if($type==1){
    		$sql = " SELECT COUNT(id) FROM `ln_loan` WHERE status =1 AND customer_id = $client_id LIMIT 1 ";
    	}else{
    		$sql = "SELECT COUNT(l.id) FROM `ln_loan` AS l WHERE l.status =1 AND
    		 l.customer_id = $client_id AND l.loan_type=2 LIMIT 1";
    	} 
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
    	$sql ="SELECT cd.`date_payment` FROM `ln_client_receipt_money_detail` AS cd,`ln_client_receipt_money` AS c WHERE c.`id` = cd.`crm_id` AND c.`loan_number`='$loanNumber' ORDER BY cd.`id` DESC";
    	//return $sql;
    	return $db->fetchOne($sql);
    }
  
}