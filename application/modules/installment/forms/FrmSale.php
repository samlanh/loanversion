<?php 
class Installment_Form_FrmSale extends Zend_Form
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
	/////////////	Form Sale		/////////////////
	public function searchSale($data=null){
		$session_user=new Zend_Session_Namespace('authloan');
		$currentBranch = $session_user->branch_id;
		$currentlevel = $session_user->level;
		
		$session_user=new Zend_Session_Namespace('authloan');
		$userName=$session_user->user_name;
		$GetUserId= $session_user->user_id;
		$level = $session_user->level;
		$location_id = $session_user->location_id;
		
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
		
		//Set Value Current Branch
		if ($currentlevel!=1){
			$branch_id->setValue($currentBranch);
			$branch_id->setAttribs(array(
					'readonly'=>true
			));
		}else{
			$branch_id->setValue($request->getParam("branch_id"));
		}
		
		
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
		
		$db = new Application_Model_DbTable_DbGlobal();
		$dataclient=$db->getAllClientinstallment($location_id);
		
		$customer = new Zend_Dojo_Form_Element_FilteringSelect('customer');
		$customer->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'true'
		));
		$options_branch=array(''=>$this->tr->translate("Choose Customer"));
		if(!empty($dataclient))foreach($dataclient AS $row){
			$options_branch[$row['id']]=$row['name'];
		}
		$customer->setMultiOptions($options_branch);
		$customer->setValue($request->getParam("customer"));
		
		$opt = array(''=>$tr->translate("PRODUCT_CATEGORY"));
		$category = new Zend_Form_Element_Select("category");
		$category->setAttribs(array(
				'class'=>'form-control select2me',
				'onChange'=>'',
				'class'=>'fullside',
				'dojoType'=>'dijit.form.FilteringSelect',
		));
		$db = new Installment_Model_DbTable_DbProduct();
		$row_cat = $db->getCategory();
		if(!empty($row_cat)){
			foreach ($row_cat as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$category->setMultiOptions($opt);
		$category->setValue($request->getParam("category"));
		
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
		
		$this->addElements(array($category,$to_date,$from_date,$branch_id,$_title,$_status,$customer));
		return $this;
		
	}
}