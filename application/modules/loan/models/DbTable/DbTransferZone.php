<?php

class Loan_Model_DbTable_DbTransferZone extends Zend_Db_Table_Abstract
{
	protected $_name = 'ln_tranfser_zone';
    public function getzoneinfo(){
    	$db = $this->getAdapter();
    	$sql = "SELECT zone_id ,zone_name FROM `ln_zone` WHERE zone_name!='' ";
    	return $db->fetchAll($sql);
    }
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authloan');
    	return $session_user->user_id;
    }
    public function getAllinfoZone($search = null){
    	$db = $this->getAdapter();
    	$sql = 'SELECT t.id,
				(SELECT branch_namekh FROM `ln_branch` WHERE br_id =t.branch_id LIMIT 1) AS branch_name,
				(SELECT zone_name FROM `ln_zone` WHERE  zone_id=t.from_zone LIMIT 1) AS from_zone,
				(SELECT co_khname FROM `ln_co` WHERE ln_co.co_id=t.to_co) AS co_name,
				date,note,
				(SELECT `name_en` FROM `ln_view` WHERE TYPE = 3 AND key_code = t.status ) AS status
				FROM `ln_tranfser_zone` AS t';
    	$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
    	$where = " WHERE ".$from_date." AND ".$to_date;
    	
    	$order=" ORDER BY t.id DESC ";
    	if(!empty($search['co_code']) AND $search['co_code']>-1){
    		$where.=" AND t.to_co =".$search['co_code'];
    	}
    	if($search['branch_name']>0){
    		$where.=" AND t.branch_id = ".$search['branch_name'];
    	}
    	
    	if($search['status']>-1){
    		$where.=" AND t.status = ".$search['status'];
    	}
    	
    	if(!empty($search['note'])){
    		$s_where=array();
    		$s_search = str_replace(' ', '', addslashes(trim($search['note'])));
    		$s_where[]="REPLACE(t.note,' ','')  LIKE '%{$s_search}%'";
    		$where .=' AND '.implode(' OR ',$s_where).' ';
    	}
    	//echo $sql.$where;
    	return $db->fetchAll($sql.$where.$order);
    }
    public function getAllinfoTransfer($id){
    	$db = $this->getAdapter();
    	$sql ="SELECT * FROM `ln_tranfser_zone` WHERE id = $id";
    	return $db->fetchRow($sql);
    }
    public function getZonename(){
    	$db=$this->getAdapter();
    	$sql="SELECT id,branch_id,zone_name,code_to,date,note,STATUS FROM `ln_tranfser_co`";
    	return $db->fetchAll($sql);
    }
    function getLoanNumberByZone($zone_id){
    	$dbg = $this->getAdapter();
    	$sql="SELECT g.g_id,g.zone_id,lm.member_id  FROM `ln_loan_group` AS g,`ln_loan_member` AS lm WHERE
    		g.g_id=lm.group_id AND g.zone_id=$zone_id ";
    	return $dbg->fetchAll($sql);
    }
    public function insertTransferZone($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	$this->_name='ln_tranfser_zone';
    	try {
    		
    		$_data_arr = array(
    				'branch_id'=> $data['branch_name'],
    				'from_zone'=> $data['zone_name'],
    				'to_co'=> $data['to_co'],
    				'status'=> $data['status'],
    				'date'=> $data['Date'],
    				'note'=> $data['Note'],
    				'user_id'=>$this->getUserId()
    		);
    		$this->insert($_data_arr);
    		
    		$rsz = $this->getLoanNumberByZone($data['zone_name']);
    		if(!empty($rsz))foreach ($rsz as $rsg){
	    		$_arr_fund = array(
	    				'collect_by'=>$data['to_co'],
	    		);
	    		
	    		$where = " member_id = ".$rsg['member_id']."  AND status = 1 ";
	    		$this->_name ="ln_loanmember_funddetail";
	    		$this->update($_arr_fund, $where);
	    		
	    		$sql = "SELECT crm_id,cd.loan_number FROM `ln_client_receipt_money` AS c ,`ln_client_receipt_money_detail` AS cd WHERE
	    		c.id=cd.crm_id AND cd.loan_number='".$rsg['member_id']."' GROUP BY c.id ";
	    		$rows = $db->fetchAll($sql);
	    		
	    		$this->_name="ln_client_receipt_money";
	    		if(!empty($rows))foreach($rows as $rs){
	    			$arr = array("co_id"=>$data['to_co']);
	    			$where = " id = ".$rs['crm_id'];
	    			$this->update($arr, $where);
	    		}
	    	}
	    	
    		$this->_name ="ln_loan_group";
    		$_arr = array(
    				'co_id'=>$data['to_co'],
    		);
    		$where = " zone_id = ".$data['zone_name'];
    		$this->update($_arr, $where);
    		$db->commit();
	    	
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Form_FrmMessage::message("INSERT_FAIL");
    		Application_Model_DbTable_DbUserLog::writeMessageError($err);
    	}
    }
    public function updatTransfer($data,$id){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try {
    		$_data_arr = array(
    				'branch_id'=> $data['branch_name'],
    				'from_zone'=> $data['zone_name'],
    				'to_co'=> $data['to_co'],
    				'status'=> $data['status'],
    				'date'=> $data['Date'],
    				'note'=> $data['Note'],
    				'user_id'=>$this->getUserId()
    		);
    		$wheres = "id = $id";
    		$this->update($_data_arr, $wheres);
    		
    		$rsz = $this->getLoanNumberByZone($data['zone_name']);
    		if(!empty($rsz))foreach ($rsz as $rsg){
	    		$_arr_fund = array(
	    				'collect_by'=>$data['to_co'],
	    		);
	    		
	    		$where = " member_id = ".$rsg['member_id']."  AND status = 1 ";
	    		$this->_name ="ln_loanmember_funddetail";
	    		$this->update($_arr_fund, $where);
	    		
	    		$sql = "SELECT crm_id,cd.loan_number FROM `ln_client_receipt_money` AS c ,`ln_client_receipt_money_detail` AS cd WHERE
	    		c.id=cd.crm_id AND cd.loan_number='".$rsg['member_id']."' GROUP BY c.id ";
	    		$rows = $db->fetchAll($sql);
	    		
	    		$this->_name="ln_client_receipt_money";
	    		if(!empty($rows))foreach($rows as $rs){
	    			$arr = array("co_id"=>$data['to_co']);
	    			$where = " id = ".$rs['crm_id'];
	    			$this->update($arr, $where);
	    		}
	    	}
	    	
    		$this->_name ="ln_loan_group";
    		$_arr = array(
    				'co_id'=>$data['to_co'],
    		);
    		$where = " zone_id = ".$data['zone_name'];
    		$this->update($_arr, $where);
    		$db->commit();
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("INSERT_FAIL");
    		$err =$e->getMessage();
    		Application_Model_DbTable_DbUserLog::writeMessageError($err);
    		$db->rollBack();
    	}
    }
  
}

