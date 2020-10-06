<?php
include_once('src/FetchBonuslyTransactions.php');
use BonuslyChord\FetchBonuslyTransactions;

$monthToGet = new DateTime('July 2020');
$fetch = new FetchBonuslyTransactions($monthToGet);
$fetch->writeCsv($monthToGet->format('M-Y') . '.csv');
