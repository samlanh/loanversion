<?php

class Pawnshop_Model_DbTable_DbSale extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_pawn_sale';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    }
    
    function getAllPawnSale($search = null){
    	$db = $this->getAdapter();
    	$from_date =(empty($search['start_date']))? '1': "selling_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "selling_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    
    	$sql = "SELECT
    				ps.id,
    				(SELECT branch_nameen FROM `ln_branch` WHERE br_id = ps.branch_id LIMIT 1) AS branch_name ,
			    	ps.invoice_no,
			    	ps.customer_name,
			    	(SELECT name_en FROM `ln_view` WHERE type =11 AND ps.gender=key_code LIMIT 1) AS sex,
			    	ps.tel,
			    	ps.address,
			    	
			    	CONCAT(
						(select product_en from ln_pawnshopproduct as pspro where pspro.id = psp.product_id),
						'(',product_description,')',
						'(',(select name_kh from ln_clientsaving where client_id = customer_id),')'
					) as pawn_name,
			    	
			    	ps.selling_date,
			    	(SELECT CONCAT(first_name,' ', last_name) FROM rms_users as u WHERE u.id=ps.user_id )AS user_name,
			    	ps.status
    			FROM
    				ln_pawn_sale as ps,
    				ln_pawnshop as psp
    			where 
    				ps.pawn_id = psp.id	
    		";
    
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
    	$order=" ORDER BY ps.id DESC";
    	return $db->fetchAll($sql.$where.$order);
    }
    
    
	public function addSalePawn($_data){
		
		try{
			$_arr=array(
					'branch_id'	  	=> $_data['branch_id'],
					'customer_name'	=> $_data['customer_name'],
					'tel'	  		=> $_data['tel'],
					'gender'	  	=> $_data['gender'],
					'dob'	  		=> $_data['dob'],
					'address'		=> $_data['address'],
					'nationid_no'	=> $_data['nation_id'],
					
					'invoice_no'	=> $_data['invoice_no'],
					'pawn_id'	    => $_data['product_id'],
					'qty'		    => 1,
					'selling_price' => $_data['unit_price'],
					'description'  	=> $_data['description'],
					'selling_date'  => $_data['selling_date'],
					
					'user_id'	  	=> $this->getUserId(),
					
			);
			$id = $this->insert($_arr);
			
			$this->_name = "ln_pawnshop";
			$array = array(
						'is_sold'	=> 1,
					);
			$where = " id = ".$_data['product_id'];
			$this->update($array, $where);
			
		}catch(Exception $e){
			echo $e->getMessage();
		}
	}
	public function getPawnSaleById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM ln_pawn_sale WHERE id = $id LIMIT 1 ";
		return $db->fetchRow($sql);
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

	
	function getProductIdByBranch($branch_id){
		$db=$this->getAdapter();
		
		$sql="select 
					id,
					CONCAT(
						(select product_en from ln_pawnshopproduct as psp where psp.id = ps.product_id),
						'(',product_description,')',
						'(',(select name_kh from ln_clientsaving where client_id = customer_id),')'
					) as name
				from 
					ln_pawnshop	as ps
				where
					is_sold=0
					and is_dach = 1	
					and branch_id = $branch_id
			";
		return $db->fetchAll($sql);
	}
	
	function getProductIdByBranchEdit($branch_id){
		$db=$this->getAdapter();
	
		$sql="select
					id,
					CONCAT(
					(select product_en from ln_pawnshopproduct as psp where psp.id = ps.product_id),
					'(',product_description,')',
					'(',(select name_kh from ln_clientsaving where client_id = customer_id),')'
					) as name
				from
					ln_pawnshop	as ps
				where
					is_dach = 1
					and branch_id = $branch_id
			";
		return $db->fetchAll($sql);
	}
	
	function getProductDetail($pawn_id){
		$db=$this->getAdapter();
		$sql="select
					*
				from
					ln_pawnshop	as ps
				where
					ps.id = $pawn_id
			";
		return $db->fetchRow($sql);
	}
	
	
	
	
	
	
	
}

