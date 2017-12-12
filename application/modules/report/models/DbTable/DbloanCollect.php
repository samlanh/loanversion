<?php
class Report_Model_DbTable_DbloanCollect extends Zend_Db_Table_Abstract
{
      
       protected  $db_name='ln_loanmember_funddetail';
//     public function getUserId(){
//     	$session_user=new Zend_Session_Namespace('authloan');
//     	return $session_user->user_id;
//     }
    public function getAllLnClient($search=null){
    	$db=$this->getAdapter();
    	$start_date = $search['start_date'];
   		$end_date = $search['end_date'];
    	$sql = "SELECT *,
		(SELECT symbol FROM `ln_currency` WHERE id=v_newloancolect.currency_type LIMIT 1) AS currencyname
    	 FROM 
    		v_newloancolect WHERE 1 ";
    	$where ='';
    	
    	$to_date = (empty($search['end_date']))? '1': " date_payment = '".$search['end_date']." 00:00:00'";
    	$where= " AND  ".$to_date;
    	
    	if($search['branch_id']>0){
    		$where.=" AND branch_id = ".$search['branch_id'];
    	}
    	if($search['client_name']>0){
    		$where.=" AND client_id = ".$search['client_name'];
    	}
    	if($search['co_id']>0){
    		$where.=" AND co_id = ".$search['co_id'];
    	}
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = trim(addslashes($search['adv_search']));
    		$s_where[] = " branch_kh LIKE '%{$s_search}%'";
    		$s_where[] = " loan_number LIKE '%{$s_search}%'";
    		$s_where[] = " co_name LIKE '%{$s_search}%'";
    		$s_where[] = " client_name LIKE '%{$s_search}%'";
    		$s_where[] = " principle_after LIKE '%{$s_search}%'";
    		$s_where[] = " total_interest LIKE '%{$s_search}%'";
    		$s_where[] = " loan_number LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	$order=" ORDER BY currency_type ASC, date_payment DESC";
    	return $db->fetchAll($sql.$where.$order);
    }
	public function latepayment($search=null){
		$db=$this->getAdapter();
		$pay_date = $search['payment_date'];
		$late_date = $search['late_date'];
		$sql="SELECT * FROM v_getloancollects WHERE 1";
		$late_pay_date=$late_date-$pay_date;
	}
	
	public function getLoanCollectionByCo($search=null){
		$db = $this->getAdapter();
		$start_date = $search['start_date'];
		$end_date = $search['end_date'];
		try{
			$sql="SELECT 
					  b.`branch_namekh`,
					  CONCAT(co.`co_code`, '-',co.`co_khname`,',',co.`co_firstname`,' ',co.`co_lastname`) AS co_name,
					  c.`receipt_no`,
					  c.`date_input`,
					  c.`principal_amount`,
					  c.`recieve_amount`,
					  c.`return_amount`,
					  c.`penalize_amount`,
					  c.`service_charge`,
					  c.`total_interest`,
					  c.`total_payment`,
					  c.`amount_payment`,
					  c.`total_principal_permonth`,
					  cm.`date_payment`,
					  cm.`principal_permonth`,
					  cm.`remain_capital`,
					  cm.`capital`,
					  cm.`penelize_amount`,
					  cm.`service_charge`,
					  cm.`total_interest`,
					  cm.`total_payment`,
					  cm.`loan_number`,
					  cm.`total_recieve`,
					  cm.`pay_after`,
					  lc.`name_kh`,
					  lc.`phone`,
					  lc.`client_number`,
					  lm.`total_capital`,
					  lm.`interest_rate`,
					  lg.`total_duration`,
					  lg.`date_release`,
					  (SELECT
					     `ln_view`.`name_en`
					   FROM `ln_view`
					   WHERE ((`ln_view`.`type` = 14)
					          AND (`ln_view`.`key_code` = `lg`.`pay_term`))) AS `Term Borrow`
					FROM
					  `ln_client_receipt_money` AS c,
					  `ln_co` AS co,
					  `ln_branch` AS b ,
					  `ln_client_receipt_money_detail` AS cm,
					  `ln_client` AS lc,
					  `ln_loan_member` AS lm,
					  `ln_loan_group` AS lg
					WHERE c.`co_id` = co.`co_id` 
					AND c.id=cm.`crm_id`
					  AND c.`branch_id`=b.`br_id`";
			
			$where ='';
	      	if(!empty($search['advance_search'])){
	      		//print_r($search);
	      		$s_where = array();
	      		$s_search = $search['advance_search'];
	      		$s_where[] = "lcrm.`loan_number` LIKE '%{$s_search}%'";
	      		$s_where[] = " lcrm.`receipt_no` LIKE '%{$s_search}%'";
	      		$s_where[] = " lcrm.`total_payment` LIKE '%{$s_search}%'";
	      		$s_where[] = " lcrm.`total_interest` LIKE '%{$s_search}%'";
	      		$s_where[] = " lcrm.`penalize_amount` LIKE '%{$s_search}%'";
	      		$s_where[] = " lcrm.`service_charge` LIKE '%{$s_search}%'";
	      		$where .=' AND ('.implode(' OR ',$s_where).')';
	      	}
	      	if($search['status']!=""){
	      		$where.= " AND status = ".$search['status'];
	      	}
	      	 
	      	if(!empty($search['start_date']) or !empty($search['end_date'])){
	      		$where.=" c.`date_input` BETWEEN '$start_date' AND '$end_date'";
	      	}
	      	if($search['branch_id']>0){
	      		$where.=" AND lcrm.`branch_id`= ".$search['branch_id'];
	      	}
	      	if($search['co_id']>0){
	      		$where.=" AND co.`co_id`= ".$search['co_id'];
	      	}
	      	if($search['paymnet_type']>0){
	      		$where.=" AND lcrm.`payment_option`= ".$search['paymnet_type'];
	      	}
	      	 
	      	//$where='';
	      	$order = " ORDER BY lcrm.currency_type";
	      	//echo $sql.$where.$order;
	      	return $db->fetchAll($sql.$where.$order);
			
		}catch (Exception $e){
			echo $e->getMessage();
		}
	}
	public function getAllloanArea($search=null){//ក្នុងគ្រា 
		$db = $this->getAdapter();
		
// 		$db = $this->getAdapter();
// 		$where="";
// 		$to_date = (empty($search['end_date']))? '1': " date_release <= '".$search['end_date']." 23:59:59'";
// 		$where.= "  AND ".$to_date;
		
		$sql=" SELECT
  COUNT(lg.g_id) AS new_loan,		
  SUM(`g`.`total_capital`) AS `total_release`,
  SUM(g.interest_rate) AS total_interest_rate,
  `g`.`currency_type`   AS `currency_type`,
  `g`.`client_id`       AS `client_id`,
  (SELECT
     `ln_branch`.`branch_namekh`
   FROM `ln_branch`
   WHERE (`ln_branch`.`br_id` = `g`.`branch_id`)
   LIMIT 1) AS `branch_name`,
  (SELECT
     `ln_currency`.`symbol`
   FROM `ln_currency`
   WHERE (`ln_currency`.`id` = `g`.`currency_type`) LIMIT 1) AS `curr_type`,
  (SELECT
     SUM(`cd`.`principal_permonth`)
   FROM `ln_client_receipt_money_detail` `cd`
   WHERE (`cd`.`loan_number` = `g`.`loan_number`) LIMIT 1) AS `total_payment`,
  `lg`.`co_id`          AS `co_id`,
  (SELECT
     `ln_co`.`co_khname`
   FROM `ln_co`
   WHERE (`ln_co`.`co_id` = `lg`.`co_id`) LIMIT 1) AS `co_name`
FROM (`ln_loan_group` `lg`
   JOIN `ln_loan_member` `g`)
WHERE (`lg`.`g_id` = `g`.`group_id`)
       AND (`g`.`status` = 1)
       AND (`g`.`is_completed` = 0)
       AND (`g`.`is_reschedule` <> 1) ";//IF BAD LOAN STILL GET IT

// 		if(!empty($search['adv_search'])){
// 			$s_where = array();
// 			$s_search = addslashes(trim($search['adv_search']));
// 			$s_where[] = " branch_name LIKE '%{$s_search}%'";
// 			$s_where[] = " loan_number LIKE '%{$s_search}%'";
// 			$s_where[] = " client_number LIKE '%{$s_search}%'";
// 			$s_where[] = " client_kh LIKE '%{$s_search}%'";
// 			$s_where[] = " co_name LIKE '%{$s_search}%'";
// 			$s_where[] = " total_capital LIKE '%{$s_search}%'";
// 			$s_where[] = " total_duration LIKE '%{$s_search}%'";
// 			$s_where[] = " loan_type LIKE '%{$s_search}%'";
// 			$s_where[] = " total_payment LIKE '%{$s_search}%'";
// 			$where .=' AND ('.implode(' OR ',$s_where).')';
// 		}
// 		return $db->fetchAll($sql.$where);
		
		try{

			if(!empty($search['advance_search'])){
				// 				$s_where = array();
				// 				$s_search = $search['advance_search'];
				// 				$s_where[] = "lcrm.`loan_number` LIKE '%{$s_search}%'";
				// 				$s_where[] = " lcrm.`receipt_no` LIKE '%{$s_search}%'";
				// 				$s_where[] = " lcrm.`total_payment` LIKE '%{$s_search}%'";
				// 				$s_where[] = " lcrm.`total_interest` LIKE '%{$s_search}%'";
				// 				$s_where[] = " lcrm.`penalize_amount` LIKE '%{$s_search}%'";
				// 				$s_where[] = " lcrm.`service_charge` LIKE '%{$s_search}%'";
				// 				$where .=' AND ('.implode(' OR ',$s_where).')';
			}
			$order = " GROUP BY `g`.`group_id`,g.currency_type ORDER BY `g`.`currency_type`,lg.`co_id` ASC ";
// 			echo $sql.$order;exit();
			return $db->fetchAll($sql.$order);
		}catch (Exception $e){
			echo $e->getMessage();
		}
	}
	public function getAllOutstadingArea($search=null){
		$db = $this->getAdapter();
		$where="";
		$to_date = (empty($search['end_date']))? '1': " date_release <= '".$search['end_date']." 23:59:59'";
		$where.= "  AND ".$to_date;
	
		$sql="SELECT * FROM v_loanoutstanding WHERE 1 ";//IF BAD LOAN STILL GET IT
		if($search['branch_id']>0){
			$where.=" AND br_id = ".$search['branch_id'];
		}
		if($search['member']>0){
			$where.=" AND client_id = ".$search['member'];
		}
		if($search['co_id']>0){
			$where.=" AND co_id = ".$search['co_id'];
		}
		if(@$search['pay_every']>0){
			if($search['pay_every']==1){
				$pay='Day';
			}elseif($search['pay_every']==2){
				$pay='Week';
			}else{
				$pay='Month';
			}
			$where.= " AND pay_term  LIKE '%{$pay}%'";
		}
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " branch_name LIKE '%{$s_search}%'";
			$s_where[] = " loan_number LIKE '%{$s_search}%'";
			$s_where[] = " client_number LIKE '%{$s_search}%'";
			$s_where[] = " client_kh LIKE '%{$s_search}%'";
			$s_where[] = " co_name LIKE '%{$s_search}%'";
			$s_where[] = " total_capital LIKE '%{$s_search}%'";
			$s_where[] = " total_duration LIKE '%{$s_search}%'";
			$s_where[] = " loan_type LIKE '%{$s_search}%'";
			$s_where[] = " total_payment LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		return $db->fetchAll($sql.$where);
	}
	
	function getAmountReceiveByLoan($loan_number){
		$db = $this->getAdapter();
		$sql="
		SELECT
		crm.`payment_option`,
		`crm`.`total_principal_permonth` AS `principle_amount`,
		(crm.`total_interest`) AS interest,
		(crm.`penalize_amount`) AS penelize,
		(crm.`service_charge`) AS service,
		crm.`return_amount` AS return_amount,
		crm.`recieve_amount` AS amount_recieve,
		(`crm`.`total_payment`) AS `payment`,
		SUM(crmd.`principal_permonth`) AS total_principal_permonth,
		SUM(crmd.`total_payment`) AS total_payment,
		SUM(crmd.`total_interest`) AS total_interest,
		SUM(crmd.`total_recieve`) AS recieve_amount,
		SUM(crmd.`penelize_amount`) AS penelize_amount,
		SUM(crmd.`service_charge`) AS service_charge
		FROM
		`ln_client_receipt_money` AS crm,
		`ln_client_receipt_money_detail` AS crmd
		WHERE crmd.`crm_id`=crm.`id` AND  crmd.`loan_number`='".$loan_number."' GROUP BY crmd.`crm_id` ";
		$principle_amount=0;
		$row =  $db->fetchAll($sql);
		if(!empty($row)){
			 
			$interest=0;
			$alltotal=0;
			foreach ($row as $rs){
				if($rs["payment_option"]==4){
					$principle= $rs["principle_amount"];
					$total_pay = $rs["payment"];
					$interest= $rs["interest"];
					$recieve = $rs["amount_recieve"]-$rs["return_amount"];
					$penelize = $rs["penelize"];
					$service_charge =$rs["service"];
					$is_set=1;
					// 					}
				}else{
					$is_set=0;
					$service_charge = $rs["service_charge"];
					$interest = $rs["total_interest"];
					$principle = $rs["total_principal_permonth"];
					$penelize = $rs["penelize"];
					$recieve = $rs["recieve_amount"]-$rs["return_amount"];
					$total_pay = $rs["total_payment"];
				}
				$new_service = $recieve-$service_charge;
				if($new_service>=0){
					$service = $service_charge;
					$new_penelize = $new_service - $penelize;
					if($new_penelize>=0){
						$penelize_amount =  $penelize;
						$new_interest = $new_penelize - $interest;
						if($new_interest>=0){
							$interest_amount = $interest;
							//echo $interest_amount;exit();
							$new_printciple = $new_interest - $principle;
							if($new_printciple>=0){
								$principle_amount = $principle;
								// 									exit();
							}else{
								$principle_amount = abs($new_interest);
								// 									echo  $principle_amount;
							}
						}else{
							$interest_amount = abs($new_penelize);
							$principle_amount=0;
	
						}
					}else{
						$penelize_amount = abs($new_service);
						$interest =0;
						$principle_amount=0;
							
					}
				}else{
					$service = abs($recieve);
					$penelize_amount = 0;
					$interest =0;
					$principle_amount=0;
				}
				$alltotal = $alltotal+$principle_amount;
			}
		}else{
			$alltotal=0;
		}
		return $alltotal;
	}
	
	 function getReleaseloanByCO($co_id,$currency_id,$search){
			$db =$this->getAdapter();
			$from_date =(empty($search['start_date']))? '1': " l.date_release >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': " l.date_release <= '".$search['end_date']." 23:59:59'";
			$where= " AND ".$from_date." AND ".$to_date;
			$sql=" SELECT 
		    COUNT(l.id) AS new_loan,
			SUM(l.loan_amount) AS total_release,
			(SELECT symbol FROM `ln_currency` WHERE id=l.currency_type limit 1) AS curr_type,
			SUM(l.interest_rate) AS total_interest_rate
			FROM 
			ln_loan AS l
			WHERE 
			 (`l`.`status` = 1)
			AND l.is_badloan=0
			AND (`l`.`is_completed` = 0)			
            AND l.co_id=".$co_id." 
            AND l.currency_type=".$currency_id;	
			$where.=" LIMIT 1";
			return $db->fetchRow($sql.$where);
	}
	public function getALLParBYCO($search=null){
		$db = $this->getAdapter();
		$sql=" SELECT
			  `l`.`branch_id`      AS `branch_id`,
			  `l`.`co_id`          AS `co_id`,
			  (SELECT
			     `ln_co`.`co_khname`
			   FROM `ln_co`
			   WHERE (`ln_co`.`co_id` = `l`.`co_id`) LIMIT 1 ) AS `co_name`,
			  (SELECT
			     `ln_branch`.`branch_namekh`
			   FROM `ln_branch`
			   WHERE (`ln_branch`.`br_id` = `l`.`branch_id`)
			   LIMIT 1) AS `branch_name`,
			   l.loan_number,
			  `l`.`loan_amount`  AS `total_capital`,
			  `l`.`interest_rate`  AS `interest_rate`,
			  `l`.`currency_type`  AS `curr_type`,
			  `l`.`loan_number`    AS `loan_number`,
			  
			  (SELECT SUM(cm.principal_paid) FROM ln_client_receipt_money AS cm WHERE STATUS=1 AND cm.loan_id=l.id) AS principal_paid,
			  (SELECT SUM(cm.interest_paid) FROM ln_client_receipt_money AS cm WHERE STATUS=1 AND cm.loan_id=l.id) AS interest_paid,
			  (SELECT SUM(cm.penalize_paid) FROM ln_client_receipt_money AS cm WHERE STATUS=1 AND cm.loan_id=l.id) AS penalize_paid,
			  (SELECT SUM(cm.service_paid) FROM ln_client_receipt_money AS cm WHERE STATUS=1 AND cm.loan_id=l.id) AS service_paid,
			  (SELECT SUM(total_paymentpaid) FROM ln_client_receipt_money AS cm WHERE STATUS=1 AND cm.loan_id=l.id) AS total_paymentpaid,
			  
			  (SELECT
			     `ln_currency`.`symbol`
			   FROM `ln_currency`
			   WHERE (`ln_currency`.`id` = `l`.`currency_type`) LIMIT 1) AS `currency_type`
			FROM (`ln_loan` `l`)
			WHERE 
			        l.`status` = 1
			       AND (`l`.`is_completed` = 0)
				   AND l.is_badloan=0 ";

		$to_date = (empty($search['end_date']))? '1': " date_release <= '".$search['end_date']." 23:59:59'";
		$where= "  AND ".$to_date;
		
		if(!empty($search['adv_search'])){
			//       		$s_where = array();
			//       		$s_search = addslashes(trim($search['adv_search']));
			//       		$s_where[] = " branch_name LIKE '%{$s_search}%'";
			//       		$s_where[] = " `loan_number` LIKE '%{$s_search}%'";
			//       		$s_where[] = " `client_number` LIKE '%{$s_search}%'";
			//       		$s_where[] = " `name_kh` LIKE '%{$s_search}%'";
			//       		$s_where[] = " `total_capital` LIKE '%{$s_search}%'";
			//       		$s_where[] = " `interest_rate` LIKE '%{$s_search}%'";
			//       		$s_where[] = " `total_duration` LIKE '%{$s_search}%'";
			//       		$s_where[] = " `term_borrow` LIKE '%{$s_search}%'";
			//       		$s_where[] = " `total_principal` LIKE '%{$s_search}%'";
			//       		$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['co_id']>0){
			$where.=" AND `l`.`co_id` = ".$search['co_id'];
		}
		if($search['branch_id']>0){
		     $where.=" AND `l`.`branch_id` = ".$search['branch_id'];
		}
		if($search['currency_type']>0){
		     $where.=" AND `l`.`currency_type` = ".$search['currency_type'];
		}
		$order = " GROUP BY `l`.`id`
	      ORDER BY `l`.`currency_type`,`l`.`co_id` ";
		return $db->fetchAll($sql.$where.$order);
	}
	public function getLoanlateByLoan($loan_number,$search = null){
		$end_date = $search['end_date'];
		$db = $this->getAdapter();
		$sql=" SELECT
		l.`loan_number`,
		l.`loan_amount`,
		l.`interest_rate`,
		l.`date_release`,
		l.`date_line`,
		l.`total_duration`,
		l.`time_collect`,
		l.`currency_type` AS curr_type,
		l.`collect_typeterm`,
		SUM(d.`principal_permonth`) AS total_principal,
		SUM(d.`principle_after`) AS principle_after,
		SUM(d.`total_interest_after`) AS total_interest_after,
		SUM(d.`total_payment_after`) AS total_payment_after,
		SUM(d.`penelize`) AS penelize,
		d.`date_payment` 
		FROM
		`ln_loan_detail` AS d,
		`ln_loan` AS l
		WHERE d.`is_completed` = 0
		AND l.`id` = d.`loan_id`
		AND l.`status` = 1
		AND l.`is_badloan` = 0
		AND l.`is_completed` = 0
		AND d.`status` = 1
		AND d.`is_completed`=0
        AND l.loan_number='".$loan_number."'";
		$where='';
		if(!empty($search['adv_search'])){
			$s_search = addslashes(trim($search['adv_search']));
// 			$s_where[] = " b.branch_namekh LIKE '%{$s_search}%'";
// 			$s_where[] = "  lm.`currency_type` LIKE '%{$s_search}%'";
// 			$s_where[] = " lm.loan_number LIKE '%{$s_search}%'";
// 			$s_where[] = " c.client_number LIKE '%{$s_search}%'";
// 			$s_where[] = " c.name_kh LIKE '%{$s_search}%'";
// 			$s_where[] = " f.principle_after LIKE '%{$s_search}%'";
// 			$s_where[] = " f.principal_permonth LIKE '%{$s_search}%'";
// 			$s_where[] = " f.total_interest_after LIKE '%{$s_search}%'";
// 			$s_where[] = " f.total_payment_after LIKE '%{$s_search}%'";
// 			$where .=' AND ('.implode(' OR ',$s_where).')';
		}		
		if(!empty($search['end_date'])){
			$where.=" AND d.date_payment < '$end_date'";
		}
		if($search['branch_id']>0){
			$where.=" AND l.`branch_id` = ".$search['branch_id'];
		}		
		$group_by = " GROUP BY l.`id` ORDER BY d.`date_payment` ASC LIMIT 1";
		return $db->fetchRow($sql.$where.$group_by);
	}
	
}

