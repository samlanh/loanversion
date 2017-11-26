<?php

class Loan_Model_DbTable_DbRepaymentSchedule extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_loan_group';
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
    public function addRepayMentSchedule($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$loan_id = $data['get_laonnumber'];
    		$arr_update = array(
    				'is_reschedule'=>1
    		);
    		$where = ' id = '.$loan_id;
    		$this->_name="ln_loan";
    		$this->update($arr_update, $where);
    		
    		$session_transfer=new Zend_Session_Namespace();
    		$session_user=new Zend_Session_Namespace('authloan');
    		$user_id = $session_user->user_id;
    		
    		$this->_name="ln_loan_detail";
    		$where = " status=1 AND is_completed=0 AND loan_id =".$loan_id;
    		$this->delete($where);
    		
    		$sql="  SELECT COUNT(id) FROM ln_loan_detail WHERE loan_id= ".$loan_id;
    		$start_id = $db->fetchOne($sql);

    		$dbtable = new Application_Model_DbTable_DbGlobal();
    		$dbsetting = new Setting_Model_DbTable_DbLabel();
    		$array = $dbsetting->getAllSystemSetting();
    		
    		$arr = array(
    				're_loan_number'=>$data['loan_code'].$array['reschedule_postfix'],
    				'loan_number'=>$data['loan_code'],
    				'reschedule_date'=>$data['release_date'],
    				're_amount'=>$data['total_amount'],
    				're_interest_rate'=>$data['interest_rate'],
    				'maturity'=>$data['date_line'],
    				're_payment_method'=>$data['repayment_method'],
    				'user_id'=>$user_id,
    		);
    		$this->_name="ln_reschedule";
    		$this->insert($arr);
    		
    		$g_id = $data['group_id'];
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
    					        //'saving_amount'=>$saving,
    							'installment_amount'=>$i
    					);
    					$this->insert($datapayment);
    		
    					$this->_name='ln_loan';
    					$datagroup = array('date_line'=>$next_payment);
    					$where =" id= ".$loan_id;
    					$this->update($datagroup, $where);//add group loan
    				}
    				$db->commit();
                
//     			$remain_principal = $data['total_amount'];
//     			$next_payment = $data['first_payment'];
//     			$start_date = $data['release_date'];//loan release;
//     			$from_date =  $data['release_date'];
    			
//     			$old_remain_principal = 0;
//     			$old_pri_permonth = 0;
//     			$old_interest_paymonth = 0;
//     			$old_amount_day = 0;
//     			$amount_collect = 1;
//     			$ispay_principal=2;//for payment type = 5;
//     			$is_subremain = 2;
//     			$curr_type = $data['currency_type'];
    			
//     			$penelize_service=0;
//     			$saving=empty($data['deposit'])?0:$data['deposit'];
    			
//     			//for IRR method
//     			if($data['repayment_method']==6){
//     				$term_install = $data['period']/$data['amount_collect'];
//     				$loan_amount = $data['total_amount'];
//     				$total_loan_amount = $loan_amount+($loan_amount*($data['interest_rate']*$data['amount_collect'])/100*$term_install);
//     				$irr_interest = $this->calCulateIRR($total_loan_amount,$loan_amount,($term_install),$curr_type);
//     			}
//     			//end IRR method
//     			$this->_name='ln_loan_detail';
    			
//     			$borrow_term = $dbtable->getSubDaysByPaymentTerm($data['pay_every'],null);//return amount day for payterm
//     			$amount_borrow_term = $borrow_term*$data['period'];//amount of borrow
    			
//     			$fund_term = $dbtable->getSubDaysByPaymentTerm($data['collect_termtype'],null);//return amount day for payterm
//     			$amount_fund_term = $fund_term*$data['amount_collect'];
    			
//     			$loop_payment = ($amount_borrow_term)/($amount_fund_term);
//     			$payment_method = $data['repayment_method'];
// 	            $str_next = $dbtable->getNextDateById($data['collect_termtype'],$data['amount_collect']);//for next,day,week,month;
    			
// 				for($i=1;$i<=$loop_payment;$i++){
//     				$amount_collect = $data['amount_collect'];
    				
//     				if($payment_method==1){//decline//completed
//     					$pri_permonth = $data['total_amount']/(($amount_borrow_term-($data['graice_pariod']*$borrow_term))/$amount_fund_term);
//     					$pri_permonth = $this->round_up_currency($curr_type, $pri_permonth);
//     					if($i*$amount_collect<=$data['graice_pariod']){//check here//for graice period
//     						$pri_permonth = 0;
//     					}
//     					if($i!=1){
//     						if($data['graice_pariod']!=0){//if collect =1 not other check
//     							if($i*$amount_collect>$data['graice_pariod']+$amount_collect){//not wright
//     								$remain_principal = $remain_principal-$pri_permonth;
//     							}else{
    								
//     							}
//     						}else{
//     							$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា}
//     						}
//     						if($i==$loop_payment){//check condition here//for end of record only
//     							$pri_permonth = $data['total_amount']-$pri_permonth*($i-(($data['graice_pariod']/$amount_collect)+1));//code error here
//     						}
//     						$start_date = $next_payment;
//     						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
// 							$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
//     						$interest_paymonth = $remain_principal*($data['interest_rate']/100/$borrow_term)*$amount_day;
//     					}else{
//     						$next_payment = $data['first_payment'];
//     						$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
//     						$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
//     						$interest_paymonth = $remain_principal*($data['interest_rate']/100/$borrow_term)*$amount_day;
//     					}
//     				}elseif($payment_method==2){//baloon
//     					$pri_permonth=0;
//     					if(($i*$amount_fund_term)==$amount_borrow_term){//check here
//     						$pri_permonth = ($curr_type==1)?round($data['total_amount'],-2):$data['total_amount'];
//     						$pri_permonth =$this->round_up_currency($curr_type, $pri_permonth);
//     						$remain_principal = $pri_permonth;//$remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
//     					}
//     					if($i!=1){
//     						$start_date = $next_payment;
//     						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
//     						$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
//     					}else{
//     						$next_payment = $data['first_payment'];
//     						$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
//     						$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
//     					}
//     					$interest_paymonth = $data['total_amount']*($data['interest_rate']/100/$borrow_term)*$amount_day;
    					
//     				}elseif($payment_method==3){//fixed rate
//     					$pri_permonth = ($data['total_amount']/($amount_borrow_term/$amount_fund_term));
//     					$pri_permonth =$this->round_up_currency($curr_type,$pri_permonth);
//     					if($i!=1){
//     						$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
//     						if($i==$loop_payment){//for end of record only
//     							$pri_permonth = $remain_principal;
//     						}
//     						$start_date = $next_payment;
//     						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
//     					}else{
//     						$next_payment = $data['first_payment'];
//     						$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
//     					}
//     					    $amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
//     					    $interest_paymonth = $data['total_amount']*($data['interest_rate']/100/$borrow_term)*$amount_day;
    						
//     				}elseif($payment_method==4){//fixed payment full last period yes
//     					$panelize_descreas =1500;
//     					if($i!=1){
//     						$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
//     						$start_date = $next_payment;
//     						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
//     						$penelize_service = $penelize_service-$panelize_descreas;
//     						if($i>11){
//     							$penelize_service=0;
//     						}
//     					}else{
//     						$next_payment = $data['first_payment'];
//     						$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
//     						$penelize_service = 39500;
//     					}
//     					$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
//     					$interest_paymonth = $remain_principal*($data['interest_rate']/100/$borrow_term)*$amount_day;
//     					$interest_paymonth = $this->round_up_currency($curr_type, $interest_paymonth);
//     					$pri_permonth = $data['amount_collect_pricipal']-$interest_paymonth;
//     					if($i==$loop_payment){//for end of record only
//     						$pri_permonth = $remain_principal;
//     					}
//     				}elseif($payment_method==5){//semi baloon//ok
//     					if($i!=1){
//     						$ispay_principal++;
//     						$is_subremain++;
//     						$pri_permonth=0;
// 								if(($is_subremain-1)==$data['amount_collect_pricipal']){
//     								$pri_permonth = ($data['total_amount']/$data['period'])*$data['amount_collect_pricipal'];
//     								$pri_permonth = $this->round_up_currency($curr_type, $pri_permonth);
//     								$is_subremain=1;
//     							}
//     							if(($ispay_principal-1)==$data['amount_collect_pricipal']+1){
//     								$remain_principal = $remain_principal-($data['total_amount']/$data['period'])*$data['amount_collect_pricipal'];
//     								$ispay_principal=2;
//     							}
//     							if($i==$loop_payment){//check condition here//for end of record only
//     								$pri_permonth = $remain_principal;
//     							}
//     							$start_date = $next_payment;
//     							$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
//     							$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
//     							$interest_paymonth = $remain_principal*($data['interest_rate']/100/$borrow_term)*$amount_day;
// //     							$interest_paymonth = ($remain_principal*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($day_perterm/$day_perterm));
//     					}else{
//     						$pri_permonth = 0;//check if get pri first too much change;
//     						$next_payment = $data['first_payment'];
//     						$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
//     						$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
//     						$interest_paymonth = $data['total_amount']*($data['interest_rate']/100/$borrow_term)*$amount_day;
// //     						$interest_paymonth = ($data['total_amount']*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($day_perterm/$day_perterm));
//     					}
//     				}elseif($payment_method==7){
// 	    				if($i!=1){
// 	    					$start_date = $next_payment;
// 	    					$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
// 	    					$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
// 	    					$remain_principalirr = $remain_principalirr-$pri_permonthirr;
// 	    					$interest_paymonth = $this->round_up_currency($curr_type,$remain_principalirr*$irr_interest);
// 	    					$fixed_principal = intval($total_loan_amount/$term_install);
// 	    					$fixed_principal= $this->round_up_currency($curr_type,$fixed_principal);
// 	    					$pri_permonthirr = $fixed_principal-$interest_paymonth;
	    					
// 	    					if($i==$loop_payment){//for end of record only
// 	    						   $pri_permonthirr = $remain_principalirr;
// 	    						   $fixed_principal = intval($total_loan_amount/$term_install);
// 	    						   $fixed_principal= $this->round_up_currency($curr_type,$fixed_principal);
// 	    						   $interest_paymonth = $fixed_principal-$remain_principalirr;
// 	    					}
	    					
// 	    				}else{
// 	    					$fixed_principal = intval($total_loan_amount/$term_install);//fixed 'ex: 100.70=>100
// 	    					$fixed_principal= $this->round_up_currency($curr_type,$fixed_principal);
// 	    					$post_fiexed = $total_loan_amount/$term_install-$fixed_principal;
// 	    					$total_payment_first = $this->round_up_currency($curr_type,$post_fiexed*$term_install);
// 	    					$pri_permonthirr = $fixed_principal+$total_payment_first;
// 	    					$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
// 	    					$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
// 	    					$interest_paymonth = $this->round_up_currency($curr_type,$loan_amount*($irr_interest));
// 	    					$pri_permonthirr = ($fixed_principal+$total_payment_first)-$interest_paymonth;
// 	    				}
	    				
// 	    				$pri_permonth = $data['total_amount']/(($amount_borrow_term)/$amount_fund_term);
// 	    				$pri_permonth = $this->round_up_currency($curr_type, $pri_permonth);
// 	    				if($i!=1){
// 	    					$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា}
// 	    					if($i==$loop_payment){//check condition here//for end of record only
// 	    						$pri_permonth = $remain_principal;//$data['total_amount']-$pri_permonth*$i;//code error here
// 	    					}
// 	    				}
	    				
//     				}///
//     				elseif($payment_method==8){
//     					$panelize_descreas =1500;
//     					$pri_permonth = ($data['total_amount']/($amount_borrow_term/$amount_fund_term));
//     					$pri_permonth =$this->round_up_currency($curr_type,$pri_permonth);
    				
//     					if($i!=1){
//     						if($data['period']<=15){//if period<18;ok
//     							$ispay_principal++;
//     							$is_subremain++;
//     							if(($is_subremain-1)==2){
//     								$is_subremain=1;
//     							}
//     							if(($ispay_principal-1)==2+1){
//     								$ispay_principal=2;
//     								$interest_rate = $interest_rate-0.1;
//     							}
    								
//     						}elseif($data['period']<=20){
//     							if($i>5){//top record
//     								$interest_rate=$data['interest_rate']-0.1;
//     							}
//     							if($loop_payment-$i<5){//5 last record
//     								$interest_rate=$data['interest_rate']-0.2;
//     							}
    				
//     						}else{//>20week or = 24
    				
//     							if($i>5){//top record
//     								$interest_rate=$data['interest_rate']-0.1;
//     							}
//     							if($loop_payment-$i<5){//5 last record
//     								$interest_rate=$data['interest_rate']-0.2;
//     							}
//     						}
//     						$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
//     						if($i==$loop_payment){//for end of record only
//     							$pri_permonth = $remain_principal;
//     						}
//     						$start_date = $next_payment;
//     						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
    							
//     						$penelize_service = $penelize_service-$panelize_descreas;
//     						if($i>11){
//     							$penelize_service=0;
//     						}
//     					}else{
//     						$penelize_service = 39500;
//     						$interest_rate = $data['interest_rate'];
    							
//     						$next_payment = $data['first_payment'];
//     						$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
//     					}
//     					$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
//     					$amount_peroid = $dbtable->getAmountDayByTerm($data['pay_every']);
//     					$interest_paymonth = $data['total_amount']*($interest_rate/100/$borrow_term)*$amount_peroid;
    				
//     				}
//     				else{//    fixed payment with fixed rate
//     					if($i!=1){
//     						$start_date = $next_payment;
//     						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
//     						$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
//     						$remain_principal = $remain_principal-$pri_permonth;
//     						$interest_paymonth = $this->round_up_currency($curr_type,$remain_principal*$irr_interest);
//     						//$fixed_principal = round($total_loan_amount/$term_install,0, PHP_ROUND_HALF_DOWN);
//     						$fixed_principal = intval($total_loan_amount/$term_install);
//     						$pri_permonth = $fixed_principal-$interest_paymonth;
    						
//     						if($i==$loop_payment){//for end of record only
//     							$pri_permonth = $remain_principal;
//     							//$fixed_principal = round($total_loan_amount/$term_install,0, PHP_ROUND_HALF_DOWN);
//     							$fixed_principal = intval($total_loan_amount/$term_install);
//     							$fixed_principal= $this->round_up_currency($curr_type,$fixed_principal);
//     							$interest_paymonth = $fixed_principal-$remain_principal;
//     						}
    							
//     					}else{
//     						$fixed_principal = intval($total_loan_amount/$term_install);//fixed 'ex: 100.70=>100
//     						//$fixed_principal = round($total_loan_amount/$term_install,0, PHP_ROUND_HALF_DOWN);//fixed
//     						$fixed_principal= $this->round_up_currency($curr_type,$fixed_principal);
//     						$post_fiexed = $total_loan_amount/$term_install-$fixed_principal;
//     						$total_payment_first = $this->round_up_currency($curr_type,$post_fiexed*$term_install);
//     						$pri_permonth = $fixed_principal+$total_payment_first;
//     						$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
//     						$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
//     						$interest_paymonth = $this->round_up_currency($curr_type,$loan_amount*($irr_interest));
//     						$pri_permonth = ($fixed_principal+$total_payment_first)-$interest_paymonth;
//     					}	   
//     			    }
//     				$old_remain_principal =$old_remain_principal+$remain_principal;
//     				$old_pri_permonth = $old_pri_permonth+$pri_permonth;
//     				$old_interest_paymonth = $this->round_up_currency($curr_type,($old_interest_paymonth+$interest_paymonth));
//     				$old_amount_day =$old_amount_day+ $amount_day;
    				
//     				if($i==$loop_payment){
//     					$this->_name='ln_loan_group';
//     					$datagroup = array('date_line'=>$next_payment);
//     					$where =" g_id= ".$g_id;
//     					$this->update($datagroup, $where);//add group loan
//     					$this->_name='ln_loanmember_funddetail';
//     				}
    				
//     				if($data['amount_collect']==$amount_collect){
    					
//     					$datapayment = array(
//     							'member_id'=>$member_id,
//     							'total_principal'=>$remain_principal,//good
//     							'principal_permonth'=> $old_pri_permonth,//good
//     							'principle_after'=> $old_pri_permonth,//good
//     							'total_interest'=>$old_interest_paymonth,//good
//     							'total_interest_after'=>$old_interest_paymonth,//good
//     							'total_payment'=>$old_pri_permonth+$old_interest_paymonth+$saving,//good
//     							'total_payment_after'=>$old_pri_permonth+$old_interest_paymonth+$saving,//good
//     							'date_payment'=>$next_payment,//good
//     							'is_completed'=>0,
//     							'branch_id'=>$data['branch_id'],
//     							'status'=>1,
//     							'amount_day'=>$old_amount_day,
//     							'collect_by'=>$data['co_id'],
//     							'installment_amount'=>$i+$start_id,
//     							'penelize_service'=>$penelize_service,
//     							'saving_amount'=>$saving,
//     					);
//     					$this->insert($datapayment);
//     					$amount_collect=0;
//     					$old_remain_principal = 0;
//     					$old_pri_permonth = 0;
//     					$old_interest_paymonth = 0;
//     					$old_amount_day = 0;
	    				
//     					$from_date=$next_payment;
// 	     				if($i!=1){
// 	    					$next_payment = $dbtable->checkDefaultDate($str_next, $start_date, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
// 	     				}
//     				}else{
//     				}
//     				$amount_collect++;
//     			}
//     			if(($amount_borrow_term)%($amount_fund_term)!=0){///end for record odd number only
//     				$start_date = $next_payment;//$dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount']);
//     				$next_payment = $dbtable->checkFirstHoliday($data['date_line'],$data['every_payamount']);
//     				$amount_day = $amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
//     				if($payment_method==1){
//     					$pri_permonth = $remain_principal-$pri_permonth; // $pri_permonth*($amount_day/$amount_fund_term);//check it if khmer currency
//     					$interest_paymonth = $pri_permonth*($data['interest_rate']/100/$borrow_term)*$amount_day;
//     					$interest_paymonth = $this->round_up_currency($curr_type,$interest_paymonth);
//     				}elseif($payment_method==2){
//     					$pri_permonth = $this->round_up_currency($curr_type, $pri_permonth);
//     					$remain_principal = $pri_permonth;//$remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
//     					$interest_paymonth = $pri_permonth*($data['interest_rate']/100/$borrow_term)*$amount_day;
//     					$interest_paymonth = $this->round_up_currency($curr_type,$interest_paymonth);
//     				}elseif($payment_method==3){
//     					$pri_permonth = $remain_principal-$pri_permonth;
//     					$interest_paymonth = $data['total_amount']*($data['interest_rate']/100/$borrow_term)*$amount_day;
// //     					$interest_paymonth = ($data['total_amount']*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($day_perterm/$day_perterm));
//     					$interest_paymonth = $this->round_up_currency($curr_type,$interest_paymonth);
// //     					$pri_permonth = $this->round_up_currency($curr_type,$pri_permonth);
//     				}elseif($payment_method==4){
//     					$interest_paymonth = $data['total_amount']*($data['interest_rate']/100/$borrow_term)*$amount_day;
// //     					$interest_paymonth = ($data['total_amount']*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($amount_day/$day_perterm));
//     					$interest_paymonth = $this->round_up_currency($curr_type,$interest_paymonth);
//     					$pri_permonth = $remain_principal-$pri_permonth;
//     				}elseif($payment_method==5){
//     					$pri_permonth = $remain_principal;
//     					$interest_paymonth = $remain_principal*($data['interest_rate']/100/$borrow_term)*$amount_day;
//     					$interest_paymonth = $this->round_up_currency($curr_type,$interest_paymonth);
//     				}elseif($payment_method==6){
//     					$interest_paymonth = $this->round_up_currency($curr_type,$remain_principal*$irr_interest);
//     					$fixed_principal = round($total_loan_amount/$term_install,0, PHP_ROUND_HALF_DOWN);
//     					$pri_permonth = $remain_principal;
//     				}
    				
//     				$datapayment = array(
//     						'member_id'=>$member_id,
//     						'total_principal'=>$pri_permonth,//good
//     						'principal_permonth'=> $pri_permonth,//good
//     						'principle_after'=> $old_pri_permonth,//good
//     						'total_interest'=>$interest_paymonth,//good
//     						'total_interest_after'=>$interest_paymonth+$saving,//good
//     						'total_payment'=>$interest_paymonth+$pri_permonth,//good
//     						'total_payment_after'=>$old_pri_permonth+$old_interest_paymonth+$saving,//good
//     						'date_payment'=>$next_payment,//good
//     						'is_completed'=>0,
//     						'branch_id'=>$data['branch_id'],
//     						'status'=>1,
//     						'amount_day'=>$amount_day,
//     						'collect_by'=>$data['co_id'],
//     						'installment_amount'=>$i+$start_id,
//     						'penelize_service'=>$penelize_service,
//     						'saving_amount'=>$saving,
//     				);
//     				$this->insert($datapayment);
    				
//     				$this->_name='ln_loan_group';
//     				$datagroup = array('date_line'=>$next_payment);
//     				$where =" g_id= ".$g_id;
//     				$this->update($datagroup, $where);//add group loan
//     				$this->_name='ln_loanmember_funddetail';
    				
//     			}
//     		   $db->commit();
    			return 1;
    		}catch (Exception $e){
    			$db->rollBack();
    			$err =$e->getMessage();
    			echo $err;exit();
    			Application_Model_DbTable_DbUserLog::writeMessageError($err);
    		}
    }
    public function getNextDateById($pay_term){
    	if($pay_term==3){
    		$str_next = 'next month';
    	}elseif($pay_term==2){
    		$str_next = 'next week';
    	}else{
    		$str_next = 'next day';
    	}
    	return $str_next;
    }
    public function getSubDaysByPaymentTerm($pay_term){
    	if($pay_term==3){
    		$amount_days =30;
    	}elseif($pay_term==2){
    		$amount_days =7;
    	}else{
    		$amount_days =1;
    	}
    	return $amount_days;
    	
    }
    public function CountDayByDate($start,$end){
    	$db = new Application_Model_DbTable_DbGlobal();
    	return ($db->countDaysByDate($start,$end));
    }
    public function CalculateByMethod($method_type){
    	
    }
    public function getLoanInfo($id){
    	$db=$this->getAdapter();
    $sql=" SELECT 
        (SELECT lf.total_principal FROM `ln_loanmember_funddetail` AS lf WHERE lf. member_id= l.member_id AND status=1 AND lf.is_completed=0 LIMIT 1)  AS total_principal
    	,l.currency_type ,l.interest_rate , l.loan_number,l.payment_method, l.group_id,
    	(SELECT co_id FROM `ln_loan_group` WHERE g_id  = l.group_id LIMIT 1 ) AS co_id , 
    	(SELECT zone_id FROM `ln_loan_group` WHERE g_id  = l.group_id LIMIT 1) AS zone_id,
    	(SELECT level FROM `ln_loan_group` WHERE g_id  = l.group_id LIMIT 1) AS level  
    	 FROM `ln_loan_member` AS l WHERE l.client_id=$id AND status=1 AND l.is_completed=0 ";
    	return $db->fetchRow($sql);
    }
    public function getLoanInfoBymemberId($id){
    	$db=$this->getAdapter();
    	$sql=" SELECT
    	(SELECT lf.total_principal FROM `ln_loanmember_funddetail` AS lf WHERE lf. member_id= l.member_id AND status=1 AND lf.is_completed=0 LIMIT 1)  AS total_principal
    	,l.client_id,l.currency_type ,l.interest_rate , l.loan_number,l.payment_method, l.group_id,l.branch_id,
    	(SELECT co_id FROM `ln_loan_group` WHERE g_id  = l.group_id LIMIT 1 ) AS co_id ,
    	(SELECT zone_id FROM `ln_loan_group` WHERE g_id  = l.group_id LIMIT 1) AS zone_id,
    	(SELECT level FROM `ln_loan_group` WHERE g_id  = l.group_id LIMIT 1) AS level
    	FROM `ln_loan_member` AS l WHERE l.member_id=$id AND status=1 AND l.is_completed=0 ";
    	return $db->fetchRow($sql);
    }
    
    public function getClientByTypes($type){
    	$this->_name='ln_loan_member';
    	$sql ="SELECT
    	(SELECT c.client_number FROM `ln_client` AS c WHERE lm.client_id=c.client_id LIMIT 1 )AS client_number,
    	(SELECT c.name_en FROM `ln_client` AS c WHERE lm.client_id=c.client_id LIMIT 1 )AS name_en,
    	lm.client_id ,lm.loan_number
    	FROM `ln_loan_member` AS lm WHERE is_completed = 0 AND status=1 ";
    	$db = $this->getAdapter();
    	$rows = $db->fetchAll($sql);
    	$options=array(0=>'------Select------');
    	if(!empty($rows))foreach($rows AS $row){
    		if($type==1){
    			$lable = $row['client_number'];
    		}elseif($type==2){
    			$lable = $row['name_en'];
    		}
    		else{$lable = $row['loan_number'];
    		}
    		$options[$row['client_id']]=$lable;
    	}
    	return $options;
    }
    public function updateRepaymentSchedule($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		if($data['status_using']==0){
    			$arr_update = array(
    					'status'=>0
    			);
    			$where = ' status = 1 AND g_id = '.$data['id'];
    			$this->update($arr_update, $where);
    			 
    			$this->_name = 'ln_loan_member';
    			$where = ' is_completed = 0 AND status = 1 AND group_id = '.$data['id'].' AND client_id = '.$data["member"];
    			$this->update($arr_update, $where);
    			 
    			 
    			$rows= $this->getAllMemberLoanById($data['id']);
    			$s_where = array();
    			$where = '';
    			foreach ($rows as $id => $row){
    				$s_where[] = "`member_id` = ".$row['member_id'];
    			}
    			$where .= implode(' OR ',$s_where);
    			$where.=" AND status=1 AND is_completed=0 ";
    			 
    			$arr = array(
    					'status'=>0
    			);
    			$this->_name='ln_loanmember_funddetail';
    			 
    			$db->commit();
    			return 1;
    		}
    	
    		$datagroup = array(
    				'group_id'=>$data['member'],
    				'co_id'=>$data['co_id'],
    				'zone_id'=>$data['zone'],
    				'level'=>$data['level'],
    				'date_release'=>$data['release_date'],
    				'date_line'=>$data['date_line'],
    				'create_date'=>date("Y-m-d"),
    				'branch_id'=>$data['branch_id'],
    				'total_duration'=>$data['period'],
    				'first_payment'=>$data['first_payment'],
    				'time_collect'=>$data['time'],
    				'pay_term'=>$data['pay_every'],
    				'payment_method'=>$data['repayment_method'],
    				'holiday'=>$data['every_payamount'],
    				'is_renew'=>0,
    				'loan_type'=>1,
    				'collect_typeterm'=>$data['collect_termtype'],
    				'user_id'=>$this->getUserId()
    		);
    		$g_id = $data['id'];
    		$where = $db->quoteInto('g_id=?', $g_id);
    		$this->update($datagroup, $where);
    		unset($datagroup);
    	
    		$datamember = array(
    				'group_id'=>$g_id,
//     				'loan_number'=>$data['loan_code'],
    				'client_id'=>$data['member'],
    				'payment_method'=>$data['repayment_method'],
    				'currency_type'=>$data['currency_type'],
    				'total_capital'=>$data['total_amount'],//$data[''],
    				'other_fee'=>$data['other_fee'],
    				'admin_fee'=>$data['loan_fee'],
    				'interest_rate'=>$data['interest_rate'],
    				'status'=>1,
    				'is_completed'=>0,
    				'branch_id'=>$data['branch_id'],
    				//'pay_before'=>$data['pay_before'],
    				'pay_after'=>$data['pay_late'],
    				'graice_period'=>$data['graice_pariod'],
    				'amount_collect_principal'=>$data['amount_collect'],
    				'collect_typeterm'=>$data['collect_termtype'],
    				'semi'=>$data['amount_collect_pricipal']
    		);
    		$this->_name='ln_loan_member';
    	
    		$where = $db->quoteInto('group_id=?', $data['id']);
    		$this->update($datamember, $where);
    		unset($datamember);
    	
    		$rows= $this->getAllMemberLoanById($data['id']);
    		$s_where = array();
    		$where = '';
    		foreach ($rows as $id => $row){
    			$s_where[] = "`member_id` = ".$row['member_id'];
    		}
    		$where .= implode(' OR ',$s_where);
    		$where.=" AND status=1 AND is_completed=0 ";
    	
    		$arr = array(
    				'status'=>0
    		);
    		$this->_name='ln_loanmember_funddetail';
    		$this->delete($where);
    		$dbtable = new Application_Model_DbTable_DbGlobal();
    		//======================
    		$remain_principal = $data['total_amount'];
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
    			
    			//for IRR method
    			if($data['repayment_method']==6){
    				$term_install = $data['period']/$data['amount_collect'];
    				$loan_amount = $data['total_amount'];
    				$total_loan_amount = $loan_amount+($loan_amount*($data['interest_rate']*$data['amount_collect'])/100*$term_install);
    				$irr_interest = $this->calCulateIRR($total_loan_amount,$loan_amount,($term_install),$curr_type);
    			}
    			//end IRR method    			
    			$this->_name='ln_loanmember_funddetail';
    			$borrow_term = $dbtable->getSubDaysByPaymentTerm($data['pay_every'],null);//return amount day for payterm
    			$amount_borrow_term = $borrow_term*$data['period'];//amount of borrow
    			
    			$fund_term = $dbtable->getSubDaysByPaymentTerm($data['collect_termtype'],null);//return amount day for payterm
    			$amount_fund_term = $fund_term*$data['amount_collect'];
    			
    			$loop_payment = ($amount_borrow_term)/($amount_fund_term);
    			$payment_method = $data['repayment_method'];
	            $str_next = $dbtable->getNextDateById($data['collect_termtype'],$data['amount_collect']);//for next,day,week,month;
    			
				for($i=1;$i<=$loop_payment;$i++){
    				$amount_collect = $data['amount_collect'];
//     				$day_perterm = $dbtable->getSubDaysByPaymentTerm($data['collect_termtype'],$amount_collect);//return amount day for payterm
    			   // $str_next = $dbtable->getNextDateById($data['collect_termtype'],$data['amount_collect']);//for next,day,week,month;
    				
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
//     							echo $remain_principal;
    							$pri_permonth = $data['total_amount']-$pri_permonth*($i-(($data['graice_pariod']/$amount_collect)+1));//code error here
    							
    						}
    						$start_date = $next_payment;
    						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
    						
							//$next_payment = $dbtable->checkDefaultDate($str_next, $defaultnext_payment, $data['amount_collect']);
							$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
 
    						$interest_paymonth = $remain_principal*($data['interest_rate']/100/$borrow_term)*$amount_day;
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
    					}else{
    						$next_payment = $data['first_payment'];
    						$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
    						$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    					}
    					$interest_paymonth = $data['total_amount']*($data['interest_rate']/100/$borrow_term)*$amount_day;
//     					$interest_paymonth = $data['total_amount']*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($amount_day/$day_perterm);
    					
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
    					}else{
    						$next_payment = $data['first_payment'];
    						$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
    					}
    					    $amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    					    $interest_paymonth = $data['total_amount']*($data['interest_rate']/100/$borrow_term)*$amount_day;
    						
    				}elseif($payment_method==4){//fixed payment full last period yes
    					if($i!=1){
    						$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    						$start_date = $next_payment;
    						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
//     						$interest_paymonth = $data['total_amount']*($data['interest_rate']/100)*($amount_day/$day_perterm);
    							
    					}else{
    						$next_payment = $data['first_payment'];
    						$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
    					}
    					$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    					$interest_paymonth = $remain_principal*($data['interest_rate']/100/$borrow_term)*$amount_day;
    					$interest_paymonth = $this->round_up_currency($curr_type, $interest_paymonth);
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
//     							$interest_paymonth = ($remain_principal*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($day_perterm/$day_perterm));
    					}else{
    						$pri_permonth = 0;//check if get pri first too much change;
    						$next_payment = $data['first_payment'];
    						$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
    						$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    						$interest_paymonth = $data['total_amount']*($data['interest_rate']/100/$borrow_term)*$amount_day;
//     						$interest_paymonth = ($data['total_amount']*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($day_perterm/$day_perterm));
    					}
    				}else{//    fixed payment with fixed rate
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
    							
    					}else{
    						$fixed_principal = intval($total_loan_amount/$term_install);//fixed 'ex: 100.70=>100
    						//$fixed_principal = round($total_loan_amount/$term_install,0, PHP_ROUND_HALF_DOWN);//fixed
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
    				
    				
    				if($data['amount_collect']==$amount_collect){
    					
    					$datapayment = array(
    							'member_id'=>$g_id,
    							'total_principal'=>$remain_principal,//good
    							'principal_permonth'=> $old_pri_permonth,//good
    							'principle_after'=> $old_pri_permonth,//good
    							'total_interest'=>$old_interest_paymonth,//good
    							'total_interest_after'=>$old_interest_paymonth,//good
    							'total_payment'=>$old_pri_permonth+$old_interest_paymonth,//good
    							'total_payment_after'=>$old_pri_permonth+$old_interest_paymonth,//good
    							'date_payment'=>$next_payment,//good
    							'is_completed'=>0,
    							'branch_id'=>$data['branch_id'],
    							'status'=>1,
    							'amount_day'=>$old_amount_day,
    							'collect_by'=>$data['co_id']
    					);
    					$this->insert($datapayment);
    					$amount_collect=0;
    					$old_remain_principal = 0;
    					$old_pri_permonth = 0;
    					$old_interest_paymonth = 0;
    					$old_amount_day = 0;
	    				
    					$from_date=$next_payment;
	     				if($i!=1){
	    					$next_payment = $dbtable->checkDefaultDate($str_next, $start_date, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
	     				}
    				}else{
    					//if(){ the old record;
    						
    					//}
    					//if record not cherk min smer 0
    					
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
    						'member_id'=>$g_id,
    						'total_principal'=>$pri_permonth,//good
    						'principal_permonth'=> $pri_permonth,//good
    						'principle_after'=> $old_pri_permonth,//good
    						'total_interest'=>$interest_paymonth,//good
    						'total_interest_after'=>$interest_paymonth,//good
    						'total_payment'=>$interest_paymonth+$pri_permonth,//good
    						'total_payment_after'=>$old_pri_permonth+$old_interest_paymonth,//good
    						'date_payment'=>$next_payment,//good
    						'is_completed'=>0,
    						'branch_id'=>$data['branch_id'],
    						'status'=>1,
    						'amount_day'=>$amount_day,
    						'collect_by'=>$data['co_id']
    				);
    				$this->insert($datapayment);
    				
    			}
    		   $db->commit();
    			return 1;
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Form_FrmMessage::message("INSERT_FAIL");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    public function getAllMemberLoanById($member_id){//for get id fund detail for update
    	$db = $this->getAdapter();
    	$sql = "SELECT lm.member_id ,lm.client_id,lm.group_id ,lm.loan_number,
    	(SELECT name_kh FROM `ln_client` WHERE client_id = lm.client_id LIMIT 1) AS client_name_kh,
    	(SELECT name_en FROM `ln_client` WHERE client_id = lm.client_id LIMIT 1) AS client_name_en,
    	(SELECT client_number FROM `ln_client` WHERE client_id = lm.client_id LIMIT 1) AS client_number,
    	lm.total_capital,lm.admin_fee,lm.loan_purpose FROM `ln_loan_member` AS lm
    	WHERE lm.status =1 AND lm.group_id = $member_id ";
    	return $db->fetchAll($sql);
    }
    
    public function getLaoForRepaymentSchedule($loan_id){
    	$db = $this->getAdapter();
    	$sql = "SELECT
			    	d.`date_payment`,
			    	sum(d.`principle_after`),
			    	sum(d.`total_interest_after`),
			    	sum(d.`penelize`),
			    	sum(d.`total_payment_after`),
			    	SUM(d.`principle_after`+d.`total_interest_after`+d.`penelize`) as all_total,
			    	l.`date_release` AS release_date,
			    	(SELECT cm.`date_input` FROM `ln_client_receipt_money` AS  cm
					WHERE cm.`loan_id`=l.id AND cm.status=1 ORDER BY cm.`date_pay` DESC LIMIT 1) AS last_pay_date,
			    	l.customer_id AS client_id,
			    	l.currency_type ,
			    	l.interest_rate , 
			    	l.loan_number,
			    	l.payment_method, 
			    	l.branch_id,
			    	l.`holiday`,
			    	l.`collect_typeterm`,
			    	l.co_id ,
			    	l.zone_id,
			    	l.level
			    FROM 
    		`ln_loan` AS l,
    		`ln_loan_detail` AS d 
    		WHERE 
		 	d.status=1
    		AND d.`is_completed`=0 
    		AND l.id=$loan_id AND l.`status`=1 ";
    	return $db->fetchAll($sql);
    }
    
}
  


