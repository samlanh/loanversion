<?php

class Pawnshop_Model_DbTable_DbSale extends Zend_Db_Table_Abstract
{
    protected $_name = 'ln_pawn_sale';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authloan');
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
			    	selling_price,
			    	description,
			    	ps.selling_date,
			    	(SELECT CONCAT(first_name,' ', last_name) FROM rms_users as u WHERE u.id=ps.user_id )AS user_name,
			    	ps.status
    			FROM
    				ln_pawn_sale as ps,
    				ln_pawnshop as psp
    			where 
    				ps.pawn_id = psp.id	";
    
    	if(!empty($search['adv_search'])){
	    	$s_where = array();
	    	$s_search = $search['adv_search'];
	    	$s_where[] = " customer_name LIKE '%{$s_search}%'";
	    	$s_where[] = " invoice_no LIKE '%{$s_search}%'";
	    	$s_where[] = " (select product_en from ln_pawnshopproduct as pspro where pspro.id = psp.product_id) LIKE '%{$s_search}%'";
	    	$s_where[] = " product_description LIKE '%{$s_search}%'";
	    	$s_where[] = " (select name_kh from ln_clientsaving where client_id = customer_id) LIKE '%{$s_search}%'";
	    	
	    	$s_where[] = " CONCAT(
						(select product_en from ln_pawnshopproduct as pspro where pspro.id = psp.product_id),
						'(',product_description,')',
						'(',(select name_kh from ln_clientsaving where client_id = customer_id),')'
					) LIKE '%{$s_search}%'";
	    	
	    	$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission('ps.branch_id');
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
	
	public function updateSalePawn($_data,$id){
	
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
					'status'	 	=> $_data['status'],
			);
			$where = " id = $id ";
			$this->update($_arr, $where);
				
			$this->_name = "ln_pawnshop";
			if($_data['status']==1){
				$is_sold = 1;
			}else{
				$is_sold = 0;
			}
			$array = array(
					'is_sold'	=> $is_sold,
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
					and branch_id = $branch_id ";
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
		$sql="SELECT
					ps.*,
					(SELECT SUM(d.principle_after) FROM `ln_pawnshop_detail` AS d WHERE d. pawn_id= ps.id AND STATUS=1 AND d.is_completed=0 LIMIT 1)  AS total_principal,
					(SELECT SUM(principal_paid) FROM `ln_pawn_receipt_money`  WHERE loan_id=ps.id AND status=1) AS principal_paid,
    				(SELECT COUNT(d.id) FROM `ln_pawnshop_detail` AS d WHERE d. pawn_id= ps.id AND STATUS=1 AND d.is_completed=0 LIMIT 1)  AS remaintimes
				FROM
					ln_pawnshop	as ps
				WHERE
					ps.id = $pawn_id ";
		return $db->fetchRow($sql);
	}
}