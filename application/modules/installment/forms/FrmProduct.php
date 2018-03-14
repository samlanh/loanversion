<?php 
class Installment_Form_FrmProduct extends Zend_Form
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
	/////////////	Form Product		/////////////////
	public function add($data=null){
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
		
		$opt = array(''=>$tr->translate("PRODUCT_CATEGORY"),-1=>$tr->translate("ADD_NEW_CATEGORY"));
		$category = new Zend_Form_Element_Select("category");
		$category->setAttribs(array(
				'class'=>'form-control select2me',
				'onChange'=>'',
				'class'=>'fullside',
				'dojoType'=>'dijit.form.FilteringSelect',
		));
		$row_cat = $db->getCategory();
		if(!empty($row_cat)){
			foreach ($row_cat as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$category->setMultiOptions($opt);
		$category->setValue($request->getParam("category"));
		
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
		
		$this->addElements(array($to_date,$from_date,$category,$branch_id,$_title,$_status,$category));
		return $this;
		
	}
// 	function productFilter(){
// 		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
// 		$request=Zend_Controller_Front::getInstance()->getRequest();
// 		$db = new Installment_Model_DbTable_DbProduct();
// 		$ad_search = new Zend_Dojo_Form_Element_TextBox("ad_search");
// 		$ad_search->setAttribs(array(
// 				'class'=>'form-control',
// 		));
// 		$ad_search->setValue($request->getParam("ad_search"));
		
// 		$branch = new Zend_Form_Element_Select("branch");
// 		$opt = array(''=>$tr->translate("SELECT_BRANCH"));
// 		$row_branch = $db->getBranch();
// 		if(!empty($row_branch)){
// 			foreach ($row_branch as $rs){
// 				$opt[$rs["id"]] = $rs["name"];
// 			}
// 		}
// 		$branch->setAttribs(array(
// 				'class'=>'form-control select2me',
// 				'dojoType'=>'dijit.form.TextBox',
// 		));
// 		$branch->setMultiOptions($opt);
// 		$branch->setValue($request->getParam("branch"));
		
// 		$status = new Zend_Form_Element_Select("status");
// 		$opt = array('-1'=>$tr->translate("ALL"),'1'=>$tr->translate("ACTIVE"),'0'=>$tr->translate("DEACTIVE"));
// 		$status->setAttribs(array(
// 				'class'=>'form-control select2me',
// 				'dojoType'=>'dijit.form.TextBox',
// 		));
// 		$status->setMultiOptions($opt);
// 		$status->setValue($request->getParam("status"));
		
// 		$opt = array(''=>$tr->translate("SELECT_MODEL"));
// 		$model = new Zend_Form_Element_Select("model");
// 		$model->setAttribs(array(
// 				'class'=>'form-control select2me',
// 				'dojoType'=>'dijit.form.TextBox',
// 		));
// 		$row_model = $db->getModel();
// 		if(!empty($row_model)){
// 			foreach ($row_model as $rss){
// 				$opt[$rss["key_code"]] = $rss["name"];
// 			}
// 		}
// 		$model->setMultiOptions($opt);
// 		$model->setValue($request->getParam("model"));
			
// 		$opt = array(''=>$tr->translate("SELECT_CATEGORY"));
// 		$category = new Zend_Form_Element_Select("category");
// 		$category->setAttribs(array(
// 				'class'=>'form-control select2me',
// 				'dojoType'=>'dijit.form.TextBox',
// 		));
// 		$row_cat = $db->getCategory();
// 		if(!empty($row_cat)){
// 			foreach ($row_cat as $rs){
// 				$opt[$rs["id"]] = $rs["name"];
// 			}
// 		}
// 		$category->setMultiOptions($opt);
// 		$category->setValue($request->getParam("category"));
		
// 		$opt = array(''=>$tr->translate("SELECT_COLOR"));
// 		$color = new Zend_Form_Element_Select("color");
// 		$color->setAttribs(array(
// 				'class'=>'form-control select2me',
// 				'dojoType'=>'dijit.form.TextBox',
// 		));
// 		$row_color = $db->getColor();
// 		if(!empty($row_color)){
// 			foreach ($row_color as $rs){
// 				$opt[$rs["key_code"]] = $rs["name"];
// 			}
// 		}
// 		$color->setMultiOptions($opt);
// 		$color->setValue($request->getParam("color"));
			
// 		$opt = array(''=>$tr->translate("SELECT_SIZE"));
// 		$size = new Zend_Form_Element_Select("size");
// 		$size->setAttribs(array(
// 				'class'=>'form-control select2me',
// 				'dojoType'=>'dijit.form.TextBox',
// 		));
// 		$row_size = $db->getSize();
// 		if(!empty($row_size)){
// 			foreach ($row_size as $rs){
// 				$opt[$rs["key_code"]] = $rs["name"];
// 			}
// 		}
// 		$size->setMultiOptions($opt);
// 		$size->setValue($request->getParam("size"));
		
// 		$status_qty = new Zend_Form_Element_Select("status_qty");
// 		$opt = array(-1=>$tr->translate("ទាំងអស់"),1=>$tr->translate("ផលិតផលមានស្តុក"),0=>$tr->translate("ផលិតផលអស់ពីស្តុក"));
// 		$status_qty->setAttribs(array(
// 				'class'=>'form-control select2me',
// 				'dojoType'=>'dijit.form.TextBox',
// 		));
// 		$status_qty->setMultiOptions($opt);
// 		$status_qty->setValue($request->getParam("status_qty"));
		
// 		$this->addElements(array($status_qty,$ad_search,$branch,$model,$category,$color,$size,$status));
// 		return $this;
// 	}
}