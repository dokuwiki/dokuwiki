<?php
/**
 * Password Hashing Class
 *
 * This class implements various mechanisms used to hash passwords
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 * @license LGPL2
 */
class PassHash {
    /**
     * Verifies a cleartext password against a crypted hash
     *
     * The method and salt used for the crypted hash is determined automatically,
     * then the clear text password is crypted using the same method. If both hashs
     * match true is is returned else false
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * @param $clear string Clear-Text password
     * @param $hash  string Hash to compare against
     * @return  bool
     */
    function verify_hash($clear, $hash) {
        $method = '';
        $salt   = '';
        $magic  = '';

        //determine the used method and salt
        $len = strlen($hash);
        if(preg_match('/^\$1\$([^\$]{0,8})\$/', $hash, $m)) {
            $method = 'smd5';
            $salt   = $m[1];
            $magic  = '1';
        } elseif(preg_match('/^\$apr1\$([^\$]{0,8})\$/', $hash, $m)) {
            $method = 'apr1';
            $salt   = $m[1];
            $magic  = 'apr1';
        } elseif(preg_match('/^\$P\$(.{31})$/', $hash, $m)) {
            $method = 'pmd5';
            $salt   = $m[1];
            $magic  = 'P';
        } elseif(preg_match('/^\$H\$(.{31})$/', $hash, $m)) {
            $method = 'pmd5';
            $salt   = $m[1];
            $magic  = 'H';
        } elseif(preg_match('/^sha1\$(.{5})\$/', $hash, $m)) {
            $method = 'djangosha1';
            $salt   = $m[1];
        } elseif(preg_match('/^md5\$(.{5})\$/', $hash, $m)) {
            $method = 'djangomd5';
            $salt   = $m[1];
        } elseif(preg_match('/^\$2a\$(.{2})\$/', $hash, $m)) {
            $method = 'bcrypt';
            $salt   = $hash;
        } elseif(substr($hash, 0, 6) == '{SSHA}') {
            $method = 'ssha';
            $salt   = substr(base64_decode(substr($hash, 6)), 20);
        } elseif(substr($hash, 0, 6) == '{SMD5}') {
            $method = 'lsmd5';
            $salt   = substr(base64_decode(substr($hash, 6)), 16);
        } elseif(preg_match('/^:B:(.+?):.{32}$/', $hash, $m)) {
            $method = 'mediawiki';
            $salt   = $m[1];
        } elseif(preg_match('/^\$6\$(.+?)\$/', $hash, $m)) {
            $method = 'sha512';
            $salt   = $m[1];
        } elseif($len == 32) {
            $method = 'md5';
        } elseif($len == 40) {
            $method = 'sha1';
        } elseif($len == 16) {
            $method = 'mysql';
        } elseif($len == 41 && $hash[0] == '*') {
            $method = 'my411';
        } elseif($len == 34) {
            $method = 'kmd5';
            $salt   = $hash;
        } else {
            $method = 'crypt';
            $salt   = substr($hash, 0, 2);
        }

        //crypt and compare
        $call = 'hash_'.$method;
        if($this->$call($clear, $salt, $magic) === $hash) {
            return true;
        }
        return false;
    }

    /**
     * Create a random salt
     *
     * @param int $len The length of the salt
     * @return string
     */
    public function gen_salt($len = 32) {
        $salt  = '';
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        for($i = 0; $i < $len; $i++) {
            $salt .= $chars[$this->random(0, 61)];
        }
        return $salt;
    }

    /**
     * Initialize the passed variable with a salt if needed.
     *
     * If $salt is not null, the value is kept, but the lenght restriction is
     * applied (unless, $cut is false).
     *
     * @param string &$salt The salt, pass null if you want one generated
     * @param int    $len   The length of the salt
     * @param bool   $cut   Apply length restriction to existing salt?
     */
    public function init_salt(&$salt, $len = 32, $cut = true) {
        if(is_null($salt)) {
            $salt = $this->gen_salt($len);
            $cut  = true; // for new hashes we alway apply length restriction
        }
        if(strlen($salt) > $len && $cut) $salt = substr($salt, 0, $len);
    }

    // Password hashing methods follow below

    /**
     * Password hashing method 'smd5'
     *
     * Uses salted MD5 hashs. Salt is 8 bytes long.
     *
     * The same mechanism is used by Apache's 'apr1' method. This will
     * fallback to a implementation in pure PHP if MD5 support is not
     * available in crypt()
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author <mikey_nich at hotmail dot com>
     * @link   http://de.php.net/manual/en/function.crypt.php#73619
     * @param string $clear The clear text to hash
     * @param string $salt  The salt to use, null for random
     * @return string Hashed password
     */
    public function hash_smd5($clear, $salt = null) {
        $this->init_salt($salt, 8);

        if(defined('CRYPT_MD5') && CRYPT_MD5 && $salt !== '') {
            return crypt($clear, '$1$'.$salt.'$');
        } else {
            // Fall back to PHP-only implementation
            return $this->hash_apr1($clear, $salt, '1');
        }
    }

    /**
     * Password hashing method 'lsmd5'
     *
     * Uses salted MD5 hashs. Salt is 8 bytes long.
     *
     * This is the format used by LDAP.
     *
     * @param string $clear The clear text to hash
     * @param string $salt  The salt to use, null for random
     * @return string Hashed password
     */
    public function hash_lsmd5($clear, $salt = null) {
        $this->init_salt($salt, 8);
        return "{SMD5}".base64_encode(md5($clear.$salt, true).$salt);
    }

    /**
     * Password hashing method 'apr1'
     *
     * Uses salted MD5 hashs. Salt is 8 bytes long.
     *
     * This is basically the same as smd1 above, but as used by Apache.
     *
     * @author <mikey_nich at hotmail dot com>
     * @link   http://de.php.net/manual/en/function.crypt.php#73619
     * @param string $clear The clear text to hash
     * @param string $salt  The salt to use, null for random
     * @param string $magic The hash identifier (apr1 or 1)
     * @return string Hashed password
     */
    public function hash_apr1($clear, $salt = null, $magic = 'apr1') {
        $this->init_salt($salt, 8);

        $len  = strlen($clear);
        $text = $clear.'$'.$magic.'$'.$salt;
        $bin  = pack("H32", md5($clear.$salt.$clear));
        for($i = $len; $i > 0; $i -= 16) {
            $text .= substr($bin, 0, min(16, $i));
        }
        for($i = $len; $i > 0; $i >>= 1) {
            $text .= ($i & 1) ? chr(0) : $clear{0};
        }
        $bin = pack("H32", md5($text));
        for($i = 0; $i < 1000; $i++) {
            $new = ($i & 1) ? $clear : $bin;
            if($i % 3) $new .= $salt;
            if($i % 7) $new .= $clear;
            $new .= ($i & 1) ? $bin : $clear;
            $bin = pack("H32", md5($new));
        }
        $tmp = '';
        for($i = 0; $i < 5; $i++) {
            $k = $i + 6;
            $j = $i + 12;
            if($j == 16) $j = 5;
            $tmp = $bin[$i].$bin[$k].$bin[$j].$tmp;
        }
        $tmp = chr(0).chr(0).$bin[11].$tmp;
        $tmp = strtr(
            strrev(substr(base64_encode($tmp), 2)),
            "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",
            "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz"
        );
        return '$'.$magic.'$'.$salt.'$'.$tmp;
    }

    /**
     * Password hashing method 'md5'
     *
     * Uses MD5 hashs.
     *
     * @param string $clear The clear text to hash
     * @return string Hashed password
     */
    public function hash_md5($clear) {
        return md5($clear);
    }

    /**
     * Password hashing method 'sha1'
     *
     * Uses SHA1 hashs.
     *
     * @param string $clear The clear text to hash
     * @return string Hashed password
     */
    public function hash_sha1($clear) {
        return sha1($clear);
    }

    /**
     * Password hashing method 'ssha' as used by LDAP
     *
     * Uses salted SHA1 hashs. Salt is 4 bytes long.
     *
     * @param string $clear The clear text to hash
     * @param string $salt  The salt to use, null for random
     * @return string Hashed password
     */
    public function hash_ssha($clear, $salt = null) {
        $this->init_salt($salt, 4);
        return '{SSHA}'.base64_encode(pack("H*", sha1($clear.$salt)).$salt);
    }

    /**
     * Password hashing method 'crypt'
     *
     * Uses salted crypt hashs. Salt is 2 bytes long.
     *
     * @param string $clear The clear text to hash
     * @param string $salt  The salt to use, null for random
     * @return string Hashed password
     */
    public function hash_crypt($clear, $salt = null) {
        $this->init_salt($salt, 2);
        return crypt($clear, $salt);
    }

    /**
     * Password hashing method 'mysql'
     *
     * This method was used by old MySQL systems
     *
     * @link   http://www.php.net/mysql
     * @author <soren at byu dot edu>
     * @param string $clear The clear text to hash
     * @return string Hashed password
     */
    public function hash_mysql($clear) {
        $nr      = 0x50305735;
        $nr2     = 0x12345671;
        $add     = 7;
        $charArr = preg_split("//", $clear);
        foreach($charArr as $char) {
            if(($char == '') || ($char == ' ') || ($char == '\t')) continue;
            $charVal = ord($char);
            $nr ^= ((($nr & 63) + $add) * $charVal) + ($nr << 8);
            $nr2 += ($nr2 << 8) ^ $nr;
            $add += $charVal;
        }
        return sprintf("%08x%08x", ($nr & 0x7fffffff), ($nr2 & 0x7fffffff));
    }

    /**
     * Password hashing method 'my411'
     *
     * Uses SHA1 hashs. This method is used by MySQL 4.11 and above
     *
     * @param string $clear The clear text to hash
     * @return string Hashed password
     */
    public function hash_my411($clear) {
        return '*'.sha1(pack("H*", sha1($clear)));
    }

    /**
     * Password hashing method 'kmd5'
     *
     * Uses salted MD5 hashs.
     *
     * Salt is 2 bytes long, but stored at position 16, so you need to pass at
     * least 18 bytes. You can pass the crypted hash as salt.
     *
     * @param string $clear The clear text to hash
     * @param string $salt  The salt to use, null for random
     * @return string Hashed password
     */
    public function hash_kmd5($clear, $salt = null) {
        $this->init_salt($salt);

        $key   = substr($salt, 16, 2);
        $hash1 = strtolower(md5($key.md5($clear)));
        $hash2 = substr($hash1, 0, 16).$key.substr($hash1, 16);
        return $hash2;
    }

    /**
     * Password hashing method 'pmd5'
     *
     * Uses salted MD5 hashs. Salt is 1+8 bytes long, 1st byte is the
     * iteration count when given, for null salts $compute is used.
     *
     * The actual iteration count is the given count squared, maximum is
     * 30 (-> 1073741824). If a higher one is given, the function throws
     * an exception.
     *
     * @link  http://www.openwall.com/phpass/
     * @param string $clear   The clear text to hash
     * @param string $salt    The salt to use, null for random
     * @param string $magic   The hash identifier (P or H)
     * @param int    $compute The iteration count for new passwords
     * @throws Exception
     * @return string Hashed password
     */
    public function hash_pmd5($clear, $salt = null, $magic = 'P', $compute = 8) {
        $itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        if(is_null($salt)) {
            $this->init_salt($salt);
            $salt = $itoa64[$compute].$salt; // prefix iteration count
        }
        $iterc = $salt[0]; // pos 0 of salt is iteration count
        $iter  = strpos($itoa64, $iterc);

        if($iter > 30) {
            throw new Exception("Too high iteration count ($iter) in ".
                                    __CLASS__.'::'.__FUNCTION__);
        }

        $iter = 1 << $iter;
        $salt = substr($salt, 1, 8);

        // iterate
        $hash = md5($salt.$clear, true);
        do {
            $hash = md5($hash.$clear, true);
        } while(--$iter);

        // encode
        $output = '';
        $count  = 16;
        $i      = 0;
        do {
            $value = ord($hash[$i++]);
            $output .= $itoa64[$value & 0x3f];
            if($i < $count)
                $value |= ord($hash[$i]) << 8;
            $output .= $itoa64[($value >> 6) & 0x3f];
            if($i++ >= $count)
                break;
            if($i < $count)
                $value |= ord($hash[$i]) << 16;
            $output .= $itoa64[($value >> 12) & 0x3f];
            if($i++ >= $count)
                break;
            $output .= $itoa64[($value >> 18) & 0x3f];
        } while($i < $count);

        return '$'.$magic.'$'.$iterc.$salt.$output;
    }

    /**
     * Alias for hash_pmd5
     */
    public function hash_hmd5($clear, $salt = null, $magic = 'H', $compute = 8) {
        return $this->hash_pmd5($clear, $salt, $magic, $compute);
    }

    /**
     * Password hashing method 'djangosha1'
     *
     * Uses salted SHA1 hashs. Salt is 5 bytes long.
     * This is used by the Django Python framework
     *
     * @link http://docs.djangoproject.com/en/dev/topics/auth/#passwords
     * @param string $clear The clear text to hash
     * @param string $salt  The salt to use, null for random
     * @return string Hashed password
     */
    public function hash_djangosha1($clear, $salt = null) {
        $this->init_salt($salt, 5);
        return 'sha1$'.$salt.'$'.sha1($salt.$clear);
    }

    /**
     * Password hashing method 'djangomd5'
     *
     * Uses salted MD5 hashs. Salt is 5 bytes long.
     * This is used by the Django Python framework
     *
     * @link http://docs.djangoproject.com/en/dev/topics/auth/#passwords
     * @param string $clear The clear text to hash
     * @param string $salt  The salt to use, null for random
     * @return string Hashed password
     */
    public function hash_djangomd5($clear, $salt = null) {
        $this->init_salt($salt, 5);
        return 'md5$'.$salt.'$'.md5($salt.$clear);
    }

    /**
     * Passwordhashing method 'bcrypt'
     *
     * Uses a modified blowfish algorithm called eksblowfish
     * This method works on PHP 5.3+ only and will throw an exception
     * if the needed crypt support isn't available
     *
     * A full hash should be given as salt (starting with $a2$) or this
     * will break. When no salt is given, the iteration count can be set
     * through the $compute variable.
     *
     * @param string $clear   The clear text to hash
     * @param string $salt    The salt to use, null for random
     * @param int    $compute The iteration count (between 4 and 31)
     * @throws Exception
     * @return string Hashed password
     */
    public function hash_bcrypt($clear, $salt = null, $compute = 8) {
        if(!defined('CRYPT_BLOWFISH') || CRYPT_BLOWFISH != 1) {
            throw new Exception('This PHP installation has no bcrypt support');
        }

        if(is_null($salt)) {
            if($compute < 4 || $compute > 31) $compute = 8;
            $salt = '$2a$'.str_pad($compute, 2, '0', STR_PAD_LEFT).'$'.
                $this->gen_salt(22);
        }

        return crypt($clear, $salt);
    }

    /**
     * Password hashing method SHA512
     *
     * This is only supported on PHP 5.3.2 or higher and will throw an exception if
     * the needed crypt support is not available
     *
     * @param string $clear The clear text to hash
     * @param string $salt  The salt to use, null for random
     * @return string Hashed password
     * @throws Exception
     */
    public function hash_sha512($clear, $salt = null) {
        if(!defined('CRYPT_SHA512') || CRYPT_SHA512 != 1) {
            throw new Exception('This PHP installation has no SHA512 support');
        }
        $this->init_salt($salt, 8, false);
        return crypt($clear, '$6$'.$salt.'$');
    }

    /**
     * Password hashing method 'mediawiki'
     *
     * Uses salted MD5, this is referred to as Method B in MediaWiki docs. Unsalted md5
     * method 'A' is not supported.
     *
     * @link  http://www.mediawiki.org/wiki/Manual_talk:User_table#user_password_column
     * @param string $clear The clear text to hash
     * @param string $salt  The salt to use, null for random
     * @return string Hashed password
     */
    public function hash_mediawiki($clear, $salt = null) {
        $this->init_salt($salt, 8, false);
        return ':B:'.$salt.':'.md5($salt.'-'.md5($clear));
    }

    /**
     * Wraps around native hash_hmac() or reimplents it
     *
     * This is not directly used as password hashing method, and thus isn't callable via the
     * verify_hash() method. It should be used to create signatures and might be used in other
     * password hashing methods.
     *
     * @see hash_hmac()
     * @author KC Cloyd
     * @link http://www.php.net/manual/en/function.hash-hmac.php#93440
     *
     * @param string $algo Name of selected hashing algorithm (i.e. "md5", "sha256", "haval160,4",
     *                     etc..) See hash_algos() for a list of supported algorithms.
     * @param string $data Message to be hashed.
     * @param string $key  Shared secret key used for generating the HMAC variant of the message digest.
     * @param bool $raw_output When set to TRUE, outputs raw binary data. FALSE outputs lowercase hexits.
     *
     * @return string
     */
    public static function hmac($algo, $data, $key, $raw_output = false) {
        // use native function if available and not in unit test
        if(function_exists('hash_hmac') && !defined('SIMPLE_TEST')){
            return hash_hmac($algo, $data, $key, $raw_output);
        }

        $algo = strtolower($algo);
        $pack = 'H' . strlen($algo('test'));
        $size = 64;
        $opad = str_repeat(chr(0x5C), $size);
        $ipad = str_repeat(chr(0x36), $size);

        if(strlen($key) > $size) {
            $key = str_pad(pack($pack, $algo($key)), $size, chr(0x00));
        } else {
            $key = str_pad($key, $size, chr(0x00));
        }

        for($i = 0; $i < strlen($key) - 1; $i++) {
            $opad[$i] = $opad[$i] ^ $key[$i];
            $ipad[$i] = $ipad[$i] ^ $key[$i];
        }

        $output = $algo($opad . pack($pack, $algo($ipad . $data)));

        return ($raw_output) ? pack($pack, $output) : $output;
    }

    /**
     * Use DokuWiki's secure random generator if available
     *
     * @param $min
     * @param $max
     *
     * @return int
     */
    protected function random($min, $max){
        if(function_exists('auth_random')){
            return auth_random($min, $max);
        }else{
            return mt_rand($min, $max);
        }
    }
}
