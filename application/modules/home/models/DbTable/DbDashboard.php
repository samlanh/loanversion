<?php

class Home_Model_DbTable_DbDashboard extends Zend_Db_Table_Abstract
{

//     protected $_name = 'ln_properties';
//     public function getUserId(){
//     	$session_user=new Zend_Session_Namespace('authloan');
//     	return $session_user->user_id;
//     }
	
	function CountAllClient(){
		$db = $this->getAdapter();
		$sql="SELECT COUNT(p.`client_id`) AS total
			FROM `ln_client` AS p 
			WHERE p.`status` =1 ";
		return $db->fetchOne($sql);
	}
	
	function CountAllAgency(){
		$db = $this->getAdapter();
		$sql="SELECT COUNT(p.`co_id`) AS total
		FROM `ln_co` AS p 
		WHERE p.`status` =1 ";
		return $db->fetchOne($sql);
	}
	
	function CountAllLoan(){
		$db = $this->getAdapter();
		$sql="SELECT COUNT(l.`id`) AS countLoan, SUM(l.`loan_amount`) AS total,l.`currency_type`
		FROM `ln_loan` AS l 
		WHERE l.`status`=1
		GROUP BY l.`currency_type` ";
		return $db->fetchAll($sql);
	}
	
	function CountAllLoanComplete(){
		$db = $this->getAdapter();
		$sql="SELECT COUNT(l.`id`) AS countLoan, SUM(l.`loan_amount`) AS total,l.`currency_type`
		FROM `ln_loan` AS l 
		WHERE l.`status`=1 AND l.`is_completed`=1
		GROUP BY l.`currency_type` ";
		return $db->fetchAll($sql);
	}
	
	function CountAllBadLoan(){
		$db = $this->getAdapter();
		$sql="SELECT COUNT(l.`id`) AS countLoan
		FROM `ln_loan` AS l 
		WHERE l.`status`=1 AND l.`is_badloan`=1 ";
		return $db->fetchOne($sql);
	}
	
	function TotalExpense(){
		$db = $this->getAdapter();
		$sql="SELECT SUM(p.`total_amount`) AS totalAmount,p.`curr_type`
			FROM `ln_income_expense` AS p 
			WHERE p.`status` =1
			GROUP BY p.`curr_type` ";
		return $db->fetchAll($sql);
	}
	function getTotalOtherIncome(){
		$db = $this->getAdapter();
		$sql="SELECT SUM(i.`total_amount`) AS total,i.`curr_type`
		FROM ln_income AS i
		WHERE i.`status`=1 GROUP BY i.`curr_type` ";
		return $db->fetchAll($sql);
	}
	
	function getTotalLoanCollectIncome(){
		$db = $this->getAdapter();
		$sql="SELECT SUM(v.`amount_recieve`) AS total, v.`currency_type`
		 FROM v_getcollectmoney AS v WHERE v.status=1 
		 GROUP BY v.`currency_type`";
		return $db->fetchAll($sql);
	}
	function getLoanDisbursPerMonth($yearMonth){
		$db = $this->getAdapter();
		$sql="SELECT COUNT(l.`id`) AS countLoan, SUM(l.`loan_amount`) AS total,l.`currency_type`
		FROM `ln_loan` AS l 
		WHERE l.`status`=1 AND
		DATE_FORMAT(l.`date_release`, '%Y-%m')='$yearMonth'
		GROUP BY l.`currency_type`";
		$row = $db->fetchAll($sql);
		$reilsLoan = 0;
		$dollarLoan = 0;
		$bahtLoan = 0;
		if (!empty($row)) foreach ($row as $rs){
			if ($rs['currency_type']==1){
				$reilsLoan = $rs['countLoan'];
			}else if ($rs['currency_type']==2){
				$dollarLoan = $rs['countLoan'];
			}else if ($rs['currency_type']==3){
				$bahtLoan = $rs['countLoan'];
			}
		}
		$array = array('riels'=>$reilsLoan,'dollar'=>$dollarLoan,'bath'=>$bahtLoan);
		return $array;
	}
	function getCountPawnShop($completed=null,$dach=null){
		$db = $this->getAdapter();
		$sql="SELECT COUNT(p.`id`) AS countPawnShop 
		FROM ln_pawnshop AS p
		WHERE p.`status`=1";
		if (!empty($completed)){
			$sql.=" AND p.`is_completed`=1";
		}
		if (!empty($dach)){
			$sql.=" AND p.`is_dach`=1";
		}
		$sql.=" LIMIT 1";
		return $db->fetchOne($sql);
	}
	
}