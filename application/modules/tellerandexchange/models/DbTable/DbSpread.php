<?php

class Tellerandexchange_Model_DbTable_DbSpread extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_exchangerate';
    
    /**
     * Get current rate s
     * @return array(6);
     */
    function getAllSpreadList($search=null){
    	$db = $this->getAdapter();
    	$sql = "SELECT id,
					(SELECT curr_namekh FROM `ln_currency` WHERE id = in_cur_id) AS buy_type,
					(SELECT curr_namekh FROM `ln_currency` WHERE id = out_cur_id) AS sell_type,
					rate_in,
					rate_out,
					spread,
					create_date,
					active AS status
					FROM `ln_exchangerate`  ORDER BY is_using DESC";
    	return $db->fetchAll($sql);
    }
    function getCurrentRate(){
    	$db = $this->getAdapter();
    	$sql = "SELECT `id`,`in_cur_id`,`out_cur_id`,`rate_in`,`spread`,`rate_out`
				FROM `ln_exchangerate` as r
    			WHERE r.`active` = 1
				ORDER BY r.`in_cur_id`, r.`out_cur_id`";
    	$rows = $db->fetchAll($sql);
    	
    	$result =array(
    					'DB'=>0,
		    			'BD'=>0,
		    			'DR'=>0,
		    			'RD'=>0,
		    			'SPDR'=>0,
		    			'SPDB'=>0,
    					'SPBR'=>0,
		    			'BR'=>0,
		    			'RB'=>0
    				);
		foreach ($rows AS $i => $r){
			echo "<br/><br/>";
			if($r['in_cur_id'] == 2 && $r['out_cur_id'] == 3){
				$result['DB'] = $r['rate_in'];
				$result['BD'] = $r['rate_out'];
				$result['SPDB'] = $r['spread'];
			}
			elseif($r['in_cur_id'] == 1 && $r['out_cur_id'] == 2){
				$result['DR'] = $r['rate_in'];
				$result['RD'] = $r['rate_out'];	
				$result['SPDR'] = $r['spread'];
			}
			elseif($r['in_cur_id'] == 3 && $r['out_cur_id'] == 1){
				$result['BR'] = $r['rate_in'];
				$result['RB'] = $r['rate_out'];
				$result['SPBR'] = $r['spread'];
			}
		}
    	return $result;
    }
    function  getAllExchageRate(){
    	$db = $this->getAdapter();
    	$sql = "SELECT ex.`id`,ex.`in_cur_id`,ex.`out_cur_id`,ex.`rate_in`,ex.`spread`,ex.`rate_out` FROM `ln_exchangerate` AS ex";
    	return $db->fetchAll($sql);
    }
    function setNewRate($data){
    	$db = $this->getAdapter();
    	//$db_loan = new Application_Model_DbTable_DbLoan();
    	$db->beginTransaction();
    	$ex = $this->getAllExchageRate();
    	try {
//     		print_r($data);exit();
    		$dbEx = new Tellerandexchange_Model_DbTable_Dbexchange();
    		$allExchangeRate = $dbEx->getAllExchangeRate();
    		$date = date("Y-m-d H:i:s");
    		if (!empty($allExchangeRate)) foreach ($allExchangeRate as $rs){
    			$_data=array(
    					"rate_in"		=>$data['rateIn'.$rs['id']],
    					"spread"		=>$data['spread'.$rs['id']],
    					"rate_out"		=>$data['rateOut'.$rs['id']],
    					"create_date"	=>$date
    			);
    			$where = "in_cur_id=".$rs['in_cur_id']." AND out_cur_id=".$rs['out_cur_id']." AND id=".$rs['id'];
    			$this->update($_data, $where);
    		}
    		
    		
//     		if(!empty($ex)){
//     			$_data=array(
    			
//     					    "rate_in"		=>$data['DB'],
// 	    					"spread"		=>$data['SPBD'],
// 	    					"rate_out"		=>$data['BD'],
// 	    					"create_date"	=>$date
//     			);
//     			$where = "in_cur_id=2 AND out_cur_id=3";
//     			$this->update($_data, $where);
    			
//     			$_data=array(
//     					    "rate_in"		=>$data['DR'],
// 	    					"spread"		=>$data['SPRD'],
// 	    					"rate_out"		=>$data['RD'],
// 	    					"create_date"	=>$date
//     			);
//     			$where = "in_cur_id= 1 AND out_cur_id = 2";
//     			$this->update($_data, $where);
    			
//     			$_data=array(
//     					    "rate_in"		=>$data['BR'],
// 	    					"spread"		=>$data['SPBR'],
// 	    					"rate_out"		=>$data['RB'],
// 	    					"create_date"	=>$date
//     			);
//     			$where = "in_cur_id = 3 AND out_cur_id = 1";
//     			$this->update($_data, $where);
    			
//     		}else{
	    		
// 	    			$_data=array(
// 	    					"in_cur_id"		=> 	2,
// 	    					"out_cur_id"	=> 3,
// 	    					"rate_in"		=>$data['DB'],
// 	    					"spread"		=>$data['SPBD'],
// 	    					"rate_out"		=>$data['BD'],
// 	    					"create_date"	=>$date
// 	    			);
// 	    			$this->insert($_data);
	    			
// 	    			$_data=array(
// 	    					"in_cur_id"		=> 	2,
// 	    					"out_cur_id"	=> 1,
// 	    			    	"rate_in"=>$data['DR'],
// 	    			    	"spread"=>$data['SPRD'],
// 	    			    	"rate_out"=>$data['RD'],
// 	    			    	"create_date"=>$date
// 	    			   );
// 	    			$this->insert($_data);
	    			
// 	    			$_data=array(
// 	    				"in_cur_id"		=> 	3,
// 	    				"out_cur_id"	=> 1,
// 	    			    "rate_in"=>$data['BR'],
// 	    			    "spread"=>$data['SPBR'],
// 	    			    "rate_out"=>$data['RB'],
// 	    			    "create_date"=>$date
// 	    			  );
// 	    			$this->insert($_data);
//     		}
    		
    		return  $db->commit();
    	} catch (Exception $e) {
    		$db->rollBack();
    	}
    }
    function getAllCurrencyType(){
    	$db = $this->getAdapter();
    	$sql="SELECT c.* FROM `ln_currency` AS c WHERE  c.`status` =1";
    	return $db->fetchAll($sql);
    }
    function getSpreadById($id){
    	$db = $this->getAdapter();
    	$sql="SELECT c.* FROM `ln_exchangerate` AS c WHERE  c.`active` =1 AND c.id = $id";
    	return $db->fetchRow($sql);
    }
    function addNewCurrencyExchange($data){
    	$db = $this->getAdapter();
    	//$db_loan = new Application_Model_DbTable_DbLoan();
    	$db->beginTransaction();
    	$ex = $this->getAllExchageRate();
    	try {
    		$_arr=array(
    				"in_cur_id"		=>$data['in_cur_id'],
    				"out_cur_id"	=> $data['out_cur_id'],
    				"rate_in"=>$data['rate_in'],
    				"spread"=>$data['spread'],
    				"rate_out"=>$data['rate_out'],
//     				"create_date"=>date("Y-m-d H:i:s"),
    		);
    		if (!empty($data['id'])){
    			$id = $data['id'];
    			$_arr['active']=$data['active'];
    			$where = " id = $id";
    			$this->update($_arr, $where);
    		}else{
    			$_arr['create_date']=date("Y-m-d H:i:s");
    			$id = $this->insert($_arr);
    		}
    		$db->commit();
    		return $id;
    	} catch (Exception $e) {
    		$db->rollBack();
    	}
    }
    public function CheckCurrencyInCurrencyOut($data){
    	$db = $this->getAdapter();
    	$sql='SELECT p.id FROM `ln_exchangerate` AS p WHERE p.in_cur_id = '.$data['in_cur_id'].' AND p.out_cur_id = '.$data['out_cur_id'].' LIMIT 1';
    	$row= $db->fetchRow($sql);
    	if (empty($row)){
    		$sql='SELECT p.id FROM `ln_exchangerate` AS p WHERE p.in_cur_id = '.$data['out_cur_id'].' AND p.out_cur_id = '.$data['in_cur_id'].' LIMIT 1';
    		$row= $db->fetchRow($sql);
    	}
    	if (!empty($row)){
    		return 1;
    	}
    	else{
    		return 0;
    	}
    }
}
?>
