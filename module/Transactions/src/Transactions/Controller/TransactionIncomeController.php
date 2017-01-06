<?php
namespace Transactions\Controller;

use Transactions\Controller\AbstractTransactionController;

class TransactionIncomeController extends AbstractTransactionController
{
	public function __construct()
	{
		$this->type=1;
	}
}