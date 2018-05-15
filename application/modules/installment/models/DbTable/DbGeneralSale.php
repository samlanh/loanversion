<?php

class Installment_Model_DbTable_DbGeneralSale extends Zend_Db_Table_Abstract
{
	protected $_name = 'ln_ins_sales_install';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace('authloan');
		return $session_user->user_id;
	}

	function round_up($value, $places)
	{
		$mult = pow(10, abs($places));
		return $places < 0 ?
		ceil($value / $mult) * $mult :
		ceil($value * $mult) / $mult;
	}
	function round_up_currency($curr_id, $value,$places=-2){
		if ($curr_id==1){//for riel
			$value_array = explode(".", $value);
			return $this->round_up($value, $places);
		}
		else{
			return round($value,2);
		}
	}
	public function getAllSale($search){
		$from_date =(empty($search['start_date']))? '1': "g.`dateSold` >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "g.`dateSold` <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		 
			$db = $this->getAdapter();
			$sql=" SELECT g.id,
			(SELECT b.branch_namekh FROM `ln_branch` AS b WHERE b.br_id = g.`branch_id` LIMIT 1) branchNamekh,
			g.`saleNO`,
			(SELECT c.name_kh FROM `ln_ins_client` AS c WHERE c.client_id = g.`customerId` LIMIT 1) AS name_kh,
			g.`total`,g.`paid`,g.`balance`,
			g.`dateSold`,
			g.`note`,
			g.`status`
			 FROM `ln_ins_generalsale` AS g
			 WHERE 1";
			if(!empty($search['adv_search'])){
				$s_where = array();
				$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
				$s_where[] = "REPLACE(g.`saleNO`,' ','')  	LIKE '%{$s_search}%'";
				$s_where[] = "REPLACE(g.`total`,' ','')  LIKE '%{$s_search}%'";
				$s_where[] = "REPLACE(g.`paid`,' ','')  LIKE '%{$s_search}%'";
				$s_where[] = "REPLACE(g.`note`,' ','')  LIKE '%{$s_search}%'";
				$where .=' AND ('.implode(' OR ',$s_where).')';
			}
			if($search['status']>-1){
				$where.= " AND g.`status` = ".$search['status'];
			}
			if(($search['member'])>0){
				$where.= " AND g.`customerId`=".$search['member'];
			}
			if(($search['branch_id'])>0){
				$where.= " AND g.`branch_id` = ".$search['branch_id'];
			}
			$dbp = new Application_Model_DbTable_DbGlobal();
			$where.=$dbp->getAccessPermission('g.`branch_id`');
			
			$order = " ORDER BY g.id DESC";
			$db = $this->getAdapter();
			return $db->fetchAll($sql.$where.$order);
	}
	public function addGeneralSale($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$dbtable = new Application_Model_DbTable_DbGlobal();
			$saleNo = $dbtable->getGeneralSaleNumber($data['branch_id']);
	
			$datagroup = array(
					'branch_id'=>$data['branch_id'],
					'saleNO'=>$saleNo,
					'customerId'=>$data['member'],
					'dateSold'=>$data['dateSold'],
					'amount'=>$data['total'],
					'total'=>$data['total'],
					'paid'=>$data['paid'],
					'balance'=>$data['balance'],
					'note'=>$data['note'],
					'createDate'=>date("Y-m-d H:i:s"),
					'userId'=>$this->getUserId(),
					'status'=>1,
			);
			$this->_name='ln_ins_generalsale';
			$sale_id = $this->insert($datagroup);//add 
			
			$identity = $data['identity'];
			$ids = explode(",", $identity);
			
			$dbpo = new Installment_Model_DbTable_DbPurchase();
			if (!empty($ids)){
				foreach ($ids as $i){
					$arr = array(
							'saleId'=>$sale_id,
							'productID'=>$data['productID'.$i],
							'sellingPrice'=>$data['price'.$i],
							'qty'=>$data['qty'.$i],
							'amount'=>$data['amount'.$i],
							'note'=>$data['description'.$i],
							);
					$this->_name='ln_ins_generalsale_detail';
					$this->insert($arr);
					$dbpo->updateStock($data['productID'.$i],$data['branch_id'],-$data['qty'.$i]);
				}
			}
			
			$db->commit();
			return $sale_id;
		}catch (Exception $e){
			$db->rollBack();
			//Application_Form_FrmMessage::message("INSERT_FAIL");
			echo $e->getMessage();exit();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	function getGeneralsaleById($id){
		$db = $this->getAdapter();
		$sql="SELECT g.*
		 FROM `ln_ins_generalsale` AS g
		 WHERE g.id=$id";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('g.branch_id');
		return $db->fetchRow($sql);
	}
	function getGeneraldetailSaleById($saleId){
		$db = $this->getAdapter();
		$sql="SELECT g.*,
		(SELECT p.item_name FROM `ln_ins_product` AS p WHERE p.id = g.`productID` LIMIT 1) AS item_name,
(SELECT p.item_code FROM `ln_ins_product` AS p WHERE p.id = g.`productID` LIMIT 1) AS item_code
		 FROM `ln_ins_generalsale_detail` AS g
		 WHERE g.`saleId`=$saleId";
		return $db->fetchAll($sql);
	}
	
	public function updateGeneralSale($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$dbtable = new Application_Model_DbTable_DbGlobal();
			$saleNo = $dbtable->getGeneralSaleNumber($data['branch_id']);
			$sale_id = $data['id'];
			$dbpo = new Installment_Model_DbTable_DbPurchase();
			$row = $this->getGeneralsaleById($sale_id);
			$rowdetail = $this->getGeneraldetailSaleById($sale_id);
			if (!empty($rowdetail)) foreach ($rowdetail as $old){ //revious stock
				$dbpo->updateStock($old['productID'],$row['branch_id'],$old['qty']);
			}
			
			$datagroup = array(
					'branch_id'=>$data['branch_id'],
					'saleNO'=>$saleNo,
					'customerId'=>$data['member'],
					'dateSold'=>$data['dateSold'],
					'amount'=>$data['total'],
					'total'=>$data['total'],
					'paid'=>$data['paid'],
					'balance'=>$data['balance'],
					'note'=>$data['note'],
					'createDate'=>date("Y-m-d H:i:s"),
					'userId'=>$this->getUserId(),
					'status'=>1,
			);
			$this->_name='ln_ins_generalsale';
			$where=" id = ".$sale_id;
// 			$sale_id = $data['id'];
			$this->update($datagroup, $where);//add

			
			
			$identity = $data['identity'];
			$ids = explode(",", $identity);
				
			if (!empty($ids)){
				$detailId="";
					foreach ($ids as $i){
						if (empty($detailId)){
							if (!empty($data['detailid'.$i])){
								$detailId = $data['detailid'.$i];
							}
						}else{
							if (!empty($data['detailid'.$i])){
								$detailId= $detailId.",".$data['detailid'.$i];
							}
						}
					}
					$this->_name="ln_ins_generalsale_detail";
					$where="saleId = ".$sale_id;
					if (!empty($detailId)){
						$where.=" AND id NOT IN ($detailId) ";
					}
					$this->delete($where);
				
				foreach ($ids as $i){
					if (!empty($data['detailid'.$i])){
						$arr = array(
								'saleId'=>$sale_id,
								'productID'=>$data['productID'.$i],
								'sellingPrice'=>$data['price'.$i],
								'qty'=>$data['qty'.$i],
								'amount'=>$data['amount'.$i],
								'note'=>$data['description'.$i],
						);
						$this->_name='ln_ins_generalsale_detail';
						$where1=" id = ".$data['detailid'.$i];
						$this->update($arr, $where1);
						$dbpo->updateStock($data['productID'.$i],$data['branch_id'],-$data['qty'.$i]);
					}else{
						$arr = array(
								'saleId'=>$sale_id,
								'productID'=>$data['productID'.$i],
								'sellingPrice'=>$data['price'.$i],
								'qty'=>$data['qty'.$i],
								'amount'=>$data['amount'.$i],
								'note'=>$data['description'.$i],
						);
						$this->_name='ln_ins_generalsale_detail';
						$this->insert($arr);
						$dbpo->updateStock($data['productID'.$i],$data['branch_id'],-$data['qty'.$i]);
					}
				}
			}
				
			$db->commit();
			return $sale_id;
		}catch (Exception $e){
			$db->rollBack();
			//Application_Form_FrmMessage::message("INSERT_FAIL");
			echo $e->getMessage();exit();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	function getProductInfo($data){
		$pro_id = $data['product_name'];
		$db = $this->getAdapter();
		$sql="SELECT p.* FROM `ln_ins_product` AS p WHERE p.`id` = $pro_id LIMIT 1";
		$row = $db->fetchRow($sql);
		$string='';
		$baseurl= Zend_Controller_Front::getInstance()->getBaseUrl();
		if(!empty($row)){
			$string.='
			<td style="text-align: center;vertical-align: middle; ">'.$data['record_no'].'</td>
			<td style="vertical-align: middle; text-align: left; border-left:solid 1px #ccc; ">&nbsp;<label id="labelservice'.$data['record_no'].'">'.$row['item_code'].'</label><input type="hidden" dojoType="dijit.form.TextBox" name="productID'.$data['record_no'].'" id="productID'.$data['record_no'].'" value="'.$row['id'].'" ></td>
			<td style="vertical-align: middle; text-align: left; border-left:solid 1px #ccc; ">&nbsp;<label id="labelitemsname'.$data['record_no'].'">'.$row['item_name'].'</label></td>
			<td><input type="text" class="fullside" dojoType="dijit.form.NumberTextBox" required="required" onKeyup="calculateamount('.$data['record_no'].');"  name="qty'.$data['record_no'].'" id="qty'.$data['record_no'].'" value="1" style="text-align: center;" ></td>
			<td><input type="text" class="fullside" dojoType="dijit.form.NumberTextBox" required="required" onKeyup="calculateamount('.$data['record_no'].');" name="price'.$data['record_no'].'" id="price'.$data['record_no'].'" value="'.$row['selling_price'].'" style="text-align: center;" ></td>
			<td><input type="text" class="fullside" dojoType="dijit.form.NumberTextBox" required="required"  readonly="readonly" name="amount'.$data['record_no'].'" id="amount'.$data['record_no'].'" value="'.$row['selling_price'].'" style="text-align: center;" ></td>
			<td><input type="text" class="fullside" dojoType="dijit.form.TextBox"  name="description'.$data['record_no'].'" id="description'.$data['record_no'].'" value="" style="text-align: center;" ></td>
			<td style="cursor: pointer;text-align: center;  vertical-align: middle;"><img onclick="newdeleteRecord('.$data['record_no'].')" src="'.$baseurl.'/images/Delete_16.png"><input type="hidden" dojoType="dijit.form.TextBox" name="detailid'.$data['record_no'].'" id="detailid'.$data['record_no'].'" value="" ></td>
			';
		}
		$array = array('stringrow'=>$string,'rsult'=>$row,'newrowid'=>$data['record_no']);
		return $array;
	}
}