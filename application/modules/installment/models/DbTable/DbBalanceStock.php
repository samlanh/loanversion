<?php

class Installment_Model_DbTable_DbBalanceStock extends Zend_Db_Table_Abstract
{
    protected $_name = 'ln_ins_product';
    public function setName($name)
    {
    	$this->_name=$name;
    }
	public function getUserId(){
		return Application_Model_DbTable_DbGlobal::GlobalgetUserId();
	}
	function getSumaryStock($search= null){
		$db  = $this->getAdapter();
		$from_date =(empty($search['start_date']))? '1': $search['start_date']." 00:00:00";
		$to_date = (empty($search['end_date']))? '1': $search['end_date']." 23:59:59";
		$sql="SELECT pl.`location_id`,
		(SELECT b.branch_namekh FROM `ln_branch` AS b WHERE b.br_id = pl.`location_id` LIMIT 1) AS branch_namekh,
		(SELECT b.branch_nameen FROM `ln_branch` AS b WHERE b.br_id = pl.`location_id` LIMIT 1) AS branch_nameen,
		(SELECT c.name FROM `ln_ins_category` AS c WHERE c.id = p.`cate_id` LIMIT 1) AS categoryName,
		(SELECT SUM(pd.`qty`) FROM `ln_ins_purchase_detail` AS pd,`ln_ins_purchase` AS pu WHERE pu.`id`=pd.`po_id` AND pd.pro_id = p.`id` AND pu.`date` >='$from_date' AND pu.`date` <='$to_date' GROUP BY pd.`pro_id` LIMIT 1) AS purchaseQty,
		(SELECT SUM(pd.`amount`) FROM `ln_ins_purchase_detail` AS pd,`ln_ins_purchase` AS pu
		WHERE pu.`id`=pd.`po_id` AND pd.pro_id = p.`id` AND pu.`date` >='1' AND pu.`date` <='2018-05-09 23:59:59' GROUP BY pd.`pro_id` LIMIT 1) AS purchaseAmount,
		(SELECT COUNT(l.id) FROM `ln_ins_sales_install` AS l WHERE l.product_id = p.`id` AND l.`date_sold` >='$from_date' AND l.`date_sold` <='$to_date'  GROUP BY l.product_id LIMIT 1) AS stockOut,
		(SELECT SUM(l.selling_price) FROM `ln_ins_sales_install` AS l WHERE l.product_id = p.`id` AND l.`date_sold` >='1' AND l.`date_sold` <='2018-05-09 23:59:59'
		GROUP BY l.product_id LIMIT 1) AS stockOutAmount,
		p.*,
		pl.`qty`,pl.`qty_warning` FROM
		`ln_ins_product` AS p,
		`ln_ins_prolocation` AS pl
		WHERE
		pl.`pro_id` = p.`id`";
		 
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_search = str_replace(' ', '',$s_search);
			$s_where[] = " REPLACE(p.item_name,' ','')  LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(p.item_code,' ','')LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(p.cost_price,' ','')  LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(p.selling_price,' ','')LIKE '%{$s_search}%'";
			$sql .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if($search["branch_id"]>0){
			$sql.=' AND pl.`location_id`='.$search["branch_id"];
		}
		if($search["category"]>0){
			$sql.=' AND p.cate_id='.$search["category"];
		}
		if($search["product_type"]>0){
			$sql.=' AND p.product_type='.$search["product_type"];
		}
		if($search["status"]>-1){
			$sql.=' AND p.status='.$search["status"];
		}
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('pl.`location_id`');
		$sql.=" ORDER BY pl.`location_id`";
		return $db->fetchAll($sql);
	
	}
	public function getBalanceCode(){
		$this->_name='ln_ins_balancstock';
		$db = $this->getAdapter();
		$sql=" SELECT id FROM $this->_name ORDER BY id DESC LIMIT 1 ";
		$acc_no = $db->fetchOne($sql);
		$new_acc_no= (int)$acc_no+1;
		$acc_no= strlen((int)$acc_no+1);
		$pre = "BL";
		for($i = $acc_no;$i<6;$i++){
			$pre.='0';
		}
		return $pre.$new_acc_no;
	}
	function addBalanceStock($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$datagroup = array(
					'code'=>$this->getBalanceCode(),
					'description'=>"",
					'closingDate'=>$data['closingDate'],
					'create_date'=>date("Y-m-d H:i:s"),
					'user_id'=>$this->getUserId(),
					'status'=>1,
			);
			$this->_name='ln_ins_balancstock';
			$balancstock = $this->insert($datagroup);//add
			
			$identity = $data['identity'];
			$ids = explode(",", $identity);
			if (!empty($ids)) foreach ($ids as $i){
				$arr = array(
						'balance_id'=>$balancstock,
						'location_id'=>$data['branch_id'.$i],
						'product_id'=>$data['productId'.$i],
						'qtyBefore'=>$data['qty'.$i],
						'currentQty'=>$data['qtyBalacne'.$i],
						'currentCost'=>$data['costBalacne'.$i],
						'note'=>$data['note'.$i],
						'user_id'=>$this->getUserId(),
						);
				$this->_name='ln_ins_balancstockdetail';
				$sale_id = $this->insert($arr);//add
			}
			$db->commit();
// 			return $sale_id;
		}catch (Exception $e){
			$db->rollBack();
			//Application_Form_FrmMessage::message("INSERT_FAIL");
			echo $e->getMessage();exit();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	function checkBalancStockCurrentDate($date){
		$dateCheck = date("Y-m",strtotime($date));
		$db = $this->getAdapter();
		$sql="SELECT b.* FROM `ln_ins_balancstock` AS b 
		WHERE DATE_FORMAT(b.`closingDate`,'%Y-%m') = '$dateCheck'";
		$rs = $db->fetchRow($sql);
		if (!empty($rs)){
			return 1;
		}
		return null;
	}
}