<?php

class Loan_Model_DbTable_DbLoanGroupTEst extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_loan_group';
    public function getAllGroupLoan($search){
    	$from_date =(empty($search['from_date']))? '1': "lg.date_release >= '".$search['from_date']." 00:00:00'";
    	$to_date = (empty($search['to_date']))? '1': "lg.date_release <= '".$search['to_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	 
    	$db = $this->getAdapter();
    	$sql=" SELECT lm.member_id,lm.loan_number,
    	(SELECT name_kh FROM `ln_client` WHERE client_id = lm.client_id LIMIT 1) AS client_name_kh,
    	(SELECT name_en FROM `ln_client` WHERE client_id = lm.client_id LIMIT 1) AS client_name_en,
    	lm.total_capital,lm.interest_rate,
    	(SELECT payment_nameen FROM `ln_payment_method` WHERE id = lm.payment_method LIMIT 1) AS payment_method,
    	(SELECT zone_name FROM `ln_zone` WHERE zone_id=lg.zone_id LIMIT 1) AS zone_name,
    	(SELECT co_firstname FROM `ln_co` WHERE co_id =lg.co_id LIMIT 1) AS co_name,
    	(SELECT branch_namekh FROM `ln_branch` WHERE br_id =lg.branch_id LIMIT 1) AS branch,
    	lg.status  FROM `ln_loan_group` AS lg,`ln_loan_member` AS lm
    	WHERE lg.g_id = lm.group_id AND lg.loan_type =2 ";
    	if($search['status']>1){
    		$where.= "lm.status = ".$search['status'];
    
    	}
    	$order = " ORDER BY ";
    	$db = $this->getAdapter();
    	return $db->fetchAll($sql.$where);
    }
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
    	return (($curr_id==1)? $this->round_up($value, $places):$value);
    }
    public function addNewLoanGroup($data){
    	
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$datagroup = array(
    				'group_id'=>$data['group_id'],
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
    				'loan_type'=>2,
    				'collect_typeterm'=>$data['collect_termtype']
    		);
    		$g_id = $this->insert($datagroup);//add group loan
    		unset($datagroup);
    		
    		$next_payment = $data['first_payment'];
    		$start_date = $data['release_date'];//loan release;
    		$curr_type = $data['currency_type'];
    		
    		$tranlist = explode(',',$data['record_row']);
    		$dbtable = new Application_Model_DbTable_DbGlobal();
    		foreach ($tranlist as $i) {
    			
    			$loan_number = $dbtable->getLoanNumber();
    			$datamember = array(
    					'group_id'=>$g_id,
    					'loan_number'=>$loan_number,
    					'client_id'=>$data['member_id'.$i],
    					'payment_method'=>$data['repayment_method'],
    					'currency_type'=>$data['currency_type'],
    					'admin_fee'=>$data['admin_fee'.$i],
    					'total_capital'=>$data['debt_amount'.$i],//$data[''],
    					'interest_rate'=>$data['interest_rate'],
    					'branch_id'=>$data['branch_id'],
    					'pay_before'=>$data['pay_before'],
    					'pay_after'=>$data['pay_late'],
    					'graice_period'=>$data['graice_pariod'],
    					'amount_collect_principal'=>$data['amount_collect'],
    					'collect_typeterm'=>$data['collect_termtype'],
    					'loan_purpose'=>$data['note'.$i],
    					'semi'=>$data['amount_collect_pricipal']
    			);
    			
    			$this->_name='ln_loan_member';
    			$member_id = $this->insert($datamember);//add member loan
    			unset($datamember);
    			
    			$old_remain_principal = 0;
    			$old_pri_permonth = 0;
    			$old_interest_paymonth = 0;
    			$old_amount_day = 0;
    			$amount_collect = 1;
    			$ispay_principal=2;//for payment type = 5;
    			$is_subremain = 2;
    			$remain_principal = $data['debt_amount'.$i];
    			
    			
    			$this->_name='ln_loanmember_funddetail';
    			
    			$borrow_term = $dbtable->getSubDaysByPaymentTerm($data['pay_every'],null);//return amount day for payterm
    			$amount_borrow_term = $borrow_term*$data['period'];//amount of borrow
    			
    			$fund_term = $dbtable->getSubDaysByPaymentTerm($data['collect_termtype'],null);//return amount day for payterm
    			$amount_fund_term = $fund_term*$data['amount_collect'];
    			
    			$loop_payment = ($amount_borrow_term)/($amount_fund_term);
    			$payment_method = $data['repayment_method'];
    			for($j=1;$j<=$loop_payment;$j++){
    				//return amount next day collection
    				$amount_collect = $data['amount_collect'];
    				$day_perterm = $dbtable->getSubDaysByPaymentTerm($data['collect_termtype'],$amount_collect);//return amount day for payterm
    				
    				//$day_perterm = $dbtable->getSubDaysByPaymentTerm($data['pay_every'],$amount_collect);//return amount day for payterm
    				$str_next = $dbtable->getNextDateById($data['collect_termtype'],$data['amount_collect']);//for next,day,week,month;
    				
    				if($payment_method==1){//decline//completed
//     					$pri_permonth = ($data['debt_amount'.$j]/($data['period']-$data['graice_pariod'])*$amount_collect);
    					$pri_permonth = $data['debt_amount'.$i]/(($amount_borrow_term-($data['graice_pariod']*$borrow_term))/$amount_fund_term);
    					$pri_permonth = $this->round_up_currency($curr_type, $pri_permonth);
    					
    					if($j*$amount_collect<=$data['graice_pariod']){//check here//for graice period
    						$pri_permonth = 0;
    					}
    					if($j!=1){
    						if($data['graice_pariod']!=0){//if collect =1 not other check
    							if($j*$amount_collect>$data['graice_pariod']+$amount_collect){//not wright
    								$remain_principal = $remain_principal-$pri_permonth;
    							}else{
    								
    							}
    						}else{
    							$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា}
    						}
    						if($j==$loop_payment){//check condition here//for end of record only
    							$pri_permonth = $data['debt_amount'.$i]-$pri_permonth*($j-(($data['graice_pariod']/$amount_collect)+1));//code error here
    						}
    						$start_date = $next_payment;
    						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount']);
    						$amount_day = $dbtable->CountDayByDate($start_date,$next_payment);
    						$interest_paymonth = $remain_principal*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($amount_day/$day_perterm);
    					
    					}else{
    						$next_payment = $data['first_payment'];
    						$amount_day = $dbtable->CountDayByDate($start_date,$next_payment);
    						$interest_paymonth = ($data['debt_amount'.$i])*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($amount_day/$day_perterm);
    					}
    				}elseif($payment_method==2){//baloon
    					$pri_permonth=0;
    					if(($j*$amount_fund_term)==$amount_borrow_term){//end record//if j == loop
    						$pri_permonth = $data['debt_amount'.$i];
    						$remain_principal = $pri_permonth;//$remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    					
    					}
    					if($j!=1){
    						$start_date = $next_payment;
    						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount']);
    						$amount_day = $dbtable->CountDayByDate($start_date,$next_payment);
//     						$interest_paymonth = $data['debt_amount'.$i]*($data['interest_rate']/100)*($amount_day/$day_perterm);
    					}else{
    						$next_payment = $data['first_payment'];//$dbtable->getNextPayment($str_next, $start_date, $data['amount_collect'],$data['every_payamount']);
    						$amount_day = $dbtable->CountDayByDate($start_date,$next_payment);
    					}
    					$interest_paymonth = $data['debt_amount'.$i]*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($amount_day/$day_perterm);
    				}elseif($payment_method==3){//fixed rate
    					$pri_permonth = ($data['debt_amount'.$i]/($amount_borrow_term/$amount_fund_term));
    					$pri_permonth =$this->round_up_currency($curr_type,$pri_permonth);
    					
    					if($j!=1){
    						$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    						if($i==$loop_payment){//for end of record only
    							$pri_permonth = $remain_principal;
    						}
    						$start_date = $next_payment;
    						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount']);
    					}else{
    						$next_payment = $data['first_payment'];
    						$amount_day = $dbtable->CountDayByDate($start_date,$next_payment);
    					}
    						$interest_paymonth = $data['debt_amount'.$i]*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($day_perterm/$day_perterm);
    						
    				}elseif($payment_method==4){//fixed payment full last period yes
    					if($j!=1){
    						$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    						$start_date = $next_payment;
    						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount']);
    						
    						$interest_paymonth = $data['debt_amount'.$i]*($data['interest_rate']/100)*($amount_day/$day_perterm);
    							
    					}else{
    						$next_payment = $data['first_payment'];
    					}
    					$amount_day = $dbtable->CountDayByDate($start_date,$next_payment);
    					$interest_paymonth = $remain_principal*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($amount_day/$day_perterm);
    					$interest_paymonth = $this->round_up_currency($curr_type, $interest_paymonth);
    					
    					$pri_permonth = $data['amount_collect_pricipal']-$interest_paymonth;//don't understand
    					if($j==$loop_payment){//for end of record only
    						$pri_permonth = $remain_principal;
    					}
    				}elseif($payment_method==5){//semi baloon//ok
    					if($j!=1){
    						$ispay_principal++;
    						$is_subremain++;
    						$pri_permonth = ($data['debt_amount'.$i]/$data['period'])*$data['amount_collect_pricipal'];
    						$pri_permonth = ($curr_type==1)?round($pri_permonth,-2):$pri_permonth;
    						$pri_permonth=0;
								if(($is_subremain-1)==$data['amount_collect_pricipal']){
    								$pri_permonth = ($data['debt_amount'.$i]/$data['period'])*$data['amount_collect_pricipal'];
    								$is_subremain=1;
    							}
    							if(($ispay_principal-1)==$data['amount_collect_pricipal']+1){
    								$remain_principal = $remain_principal-($data['debt_amount'.$i]/$data['period'])*$data['amount_collect_pricipal'];
    								$ispay_principal=2;
    							}
    							if($j==($data['period']/$data['amount_collect'])){//check condition here//for end of record only
    								$pri_permonth = ($data['debt_amount'.$i]/$data['period'])*$data['amount_collect_pricipal'];
    								$pri_permonth = $data['debt_amount'.$i]-$pri_permonth*($j-(($data['graice_pariod']/$amount_collect)+1));//code error here
    							}
    							
    							$start_date = $next_payment;
    							$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount']);
    							$amount_day = $dbtable->CountDayByDate($start_date,$next_payment);
    							$interest_paymonth = $remain_principal*($data['interest_rate']/100)*($amount_day/$day_perterm);
    					
    					}else{
    						$pri_permonth = 0;//check if get pri first too much change;
    						$next_payment = $data['first_payment'];
    						$amount_day = $dbtable->CountDayByDate($start_date,$next_payment);
    						$interest_paymonth = $data['debt_amount'.$i]*($data['interest_rate']/100)*($amount_day/$day_perterm);
    					}
    				}else{//    fixed payment with fixed rate
    					    $pri_permonth = $data['debt_amount'.$i]/$data['period']*$amount_collect;
    					    $pri_permonth =$this->round_up_currency($curr_type, $pri_permonth);
    					    	if($j!=1){
    					    			$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    					    	        $start_date = $next_payment;
    					    	        if($j==$loop_payment){//check condition here//for end of record only
    					    	        	$pri_permonth = $remain_principal;
    					    	        }
    					    			$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount']);
    					    			$amount_day = $dbtable->CountDayByDate($start_date,$next_payment);
    					    	}else{
    					    			$next_payment = $data['first_payment'];
    					    			$amount_day = $dbtable->CountDayByDate($start_date,$next_payment);
    					    	}
    					    			$interest_paymonth = ($data['debt_amount'.$i]*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)/$data['period']*($day_perterm/$day_perterm));
    				}
    				$old_remain_principal =$old_remain_principal+$remain_principal;
    				$old_pri_permonth = $old_pri_permonth+$pri_permonth;
    				
    				$old_interest_paymonth = $old_interest_paymonth+$interest_paymonth;
    				$old_amount_day =$old_amount_day+ $amount_day;
    				
    				if($data['amount_collect']==$amount_collect){
    					
    					$datapayment = array(
    							'member_id'=>$member_id,
    							'total_principal'=>$remain_principal,//good
    							'principal_permonth'=> $old_pri_permonth,//good
    							'total_interest'=>$old_interest_paymonth,//good
    							'total_payment'=>$old_pri_permonth+$old_interest_paymonth,//good
    							'date_payment'=>$next_payment,//good
    							'is_completed'=>0,
    							'branch_id'=>1,
    							'status'=>1,
    							'amount_day'=>$old_amount_day,
    					);
    					$this->insert($datapayment);
    					$amount_collect=0;
    					$old_remain_principal = 0;
    					$old_pri_permonth = 0;
    					$old_interest_paymonth = 0;
    					$old_amount_day = 0;
    					
    				}else{
    					
    				}
    				$amount_collect++;
    			}
    			if(($amount_borrow_term)%($amount_fund_term)!=0){///end for record odd number only
    				$start_date = $next_payment;//$dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount']);
    				$amount_day = $amount_day = $dbtable->CountDayByDate($start_date,$data['date_line']);
    				if($payment_method==1){
    					$pri_permonth =$remain_principal-$pri_permonth; // $pri_permonth*($amount_day/$amount_fund_term);//check it if khmer currency
    					$pri_permonth = ($curr_type==1)?round($pri_permonth,-2):$pri_permonth;
    					$interest_paymonth = ($pri_permonth*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($amount_day/$day_perterm));
    				}elseif($payment_method==2){
    					$pri_permonth = ($curr_type==1)?round($data['total_amount'],-2):$data['debt_amount'.$i];
    					$pri_permonth = ($curr_type==1)? round($pri_permonth,-2): round($pri_permonth);
    					$remain_principal = $pri_permonth;//$remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    					$interest_paymonth = ($data['debt_amount'.$i]*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($amount_day/$day_perterm));
    				}elseif($payment_method==3){
    					$pri_permonth = $remain_principal-$pri_permonth;
    					$interest_paymonth = ($data['debt_amount'.$i]*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($day_perterm/$day_perterm));
    					$pri_permonth = ($curr_type==1)? round($pri_permonth,-2): round($pri_permonth);
    					
    				}elseif($payment_method==4){
    					$interest_paymonth = ($data['debt_amount'.$i]*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($amount_day/$day_perterm));
    					$pri_permonth = $remain_principal-$pri_permonth;
    				}elseif($payment_method==5){
    					$pri_permonth = $remain_principal;
    					$interest_paymonth = ($remain_principal*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($amount_day/$day_perterm));
    				}elseif($payment_method==6){
    					$pri_permonth = $pri_permonth*($amount_day/$amount_fund_term);
    					$interest_paymonth = ($data['debt_amount'.$i]*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($amount_day/$day_perterm));
    					
    				}
    				
    				$datapayment = array(
    						'member_id'=>$member_id,
    						'total_principal'=>$pri_permonth,//good
    						'principal_permonth'=> $pri_permonth,//good
    						'total_interest'=>$interest_paymonth,//good
    						'total_payment'=>$interest_paymonth+$pri_permonth,//good
    						'date_payment'=>$data['date_line'],//good
    						'is_completed'=>0,
    						'branch_id'=>1,
    						'status'=>1,
    						'amount_day'=>$amount_day,
    				);
    				$this->insert($datapayment);
    			}
    		}
    		$db->commit();
    		return 1;
    	}catch (Exception $e){
    		$db->rollBack();
    		echo $e->getMessage();exit();
    		return $e->getMessage();
    	}
    }
    public function upDateLoanDisburse($data){
    	$db = $this->getAdapter();
//     	$db->beginTransaction();
//     	try{
    		$datagroup = array(
    				'group_id'=>$data['group_id'],
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
    				'loan_type'=>2,
    				'collect_typeterm'=>$data['collect_termtype']
    		);
    		$g_id = $data['id'];//add group loan
    		$where = $db->quoteInto('g_id=?', $g_id);
    		$this->update($datagroup, $where);
    		unset($datagroup);
    		
    		$this->_name='ln_loan_member';//update member 
    		$arr =array('status'=>0);
    		$where = $db->quoteInto('group_id=?', $g_id);
    		$this->update($arr, $where);
    		
    		$rows= $this->getAllMemberLoanById($g_id);
    		$s_where = array();
    		$where = '';
    		foreach ($rows as $id => $row){
					$s_where[] = "`member_id` = ".$row['member_id'];
    		}
    		$where .= implode(' OR ',$s_where);
    		
    		$this->_name='ln_loanmember_funddetail';//update schedule detail of member 
    		$this->update($arr, $where);
    		
    		$this->_name='ln_loan_member';//update member
    		$next_payment = $data['first_payment'];
    		$start_date = $data['release_date'];//loan release;
    		$curr_type = $data['currency_type'];
    		
    		$tranlist = explode(',',$data['record_row']);
    		$dbtable = new Application_Model_DbTable_DbGlobal();
    		foreach ($tranlist as $i) {
    			$this->_name='ln_loan_member';//update member
    			$loan_number = $dbtable->getLoanNumber();
    			$datamember = array(
    					'group_id'=>$g_id,
    					'loan_number'=>$loan_number,
    					'client_id'=>$data['member_id'.$i],
    					'payment_method'=>$data['repayment_method'],
    					'currency_type'=>$data['currency_type'],
    					'admin_fee'=>$data['admin_fee'.$i],
    					'total_capital'=>$data['debt_amount'.$i],//$data[''],
    					'interest_rate'=>$data['interest_rate'],
    					'branch_id'=>$data['branch_id'],
    					'pay_before'=>$data['pay_before'],
    					'pay_after'=>$data['pay_late'],
    					'graice_period'=>$data['graice_pariod'],
    					'amount_collect_principal'=>$data['amount_collect'],
    					'collect_typeterm'=>$data['collect_termtype'],
    					'loan_purpose'=>$data['note'.$i],
    					'semi'=>$data['amount_collect_pricipal']
    			);
    			 
    			$member_id = $this->insert($datamember);//add member loan
    			unset($datamember);
    			$old_remain_principal = 0;
    			$old_pri_permonth = 0;
    			$old_interest_paymonth = 0;
    			$old_amount_day = 0;
    			$amount_collect = 1;
    			$ispay_principal=2;//for payment type = 5;
    			$is_subremain = 2;
    			$remain_principal = $data['debt_amount'.$i];
    			 
    			 
    			$this->_name='ln_loanmember_funddetail';
    			 
    			$borrow_term = $dbtable->getSubDaysByPaymentTerm($data['pay_every'],null);//return amount day for payterm
    			$amount_borrow_term = $borrow_term*$data['period'];//amount of borrow
    			 
    			$fund_term = $dbtable->getSubDaysByPaymentTerm($data['collect_termtype'],null);//return amount day for payterm
    			$amount_fund_term = $fund_term*$data['amount_collect'];
    			 
    			$loop_payment = ($amount_borrow_term)/($amount_fund_term);
    			$payment_method = $data['repayment_method'];
    			
    			$this->_name='ln_loanmember_funddetail';
    			for($j=1;$j<=$loop_payment;$j++){
    				//return amount next day collection
    				$amount_collect = $data['amount_collect'];
    				$day_perterm = $dbtable->getSubDaysByPaymentTerm($data['collect_termtype'],$amount_collect);//return amount day for payterm
    
    				//$day_perterm = $dbtable->getSubDaysByPaymentTerm($data['pay_every'],$amount_collect);//return amount day for payterm
    				$str_next = $dbtable->getNextDateById($data['collect_termtype'],$data['amount_collect']);//for next,day,week,month;
    
    				if($payment_method==1){//decline//completed
    					//     					$pri_permonth = ($data['debt_amount'.$j]/($data['period']-$data['graice_pariod'])*$amount_collect);
    					$pri_permonth = $data['debt_amount'.$i]/(($amount_borrow_term-($data['graice_pariod']*$borrow_term))/$amount_fund_term);
    					$pri_permonth = $this->round_up_currency($curr_type, $pri_permonth);
    						
    					if($j*$amount_collect<=$data['graice_pariod']){//check here//for graice period
    						$pri_permonth = 0;
    					}
    					if($j!=1){
    						if($data['graice_pariod']!=0){//if collect =1 not other check
    							if($j*$amount_collect>$data['graice_pariod']+$amount_collect){//not wright
    								$remain_principal = $remain_principal-$pri_permonth;
    							}else{
    
    							}
    						}else{
    							$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា}
    						}
    						if($j==$loop_payment){//check condition here//for end of record only
    							$pri_permonth = $data['debt_amount'.$i]-$pri_permonth*($j-(($data['graice_pariod']/$amount_collect)+1));//code error here
    						}
    						$start_date = $next_payment;
    						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount']);
    						$amount_day = $dbtable->CountDayByDate($start_date,$next_payment);
    						$interest_paymonth = $remain_principal*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($amount_day/$day_perterm);
    							
    					}else{
    						$next_payment = $data['first_payment'];
    						$amount_day = $dbtable->CountDayByDate($start_date,$next_payment);
    						$interest_paymonth = ($data['debt_amount'.$i])*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($amount_day/$day_perterm);
    					}
    				}elseif($payment_method==2){//baloon
    					$pri_permonth=0;
    					if(($j*$amount_fund_term)==$amount_borrow_term){//end record//if j == loop
    						$pri_permonth = $data['debt_amount'.$i];
    						$remain_principal = $pri_permonth;//$remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    							
    					}
    					if($j!=1){
    						$start_date = $next_payment;
    						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount']);
    						$amount_day = $dbtable->CountDayByDate($start_date,$next_payment);
    						//     						$interest_paymonth = $data['debt_amount'.$i]*($data['interest_rate']/100)*($amount_day/$day_perterm);
    					}else{
    						$next_payment = $data['first_payment'];//$dbtable->getNextPayment($str_next, $start_date, $data['amount_collect'],$data['every_payamount']);
    						$amount_day = $dbtable->CountDayByDate($start_date,$next_payment);
    					}
    					$interest_paymonth = $data['debt_amount'.$i]*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($amount_day/$day_perterm);
    				}elseif($payment_method==3){//fixed rate
    					$pri_permonth = ($data['debt_amount'.$i]/($amount_borrow_term/$amount_fund_term));
    					$pri_permonth =$this->round_up_currency($curr_type,$pri_permonth);
    						
    					if($j!=1){
    						$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    						if($i==$loop_payment){//for end of record only
    							$pri_permonth = $remain_principal;
    						}
    						$start_date = $next_payment;
    						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount']);
    					}else{
    						$next_payment = $data['first_payment'];
    						$amount_day = $dbtable->CountDayByDate($start_date,$next_payment);
    					}
    					$interest_paymonth = $data['debt_amount'.$i]*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($day_perterm/$day_perterm);
    
    				}elseif($payment_method==4){//fixed payment full last period yes
    					if($j!=1){
    						$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    						$start_date = $next_payment;
    						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount']);
    
    						$interest_paymonth = $data['debt_amount'.$i]*($data['interest_rate']/100)*($amount_day/$day_perterm);
    							
    					}else{
    						$next_payment = $data['first_payment'];
    					}
    					$amount_day = $dbtable->CountDayByDate($start_date,$next_payment);
    					$interest_paymonth = $remain_principal*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($amount_day/$day_perterm);
    					$interest_paymonth = $this->round_up_currency($curr_type, $interest_paymonth);
    						
    					$pri_permonth = $data['amount_collect_pricipal']-$interest_paymonth;//don't understand
    					if($j==$loop_payment){//for end of record only
    						$pri_permonth = $remain_principal;
    					}
    				}elseif($payment_method==5){//semi baloon//ok
    					if($j!=1){
    						$ispay_principal++;
    						$is_subremain++;
    						$pri_permonth = ($data['debt_amount'.$i]/$data['period'])*$data['amount_collect_pricipal'];
    						$pri_permonth = ($curr_type==1)?round($pri_permonth,-2):$pri_permonth;
    						$pri_permonth=0;
    						if(($is_subremain-1)==$data['amount_collect_pricipal']){
    							$pri_permonth = ($data['debt_amount'.$i]/$data['period'])*$data['amount_collect_pricipal'];
    							$is_subremain=1;
    						}
    						if(($ispay_principal-1)==$data['amount_collect_pricipal']+1){
    							$remain_principal = $remain_principal-($data['debt_amount'.$i]/$data['period'])*$data['amount_collect_pricipal'];
    							$ispay_principal=2;
    						}
    						if($j==($data['period']/$data['amount_collect'])){//check condition here//for end of record only
    							$pri_permonth = ($data['debt_amount'.$i]/$data['period'])*$data['amount_collect_pricipal'];
    							$pri_permonth = $data['debt_amount'.$i]-$pri_permonth*($j-(($data['graice_pariod']/$amount_collect)+1));//code error here
    						}
    							
    						$start_date = $next_payment;
    						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount']);
    						$amount_day = $dbtable->CountDayByDate($start_date,$next_payment);
    						$interest_paymonth = $remain_principal*($data['interest_rate']/100)*($amount_day/$day_perterm);
    							
    					}else{
    						$pri_permonth = 0;//check if get pri first too much change;
    						$next_payment = $data['first_payment'];
    						$amount_day = $dbtable->CountDayByDate($start_date,$next_payment);
    						$interest_paymonth = $data['debt_amount'.$i]*($data['interest_rate']/100)*($amount_day/$day_perterm);
    					}
    				}else{//    fixed payment with fixed rate
    					$pri_permonth = $data['debt_amount'.$i]/$data['period']*$amount_collect;
    					$pri_permonth =$this->round_up_currency($curr_type, $pri_permonth);
    					if($j!=1){
    						$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    						$start_date = $next_payment;
    						if($j==$loop_payment){//check condition here//for end of record only
    							$pri_permonth = $remain_principal;
    						}
    						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount']);
    						$amount_day = $dbtable->CountDayByDate($start_date,$next_payment);
    					}else{
    						$next_payment = $data['first_payment'];
    						$amount_day = $dbtable->CountDayByDate($start_date,$next_payment);
    					}
    					$interest_paymonth = ($data['debt_amount'.$i]*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)/$data['period']*($day_perterm/$day_perterm));
    				}
    				$old_remain_principal =$old_remain_principal+$remain_principal;
    				$old_pri_permonth = $old_pri_permonth+$pri_permonth;
    
    				$old_interest_paymonth = $old_interest_paymonth+$interest_paymonth;
    				$old_amount_day =$old_amount_day+ $amount_day;
    
    				if($data['amount_collect']==$amount_collect){
    						
    					$datapayment = array(
    							'member_id'=>$member_id,
    							'total_principal'=>$remain_principal,//good
    							'principal_permonth'=> $old_pri_permonth,//good
    							'total_interest'=>$old_interest_paymonth,//good
    							'total_payment'=>$old_pri_permonth+$old_interest_paymonth,//good
    							'date_payment'=>$next_payment,//good
    							'is_completed'=>0,
    							'branch_id'=>1,
    							'status'=>1,
    							'amount_day'=>$old_amount_day,
    					);
    					$this->insert($datapayment);
    					$amount_collect=0;
    					$old_remain_principal = 0;
    					$old_pri_permonth = 0;
    					$old_interest_paymonth = 0;
    					$old_amount_day = 0;
    						
    				}else{
    						
    				}
    				$amount_collect++;
    			}
    			if(($amount_borrow_term)%($amount_fund_term)!=0){///end for record odd number only
    				$start_date = $next_payment;//$dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount']);
    				$amount_day = $amount_day = $dbtable->CountDayByDate($start_date,$data['date_line']);
    				if($payment_method==1){
    					$pri_permonth =$remain_principal-$pri_permonth; // $pri_permonth*($amount_day/$amount_fund_term);//check it if khmer currency
    					$pri_permonth = ($curr_type==1)?round($pri_permonth,-2):$pri_permonth;
    					$interest_paymonth = ($pri_permonth*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($amount_day/$day_perterm));
    				}elseif($payment_method==2){
    					$pri_permonth = ($curr_type==1)?round($data['total_amount'],-2):$data['debt_amount'.$i];
    					$pri_permonth = ($curr_type==1)? round($pri_permonth,-2): round($pri_permonth);
    					$remain_principal = $pri_permonth;//$remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    					$interest_paymonth = ($data['debt_amount'.$i]*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($amount_day/$day_perterm));
    				}elseif($payment_method==3){
    					$pri_permonth = $remain_principal-$pri_permonth;
    					$interest_paymonth = ($data['debt_amount'.$i]*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($day_perterm/$day_perterm));
    					$pri_permonth = ($curr_type==1)? round($pri_permonth,-2): round($pri_permonth);
    						
    				}elseif($payment_method==4){
    					$interest_paymonth = ($data['debt_amount'.$i]*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($amount_day/$day_perterm));
    					$pri_permonth = $remain_principal-$pri_permonth;
    				}elseif($payment_method==5){
    					$pri_permonth = $remain_principal;
    					$interest_paymonth = ($remain_principal*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($amount_day/$day_perterm));
    				}elseif($payment_method==6){
    					$pri_permonth = $pri_permonth*($amount_day/$amount_fund_term);
    					$interest_paymonth = ($data['debt_amount'.$i]*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($amount_day/$day_perterm));
    						
    				}
    
    				$datapayment = array(
    						'member_id'=>$member_id,
    						'total_principal'=>$pri_permonth,//good
    						'principal_permonth'=> $pri_permonth,//good
    						'total_interest'=>$interest_paymonth,//good
    						'total_payment'=>$interest_paymonth+$pri_permonth,//good
    						'date_payment'=>$data['date_line'],//good
    						'is_completed'=>0,
    						'branch_id'=>1,
    						'status'=>1,
    						'amount_day'=>$amount_day,
    				);
    				$this->insert($datapayment);
    			}
    		}
// exit();
//     		$db->commit();
//     		return 1;
//     	}catch (Exception $e){
//     		$db->rollBack();
//     		echo $e->getMessage();exit();
//     		return $e->getMessage();
//     	}
    }
    public function getAllMemberLoanById($member_id){
    	$db = $this->getAdapter();
    	$sql = "SELECT member_id ,client_id,group_id ,loan_number,
    			total_capital,admin_fee,loan_purpose FROM `ln_loan_member` 
    			WHERE status =1 AND group_id = $member_id ";
    	return $db->fetchAll($sql);
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
    function getAllMemberByGroup($group_member){
    	$db = $this->getAdapter();
    	$sql = "SELECT client_id,name_en FROM `ln_client` WHERE 
    	(parent_id = $group_member OR client_id = $group_member) AND status=1 ";
    	return $db->fetchAll($sql);
    }
  
}

