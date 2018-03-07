<?php

class Pawnshop_Model_DbTable_DbPawnshopdach extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_pawnshopdach';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authloan');
    	return $session_user->user_id;
    	 
    }
    public function addPawnshopdach($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    				$dbtable = new Application_Model_DbTable_DbGlobal();
					$day_amount = 1;
				    $day_amount=30;
				     $arr = array(
						'pawn_id'=>$data['id'],
						'note'=>$data['noted'],
						'date_dach'=>$data['date_date'],
						'user_id'=>$this->getUserId(),
						'create_date'=>date("Y-m-d"),
				      );
				$this->insert($arr);//add group loan
				
				$arr = array(
						'is_dach'=>1
						);
				$this->_name="ln_pawnshop";
				$where="id=".$data['id'];
				$this->update($arr, $where);
    			$db->commit();
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Form_FrmMessage::message("INSERT_FAIL");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
}