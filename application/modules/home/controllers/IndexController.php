<?php
class Home_indexController extends Zend_Controller_Action {
	
	
public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
	}
	public function indexAction()
	{
		$db = new Home_Model_DbTable_DbDashboard();
		$CountAllClient = $db->CountAllClient();
		$CountAllAgency = $db->CountAllAgency();
		
		$this->view->AllClient = $CountAllClient;
		$this->view->CountAllAgency = $CountAllAgency;
		
		$TotalExpense = $db->TotalExpense();
		$TotalOtherIncome = $db->getTotalOtherIncome();
		$loanCollect = $db->getTotalLoanCollectIncome();
		
		$incomeOtherDollar=0;
		$incomeOtherRiels=0;
		$incomeOtherBath=0;
		if (!empty($TotalOtherIncome)) foreach ($TotalOtherIncome as $rs){
			if ($rs['curr_type']==1){
				$incomeOtherRiels = $incomeOtherRiels + $rs['total'];
			}else if ($rs['curr_type']==2){
				$incomeOtherDollar = $incomeOtherDollar + $rs['total'];
			}else if ($rs['curr_type']==3){
				$incomeOtherBath = $incomeOtherBath + $rs['total'];
			}
			
		}
		
		$incomeDollar=0;
		$incomeRiels=0;
		$incomeBath=0;
		if (!empty($loanCollect)) foreach ($loanCollect as $rs){
			if ($rs['currency_type']==1){
				$incomeRiels = $incomeRiels + $rs['total'];
			}else if ($rs['currency_type']==2){
				$incomeDollar = $incomeDollar + $rs['total'];
			}else if ($rs['currency_type']==3){
				$incomeBath = $incomeBath + $rs['total'];
			}
				
		}
		$totalIncomeDollar=$incomeOtherDollar + $incomeDollar;
		$totalIncomeRiels=$incomeOtherRiels + $incomeRiels;
		$totalIncomeBath=$incomeOtherBath + $incomeBath;
		$this->view->totalIncomeDollar = $totalIncomeDollar;
		$this->view->totalIncomeRiels = $totalIncomeRiels;
		$this->view->totalIncomeBath = $totalIncomeBath;
		
		$ExpenseDollar=0;
		$ExpenseRiels=0;
		$ExpenseBath=0;
		if (!empty($TotalExpense)) foreach ($TotalExpense as $rs){
			if ($rs['curr_type']==1){
				$ExpenseRiels = $ExpenseRiels + $rs['totalAmount'];
			}else if ($rs['curr_type']==2){
				$ExpenseDollar = $ExpenseDollar + $rs['totalAmount'];
			}else if ($rs['curr_type']==3){
				$ExpenseBath = $ExpenseBath + $rs['totalAmount'];
			}
		
		}
		$this->view->ExpenseDollar = $ExpenseDollar;
		$this->view->ExpenseRiels = $ExpenseRiels;
		$this->view->ExpenseBath = $ExpenseBath;
		
		$netIncomeDollar = $totalIncomeDollar - $ExpenseDollar;
		$netIncomeRiels = $totalIncomeRiels - $ExpenseRiels;
		$netIncomeBath = $totalIncomeDollar - $ExpenseBath;
		$this->view->netIncomeDollar = $netIncomeDollar;
		$this->view->netIncomeRiels = $netIncomeRiels;
		$this->view->netIncomeBath = $netIncomeBath;
		
		
		$countAllLoan = $db->CountAllLoan();
		$this->view->countAllLoan = $countAllLoan;
		
		$CountAllLoanComplete = $db->CountAllLoanComplete();
		$this->view->allLoanComplete = $CountAllLoanComplete;
		
		$badLoan = $db->CountAllBadLoan();
		$this->view->countBadLoan = $badLoan;
		
		$AllPawnShop = $db->getCountPawnShop();
		$this->view->allPawnShop = $AllPawnShop;
		
		$PawnShopCompleted = $db->getCountPawnShop(1);
		$this->view->PawnShopCompleted = $PawnShopCompleted;
		
		$PawnShopDach = $db->getCountPawnShop(null,1);
		$this->view->PawnShopDach = $PawnShopDach;
	}
	
}

