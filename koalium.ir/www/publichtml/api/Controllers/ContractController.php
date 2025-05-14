<?php
require_once '../models/Contract.php';

class ContractController {
    private $contractModel;

    public function __construct() {
        $this->contractModel = new Contract();
    }

    public function getAllContracts() {
        $contracts = $this->contractModel->getAll();
        Response::send(200, $contracts);
    }

    public function getActiveContracts($userId) {
        $contracts = $this->contractModel->getActiveByUser($userId);
        Response::send(200, $contracts);
    }
}