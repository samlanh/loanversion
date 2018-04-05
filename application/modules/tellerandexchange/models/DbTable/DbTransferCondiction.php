<?php

class Tellerandexchange_Model_DbTable_DbTransferCondiction extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_transfercondiction';
    
    /**
     * Get current rate s
     * @return array(6);
     */
    function addCondictionTransfer($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try {
    		$dbg = new Application_Model_DbTable_DbGlobal();
    		$useId = $dbg->getUserId();
    		$_arr=array(
    				"currency_id"		=>$data['currency_id'],
    				"fromAmount"	=> $data['fromAmount'],
    				"toAmount"=>$data['toAmount'],
    				"totalFee"=>$data['totalFee'],
    				"commisionFee"=>$data['commisionFee'],
    				"note"=>$data['note'],
    				"user_id"=>$useId,
    		);
    		if (!empty($data['id'])){
    			$id = $data['id'];
    			$_arr['modifyDate']=date("Y-m-d H:i:s");
    			$_arr['status']=$data['status'];
    			$where = " id = $id";
    			$this->update($_arr, $where);
    		}else{
    			$_arr['status']=1;
    			$_arr['createDate']=date("Y-m-d H:i:s");
    			$id = $this->insert($_arr);
    		}
    		$db->commit();
    		return $id;
    	} catch (Exception $e) {
    		$db->rollBack();
    	}
    }
    
    function getAllTransferCondiction($search=null){
    	$db = $this->getAdapter();
    	$sql = "SELECT 
			tr.`id`,
			(SELECT CONCAT(c.curr_namekh,' ',c.symbol ) FROM `ln_currency` AS c WHERE c.id = tr.`currency_id` LIMIT 1) AS currencyKH,
			tr.`fromAmount`,
			tr.`toAmount`,
			tr.`totalFee`,
			tr.`commisionFee`,
			tr.`status`
			FROM 
			`ln_transfercondiction` AS tr WHERE 1 ";
    	if ($search['currencySearch']>0){
    		$sql.=" AND tr.`currency_id` =".$search['currencySearch'];
    	}
    	if ($search['status']>-1){
    		$sql.=" AND tr.`status` =".$search['status'];
    	}
    	$sql.=" ORDER BY tr.`currency_id` ASC";
    	return $db->fetchAll($sql);
    }
    
    function  getAllExchageRate(){
    	$db = $this->getAdapter();
    	$sql = "SELECT ex.`id`,ex.`in_cur_id`,ex.`out_cur_id`,ex.`rate_in`,ex.`spread`,ex.`rate_out` FROM `ln_exchangerate` AS ex";
    	return $db->fetchAll($sql);
    }
   
    function getAllCurrencyType(){
    	$db = $this->getAdapter();
    	$sql="SELECT c.* FROM `ln_currency` AS c WHERE  c.`status` =1";
    	return $db->fetchAll($sql);
    }
    function getTransferCondictionById($id){
    	$db = $this->getAdapter();
    	$sql="SELECT c.* FROM `ln_transfercondiction` AS c WHERE  c.id = $id LIMIT 1";
    	return $db->fetchRow($sql);
    }
   
}
?>
