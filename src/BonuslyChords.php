<?php

namespace BonuslyChord;

class BonuslyChords
{
    private $transactions;
    private $sums;

    public function __construct($transactions)
    {
        $this->transactions = $transactions;
        $this->compute();
    }

    public  function getSums() {
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

}