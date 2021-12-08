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
            // remove any current version of the tuple
            $record = preg_replace('/(^|:)' . preg_quote($key, '/') . '\*\d*/', '', $record);
        }
        $record = trim($record, ':');
        if ($count) {
            if ($record) {
                return "{$key}*{$count}:" . $record;
            } else {
                return "{$key}*{$count}";
            }
        }
        return $record;
    }

    /**
     * Sum the counts in a list of tuples
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
            list(/* $key */, $cnt) = explode('*', $tuple);
            $freq += (int)$cnt;
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
     * @param string $record The row value to parse
     * @param array $filtermap Associative array of ($key => $mapping)
     * @return array mapped counts
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    public static function parseTuples($record, $filtermap)
    {
        $result = array();
        if ($record == '') return $result;
        $parts = explode(':', $record);
        foreach ($parts as $tuple) {
            if ($tuple === '') continue;
            list($key, $cnt) = explode('*', $tuple);
            if (!$cnt) continue;
            if (empty($filtermap[$key])) continue;
            $mapped = $filtermap[$key];
            $result[$mapped] = $cnt;
        }
        return $result;
    }
}
