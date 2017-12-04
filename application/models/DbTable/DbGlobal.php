<?php

class Application_Model_DbTable_DbGlobal extends Zend_Db_Table_Abstract
{
	public function setName($name){
		$this->_name=$name;
	}
	public static function getUserId(){
		$session_user=new Zend_Session_Namespace('authloan');
		return $session_user->user_id;
	
	}
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	function getCurrentDatePayment($id){
		$db = $this->getAdapter();
		$sql="SELECT c.`date_input` FROM `ln_client_receipt_money` AS c WHERE c.`id`=$id ";
		return $db->fetchOne($sql);
	}
	function getLastDatePayment($id){
		$db = $this->getAdapter();
		$sql="SELECT crm.`date_input` FROM `ln_client_receipt_money` AS crm,`ln_client_receipt_money_detail` AS crmd WHERE crm.`id`!=$id AND crm.`id`=(SELECT crl.`crm_id` FROM `ln_client_receipt_money_detail` AS crl WHERE crl.`crm_id`=crm.`id` AND crl.`loan_number`=(SELECT c.loan_number FROM `ln_client_receipt_money_detail` AS c WHERE c.`crm_id`=crmd.id AND c.`crm_id`=$id LIMIT 1) LIMIT 1)  ORDER BY crm.`date_input` DESC LIMIT 1 ";
		return $db->fetchOne($sql);
	}
	public function getLoanNumberByBranch($type){
		$db = $this->getAdapter();
// 		if($type==1){
			$sql="SELECT l.`id` AS id,
				CONCAT((SELECT name_kh FROM `ln_client` WHERE client_id = l.customer_id LIMIT 1),'-',l.`loan_number`) AS `name`,
				l.`branch_id` 
				FROM `ln_loan` AS l
				WHERE 
					l.`is_completed` = 0 
			  		AND l.status=1 
			  		AND l.is_badloan=0 ORDER BY l.loan_number DESC ";
			return $db->fetchAll($sql);
// 		}else{
// 			$sql="SELECT m.`loan_number` AS id,m.`loan_number` AS `name`,g.`branch_id` FROM `ln_loan_member` AS m,`ln_loan_group` AS g WHERE m.`group_id`= g.`g_id` AND m.`is_completed`=0 AND g.`loan_type`=2 AND m.status=1 AND g.status=1 GROUP BY m.`loan_number` ";
// 			return $db->fetchAll($sql);
// 		}
	}
	public function getGlobalDb($sql)
  	{
  		$db=$this->getAdapter();
  		$row=$db->fetchAll($sql);  		
  		if(!$row) return NULL;
  		return $row;
  	}
  	public function getGlobalDbRow($sql)
  	{
  		$db=$this->getAdapter();  		
  		$row=$db->fetchRow($sql);
  		if(!$row) return NULL;
  		return $row;
  	}
  	public static function getActionAccess($action)
    {
    	$arr=explode('-', $action);
    	return $arr[0];    	
    }     
    public function isRecordExist($conditions,$tbl_name){
		$db=$this->getAdapter();		
		$sql="SELECT * FROM ".$tbl_name." WHERE ".$conditions." LIMIT 1"; 
		$row= count($db->fetchRow($sql));
		if(!$row) return NULL;
		return $row;	
    }
    /*for select 1 record by id of earch table by using params*/
    public function GetRecordByID($conditions,$tbl_name){
    	$db=$this->getAdapter();
    	$sql="SELECT * FROM ".$tbl_name." WHERE ".$conditions." LIMIT 1";
    	$row = $this->fetchRow($sql);
    	return $row;
    	$row= $db->fetchRow($sql);
    	return $row;
    }
    /**
     * insert record to table $tbl_name
     * @param array $data
     * @param string $tbl_name
     */
    public function addRecord($data,$tbl_name){
    	$this->setName($tbl_name);
    	return $this->insert($data);
    }
    public function updateRecord($data,$id,$tbl_name){
    	$this->setName($tbl_name);
    	$where=$this->getAdapter()->quoteInto('id=?',$id);
    	$this->update($data,$where);    	
    }   
    public function DeleteRecord($tbl_name,$id){
    	$db = $this->getAdapter();
		$sql = "UPDATE ".$tbl_name." SET status=0 WHERE id=".$id;
		return $db->query($sql);
    } 
     public function DeleteData($tbl_name,$where){
    	$db = $this->getAdapter();
		$sql = "DELETE FROM ".$tbl_name.$where;
		return $db->query($sql);
    } 
    public function getDayInkhmerBystr($str){
    	
    	$rs=array(
    			'Mon'=>'ច័ន្ទ',
    			'Tue'=>'អង្គារ',
    			'Wed'=>'ពុធ',
    			'Thu'=>"ព្រហ",
    			'Fri'=>"សុក្រ",
    			'Sat'=>"សៅរី",
    			'Sun'=>"អាទិត្យ");
    	if($str==null){
    		return $rs;
    	}else{
    	return $rs[$str];
    	}
    
    }
    public function convertStringToDate($date, $format = "Y-m-d H:i:s")
    {
    	if(empty($date)) return NULL;
    	$time = strtotime($date);
    	return date($format, $time);
    }   
    public static function getResultWarning(){
          return array('err'=>1,'msg'=>'មិន​ទាន់​មាន​ទន្និន័យ​នូវ​ឡើយ​ទេ!');	
    }
   /*@author Mok Channy
    * for use session navigetor 
    * */
//    public static function SessionNavigetor($name_space,$array=null){
//    	$session_name = new Zend_Session_Namespace($name_space);
//    	return $session_name;   	
//    }
   public function getAllProvince(){
   	$this->_name='ln_province';
   	$sql = " SELECT province_id,CONCAT(province_kh_name) province_en_name FROM $this->_name WHERE status=1 AND province_kh_name!='' ORDER BY province_id DESC";
   	$db = $this->getAdapter();
   	return $db->fetchAll($sql);
   }
   public function getAllDistrict(){
   	$this->_name='ln_district';
   	$sql = " SELECT dis_id,pro_id,CONCAT(district_name,'-',district_namekh) district_name FROM $this->_name WHERE status=1 AND district_name!='' ";
   	$db = $this->getAdapter();
   	return $db->fetchAll($sql);
   }
   public function getAllDistricts(){
   	$this->_name='ln_district';
   	$sql = " SELECT dis_id AS id,pro_id,CONCAT(district_namekh) name FROM $this->_name WHERE status=1 AND district_name!='' ";
   	$db = $this->getAdapter();
   	return $db->fetchAll($sql);
   }
   public function getCommune(){
   	$this->_name='ln_commune';
   	$sql = " SELECT com_id,com_id AS id,commune_name,CONCAT(commune_namekh) AS name,district_id FROM $this->_name WHERE status=1 AND commune_name!='' ";
   	$db = $this->getAdapter();
   	return $db->fetchAll($sql);
   }
//    public function getCommuneByDistrict($district_id){
// 	   	$this->_name='ln_commune';
// 	   	$sql = " SELECT com_id,com_id AS id,commune_name,CONCAT(commune_name,'-',commune_namekh) AS name,district_id FROM $this->_name WHERE status=1 AND 
// 	   	commune_name!='' AND district_id=$district_id ORDER BY commune_name ASC";
// 	   	$db = $this->getAdapter();
// 	   	return $db->fetchAll($sql);
//    }
   
   public function getVillage(){
   	$this->_name='ln_village';
   	$sql = " SELECT vill_id,vill_id AS id,village_name,CONCAT(village_namekh) AS name,commune_id FROM $this->_name WHERE status=1 AND village_name!='' ";
   	$db = $this->getAdapter();
   	return $db->fetchAll($sql);
   }
   public function getZoneList($option=null){
   	$this->_name='ln_zone';
   	$sql = " CALL `stGetAllZone`() ";
   	$db = $this->getAdapter();
   	$rows =  $db->fetchAll($sql);
   	if(!empty($rows)){
   	if($option!=null){
   		$request=Zend_Controller_Front::getInstance()->getRequest();
   		$action = $request->getControllerName();
   		if($action=='loan'){$options=array(-1=>"ជ្រើសរើសតំបន់");}
   		if(!empty($rows))foreach($rows as $rs){
   				$options[$rs['zone_id']]=$rs['zone_name'].' - '.$rs['zone_num'];}
   				return $options;
   	}
   	}
   	return $rows;
   }
   public function getAllCOName($option=null){
   	$this->_name='ln_co';
   	$sql = " call stGetAllCOName();";
   	$db = $this->getAdapter();
   	$rows =  $db->fetchAll($sql);
   	$options = array(''=>'----Select Credit Officer ----','-1'=>'Add New');
   	if(!empty($rows)){
   	if($option!=null){
   			foreach($rows as $rs){
   				$options[$rs['co_id']]=$rs['co_firstname']." - ".$rs['co_khname'];}
   				return $options;
   		}
   	}
   	return $rows;
   }
   public function getAllCoNameOnly(){
   	$db= $this->getAdapter();
   	$sql = " SELECT co_id AS id, CONCAT(co_firstname,' - ',co_khname,' - ',co_code) AS name
   	  FROM ln_co WHERE STATUS=1 AND co_khname!='' AND `position_id`=1 ";
   	return $db->fetchAll($sql);
   }
   public function getAllCurrency($id,$opt = null){
	   	$sql = "SELECT * FROM ln_currency WHERE status = 1 ";
	   	if($id!=null){
	   		$sql.=" AND id = $id";
	   	}
	   	$rows = $this->getAdapter()->fetchAll($sql);
	   	if($opt!=null){
	   		$options="";
	   		if(!empty($rows))foreach($rows AS $row){
	   			$options[$row['id']]=($row['displayby']==1)?$row['displayby']:$row['curr_nameen'];
	   		}
	   		return $options;
	   	}else{
	   		return $rows;
	   	}
   	
   }
   public function getNewReceiptId(){
   	$this->_name='ln_callecteralllist';
   	$db = $this->getAdapter();
   	$sql=" SELECT id ,code_call FROM $this->_name ORDER BY id DESC LIMIT 1 ";
   	$acc_no = $db->fetchOne($sql);
   	$new_acc_no= (int)$acc_no+1;
   	$acc_no= strlen((int)$acc_no+1);
   	$pre = "";
   	for($i = $acc_no;$i<5;$i++){
   		$pre.='0';
   	}
   	return $pre.$new_acc_no;
   }
   
   public function getCodecallId(){
   	$this->_name='ln_callecteralllist';
   	$db = $this->getAdapter();
   	$sql=" SELECT id ,code_call FROM $this->_name ORDER BY id DESC LIMIT 1 ";
   	$acc_no = $db->fetchOne($sql);
   	$new_acc_no= (int)$acc_no+1;
   	$acc_no= strlen((int)$acc_no+1);
   	$pre = "";
   	for($i = $acc_no;$i<5;$i++){
   		$pre.='0';
   	}
   	return $pre.$new_acc_no;
   }
   
   public function getNewClientId(){
   	$this->_name='ln_client';
   	$db = $this->getAdapter();
   	$sql=" SELECT client_id ,client_number FROM $this->_name ORDER BY client_id DESC LIMIT 1 ";
   	$acc_no = $db->fetchOne($sql);
   	$new_acc_no= (int)$acc_no+1;
   	$acc_no= strlen((int)$acc_no+1);
   	$pre = "";
   	for($i = $acc_no;$i<6;$i++){
   		$pre.='0';
   	}
   	return $pre.$new_acc_no;
   }
   public function getNewInvoiceExchange(){
   	$this->_name='ln_exchange';
   	$db = $this->getAdapter();
   	$sql=" SELECT id FROM $this->_name ORDER BY id DESC LIMIT 1 ";
   	$acc_no = $db->fetchOne($sql);
   	$new_acc_no= (int)$acc_no+1;
   	$acc_no= strlen((int)$acc_no+1);
   	$pre = "";
   	for($i = $acc_no;$i<6;$i++){
   		$pre.='0';
   	}
   	return $pre.$new_acc_no;
   }
   public function getLoanNumber($data=array('branch_id'=>1,'is_group'=>0)){
   	$this->_name='ln_loan';
   	$db = $this->getAdapter();
   	if(empty($data['is_group'])){
   		$data['is_group']=0;
   	}
   	if(($data['is_group'])!=0){
   		$sql = "SELECT COUNT(id)  FROM `ln_loan` WHERE branch_id=".$data['branch_id']." AND loan_type=2 LIMIT 1 ";
   		$pre = $this->getPrefixCode($data['branch_id'])."GL";
   	}else{
   		$sql=" SELECT COUNT(id)  FROM $this->_name WHERE branch_id=".$data['branch_id']." LIMIT 1 ";
   		$pre = $this->getPrefixCode($data['branch_id'])."L";
   	}
   
   	$acc_no = $db->fetchOne($sql);
   	
   	$new_acc_no= (int)$acc_no+1;
   	$acc_no= strlen((int)$acc_no+1);
   	
   	for($i = $acc_no;$i<5;$i++){
   		$pre.='0';
   	}
   	return $pre.$new_acc_no;
   }
   public function getSavingNumber($data=array('branch_id'=>2)){
   	$this->_name='ln_savingaccount';
   	$db = $this->getAdapter();
   		$sql=" SELECT COUNT(id) FROM $this->_name WHERE branch_id=".$data['branch_id']." LIMIT 1 ";
   		$pre = $this->getPrefixCode($data['branch_id'])."S";
   	$acc_no = $db->fetchOne($sql);
   	$new_acc_no= (int)$acc_no+1;
   	$acc_no= strlen((int)$acc_no+1);
   	for($i = $acc_no;$i<5;$i++){
   		$pre.='0';
   	}
   	return $pre.$new_acc_no;
   }
   function getPrefixCode($branch_id){
   	$db  = $this->getAdapter();
   	$sql = " SELECT prefix FROM `ln_branch` WHERE br_id = $branch_id  LIMIT 1";
   	return $db->fetchOne($sql);
   }
   public function getStaffNumberByBranch($branch_id){
   	$this->_name='ln_co';
   	$db = $this->getAdapter();
   		$sql = "SELECT COUNT(co_id)FROM $this->_name WHERE branch_id=".$branch_id." LIMIT 1 ";
   		$pre = $this->getPrefixCode($branch_id)."-";
   	$acc_no = $db->fetchOne($sql);
   
   	$new_acc_no= (int)$acc_no+1;
   	$acc_no= strlen((int)$acc_no+1);
   
   	for($i = $acc_no;$i<5;$i++){
   		$pre.='0';
   	}
   	return $pre.$new_acc_no;
   }
 
   public function getClientByType($type=null,$client_id=null ,$row=null){
   $this->_name='ln_client';
   $where='';
   if($type!=null){
   	$where=' AND is_group = 1';
   }
   	$sql = " SELECT client_id,name_kh,name_en,client_number,
   				(SELECT `ln_village`.`village_namekh` FROM `ln_village` WHERE (`ln_village`.`vill_id` = `ln_client`.`village_id`) limit 1) AS `village_name`,
				(SELECT `c`.`commune_namekh` FROM `ln_commune` `c` WHERE (`c`.`com_id` = `ln_client`.`com_id`) LIMIT 1) AS `commune_name`,
				(SELECT `d`.`district_namekh` FROM `ln_district` `d` WHERE (`d`.`dis_id` = `ln_client`.`dis_id`) LIMIT 1) AS `district_name`,
				(SELECT province_kh_name FROM `ln_province` WHERE province_id= ln_client.pro_id  LIMIT 1) AS province_en_name

   	FROM $this->_name WHERE status=1 AND name_en!=''";
   
   	$db = $this->getAdapter();
   	if($row!=null){
   		if($client_id!=null){ $where.=" AND client_id  =".$client_id ." LIMIT 1";}
   		return $db->fetchRow($sql.$where);
   	}
   	return $db->fetchAll($sql.$where);
   }
   
   public function getAssetByType($type=null,$Asset_id=null ,$row=null){
   	$this->_name='ln_account_name';
   	$where='';
   	if($type!=null){
   		$where=' AND is_group = 1';
   	}
   	$sql = "SELECT id,account_code,account_name_en FROM $this->_name WHERE STATUS=1 AND parent_id=49";
   
   	$db = $this->getAdapter();
   	if($row!=null){
   		if($Asset_id!=null){
   			$where.=" AND id  =".$Asset_id ." LIMIT 1";
   		}
   		return $db->fetchRow($sql.$where);
   	}
   	return $db->fetchAll($sql.$where);
   }
   
   public function getOwnerByType($type=null,$customer_id=null ,$row=null){
   	$this->_name='ln_callecteralllist';
   	$where='';
   	if($type!=null){
   		$where=' AND is_group = 1';
   	}
   	$sql = "SELECT branch,receipt,code_call,
            customer_id,(SELECT name_en FROM ln_client WHERE client_id=customer_id) AS customer_name,
   			type_call,owner_call,callnumber,create_date,date_debt,
   			term,amount_term,date_line,curr_type,amount_debt,note,user_id,status,is_verify,verify_by,
   			is_fund FROM $this->_name  WHERE status=1 AND customer_id!='' ";
   	$db = $this->getAdapter();
   	if($row!=null){
   		if($customer_id!=null){
   			$where.=" AND id  =".$customer_id ." LIMIT 1";
   		}
   		return $db->fetchRow($sql.$where);
   	}
   	return $db->fetchAll($sql.$where);
   }
    
   
   public static function getCurrencyType($curr_type){
   	$curr_option = array(
   			1=>'រៀល',
   			2=>'ដុល្លា'
   			);
   	return $curr_option[$curr_type];
   	
   }
   public function getAllSituation($id = null){
   	$_status = array(
   			1=>$this->tr->translate("Single"),
   			2=>$this->tr->translate("Married"),
   			3=>$this->tr->translate("Windowed"),
   			4=>$this->tr->translate("Mindowed")
   	);
   	if($id==null)return $_status;
   	else return $_status[$id];
   }
   public function GetAllIDType($id = null){
   	$_status = array(
   			1=>$this->tr->translate("National ID"),
   			2=>$this->tr->translate("Family Book"),
   			3=>$this->tr->translate("Resident Book"),
   			4=>$this->tr->translate("Other")
   	);
   	if($id==null)return $_status;
   	else return $_status[$id];
   }
   public function getAllDegree($id=null){
   	$tr= Application_Form_FrmLanguages::getCurrentlanguage();
   	$opt_degree = array(
   			''=>$this->tr->translate("----ជ្រើសរើស----"),
   			1=>$this->tr->translate("Diploma"),
   			2=>$this->tr->translate("Associate"),
   			3=>$this->tr->translate("Bechelor"),
   			4=>$this->tr->translate("Master"),
   			5=>$this->tr->translate("PhD")
   	);
   	if($id==null)return $opt_degree;
   	else return $opt_degree[$id]; 
  }
  public function getAllBranchName($branch_id=null){
  	$db = $this->getAdapter();
  	$sql= "SELECT br_id,branch_namekh,
  	branch_nameen,br_address,branch_code,branch_tel,displayby
  	FROM `ln_branch` WHERE branch_namekh !=''";
  	if($branch_id!=null){
  		$sql.=" AND br_id=$branch_id LIMIT 1";
  	}
  	return $db->fetchAll($sql);
  }
  function countDaysByDate($start,$end){
  	$first_date = strtotime($start);
  	$second_date = strtotime($end);
  	$offset = $second_date-$first_date;
  	return floor($offset/60/60/24);
  
  }

 public function returnAfterHoliday($holiday_option,$date){
	  $rs = $this->checkHolidayExist($holiday_option,$date);
	  if(is_array($rs)){
	  	$d = new DateTime($rs['start_date']);
	  	$d->modify( 'next day' );//here check for holiday_option
	  	$date =  $d->format( 'Y-m-d' );
	  	$this->returnAfterHoliday($holiday_option,$date);
	  }else{
	  	echo $date;
	  	return $date;
	  }
  }
  public function getClientByMemberId($loan_id){
  	$sql="SELECT l.level,
		l.date_release,
		l.total_duration,
		l.first_payment,
		l.pay_term,
		l.payment_method,
		l.loan_type,
  		(SELECT branch_namekh FROM `ln_branch` WHERE br_id =l.branch_id LIMIT 1) AS branch_name,
  		(SELECT co_khname FROM `ln_co` WHERE co_id =l.co_id LIMIT 1) AS co_khname,
  		(SELECT co_firstname FROM `ln_co` WHERE co_id =l.co_id LIMIT 1) AS co_enname,
  		(SELECT displayby FROM `ln_co` WHERE co_id =l.co_id LIMIT 1) AS displayby,
  		(SELECT tel FROM `ln_co` WHERE co_id =l.co_id LIMIT 1) AS tel,
  		(SELECT client_number FROM `ln_client` WHERE client_id = l.customer_id LIMIT 1) AS client_number,
  		(SELECT name_kh FROM `ln_client` WHERE client_id = l.customer_id LIMIT 1) AS client_name_kh,
  		(SELECT name_en FROM `ln_client` WHERE client_id = l.customer_id LIMIT 1) AS client_name_en,
  		(SELECT phone FROM `ln_client` WHERE client_id = l.customer_id LIMIT 1) AS client_phone,
  		(SELECT displayby FROM `ln_client` WHERE client_id = l.customer_id LIMIT 1) AS displayclient,
  		l.customer_id AS client_id,
  		(SELECT curr_namekh FROM `ln_currency` WHERE id = l.currency_type LIMIT 1) AS currency_type
  		,l.loan_amount AS total_capital,l.loan_number,
  		l.interest_rate,l.branch_id,
        (SELECT CONCAT(last_name ,' ',first_name)  FROM `rms_users` WHERE id = l.user_id LIMIT 1) AS user_name
  		FROM 
  	   `ln_loan` AS l WHERE 1 ";
  	if(!empty($loan_id)){
  		$sql.=" AND l.id = $loan_id";
  	}
  	$db=$this->getAdapter();
  	return $db->fetchRow($sql);
  }
//   public function getClientGroupByMemberId($group_id){
//   	$sql="SELECT lg.level,lg.date_release,lg.total_duration,lg.first_payment,
//   	lg.pay_term,lg.payment_method,
//   	lg.loan_type,
//   	(SELECT branch_namekh FROM `ln_branch` WHERE br_id =lg.branch_id LIMIT 1) as branch_name,
//   	(SELECT co_khname FROM `ln_co` WHERE co_id =lg.co_id LIMIT 1) AS co_khname,
//   	(SELECT co_firstname FROM `ln_co` WHERE co_id =lg.co_id LIMIT 1) AS co_enname,
//   	(SELECT displayby FROM `ln_co` WHERE co_id =lg.co_id LIMIT 1) AS displayby,
//   	(SELECT tel FROM `ln_co` WHERE co_id =lg.co_id LIMIT 1) AS tel,
//   	(SELECT client_number FROM `ln_client` WHERE client_id = lm.client_id LIMIT 1) AS client_number,
//   	(SELECT phone FROM `ln_client` WHERE client_id = lm.client_id LIMIT 1) AS client_phone,
//   	(SELECT name_kh FROM `ln_client` WHERE client_id = lm.client_id LIMIT 1) AS client_name_kh,
//   	(SELECT name_en FROM `ln_client` WHERE client_id = lm.client_id LIMIT 1) AS client_name_en,
//   	(SELECT displayby FROM `ln_client` WHERE client_id = lm.client_id LIMIT 1) AS displayclient,
//   	lm.client_id,
//   	(SELECT curr_namekh FROM `ln_currency` WHERE id = lm.currency_type limit 1) AS currency_type
//   	,SUM(lm.total_capital) AS total_capital,lm.loan_number,
//   	lm.interest_rate,lm.branch_id,
//   	(SELECT CONCAT(last_name ,' ',first_name)  FROM `rms_users` WHERE id = lg.user_id LIMIT 1) AS user_name
//   	FROM
//   	`ln_loan_group` AS lg,`ln_loan_member` AS lm WHERE
//   	lg.g_id =lm.group_id  ";
//   	if(!empty($group_id)){
//   		$sql.=" AND lm.group_id = $group_id";
//   	}
//   	$sql.=" GROUP BY lm.group_id";
//   	$db=$this->getAdapter();
//   	return $db->fetchRow($sql);
//   }
  function getAllPaymentMethod($payment_id=null,$option = null){
  	$sql = "SELECT * FROM ln_payment_method WHERE status = 1 ";
  	if($payment_id!=null){
  		$sql.=" AND id = $payment_id";
  	}
  	$rows = $this->getAdapter()->fetchAll($sql);
  	if($option!=null){
  		$options="";
  		if(!empty($rows))foreach($rows AS $row){
  			$options[$row['id']]=($row['displayby']==1)?$row['payment_namekh']:$row['payment_nameen'];
  		}
  		return $options;
  	}else{
  		return $rows;
  	}
//   return $this->getAdapter()->fetchAll($sql);	
  	
  }
  public function getAllStaffPosition($id=null,$option = null){
  	$db = $this->getAdapter();
  	$sql=" SELECT id,position_en,position_kh,displayby 
  			FROM `ln_position` WHERE status =1 ";
  	if($id!=null){
  		$sql.=" AND id = $id LIMIT 1";
  	}
  	$rows = $db->fetchAll($sql);
  	if($option!=null){
  		$options=array(''=>"----ជ្រើសរើស----");
  		if(!empty($rows))foreach($rows AS $row){
  			$options[$row['id']]=($row['displayby']==1)?$row['position_kh']:$row['position_en'];
  		}
  		return $options;
  	}else{
  		return $rows;
  	}
  }
  
  public function getAllDepartment($id=null,$option = null){
  	$db = $this->getAdapter();
  	$sql=" SELECT id,department_kh,department_en,displayby
  	FROM `ln_department` WHERE status =1 ";
  	if($id!=null){
  		$sql.=" AND id = $id LIMIT 1";
  	}
  	$rows = $db->fetchAll($sql);
  	if($option!=null){
  		$options=array(''=>"----ជ្រើសរើស----",'-1'=>"Add New");
  		if(!empty($rows))foreach($rows AS $row){
  			$options[$row['id']]=($row['displayby']==1)?$row['department_kh']:$row['department_kh'];
  		}
  		return $options;
  	}else{
  		return $rows;
  	}
  }
  public  function getclientdtype(){
  	$db = $this->getAdapter();
  	$sql="SELECT id,key_code,CONCAT(name_kh,'-',name_en) AS name ,displayby FROM `ln_view` WHERE name_en!='' AND status =1 AND type=23";
  	$rows = $db->fetchAll($sql);
  	return $rows;
  }
  public function getVewOptoinTypeByType($type=null,$option = null,$limit =null,$first_option =null){
  	$db = $this->getAdapter();
  	$sql="SELECT id,key_code,CONCAT(name_kh,'-',name_en) AS name_en ,displayby FROM `ln_view` WHERE status =1 ";//just concate
  	if($type!=null){
  		$sql.=" AND type = $type ";
  	}
  	if($limit!=null){
  		$sql.=" LIMIT $limit ";
  	}
  	$rows = $db->fetchAll($sql);
  	if($option!=null){
  		$options=array();
  		if($first_option==null){//if don't want to get first select
  			$options=array(''=>"-----ជ្រើសរើស-----",-1=>"Add New",);
  		}
  		if(!empty($rows))foreach($rows AS $row){
  			$options[$row['key_code']]=$row['name_en'];//($row['displayby']==1)?$row['name_kh']:$row['name_en'];
  		}
  		return $options;
  	}else{
  		return $rows;
  	}
  }
  public function getVewOptoinTypeBys($option = null,$limit =null){
  	$db = $this->getAdapter();
  	$sql="SELECT id,title_en,title_kh,displayby,date,status FROM ln_callecteral_type WHERE status =1 ";
  	if($limit!=null){
  		$sql.=" LIMIT $limit ";
  	}
  	$rows = $db->fetchAll($sql);
  	if($option!=null){
  		$options=array(''=>"-----ជ្រើសរើស-----");
  		if(!empty($rows))foreach($rows AS $row){
  			$options[$row['id']]=($row['displayby']==1)?$row['title_kh']:$row['title_en'];
  		}
  		return $options;
  	}else{
  		return $rows;
  	}
  }
  public function getCollteralType($option = null,$limit =null){
  	$db = $this->getAdapter();
  	$sql="SELECT id,title_kh As name FROM `ln_callecteral_type` WHERE status =1 and title_kh!='' ";
  	if($limit!=null){
  		$sql.=" LIMIT $limit ";
  	}
  	$rows = $db->fetchAll($sql);
  	if($option!=null){
//   		$options=array(''=>"-----Select Callecteral Type-----",'-1'=>"Add New");
//   		if(!empty($rows))foreach($rows AS $row){
//   			$options[$row['id']]=($row['displayby']==1)?$row['title_kh']:$row['title_en'];
//   		}
//   		return $options;
  		return $rows;
  	}else{
  		return $rows;
  	}
  }
  
  
 public function setReportParam($arr_param,$file){
  	$contents = file_get_contents('.'.$file);
  	if($arr_param!=null){
  		foreach($arr_param as $key=>$read){
  			$contents=str_replace('@'.$key, $read, $contents);
  		}
  	}
  	$info=pathinfo($file);
  	$newfile=$info['dirname'].'/_'.$info['basename'];
  	file_put_contents('.'.$newfile, $contents);
  	return $newfile;
  }
  public function getHeadBudgetList($type,$start){
  	$heads=$this->getDibursementInYear($type, $start);
  	$str='<tr>';
  	foreach($heads as $value){
  		$str.='<td class="tdheader">'.$value.'</td>';
  	}
  	return $str.'</tr>';
  }
//   public function getContent($rows, $type){
//   	$str='';
//   	if($rows){
//   		$i=0;
//   		foreach($rows as $read){
//   			$i++;
//   			$str.='<tr><td class="no">'.$i.'</td>';
//   			$temp='';
//   			$c=0;
//   			foreach($read as $key=>$value){
//   				if($key!='id'){
//   					if ($type == 'payment'){
//   						if ($key == 'amount' || $key == 'amount_kh'){
//   							$str.='<td align="right">'.number_format($value,2).'</td>';
//   						}
//   						elseif ($key == "rate"){
//   							$str.='<td align="right">'.number_format($value).'</td>';
//   						}
//   						elseif ($key == "create_date"){
//   							$str.='<td align="center">'. date( "d, M Y", strtotime($value)) .'</td>';
//   						}
//   						elseif ($key == "years"){
//   							$str.='<td align="center">'. $value .'</td>';
//   						}
//   						else{
//   							$str.='<td>'.$value.'</td>';
//   						}
//   					}
//   					elseif(!($key=='title_english' || $key=='title_khmer')){
//   						$str.='<td>'.$this->checkValue($value).'</td>';
//   					}
//   					else{
//   						$c++;
//   						if($c==1)$temp=$value;
//   						elseif($c==2){
//   							$str.='<td>'.$temp.'<br/>'.$value.'<br/></td>'; $temp='';$c=0;
//   						}
//   					}
//   				}
//   			}
//   			$str.'</tr>';
  
//   		}
//   	}
//   	return $str;
//   }
//   public function checkValue($value)
//   {
//   	if($value=='' || $value==0) return '-';
//   	return $value;
  
//   }
  public function getSubDaysByPaymentTerm($pay_term,$amount_collect = null){
  	if($pay_term==3){
  		$amount_days =30;
  	}elseif($pay_term==2){
  		$amount_days =7;
  	}else{
  		$amount_days =1;
  	}
  	return $amount_days;//;*$amount_collect;//return all next day collect laon form customer
  }
 function preveiwDate($next_payment,$times){
 	if($times==2){
 		$next_payment = date("Y-m-d", strtotime("$next_payment -1 month"));
 		return $next_payment;
 	}else{
 		return $next_payment;
 	}
 }
 public function getNextPayment($str_next,$next_payment,$amount_amount,$holiday_status=null,$first_payment=null){//code make slow
 $default_day = Date("d",strtotime($first_payment));
 $prev_month=$next_payment;
 for($i=0;$i<$amount_amount;$i++){
		if($default_day>28){
		   if($str_next!='+1 month'){
		   	$next_payment = date("Y-m-d", strtotime("$next_payment $str_next"));
				$default_day='d';
				if($str_next=='+1 day'){//if day
				}else{
					$next_payment = date("Y-m-$default_day", strtotime("$next_payment $str_next"));//code here have problem
				}
			}else{
				
// 				$next_payment = date("Y-m-$default_day", strtotime("$next_payment $str_next"));
// 				$str_next='-1 month';
// 				$next_payment = date("Y-m-d", strtotime("$next_payment $str_next"));
/*update*/
				if($default_day==31){
					// 		 		$next_payment = date("Y-m-t", strtotime("$next_payment +1 day"));//if many hear not work
					$date= new DateTime($next_payment);
					$date->modify('+1 day');
					$next_payment = $date->format("Y-m-t");
					return $next_payment;
				}elseif($default_day==30 OR $default_day==29){
					$date= new DateTime($prev_month);
					$pre_month = $date->format("m");
					$prev_month = $pre_month;
					if($pre_month=='01'){
						$date= new DateTime($next_payment);
						$next_payment = $date->format("Y-02-20");
						$date= new DateTime($next_payment);
						$next_payment = $date->format("Y-m-t");
					}//for Feb
					else{
						$date= new DateTime($next_payment);
						$date->modify('+1 month');
						$next_payment = $date->format("Y-m-$default_day");
					}
				}else{//for 29
						 
				}
/*update new*/	
				return $next_payment;
				break;			
			}
		}else{
			if($str_next!='+1 month'){
				$default_day='d';
			}
	  		$next_payment = date("Y-m-$default_day", strtotime("$next_payment $str_next"));
		}
  	}
  	if($holiday_status==3){//normal
  		if($str_next=='+1 day'){
  			while($next_payment!=$this->checkHolidayExist($next_payment,$holiday_status)){
  				$next_payment = $this->checkHolidayExist($next_payment,$holiday_status);
  			}
  		}
  		return $next_payment;//if normal day
  	}else{//check for sat and sunday
  		if($default_day<=28){
  		while($next_payment!=$this->checkHolidayExist($next_payment,$holiday_status)){
  			$next_payment = $this->checkHolidayExist($next_payment,$holiday_status);
  		}
  	}
//   		echo $next_payment;exit();
  		return $next_payment;
  	}
  	
  }
  function checkDefaultDate($str_next,$next_payment,$amount_amount,$holiday_status=null,$first_payment=null){
  	$default_day = Date("d",strtotime($first_payment));
  	for($i=0;$i<$amount_amount;$i++){
  		if($default_day>28){
  			$next_payment = date("Y-m-d", strtotime("$next_payment $str_next"));
  			if($str_next!='+1 month'){
  				$default_day='d';
  				$next_payment = date("Y-m-$default_day", strtotime("$next_payment $str_next"));
  			}else{
  				$next_payment = date('Y-m-d', strtotime("$next_payment $str_next"));
  				$str_next='-1 month';
  				$next_payment = date('Y-m-d', strtotime("$next_payment $str_next"));
  			}
  		}else{
  			if($str_next!='+1 month'){
  				$default_day='d';
  			}
  			$next_payment = date("Y-m-$default_day", strtotime("$next_payment $str_next"));
  		}
  	}
  		return $next_payment;
  }
	  
  function checkFirstHoliday($next_payment,$holiday_status){
//   	print_r($this->checkHolidayExist($next_payment,$holiday_status));
  	if($holiday_status==3){
  		return $next_payment;//if normal day
  	}else{
  		while($next_payment!=$this->checkHolidayExist($next_payment,$holiday_status)){
  			$next_payment = $this->checkHolidayExist($next_payment,$holiday_status);
  		}
  		return $next_payment;
  	}
  	
  	
//   	$str_option = 'next day';
//   	$d->modify($str_option);
//   	$date_next =  $d->format( 'Y-m-d' );
  	
  }
  function checkEndOfMonth($default_day,$next_payment,$str_next){//default = 31 ,
  	if($str_next=='+1 month'){
  		$str_next='-1 month';
  	}else if($str_next=='+1 week'){
  		$str_next='-1 week';
  	}else{
  		$str_next='-1 day';
  	}
  	
  	$next_payment = date("Y-m-d", strtotime("$next_payment $str_next"));
  	$m = (integer) date('m',strtotime($next_payment));
  	$end_date   = date('Y-m-d',mktime(1,1,1,++$m,0,date('Y',strtotime($next_payment))));
  	return $end_date;
  	
  }
  public function getAmountDayByTerm($pay_term){
  	if($pay_term==3){
  		$amount_day = 30;
  	}elseif($pay_term==2){
  		$amount_day = 7;
  	}else{
  		$amount_day = 1;
  	}
  	return $amount_day;
  }
  public function getNextDateById($pay_term,$amount_next_day){
  	if($pay_term==3){
  		$str_next = '+1 month';
  	}elseif($pay_term==2){
  		$str_next = '+1 week';
  	}else{
  		$str_next = '+1 day';
  	}
  	return $str_next;
  }
  public function checkHolidayExist($date_next,$holiday_option){//for check collect payment in holiday or not
  	$db = $this->getAdapter();
  	$sql="SELECT start_date FROM `ln_holiday` WHERE start_date='".$date_next."'";
  	$rs =  $db->fetchRow($sql);
  	$db = new Setting_Model_DbTable_DbLabel();
  	$array = $db->getAllSystemSetting();
  	if($rs){
  		$d = new DateTime($rs['start_date']);
  		if($holiday_option==1){
  			$str_option = 'previous day';
  		}elseif($holiday_option==2){
  			$str_option = 'next day';
  		}else{
  			return  $d->format( 'Y-m-d' );
  		}
  		$d->modify($str_option); //here check for holiday option //can next day,next week,next month
  		$date_next =  $d->format( 'Y-m-d');
  		
  		
  		$d = new DateTime($date_next);
  		$day_work = date("D",strtotime($date_next));
  		if($day_work=='Sat' OR $day_work=='Sun' ){//if 
  			if(($day_work=='Sat' AND $array['work_saturday']==1) OR ($day_work=='Sun' AND $array['work_sunday']==1)){//sat working
  				return $date_next;
  			}else if($day_work=='Sat' AND $array['work_saturday']==0){//sat not working
  				if($holiday_option==1){//after 
  					$str_next = '+2 day';//why
  				}else if($holiday_option==2){
  					$str_next = '+1 day';
  				
  				}else{//before
  					$str_next = '-1 day';//thu
  				}
  				$d->modify($str_next); //here check for holiday option //can next day,next week,next month
  				$date_next =  $d->format( 'Y-m-d' );
  				return $date_next;
  			}else{//sun not working continue to monday // but not check if mon day not working
  				if($holiday_option==2){//after
  					$str_next = '+1 day';
  				}else{//before
  					$str_next = '-1 day';//thu
  				}
  				$d->modify($str_next); //here check for holiday option //can next day,next week,next month
  				$date_next =  $d->format( 'Y-m-d' );
  				return $date_next;
  			}
  		}else{
  			return $date_next;
  		}
  	}
  	else{
  		$d = new DateTime($date_next);
  		$day_work = date("D",strtotime($date_next));
  	    if($day_work=='Sat' OR $day_work=='Sun' ){
  	    	if(($day_work=='Sat' AND $array['work_saturday']==1) OR ($day_work=='Sun' AND $array['work_sunday']==1)){//sat working
  	    		return $date_next;
  	    	}else if($day_work=='Sat' AND $array['work_saturday']==0){//sat not working
  	    		$str_next = '+2 day';
  	    		$d->modify($str_next); //here check for holiday option //can next day,next week,next month
  	    		$date_next =  $d->format( 'Y-m-d' );
  	    		return $date_next;
  	    	}else{//sun not working continue to monday // but not check if mon day not working
  	    		$str_next = '+1 day';
  	    		$d->modify($str_next); //here check for holiday option //can next day,next week,next month
  	    		$date_next =  $d->format( 'Y-m-d' );
  	    		return $date_next;
  	    	}
  	    }else{
  	    	return $date_next;
  	    }
  	}
  }
  public function CountDayByDate($start,$end){
  	//$db = new Application_Model_DbTable_DbGlobal();
	$date = $this->countDaysByDate($start,$end);
  	return $date;
  }
  public function CurruncyTypeOption(){
  	$db = $this->getAdapter();
  	$rows=array(2=>"ដុល្លា",3=>"បាត",1=>"រៀល");
  	$option='';
  	if(!empty($rows))foreach($rows as $key=>$value){
  		$option .= '<option value="'.$key.'" >'.htmlspecialchars($value, ENT_QUOTES).'</option>';
  	}
  	return $option;
  }
  public function getSystemSetting($keycode){
  	$db = $this->getAdapter();
  	$sql = "SELECT * FROM `ln_system_setting` WHERE keycode ='".$keycode."'";
//   	echo $sql;
  	return $db->fetchRow($sql);
  }
  static function getPaymentTermById($id=null){
  	$arr = array(
  			1=>"ថ្ងៃ",
  			2=>"អាទិត្យ",
  			3=>"ខែ");
  	if($id!=null){
		return $arr[$id];
  	}
  	return $arr;
  	
  }
  public function getAccountBranchByOther($acc_id, $br_id ,$curr_id,$balance=null,$increase=null){
		$sql =" SELECT * FROM ln_account_branch 
		WHERE  account_id = $acc_id AND branch_id=$br_id AND currency_type = $curr_id LIMIT 1";
  	$db = $this->getAdapter();
  	$row =  $db->fetchRow($sql);
  	$increase = ($increase==1)?'+':'-'; 
	$table='ln_account_branch';
  	if(empty($row)){
  		$arr =array(
  				'account_id'=>$acc_id,
				'branch_id'=>$br_id,
  				'currency_type'=>$curr_id,
				'balance'=>$increase.$balance,
				'user_id'=>self::getUserId(),
				'date'=>date('Y-m-d'),
  				);
		$db->insert($table, $arr);
  		return $arr;
  	}else{

 		$where ='id = '.$row['id'] ;
  		$data = array(
  				'balance'=>($increase.$balance)+$row['balance']
  				);
  		$db->update($table,$data,$where);
  	}
  }
  public function getGroupCodeById($diplayby=1,$group_type,$opt=null){
  	$db = $this->getAdapter();
  	$sql = " CALL `stGetAllClientType`($group_type)";
  	$result = $db->fetchAll($sql);
  	$options=array(''=>"------Select Client Code-Name------");
  	if($opt!=null){
		if(!empty($result))foreach($result AS $row){
			if($group_type==1){
				$label = ($diplayby==1)?$row['group_code']:$row['client_number'].','.$row['name_kh'];//.','.$row['province_en_name'].','.$row['district_name'].','.$row['commune_name'].','.$row['village_name'];	
			}else{
				$label = ($diplayby==1)?$row['client_number']:$row['name_kh'];//.','.$row['province_en_name'].','.$row['district_name'].','.$row['commune_name'].','.$row['village_name'];	
			}
  			$options[$row['client_id']]=$label;
		}  
  		return $options;	
  	}else{
  		return $result;
  	}
  }
  public function getLoanFundExist($loan_id){
  	$sql = "CALL `stgetLoanFundExist`($loan_id) ";
  	$db = $this->getAdapter();
  	$result = $db->fetchRow($sql);
  	if(!empty($result)){
  		return true;}
  	else{ 
  		return false;}
  }
  
  function getAllClientGroup($branch_id=null){
  	$db = $this->getAdapter();
  	$sql = " SELECT c.`client_id` AS id  ,c.`branch_id`,
  	CONCAT(c.client_number ,'-',c.`name_en`,'-',c.`name_kh`) AS name , client_number
  	FROM `ln_client` AS c WHERE c.`name_en`!='' AND c.status=1 AND c.is_group=1 " ;
  	if($branch_id!=null){
  		$sql.=" AND c.`branch_id`= $branch_id ";
  
  	}
  	$sql.=" ORDER BY id DESC";
  	return $db->fetchAll($sql);
  }
  function getAllClientGroupCode($branch_id=null){
  	$db = $this->getAdapter();
  	$sql = " SELECT c.`client_id` AS id  ,c.`branch_id`,
  	group_code AS name
  	FROM `ln_client` AS c WHERE c.`name_en`!='' AND c.status=1 AND c.is_group=1 " ;
  	if($branch_id!=null){
  		$sql.=" AND c.`branch_id`= $branch_id ";
  
  	}
  	$sql.=" ORDER BY id DESC";
  	return $db->fetchAll($sql);
  }
  function getAllClientNumber($branch_id=null){
  	$db = $this->getAdapter();
  	$sql = " SELECT c.`client_id` AS id  ,c.client_number AS name, c.`branch_id`
  	FROM `ln_client` AS c WHERE (c.`name_en`!='' OR c.name_kh!='') AND c.status=1  " ;
  	if($branch_id!=null){
  		$sql.=" AND c.`branch_id`= $branch_id ";
  	}
  	$sql.="  order BY c.`client_id` DESC ";
  	return $db->fetchAll($sql);
  }
  function getAllClient($branch_id=null){
  	$db = $this->getAdapter();
  	//   	$sql = " SELECT c.`client_id` AS id  ,c.`branch_id`,
  	// 	CONCAT(c.`name_en` ,',',(SELECT village_name FROM `ln_village` WHERE vill_id = village_id  LIMIT 1) ,',',
  	// 	(SELECT commune_name FROM `ln_commune` WHERE c.com_id = com_id  LIMIT 1) ,',',
  	// 	(SELECT district_name FROM `ln_district` AS ds WHERE c.dis_id = ds.dis_id  LIMIT 1) ,',',
  	// 	(SELECT province_en_name FROM `ln_province` WHERE province_id= c.pro_id  LIMIT 1) ) AS name
  	//   	FROM `ln_client` AS c WHERE c.`name_en`!='' AND c.status=1  " ;
  	$sql = " SELECT c.`client_id` AS id  ,c.`branch_id`,
  	c.`name_kh` AS name , client_number
  	FROM `ln_client` AS c WHERE (c.`name_en`!='' OR c.name_kh!='') AND c.status=1  " ;
  	if($branch_id!=null){
  		$sql.=" AND c.`branch_id`= $branch_id ";
  
  	}
//   	$sql.=" ORDER BY id DESC";
  	$sql.="  order BY c.`client_id` DESC ";
  	return $db->fetchAll($sql);
  }
  function getClientIdBYMemberId($member_id){
  	$db = $this->getAdapter();
		$sql = " SELECT l.co_id,l.customer_id AS client_id  
			FROM  `ln_loan` AS l 
          WHERE l.status=1 AND l.id = $member_id GROUP BY l.id ";
  	return $db->fetchRow($sql);
  }

  function getAllLoanNumber($type){//type ==1 is ilPayment, type==2 is group payment
  	$db = $this->getAdapter();
  	$sql ="SELECT 
  		l.`loan_number` 
  			FROM `ln_loan` AS l 
  		WHERE 
  		l.`is_completed` = 0 
  		AND l.status=1 
  		AND l.is_badloan=0 ";
  	if($type==1){
  		$sql.=" AND l.`loan_type` = 1 ";
  	}else{
  		$sql.=" AND l.`loan_type` =2 ";
  	}
  	$sql.=" ORDER BY loan_number ASC ";
  
  	return $db->fetchAll($sql);
  }
  
  function getAllViewType($opt=null){
  		$db = $this->getAdapter();
  	$sql ="SELECT * FROM `ln_view_type`";
  	
  	$result = $db->fetchAll($sql);
  	$options=array('-1'=>"------Select View Type------");
  	if($opt!=null){
  		if(!empty($result))foreach($result AS $row){
  			    $options[$row['id']]=$row['name'];
  		}
  		return $options;
  	}else{
  		return $result;
  	}
  	
  }
  public function getLoanAllLoanNumber($diplayby=1,$opt=null){
  	$db = $this->getAdapter();
  	$sql = "CALL `stGetAllLoanNumber`";
  	$result = $db->fetchAll($sql);
  	//print_r($result);exit();
  	$options=array(''=>"---Select Loan Number---");
  	if($opt!=null){
  		if(!empty($result))foreach($result AS $row){
  			$options[$row['id']]= $row['customer_name']."-".$row['loan_number'];
  		}
  		return $options;
  	}else{
  		return $result;
  	}
  }
  public function ClassifiedLoan($type=26){
  	$db = $this->getAdapter();
  	$sql="SELECT id,key_code,name_en FROM `ln_view` WHERE status =1 ";//just concate
  	if($type!=null){
  		$sql.=" AND type = $type ";
  	}
  	 $rows = $db->fetchAll($sql);
  	$opt = array();
  	if(!empty($rows))foreach($rows AS $row){
  		$opt[$row['key_code']]=$row['name_en'];
  	}
  	return $opt;
//   	if($option!=null){
//   		$options=array();
//   		if($first_option==null){//if don't want to get first select
//   			$options=array(''=>"-----ជ្រើសរើស-----",-1=>"Add New",);
//   		}
//   		if(!empty($rows))foreach($rows AS $row){
//   			$options[$row['key_code']]=$row['name_en'];//($row['displayby']==1)?$row['name_kh']:$row['name_en'];
//   		}
//   		return $options;
//   	}else{
//   		return $rows;
//   	}
  }
  
  public function getJobName(){
  	$db = $this->getAdapter();
  	$sql= "SELECT DISTINCT `job` FROM `ln_client` WHERE job!='' ";
  	return $db->fetchAll($sql);
  }
  public function getAllInterest($option=null){
  	$this->_name='ln_zone';
  	$sql = " SELECT id,value,label FROM `in_ln_interest` WHERE value > 0 ";
  	$db = $this->getAdapter();
  	$rows =  $db->fetchAll($sql);
  	if($option!=null){
  		if(!empty($rows))foreach($rows as $rs){
  			$options[$rs['value']]=$rs['label'];
  		}
  		return $options;
  	}
  	return $rows;
  }
  function getIndividuleClient(){
  	$db = $this->getAdapter();
  	$sql = "SELECT c.`client_id` AS id ,c.`name_kh` AS name ,c.`branch_id`,c.`client_number` FROM `ln_client` AS c WHERE c.`is_group`=0  AND c.`name_en`!='' " ;
  	return $db->fetchAll($sql);
  }
//   function getAllClient(){
//   	$db = $this->getAdapter();
//   	$sql = "SELECT c.`client_id` AS id ,c.`name_kh` AS name ,c.`branch_id`,c.`client_number` FROM `ln_client` AS c WHERE c.`is_group`=1  AND c.`name_en`!='' " ;
//   	return $db->fetchAll($sql);
//   }
  public function getAllProduct(){
  	$sql=" SELECT v.id,v.product_en,v.product_kh,
  	v.status FROM ln_pawnshopproduct AS v WHERE v.status=1 ";
  	$Other=" ORDER BY  v.id desc ";
  	$db = $this->getAdapter();
  	return $db->fetchAll($sql);
  }
  public function getPawnshoNumber($data=array('branch_id'=>1,'is_group'=>0)){
  	$db = $this->getAdapter();
  	$sql=" SELECT COUNT(id)  FROM ln_pawnshop WHERE branch_id=".$data['branch_id']." LIMIT 1 ";
  	$pre = $this->getPrefixCode($data['branch_id'])."PS";
  	$acc_no = $db->fetchOne($sql);
  	$new_acc_no= (int)$acc_no+1;
  	$acc_no= strlen((int)$acc_no+1);
  
  	for($i = $acc_no;$i<5;$i++){
  		$pre.='0';
  	}
  	return $pre.$new_acc_no;
  }
  
}
?>