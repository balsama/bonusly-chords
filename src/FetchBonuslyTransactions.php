<?php

namespace BonuslyChord;
include_once('FetchBonuslyBase.php');
use BonuslyChord\FetchBonuslyBase;

class FetchBonuslyTransactions extends FetchBonuslyBase {
    private $totalFetched = 0;
    private $transactions = [];
    private $lastRecordDateTime;
    private $monthToGet;
    private $sums;

    public function __construct(\DateTime $monthToGet) {
        parent::__construct('bonuses');
        $this->monthToGet = $monthToGet;
        $this->fetchAllTransactions();
        $this->compute();
    }

    public function fetchAllTransactions() {
        $json = $this->getJson();
        foreach ($json as $potential) {
            $thisRecordsDatetime = new \DateTime($potential['created_at']);
            if ($thisRecordsDatetime->format('Y-m') === $this->monthToGet->format('Y-m')) {
                $this->transactions[] = $potential;
            }
        }
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
        $oldestRecordFetchedThusFar = end($json);
        $oldestRecordFetchedThusFarDateTime = new \DateTime($oldestRecordFetchedThusFar['created_at']);
        if ($oldestRecordFetchedThusFarDateTime->format('Ym') < $this->monthToGet->format('Ym')) {
            return false;
        }
        echo "Oldest record fetched thus far is: " . $oldestRecordFetchedThusFarDateTime->format('Y-m-d') . "\n";
        return true;
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