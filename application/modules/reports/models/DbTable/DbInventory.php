<?php
class Reports_Model_DbTable_DbInventory extends Zend_Db_Table_Abstract
{
      

    public function getInventory($search=null){
    	$db=$this->getAdapter();
    	$sql = "SELECT pl.`location_id`,
			(SELECT b.branch_namekh FROM `ln_branch` AS b WHERE b.br_id = pl.`location_id` LIMIT 1) AS branch_namekh,
			(SELECT b.branch_nameen FROM `ln_branch` AS b WHERE b.br_id = pl.`location_id` LIMIT 1) AS branch_nameen,
			(SELECT c.name FROM `ln_ins_category` AS c WHERE c.id = p.`cate_id` LIMIT 1) AS categoryName,
			p.*,
			pl.`qty`,pl.`qty_warning` FROM 
			`ln_ins_product` AS p,
			`ln_ins_prolocation` AS pl
			WHERE 
				pl.`pro_id` = p.`id`";
//     	$from_date =(empty($search['start_date']))? '1': " date_release >= '".$search['start_date']." 00:00:00'";
//     	$to_date = (empty($search['end_date']))? '1': " date_release <= '".$search['end_date']." 23:59:59'";
//     	$where.= " AND ".$from_date." AND ".$to_date;
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
    	if($search["status"]>-1){
    		$sql.=' AND p.status='.$search["status"];
    	}
    	$sql.=" ORDER BY pl.`location_id`,p.`cate_id` DESC";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('branch_id');
    	return $db->fetchAll($sql);
    }
    function getAllInventoryPurchase($search=null){
    	$db= $this->getAdapter();
    	$sql=" 
    		SELECT (SELECT b.branch_namekh FROM `ln_branch` AS b WHERE b.br_id = sp.`branch_id` LIMIT 1) AS branch_namekh,
			 sp.*, s.`supplier_no`,s.`sup_name`,s.`tel`,s.`email`
			FROM 
			ln_ins_supplier AS s,
			ln_ins_purchase AS sp
			WHERE s.id=sp.supplier_id
			AND s.`status`=1
			";
    	$from_date =(empty($search['start_date']))? '1': " sp.date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " sp.date <= '".$search['end_date']." 23:59:59'";
    	$sql.= " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_search = str_replace(' ', '',$s_search);
    		$s_where[] = " REPLACE(s.`supplier_no`,' ','')  LIKE '%{$s_search}%'";
    		$s_where[]= " s.invoice_no LIKE '%{$s_search}%'";
    		$s_where[]= " s.tel LIKE '%{$s_search}%'";
    		$s_where[]="  s.sup_name LIKE '%{$s_search}%'";
    		$s_where[]= " s.tel LIKE '%{$s_search}%'";
    		$s_where[]= " s.email LIKE '%{$s_search}%'";
    		$s_where[]= " sp.total_amount LIKE '%{$s_search}%'";
    		$sql .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['branch_id'])){
    		$sql.=" AND sp.branch_id=".$search['branch_id'];
    	}
    	if(!empty($search['supllier'])){
    		$sql.=" AND sp.supplier_id=".$search['supllier'];
    	}
    	if($search['status']>-1){
    		$sql.=" AND sp.status=".$search['status'];
    	}
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('branch_id');
    	$sql.=" ORDER BY sp.`id` DESC";
    	return $db->fetchAll($sql);
    }
    public function getPurchaseById($id){
    	$db = $this->getAdapter();
    	$sql="
    	 SELECT (SELECT b.branch_namekh FROM `ln_branch` AS b WHERE b.br_id = sp.`branch_id` LIMIT 1) AS branch_namekh,
			 sp.*, s.`supplier_no`,s.`sup_name`,s.`tel`,s.`email`
			FROM 
			ln_ins_supplier AS s,
			ln_ins_purchase AS sp
			WHERE s.id=sp.supplier_id
			AND s.`status`=1 AND s.`id`=$id LIMIT 1
    	";
    	return $db->fetchRow($sql);
    }
    public function getPurchseDetail($PurchaseId){
    	$db = $this->getAdapter();
    	$sql="
    	SELECT 
			p.`item_code`,p.`item_name`,p.`cate_id`,
			(SELECT c.name FROM `ln_ins_category` AS c WHERE c.id = p.`cate_id` LIMIT 1) AS categoryName,
			pd.* 
			FROM 
			`ln_ins_purchase_detail` AS pd,
			`ln_ins_product` AS p
			WHERE pd.`po_id` =$PurchaseId
			AND pd.`pro_id` = p.`id`
    	";
    	return $db->fetchAll($sql);
    }
}

