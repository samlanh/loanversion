<?php

class Other_Model_DbTable_DbBranch extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_branch';
    function addbranch($_data){
    	$part= PUBLIC_PATH.'/images/';
    	if (!file_exists($part)) {
    		mkdir($part, 0777, true);
    	}
    	$photo ="";
    	if (!empty($_FILES['photo']['name'])){
    		$ss =   explode(".", $_FILES['photo']['name']);
    		$image_name = "pawn".date("Y").date("m").date("d").time().".".end($ss);
    		$tmp = $_FILES['photo']['tmp_name'];
    		if(move_uploaded_file($tmp, $part.$image_name)){
    			$photo = $image_name;
    		}
    		else{
    			$string = "Image Upload failed";
    		}
    	}
    	
    	$_arr = array(
    			'branch_namekh'=>$_data['branch_namekh'],
    			'branch_nameen'=>$_data['branch_nameen'],
    			'prefix'=>$_data['prefix_code'],
    			'br_address'=>$_data['br_address'],
    			'branch_code'=>$_data['branch_code'],
    			'branch_tel'=>$_data['branch_tel'],
//     			'fax'=>$_data['fax'],
    			'description'=>$_data['description'],
    			'other'=>$_data['branch_note'],
    			'status'=>$_data['branch_status'],
    			'images_branch'=>$photo
    			//'displayby'=>$_data['branch_display'],
    			);
    	$this->insert($_arr);//insert data
//     	$where = 'id = 1';
//     	$this->delete($where);
    }
    public function updateBranch($_data,$id){
    	$_arr = array(
    			'branch_namekh'=>$_data['branch_namekh'],
    			'branch_nameen'=>$_data['branch_nameen'],
    			'prefix'      =>      $_data['prefix_code'],
    			'br_address'=>$_data['br_address'],
    			'branch_code'=>$_data['branch_code'],
    			'branch_tel'=>$_data['branch_tel'],
//     			'fax'=>$_data['fax'],
    			'description'=>$_data['description'],
    			'other'=>$_data['branch_note'],
    			'status'=>$_data['branch_status'],
    			//'displayby'=>$_data['branch_display'],
    			);
    	$part= PUBLIC_PATH.'/images/';
    	if (!file_exists($part)) {
    		mkdir($part, 0777, true);
    	}
    	$photo ="";
    	if (!empty($_FILES['photo']['name'])){
    		$ss =   explode(".", $_FILES['photo']['name']);
    		$image_name = "pawnshop".date("Y").date("m").date("d").time().".".end($ss);
    		$tmp = $_FILES['photo']['tmp_name'];
    		if(move_uploaded_file($tmp, $part.$image_name)){
    			$photo = $image_name;
    		}
    		else{
    			$string = "Image Upload failed";
    		}
    		$_arr['images_branch']=$photo;
    	}
    	$where=$this->getAdapter()->quoteInto("br_id=?", $id);
    	$this->update($_arr, $where);
    }
    	
    function getAllBranch($search=null){
    	$db = $this->getAdapter();
    	$sql = "SELECT b.br_id,b.branch_namekh,b.branch_nameen,b.prefix,
				 b.branch_code,b.br_address,b.branch_tel,b.other,b.`status`
				 FROM ln_branch AS b ";
    	$where = ' WHERE b.branch_namekh!="" AND b.branch_nameen !="" ';
    	
    	if($search['status_search']>-1){
    		$where.= " AND b.status = ".$search['status_search'];
    	}
    	
    	if(!empty($search['adv_search'])){
    		$s_where=array();
    		$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
    		$s_where[]=" REPLACE(b.prefix,' ','') 			LIKE '%{$s_search}%'";
    		$s_where[]=" REPLACE(b.branch_namekh,' ','')  	LIKE '%{$s_search}%'";
    		$s_where[]=" REPLACE(b.branch_nameen,' ','')  	LIKE '%{$s_search}%'";
    		$s_where[]=" REPLACE(b.br_address,' ','')  		LIKE '%{$s_search}%'";
    		$s_where[]=" REPLACE(b.branch_code,' ','')  	LIKE '%{$s_search}%'";
    		$s_where[]=" REPLACE(b.branch_tel,' ','')  		LIKE '%{$s_search}%'";
    		$s_where[]=" REPLACE(b.fax,' ','')  			LIKE '%{$s_search}%'";
    		$s_where[]=" REPLACE(b.other,' ','')  			LIKE '%{$s_search}%'";
    		$s_where[]=" REPLACE(b.displayby,' ','')  		LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ',$s_where).')';
    	}
    	$order=' ORDER BY b.br_id DESC';
   //echo $sql.$where;
   return $db->fetchAll($sql.$where.$order);
    }
    
 function getBranchById($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT * FROM
    	$this->_name ";
    	$where = " WHERE `br_id`= $id" ;
  
   		return $db->fetchRow($sql.$where);
    }
    public static function getBranchCode(){
    	$db = new Application_Model_DbTable_DbGlobal();
    	$sql = "SELECT COUNT(br_id) AS amount FROM `ln_branch`";
    	$acc_no= $db->getGlobalDbRow($sql);
    	$acc_no=$acc_no['amount'];
    	$new_acc_no= (int)$acc_no+1;
    	$acc_no= strlen((int)$acc_no+1);
    	$pre = "";
    	for($i = $acc_no;$i<3;$i++){
    		$pre.='0';
    	}
    	return "C-".$pre.$new_acc_no;
    }
}  
	  

