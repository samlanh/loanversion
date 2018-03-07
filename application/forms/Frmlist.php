<?php
/*
 * Author: 	Mok channy
 * Date	 : 	18-Feb-2014
 */
class Application_Form_Frmlist //extends Zend_Controller_Action
{
//   public function getPagine($url,$start,$limit,$record_count){
//   	$frm_list = new Application_Model_GlobalClass();
//     $rs_page =$frm_list->getList($url,"list",$start, $limit,$record_count); 
//     return $rs_page;
//   }	
  /* $url     : url for current action 
   * $collumn : number of collumn in gride view
   * $result  : result for execute query 
   * $start   : number of start select result
   * $limit   :n
   **/
	public function getCheckList($delete=0, $columns,$rows,$link=null,$editLink="", $class='items', $textalign= "left", $report=false, $id = "table")
	{
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		/*
		 * Define string of pagination Sophen 27 June 2012
		*/
		$stringPagination = '<script type="text/javascript">
		$(document).ready(function(){
		$("#'.$id.'").tablesorter();
		$("#'.$id.'").tablesorter().tablesorterPager({container: $("#pagination_'.$id.'"),size:15});
		$("#pagedisplay").focus(function(){ this.blur();
	});
	});
	</script>
	<div id="pagination_'.$id.'" class="pager" >
	<form >
	<table  style="width: 200px;"><tr>
	<td><img src="'.BASE_URL.'/images/first.gif" class="first"/></td>
	<td><img src="'.BASE_URL.'/images/previous.gif" class="prev"/></td>
	<td><input id="pagedisplay" type="text" class="pagedisplay"/></td>
	<td><img src="'.BASE_URL.'/images/next.gif" class="next"/></td>
	<td><img src="'.BASE_URL.'/images/last.gif" class="last"/></td>
	<td><select class="pagesize" >
	<option   value="10">10</option>
	<option selected="selected"  value="15">15</option>
	<option value="20">20</option>
	<option value="30">30</option>
	<option value="40">40</option>
	<option value="50">50</option>
	<option value="60">60</option>
	<option value="70">70</option>
	<option value="80">80</option>
	<option value="90">90</option>
	<option value="100">100</option>
	</select>
	</td>
	</tr>
	</table>
	</form>
	</div>	';
		/* end define string*/
		 
		$head='<form name="list"><div style="overflow:scroll; max-height: 450px; overflow-x:hidden;" ><table class="collape tablesorter" id="'.$id.'" width="100%">';
		$col_str='';
		$col_str .='<thead><tr>';
		if($delete== 1) {
			$col_str .= '<th class="tdheader tdcheck"></td>';
		}
		$col_str .= '<th class="tdheader">'.$tr->translate("NUM").'</th>';
		//add columns
		foreach($columns as $column){
			$col_str=$col_str.'<th class="tdheader">'.$tr->translate($column).'</th>';
		}
		if($editLink != "") {
			$col_str .='<th class="tdheader tdedit">'.$tr->translate('EDIT_CAP').'</th>';
		}
		$col_str.='</tr></thead>';
		$row_str='<tbody>';
		//add element rows
		if($rows==NULL) return $head.$col_str.'</table></div><center style="font-size:18pt;">No record</center></form>';
		$temp=0;
		/*------------------------Check param id----------------------------------*/
	
		/*------------------------End check---------------------------------------*/
		$r=0;
		foreach($rows as $row){
			if($r%2==0)$attb='normal';
			else $attb='alternate';
			$r++;
			//-------------------check select-----------------
	
			//-------------------end check select-----------------
			$row_str.='<tr class="'.$attb.'"> ';
			$i=0;
			foreach($row as $key=>$read) {
				if($read==null) $read='&nbsp';
				if($i==0) {
					$temp=$read;
					if($delete==1){
						$row_str .= '<td><input type="checkbox" name="del[]" id="del[]" value="'.$temp.'" /></td>';
					}
					$row_str.='<td class="items-no">'.$r.'</td>';
				} else {
					if($link!=null){
						foreach($link as $column=>$url)
							if($key==$column){
							$img='';
							$array=array('tag'=>'a','attribute'=>array('href'=>Application_Form_FrmMessage::redirectorview($url).'?id='.$temp));
							$read=$this->formSubElement($array,$img.$read);
						}
					}
					$text='';
					if($i!=1){
						$text=$this->textAlign($read);
						$read=$this->checkValue($read);
	
						if($textalign != 'left'){
							$text  = " align=". $textalign;
						}
					}
					$row_str.='<td class="'.$class.'" '.$text.'>'.$read.'</td>';
					if($i == count($columns)) {
						if($editLink != "") {
							$row_str.='<td class="'.$class.'"><a class="edit" href="'.$editLink.'?id='.$temp.'">'.'</a></td>';
						}
					}
				}
				$i++;
			}
			$row_str.='</tr>';
		}
		$counter='<span class="row_num">'.$tr->translate('NUM-RECORD').count($rows).'</span>';
		$row_str.='</tbody>';
		$footer='</table></div></form>';
		if(!$report){
			$footer .= '<div class="footer_list">'.$stringPagination.$counter.'</div>';
		}
		return $head.$col_str.$row_str.$footer;
	}
	public function formSubElement($array,$element='')
	{
		$stat='';
		foreach($array as $tag=>$name){
			if($tag=='tag'){
				$stat.='<'.$name.' ';
				$closetag='</'.$name.'>';
			}
			else
				foreach($name as $att=>$value)
				$stat.=$att.'="'.$value.'" ';
		}
		$stat.=">".$element.$closetag;
		return $stat;
	}
	private function textAlign($value){
		$temp=str_replace(',','', $value);
		if($this->is_date($temp) || strtolower($temp) == "yes" || strtolower($temp) == "no" ) return  'style="text-align:center"';
		else{
			$temp=explode('-', $value);
			if(count($temp)>2){
				if(is_numeric($temp[0]) && is_numeric($temp[2])){
					if(!is_numeric($temp[1]) && strlen($temp[1])==3) return 'style="text-align:center"';
				}
			}
			$pos = strpos($value, "class=\"colorcase");
			if($pos){
				return 'style="text-align:center"';
			}
		}
		return '';
	}
	public function is_date($str)
	{
		try{
			$temp=explode('-', $str);
			if(is_array($temp) && count($temp)>=3){
				if(is_numeric($temp[0]) && is_numeric($temp[1]) && is_numeric(substr($temp[2],0,2))){
	
					$d=substr($temp[2],0,2);
					 
					$m=$temp[1];
					$y=$temp[0];
					if(checkdate($m, $d, $y)) return true;
				}
			}
			return false;
		}catch(Zend_Exception $e){
			return false;
		}
	}
	public function checkValue($value){
		if($this->is_date($value)) return @date_format(date_create($value), 'd-M-Y');
		return $value;
	}
  public function grideView($edit_url,$url,$collumn,$result=null,$start,$limit,$record_count){
  //$page = $this->getPagine($url,$start,$limit,$recount_count);
  $frm_list = new Application_Model_GlobalClass();
  $page =$frm_list->getList($url,"list",$start, $limit,$record_count);
  $result_row = $page["result_row"];
  $rows_per_page = $page["rows_per_page"];
  $nevigation = $page["nevigation"];
  $stringPagination='<style>
  	#grid{
  	margin: 0 auto;
  	}
  	.dojoxGridSortNode{
  		text-align: center;
  		height: 30px;line-height:27px;
  	}
  	.height-text{height:30px; min-width: 350px;
  	}
  	</style>';
  $stringPagination.='<script>
  	dojo.require("dojox.grid.DataGrid");
  	dojo.require("dijit.Dialog");
  	dojo.require("dojo.data.ItemFileWriteStore");
  	dojo.require("dojo.store.Memory");';
    $rs=(Zend_Json::encode($result));
    $stringPagination.="var tran_store  = getDataStorefromJSON('id','name',$rs)";
  	$stringPagination.=';dojo.ready(function(){
  	
  			grid = new dojox.grid.DataGrid({						
  				store: tran_store,	
  				autoHeight: true, 		
  				structure: [
  					{ name: "N.", field: "num", width: "40px", cellStyles: "text-align: center;" },
  					{ name: "id", field: "id", hidden: "true" },';
  	if(!empty($result['err'])){
  		echo '<script>alert("មិន​ទាន់​មាន​ទន្និន័យ​​ទេ!");</script>';
  	}
  	$key_col = @array_keys(@$result[0]);$key_index = 2;
  	$tr=Application_Form_FrmLanguages::getCurrentlanguage();
  	for($i=0;$i<count($collumn);$i++){
  		$stringPagination.="{ name:'".$tr->translate($collumn[$i])."',field: '".$key_col[$key_index]."', width: 'auto'},";
  		$key_index++;
  	}
  			$stringPagination.="]
  			}, 'grid');
  			grid.startup();
  	
  			dojo.connect(grid,  'onRowClick', grid, function(evt){
  				var idx = evt.rowIndex,
  					item = this.getItem(idx);
  				window.location = '".$edit_url."/id/' + this.store.getValue(item, 'id');
  			});
  		});	
  	</script>";
  	$stringPagination.='<table class="full">
  	<tr>
  	<td colspan="2">
  	<div id="grid" ></div>
  	</td>
  	</tr>
  	<tr>
  	<td>'.$result_row.'</td>	
  	          <td align="right" >'
  			    .$rows_per_page.
  			  '</td>
  		  </tr>
  		  <tr>
  			  	<td colspan="2" align="center">
  			  		<div id="navigetion" style="margin: 0 auto;">'.$nevigation.'</div>
  			  	</td>
  		  </tr>	  
  	</table>';
  	
  	return $stringPagination;
  }
}