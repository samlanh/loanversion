<?php

class Payroll_Model_DbTable_DbPermission extends Zend_Db_Table_Abstract
{
    protected $_name = 'ln_permission';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authloan');
    	return $session_user->user_id;
    }
	public function addPermission($_data){
		$_arr=array(
				'employee_id'	=> $_data['employee'],
				'branch_id'		=>$_data['branch_id'],
				'approve_by'	=> $_data['approve_by'],
				'request_date'  => $_data['request_date'],
				'permission_type'=> $_data['type'],
				'from_date'   	=> $_data['from_date'],
				'to_date'		=> $_data['to_date'],
				'time'		    => $_data['time'],
				'all_day'     	=> !empty($_data['all_day'])?$_data['all_day']:0,
				'paid_leave'    => !empty($_data['paid_leave'])?$_data['paid_leave']:0,
				'every_day'     => !empty($_data['every_day'])?$_data['every_day']:0,
				'reason'		=> $_data['reason'],
				'status'		=> $_data['status'],
				'date'			=> date('Y-m-d'),
				'user_id'		=> $this->getUserId()
		);
		if(!empty($_data['id'])){
			$where = 'id = '.$_data['id'];
			return  $this->update($_arr, $where);
		}else{
			return  $this->insert($_arr);
		}
		
	}
	public function getPermissionById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM $this->_name WHERE id = ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
	function getAllPermission($search=null){
		
		$from_date =(empty($search['from_date']))? '1': "from_date >= '".$search['from_date']." 00:00:00'";
		$to_date = (empty($search['to_date']))? '1': "to_date <= '".$search['to_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		
		$db = $this->getAdapter();
		$sql = "SELECT id,
		(SELECT co_khname FROM ln_co WHERE co_id = employee_id limit 1 ) AS staff_name,
		(SELECT branch_namekh FROM ln_branch WHERE br_id = branch_id) AS branch_name,
		(SELECT co_khname FROM ln_co WHERE co_id = approve_by limit 1 ) AS approve_by,
		request_date,
		(SELECT name_kh FROM ln_view WHERE type=7 AND key_code =permission_type limit 1) AS types,
		from_date,to_date,time,reason,
		(SELECT user_name FROM rms_users WHERE id = user_id limit 1 ) AS user_id,
		 date, status FROM `ln_permission` WHERE 1 ";
// 		$where=" ";
		if($search['status_search']>-1){
			$where.= " AND status = ".$search['status_search'];
		}
		if(!empty($search['employee'])){
			$where.= " AND employee_id = ".$search['employee'];
		}
		if(!empty($search['branch_id'])){
			$where.= " AND branch_id = ".$search['branch_id'];
		}
		if(!empty($search['approve_by'])){
			$where.= " AND approve_by = ".$search['approve_by'];
		}
		if(!empty($search['type'])){
			$where.= " AND permission_type = ".$search['type'];
		}
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = $search['adv_search'];
			$s_where[] = " employee_id LIKE '%{$s_search}%'";
			$s_where[] = " reason LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		return $db->fetchAll($sql.$where);
	}	
}

