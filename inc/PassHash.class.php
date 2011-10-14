<?php
/**
 * Password Hashing Class
 *
 * This class implements various mechanisms used to hash passwords
 *
 * @author Andreas Gohr <andi@splitbrain.org>
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
     * @return  bool
     */
    function verify_hash($clear,$hash){
        $method='';
        $salt='';
        $magic='';

        //determine the used method and salt
        $len = strlen($hash);
        if(preg_match('/^\$1\$([^\$]{0,8})\$/',$hash,$m)){
            $method = 'smd5';
            $salt   = $m[1];
            $magic  = '1';
        }elseif(preg_match('/^\$apr1\$([^\$]{0,8})\$/',$hash,$m)){
            $method = 'apr1';
            $salt   = $m[1];
            $magic  = 'apr1';
        }elseif(preg_match('/^\$P\$(.{31})$/',$hash,$m)){
            $method = 'pmd5';
            $salt   = $m[1];
            $magic  = 'P';
        }elseif(preg_match('/^\$H\$(.{31})$/',$hash,$m)){
            $method = 'pmd5';
            $salt   = $m[1];
            $magic  = 'H';
        }elseif(preg_match('/^sha1\$(.{5})\$/',$hash,$m)){
            $method = 'djangosha1';
            $salt   = $m[1];
        }elseif(preg_match('/^md5\$(.{5})\$/',$hash,$m)){
            $method = 'djangomd5';
            $salt   = $m[1];
        }elseif(substr($hash,0,6) == '{SSHA}'){
            $method = 'ssha';
            $salt   = substr(base64_decode(substr($hash, 6)),20);
        }elseif($len == 32){
            $method = 'md5';
        }elseif($len == 40){
            $method = 'sha1';
        }elseif($len == 16){
            $method = 'mysql';
        }elseif($len == 41 && $hash[0] == '*'){
            $method = 'my411';
        }elseif($len == 34){
            $method = 'kmd5';
            $salt   = $hash;
        }else{
            $method = 'crypt';
            $salt   = substr($hash,0,2);
        }

        //crypt and compare
        $call = 'hash_'.$method;
        if($this->$call($clear,$salt,$magic) === $hash){
            return true;
        }
        return false;
    }

    /**
     * Create a random salt
     *
     * @param int $len - The length of the salt
     */
    public function gen_salt($len=32){
        $salt  = '';
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        for($i=0;$i<$len;$i++) $salt .= $chars[mt_rand(0,61)];
        return $salt;
    }

    /**
     * Initialize the passed variable with a salt if needed.
     *
     * If $salt is not null, the value is kept, but the lenght restriction is
     * applied.
     *
     * @param stringref $salt - The salt, pass null if you want one generated
     * @param int $len - The length of the salt
     */
    public function init_salt(&$salt,$len=32){
        if(is_null($salt)) $salt = $this->gen_salt($len);
        if(strlen($salt) > $len) $salt = substr($salt,0,$len);
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
     * @param string $clear - the clear text to hash
     * @param string $salt  - the salt to use, null for random
     * @param string $magic - the hash identifier (apr1 or 1)
     * @returns string - hashed password
     */
    public function hash_smd5($clear, $salt=null){
        $this->init_salt($salt,8);

        if(defined('CRYPT_MD5') && CRYPT_MD5){
            return crypt($clear,'$1$'.$salt.'$');
        }else{
            // Fall back to PHP-only implementation
            return $this->hash_apr1($clear, $salt, '1');
        }
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
     * @param string $clear - the clear text to hash
     * @param string $salt  - the salt to use, null for random
     * @param string $magic - the hash identifier (apr1 or 1)
     * @returns string - hashed password
     */
    public function hash_apr1($clear, $salt=null, $magic='apr1'){
        $this->init_salt($salt,8);

        $len = strlen($clear);
        $text = $clear.'$'.$magic.'$'.$salt;
        $bin = pack("H32", md5($clear.$salt.$clear));
        for($i = $len; $i > 0; $i -= 16) {
            $text .= substr($bin, 0, min(16, $i));
        }
        for($i = $len; $i > 0; $i >>= 1) {
            $text .= ($i & 1) ? chr(0) : $clear{0};
        }
        $bin = pack("H32", md5($text));
        for($i = 0; $i < 1000; $i++) {
            $new = ($i & 1) ? $clear : $bin;
            if ($i % 3) $new .= $salt;
            if ($i % 7) $new .= $clear;
            $new .= ($i & 1) ? $bin : $clear;
            $bin = pack("H32", md5($new));
        }
        $tmp = '';
        for ($i = 0; $i < 5; $i++) {
            $k = $i + 6;
            $j = $i + 12;
            if ($j == 16) $j = 5;
            $tmp = $bin[$i].$bin[$k].$bin[$j].$tmp;
        }
        $tmp = chr(0).chr(0).$bin[11].$tmp;
        $tmp = strtr(strrev(substr(base64_encode($tmp), 2)),
                "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",
                "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz");
        return '$'.$magic.'$'.$salt.'$'.$tmp;
    }

    /**
     * Password hashing method 'md5'
     *
     * Uses MD5 hashs.
     *
     * @param string $clear - the clear text to hash
     * @returns string - hashed password
     */
    public function hash_md5($clear){
        return md5($clear);
    }

    /**
     * Password hashing method 'sha1'
     *
     * Uses SHA1 hashs.
     *
     * @param string $clear - the clear text to hash
     * @returns string - hashed password
     */
    public function hash_sha1($clear){
        return sha1($clear);
    }

    /**
     * Password hashing method 'ssha' as used by LDAP
     *
     * Uses salted SHA1 hashs. Salt is 4 bytes long.
     *
     * @param string $clear - the clear text to hash
     * @param string $salt  - the salt to use, null for random
     * @returns string - hashed password
     */
    public function hash_ssha($clear, $salt=null){
        $this->init_salt($salt,4);
        return '{SSHA}'.base64_encode(pack("H*", sha1($clear.$salt)).$salt);
    }

    /**
     * Password hashing method 'crypt'
     *
     * Uses salted crypt hashs. Salt is 2 bytes long.
     *
     * @param string $clear - the clear text to hash
     * @param string $salt  - the salt to use, null for random
     * @returns string - hashed password
     */
    public function hash_crypt($clear, $salt=null){
        $this->init_salt($salt,2);
        return crypt($clear,$salt);
    }

    /**
     * Password hashing method 'mysql'
     *
     * This method was used by old MySQL systems
     *
     * @link http://www.php.net/mysql
     * @author <soren at byu dot edu>
     * @param string $clear - the clear text to hash
     * @returns string - hashed password
     */
    public function hash_mysql($clear){
        $nr=0x50305735;
        $nr2=0x12345671;
        $add=7;
        $charArr = preg_split("//", $clear);
        foreach ($charArr as $char) {
            if (($char == '') || ($char == ' ') || ($char == '\t')) continue;
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
     * @param string $clear - the clear text to hash
     * @returns string - hashed password
     */
    public function hash_my411($clear){
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
     * @param string $clear - the clear text to hash
     * @param string $salt  - the salt to use, null for random
     * @returns string - hashed password
     */
    public function hash_kmd5($clear, $salt=null){
        $this->init_salt($salt);

        $key = substr($salt, 16, 2);
        $hash1 = strtolower(md5($key . md5($clear)));
        $hash2 = substr($hash1, 0, 16) . $key . substr($hash1, 16);
        return $hash2;
    }

    /**
     * Password hashing method 'pmd5'
     *
     * Uses salted MD5 hashs. Salt is 1+8 bytes long, 1st byte is the
     * iteration count when given, for null salts $compute is used.
     *
     * @param string $clear - the clear text to hash
     * @param string $salt  - the salt to use, null for random
     * @param string $magic - the hash identifier (P or H)
     * @param int  $compute - the iteration count for new passwords
     * @returns string - hashed password
     */
    public function hash_pmd5($clear, $salt=null, $magic='P',$compute=8){
        $itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        if(is_null($salt)){
            $this->init_salt($salt);
            $salt = $itoa64[$compute].$salt; // prefix iteration count
        }
        $iterc = $salt[0]; // pos 0 of salt is iteration count
        $iter = strpos($itoa64,$iterc);
        $iter = 1 << $iter;
        $salt = substr($salt,1,8);

        // iterate
        $hash = md5($salt . $clear, true);
        do {
            $hash = md5($hash . $clear, true);
        } while (--$iter);

        // encode
        $output = '';
        $count = 16;
        $i = 0;
        do {
            $value = ord($hash[$i++]);
            $output .= $itoa64[$value & 0x3f];
            if ($i < $count)
                $value |= ord($hash[$i]) << 8;
            $output .= $itoa64[($value >> 6) & 0x3f];
            if ($i++ >= $count)
                break;
            if ($i < $count)
                $value |= ord($hash[$i]) << 16;
            $output .= $itoa64[($value >> 12) & 0x3f];
            if ($i++ >= $count)
                break;
            $output .= $itoa64[($value >> 18) & 0x3f];
        } while ($i < $count);

        return '$'.$magic.'$'.$iterc.$salt.$output;
    }

    /**
     * Alias for hash_pmd5
     */
    public function hash_hmd5($clear, $salt=null, $magic='H', $compute=8){
        return $this->hash_pmd5($clear, $salt, $magic, $compute);
    }

    /**
     * Password hashing method 'djangosha1'
     *
     * Uses salted SHA1 hashs. Salt is 5 bytes long.
     * This is used by the Django Python framework
     *
     * @link http://docs.djangoproject.com/en/dev/topics/auth/#passwords
     * @param string $clear - the clear text to hash
     * @param string $salt  - the salt to use, null for random
     * @returns string - hashed password
     */
    public function hash_djangosha1($clear, $salt=null){
        $this->init_salt($salt,5);
        return 'sha1$'.$salt.'$'.sha1($salt.$clear);
    }

    /**
     * Password hashing method 'djangomd5'
     *
     * Uses salted MD5 hashs. Salt is 5 bytes long.
     * This is used by the Django Python framework
     *
     * @link http://docs.djangoproject.com/en/dev/topics/auth/#passwords
     * @param string $clear - the clear text to hash
     * @param string $salt  - the salt to use, null for random
     * @returns string - hashed password
     */
    public function hash_djangomd5($clear, $salt=null){
        $this->init_salt($salt,5);
        return 'md5$'.$salt.'$'.md5($salt.$clear);
    }

}
