<?php

/**
 * DokuWiki 32bit shims for IP address functions.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

namespace dokuwiki;

class Ip32
{
    /**
     * slow and ugly bitwise_and for 32bit arch
     * @param $u64 unsigned 64bit integer as string
     *            likely from ipv6_upper_lower_32
     * @param $pow 0-64 power of 2 for bitmask
     */
    public static function bitmask64_32(string $u64, int $pow) : string {
        //$u64 = sprintf("%.0f", $u65);
        $b32 = '4294967296';
        $bin = sprintf("%032b%032b",
                bcdiv($u64, $b32, 0),
                bcmod($u64, $b32));

        $mask = Ip32::make_bitmask_32(64-$pow);

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
    private static function make_bitmask_32(int $pow) : string {
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
     * @return int[] upper 64 and lower 64 for ipToNumber
     */
    public static function ipv6_upper_lower_32(string $binary) {
       // unpack into four 32-bit unsigned ints to recombine as 2 64-bit
       $b32 = 4294967296; // bcpow(2, 32)
       $parts = unpack('N4', $binary);
       $upper = bcadd(bcmul($parts[1], $b32),
                      $parts[2]);
       $lower = bcadd(bcmul($parts[3], $b32),
                      $parts[4]);
       return ['upper' => $upper, 'lower' => $lower];
    }
 }
