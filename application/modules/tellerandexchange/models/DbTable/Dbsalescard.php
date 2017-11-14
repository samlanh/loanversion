<?php

class Tellerandexchange_Model_DbTable_Dbsalescard extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_salecard';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authloan');
    	return $session_user->user_id;
    	 
    }
    public function addsaleCard($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$arr = array(
    				'commission'=>$data['commission'],
    				'amount_sale'=>$data['amount'],
					'curr_type'=>$data['type_money'],
    				'title'=>$data['title'],
    				'note'=>$data['note'],
    				'sale_date'=>$data['date'],
    				'user_id'=>$this->getUserId(),
    				);
    		if(!empty($data['id'])){
    			$where="id = ".$data['id'];
    			$this->update($arr, $where);
    		}else{
    			$g_id = $this->insert($arr);//add group loan
    		}
    		$db->commit();
    		return 1;
    	}catch (Exception $e){
    		$db->rollBack();
    		return $e->getMessage();
    	}
    }
    function getAllSaleTransaction($search){
    	$db = $this->getAdapter();
    	$sql="SELECT id,title,sale_date,
		(SELECT ln_currency.curr_nameen FROM `ln_currency` WHERE ln_currency.id=curr_type limit 1)
		,amount_sale,commission,note FROM ln_salecard Where 1 ";
    	
    	$from_date =(empty($search['start_date']))? '1': " sale_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " sale_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = trim(addslashes($search['adv_search']));
    		$s_where[] = " title LIKE '%{$s_search}%'";
    		$s_where[] = " amount_sale LIKE '%{$s_search}%'";
    		$s_where[] = " commission LIKE '%{$s_search}%'";
    		$s_where[] = " note LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	
    	return $db->fetchAll($sql.$where);
    }
    function getTransactionByID($id){
    	$db = $this->getAdapter();
    	$sql = " SELECT * FROM `ln_salecard` WhERE id = $id";
    	return $db->fetchRow($sql);
    }
   
  
}

