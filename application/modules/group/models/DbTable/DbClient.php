<?php

class Group_Model_DbTable_DbClient extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_client';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authloan');
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
		
		$client_code=$_data['client_no'];
		/*if(!empty($_data['id'])){
			$client_code=$_data['client_no'];
		}else{
			$client_code = $this->getClientCode($_data['branch_id']);
		}*/
		
		$_arr=array(
				'is_group'	  => $_data['is_group'],
				//'parent_id'	  =>$parent,
				'branch_id'	  => $_data['branch_id'],
				'parent_id'	  =>($_data['group_id']!=-1)?$_data['group_id']:"",
				'group_code' => ($_data['is_group']==1)?$_data['group_code']:"",
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
				'spouse_name' => $_data['spouse'],
				'spouse_nationid'=>$_data['spouse_nationid'],
				'guarantor_with'=>$_data['guarantor_with'],
				'guarantor_tel'=>$_data['guarantor_tel'],
				'create_date' => date("Y-m-d"), 
				'remark'	  => $_data['desc'],
				'status'      => $_data['status'],
				'client_d_type'      => $_data['client_d_type'],
				'join_d_type'      => $_data['join_d_type'],
				'guarantor_d_type'      => $_data['guarantor_d_type'],
				'guarantor_address'      => $_data['guarantor_address'],
				'user_id'	  => $this->getUserId(),
				'dob_guarantor'  => $_data['dob_guarantor'],
				'dob_join_acc'  => $_data['dob_join_acc'],
				
		);
		if(!empty($_data['id'])){
			$where = 'client_id = '.$_data['id'];
			$this->update($_arr, $where);
			return $_data['id'];
			 
		}else{
			//echo $this->insert($_arr);exit();
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
	public function getClientDetailInfo($id){
		$db = $this->getAdapter();
		$sql = "SELECT c.client_id ,c.is_group,group_code, c.client_number ,c.name_kh ,c.name_en,c.join_with ,c.join_nation_id,c.relate_with, 
		c.join_tel, c.guarantor_with ,c.guarantor_tel ,nation_id,
		c.position_id ,(SELECT commune_name FROM `ln_commune` WHERE com_id = c.com_id   LIMIT 1) AS commune_name
		,(SELECT district_name FROM `ln_district` AS ds WHERE dis_id = c.dis_id  LIMIT 1) AS district_name
		,(SELECT province_en_name FROM `ln_province` WHERE province_id= c.pro_id  LIMIT 1) AS province_en_name
		,(SELECT village_name FROM `ln_village` WHERE vill_id = c.village_id  LIMIT 1) AS village_name ,c.street,c.house ,
		c.id_type ,c.id_number, c.phone,c.job , c.spouse_name , c.spouse_nationid ,c.remark ,c.status , c.user_id ,
		(SELECT name_en FROM `ln_view` WHERE TYPE = 5 AND key_code = c.sit_status) AS sit_status , 
		(SELECT branch_nameen FROM `ln_branch` WHERE br_id =c.branch_id LIMIT 1) AS branch_name ,
		(SELECT name_en FROM `ln_client` WHERE client_id =c.parent_id ) AS parent , 
		(SELECT name_en FROM `ln_view` WHERE TYPE = 11 AND key_code =c.sex) AS sex ,
		(SELECT name_en FROM `ln_view` WHERE TYPE = 23 AND key_code =c.`client_d_type`) AS client_d_type ,
		(SELECT name_en FROM `ln_view` WHERE TYPE = 23 AND key_code =c.`join_d_type`) AS join_d_type ,  
		(SELECT name_en FROM `ln_view` WHERE TYPE = 23 AND key_code =c.`guarantor_d_type`) AS guarantor_d_type ,`guarantor_address`,      
		 photo_name FROM `ln_client` AS c WHERE client_id =  ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
	public function getClientCallateralBYId($client_id){
		$db = $this->getAdapter();
		$sql = " SELECT cc.id AS client_coll ,cd.* FROM `ln_client_callecteral` AS cc , `ln_client_callecteral_detail` AS cd WHERE  
		         cd.is_return=0 AND cd.client_coll_id = cc.id AND cc.client_id = ".$client_id;
		return $db->fetchAll($sql);
	}
    function getViewClientByGroupId($group_id){
    	$db = $this->getAdapter();
    	$sql=" SELECT * FROM $this->_name WHERE client_id=
    	(SELECT customer_id FROM `ln_loan` WHERE customer_id=".$db->quote($group_id)." LIMIT 1)";
    	$row=$db->fetchRow($sql);
    	return $row;
    }
	function getAllClients($search = null){		
		$db = $this->getAdapter();
		$from_date =(empty($search['start_date']))? '1': "create_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "create_date <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE (name_kh!='' OR  name_en!='') AND ".$from_date." AND ".$to_date;		
		$sql = "
		SELECT client_id,
		(SELECT branch_namekh FROM `ln_branch` WHERE br_id =branch_id LIMIT 1) AS branch_name ,
		client_number,name_kh,name_en,
		(SELECT name_en FROM `ln_view` WHERE TYPE =11 AND sex=key_code LIMIT 1) AS sex
		,phone,house,street,
			(SELECT village_name FROM `ln_village` WHERE vill_id= village_id) AS village_name
		    ,spouse_name,
		    create_date,
		    (SELECT  CONCAT(first_name,' ', last_name) FROM rms_users WHERE id=user_id )AS user_name,
			status FROM $this->_name ";
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
			$s_where[] = "REPLACE(client_number,' ','') 	LIKE '%{$s_search}%'";
			$s_where[] = "REPLACE(name_en,' ','')   		LIKE '%{$s_search}%'";
			$s_where[] = "REPLACE(name_kh,' ','')   		LIKE '%{$s_search}%'";
			$s_where[] = "REPLACE(join_with,' ','')  		LIKE '%{$s_search}%'";
			$s_where[] = "REPLACE(join_nation_id,' ','')  	LIKE '%{$s_search}%'";
			$s_where[] = "REPLACE(phone,' ','')   			LIKE '%{$s_search}%'";
			$s_where[] = "REPLACE(house,' ','')   			LIKE '%{$s_search}%'";
			$s_where[] = "REPLACE(street,' ','')  	 		LIKE '%{$s_search}%'";
			$s_where[] = "REPLACE(spouse_name,' ','')  		LIKE '%{$s_search}%'";
// 			$s_where[] = " spouse_nationid LIKE '%{$s_search}%'";
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
		$order=" ORDER BY client_id DESC";
 		//echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);	
	}
	public function getGroupCodeBYId($data){
		$db = $this->getAdapter();
		if($data['is_group']==1){
			$sql = "SELECT COUNT(client_id) AS number FROM `ln_client`
			      WHERE is_group =1 AND branch_id = ".$data['branch_id'] ;
			    $acc_no = $db->fetchOne($sql);
				$new_acc_no= (int)$acc_no+1;
				$acc_no= strlen((int)$acc_no+1);
				$pre ="G".$this->getPrefixCode($data['branch_id']);
				for($i = $acc_no;$i<3;$i++){
					$pre.='0';
				}
				return $pre.$new_acc_no;
		}else{
			$sql = " SELECT group_code FROM `ln_client`
			WHERE client_id = ".$data['group_id'] ;
			 $rs = $db->fetchOne($sql);
			if(empty($rs)){return ''; }else{
				return $rs;
			}
		}
		
	}
	function getPrefixCode($branch_id){
		$db  = $this->getAdapter();
		$sql = " SELECT prefix FROM `ln_branch` WHERE br_id = $branch_id  LIMIT 1";
		return $db->fetchOne($sql);
	}	
	public function getClientCode($branch_id){//for get client by branch
		$db = $this->getAdapter();
			$sql = "SELECT COUNT(client_id) AS number FROM `ln_client`
			WHERE branch_id = ".$branch_id;
		$acc_no = $db->fetchOne($sql);
		$new_acc_no= (int)$acc_no+1;
		$acc_no= strlen((int)$acc_no+1);
		$pre =$this->getPrefixCode($branch_id);
		//$pre="";
		for($i = $acc_no;$i<6;$i++){
			$pre.='0';
		}
		return $pre.$new_acc_no;
	}
// 	public function adddoocumenttype($data){
		
// 		$db = $this->getAdapter();
// 		$document_type=array(
// 				'name_en'=>$data['clienttype_nameen'],
// 				'name_kh'=>$data['clienttype_namekh'],
// 				'displayby'=>1,
// 				'type'=>23,
// 				'status'=>1
				
// 		);
		
// 		$row= $this->insert($document_type);
// 		return $row;
// 	}
	public function addIndividaulClient($_data){
		
		$client_code = $this->getClientCode($_data['branch_id']);
			$_arr=array(
					'is_group'=>0,
					'group_code'=>'',
					'parent_id'=>0,
					'client_number'=>$client_code,
					'name_kh'	  => $_data['name_kh'],
					'name_en'	  => $_data['name_en'],
					'sex'	      => $_data['sex'],
					'sit_status'  => $_data['situ_status'],
					'dis_id'      => $_data['district'],
					'village_id'  => $_data['village'],
					'street'	  => $_data['street'],
					'house'	      => $_data['house'],
					'branch_id'  => $_data['branch_id'],
					'job'        =>$_data['job'],
					'phone'	      => $_data['phone'],
					'create_date' => date("Y-m-d"),
					'client_d_type'      => $_data['client_d_type'],
					'user_id'	  => $this->getUserId(),
					'dob'			=>$_data['dob_client'],	
					'pro_id'      => $_data['province'],
					'com_id'      => $_data['commune'],
					
			
			);
			
				//echo $this->insert($_arr);exit();
				$this->_name = "ln_client";
				$id =$this->insert($_arr);
				return array('id'=>$id,'client_code'=>$client_code);
	}
}

