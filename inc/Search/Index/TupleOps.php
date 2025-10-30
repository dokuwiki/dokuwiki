<?php

namespace dokuwiki\Search\Index;

/**
 * Provides operations on tuple records used in our indexes
 *
 * Tuples consist of a key (typically a RID from another Index) and a number (usually a count).
 * Used to store page <-> word counts for example
 */
class TupleOps
{
    /**
     * Insert or replace a tuple in a line
     *
     * @param string $record This is the current row value to be modified
     * @param int|string $key The foreign rid or identifier
     * @param int $count The count to store
     * @return string A new row value
     * @author Tom N Harris <tnharris@whoopdedo.org>
     *
     */
    public static function updateTuple($record, $key, $count)
    {
        if ($record != '') {
            // remove any current version of the tuple (with or without explicit count)
            $record = preg_replace('/(^|:)' . preg_quote($key, '/') . '(\*\d+)?/', '', $record);
        }
        $record = trim($record, ':');
        if ($count) {
            // Write tuples with frequency=1 without the asterisk
            $tuple = ($count == 1) ? $key : "{$key}*{$count}";
            if ($record !== '') {
                return "{$tuple}:" . $record;
            } else {
                return $tuple;
            }
        }
        return $record;
    }

    /**
     * Sum the counts in a list of tuples
     *
     * Tuples can be in format "key*count" or just "key" (implicit count of 1)
     *
     * @param string $record The row value to parse
     * @return int sum of all counts
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    public static function aggregateTupleCounts($record)
    {
        $freq = 0;
        $parts = explode(':', $record);
        foreach ($parts as $tuple) {
            if ($tuple === '') continue;
            if (strpos($tuple, '*') !== false) {
                [/* $key */, $cnt] = explode('*', $tuple);
                $freq += (int)$cnt;
            } else {
                // No explicit count means count of 1
                $freq += 1;
            }
        }
        return $freq;
    }

    /**
     * Split a line into an array of tuples
     *
     * The given key of the given $filtermap defines which tuples to extract, the value
     * gives the name in the output array. This basically allows to map RIDs to their
     * respective real values. The result will contain the counts associated with the
     * mapped keys.
     *
     * If no $filtermap is given (null), all tuples are returned keeping their original keys
     *
     * Tuples can be in format "key*count" or just "key" (implicit count of 1)
     *
     * @param string $record The row value to parse
     * @param array|null $filtermap Associative array of ($key => $mapping), null for all tuples
     * @return array mapped counts
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    public static function parseTuples($record, $filtermap = null)
    {
        $result = array();
        if ($record == '') return $result;
        $parts = explode(':', $record);
        foreach ($parts as $tuple) {
            if ($tuple === '') continue;

            // Handle both "key*count" and "key" formats
            if (strpos($tuple, '*') !== false) {
                [$key, $cnt] = explode('*', $tuple);
                if (!$cnt) continue;
            } else {
                // No explicit count means count of 1
                $key = $tuple;
                $cnt = 1;
            }

            if (is_array($filtermap)) {
                if (!isset($filtermap[$key])) continue;
                $mapped = $filtermap[$key];
            } else {
                $mapped = $key;
            }
            $result[$mapped] = $cnt;
        }
        return $result;
    }
}
