<?php
require_once '../models/Transaction.php';

class TransactionController {
    private $transactionModel;

    public function __construct() {
        $this->transactionModel = new Transaction();
    }

    public function getUserTransactions($wallet) {
        $transactions = $this->transactionModel->getByWallet($wallet);
        Response::send(200, $transactions);
    }
}