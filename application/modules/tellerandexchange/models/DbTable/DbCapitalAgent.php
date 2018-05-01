<?php
class Tellerandexchange_Model_DbTable_DbCapitalAgent extends Zend_Db_Table_Abstract
{
	protected $_name = 'ln_exchange_capital_detail';
	
	function getCapitalAgency($search){
		$db = $this->getAdapter();
		$sql="SELECT c.id,
		(SELECT CONCAT(u.last_name,' ',u.first_name) FROM `rms_users` AS u WHERE u.id = c.`agent_id` LIMIT 1) AS agency,
		(SELECT CONCAT(c.curr_namekh,' ',c.symbol ) FROM `ln_currency` AS c WHERE c.id = c.`currency_id` LIMIT 1) AS currencyKH,
		c.`amount`,c.`for_date`
		FROM `ln_exchange_capital_detail` AS c WHERE 1";
		$from_date =(empty($search['from_date']))? '1': " c.`for_date` >= '".$search['from_date']." 00:00:00'";
		$to_date = (empty($search['to_date']))? '1': " c.`for_date` <= '".$search['to_date']." 23:59:59'";
		$sql.= " AND ".$from_date." AND ".$to_date;
		if($search['agent_id']>0){
			$sql.=" AND c.agent_id = ".$search['agent_id'];
		}
		$sql.=" ORDER BY c.id DESC";
		return $db->fetchAll($sql);
	}
	function getCurrencyRow($data){
		$db = $this->getAdapter();
		$curId= $data['currency'];
		$sql="SELECT c.* FROM `ln_currency` AS c WHERE c.`id` =$curId LIMIT 1";
		$row = $db->fetchRow($sql);
		
		$baseurl= Zend_Controller_Front::getInstance()->getBaseUrl();
		$tem='';
		$newrowid='';
		if (!empty($row)) {
			$newrowid = $row['id'];
			$tem.='
	 				<td align="center">'.$data['record_no'].'</td>
					 <td align="center">
					 	<label><strong>'.$row['curr_namekh'].' '.$row['symbol'].'</strong></label>
					 	<input type="hidden" class="fullside" dojoType="dijit.form.TextBox" required="required"  name="currency_id'.$row['id'].'" id="currency_id'.$row['id'].'" value="'.$row['id'].'" style="text-align: center;" >
					 </td>
					 <td><input type="text" class="fullside" dojoType="dijit.form.NumberTextBox" required="required"  name="amount'.$row['id'].'" id="amount'.$row['id'].'" value="0" style="text-align: center;" ></td>
					 <td width="30px" align="center"><img style="cursor: pointer;" onclick="newdeleteRecord('.$row['id'].')" src="'.$baseurl.'/images/Delete_16.png"></td>
				';
		}
		$array = array('stringrow'=>$tem,'newrowid'=>$newrowid);
		return $array;
	}
	function addCapitalCurrency($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try {
			$dbg = new Application_Model_DbTable_DbGlobal();
			$useId = $dbg->getUserId();
			$ids = explode(',', $data['identity']);
			foreach ($ids as $i){
				$amount = $data['amount'.$i];
				$arrs = array(
						'currency_id'=>$data['currency_id'.$i],
						'for_date'=>date("Y-m-d"),
						'amount'=>$amount,
						'agent_id'=>$data['agent_id'],
						'user_id'=>$useId,
				);
				$this->_name ='ln_exchange_capital_detail';
				$this->insert($arrs);
				
				$this->addCurrentCapital($amount, $data['agent_id'], $data['currency_id'.$i]);
			}
			return  $db->commit();
    	} catch (Exception $e) {
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    	}
	}
	function getCapitalByID($id){
		$db = $this->getAdapter();
		$sql="SELECT c.*, (SELECT CONCAT(u.last_name,' ',u.first_name) FROM `rms_users` AS u WHERE u.id = c.`agent_id` LIMIT 1) AS agency, 
			(SELECT CONCAT(c.curr_namekh,' ',c.symbol ) FROM `ln_currency` AS c WHERE c.id = c.`currency_id` LIMIT 1) AS currencyKH
			 FROM `ln_exchange_capital_detail` AS c WHERE c.`id` =$id
			ORDER BY c.id DESC LIMIT 1";
		return $db->fetchRow($sql);
	}
	function getCapitalByAgentIDAndDate($row){
		$date = date("Y-m-d",strtotime($row['for_date']));
		$agentId = $row['agent_id'];
		$db = $this->getAdapter();
		$sql="SELECT c.*, (SELECT CONCAT(u.last_name,' ',u.first_name) FROM `rms_users` AS u WHERE u.id = c.`agent_id` LIMIT 1) AS agency,
		(SELECT CONCAT(c.curr_namekh,' ',c.symbol ) FROM `ln_currency` AS c WHERE c.id = c.`currency_id` LIMIT 1) AS currencyKH
		FROM `ln_exchange_capital_detail` AS c WHERE DATE_FORMAT(c.`for_date`,'%Y-%m-%d') = '$date' AND  c.`agent_id` =$agentId ORDER BY c.id DESC";
		return $db->fetchAll($sql);
	}
	function getCurrencyRowEdit($data){
		$db = $this->getAdapter();
		$curId= $data['currency'];
		$sql="SELECT c.* FROM `ln_currency` AS c WHERE c.`id` =$curId LIMIT 1";
		$row = $db->fetchRow($sql);
	
		$baseurl= Zend_Controller_Front::getInstance()->getBaseUrl();
		$tem='';
		$newrowid='';
		if (!empty($row)) {
			$newrowid = $data['record_no'];
			$tem.='
			<td align="center">'.$data['record_no'].'</td>
			<td align="center">
			<label><strong>'.$row['curr_namekh'].' '.$row['symbol'].'</strong></label>
			<input type="hidden" class="fullside" dojoType="dijit.form.TextBox" required="required"  name="currency_id'.$newrowid.'" id="currency_id'.$newrowid.'" value="'.$row['id'].'" style="text-align: center;" >
			</td>
			<td><input type="text" class="fullside" dojoType="dijit.form.NumberTextBox" required="required"  name="amount'.$newrowid.'" id="amount'.$newrowid.'" value="0" style="text-align: center;" ></td>
			<td width="30px" align="center"><img style="cursor: pointer;" onclick="newdeleteRecord('.$newrowid.')" src="'.$baseurl.'/images/Delete_16.png"></td>
			';
		}
		$array = array('stringrow'=>$tem,'newrowid'=>$newrowid);
		return $array;
	}
	function editCapitalCurrency($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try {
			$dbg = new Application_Model_DbTable_DbGlobal();
			$useId = $dbg->getUserId();
			
			$ids = explode(',', $data['identity']);
			$iddetail='';
			foreach ($ids as $i){
				if (empty($iddetail)){
					if (!empty($data['detailid'.$i])){
						$iddetail=$data['detailid'.$i];
					}
				}
				else{
					if (!empty($data['detailid'.$i])){
						$iddetail=$iddetail.",".$data['detailid'.$i];
					}
				}
			}
// 			echo $iddetail;exit();
			$this->_name="ln_exchange_capital_detail";
			$where1=" ";
			if (!empty($iddetail)){
				$where1.=" id NOT IN (".$iddetail.")";
				$this->delete($where1);
			}
			
			
			foreach ($ids as $i){
				if (!empty($data['detailid'.$i])){
					$arrs = array(
							'currency_id'=>$data['currency_id'.$i],
// 							'for_date'=>date("Y-m-d"),
							'amount'=>$data['amount'.$i],
							'agent_id'=>$data['agent_id'],
							'user_id'=>$useId,
					);
					$this->_name ='ln_exchange_capital_detail';
					$where = "id = ".$data['detailid'.$i];
					$this->update($arrs, $where);
				}else{
					$arrs = array(
							'currency_id'=>$data['currency_id'.$i],
							'for_date'=>date("Y-m-d"),
							'amount'=>$data['amount'.$i],
							'agent_id'=>$data['agent_id'],
							'user_id'=>$useId,
					);
					$this->_name ='ln_exchange_capital_detail';
					$this->insert($arrs);
				}
			}
			return  $db->commit();
		} catch (Exception $e) {
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
	}
	
	function withdrawalcapital($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try {
			$dbg = new Application_Model_DbTable_DbGlobal();
			$useId = $dbg->getUserId();
			$ids = explode(',', $data['identity']);
			foreach ($ids as $i){
				$amount = "-".$data['amount'.$i];
				$arrs = array(
						'currency_id'=>$data['currency_id'.$i],
						'for_date'=>date("Y-m-d"),
						'amount'=>$amount,
						'agent_id'=>$data['agent_id'],
						'user_id'=>$useId,
				);
				$this->_name ='ln_exchange_capital_detail';
				$this->insert($arrs);
				
				$this->addCurrentCapital($amount, $data['agent_id'], $data['currency_id'.$i]);
			}
			return  $db->commit();
		} catch (Exception $e) {
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
	}
	
	function addCurrentCapital($amount,$agency,$currency){
		try {
			$capital = $this->getCapitalByAgent($agency, $currency);
			if (!empty($capital)){
				$totalAmount = $amount+$capital['amount'];
				$arr = array(
// 						'currency_id'=>$currency,
						'for_date'=>date("Y-m-d"),
						'amount'=>$totalAmount,
// 						'agent_id'=>$agency,
				);
				$this->_name ='ln_exchange_current_capital';
				$where=" currency_id = $currency AND agent_id=$agency";
				$this->update($arr, $where);
			}else{
				$arr = array(
						'currency_id'=>$currency,
						'for_date'=>date("Y-m-d"),
						'amount'=>$amount,
						'agent_id'=>$agency,
				);
				$this->_name ='ln_exchange_current_capital';
				$this->insert($arr);
			}
		} catch (Exception $e) {
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	function getCapitalByAgent($agent,$currency){
		$db = $this->getAdapter();
		$sql="SELECT cp.* FROM `ln_exchange_current_capital` AS cp WHERE cp.`agent_id`=$agent AND cp.`currency_id`=$currency LIMIT 1";
		return  $db->fetchRow($sql);
	}
	
	function getCurrencyRowEditWithdrawal($data){
		$db = $this->getAdapter();
		$curId= $data['currency'];
		$sql="SELECT c.* FROM `ln_currency` AS c WHERE c.`id` =$curId LIMIT 1";
		$row = $db->fetchRow($sql);
	
		$agent_id= $data['agent_id'];
		$currentBalanceAgent = $this->getCurrentCapitalByAgent($curId,$agent_id);
		$currentAmount = empty($currentBalanceAgent['amount'])?0:$currentBalanceAgent['amount'];
		$baseurl= Zend_Controller_Front::getInstance()->getBaseUrl();
		$tem='';
		$newrowid='';
		if (!empty($row)) {
			$newrowid = $data['record_no'];
			$tem.='
			<td align="center">'.$data['record_no'].'</td>
			<td align="center">
			<label><strong>'.$currentAmount.' '.$row['curr_namekh'].' '.$row['symbol'].'</strong></label>
			<input type="hidden" class="fullside" dojoType="dijit.form.TextBox" required="required"  name="currenntBalance'.$newrowid.'" id="currenntBalance'.$newrowid.'" value="'.$currentAmount.'" style="text-align: center;" >
			<input type="hidden" class="fullside" dojoType="dijit.form.TextBox" required="required"  name="currency_id'.$newrowid.'" id="currency_id'.$newrowid.'" value="'.$row['id'].'" style="text-align: center;" >
			</td>
			<td><input type="text" class="fullside" dojoType="dijit.form.NumberTextBox" required="required" onKeyup="checkMaxBalance('.$newrowid.');"  name="amount'.$newrowid.'" id="amount'.$newrowid.'" value="0" style="text-align: center;" ></td>
			<td width="30px" align="center"><img style="cursor: pointer;" onclick="newdeleteRecord('.$newrowid.')" src="'.$baseurl.'/images/Delete_16.png"></td>
			';
		}
		$array = array('stringrow'=>$tem,'newrowid'=>$newrowid);
		return $array;
	}
	function getCurrentCapitalByAgent($curId,$agent){
		$db = $this->getAdapter();
		$sql="SELECT c.* FROM `ln_exchange_current_capital` AS c WHERE c.`currency_id` = $curId AND c.`agent_id` = $agent LIMIT 1";
		return $db->fetchRow($sql);
	}
}

	

?>