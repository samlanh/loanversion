<?php 
class Installment_Form_FrmPurchase extends Zend_Form
{
	protected $tr;
	protected $filter=null;
	protected $text=null;
	public function init()
    {
//     	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
//     	$request=Zend_Controller_Front::getInstance()->getRequest();
    	
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$this->filter = 'dijit.form.FilteringSelect';
    	$this->text = 'dijit.form.TextBox';
    	
	}
	/////////////	Form Purchase		/////////////////
	public function searchPurchase($data=null){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$db = new Installment_Model_DbTable_DbProduct();
		
		$_title = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$_title->setAttribs(array('dojoType'=>$this->text,
				'onkeyup'=>'this.submit()',
				'placeholder'=>$tr->translate("ADVANCE_SEARCH"),
				'class'=>'fullside'
				));
		$_title->setValue($request->getParam("adv_search"));
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside'));
		$_status_opt = array(
				-1=>$tr->translate("ALL"),
				1=>$tr->translate("ACTIVE"),
				0=>$tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status"));
		
		$branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'true'
		));
		$dbg = new Application_Model_DbTable_DbGlobal();
		$rows = $dbg->getAllBranchName();
		$options_branch=array(''=>$this->tr->translate("Choose Branch"));
		if(!empty($rows))foreach($rows AS $row){
			$options_branch[$row['br_id']]=$row['branch_namekh'];
		}
		$branch_id->setMultiOptions($options_branch);
		$branch_id->setValue($request->getParam("branch_id"));
		
		
		$from_date = new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$from_date->setAttribs(array('dojoType'=>'dijit.form.DateTextBox',
				//'required'=>'true',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'class'=>'fullside',
				'onchange'=>'CalculateDate();'));
		$_date = $request->getParam("start_date");
		
		if(empty($_date)){
		}
		$from_date->setValue($_date);
		
		
		$to_date = new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$to_date->setAttribs(array('dojoType'=>'dijit.form.DateTextBox',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'required'=>'true','class'=>'fullside',
		));
		$_date = $request->getParam("end_date");
		
		if(empty($_date)){
			$_date = date("Y-m-d");
		}
		$to_date->setValue($_date);
		
		$supllier = new Zend_Dojo_Form_Element_FilteringSelect('supllier');
		$supllier->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'true'
		));
		$dbg = new Installment_Model_DbTable_DbPurchase();
		$rows = $dbg->getSuplierName();
		$options_branch=array(''=>$this->tr->translate("Choose Supplier"));
		if(!empty($rows))foreach($rows AS $row){
			$options_branch[$row['id']]=$row['sup_name'];
		}
		$supllier->setMultiOptions($options_branch);
		$supllier->setValue($request->getParam("supllier"));
		
// 		if($data!=null){
// 			$name->setValue($data["item_name"]);
// 			$pro_code->setValue($data["item_code"]);
// 			$barcode->setValue($data["barcode"]);
// 			$serial->setValue($data["serial_number"]);
// 			$category->setValue($data["cate_id"]);
// 			$size->setValue($data["size_id"]);
// 			$description->setValue($data["note"]);
// 			$status->setValue($data["status"]);
// 			$price->setValue($data["price"]);
// 		}
		
		$this->addElements(array($to_date,$from_date,$branch_id,$_title,$_status,$supllier));
		return $this;
		
	}
}