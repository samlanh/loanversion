<?php
class Report_Model_DbTable_DbLnClient extends Zend_Db_Table_Abstract
{
      
       protected  $db_name='ln_client';

    public function getAllLnClient($search = null){
    	 $db = $this->getAdapter();
    	 
//     	 $db=$this->getAdapter();
    	  
//     	 $from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
//     	 $to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
//     	 $where = " AND ".$from_date." AND ".$to_date;
    	  
//     	 $sql = " SELECT * FROM v_getclientblacklist WHERE 1";
//     	 if(!empty($search['adv_search'])){
//     	 	$s_where = array();
//     	 	$s_search = trim($search['adv_search']);
//     	 	$s_where[] = "branch_name LIKE '%{$s_search}%'";
//     	 	$s_where[] = "client_number LIKE '%{$s_search}%'";
//     	 	$s_where[] = "client_name LIKE '%{$s_search}%'";
//     	 	$s_where[] = "sex LIKE '%{$s_search}%'";
//     	 	$s_where[] = " situation LIKE '%{$s_search}%'";
//     	 	$s_where[] = " doc_name LIKE '%{$s_search}%'";
//     	 	$s_where[] = " id_number LIKE '%{$s_search}%'";
//     	 	$s_where[] = " street LIKE '%{$s_search}%'";
//     	 	$s_where[] = " house LIKE '%{$s_search}%'";
//     	 	$s_where[] = " village_name LIKE '%{$s_search}%'";
//     	 	$s_where[] = " commune_name LIKE '%{$s_search}%'";
//     	 	$s_where[] = " district_name LIKE '%{$s_search}%'";
//     	 	$s_where[] = " province_name LIKE '%{$s_search}%'";
//     	 	$where .=' AND ('.implode(' OR ',$s_where).')';
//     	 }
//     	 if(!empty($search['branch_id'])){
//     	 	$where.= " AND branch_id = ".$search['branch_id'];
//     	 }
//     	 $order = " ORDER BY id DESC ";
//     	 return $db->fetchAll($sql.$where.$order);

    	 $from_date =(empty($search['start_date']))? '1': "create_date >= '".$search['start_date']." 00:00:00'";
    	 $to_date = (empty($search['end_date']))? '1': "create_date <= '".$search['end_date']." 23:59:59'";
    	 $where = " AND ".$from_date." AND ".$to_date;
          $sql=" SELECT * FROM v_getallclient WHERE 1";
          if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = $search['adv_search'];
			$s_where[] = " branch_name LIKE '%{$s_search}%'";
			$s_where[] = " client_number LIKE '%{$s_search}%'";
			$s_where[] = " client_name LIKE '%{$s_search}%'";
			$s_where[] = " situation LIKE '%{$s_search}%'";
			$s_where[] = " doc_name LIKE '%{$s_search}%'";
			$s_where[] = " id_number LIKE '%{$s_search}%'";
			$s_where[] = " job LIKE '%{$s_search}%'";
			
			$s_where[] = " phone LIKE '%{$s_search}%'";
			$s_where[] = " house LIKE '%{$s_search}%'";
			$s_where[] = " street LIKE '%{$s_search}%'";
			$s_where[] = " village_name LIKE '%{$s_search}%'";
			$s_where[] = " com_name LIKE '%{$s_search}%'";
			
			$s_where[] = " pro_name LIKE '%{$s_search}%'";
			$s_where[] = " joint_doc_type LIKE '%{$s_search}%'";
			$s_where[] = " join_nation_id LIKE '%{$s_search}%'";
			
			$where .=' AND ('.implode(' OR ',$s_where).')';
			}
// 			if($search['status']>-1){
// 				$where.= " AND status = ".$search['status'];
// 			}
			if($search['province']>0){
				$where.=" AND pro_id= ".$search['province'];
			}
			if($search['district']>0){
				$where.=" AND dis_id= ".$search['district'];
			}
			if($search['commune']>0){
				$where.=" AND com_id= ".$search['commune'];
			}
			if($search['village']>0){
				$where.=" AND village_id= ".$search['village'];
			}
			if($search['branch_id']>0){
				$where.=" AND branch_id= ".$search['branch_id'];
			}
			$order=" ORDER BY client_id DESC";
// 			echo $sql.$where.$order;
	          return $db->fetchAll($sql.$where.$order);
    } 
    public function getAllGroup(){
    	$db = $this->getAdapter();
    	$sql="SELECT client_number,name_kh,name_en,sex,status,(SELECT branch_namekh FROM ln_branch WHERE br_id =branch_id limit 1) AS branch_name
    	,pro_id,dis_id,com_id,village_id,spouse_name,phone FROM ln_client ORDER BY client_id";
    	return $db->fetchAll($sql);
    }
    public function getAllCalleteral($search=null){
    	$db = $this->getAdapter();
    	$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
		$sql =" SELECT id ,branch_name ,co_id ,collecteral_code,client_code ,client_id,client_name,name_kh, join_with , relative , 
		collecteral_type,collecteral_owner,number_collecteral,issue_date,collecteral_title_kh,collecteral_title_en,date ,note ,status ,is_return FROM 
		`v_getallcallateral` WHERE 1";
		if($search['status_search']>-1){
 			$where.=" AND status=".$search['status_search'];
 		}
		if(!empty($search['branch_id'])){
			$where.=" AND branch_id = ".$search['branch_id'];
		}
		if(!empty($search['adv_search'])){
			$s_where=array();
			$s_search=trim($search['adv_search']);
			
			$s_where[]=" number_collecteral LIKE'%{$s_search}%'";
			$s_where[]=" collecteral_owner LIKE'%{$s_search}%'";
			$s_where[]=" collecteral_title_en LIKE'%{$s_search}%'";
			$s_where[]=" collecteral_type LIKE'%{$s_search}%'";
			$s_where[]=" branch_name LIKE'%{$s_search}%'";
			$s_where[]=" co_id LIKE'%{$s_search}%'";
			$s_where[]=" collecteral_code LIKE'%{$s_search}%'";
			$s_where[]=" client_code LIKE'%{$s_search}%'";
			$s_where[]=" name_kh LIKE'%{$s_search}%'";
			$s_where[]=" client_name LIKE'%{$s_search}%'";
			$s_where[]=" join_with LIKE'%{$s_search}%'";
			$s_where[]=" relative LIKE'%{$s_search}%'";
			$s_where[]=" note LIKE'%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		$order = " ORDER BY client_id ";
    	return $db->fetchAll($sql.$where.$order);
    }
function geteAllcallteral($search=null){
		$db = $this->getAdapter();
		
		$from_date =(empty($search['start_date']))? '1': "date_registration >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "date_registration <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
		
		$sql=" SELECT * FROM ln_client_callecteral ";
		
		if($search['status_search']>-1){
			$where.=" AND status=".$search['status_search'];
		}
		if(!empty($search['branch_id'])){
			$where.=" AND ln_client_callecteral.branch_id = ".$search['branch_id'];
		}
		if(!empty($search['co_name'])){
			$where.=" AND co_id = ".$search['co_name'];
		}
		if(!empty($search['collteral_type'])){
			$where.=" AND callate_type = ".$search['collteral_type'];
		}
		if(!empty($search['client_code'])){
			$where.=" AND client_code = ".$search['client_code'];
		}
		if(!empty($search['adv_search'])){
			$s_where=array();
			$s_search=$search['adv_search'];
			$s_where[]="code_call LIKE '%{$s_search}%'";
			$s_where[]="owner LIKE'%{$s_search}%'";
			$s_where[]="number_collteral LIKE'%{$s_search}%'";
			$s_where[]="note LIKE'%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		$order = " ORDER BY id DESC ";
		//echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);
	}
	function getAllChangeCollteral($search=null){
		$db = $this->getAdapter();
	
		$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;	
			$sql="SELECT * FROM v_getchangcolleral WHERE 1 ";
				$where='';
				if($search['status_search']>-1){
					$where.=" AND statuss =".$search['status_search'];
				}
				if(!empty($search['branch_id'])){
					$where.=" AND branch_id = ".$search['branch_id'];
				}
// 				if(!empty($search['client_name'])){
// 					$where.=" AND client_id = ".$search['client_name'];
// 				}
				if(!empty($search['adv_search'])){
					$s_where=array();
					$s_search=trim($search['adv_search']);
					$s_where[]="branch_name LIKE '%{$s_search}%'";
					$s_where[]="client_name LIKE '%{$s_search}%'";
					$s_where[]="collecteral_code LIKE '%{$s_search}%'";
					$s_where[]="collecteral_title_old LIKE '%{$s_search}%'";
					$s_where[]="collecteral_owner LIKE '%{$s_search}%'";
					$s_where[]="from_owner_name LIKE '%{$s_search}%'";
					$s_where[]="from_number_collateral LIKE '%{$s_search}%'";
					$s_where[]="collecteral_title_en LIKE '%{$s_search}%'";
					$s_where[]="collecteral_owner LIKE '%{$s_search}%'";
					$s_where[]="number_collateral LIKE '%{$s_search}%'";
					$s_where[]="note LIKE '%{$s_search}%'";
					$where .=' AND ('.implode(' OR ',$s_where).')';
				}
			return $db->fetchAll($sql.$where);
	}
	function getCalleteralByClient($client_id){
			$db = $this->getAdapter();
			$sql =" SELECT id ,branch_name ,co_id ,collecteral_code,client_code ,client_id,client_name,name_kh, join_with , relative ,spouse_name ,owner_name,
			collecteral_type,collecteral_owner,number_collecteral,issue_date,collecteral_title_en,collecteral_title_kh,date ,note ,status ,is_return FROM
			`v_getallcallateral` WHERE client_id = $client_id AND status=1 and is_return=0 ";
			$order = " ORDER BY client_id ";
			return $db->fetchAll($sql.$order);
	}
	function getChangeCollteralByClientId($client_id){
		$db = $this->getAdapter();
	
		$sql=" SELECT
			  (SELECT
			     `ln_branch`.`branch_namekh`
			   FROM `ln_branch`
			   WHERE (`ln_branch`.`br_id` = `c`.`branch_id`)) AS `branch_name`,
			  `c`.`client_id`               AS `client_id`,
			  `c`.`branch_id`               AS `branch_id`,
			  `c`.`date`                    AS `date`,
			  `c`.`status`                  AS `statuss`,
			  `c`.`note`                    AS `notes`,
			  (SELECT
			     `cc`.`collecteral_code`
			   FROM `ln_client_callecteral` `cc`
			   WHERE (`cc`.`id` = `cd`.`change_id`)
			   LIMIT 1) AS `collecteral_code`,
			  (SELECT
			     `ct`.`title_kh`
			   FROM `ln_callecteral_type` `ct`
			   WHERE (`ct`.`id` = `cd`.`from_collateral_type`)
			   LIMIT 1) AS `collecteral_title_old`,
			  (SELECT
			     `ct`.`title_kh`
			   FROM `ln_callecteral_type` `ct`
			   WHERE (`ct`.`id` = `cd`.`collateral_type`)
			   LIMIT 1) AS `collecteral_title_en`,
			  (SELECT
			     `ln_view`.`name_kh`
			   FROM `ln_view`
			   WHERE ((`ln_view`.`type` = 21)
			          AND (`ln_view`.`key_code` = `cd`.`owner_id`))
			   LIMIT 1) AS `collecteral_owner`,
			  (SELECT
			     CONCAT(`ln_client`.`client_number`,' - ',`ln_client`.`name_kh`,' - ',`ln_client`.`name_en`)
			   FROM `ln_client`
			   WHERE (`ln_client`.`client_id` = `c`.`client_id`)
			   LIMIT 1) AS `client_name`,
			  `cd`.`id`                     AS `id`,
			  `cd`.`change_id`              AS `change_id`,
			  `cd`.`client_coll_id`         AS `client_coll_id`,
			  (SELECT
			     `ln_client_callecteral_detail`.`collecteral_code`
			   FROM `ln_client_callecteral_detail`
			   WHERE (`ln_client_callecteral_detail`.`id` = `cd`.`client_coll_id`)
			   LIMIT 1) AS `from_collcode`,
			   (SELECT
			     `ln_client_callecteral_detail`.`issue_date`
			   FROM `ln_client_callecteral_detail`
			   WHERE (`ln_client_callecteral_detail`.`id` = `cd`.`client_coll_id`)
			   LIMIT 1) AS `from_issue_date`,
			   (SELECT
			     `ln_client_callecteral_detail`.`note`
			   FROM `ln_client_callecteral_detail`
			   WHERE (`ln_client_callecteral_detail`.`changecollteral_id` = `cd`.`id`)
			   LIMIT 1) AS `from_issueby`,
			   
			  (SELECT
			     `ln_client_callecteral_detail`.`collecteral_code`
			   FROM `ln_client_callecteral_detail`
			   WHERE (`ln_client_callecteral_detail`.`changecollteral_id` = `cd`.`id`)
			   LIMIT 1) AS `to_collcode`,
			  `cd`.`from_id`                AS `from_id`,
			  `cd`.`from_collateral_type`   AS `from_collateral_type`,
			  `cd`.`from_owner_id`          AS `from_owner_id`,
			  `cd`.`from_owner_name`        AS `from_owner_name`,
			  `cd`.`from_number_collateral` AS `from_number_collateral`,
			  `cd`.`from_note`              AS `from_note`,
			  `cd`.`to_id`                  AS `to_id`,
			  `cd`.`collateral_type`        AS `collateral_type`,
			  `cd`.`owner_id`               AS `owner_id`,
			  `cd`.`toowner_name`           AS `toowner_name`,
			  `cd`.`number_collateral`      AS `number_collateral`,
			  `cd`.`note`                   AS `note`,
			  `cd`.`status`                 AS `status`,
			  `cd`.`is_changed`             AS `is_changed`,
			  `cd`.`issue_date`             AS `issue_date`
			FROM (`ln_changecollteral` `c`
			   JOIN `ln_changecollteral_detail` `cd`)
			WHERE ((`c`.`id` = `cd`.`change_id`)
			       AND (`cd`.`status` = 1)
			       AND `c`.`client_id`=$client_id ) ";
		return $db->fetchAll($sql);
	}
	function getClientById($client_id){
		$db = $this->getAdapter();
		$sql =" SELECT
					  `cl`.`client_number` AS `client_code`,
					  `cl`.`name_kh` AS `name_kh`,
					  `cl`.`name_en` AS `client_name`,
					  (SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=cl.sex) AS gender,
					  `cl`.`client_id`          AS `client_id`,
					  `cl`.`branch_id`          AS `branch_id`,
					   cl.`join_with`,
					   cl.`relate_with`,
					   cl.`spouse_name`,
					   cl.dob_guarantor,
					   cl.guarantor_with,
					   cl.dob,
					   cl.house,cl.street,
					   (SELECT `ln_village`.`village_namekh` FROM `ln_village` WHERE (`ln_village`.`vill_id` = `cl`.`village_id`)) AS `village_name`,
					   (SELECT `c`.`commune_name` FROM `ln_commune` `c` WHERE (`c`.`com_id` = `cl`.`com_id`) LIMIT 1) AS `commune_name`,
					   (SELECT `d`.`district_namekh` FROM `ln_district` `d` WHERE (`d`.`dis_id` = `cl`.`dis_id`) LIMIT 1) AS `district_name`,
					   (SELECT province_kh_name FROM `ln_province` WHERE province_id= cl.pro_id  LIMIT 1) AS province_en_name,
					   (SELECT name_kh FROM `ln_view` WHERE id = cl.client_d_type LIMIT 1) AS client_doctype,
					   cl.nation_id,
					   cl.dob_join_acc 
					FROM 
					ln_client AS cl 
					WHERE ( cl.client_id = $client_id ) ";
		return $db->fetchRow($sql);
	}
}

