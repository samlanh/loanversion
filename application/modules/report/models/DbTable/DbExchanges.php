<?php
class Report_Model_DbTable_DbExchanges extends Zend_Db_Table_Abstract
{
      
      
//     public function getUserId(){
//     	$session_user=new Zend_Session_Namespace('authloan');
//     	return $session_user->user_id;
//     }
    public function getPaymentSchedule($id){
    	$db=$this->getAdapter();
    	$sql = "SELECT * FROM `ln_loanmember_funddetail` WHERE member_id= $id";
    	return $db->fetchAll($sql);
    }
    public function getExchanges(){
    	$db=$this->getAdapter();
    	$sql="SELECT penelize , service_charge From service_charge";
    }
//     public function getAllClientPaymentListRpt(){
//     	$sql="SELECT member_id,client_id,total_capital,interest_rate,total_capital,
//     	loan_purpose,payment_method,currency_type,
//     	admin_fee,branch_id,status FROM `ln_loan_member`";
//     	$db = $this->getAdapter();
//     	return $db->fetchAll($sql);
//     }
	function getDailyCurrentCapital($search){
		$db=$this->getAdapter();
		$sql="SELECT cb.*,
			(SELECT c.curr_namekh FROM `ln_currency` AS c WHERE c.id = cb.`currency_id` LIMIT 1) AS currTitleKH,
			(SELECT c.curr_nameen FROM `ln_currency` AS c WHERE c.id = cb.`currency_id` LIMIT 1) AS currTitleEN,
			(SELECT c.symbol FROM `ln_currency` AS c WHERE c.id = cb.`currency_id` LIMIT 1) AS symbol,
			(SELECT CONCAT(u.last_name,' ',u.first_name) FROM `rms_users` AS u WHERE u.id = cb.`agent_id` LIMIT 1) AS agentName
			 FROM `ln_exchange_current_capital` AS cb
			";
		$from_date =(empty($search['start_date']))? '1': "cb.for_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "cb.for_date <= '".$search['end_date']." 23:59:59'";
		$sql.= " WHERE ".$from_date." AND ".$to_date;
		return $db->fetchAll($sql);
	}
	function getTotalDailyCurrentCapital($search){
		$db=$this->getAdapter();
		$sql="SELECT cb.*,
			SUM(cb.`amount`) AS total,
			(SELECT c.curr_namekh FROM `ln_currency` AS c WHERE c.id = cb.`currency_id` LIMIT 1) AS currTitleKH,
			(SELECT c.curr_nameen FROM `ln_currency` AS c WHERE c.id = cb.`currency_id` LIMIT 1) AS currTitleEN,
			(SELECT c.symbol FROM `ln_currency` AS c WHERE c.id = cb.`currency_id` LIMIT 1) AS symbol,
			(SELECT CONCAT(u.last_name,' ',u.first_name) FROM `rms_users` AS u WHERE u.id = cb.`agent_id` LIMIT 1) AS agentName
			 FROM `ln_exchange_current_capital` AS cb
		";
		$from_date =(empty($search['start_date']))? '1': "cb.for_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "cb.for_date <= '".$search['end_date']." 23:59:59'";
		$sql.= " WHERE ".$from_date." AND ".$to_date;
		$sql.=" GROUP BY cb.`currency_id`";
		return $db->fetchAll($sql);
	}
	
}

