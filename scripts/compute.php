<?php
include_once('src/FetchBonuslyTransactions.php');
use BonuslyChord\FetchBonuslyTransactions;

$fetch = new FetchBonuslyTransactions(29);
$fetch->writeCsv('feb-2020.csv');
