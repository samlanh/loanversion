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
			FROM 
				`ln_ins_sales_install` AS s,
				`ln_ins_product` AS p 
			WHERE 
				s.product_id = p.id 
				AND s.`status` =1 ";
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
    	if(!empty($search['category'])){
    		$sql.=" AND p.cate_id=".$search['category'];
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
	    	c.client_number AS client_number,
	    	c.name_kh AS client_name_kh,
	    	c.phone AS phone,
	    	c.sex AS sex,
	    	c.dob AS dob,
	    	(SELECT name_kh FROM `ln_view` WHERE TYPE = 23 AND id =c.client_d_type LIMIT 1) AS document_type,
	    	c.nation_id AS nation_id,
	    	(SELECT `ln_village`.`village_namekh` FROM `ln_village` WHERE `ln_village`.`vill_id` = c.village_id Limit 1) AS `village_name`,
			(SELECT `cm`.`commune_namekh` FROM `ln_commune` `cm` WHERE `cm`.`com_id` = c.com_id  LIMIT 1) AS `commune_name`,
			(SELECT `d`.`district_namekh` FROM `ln_district` `d` WHERE `d`.`dis_id` = c.dis_id LIMIT 1) AS `district_name`,
			(SELECT province_kh_name FROM `ln_province` WHERE province_id= c.pro_id LIMIT 1) AS province_kh_name,
	    	(SELECT cc.name FROM `ln_ins_producttype` AS  cc WHERE cc.id=p.`cate_id` LIMIT 1) AS catName,
	    	(SELECT b.branch_namekh FROM `ln_branch` AS b WHERE b.br_id = s.branch_id LIMIT 1) AS branch_namekh,
	    	p.item_name,
	    	(SELECT cate.name FROM `ln_ins_category` AS cate WHERE cate.id=p.cate_id LIMIT 1) as cate_name,
	    	p.`item_code`,
	    	(SELECT name_en FROM `ln_view` WHERE TYPE = 29 AND key_code =s.selling_type LIMIT 1) AS sellingTypeTitle,
	    	(SELECT payment_nameen FROM `ln_payment_method` WHERE id = s.payment_method LIMIT 1) AS paymentMethodTitle,
	    	(SELECT CONCAT(last_name ,' ',first_name)  FROM `rms_users` WHERE id = s.user_id LIMIT 1) AS user_name,
	    	s.*
	    	FROM `ln_ins_sales_install` AS s,
	    	`ln_ins_product` AS p,
	    	ln_ins_client AS c
	    	WHERE
	    	s.product_id = p.id
	    	AND c.client_id = s.customer_id
	    	AND s.`status` =1 AND s.id = $id limit 1";
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
	   	FROM `ln_branch` WHERE (`ln_branch`.`br_id` = `crm`.`branch_id`) LIMIT 1) AS `branch_name`,
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
	   	AND (`crm`.`status` = 1)) ";
	   	$from_date =(empty($search['start_date']))? '1': " date_input >= '".$search['start_date']." 00:00:00'";
	   	$to_date = (empty($search['end_date']))? '1': " date_input <= '".$search['end_date']." 23:59:59'";
	   	$where = " AND ".$from_date." AND ".$to_date;
	   	 
	   	if($search['branch_id']>0){
	   		$where.=" AND branch_id= ".$search['branch_id'];
	   	}
	   	if($search['members']>0){
	   		$where.=" AND client_id = ".$search['members'];
	   	}

	   	if(!empty($search['adv_search'])){
	   		$s_where = array();
	   		$s_search = addslashes(trim($search['adv_search']));
// 	   		$s_where[] = " loan_number LIKE '%{$s_search}%'";
// 	   		$s_where[] = " client_number LIKE '%{$s_search}%'";
// 	   		$s_where[] = " client_name LIKE '%{$s_search}%'";
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
   public function getAllOutstadingLoan($search=null){
   	$db = $this->getAdapter();
   	$where="";
   	$start_date = (empty($search['start_date']))? '1': " s.`date_sold` >= '".$search['start_date']." 23:59:59'";
   	$to_date = (empty($search['end_date']))? '1': " s.`date_sold` <= '".$search['end_date']." 23:59:59'";
   	$where.= " AND ".$start_date."  AND ".$to_date;
   
   	$sql="SELECT
		   		(SELECT  `ln_branch`.`branch_namekh` FROM `ln_branch`  WHERE (`ln_branch`.`br_id` = `s`.`branch_id`) LIMIT 1) AS `branch_name`,
			   	c.client_number AS `client_number`,
			   	c.name_kh AS `client_kh`,
			   	c.`client_number` AS client_number,
			   	c.name_en AS `client_en`,
			   	(SELECT inp.item_name FROM `ln_ins_product` AS inp WHERE inp.id = s.`product_id` LIMIT 1) AS item_name,
				(SELECT inc.name FROM `ln_ins_category` AS inc WHERE inc.id = s.`cate_id` LIMIT 1) AS cateName,
			   	s.*,
			   	(SELECT  `ln_ins_receipt_money`.`paid_times` FROM `ln_ins_receipt_money` WHERE ((`ln_ins_receipt_money`.`status` = 1) AND (`s`.`id` = `ln_ins_receipt_money`.`loan_id`))
			   	ORDER BY `ln_ins_receipt_money`.`paid_times` DESC
			   	LIMIT 1) AS `installment_amount`,
			   	(SELECT
			   	SUM(`ln_ins_receipt_money`.`principal_paid`)
			   	FROM `ln_ins_receipt_money`
			   	WHERE ((`ln_ins_receipt_money`.`status` = 1)
			   	AND (`s`.`id` = `ln_ins_receipt_money`.`loan_id`))) AS `total_principaid`,
			   	(SELECT
			   	SUM(`ln_ins_receipt_money`.`interest_paid`)
			   	FROM `ln_ins_receipt_money`
			   	WHERE ((`ln_ins_receipt_money`.`status` = 1)
			   	AND (`s`.`id` = `ln_ins_receipt_money`.`loan_id`))) AS `total_interest_paid`,
			   	(SELECT
			   	SUM(`ln_ins_receipt_money`.`total_paymentpaid`)
			   	FROM `ln_ins_receipt_money`
			   	WHERE ((`ln_ins_receipt_money`.`status` = 1)
			   	AND (`s`.`id` = `ln_ins_receipt_money`.`loan_id`))) AS `total_paymentpaid`
	   	FROM
	   	`ln_ins_sales_install` AS s,
	   	`ln_ins_client` AS c
	   	WHERE c.`client_id` = s.`customer_id` AND s.status=1 ";
   	if($search['branch_id']>0){
   		$where.=" AND `s`.`branch_id` = ".$search['branch_id'];
   	}
   	if($search['members']>0){
   		$where.=" AND c.`client_id` = ".$search['members'];
   	}
   	
   	if ($search['category']>0){
   		$where.=" AND s.`cate_id` = ".$search['category'];
   	}
   	if(!empty($search['adv_search'])){
   		$s_where = array();
   		$s_search = addslashes(trim($search['adv_search']));
   		$s_where[] = " s.sale_no LIKE '%{$s_search}%'";
   		$s_where[] = " c.client_number LIKE '%{$s_search}%'";
   		$s_where[] = " c.name_en LIKE '%{$s_search}%'";
   		$s_where[] = " c.name_kh LIKE '%{$s_search}%'";
   		$s_where[] = " s.selling_price LIKE '%{$s_search}%'";
   		$s_where[] = " s.duration LIKE '%{$s_search}%'";
   		$where .=' AND ('.implode(' OR ',$s_where).')';
   	}
   	$order=" ORDER BY s.id DESC";
   	return $db->fetchAll($sql.$where.$order);
   }
   public function getAllLnClient($search=null){
   	$db=$this->getAdapter();
//    	$start_date = $search['start_date'];
   	$end_date = $search['end_date'];
   	$sql = "SELECT 
		(SELECT   `lb`.`branch_namekh` FROM `ln_branch` `lb`  WHERE (`lb`.`br_id` = l.`branch_id`)  LIMIT 1) AS `branch_namekh`,
		`c`.`name_kh` AS `name_kh`,
		c.`client_number` AS client_number,
		`c`.`phone` AS phone_number,
			(SELECT   `lb`.`branch_namekh` FROM `ln_branch` `lb`  WHERE (`lb`.`br_id` = l.`branch_id`)  LIMIT 1) AS `branch_namekh`,
   	(SELECT `ln_village`.`village_namekh` FROM `ln_village` WHERE (`ln_village`.`vill_id` = `c`.`village_id`) LIMIT 1) AS `village_name`,
	(SELECT ln_commune.`commune_namekh` FROM `ln_commune` WHERE (ln_commune.`com_id` = `c`.`com_id`) LIMIT 1) AS `commune_name`,
	(SELECT `d`.`district_namekh` FROM `ln_district` `d` WHERE (`d`.`dis_id` = `c`.`dis_id`) LIMIT 1) AS `district_name`,
	(SELECT province_kh_name FROM `ln_province` WHERE province_id= c.pro_id  LIMIT 1) AS province_en_name,
		l.*,
		d.`date_payment` AS date_payment,
		d.`principle_after` AS principle_after,
		d.`total_interest` AS total_interest,
		`d`.`total_interest_after` AS `total_interest_after`,
		`d`.`total_payment`        AS `total_payment`,
		`d`.`installment_amount`   AS `times`,
		(SELECT
		     `crm`.`date_input`
		   FROM `ln_ins_receipt_money` `crm`
		   WHERE (`l`.`id` = `crm`.`loan_id`)
		   ORDER BY `crm`.`date_input` DESC
		   LIMIT 1) AS `last_pay_date`
		
		FROM 
		`ln_ins_sales_install` AS l,
		`ln_ins_sales_installdetail` d,
		`ln_ins_client` AS c
		WHERE l.`id` = d.`sale_id`
		AND c.`client_id` = l.`customer_id`
		AND l.`is_completed` = 0
		AND l.`status` = 1
		AND d.`status` = 1 
   		AND d.`is_completed` =0";
   	$where ='';
   	 
   	$to_date = (empty($search['end_date']))? '1': " d.`date_payment` = '".$search['end_date']." 00:00:00'";
   	$where= " AND  ".$to_date;
   	 
   	if($search['branch_id']>0){
   		$where.=" AND l.`branch_id` = ".$search['branch_id'];
   	}
   	if($search['members']>0){
   		$where.=" AND l.`customer_id` = ".$search['members'];
   	}
   	if(!empty($search['adv_search'])){
   		$s_where = array();
   		$s_search = trim(addslashes($search['adv_search']));
   		$s_where[] = " (SELECT `lb`.`branch_namekh` FROM `ln_branch` `lb`  WHERE (`lb`.`br_id` = l.`branch_id`)  LIMIT 1) LIKE '%{$s_search}%'";
   		$s_where[] = " l.sale_no LIKE '%{$s_search}%'";
   		$s_where[] = " `c`.`name_kh` LIKE '%{$s_search}%'";
   		$s_where[] = " d.`principle_after` LIKE '%{$s_search}%'";
   		$s_where[] = " `d`.`total_interest_after` LIKE '%{$s_search}%'";
   		$where .=' AND ( '.implode(' OR ',$s_where).')';
   	}
   	$order=" ORDER BY  d.`date_payment` DESC";
   	return $db->fetchAll($sql.$where.$order);
   }
   public function getALLLoanlate($search = null){
   	$end_date = $search['end_date'];
   	$db = $this->getAdapter();
   	$sql = "SELECT
	   	(SELECT   `lb`.`branch_namekh` FROM `ln_branch` `lb`  WHERE (`lb`.`br_id` = l.`branch_id`)  LIMIT 1) AS `branch_namekh`,
	   	(SELECT `ln_village`.`village_namekh` FROM `ln_village` WHERE (`ln_village`.`vill_id` = `c`.`village_id`) LIMIT 1) AS `village_name`,
		(SELECT ln_commune.`commune_namekh` FROM `ln_commune` WHERE (ln_commune.`com_id` = `c`.`com_id`) LIMIT 1) AS `commune_name`,
		(SELECT `d`.`district_namekh` FROM `ln_district` `d` WHERE (`d`.`dis_id` = `c`.`dis_id`) LIMIT 1) AS `district_name`,
		(SELECT province_kh_name FROM `ln_province` WHERE province_id= c.pro_id  LIMIT 1) AS province_en_name,
	   	`c`.`name_kh` AS `name_kh`,
	   	c.`client_number` AS client_number,
	   	`c`.`phone` AS phone_number,
	   	l.*,
	   	d.`date_payment` AS date_payment,
	   	d.`principle_after` AS principle_after,
	   	d.`total_interest` AS total_interest,
	   	`d`.`total_interest_after` AS `total_interest_after`,
	   	`d`.`total_payment`        AS `total_payment`,
	   	`d`.`installment_amount`   AS `times`,
	   	(SELECT
	   	`crm`.`date_input`
	   	FROM `ln_ins_receipt_money` `crm`
	   	WHERE (`l`.`id` = `crm`.`loan_id`)
	   	ORDER BY `crm`.`date_input` DESC
	   	LIMIT 1) AS `last_pay_date`,
	   	 COUNT(l.`id`) AS amount_late
	   	
	   	FROM
	   	`ln_ins_sales_install` AS l,
	   	`ln_ins_sales_installdetail` d,
	   	`ln_ins_client` AS c
	   	WHERE l.`id` = d.`sale_id`
	   	AND c.`client_id` = l.`customer_id`
	   	AND l.`is_completed` = 0
	   	AND l.`status` = 1
	   	AND d.`status` = 1
	   	AND d.`is_completed` =0";
	   	
	//    	$sql=" SELECT
	//    	`co_khname` AS co_name ,
	//    	co_code,
	//    	b.branch_namekh,
	//    	co.`co_id`,
	//    	c.`client_number`,
	//    	c.`name_kh`,
	//    	(SELECT `ln_village`.`village_namekh` FROM `ln_village` WHERE (`ln_village`.`vill_id` = `c`.`village_id`) LIMIT 1) AS `village_name`,
	//    	(SELECT ln_commune.`commune_namekh` FROM `ln_commune` WHERE (ln_commune.`com_id` = `c`.`com_id`) LIMIT 1) AS `commune_name`,
	//    	(SELECT `d`.`district_namekh` FROM `ln_district` `d` WHERE (`d`.`dis_id` = `c`.`dis_id`) LIMIT 1) AS `district_name`,
	//    	(SELECT province_kh_name FROM `ln_province` WHERE province_id= c.pro_id  LIMIT 1) AS province_en_name,
	//    	c.`phone`,
	//    	l.`loan_amount` as total_capital,
	//    	l.`loan_number`,
	//    	l.interest_rate  AS interest_rate,
	//    	l.`date_release`,
	//    	l.`date_line`,
	//    	l.`total_duration`,
	//    	l.`time_collect`,
	//    	l.`currency_type` AS curr_type,
	//    	l.`collect_typeterm`,
	//    	(SELECT `ln_currency`.`symbol` FROM `ln_currency` WHERE (`ln_currency`.`id` = l.`currency_type` ) LIMIT 1) AS `currency_type`,
	//    	(SELECT `ln_view`.`name_en` FROM `ln_view` WHERE ((`ln_view`.`type` = 14) AND (`ln_view`.`key_code` = `l`.`pay_term`)) LIMIT 1) AS `Term Borrow`,
	//    	(SELECT `crm`.`date_input` FROM (`ln_client_receipt_money` `crm`) WHERE ((`crm`.`loan_id` = l.`id`)) ORDER BY `crm`.`date_input` DESC LIMIT 1) AS `last_pay_date`,
	//    	SUM(d.`principle_after`) AS principle_after,
	//    	SUM(d.`total_interest_after`) AS total_interest_after,
	//    	SUM(d.`total_payment_after`) AS total_payment_after,
	//    	SUM(d.`penelize`) AS penelize,
	//    	d.`date_payment` ,
	//    	`d`.`installment_amount`   AS `times`,
	//    	COUNT(l.`id`) AS amount_late,
	//    	l.`branch_id`
	//    	FROM
	//    	`ln_loan_detail` AS d,
	//    	`ln_loan` AS l,
	//    	`ln_co` AS co,
	//    	`ln_client` AS c ,
	//    	`ln_branch` AS b
	//    	WHERE
	//    	d.`is_completed` = 0
	//    	AND `l`.`is_badloan`=0
	//    	AND l.`id` = d.`loan_id`
	//    	AND l.`status` = 1
	//    	AND d.`status`=1
	//    	AND co.`co_id` = l.`co_id`
	//    	AND c.`client_id` = l.`customer_id`
	//    	AND b.`br_id`=l.branch_id ";
	   	$where='';
	   	if(!empty($search['adv_search'])){
	   		$s_where = array();
	   		$s_search = trim(addslashes($search['adv_search']));
	   		$s_where[] = " (SELECT `lb`.`branch_namekh` FROM `ln_branch` `lb`  WHERE (`lb`.`br_id` = l.`branch_id`)  LIMIT 1) LIKE '%{$s_search}%'";
	   		$s_where[] = " l.sale_no LIKE '%{$s_search}%'";
	   		$s_where[] = " `c`.`name_kh` LIKE '%{$s_search}%'";
	   		$s_where[] = " d.`principle_after` LIKE '%{$s_search}%'";
	   		$s_where[] = " `d`.`total_interest_after` LIKE '%{$s_search}%'";
	   		$where .=' AND ( '.implode(' OR ',$s_where).')';
	   	}
	   	if(!empty($search['end_date'])){
	   		$where.=" AND d.date_payment < '$end_date'";
	   	}
	   	if($search['members']>0){
	   		$where.=" AND l.`customer_id` = ".$search['members'];
	   	}
	   	if($search['branch_id']>0){
	   		$where.=" AND l.`branch_id` = ".$search['branch_id'];
	   	}
	   	$group_by = " GROUP BY l.`id` ORDER BY d.`date_payment` ASC";
	   	return $db->fetchAll($sql.$where.$group_by);
   }
   
   public function getInstallmentCollectIcome($search=null){
   	$start_date = $search['start_date'];
   	$end_date = $search['end_date'];
   
   	$db = $this->getAdapter();
   	$sql = " SELECT
   	SUM(s.principal_paid) AS principal_paid,
   	SUM(s.`interest_paid`) AS interest_paid,
   	SUM(penalize_paid) AS penalize_paid
   	FROM `ln_ins_receipt_money` AS s WHERE s.status=1 ";
   	$where ='';
   	 
   	if(!empty($search['start_date']) or !empty($search['end_date'])){
   		$where.=" AND s.`date_input` BETWEEN '$start_date' AND '$end_date'";
   	}
   	if($search['branch_id']>0){
   		$where.=" AND s.`branch_id`= ".$search['branch_id'];
   	}
//    	if($search['currency_type']>0){
//    		$where.=" AND currency_type= ".$search['currency_type'];
//    	}
   	$order = " ORDER BY s.id DESC";//Group by currency_type 
   	return $db->fetchAll($sql.$where.$order);
   }
   
   function getRetailPurchaseByID($purchaseID){
   	$db = $this->getAdapter();
   	$sql="SELECT p.*,
		(SELECT b.buyer_name FROM `ln_ins_buyer` AS b WHERE b.id = p.`buyer_id` LIMIT 1) AS buyerName,
		(SELECT b.address FROM `ln_ins_buyer` AS b WHERE b.id = p.`buyer_id` LIMIT 1) AS buyerAddress,
		(SELECT b.occupation FROM `ln_ins_buyer` AS b WHERE b.id = p.`buyer_id` LIMIT 1) AS buyerOccupation,
		(SELECT s.sup_name FROM `ln_ins_supplier` AS s WHERE s.id = p.`supplier_id` LIMIT 1) AS supplier_name,
		(SELECT s.address FROM `ln_ins_supplier` AS s WHERE s.id = p.`supplier_id` LIMIT 1) AS supplierAddress,
		(SELECT s.occupation FROM `ln_ins_supplier` AS s WHERE s.id = p.`supplier_id` LIMIT 1) AS supplierOccupation,
		(SELECT s.age FROM `ln_ins_supplier` AS s WHERE s.id = p.`supplier_id` LIMIT 1) AS supplierAge,
		(SELECT s.sex FROM `ln_ins_supplier` AS s WHERE s.id = p.`supplier_id` LIMIT 1) AS supplierSex
		FROM ln_ins_purchase AS p WHERE p.id=$purchaseID AND p.`type`=2";
   	return $db->fetchRow($sql);
   }
   function getPurchaseDetailByID($purchaseID){
   	$db = $this->getAdapter();
   	$sql="SELECT pd.*,
   	(SELECT p.item_name FROM `ln_ins_product` AS p WHERE p.id = pd.`pro_id` LIMIT 1) AS item_name
   	FROM `ln_ins_purchase_detail` AS pd WHERE pd.`po_id` =$purchaseID";
   	return $db->fetchAll($sql);
   }
}

