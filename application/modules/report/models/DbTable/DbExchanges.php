<?php
class Report_Model_DbTable_DbExchanges extends Zend_Db_Table_Abstract
{
      
      
//     public function getUserId(){
//     	$session_user=new Zend_Session_Namespace('authloan');
//     	return $session_user->user_id;
//     }
//     public function getPaymentSchedule($id){
//     	$db=$this->getAdapter();
//     	$sql = "SELECT * FROM `ln_loanmember_funddetail` WHERE member_id= $id";
//     	return $db->fetchAll($sql);
//     }
//     public function getExchanges(){
//     	$db=$this->getAdapter();
//     	$sql="SELECT penelize , service_charge From service_charge";
//     }
//     public function getAllClientPaymentListRpt(){
//     	$sql="SELECT member_id,client_id,total_capital,interest_rate,total_capital,
//     	loan_purpose,payment_method,currency_type,
//     	admin_fee,branch_id,status FROM `ln_loan_member`";
//     	$db = $this->getAdapter();
//     	return $db->fetchAll($sql);
//     }
	function getCurrentCapitalAgent($search){
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
		if ($search['agent_id']>0){
			$sql.=" AND cb.`agent_id`=".$search['agent_id'];
		}
		$sql.=" Order BY cb.`agent_id` ASC";
		return $db->fetchAll($sql);
	}
	public function getAllxchange($search = null){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM `v_xchange` WHERE 1";
		$where ='';
		$from_date =(empty($search['start_date']))? '1': "statusDate >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "statusDate <= '".$search['end_date']." 23:59:59'";
		$where.= " AND ".$from_date." AND ".$to_date;
		 
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = trim(addcslashes($search['adv_search']));
			$s_where[] = " changedAmount LIKE '%{$s_search}%'";
			$s_where[]=" fromAmount LIKE '%{$s_search}%'";
			$s_where[] = " rate LIKE '%{$s_search}%'";
			$s_where[]=" recieptNo LIKE '%{$s_search}%'";
			$s_where[] = " recievedAmount LIKE '%{$s_search}%'";
			$s_where[]=" status_in LIKE '%{$s_search}%'";
			$s_where[] = " statusDate LIKE '%{$s_search}%'";
			$s_where[]=" toAmount LIKE '%{$s_search}%'";
			$s_where[]=" toAmountType LIKE '%{$s_search}%'";
			$s_where[]=" fromAmountType LIKE '%{$s_search}%'";
			$s_where[]=" from_to LIKE '%{$s_search}%'";
			$s_where[]=" recievedType LIKE '%{$s_search}%'";
			$s_where[]=" specail_customer LIKE '%{$s_search}%'";
	
			$where .=' AND ('.implode(' OR ',$s_where).')';
			//       	if($search['branch_id']>0){
			//       		$where.=" AND branch_id = ".$search['branch_id'];
			//       	}
			//       	if($search['client_name']>0){
			//       		$where.=" AND client_id = ".$search['client_name'];
			//       	}
		}
		$order=" ORDER BY id DESC";
		//       	echo $sql.$where;
		return $db->fetchAll($sql.$where.$order);
	}
	public function getAllxchangeBYID($id){
		$db = $this->getAdapter();
		$sql = "SELECT v.*,
		(SELECT c.`curr_namekh` FROM ln_currency AS c WHERE c.symbol = v.`fromAmountType`) AS fromTxtType,
		(SELECT c.`curr_namekh` FROM ln_currency AS c WHERE c.symbol = v.`toAmountType`) AS toTxtType
		FROM `v_xchange` AS v WHERE v.`id`=$id LIMIT 1";
		return $db->fetchRow($sql);
	}
	function getTotalExchangeBuyIn($search){
		$db = $this->getAdapter();
		$sql="SELECT SUM(v.`fromAmount`) AS totalFromAmount,
		(SELECT c.`curr_namekh` FROM ln_currency AS c WHERE c.symbol = v.`fromAmountType`) AS fromTxtType,
		(SELECT c.`curr_namekh` FROM ln_currency AS c WHERE c.symbol = v.`toAmountType`) AS toTxtType,
		v.*
		 FROM `v_xchange` AS v WHERE 1
		";
		$from_date =(empty($search['start_date']))? '1': " v.statusDate >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " v.statusDate <= '".$search['end_date']." 23:59:59'";
		$where="";
		$where.= " AND ".$from_date." AND ".$to_date;
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = trim(addcslashes($search['adv_search']));
			$s_where[] = " v.changedAmount LIKE '%{$s_search}%'";
			$s_where[]=" v.fromAmount LIKE '%{$s_search}%'";
			$s_where[] = " v.rate LIKE '%{$s_search}%'";
			$s_where[]=" v.recieptNo LIKE '%{$s_search}%'";
			$s_where[] = " v.recievedAmount LIKE '%{$s_search}%'";
			$s_where[]=" v.status_in LIKE '%{$s_search}%'";
			$s_where[] = " v.statusDate LIKE '%{$s_search}%'";
			$s_where[]=" v.toAmount LIKE '%{$s_search}%'";
			$s_where[]=" v.toAmountType LIKE '%{$s_search}%'";
			$s_where[]=" v.fromAmountType LIKE '%{$s_search}%'";
			$s_where[]=" v.from_to LIKE '%{$s_search}%'";
			$s_where[]=" v.recievedType LIKE '%{$s_search}%'";
			$s_where[]=" v.specail_customer LIKE '%{$s_search}%'";
		
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		$where.="	GROUP BY v.`fromAmountType`
		ORDER BY v.`fromAmountType` ASC";
		return $db->fetchAll($sql.$where);
	}
	function getTotalExchangeSellout($search){
		$db = $this->getAdapter();
		$sql="SELECT SUM(v.`toAmount`) AS totalToAmount,
		(SELECT c.`curr_namekh` FROM ln_currency AS c WHERE c.symbol = v.`fromAmountType`) AS fromTxtType,
		(SELECT c.`curr_namekh` FROM ln_currency AS c WHERE c.symbol = v.`toAmountType`) AS toTxtType,
		v.*
		FROM `v_xchange` AS v WHERE 1
		";
		$from_date =(empty($search['start_date']))? '1': " v.statusDate >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " v.statusDate <= '".$search['end_date']." 23:59:59'";
		$where="";
		$where.= " AND ".$from_date." AND ".$to_date;
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = trim(addcslashes($search['adv_search']));
			$s_where[] = " v.changedAmount LIKE '%{$s_search}%'";
			$s_where[]=" v.fromAmount LIKE '%{$s_search}%'";
			$s_where[] = " v.rate LIKE '%{$s_search}%'";
			$s_where[]=" v.recieptNo LIKE '%{$s_search}%'";
			$s_where[] = " v.recievedAmount LIKE '%{$s_search}%'";
			$s_where[]=" v.status_in LIKE '%{$s_search}%'";
			$s_where[] = " v.statusDate LIKE '%{$s_search}%'";
			$s_where[]=" v.toAmount LIKE '%{$s_search}%'";
			$s_where[]=" v.toAmountType LIKE '%{$s_search}%'";
			$s_where[]=" v.fromAmountType LIKE '%{$s_search}%'";
			$s_where[]=" v.from_to LIKE '%{$s_search}%'";
			$s_where[]=" v.recievedType LIKE '%{$s_search}%'";
			$s_where[]=" v.specail_customer LIKE '%{$s_search}%'";
	
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		$where.="	GROUP BY v.`toAmountType`
		ORDER BY v.`toAmountType` ASC";
		return $db->fetchAll($sql.$where);
	}
	function getCapitalSummary($search){
		$db = $this->getAdapter();
		$sql="SELECT ed.*,
			(SELECT c.`curr_namekh` FROM ln_currency AS c WHERE c.id = ed.`currency_id` LIMIT 1) AS currencytitleKH,
			(SELECT c.`symbol` FROM ln_currency AS c WHERE c.id = ed.`currency_id` LIMIT 1) AS symbol,
			(SELECT CONCAT(u.last_name,' ',u.first_name) FROM `rms_users` AS u WHERE u.id = ed.`agent_id` LIMIT 1) AS agentName		 
			 FROM `ln_exchange_capital_detail` AS ed WHERE 1";
		$from_date =(empty($search['start_date']))? '1': " ed.for_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " ed.for_date <= '".$search['end_date']." 23:59:59'";
		$where="";
		$where.= " AND ".$from_date." AND ".$to_date;
		if ($search['agent_id']>0){
			$sql.=" AND ed.`agent_id`=".$search['agent_id'];
		}
		return $db->fetchAll($sql.$where);
	}
	
	function getCurrencyFromXchage(){
		$db = $this->getAdapter();
		$sql="SELECT 
		e.`fromAmountType` AS symbol,
		(SELECT c.id FROM `ln_currency` AS c WHERE c.symbol = e.`fromAmountType` LIMIT 1) AS CurrID,
		(SELECT c.curr_namekh FROM `ln_currency` AS c WHERE c.symbol = e.`fromAmountType` LIMIT 1) AS CurrTitleKH,
		(SELECT c.curr_nameen FROM `ln_currency` AS c WHERE c.symbol = e.`fromAmountType` LIMIT 1) AS CurrTitleEN
		FROM `ln_xchange` AS e
		GROUP BY e.`fromAmountType`	";
		$row = $db->fetchAll($sql);
		
		$sqlTo="SELECT 
		e.`toAmountType` AS symbol,
		(SELECT c.id FROM `ln_currency` AS c WHERE c.symbol = e.`toAmountType` LIMIT 1) AS CurrID,
		(SELECT c.curr_namekh FROM `ln_currency` AS c WHERE c.symbol = e.`toAmountType` LIMIT 1) AS CurrTitleKH,
		(SELECT c.curr_nameen FROM `ln_currency` AS c WHERE c.symbol = e.`toAmountType` LIMIT 1) AS CurrTitleEN
		FROM `ln_xchange` AS e
		WHERE e.`toAmountType` NOT IN (SELECT ex.`fromAmountType` FROM `ln_xchange` AS ex  GROUP BY ex.`fromAmountType`)
		GROUP BY e.`toAmountType`";
		$rowTo = $db->fetchAll($sqlTo);
		if (!empty($rowTo)){
			array_push($row, $rowTo[0]);
		}
		
		$idCurr="";
		foreach ($row as $curency){
			if (!empty($curency['CurrID'])){
				if (empty($idCurr)){
					$idCurr = $curency['CurrID'];
				}else{
					$idCurr = $idCurr.",".$curency['CurrID'];
				}
			}
		}
		$sqlCurrencyInCurrentCapital = "
		SELECT 
			cb.currency_id AS CurrID,
			(SELECT c.symbol FROM `ln_currency` AS c WHERE c.id = cb.`currency_id` LIMIT 1) AS symbol,
			(SELECT c.curr_namekh FROM `ln_currency` AS c WHERE c.id = cb.`currency_id` LIMIT 1) AS CurrTitleKH,
			(SELECT c.curr_nameen FROM `ln_currency` AS c WHERE c.id = cb.`currency_id` LIMIT 1) AS CurrTitleEN
			 FROM `ln_exchange_current_capital` AS cb
			 WHERE 	 1 ";
		if (!empty($idCurr)){
			$sqlCurrencyInCurrentCapital.=" AND cb.`currency_id` NOT IN ($idCurr)";
		}
		$sqlCurrencyInCurrentCapital.=" ORDER BY cb.`currency_id` ASC";
		$rowCPcurr = $db->fetchAll($sqlCurrencyInCurrentCapital);
		if (!empty($rowCPcurr)){
			array_push($row, $rowCPcurr[0]);
		}
		return $row;
	}
	function getTotalExchangeBuyCurrency($currency,$search){
		$db = $this->getAdapter();
		$sql="SELECT 
		SUM(e.`fromAmount`) AS totalBuy
		FROM `ln_xchange` AS e
		WHERE e.`fromAmountType`='$currency'";
		$from_date =(empty($search['start_date']))? '1': " e.`statusDate` >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " e.`statusDate` <= '".$search['end_date']." 23:59:59'";
		$sql.= " AND ".$from_date." AND ".$to_date;
		if ($search['agent_id']>0){
			$sql.=" AND e.`userid`=".$search['agent_id'];
		}
		return $db->fetchOne($sql);
	}
	function getTotalExchangeSellCurrency($currency,$search){
		$db = $this->getAdapter();
		$sql="SELECT
		SUM(e.`toAmount`) AS totalsell
		FROM `ln_xchange` AS e
		WHERE e.`toAmountType`='$currency'";
		$from_date =(empty($search['start_date']))? '1': " e.`statusDate` >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " e.`statusDate` <= '".$search['end_date']." 23:59:59'";
		$sql.= " AND ".$from_date." AND ".$to_date;
		if ($search['agent_id']>0){
			$sql.=" AND e.`userid`=".$search['agent_id'];
		}
		return $db->fetchOne($sql);
	}
	
	function getTotalWithdrawalByCurrency($currency,$search){
		$db = $this->getAdapter();
		$sql="SELECT 
			SUM(ed.`amount`) AS totalWithdraw 
			FROM `ln_exchange_capital_detail` AS ed
			WHERE ed.`amount` <= 0
			AND ed.`currency_id` =$currency";
		$from_date =(empty($search['start_date']))? '1': " ed.`for_date` >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " ed.`for_date` <= '".$search['end_date']." 23:59:59'";
		$sql.= " AND ".$from_date." AND ".$to_date;
		if ($search['agent_id']>0){
			$sql.=" AND ed.`agent_id`=".$search['agent_id'];
		}
		return $db->fetchOne($sql);
	}
	function getTotalDepositeByCurrency($currency,$search){
		$db = $this->getAdapter();
		$sql="SELECT
		SUM(ed.`amount`) AS totalDeposite
		FROM `ln_exchange_capital_detail` AS ed
		WHERE ed.`amount` >= 0
		AND ed.`currency_id` =$currency";
		$from_date =(empty($search['start_date']))? '1': " ed.`for_date` >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " ed.`for_date` <= '".$search['end_date']." 23:59:59'";
		$sql.= " AND ".$from_date." AND ".$to_date;
		if ($search['agent_id']>0){
			$sql.=" AND ed.`agent_id`=".$search['agent_id'];
		}
		return $db->fetchOne($sql);
	}
}

