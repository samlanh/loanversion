<?php
class Report_Model_DbTable_DbPawnSale extends Zend_Db_Table_Abstract
{
      
    public function getAllPawnSale($search){
    	$db = $this->getAdapter();
        $sql="SELECT
        			*,
        			(SELECT branch_nameen FROM `ln_branch` WHERE br_id = ps.branch_id LIMIT 1) AS branch_name ,
        			(SELECT name_en FROM `ln_view` WHERE type =11 AND ps.gender=key_code LIMIT 1) AS sex,
        			CONCAT(
						(select product_en from ln_pawnshopproduct as pspro where pspro.id = psp.product_id),
						'(',product_description,')',
						'(',(select name_kh from ln_clientsaving where client_id = customer_id),')'
					) as product_name,
					(SELECT CONCAT(first_name,' ', last_name) FROM rms_users as u WHERE u.id=ps.user_id )AS user_name,
					(select name_en from ln_view where type=3 and key_code = ps.status) as status
        		FROM 
        			ln_pawn_sale as ps,
    				ln_pawnshop as psp
    			where 
    				ps.pawn_id = psp.id	
          	";
        
        $from_date =(empty($search['start_date']))? '1': "selling_date >= '".$search['start_date']." 00:00:00'";
        $to_date = (empty($search['end_date']))? '1': "selling_date <= '".$search['end_date']." 23:59:59'";
        $where = " AND ".$from_date." AND ".$to_date;
        
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
        if($search['status']>-1){
        	$where .= " and ps.status = ".$search['status'];
        }
        $dbp = new Application_Model_DbTable_DbGlobal();
        $where.=$dbp->getAccessPermission('ps.branch_id');
        
        $order_by = " ORDER BY ps.id DESC ";
    	return $db->fetchAll($sql.$where.$order_by);
    }
	
}

