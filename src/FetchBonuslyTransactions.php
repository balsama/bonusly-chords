<?php

namespace BonuslyChord;

class FetchBonuslyTransactions extends FetchBonuslyBase {
    private $useTestData;
    private $totalFetched = 0;
    private $transactions = [];
    private $lastRecordDateTime;
    private $monthToGet;
    private $sums;

    public function __construct(\DateTime $monthToGet, $useTestData = false) {
        parent::__construct('bonuses');
        $this->useTestData = $useTestData;
        $this->monthToGet = $monthToGet;
        $this->fetchAllTransactions();
    }

    public function fetchAllTransactions() {
        if ($this->useTestData) {
            $this->transactions = json_decode(file_get_contents('sample-data/sep-2020-raw.json'), true);
            return;
        }
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

}