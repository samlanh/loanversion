<?php 
Class Capital_Form_FrmInterest extends Zend_Dojo_Form {
	public function frmInterest($_data=null)
	{ 
		$interest1=new Zend_Dojo_Form_Element_NumberTextBox('interest1');
		$interest1->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'	=>	'fullside',
				'required'	=> true,
				'Onkeyup'	=>	'validateTransfer(2);'
		));
		$interest1->setValue(0);
		
		$interest2=new Zend_Dojo_Form_Element_NumberTextBox('interest2');
		$interest2->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'placeHolder'   =>  '0',
				'class'	=>	'fullside',
				'required'	=> true,
				'Onkeyup'	=>	'validateTransfer(3);'
		));
		$interest2->setValue(0);
		
		$interest3=new Zend_Dojo_Form_Element_NumberTextBox('interest3');
		$interest3->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'placeHolder'   =>  '0',
				'class'	=>	'fullside',
				'Onkeyup'	=>	'validateTransfer(1);',
		));
		$interest3->setValue(0);
		
		$interest4=new Zend_Dojo_Form_Element_NumberTextBox('interest4');
		$interest4->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'	=>	'fullside',
				'required'	=> true,
				'Onkeyup'	=>	'validateTransfer(2);'
		));
		$interest4->setValue(0);
		
		$inter_one = new Zend_Dojo_Form_Element_ValidationTextBox("inter_one");
		$inter_one->setAttribs(array(
			'dojoType'	=>	'dijit.form.ValidationTextBox',
			'class'	=>	'fullside',
			'required'=>true,
		));
		
		$inter_two = new Zend_Dojo_Form_Element_ValidationTextBox("inter_two");
		$inter_two->setAttribs(array(
				'dojoType'	=>	'dijit.form.ValidationTextBox',
				'class'	=>	'fullside',
				'required'=>true,
		));
		
		$inter_three = new Zend_Dojo_Form_Element_ValidationTextBox("inter_three");
		$inter_three->setAttribs(array(
				'dojoType'	=>	'dijit.form.ValidationTextBox',
				'class'	=>	'fullside',
				'required'=>true,
		));
		
		$inter_four = new Zend_Dojo_Form_Element_ValidationTextBox("inter_four");
		$inter_four->setAttribs(array(
				'dojoType'	=>	'dijit.form.ValidationTextBox',
				'class'	=>	'fullside',
				'required'=>true,
		));
		 
		$id = new Zend_Form_Element_Hidden('id');
		if($_data!=null){
			$interest1->setValue($_data['value']);
			$inter_one->setValue($_data['label']);
// 			$interest3->setValue($_data['inter_three']);
// 			$interest4->setValue($_data['inter_four']);
// 			$inter_one->setValue($_data['value']);
// 			$inter_two->setValue($_data['label']);
			$id->setValue($_data['id']);
		
		}
		$this->addElements(array($id,$interest1,$interest2,$interest3,$interest4,$inter_one,$inter_two,$inter_three,$inter_four));
		return $this;
	}
}
?>