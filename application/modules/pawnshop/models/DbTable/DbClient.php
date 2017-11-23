<?php

class Pawnshop_Model_DbTable_DbClient extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_clientsaving';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    }
	public function addClient($_data){
		$photoname = str_replace(" ", "_", $_data['name_en']) . '.jpg';
		$upload = new Zend_File_Transfer();
		$upload->addFilter('Rename',
				array('target' => PUBLIC_PATH . '/images/'. $photoname, 'overwrite' => true) ,'photo');
		$receive = $upload->receive();
		if($receive)
		{
			$_data['photo'] = $photoname;
		}
		else{
			$_data['photo']="";
		}
		
		try{
		
		if(!empty($_data['id'])){
			$client_code=$_data['client_no'];
		}else{
			$client_code = $this->getClientCode($_data['branch_id']);
		}
		
		$_arr=array(
				'branch_id'	  => $_data['branch_id'],
				'client_number'=> $client_code,//$_data['client_no'],
				'name_kh'	  => $_data['name_kh'],
				'name_en'	  => $_data['name_en'],
				'join_with'	  => $_data['join_with'],
				'join_nation_id'=> $_data['join_nation_id'],
				'relate_with'	  => $_data['relate_with'],
				'join_tel'	  => $_data['relate_tel'],
				'sex'	      => $_data['sex'],
				'dob'			=>$_data['dob_client'],
				'sit_status'  => $_data['situ_status'],
				'pro_id'      => $_data['province'],
				'dis_id'      => $_data['district'],
				'com_id'      => $_data['commune'],
				'village_id'  => $_data['village'],
				'street'	  => $_data['street'],
				'house'	      => $_data['house'],
				'photo_name'  =>$_data['photo'],
				'job'        =>$_data['job'],
				'nation_id'=>$_data['national_id'],
				'phone'	      => $_data['phone'],
				'create_date' => date("Y-m-d"), 
// 				'remark'	  => $_data['desc'],
// 				'status'      => $_data['status'],
				'client_d_type'      => $_data['client_d_type'],
				'join_d_type'      => $_data['join_d_type'],
				'user_id'	  => $this->getUserId(),
				'dob_join_acc'  => $_data['dob_join_acc'],
				
		);
		if(!empty($_data['id'])){
			$where = 'client_id = '.$_data['id'];
			$this->update($_arr, $where);
			return $_data['id'];
			 
		}else{
			return  $this->insert($_arr);
		}
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	public function getClientById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM $this->_name WHERE client_id = ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
	
	function getAllClients($search = null){		
		$db = $this->getAdapter();
		$from_date =(empty($search['start_date']))? '1': "create_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "create_date <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE (name_kh!='' AND  name_en!='') AND ".$from_date." AND ".$to_date;		
		$sql = "
		SELECT client_id,
		(SELECT branch_nameen FROM `ln_branch` WHERE br_id =branch_id LIMIT 1) AS branch_name ,
		client_number,name_kh,name_en,
		(SELECT name_en FROM `ln_view` WHERE TYPE =11 AND sex=key_code LIMIT 1) AS sex
		,phone,house,street,
			(SELECT village_name FROM `ln_village` WHERE vill_id= village_id) AS village_name
		    ,join_with,create_date,
		    (SELECT  CONCAT(first_name,' ', last_name) FROM rms_users WHERE id=user_id )AS user_name,
			status FROM $this->_name ";
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = $search['adv_search'];
			$s_where[] = " client_number LIKE '%{$s_search}%'";
			$s_where[] = " name_en LIKE '%{$s_search}%'";
			$s_where[] = " name_kh LIKE '%{$s_search}%'";
			$s_where[] = "join_with LIKE '%{$s_search}%'";
			$s_where[] = "join_nation_id LIKE '%{$s_search}%'";
			$s_where[] = " phone LIKE '%{$s_search}%'";
			$s_where[] = " house LIKE '%{$s_search}%'";
			$s_where[] = " street LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1){
			$where.= " AND status = ".$search['status'];
		}
		if($search['province_id']>0){
			$where.=" AND pro_id= ".$search['province_id'];
		}
		if(!empty($search['district_id'])){
			$where.=" AND dis_id= ".$search['district_id'];
		}
		if(!empty($search['comm_id'])){
			$where.=" AND com_id= ".$search['comm_id'];
		}
		if(!empty($search['village'])){
			$where.=" AND village_id= ".$search['village'];
		}
		$where.=" AND client_type = 1 ";
		$order=" ORDER BY client_id DESC";
//  		echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);	
	}
	
	function getPrefixCode($branch_id){
		$db  = $this->getAdapter();
		$sql = " SELECT prefix FROM `ln_branch` WHERE br_id = $branch_id  LIMIT 1";
		return $db->fetchOne($sql);
	}	
	public function getClientCode($branch_id){//for get client by branch
		$db = $this->getAdapter();
			$sql = "SELECT COUNT(client_id) AS number FROM $this->_name
			WHERE branch_id = ".$branch_id;
		$acc_no = $db->fetchOne($sql);
		$new_acc_no= (int)$acc_no+1;
		$acc_no= strlen((int)$acc_no+1);
		$pre =$this->getPrefixCode($branch_id);
		for($i = $acc_no;$i<6;$i++){
			$pre.='0';
		}
		return $pre.$new_acc_no;
	}
// 	public function addIndividaulClient($_data){
// 		$client_code = $this->getClientCode($_data['branch_id']);
// 			$_arr=array(
// 					'is_group'=>0,
// 					'group_code'=>'',
// 					'parent_id'=>0,
// 					'client_number'=>$client_code,
// 					'name_kh'	  => $_data['name_kh'],
// 					'name_en'	  => $_data['name_en'],
// 					'sex'	      => $_data['sex'],
// 					'sit_status'  => $_data['situ_status'],
// 					'dis_id'      => $_data['district'],
// 					'village_id'  => $_data['village'],
// 					'street'	  => $_data['street'],
// 					'house'	      => $_data['house'],
// 					'branch_id'  => $_data['branch_id'],
// 					'job'        =>$_data['job'],
// 					'phone'	      => $_data['phone'],
// 					'create_date' => date("Y-m-d"),
// 					'client_d_type'      => $_data['client_d_type'],
// 					'user_id'	  => $this->getUserId(),
// 					'dob'			=>$_data['dob_client'],	
// 					'pro_id'      => $_data['province'],
// 					'com_id'      => $_data['commune'],
					
			
// 			);
			
// 				$this->_name = "ln_client";
// 				$id =$this->insert($_arr);
// 				return array('id'=>$id,'client_code'=>$client_code);
// 	}
	function getAllClient($branch_id=null){//ajax
		$db = $this->getAdapter();
		$sql = " SELECT c.`client_id` AS id  ,c.`branch_id`,
		CONCAT(c.`name_en`,'-',c.`name_kh`) AS name , client_number
		FROM `ln_clientsaving` AS c WHERE c.`name_en`!='' AND c.status=1  " ;
		if($branch_id!=null){
			$sql.=" AND c.`branch_id`= $branch_id ";
	
		}
		//   	$sql.=" ORDER BY id DESC";
		return $db->fetchAll($sql);
	}
	function getAllClientNumber($branch_id=null){//ajax
		$db = $this->getAdapter();
		$sql = " SELECT c.`client_id` AS id  ,c.client_number AS name, c.`branch_id`
		FROM `ln_clientsaving` AS c WHERE c.`name_en`!='' AND c.client_number !='' AND c.status=1  " ;
		if($branch_id!=null){
			$sql.=" AND c.`branch_id`= $branch_id ";
		}
		return $db->fetchAll($sql);
	}
}

