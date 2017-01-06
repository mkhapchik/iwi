<?php
namespace Transactions\Controller;

use Transactions\Controller\AbstractTransactionController;

class TransactionExpenseController extends AbstractTransactionController
{
	public function __construct()
	{
		$this->type=0;
	}
}