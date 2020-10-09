<?php

namespace BonuslyChord;

use BonuslyChord\FetchBonuslyBase;

class FetchBonuslyCompany extends FetchBonuslyBase
{
    private $useTestData;
    private $employees = [];
    private $processedEmployees = [];
    private $totalFetched = 0;

    public function __construct($useTestData = false)
    {
        $this->useTestData = $useTestData;
        parent::__construct('users');
    }

    public function fetchAllEmployees() {
        if ($this->useTestData) {
            $this->employees = json_decode(file_get_contents('sample-data/sep-2020-users-raw.json'), true);
            $this->processEmployees();
            return;
        }
        $json = $this->getJson();
        $amountFetched = count($json);
        if ($amountFetched) {
            $this->employees = array_merge($this->employees, $json);
            $this->totalFetched = $this->totalFetched + $amountFetched;
            $this->setRecordsToSkip($this->totalFetched);
            $this->fetchAllEmployees();
        }
        $this->processEmployees();
    }

    private function processEmployees() {
        foreach ($this->employees as $employee) {
            $this->processedEmployees[$employee['email']] = [
                'name' => $employee['display_name'],
                'budget' => $employee['custom_properties']['is_supervisor'] ? 200 : 100,
                'spent' => 0,
                'manager' => $employee['manager_email'],
                'department' => (array_key_exists('department', $employee)) ? $employee['department'] : 'unknown',
                'sub_department' => (array_key_exists('sub_department', $employee)) ? $employee['sub_department'] : 'unknown',
            ];
        }
        ksort($this->processedEmployees);
    }

    public function getEmployees() {
        return $this->employees;
    }
    public function getProcessedEmployees() {
        return $this->processedEmployees;
    }
}