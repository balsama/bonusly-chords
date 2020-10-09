<?php

namespace BonuslyChord;

class Helpers
{
    public static function getTransactionByDay($transactions) {
        $transactionsByDay = [];
        foreach ($transactions as $transaction) {
            $date = new \DateTime($transaction['created_at']);
            $day = $date->format('Y-m-d');
            if (array_key_exists($day, $transactionsByDay)) {
                $transactionsByDay[$day]++;
            }
            else {
                $transactionsByDay[$day] = 1;
            }
        }
        $transactionsByDay = array_reverse($transactionsByDay, true);
        $transactionsByDay = self::fillMissingDateArrayKeys($transactionsByDay);
        $transactionsByDay = self::includeArrayKeysInArray($transactionsByDay);
        return $transactionsByDay;
    }

    public static function getBudgetVSpentPerPerson($transactions, $employees) {
        foreach ($transactions as $transaction) {
            if (!array_key_exists($transaction['giver']['email'], $employees)) {
                $employee = $transaction['giver'];
                $employees[$employee['email']] = [
                    'name' => $employee['display_name'],
                    'budget' => $employee['custom_properties']['is_supervisor'] ? 200 : 100,
                    'spent' => 0,
                    'unspent' => 0,
                    'manager' => $employee['manager_email'],
                ];
            }
            $amount = ($transaction['amount'] * count($transaction['receivers']));
            $alreadySpent = $employees[$transaction['giver']['email']]['spent'];
            $employees[$transaction['giver']['email']]['spent'] = ($alreadySpent + $amount);
            $employees[$transaction['giver']['email']]['unspent'] = ($employees[$transaction['giver']['email']]['budget'] - $employees[$transaction['giver']['email']]['spent']);
        }

        $normalizedEmployees = [];
        $headers = ['Name', 'Spent', 'Unspent', 'Budget', 'Percent Spent', 'Department', 'Sub-department', 'Manager'];
        foreach ($employees as $employee) {
            $spent = (array_key_exists('spent', $employee)) ? $employee['spent'] : 0;
            $department = (array_key_exists('department', $employee)) ? $employee['department'] : 'unknown';
            $subdepartment = (array_key_exists('sub_department', $employee)) ? $employee['sub_department'] : 'unknown';
            $normalizedEmployees[] = [
                'name' => $employee['name'],
                'spent' => $spent,
                'unspent' => ($employee['budget'] - $spent),
                'budget' => $employee['budget'],
                'percent_spent' => ($spent / $employee['budget']) * 100,
                'department' => $department,
                'sub_department' => $subdepartment,
                'manager' => $employee['manager'],
            ];
        }

        usort($normalizedEmployees, function($a, $b) {
            return $a['percent_spent'] <=> $b['percent_spent'];
        });

        array_unshift($normalizedEmployees, $headers);

        return $normalizedEmployees;
    }

    public static function getBudgetVSpentPerDepartment($budgetVSpentPerPerson) {
        $budgetVSpentPerDepartment = [];
        $headers = ['Department', 'Spent', 'Unspent', 'Budget', 'Percent Spent'];
        foreach ($budgetVSpentPerPerson as $person) {
            if (!array_key_exists('department', $person)) {
                continue;
            }
            if (!array_key_exists($person['department'], $budgetVSpentPerDepartment)) {
                $budgetVSpentPerDepartment[$person['department']] = [
                    'department' => $person['department'],
                    'spent' => 0,
                    'unspent' => 0,
                    'budget' => 0,
                    'percent_spent' => 0,
                ];
            }

            $existingBudget = $budgetVSpentPerDepartment[$person['department']]['budget'];
            $existingSpent = $budgetVSpentPerDepartment[$person['department']]['spent'];

            $budgetVSpentPerDepartment[$person['department']]['budget'] = ($existingBudget + $person['budget']);
            $budgetVSpentPerDepartment[$person['department']]['spent'] = ($existingSpent + $person['spent']);
            $budgetVSpentPerDepartment[$person['department']]['unspent'] = ($budgetVSpentPerDepartment[$person['department']]['budget'] - $budgetVSpentPerDepartment[$person['department']]['spent']);
            $budgetVSpentPerDepartment[$person['department']]['percent_spent'] = ($budgetVSpentPerDepartment[$person['department']]['spent'] / $budgetVSpentPerDepartment[$person['department']]['budget']) * 100;

        }
        ksort($budgetVSpentPerDepartment);
        usort($budgetVSpentPerDepartment, function($a, $b) {
            return $a['percent_spent'] <=> $b['percent_spent'];
        });
        array_unshift($budgetVSpentPerDepartment, $headers);

        return $budgetVSpentPerDepartment;
    }

    public static function getBudgetVSpentPerSubDepartment($budgetVSpentPerPerson) {
        $budgetVSpentPerSubDepartment = [];
        $headers = ['Sub-department', 'Spent', 'Unspent', 'Budget', 'Percent Spent'];
        foreach ($budgetVSpentPerPerson as $person) {
            if (!array_key_exists('sub_department', $person)) {
                continue;
            }
            if (!array_key_exists($person['sub_department'], $budgetVSpentPerSubDepartment)) {
                $budgetVSpentPerSubDepartment[$person['sub_department']] = [
                    'sub_department' => $person['sub_department'],
                    'spent' => 0,
                    'unspent' => 0,
                    'budget' => 0,
                    'percent_spent' => 0,
                ];
            }

            $existingBudget = $budgetVSpentPerSubDepartment[$person['sub_department']]['budget'];
            $existingSpent = $budgetVSpentPerSubDepartment[$person['sub_department']]['spent'];

            $budgetVSpentPerSubDepartment[$person['sub_department']]['budget'] = ($existingBudget + $person['budget']);
            $budgetVSpentPerSubDepartment[$person['sub_department']]['spent'] = ($existingSpent + $person['spent']);
            $budgetVSpentPerSubDepartment[$person['sub_department']]['unspent'] = ($budgetVSpentPerSubDepartment[$person['sub_department']]['budget'] - $budgetVSpentPerSubDepartment[$person['sub_department']]['spent']);
            $budgetVSpentPerSubDepartment[$person['sub_department']]['percent_spent'] = ($budgetVSpentPerSubDepartment[$person['sub_department']]['spent'] / $budgetVSpentPerSubDepartment[$person['sub_department']]['budget']) * 100;

        }
        ksort($budgetVSpentPerSubDepartment);
        usort($budgetVSpentPerSubDepartment, function($a, $b) {
            return $a['percent_spent'] <=> $b['percent_spent'];
        });
        array_unshift($budgetVSpentPerSubDepartment, $headers);

        return $budgetVSpentPerSubDepartment;
    }

    public static function getBudgetVSpentPerTeam($budgetVSpentPerPerson) {
        $budgetVSpentPerTeam = [];
        $headers = ['Team Manager', 'Spent', 'Unspent', 'Total Budget', 'Percent Spent', 'Department', 'Sub-department', 'Team Size'];
        foreach ($budgetVSpentPerPerson as $person) {
            if (!array_key_exists($person['manager'], $budgetVSpentPerTeam)) {
                $budgetVSpentPerTeam[$person['manager']] = [
                    'manager' => $person['manager'],
                    'spent' => 0,
                    'unspent' => 0,
                    'budget' => 0,
                    'percent_spent' => 0,
                    'department' => $person['department'],
                    'sub_department' => $person['sub_department'],
                    'team_size' => 0,
                ];
            }
            $existingBudget = $budgetVSpentPerTeam[$person['manager']]['budget'];
            $existingSpent = $budgetVSpentPerTeam[$person['manager']]['spent'];
            $budgetVSpentPerTeam[$person['manager']]['budget'] = ($existingBudget + $person['budget']);
            $budgetVSpentPerTeam[$person['manager']]['spent'] = ($existingSpent + $person['spent']);
            $budgetVSpentPerTeam[$person['manager']]['unspent'] = ($budgetVSpentPerTeam[$person['manager']]['budget'] - $budgetVSpentPerTeam[$person['manager']]['spent']);
            $budgetVSpentPerTeam[$person['manager']]['percent_spent'] = ($budgetVSpentPerTeam[$person['manager']]['spent'] / $budgetVSpentPerTeam[$person['manager']]['budget']) * 100;
            $budgetVSpentPerTeam[$person['manager']]['team_size']++;
        }
        ksort($budgetVSpentPerTeam);
        usort($budgetVSpentPerTeam, function($a, $b) {
            return $a['percent_spent'] <=> $b['percent_spent'];
        });
        array_unshift($budgetVSpentPerTeam, $headers);
        return $budgetVSpentPerTeam;
    }

    public static function getTotals($budgetVSpentPerPerson) {
        $totals = [
            'budget' => 0,
            'spent' => 0,
            'unspent' => 0,
        ];
        foreach ($budgetVSpentPerPerson as $person) {
            if (!array_key_exists('spent', $person)) {
                continue;
            }
            if ($person['spent'] > 200) {
                // Exclude large transactions which are outside of the allocated budget.
                continue;
            }
            $totals['budget'] = $totals['budget'] + $person['budget'];
            $totals['spent'] = $totals['spent'] + $person['spent'];
            $unspent = ($person['budget'] - $person['spent']);
            $totals['unspent'] = $totals['unspent'] + ($unspent);
        }
        $formattedTotals = [
            ['Budget', 'Spent', 'Unspent'],
            [(string) $totals['budget'], (string) $totals['spent'], (string) $totals['unspent']],
        ];
        return $formattedTotals;
    }

    public static function writeCsv($filename, $data) {
        $fp = fopen('data/' . $filename, 'w');

        foreach ($data as $fields) {
            fputcsv($fp, $fields);
        }
    }

    /**
     * Given an array keyed by dates, returns an array with any missing date keys filled.
     *
     * @param  array $array
     * @return array $array
     */
    public static function fillMissingDateArrayKeys(array $array)
    {
        if (empty($array)) {
            return [];
        }
        $formatLength = strlen(array_key_first($array));

        switch ($formatLength) {
            case 4:
                $format = 'Y';
                $step = 'year';
                break;
            case 7:
                $format = 'Y-m';
                $step = 'month';
                break;
            case 10:
                $format = 'Y-m-d';
                $step = 'day';
                break;
            default:
                throw new \InvalidArgumentException(
                    'The keys must be dates in one of the following formats: `Y`, `Y-m`, or `Y-m-d`. `'
                    . array_key_first($array)
                    . '` was provided.'
                );
        }

        $start = strtotime(array_key_first($array));
        $current = $start;
        $end = strtotime(array_key_last($array));
        $newArray = [];
        $defaultValue = (is_array(reset($array))) ? [] : 0;
        while ($current < $end) {
            $newArray[date($format, $current)] = $defaultValue;
            $current = strtotime("+1 $step", $current);
        }

        return array_merge($newArray, $array);
    }

    /**
     * Shifts each top level key of an array of arrays into the row's contained array while preserving top-level keys.
     *
     * @example
     *   Given
     *     [
     *       'a' => 'foo',
     *       'b' => 'bar',
     *     ]
     *   Returns
     *     [
     *       'a' => ['a', 'foo'],
     *       'b' => ['b', 'bar'],
     *     ]
     *
     * @param  array[] | string[] $array
     * @return array[]
     */
    public static function includeArrayKeysInArray(array $array)
    {
        $newArray = [];
        foreach ($array as $key => $row) {
            if (is_array($row)) {
                array_unshift($row, $key);
                $newArray[$key] = $row;
            } elseif (is_string($row) || is_int($row)) {
                $newArray[$key] = [$key, $row];
            } else {
                throw new \InvalidArgumentException('Expected each row in the array to be an array, string, or int.');
            }
        }

        return $newArray;
    }
}