<?php

class Pawnshop_Model_DbTable_DbPayment extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_pawn_receipt_money';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    	 
    }
    function addPawnshopPayment($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$amount_receive = $data["amount_receive"];
    		$total_payment = $data["total_payment"];
    		if($amount_receive>$total_payment){
    			$paidamount = $amount_receive - $data['amount_return'];
    			$is_compleated = 1;
    		}elseif($amount_receive<$total_payment){
    			$paidamount = $amount_receive;
    			$is_compleated = 0;
    		}else{
    			$paidamount = $total_payment;
    			$is_compleated = 1;
    		}
    		//==============
    		$payoff=0;
    		$principle = $data["os_amount"];
    		$penelize  = $data["penalize_amount"];
    		$more_interest = $data["more_interest"];
    		$interest = $data["total_interest"];
    		$total_pay = $data["total_payment"];
    		$recieve = $data["amount_receive"]-$data["amount_return"];
    		 
    		$new_service = $recieve-$more_interest;
    		if($new_service>=0){
    			$service = $more_interest;
    			$new_penelize = $new_service - $penelize;
    			if($new_penelize>=0){
    				$penelize_amount =  $penelize;
    				$new_interest = $new_penelize - $interest;
    				if($new_interest>=0){
    					$interest_amount = $interest;
    					$new_printciple = $new_interest - $principle;
    					if($new_printciple>=0){
    						$principle_amount = $principle;
    						$payoff=1;
    					}else{
    						$principle_amount = abs($new_interest);
    					}
    				}else{
    					$interest_amount = abs($new_penelize);
    					$principle_amount=0;
    				}
    			}else{
    				$penelize_amount = abs($new_service);
    				$interest =0;
    				$principle_amount=0;
    			}
    		}else{
    			$service = abs($recieve);
    			$penelize_amount = 0;
    			$interest =0;
    			$principle_amount=0;
    		}
    		
    		$datagroup = array(
    				'branch_id'=>$data['branch_id'],
    				'pawn_id'=>$data['loan_number'],
    				'client_id'=>$data['client_code'],
    				'receipt_no'=>$data['reciept_no'],
    				'date_pay'=>$data['collect_date'],
    				'date_input'=>date("Y-m-d"),
    				'date_payment'=>$data['end_date'],
    				'principal_amount'=>$data['os_amount'],
    				'interest_amount'=>$data['total_interest'],
    				'moreinterest_amount'=>$data['more_interest'],
    				'total_amount'=>$data['total_payment'],//trov bong sak rob
    				'recieve_amount'=>$data['amount_receive'],
    				'penalize_amount'=>$data['penalize_amount'],
    				'principal_amountpaid'=>$principle_amount,
    				'interest_amountpaid'=>$interest_amount,
    				'more_interestpaid'=>$service,
    				'penalize_amount'=>$penelize_amount,
    				'total_paid'=>$paidamount,
    				'is_payoff'=>$payoff,
    				'is_completed'=>$is_compleated,
    				'return_amount'=>$data['amount_return'],
    				'note'=>$data['note'],
    				'payment_option'=>$data['option_pay'],
    				'currency_type'=>$data['currency_type'],
    				'user_id'=>$this->getUserId(),
    				);
    		$g_id = $this->insert($datagroup);//add group loan
    		
    	
    		$this->_name="ln_pawnshop";
    		$where = " id = ".$data['loan_number'];
    		$arr = array(
    				"is_completed"=>1
    		);
    		$this->update($arr, $where);
    		if($data['option_pay']==1){//pay off    			
    		}else{//
    			$this->_name="ln_pawnshop";
    			$day_amount = 1;
    			if($data['payment_term']==3){//month
    				$day_amount=30;
    			}elseif($data['payment_term']==2){
    				$day_amount=7;
    			}
    			$total_interest = $data['total_amount_loan']*($day_amount*$data['loan_period'])*($data['interest_rate']/$day_amount/100);
    			$arr = array(
    					'branch_id'=>$data['branch_id'],
    					'saving_number'=>$data['pawn_number'],
    					'parent_id'=>$data['loan_number'],
    					'level'=>$data['load_level'],
    					'client_id'=>$data['client_id'],
    					'release_amount'=>$data['total_amount_loan'],
    					'period'=>$data['loan_period'],
    					'currency_type'=>$data['currency_type'],
    					'term_type'=>$data['payment_term'],
    					'interest_rate'=>$data['interest_rate'],
    					'saving_date'=>$data['release_date'],
    					'saving_close'=>$data['end_date'],
//     					'reciept_no'=>$data['receipt_num'],
    					'product_id'=>$data['product_id'],
    					'is_renew'=>1,
    					'product_description'=>$data['description'],
    					'total_interest'=>$total_interest,
    			);
    			$record_id = $this->insert($arr);//add member loan
    			
    		}
    		$db->commit();
    		return 1;
    	}catch (Exception $e){
    		$db->rollBack();
    		$err = $e->getMessage(); 
    		Application_Model_DbTable_DbUserLog::writeMessageError($err);
    	}
    
    }
    public function getAllPawnPayment($search){
		$start_date = $search['start_date'];
    	$end_date = $search['end_date'];
    	
    	$from_date =(empty($search['start_date']))? '1': " cm.date_input >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " cm.date_input <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	
    	$db = $this->getAdapter(); 
    	$sql = " SELECT cm.`id`,
					(SELECT b.`branch_namekh` FROM `ln_branch` AS b WHERE b.`br_id`=cm.`branch_id`) AS branch,
					pp.saving_number,
					(SELECT c.`name_kh` FROM `ln_clientsaving` AS c WHERE c.`client_id`=cm.`client_id` AND client_type=1 ) AS client_name ,
					cm.`receipt_no`,
					cm.`principal_amount`,
					cm.`interest_amount`,
					cm.`penalize_amount`,
					cm.`total_amount`,
					cm.`recieve_amount`,
					cm.`date_pay`,
					cm.`date_input`
				FROM `ln_pawn_receipt_money` AS cm,ln_pawnshop AS pp WHERE pp.id=cm.pawn_id ";
    	if(!empty($search['advance_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['advance_search']));
    		$s_where[] = " pp.`saving_number` LIKE '%{$s_search}%'";
    		$s_where[] = " cm.`receipt_no` LIKE '%{$s_search}%'";
    		
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
//     	if($search['status']!=""){
//     		$where.= " AND status = ".$search['status'];
//     	}
    	
    	if($search['client_name']>0){
    		$where.=" AND cm.`client_id`= ".$search['client_name'];
    	}
    	if($search['branch_id']>0){
    		$where.=" AND cm.`branch_id`= ".$search['branch_id'];
    	}
    	
    	$order = " ORDER BY id DESC ";
    	//echo $sql.$where.$order;
    	return $db->fetchAll($sql.$where.$order);
    }
    function getPawnAccountNumber(){//type ==1 is ilPayment, type==2 is group payment
    	$db = $this->getAdapter();
    	$sql ="
    	SELECT id,CONCAT((SELECT name_en FROM `ln_clientsaving` WHERE 
    		client_id = ln_pawnshop.client_id LIMIT 1),'-',`saving_number`) AS `name`,`branch_id`
    		 FROM `ln_pawnshop` WHERE `is_completed` = 0 AND status=1 ";
    	return $db->fetchAll($sql);
    }
    public function getClientNamebyBranch($type=null,$client_id=null ,$row=null){
    	$this->_name='ln_client';
    	$where='';
    	$sql = " SELECT client_id AS id,CONCAT(name_en,' - ',name_kh) AS name,branch_id,client_number From ln_clientsaving
    	WHERE status=1 AND name_en!='' ORDER BY client_id DESC ";
    	$db = $this->getAdapter();
    	return $db->fetchAll($sql.$where);
    }
    public function getClientCodebyBranch($type=null,$client_id=null ,$row=null){
    	$this->_name='ln_client';
    	$where='';
    	$sql = " SELECT client_id AS id,client_number AS name,branch_id,client_number From ln_clientsaving
    	WHERE status=1 AND name_en!='' ORDER BY client_id DESC ";
    	$db = $this->getAdapter();
    	return $db->fetchAll($sql.$where);
    }
	function getPawnPaymentByID($id){
		$db = $this->getAdapter();
		$sql="SELECT * FROM `ln_pawnshop` WHERE id = $id LIMIT 1";
		return $db->fetchRow($sql);
	}
	
}

