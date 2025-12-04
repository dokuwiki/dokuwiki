<?php

/**
 * DokuWiki 32bit shims for IP address functions.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

namespace dokuwiki;

class Ip32
{
    public static $b32 = '4294967296';
    /**
     * slow and ugly bitwise_and for 32bit arch
     * @param $u64 unsigned 64bit integer as string
     *            likely from ipv6UpperLowerOn32
     * @param $pow 0-64 power of 2 for bitmask
     */
    public static function bitmask64On32(string $u64, int $pow) : string {
        $upper = bcdiv($u64, Ip32::$b32, 0);
        $lower = bcmod($u64, Ip32::$b32);
        // str of len 32 all 0s and 1s
        $upper_bin = Ip32::decimalToBinary32($upper);
        $lower_bin = Ip32::decimalToBinary32($lower);
        $bin = $upper_bin . $lower_bin;

        $mask = Ip32::makeBitmaskOn32(64-$pow);

        // most right is lowest bit
        $res='0';
        for ($i=0; $i<64; $i++){
            if (bcmul($bin[$i], $mask[$i]) == 1) {
                $res = bcadd($res, bcpow(2, 63-$i));
            }
        }
        return $res;
    }


    /**
     * modeling bitshift like  ~0 << $pow for 32-bit arch
     * @param pow power of 2 for mask
     * @return 64-char string of 1 and 0s
     * pow=1
     * 1111111111111111111111111111111111111111111111111111111111111110
     * pow=63
     * 1000000000000000000000000000000000000000000000000000000000000000
     * pow=64
     * 0000000000000000000000000000000000000000000000000000000000000000
     */
    private static function makeBitmaskOn32(int $pow) : string {
        $pow = $pow < 0 ? 64 - $pow : $pow;
        $mask = sprintf("%064d",0);
        for ($i=0; $i<64; $i++) {
            if ($i >= $pow) {
                $mask[63 - $i] = '1';
            }
        }
        return $mask;
    }

    /**
     * conversion of inet_pton ipv6 into 64-bit upper and lower
     * bcmath version for 32-bit architecture
     * w/o no unpack('J') - unsigned long long (always 64 bit, big endian byte order)
     *
     * results match unpack('Jupper/Jlower', $binary)
     *
     * @param string $binary inet_pton's ipv6 16 element binary
     *
     * @return string[] upper 64 and lower 64 for ipToNumber
     */
    public static function ipv6UpperLowerOn32(string $binary) {
       // unpack into four 32-bit unsigned ints to recombine as 2 64-bit
       $parts = unpack('N4', $binary);
       $upper = Ip32::partsTo64($parts[1], $parts[2]);
       $lower = Ip32::partsTo64($parts[3], $parts[4]);
       return ['upper' => $upper, 'lower' => $lower];
    }

    private static function partsTo64(string $high, string $low) : string {
        // signed to unsigned
        $high = $high<0 ? bcadd($high, Ip32::$b32) : $high;
        $low  = $low <0 ? bcadd($low , Ip32::$b32) : $low;

        return bcadd(bcmul($high, Ip32::$b32), $low);
    }

    /**
     * Convert a decimal number to 32-bit binary string using bcmath
     * Handles large numbers that exceed PHP_INT_MAX on 32-bit systems
     *
     * @param string $decimal The decimal number as string
     * @return string 32-bit binary representation
     */
    private static function decimalToBinary32(string $decimal) : string {
        if (bccomp($decimal, '0') == 0) {
            return str_repeat('0', 32);
        }
        $binary = '';
        $num = $decimal;
        for ($i = 31; $i >= 0; $i--) {
            $power = bcpow('2', (string)$i);
            if (bccomp($num, $power) >= 0) {
                $binary .= '1';
                $num = bcsub($num, $power);
            } else {
                $binary .= '0';
            }
        }
        return $binary;
    }
 }
