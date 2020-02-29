<?php

namespace BonuslyChord;
include_once('FetchBonuslyBase.php');
use BonuslyChord\FetchBonuslyBase;

class FetchBonuslyTransactions extends FetchBonuslyBase {
    private $totalFetched = 0;
    private $transactions = [];
    private $lastRecordDateTime;
    private $numberOfDaysToGet;
    private $sums;

    public function __construct($numberOfDaysToGet = 30) {
        parent::__construct('bonuses');
        $this->numberOfDaysToGet = $numberOfDaysToGet;
        $this->fetchAllTransactions();
        $this->compute();
    }

    public function fetchAllTransactions() {
        $json = $this->getJson();
        $this->transactions = array_merge($this->transactions, $json);
        $amountFetched = count($json);
        if ($this->totalFetched == 0) {
            $this->recordLastTransactionDate($json);
        }
        if ($this->keepGoing($json)) {
            $this->totalFetched = $this->totalFetched + $amountFetched;
            $this->setRecordsToSkip($this->totalFetched);
            $this->fetchAllTransactions();
        }
    }

    public function getTransactions() {
        return $this->transactions;
    }

    private function recordLastTransactionDate($json) {
        $firstRecord = reset($json);
        $this->lastRecordDateTime = new \DateTime($firstRecord['created_at']);
    }

    private function keepGoing($json) {
        $firstRecord = end($json);
        $firstRecordDateTime = new \DateTime($firstRecord['created_at']);
        $diff = $firstRecordDateTime->diff($this->lastRecordDateTime);
        if ($diff->d < $this->numberOfDaysToGet) {
            return true;
        }
        return false;
    }

    private function compute() {
        $sums = [];
        foreach ($this->transactions as $transaction) {
            $departmentFrom = $this->getDepartment($transaction, 'giver');
            $departmentTo = $this->getDepartment($transaction, 'receiver');
            $amount = $transaction['amount'];
            if (isset($sums[$departmentFrom . " TO " . $departmentTo])) {
                $sums[$departmentFrom . " TO " . $departmentTo] = $sums[$departmentFrom . " TO " . $departmentTo] + $amount;
            }
            else {
                $sums[$departmentFrom . " TO " . $departmentTo] = $amount;
            }
        }
        ksort($sums);
        $this->sums = $sums;
    }

    /**
     * @param $transaction
     * @param $role
     *   Needs to one of 'giver' or 'receiver'
     * @return string
     */
    private function getDepartment($transaction, $role) {
        if (isset($transaction[$role]['sub_department'])) {
            return $transaction[$role]['sub_department'];
        }
        elseif (isset($transaction[$role]['department'])) {
            return $transaction[$role]['department'];
        }
        return 'undefined';
    }

    public function getSums() {
        return $this->sums;
    }

    public function getSumsAsCsv() {
        $values[] = [
            'from',
            'to',
            'count',
        ];
        foreach ($this->sums as $departments=>$sum) {
            $splitDepartments = explode(" TO ", $departments);
            $from = $splitDepartments[0];
            $to = $splitDepartments[1];
            $values[] = [
                $from,
                $to,
                $sum,
            ];
        }
        return $values;
    }

    public function writeCsv($filename) {
        $fp = fopen('data/' . $filename, 'w');

        foreach ($this->getSumsAsCsv() as $fields) {
            fputcsv($fp, $fields);
        }
    }

}