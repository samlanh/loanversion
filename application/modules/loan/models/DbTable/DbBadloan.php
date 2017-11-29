<?php

class Loan_Model_DbTable_DbBadloan extends Zend_Db_Table_Abstract
{
    protected $_name = 'ln_badloan';
    function addbadloan($_data){
    	$session_transfer=new Zend_Session_Namespace();
    	$session_user=new Zend_Session_Namespace('authloan');
    	$user_id = $session_user->user_id;
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	
//     	print_r($_data);exit();
    	
    	try{
    		if($_data['Term']<90){
    			$writ_off = 0;
    		}elseif($_data['Term']>=90) {
    			$writ_off = 1;
    		}
    		
	    	$arr = array(
	    			'branch'=>$_data['branch'],
	    			'client_code'=>$_data['client_codeadd'],
	    			'client_name'=>$_data['client_nameadd'],
	    			'loan_id'=>$_data['loannumber'],
	    			'intrest_amount'=>$_data['Interest_amount'],
	    			'date'=>$_data['Date'],
	    			'loss_date'=>$_data['date_loss'],
	    			'cash_type'=>$_data['cash_type'],
	    			'total_amount'=>$_data['Total_amount'],
	    			'tem'=>$_data['Term'],
	    			'note'=>$_data['Note'],
	    			'status'=>$_data['status'],
	    			'create_by'=>$user_id,
	    			'is_writoff'=>$writ_off    			
	    			);
	    	$this->insert($arr);//insert data
	    	//echo 1;exit();
	    	
	    	$this->_name = 'ln_loan'; 
	    	$arr_loan = array(
	    		'is_badloan' =>1,
	    	);
	    	$where=" customer_id = ".$_data['client_codeadd'];
			$this->update($arr_loan, $where);
			
			$this->_name='ln_income_expense';
				$data = array(
						'branch_id'=>$_data['branch'],
						'account_id'=>'កម្ចីខូច',
						'total_amount'=>$_data['Total_amount'],
	// 					'Date'=>$data['for_date'],
						'invoice'=>'',
						'curr_type'=>$_data['cash_type'],
						'tran_type'=>1,
						'disc'=>$_data['Note'],
						'date'=>$_data['Date'],
						'status'=>$_data['status'],
						'user_id'=>$user_id
				);
				$this->insert($data);
			
			$db->commit();
		}catch (Exception $e){
			$db->rollBack();
			echo $e->getMessage();exit();
		}
    }
    function updatebadloan($_data){
    	$session_transfer=new Zend_Session_Namespace();
    	$session_user=new Zend_Session_Namespace('authloan');
    	$user_id = $session_user->user_id;
    	if($_data['Term']<90){
    		$writ_off = 0;
    	}elseif($_data['Term']>=90) {
    		$writ_off = 1;
    	}
    	//print_r($_data);exit();
    	$arr = array(
    			'branch'=>$_data['branch'],
    			'client_code'=>$_data['client_code'],
    			'client_name'=>$_data['client_name'],
    			'date'=>$_data['Date'],
    			'loan_number'=>$_data['loannumber'],
    			'loss_date'=>$_data['date_loss'],
    			'cash_type'=>$_data['cash_type'],
    			'total_amount'=>$_data['Total_amount'],
//     			'intrest_amount'=>$_data['Interest_amount'],
    			'tem'=>$_data['Term'],
    			'note'=>$_data['Note'],
    			'status'=>$_data['status'],
    			'create_by'=>$user_id,
    			'is_writoff'=>$writ_off
    			);
    	$where=" id = ".$_data['id'];    	
    	$this->update($arr, $where);
    }
    function updatebadloan_bad($_data){
    	$session_transfer=new Zend_Session_Namespace();
    	$session_user=new Zend_Session_Namespace('authloan');
    	$user_id = $session_user->user_id;
    	if($_data['Term']<90){
    		$writ_off = 0;
    	}elseif($_data['Term']>=90) {
    		$writ_off = 1;
    	}
    	//print_r($_data);exit();
    	$arr = array(
    			'branch'=>$_data['branch'],
    			'client_code'=>$_data['client_code'],
    			'client_name'=>$_data['client_name'],
//     			'number_code'=>$_data['number_code'],
//     			'intrest_amount'=>$_data['Interest_amount'],
    			'date'=>$_data['Date'],
    			'loss_date'=>$_data['date_loss'],
    			'cash_type'=>$_data['cash_type'],
    			'total_amount'=>$_data['Total_amount'],
    			'tem'=>$_data['Term'],
    			'note'=>$_data['Note'],
    			'status'=>$_data['status'],
    			'create_by'=>$user_id,
    			'is_writoff'=>$writ_off
    	);
    	$where=" id = ".$_data['id'];
    	$this->update($arr, $where);
    	 
    	$this->_name = 'ln_loan_group';    	
    	$arr_loan_groups = array('is_badloan' =>0,);
    	$wheres=" group_id = ".$_data['idclient'];
    	$this->update($arr_loan_groups, $wheres);
    	
    	$this->_name = 'ln_loan_group';
    	$arr_loan_group = array('is_badloan' =>1,);
    	$where=" group_id = ".$_data['client_code'];
    	$this->update($arr_loan_group, $where);
    }
    function getbadloanbyid($id){
    	$db = $this->getAdapter();
    	$sql="SELECT id,branch,client_code,client_name,loan_number AS number_code,date,loss_date,cash_type,total_amount,intrest_amount
    	,tem,note,status FROM  $this->_name where id=$id AND status = 1";
    	return $db->fetchRow($sql);
    }
    function getAllBadloan($search=null){
    	$db = $this->getAdapter();
    	
    	$sql = "SELECT l.id,b.branch_namekh,
    	CONCAT((SELECT client_number FROM `ln_client` WHERE client_id = l.client_code LIMIT 1),' - ',		
    	(SELECT name_kh FROM `ln_client` WHERE client_id = l.client_code LIMIT 1)) AS client_name_en,
  		l.loss_date, 
		CONCAT (total_amount,' ',(SELECT symbol FROM `ln_currency` WHERE status = 1 AND id = l.`cash_type`))AS total_amount ,
		CONCAT (intrest_amount,' ',(SELECT symbol FROM `ln_currency` WHERE status = 1 AND id = l.`cash_type`))AS intrest_amount ,
		CONCAT (l.tem,' Days')as tem,l.note,l.date,l.status FROM `ln_badloan` AS l,ln_branch AS b 
		WHERE b.br_id = l.branch  ";    	
    	$where='';
    if(!empty($search['branch'])){
    		$where.=" AND b.br_id = ".$search['branch'];
    	}
    	if(!empty($search['client_name'])){
    		$where.=" AND l.client_code = ".$search['client_name'];
    	}
    	if(!empty($search['client_code'])){
    		$where.=" AND l.client_code = ".$search['client_code'];
    	}
    	if(!empty($search['Term'])){
    		$where.=" AND l.tem = ".$search['Term'];
    	}
    	if(!empty($search['cash_type'])){
    		$where.=" AND l.`cash_type` = ".$search['cash_type'];
    	}
    	if($search['status']!=""){
    		$where.= " AND l.status = ".$search['status'];
    	}    	
    	if(!empty($search['adv_search'])){
    		$s_where=array();
    		$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
    		$s_where[]="REPLACE(l.note,' ','')  		LIKE '%{$s_search}%'";
    		$s_where[]="REPLACE(l.total_amount,' ','')  LIKE '%{$s_search}%'";
    		$s_where[]="REPLACE(l.intrest_amount,' ','')LIKE '%{$s_search}%'";
    		$s_where[]=" l.tem = '{$s_search}' ";
    		$where .=' AND ('.implode(' OR ',$s_where).' )';
    	}
    	$order = ' ORDER BY l.id DESC ';
//     	echo $sql.$where;
    	return $db->fetchAll($sql.$where.$order);
    }
    public function getClientByTypes($type){
    	$this->_name='ln_loan';
    	$sql ="SELECT c.client_number,
    			c.name_kh,c.name_en,
    			l.customer_id AS client_id ,l.loan_number
				FROM `ln_loan` AS l,
				ln_client AS c 
				WHERE 
				l.customer_id=c.client_id  
				AND l.is_completed = 0 
				AND l.status=1 AND 
    			c.client_number !='' ORDER BY l.id DESC ";
    	$db = $this->getAdapter();
    	$rows = $db->fetchAll($sql);
    	$options=array(0=>'------Select------');
    	if(!empty($rows))foreach($rows AS $row){
    		 if($type==1){
    			$lable = $row['client_number'];
    		}elseif($type==2){ 
    			$lable = $row['name_kh'];}
    		else{$lable = $row['loan_number'];}
    		$options[$row['client_id']]=$lable;
    	}
		return $options;
    }
    public function getClientByTypesADD($type){
    	$this->_name='ln_loan_member';
    	$sql ="SELECT c.client_number,c.name_kh,l.customer_id ,l.loan_number,l.`is_badloan`
				FROM ln_loan AS l,ln_client AS c
				WHERE l.customer_id = c.client_id  AND l.is_completed = 0 AND l.status=1 AND c.client_number !='' 
				AND l.`is_badloan` = 0 ORDER BY l.customer_id DESC";
    	$db = $this->getAdapter();
    	$rows = $db->fetchAll($sql);
    	$options=array(0=>'------Select------');
    	if(!empty($rows))foreach($rows AS $row){
    		if($type==1){
    			$lable = $row['loan_number'];
    		}elseif($type==2){ $lable = $row['name_kh'];}
    		else{$lable = $row['loan_number'];}
    		$options[$row['customer_id']]=$lable;
    	}
    	return $options;
    }
    public function getClientByTypess($type=null,$client_id=null ,$row=null){
    	$this->_name='ln_loan_member';
    	$where='';
    	if($type!=null){
    		$where=' AND is_group = 1';
    	}
    	$sql ="SELECT client_id,
       (SELECT lf.total_principal FROM `ln_loanmember_funddetail` AS lf WHERE lf. member_id= member_id AND STATUS=1 AND is_completed=0 LIMIT 1) AS total_principal,
       (SELECT lf.total_interest FROM `ln_loanmember_funddetail` AS lf WHERE lf. member_id= member_id AND STATUS=1 AND is_completed=0 LIMIT 1) AS total_interest
        FROM `ln_loan_member`";
    	$db = $this->getAdapter();
    	if($row!=null){
    		if($client_id!=null){
    			$where.=" AND client_id  =".$client_id ." LIMIT 1";
    		}
    		return $db->fetchRow($sql.$where);
    	}
    	return $db->fetchAll($sql.$where);
    }
    public function getLoanInfo($id){
    	$db=$this->getAdapter();
    	$sql="SELECT (SELECT SUM(ld.principal_permonth) FROM `ln_loan_detail` AS ld WHERE ld.loan_id = l.id AND ld.status=1 AND ld.is_completed=0 LIMIT 1)  AS total_principal,
    	              (SELECT SUM(ld.total_interest) FROM `ln_loan_detail` AS ld WHERE ld.loan_id= l.id AND ld.status=1 AND ld.is_completed=0 LIMIT 1)  AS total_interest ,
    	              (SELECT ld.date_payment FROM `ln_loan_detail` AS ld WHERE ld.loan_id= l.id AND ld.status=1 AND ld.is_completed=0 LIMIT 1)  AS date_payment,
                      l.level,l.date_release,l.date_line,l.total_duration,l.pay_term,
                      SUM(l.loan_amount) AS total_capital,			  
    				  l.loan_number,l.currency_type,l.interest_rate 
    		FROM `ln_loan` AS l 
    		WHERE l.customer_id=$id AND l.status=1 AND l.is_completed=0 
    	   LIMIT 1";
    	return $db->fetchRow($sql);
    }
    public function getLoanedit($id){
    	$db=$this->getAdapter();
    	$sql="SELECT  * FROM ln_badloan WHERE id=$id AND STATUS=1";
    	return $db->fetchRow($sql);
    }
  }

