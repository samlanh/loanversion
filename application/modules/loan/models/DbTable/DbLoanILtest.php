<?php

class Loan_Model_DbTable_DbLoanILtest extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_test_loan_group';
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
    public function addNewLoanILTest($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$sql=" TRUNCATE TABLE ln_test_loan_group ";
    		$db->query($sql);
    		$sql =" TRUNCATE TABLE ln_test_loan_member";
    		$db->query($sql);
    		$sql=" TRUNCATE TABLE ln_test_loanmember_funddetail";
    		$db->query($sql);
    		
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
    				'collect_typeterm'=>$data['collect_termtype']
    		);
    		
    		$g_id = $this->insert($datagroup);//add group loan
    		
    		unset($datagroup);
    		if(empty($data['loan_code'])){
    			$data['loan_code']=$data['get_laonnumber'];
    		}
    		$datamember = array(
    				'group_id'=>$g_id,
    				'loan_number'=>$data['loan_code'],
    				'client_id'=>$data['member'],
    				'payment_method'=>$data['repayment_method'],
    				'currency_type'=>$data['currency_type'],
    				'total_capital'=>$data['total_amount'],//$data[''],
    				'admin_fee'=>$data['loan_fee'],
    				'other_fee'=>$data['other_fee'],
    				'interest_rate'=>$data['interest_rate'],
    				'status'=>1,
    				'is_completed'=>0,
    				'branch_id'=>$data['branch_id'],
    				//'pay_before'=>$data['pay_before'],
    				'pay_after'=>$data['pay_late'],
    				'graice_period'=>$data['graice_pariod'],
    				'amount_collect_principal'=>$data['amount_collect'],
    				'collect_typeterm'=>$data['collect_termtype'],
    				'loan_number'=>$data['loan_code'],
    				'semi'=>$data['amount_collect_pricipal']
    		);
    		$this->_name='ln_test_loan_member';
    		$member_id = $this->insert($datamember);//add member loan
    		unset($datamember);
    		 
    		$remain_principal = $data['total_amount'];
    		$remain_principalirr =  $data['total_amount'];
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
    		$penelize_service=0;
    		
    		//for IRR method
    		if($data['repayment_method']==6 OR $data['repayment_method']==7){
    		$term_install = $data['period']/$data['amount_collect'];
    		$loan_amount = $data['total_amount'];
    		$total_loan_amount = $loan_amount+($loan_amount*($data['interest_rate']*$data['amount_collect'])/100*$term_install);
    		
    		$irr_interest = $this->calCulateIRR($total_loan_amount,$loan_amount,($term_install),$curr_type);
    		//end of IRR
    		}
    		
    		$this->_name='ln_test_loanmember_funddetail';
    		$dbtable = new Application_Model_DbTable_DbGlobal();
    		$borrow_term = $dbtable->getSubDaysByPaymentTerm($data['pay_every'],null);//return amount day for payterm
    		$amount_borrow_term = $borrow_term*$data['period'];//amount of borrow
    		 
    		$fund_term = $dbtable->getSubDaysByPaymentTerm($data['collect_termtype'],null);//return amount day for payterm
    		$amount_fund_term = $fund_term*$data['amount_collect'];
    		 
    		$loop_payment = ($amount_borrow_term)/($amount_fund_term);
    		$payment_method = $data['repayment_method'];
    		//     			for($i=1;$i<=($data['period']/$data['amount_collect']);$i++){
    		for($i=1;$i<=$loop_payment;$i++){
    			$amount_collect = $data['amount_collect'];
    			$day_perterm = $dbtable->getSubDaysByPaymentTerm($data['collect_termtype'],$amount_collect);//return amount day for payterm
    			$str_next = $dbtable->getNextDateById($data['collect_termtype'],$data['amount_collect']);//for next,day,week,month;
    		
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
//     					$next_payment = $dbtable->preveiwDate($next_payment,$i);
    					$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
    					$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    		
    					$interest_paymonth = $remain_principal*($data['interest_rate']/100/$borrow_term)*$amount_day;
    					//     						$interest_paymonth = $remain_principal*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($amount_day/$day_perterm);
    		
    				}else{
    					$next_payment = $data['first_payment'];
    					$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
    					$amount_day = $dbtable->CountDayByDate($start_date,$next_payment);
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
    					$amount_day = $dbtable->CountDayByDate($start_date,$next_payment);
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
    					//     							$interest_paymonth = ($remain_principal*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($day_perterm/$day_perterm));
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
    						
    				}
    				else{
    					//$fixed_principal = round($total_loan_amount/$term_install,0, PHP_ROUND_HALF_DOWN);//fixed '
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
    				
    			}elseif($payment_method==8){
    				$panelize_descreas =1500;
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
    					if($i>11){$penelize_service=0;}
    				}else{
    					$penelize_service = 39500;
    					$interest_rate = $data['interest_rate'];
    					
    					$next_payment = $data['first_payment'];
    					$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
    				}
    				$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    				$amount_peroid = $dbtable->getAmountDayByTerm($data['pay_every']);
    				$interest_paymonth = $data['total_amount']*($interest_rate/100/$borrow_term)*$amount_peroid;
    				
    			}else{//    fixed payment IRR
    				if($i!=1){
    					$start_date = $next_payment;
    					$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
    					$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    					$remain_principal = $remain_principal-$pri_permonth;
    					$interest_paymonth = $this->round_up_currency($curr_type,$remain_principal*$irr_interest);
    					$fixed_principal = intval($total_loan_amount/$term_install);
    					$fixed_principal= $this->round_up_currency($curr_type,$fixed_principal);
    					$pri_permonth = $fixed_principal-$interest_paymonth;
    					
    					if($i==$loop_payment){//for end of record only
    						   $pri_permonth = $remain_principal;
    						   $fixed_principal = intval($total_loan_amount/$term_install);
    						   $fixed_principal= $this->round_up_currency($curr_type,$fixed_principal);
    						   $interest_paymonth = $fixed_principal-$remain_principal;
    					}
    					
    				}else{
    					//$fixed_principal = round($total_loan_amount/$term_install,0, PHP_ROUND_HALF_DOWN);//fixed '
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
    		
    			if($data['amount_collect']==$amount_collect){
    					
    				$datapayment = array(
    						'member_id'=>$member_id,
    						'total_principal'=>$remain_principal,//good
    						'principal_permonth'=> $old_pri_permonth,//good
    						'total_interest'=>$old_interest_paymonth,//good
    						'penelize_service'=>$penelize_service,
    						'total_payment'=>$old_pri_permonth+$old_interest_paymonth,//good
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
    				//     					$interest_paymonth = ($pri_permonth*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($amount_day/$day_perterm));
    				$interest_paymonth = $this->round_up_currency($curr_type,$interest_paymonth);
    			}elseif($payment_method==2){
    				//     					$pri_permonth = ($curr_type==1)?round($data['total_amount'],-2):$data['total_amount'];
    				//     					$pri_permonth = ($curr_type==1)? round($pri_permonth,-2): round($pri_permonth);
    				$pri_permonth = $this->round_up_currency($curr_type, $pri_permonth);
    				$remain_principal = $pri_permonth;//$remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    				$interest_paymonth = $pri_permonth*($data['interest_rate']/100/$borrow_term)*$amount_day;
    				//     					$interest_paymonth = ($data['total_amount']*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($amount_day/$day_perterm));
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
    				//     					$interest_paymonth = ($remain_principal*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($amount_day/$day_perterm));
    				$interest_paymonth = $this->round_up_currency($curr_type,$interest_paymonth);
    			}elseif($payment_method==6){
    				$pri_permonth = $remain_principal;
//     				$interest_paymonth = $data['total_amount']*($data['interest_rate']/100/$borrow_term)*$amount_day;
    				$interest_paymonth = $this->round_up_currency($curr_type,$remain_principal*$irr_interest);
    			}
    		
    			$datapayment = array(
    					'member_id'=>$member_id,
    					'total_principal'=>$pri_permonth,//good
    					'principal_permonth'=> $pri_permonth,//good
    					'total_interest'=>$interest_paymonth,//good
    					'total_payment'=>$interest_paymonth+$pri_permonth,//good
    					'date_payment'=>$data['date_line'],//good
    					'is_completed'=>0,
    					'branch_id'=>$data['branch_id'],
    					'status'=>1,
    					'amount_day'=>$amount_day,
    					'collect_by'=>$data['co_id']
    			);
    			$this->insert($datapayment);
    		
    		}
    		$sql = "SELECT f.* , DATE_FORMAT(f.date_payment, '%d-%m-%Y') AS date_payments,DATE_FORMAT(f.date_payment, '%Y-%m-%d') AS date_name FROM ln_test_loanmember_funddetail AS f  WHERE member_id = ".$member_id;
    		$rows =  $db->fetchAll($sql);
    		$db->commit();
    		return $rows;
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Form_FrmMessage::message("INSERT_FAIL");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
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
}