 <?php

class Capital_Model_DbTable_DbInterestResource extends Zend_Db_Table_Abstract
{
    protected $_name = 'in_ln_interest';
    public function setName($name)
    {
    	$this->_name=$name;
    }
    public function add($_data){

      $this->_name='in_ln_interest';
	  $_arr = array(
	  		'label'=>$_data['inter_one'],
	    	'value'=>$_data['interest1'],
	    );
	  $this->insert($_arr); 
	  
	  $_arr = array(
	  		'label'=>$_data['inter_two'],
	  		'value'=>$_data['interest2'],
	  );
	  $this->insert($_arr);
	  
	  $_arr = array(
	  		'label'=>$_data['inter_three'],
	  		'value'=>$_data['interest3'],
	  );
	  
	  $this->insert($_arr);
	  $_arr = array(
	  		'label'=>$_data['inter_four'],
	  		'value'=>$_data['interest4'],
	  );
	  $this->insert($_arr);
 } 
 
function getInterest(){
		$db=$this->getAdapter();
		$sql="SELECT id ,label, value FROM `in_ln_interest` ";
		return $db->fetchAll($sql);
	}
	
	public function updateinterestId($_data){
		$_arr = array(
				'value'=>$_data['interest1'],
	    		'label'=>$_data['inter_one'],
		);
		$where = "id =".$_data['id'];
		$this->update($_arr, $where);
	
	}
	public function getinterestId($id)
	{
		$db = $this->getAdapter();
		$sql="SELECT id , value,label FROM `in_ln_interest` WHERE id = $id";
		return $db->fetchRow($sql);
	}
     
     
}
