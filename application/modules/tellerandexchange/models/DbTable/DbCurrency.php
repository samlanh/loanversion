<?php

class Tellerandexchange_Model_DbTable_DbCurrency extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_currency';
    
    /**
     * Get current rate s
     * @return array(6);
     */
    function getAllCurrencyList($search=null){
    	$db = $this->getAdapter();
    	$sql = "SELECT c.`id`,
			c.`curr_namekh`,c.`curr_nameen`,c.`symbol`,c.`status`
			 FROM `ln_currency` AS c WHERE 1";
//     	$from_date =(empty($search['start_date']))? '1': "cb.for_date >= '".$search['start_date']." 00:00:00'";
//     	$to_date = (empty($search['end_date']))? '1': "cb.for_date <= '".$search['end_date']." 23:59:59'";
//     	$sql.= " WHERE ".$from_date." AND ".$to_date;
    	if ($search['status']>-1){
    		$sql.=" AND c.`status`=".$search['status'];
    	}
    	return $db->fetchAll($sql);
    }
    
    function addCurrency($data){
    	$db = $this->getAdapter();
		$db->beginTransaction();
		try {
			$arr = array(
					'curr_namekh'=>$data['curr_namekh'],
					'curr_nameen'=>$data['curr_nameen'],
					'symbol'=>$data['symbol'],
			);
			if (!empty($data['id'])){
				$arr['status'] = $data['status'];
				$where = " id = ".$data['id'];
				$this->update($arr, $where);
			}else{
				$arr['status'] = 1;
				$this->insert($arr);
			}
			return  $db->commit();
		} catch (Exception $e) {
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
    	
    }
    function getCurrencyBYID($id){
    	$db = $this->getAdapter();
    	$sql="SELECT c.*
 		FROM `ln_currency` AS c WHERE c.`id` =$id LIMIT 1";
    	return $db->fetchRow($sql);
    }
   
}
?>
