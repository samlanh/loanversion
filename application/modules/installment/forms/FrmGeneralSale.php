<?php 
Class Installment_Form_FrmGeneralSale extends Zend_Dojo_Form {
	protected $tr;
public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmAddGeneralSale($data=null){
		$session_user=new Zend_Session_Namespace('authloan');
		$currentBranch = $session_user->branch_id;
		$currentlevel = $session_user->level;
		$db = new Application_Model_DbTable_DbGlobal();
		
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
				'onchange'=>'filterClient();',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
		));
		
		$rows = $db->getAllBranchName();
		$options=array(''=>$this->tr->translate("Choose Branch"));
		if(!empty($rows))foreach($rows AS $row){
			$options[$row['br_id']]=$row['branch_namekh'];
		}
		$_branch_id->setMultiOptions($options);
		
		//Set Value Current Branch
		if ($currentlevel!=1){
			$_branch_id->setValue($currentBranch);
			$_branch_id->setAttribs(array(
					'readonly'=>true
			));
		}
		
		$_saleNO = new Zend_Dojo_Form_Element_TextBox('saleNO');
		$_saleNO->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'style'=>'color:red; font-weight: bold;',
				'readonly'=>'true'
		));
		$loan_number = $db->getGeneralSaleNumber($currentBranch);
		$_saleNO->setValue($loan_number);
		
		$_client_code = new Zend_Dojo_Form_Element_TextBox('client_code');
		$_client_code->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$_members = new Zend_Dojo_Form_Element_TextBox('members');
		$_members->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		$_note = new Zend_Dojo_Form_Element_TextBox('note');
		$_note->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$_dateSold = new Zend_Dojo_Form_Element_DateTextBox('dateSold');
		$_dateSold->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required' =>'true',
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
		));
		$_dateSold->setValue(date("Y-m-d"));
		
		$_total = new Zend_Dojo_Form_Element_NumberTextBox("total");
		$_total->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required' =>'true',
				'readonly'=>'true'
		));
		
		$_paid = new Zend_Dojo_Form_Element_NumberTextBox("paid");
		$_paid->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required' =>'true',
				'onKeyup' =>'calculateBalance()'
		));
		
		$_balance = new Zend_Dojo_Form_Element_NumberTextBox("balance");
		$_balance->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required' =>'true',
				'readonly'=>'true'
		));
		
		$_id = new Zend_Form_Element_Hidden('id');
		if($data!=null){
			$_branch_id->setValue($data['branch_id']);
			$_saleNO->setValue($data['saleNO']);
// 			$_client_code->setValue($data['customerId']);
// 			$_members->setValue($data['customerId']);
			$_note->setValue($data['note']);
			$_dateSold->setValue(date("Y-m-d",strtotime($data['dateSold'])));
			$_total->setValue($data['total']);
			$_paid->setValue($data['paid']);
			$_balance->setValue($data['balance']);
			$_id->setValue($data['id']);
		}
		$this->addElements(array(
				$_branch_id,
				$_saleNO,
				$_client_code,
				$_members,
				$_note,
				$_dateSold,
				$_total,
				$_paid,
				$_balance,
				$_id));
		return $this;
	}	
}