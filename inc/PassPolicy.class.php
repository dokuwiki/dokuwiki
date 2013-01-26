<?php
/**
 * Check password policies and generate random passwords accordingly
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */
class PassPolicy {
    /** @var int number of character pools that have to be used at least */
    public $min_pools = 1;

    /** @var int minimum length of the password (bytes) */
    public $min_length = 6;

    /** @var bool try to generate a pronouncable password when calling generatePassword? */
    public $pronouncable = true;

    /** @var array allowed character pools */
    public $usepools = array(
        'lower'   => true,
        'upper'   => false,
        'numeric' => true,
        'special' => false
    );

    /** @var int number of consecutive letters that may not be in the username, 0 to disable */
    public $usernamecheck = 0;

    /** @var int policy violation error */
    public $error = 0;


    const LENGTH_VIOLATION = 1;
    const POOL_VIOLATION = 2;
    const USERNAME_VIOLATION = 4;

    /**
     * Constructor
     *
     * Sets the policy from the DokuWiki config
     */
    public function __construct(){
        global $conf;

        $this->min_length = $conf['passpolicyminlen'];
        $this->min_pools  = $conf['passpolicyminpools'];
        $this->usernamecheck = $conf['passpolicyuser'];

        $opts = explode(',',$conf['passpolicypools']);
        if(count($opts)){ // ignore empty pool setups
            $this->usepools = array();
            foreach($opts as $pool){
                $this->usepools[$pool] = true;
            }
        }
        if($this->min_pools > count($this->usepools)) $this->min_pools = $this->usepools;
    }

    /**
     * @param $username
     * @return bool|string
     * @throws Exception when no password matching the current policy can be created
     */
    public function generatePassword($username) {
        $pw = '';

        if($this->pronouncable){
            for($i=0; $i < 3; $i++){
                $pw = $this->pronouncablePassword();
                if($pw === false) break; // we'll never get a pronouncable password

                // check if policy is matched
                if($this->checkPolicy($pw, $username)) break;
                // try again
            }
        }
        if($pw) return $pw; // we're done already

        for($i=0; $i < 5; $i++){
            $pw = $this->randomPassword();
            if($pw === false) break; // we'll never get a pronouncable password

            // check if policy is matched
            if($this->checkPolicy($pw, $username)) break;
            // try again
        }
        if($pw) return $pw; // we're done already

        // still here? we have big problem
        throw new Exception('can\'t create a random password matching the password policy');
    }

    /**
     * Gives a human readable explanation of the current policy as plain text.
     *
     * @return string
     */
    public function explainPolicy(){
        global $lang;

        $text = '';

        // load pool names from config plugin
        $confplugin = plugin_load('admin','config');
        $pools = array();
        foreach($this->usepools as $pool => $on){
            if($on) $pools[] = $confplugin->getLang('passpolicypools_'.$pool);
        }

        if($this->min_length)
            $text .= sprintf($lang['passpolicy_length'], $this->min_length)."\n";
        if($this->min_pools)
            $text .= sprintf($lang['passpolicy_pools'], $this->min_length, join(', ', $pools))."\n";
        if($this->usernamecheck == 1)
            $text .= $lang['passpolica_user1']."\n";
        if($this->usernamecheck > 1)
            $text .= sprintf($lang['passpolica_user2'], $this->usernamecheck)."\n";

        return trim($text);
    }

    /**
     * Checks a given password for policy violation
     *
     * @param string $pass true if the password validates against the policy
     * @param string $username
     * @return bool
     */
    public function checkPolicy($pass, $username) {
        $this->error = 0;

        // check length first:
        if(strlen($pass) < $this->min_length){
            $this->error = PassPolicy::LENGTH_VIOLATION;
            return false;
        }

        $matched_pools = 0;
        if(!empty($this->usepools['lower'])) $matched_pools += (int) preg_match('/[a-z]/', $pass);
        if(!empty($this->usepools['upper'])) $matched_pools += (int) preg_match('/[A-Z]/', $pass);
        if(!empty($this->usepools['numeric'])) $matched_pools += (int) preg_match('/[0-9]/', $pass);
        if(!empty($this->usepools['special'])) $matched_pools += (int) preg_match('/[^A-Za-z0-9]/', $pass); // we consider everything else special
        if($matched_pools < $this->min_pools){
            $this->error = PassPolicy::POOL_VIOLATION;
            return false;
        }

        if($this->usernamecheck && $username) {
            $pass     = utf8_strtolower($pass);
            $username = utf8_strtolower($username);

            // simplest case first
            if(utf8_stripspecials($pass,'','\._\-:\*') == utf8_stripspecials($username,'','\._\-:\*')){
                $this->error = PassPolicy::USERNAME_VIOLATION;
                return false;
            }

            // find possible chunks in the lenght defined in policy
            if($this->usernamecheck > 1) {
                $chunks = array();
                for($i = 0; $i < utf8_strlen($pass) - $this->usernamecheck+1; $i++) {
                    $chunk = utf8_substr($pass, $i, $this->usernamecheck+1);
                    if($chunk == utf8_stripspecials($chunk,'','\._\-:\*')){
                        $chunks[] = $chunk; // only word chars are checked
                    }
                }

                // check chunks against user name
                $chunks = array_map('preg_quote_cb', $chunks);
                $re     = join('|', $chunks);

                if(preg_match("/($re)/", $username)){
                    $this->error = PassPolicy::USERNAME_VIOLATION;
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Creates a completely random password
     *
     * @return string
     */
    protected function randomPassword() {
        $pools            = array();
        $pools['lower']   = 'abcdefghijklmnopqrstuvwxyz';
        $pools['upper']   = strtoupper($pools['lower']);
        $pools['numeric'] = '0123456789';
        $pools['special'] = '!"$%&/()=?{[]}\\*+~\'#,;.:-_<>|@';

        $usablepools = array();
        $pw = '';
        // make sure all char pools are used
        foreach($this->usepools as $pool => $on) {
            if($on){
                $pw .= $pools[$pool][rand(0, strlen($pools[$pool]) - 1)];
                $usablepools[] = $pool;
            }
        }
        if(!$usablepools) return false;

        // now fill up
        for($i = strlen($pw); $i < $this->min_length; $i++) {
            $pool = $pools[$usablepools[array_rand($usablepools)]];
            $pw .= $pool[rand(0, strlen($pool) - 1)];
        }

        // shuffle to make sure our intial chars are not necessarily at the start
        return str_shuffle($pw);
    }

    /**
     * Creates a pronouncable password
     *
     * @return bool|string  the new password, false on error
     */
    protected function pronouncablePassword() {
        if(empty($this->usepools['upper']) && empty($this->usepools['lower'])) {
            return false; // we need letters for pronouncable passwords
        }

        // prepare speakable char classes
        $consonants = 'bcdfghjklmnprstvwz'; //consonants except hard to speak ones
        $first      = $consonants;
        if(empty($this->usepools['lower']))  $consonants = strtoupper($consonants);
        if(!empty($this->usepools['upper'])) $first = strtoupper($consonants); // prefer upper for first syllable letter
        $vowels   = 'aeiou';
        $all      = $consonants.$vowels;
        $specials = '!$%&=?.-_;,';

        // calculate syllable number
        $len = $this->min_length + 1;
        if(!empty($this->usepools['numeric'])) $len -= 2; // we add two numbers later
        if(!empty($this->usepools['special'])) $len -= 1; // we add one special later
        $syllables = ceil($len / 3);

        // create words
        $pw = '';
        for($i = 0; $i < $syllables; $i++) {
            $pw .= $first[rand(0, strlen($first) - 1)];
            $pw .= $vowels[rand(0, strlen($vowels) - 1)];
            $pw .= $all[rand(0, strlen($all) - 1)];
        }

        // add a nice numbers and specials
        if(!empty($this->usepools['numeric'])) $pw .= rand(10, 99);
        if(!empty($this->usepools['special'])) $pw .= $specials[rand(0, strlen($specials) - 1)];

        return $pw;
    }

}