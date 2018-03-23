<?php
class Report_Model_DbTable_DbInventory extends Zend_Db_Table_Abstract
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
    function productNearlyOutStock($search=null){
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
				pl.`pro_id` = p.`id`
			AND pl.`qty` <= pl.`qty_warning`";
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
    function getSaleInventory($search=null){
    	$db= $this->getAdapter();
    	$sql="
    		SELECT 
    			(SELECT client_number FROM `ln_ins_client` WHERE client_id = s.customer_id LIMIT 1) AS client_number,
				(SELECT name_kh FROM `ln_ins_client` WHERE client_id = s.customer_id LIMIT 1) AS client_name_kh,
				(SELECT phone FROM `ln_ins_client` WHERE client_id = s.customer_id LIMIT 1) AS phone,
				(SELECT c.name FROM `ln_ins_category` AS  c WHERE c.id=p.`cate_id` LIMIT 1) AS catName,
				(SELECT b.branch_namekh FROM `ln_branch` AS b WHERE b.br_id = s.branch_id LIMIT 1) AS branch_namekh,
				p.item_name,
				p.`item_code`,
				(SELECT name_en FROM `ln_view` WHERE TYPE = 29 AND key_code =s.selling_type LIMIT 1) AS sellingTypeTitle,
				(SELECT payment_nameen FROM `ln_payment_method` WHERE id = s.payment_method LIMIT 1) AS paymentMethodTitle,
				s.* 
			FROM `ln_ins_sales_install` AS s,
				`ln_ins_product` AS p 
			WHERE 
				s.product_id = p.id 
				AND s.`status` =1
    	";
    	$from_date =(empty($search['start_date']))? '1': " s.date_sold >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " s.date_sold <= '".$search['end_date']." 23:59:59'";
    	$sql.= " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_search = str_replace(' ', '',$s_search);
    		$s_where[] = " REPLACE(s.`sale_no`,' ','')  LIKE '%{$s_search}%'";
    		$s_where[]= " s.invoice_no LIKE '%{$s_search}%'";
    		$s_where[]= " s.color LIKE '%{$s_search}%'";
    		$s_where[]="  s.power LIKE '%{$s_search}%'";
    		$s_where[]= " s.engine LIKE '%{$s_search}%'";
    		$s_where[]= " s.selling_price LIKE '%{$s_search}%'";
    		$sql .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['branch_id'])){
    		$sql.=" AND s.branch_id=".$search['branch_id'];
    	}
    	if(!empty($search['customer'])){
    		$sql.=" AND s.customer_id=".$search['customer'];
    	}
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('branch_id');
    	$sql.=" ORDER BY s.`id` DESC";
    	return $db->fetchAll($sql);
    }
    function getSaleInventoryById($id){
    	$db= $this->getAdapter();
    	$sql="
    	SELECT
    	(SELECT client_number FROM `ln_ins_client` WHERE client_id = s.customer_id LIMIT 1) AS client_number,
    	(SELECT name_kh FROM `ln_ins_client` WHERE client_id = s.customer_id LIMIT 1) AS client_name_kh,
    	(SELECT phone FROM `ln_ins_client` WHERE client_id = s.customer_id LIMIT 1) AS phone,
    	(SELECT sex FROM `ln_ins_client` WHERE client_id = s.customer_id LIMIT 1) AS sex,
    	(SELECT dob FROM `ln_ins_client` WHERE client_id = s.customer_id LIMIT 1) AS dob,
    	(SELECT nation_id FROM `ln_ins_client` WHERE client_id = s.customer_id LIMIT 1) AS nation_id,
    	(SELECT `ln_village`.`village_namekh` FROM `ln_village` WHERE (`ln_village`.`vill_id` = (SELECT village_id FROM `ln_ins_client` WHERE client_id = s.customer_id LIMIT 1) ) limit 1) AS `village_name`,
		(SELECT `c`.`commune_namekh` FROM `ln_commune` `c` WHERE (`c`.`com_id` = (SELECT com_id FROM `ln_ins_client` WHERE client_id = s.customer_id LIMIT 1)) LIMIT 1) AS `commune_name`,
		(SELECT `d`.`district_namekh` FROM `ln_district` `d` WHERE (`d`.`dis_id` = (SELECT dis_id FROM `ln_ins_client` WHERE client_id = s.customer_id LIMIT 1)) LIMIT 1) AS `district_name`,
		(SELECT province_kh_name FROM `ln_province` WHERE province_id= (SELECT pro_id FROM `ln_ins_client` WHERE client_id = s.customer_id LIMIT 1) LIMIT 1) AS province_kh_name,
    	(SELECT c.name FROM `ln_ins_producttype` AS  c WHERE c.id=p.`cate_id` LIMIT 1) AS catName,
    	(SELECT b.branch_namekh FROM `ln_branch` AS b WHERE b.br_id = s.branch_id LIMIT 1) AS branch_namekh,
    	p.item_name,
    	p.`item_code`,
    	(SELECT name_en FROM `ln_view` WHERE TYPE = 29 AND key_code =s.selling_type LIMIT 1) AS sellingTypeTitle,
    	(SELECT payment_nameen FROM `ln_payment_method` WHERE id = s.payment_method LIMIT 1) AS paymentMethodTitle,
    	(SELECT CONCAT(last_name ,' ',first_name)  FROM `rms_users` WHERE id = s.user_id LIMIT 1) AS user_name,
    	s.*
    	FROM `ln_ins_sales_install` AS s,
    	`ln_ins_product` AS p
    	WHERE
    	s.product_id = p.id
    	AND s.`status` =1 AND s.id = $id limit 1
    	";
    	return $db->fetchRow($sql);
    }
    function getSaleInventorySchedule($saleID){
    	$db= $this->getAdapter();
    	$sql="SELECT 
			sd.*
		FROM `ln_ins_sales_installdetail` AS sd 
		WHERE sd.`sale_id`=$saleID";
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
   function getSumaryStock($search= null){
   		$db  = $this->getAdapter();
   		$from_date =(empty($search['start_date']))? '1': $search['start_date']." 00:00:00";
   		$to_date = (empty($search['end_date']))? '1': $search['end_date']." 23:59:59";
//    		$sql.= " AND ".$from_date." AND ".$to_date;
	   	$sql="SELECT pl.`location_id`,
		(SELECT b.branch_namekh FROM `ln_branch` AS b WHERE b.br_id = pl.`location_id` LIMIT 1) AS branch_namekh,
		(SELECT b.branch_nameen FROM `ln_branch` AS b WHERE b.br_id = pl.`location_id` LIMIT 1) AS branch_nameen,
		(SELECT c.name FROM `ln_ins_category` AS c WHERE c.id = p.`cate_id` LIMIT 1) AS categoryName,
		(SELECT SUM(pd.`qty`) FROM `ln_ins_purchase_detail` AS pd,`ln_ins_purchase` AS pu WHERE pu.`id`=pd.`po_id` AND pd.pro_id = p.`id` AND pu.`date` >='$from_date' AND pu.`date` <='$to_date' GROUP BY pd.`pro_id` LIMIT 1) AS purchaseQty,
		(SELECT COUNT(l.id) FROM `ln_ins_sales_install` AS l WHERE l.product_id = p.`id` AND l.`date_sold` >='$from_date' AND l.`date_sold` <='$to_date'  GROUP BY l.product_id LIMIT 1) AS stockOut,
		p.*,
		pl.`qty`,pl.`qty_warning` FROM 
		`ln_ins_product` AS p,
		`ln_ins_prolocation` AS pl
		WHERE 
		pl.`pro_id` = p.`id`";
	   	return $db->fetchAll($sql);
   	
   }
   
   public function getALLInstallmentPayment($search=null){
   	$db = $this->getAdapter();
   	$sql="SELECT
	   	(SELECT
	   	`ln_branch`.`branch_namekh`
	   	FROM `ln_branch` WHERE (`ln_branch`.`br_id` = `crm`.`branch_id`)
	   	LIMIT 1) AS `branch_name`,
	   
	   	(SELECT `l`.`sale_no` FROM `ln_ins_sales_install` `l` WHERE (`l`.`id` = `crm`.`loan_id`) LIMIT 1) AS `loan_number`,
	   	(SELECT `c`.`name_kh` FROM `ln_ins_client` `c` WHERE (`c`.`client_id` = `crm`.`client_id`) LIMIT 1) AS `client_name`,
	   	(SELECT  `c`.`client_number` FROM `ln_ins_client` `c` WHERE (`c`.`client_id` = `crm`.`client_id`) LIMIT 1) AS `client_number`,
	   	(SELECT `u`.`first_name` FROM `rms_users` `u` WHERE (`u`.`id` = `crm`.`user_id`)) AS `user_name`,
	   	`crm`.`id`                   AS `id`,
	   	`crm`.`loan_id`				AS loan_id,
	   	`crm`.`receipt_no`           AS `receipt_no`,
	   	`crm`.`branch_id`            AS `branch_id`,
	   	`crm`.`date_pay`             AS `date_pay`,
	   	`crm`.`date_payment`         AS `date_payment`,
	   	`crm`.`date_input`           AS `date_input`,
	   	`crm`.`note`                 AS `note`,
	   	`crm`.`user_id`              AS `user_id`,
	   	`crm`.`status`               AS `status`,
	   	`crm`.`payment_option`       AS `payment_option`,
	   	
	   	`crm`.`is_payoff`            AS `is_payoff`,
	   	`crm`.`total_payment`        AS `total_payment`,
	   	`crm`.`principal_amount`     AS `principal_amount`,
	   	`crm`.`interest_amount`      AS `interest_amount`,
	   	`crm`.`principal_paid`       AS `principal_paid`,
	   	`crm`.`interest_paid`        AS `interest_paid`,
	   
	   	`crm`.`penalize_paid`        AS `penalize_paid`,
	   	`crm`.`total_paymentpaid`    AS `total_paymentpaid`,
	   	`crm`.`recieve_amount`       AS `amount_recieve`,
	   	`crm`.`return_amount`        AS `return_amount`,
	   	`crm`.`penalize_amount`      AS `penelize`,
	   
	   	`crm`.`client_id`            AS `client_id`,
	   	`crm`.`paid_times`           AS `paid_times`
	   	FROM (`ln_ins_receipt_money` `crm`
	   	JOIN `ln_ins_receipt_money_detail` `d`)
	   	WHERE ((`crm`.`status` = 1)
	   	AND (`crm`.`id` = `d`.`receipt_id`)
	   	AND (`crm`.`status` = 1))
	   	";
   	//`crm`.`currency_type`        AS `currency_type`,
   	//	`crm`.`service_paid`         AS `service_paid`,
   	//	`crm`.`service_chargeamount` AS `service_charge`,
//    	(SELECT `ln_currency`.`symbol`
//    	FROM `ln_currency`
//    	WHERE (`ln_currency`.`id` = `crm`.`currency_type`) LIMIT 1) AS `currency_typeshow`,
	   	$from_date =(empty($search['start_date']))? '1': " date_input >= '".$search['start_date']." 00:00:00'";
	   	$to_date = (empty($search['end_date']))? '1': " date_input <= '".$search['end_date']." 23:59:59'";
	   	$where = " AND ".$from_date." AND ".$to_date;
	   	 
	   	if($search['branch_id']>0){
	   		$where.=" AND branch_id= ".$search['branch_id'];
	   	}
	   	if($search['members']>0){
	   		$where.=" AND client_id = ".$search['members'];
	   	}
// 	   	if(!empty($search['adv_search'])){
// 	   		// 			$s_search = addslashes(trim($search['adv_search']));
// 	   		// 			if($s_search=='បង់មុន'){
// 	   		// 	      		$where.=" AND payment_option = 2 ";
// 	   		// 				$search['adv_search']='';
// 	   		// 			}
// 	   	}
	   	if(!empty($search['adv_search'])){
	   		$s_where = array();
	   		$s_search = addslashes(trim($search['adv_search']));
	   		$s_where[] = " branch_name LIKE '%{$s_search}%'";
	   		$s_where[] = " loan_number LIKE '%{$s_search}%'";
	   		$s_where[] = " client_number LIKE '%{$s_search}%'";
	   		$s_where[] = " client_name LIKE '%{$s_search}%'";
	   		$s_where[] = " receipt_no LIKE '%{$s_search}%'";
	   		$where .=' AND ('.implode(' OR ',$s_where).')';
	   		if($s_search=='បង់មុន'){
	   			$where.=" AND payment_option = 2 ";
	   		}
	   	}
	   	$orderby='`crm`.`id`';
	   	if (!empty($search['orderBy'])){
	   		
	   		$orderby ='`crm`.`client_id`';
	   	}
	   	$order=" GROUP BY `crm`.`id` ORDER BY $orderby DESC ";
   	return $db->fetchAll($sql.$where.$order);
   }
   
}

