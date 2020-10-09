<?php
include_once('vendor/autoload.php');

use BonuslyChord\BonuslyChords;
use BonuslyChord\FetchBonuslyTransactions;
use BonuslyChord\Helpers;
use BonuslyChord\FetchBonuslyCompany;

$monthToGet = new DateTime('July 2020');

$fetch = new FetchBonuslyTransactions($monthToGet, false);
$transactions = $fetch->getTransactions();

// Find all users
$company = new FetchBonuslyCompany(false);
$company->fetchAllEmployees();
$processedEmployees = $company->getProcessedEmployees();

// Transactions by day
$transactionsByDay = Helpers::getTransactionByDay($transactions);
Helpers::writeCsv($monthToGet->format('M-Y') . '-byDay.csv', $transactionsByDay);

// Team budget vs spent
$bvspe = Helpers::getBudgetVSpentPerPerson($transactions, $processedEmployees);
Helpers::writeCsv($monthToGet->format('M-Y') . '-byIndividual.csv', $bvspe);
array_shift($bvspe);
$bvspt = Helpers::getBudgetVSpentPerTeam($bvspe);
Helpers::writeCsv($monthToGet->format('M-Y') . '-byTeam.csv', $bvspt);

// Department budget vs spent
$bvspd = Helpers::getBudgetVSpentPerDepartment($bvspe);
Helpers::writeCsv($monthToGet->format('M-Y') . '-byDepartment.csv', $bvspd);

// Sub-department budget vs spent
$bvspsd = Helpers::getBudgetVSpentPerSubDepartment($bvspe);
Helpers::writeCsv($monthToGet->format('M-Y') . '-bySubDepartment.csv', $bvspsd);

// Totals
$totals = Helpers::getTotals($bvspe);
Helpers::writeCsv($monthToGet->format('M-Y') . '-totals.csv', $totals);

// Original Chord shit
$chords = new BonuslyChords($transactions);
$chordSums = $chords->getSumsAsCsv();

Helpers::writeCsv($monthToGet->format('M-Y') . '-chords.csv', $chordSums);
