<?php
/*
############################################################################
#  This file is part of OurBank.
############################################################################
#  OurBank is free software: you can redistribute it and/or modify
#  it under the terms of the GNU Affero General Public License as
#  published by the Free Software Foundation, either version 3 of the
#  License, or (at your option) any later version.
############################################################################
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU Affero General Public License for more details.
############################################################################
#  You should have received a copy of the GNU Affero General Public License
#  along with this program.  If not, see <http://www.gnu.org/licenses/>.
############################################################################
*/
class Loandetailsg_IndexController extends Zend_Controller_Action 
{
    public function init() 
    {
    	$this->view->title = "Loans";
	$this->view->pageTitle = "Loans details";
	$this->view->type='loans';
        $this->view->cl = new App_Model_Users ();

        $storage = new Zend_Auth_Storage_Session();
        $data = $storage->read();
        if(!$data){
                $this->_redirect('index/login'); // once session get expired it will redirect to Login page
        }


        $sessionName = new Zend_Session_Namespace('ourbank');
        $userid=$this->view->createdby = $sessionName->primaryuserid; // get the stored session id 

        $loginname=$this->view->cl->username($userid);
        foreach($loginname as $loginname) {
            $this->view->username=$loginname['username']; // get the user name
        }      
        $this->view->loanModel = new Loandetailsg_Model_loandetails();
        $this->view->adm = new App_Model_Adm ();
    }

    public function indexAction() 
    {
	$loansearch = new Loandetailsg_Form_Search();
	$this->view->form = $loansearch;
	$loantransactions = new Loandisbursmentg_Model_loan();
        $errormsg=$this->_request->getParam('msg');
        if($errormsg==1)
        {
            echo "<font color='red'>Invalid account number</font>"; 
        }
        if($errormsg==2)
        {
            echo "<font color='red'>Value is required and can't be empty</font>";
        }
    }

    public function loandetailsAction() 
    {   $accNum=$this->_request->getParam('accNum');
	$this->view->details = $details=$this->view->loanModel->searchaccounts($this->_request->getParam('accNum'));

        if($this->view->details){
        $overdue=$this->view->loanModel->findoverdue($accNum);
        if($overdue) {
            foreach($overdue as $overduedetails)
            {
                $this->view->loanModel->updateinstallment($overduedetails['accountid'],$overduedetails['installment_id']);
            }
        }

	foreach($this->view->details as $interest){
		$this->view->intesttype=$interest->interesttype;
	}
        if($this->view->details){
	$this->view->instalments = $this->view->loanModel->loanInstalments($this->_request->getParam('accNum'));
	$this->view->paid = $this->view->loanModel->paid($this->_request->getParam('accNum'));
	$this->view->declainedpaid= $this->view->loanModel->declainedpaid($this->_request->getParam('accNum'));
        foreach($this->view->declainedpaid as $declainpaid) {
            $this->view->paidamound+=$declainpaid['paid_amount'];
            $balance[]=$declainpaid['balanceamount'];
        }
//         echo $this->view->paidamound;
             $this->view->balanceamount=end($balance);
	$this->view->unpaid = $this->view->loanModel->unpaid($this->_request->getParam('accNum'));
        }
        else {
            if(!$accNum)
                { $errorno=2; }
                else 
                {   $errorno=1; }
                $this->_redirect("/loandetailsg/index/index/msg/".$errorno);
        }
    }
}
}
