<?php

namespace BonuslyChords;

use BonuslyChord\FetchBonuslyBase;

class FetchBonuslyCompany extends FetchBonuslyBase
{
    private $employees = [];
    private $totalFetched = 0;

    public function __construct()
    {
        parent::__construct('users');
    }

    public function fetchAllEmployees() {
        $json = $this->getJson();
        $amountFetched = count($json);
        if ($amountFetched) {
            $this->employees = array_merge($this->employees, $json);
            $this->totalFetched = $this->totalFetched + $amountFetched;
            $this->setRecordsToSkip($this->totalFetched);
            $this->fetchAllEmployees();
        }
    }

    public function getEmployees() {
        return $this->employees;
    }
}