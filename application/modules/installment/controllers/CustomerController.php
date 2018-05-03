<?php
class Installment_CustomerController extends Zend_Controller_Action {
	const REDIRECT_URL = '/group/index';
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			$db = new Installment_Model_DbTable_DbClient();
			if($this->getRequest()->isPost()){
				$formdata=$this->getRequest()->getPost();
				$search = array(
						'adv_search' => $formdata['adv_search'],
						'province_id'=>$formdata['province'],
						'comm_id'=>$formdata['commune'],
						'district_id'=>$formdata['district'],
						'village'=>$formdata['village'],
						'status'=>$formdata['status'],
						'start_date'=> $formdata['start_date'],
						'end_date'=>$formdata['end_date']
						);
			}
			else{
				$search = array(
						'adv_search' => '',
						'status' => -1,
						'province_id'=>0,
						'district_id'=>'',
						'comm_id'=>'',
						'village'=>'',
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'));
			}
			
			$rs_rows= $db->getAllClients($search);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","CUSTOMER_CODE","CLIENTNAME_KH","CLIENTNAME_EN","SEX","PHONE","HOUSE","STREET","VILLAGE","SPOUSE_NAME",
					"TEL","DATE","BY_USER","STATUS");
			$link=array(
					'module'=>'installment','controller'=>'customer','action'=>'edit',
			);
			$link1=array(
					'module'=>'installment','controller'=>'customer','action'=>'view',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('branch_name'=>$link,'client_number'=>$link,'name_kh'=>$link,'name_en'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	
		$frm = new Application_Form_FrmAdvanceSearch();
		$frm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		
		$fm = new Group_Form_FrmClient();
		$frm = $fm->FrmAddClient();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_client = $frm;
		$db= new Application_Model_DbTable_DbGlobal();
		$this->view->district = $db->getAllDistricts();
		$this->view->commune_name = $db->getCommune();
		$this->view->village_name = $db->getVillage();
		$this->view->result=$search;	
	}	
	public function addAction(){
		if($this->getRequest()->isPost()){
				$data = $this->getRequest()->getPost();
				$db = new Installment_Model_DbTable_DbClient();
				try{
					$id= $db->addClient($data);
					if (!empty($data['save_close'])){
							Application_Form_FrmMessage::redirectUrl("/installment/customer");
							Application_Form_FrmMessage::message("ការ​បញ្ចូល​ជោគ​ជ័យ !");
					}
					Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​ជោគ​ជ័យ !',"/installment/customer/add");
			}catch (Exception $e){
				Application_Form_FrmMessage::message("Application Error");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$db = new Application_Model_DbTable_DbGlobal();
		$client_type = $db->getclientdtype();
		array_unshift($client_type,array(
		'id' => -1,
		'name' => '---Add New ---',
		 ) );
		$this->view->clienttype = $client_type;
		$fm = new Installment_Form_FrmClient();
		$frm = $fm->FrmAddClient();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_client = $frm;
		$dbpop = new Application_Form_FrmPopupGlobal();
		$this->view->frm_popup_village = $dbpop->frmPopupVillage();
		$this->view->frm_popup_comm = $dbpop->frmPopupCommune();
		$this->view->frm_popup_district = $dbpop->frmPopupDistrict();
		$this->view->frm_popup_clienttype = $dbpop->frmPopupclienttype();
	}
	
	public function editAction(){
		$db = new Installment_Model_DbTable_DbClient();
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				$id= $db->addClient($data);
				Application_Form_FrmMessage::Sucessfull('EDIT_SUCCESS',"/installment/customer");
			}catch (Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAILE");
				echo $e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$id = $this->getRequest()->getParam("id");
		$row = $db->getClientById($id);
	        $this->view->row=$row;
		$this->view->photo = $row['photo_name'];
		if(empty($row)){
			$this->_redirect("/installment/customer");
		}
		$fm = new Installment_Form_FrmClient();
		$frm = $fm->FrmAddClient($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_client = $frm;
		
		$dbpop = new Application_Form_FrmPopupGlobal();
		$this->view->frm_popup_village = $dbpop->frmPopupVillage();
		$this->view->frm_popup_comm = $dbpop->frmPopupCommune();
		$this->view->frm_popup_district = $dbpop->frmPopupDistrict();
		$this->view->frm_popup_clienttype = $dbpop->frmPopupclienttype();
		
		$db = new Application_Model_DbTable_DbGlobal();
		$client_type = $db->getclientdtype();
		array_unshift($client_type,array(
				'id' => -1,
				'name' => '---Add New ---',
		) );
		$this->view->clienttype = $client_type;
	}
	
	function viewAction(){
		$id = $this->getRequest()->getParam("id");
		$db = new Installment_Model_DbTable_DbClient();
		$this->view->client_list = $db->getClientDetailInfo($id);
	}
}

