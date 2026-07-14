<?php

namespace dokuwiki;

/**
 * Minimal JWT implementation
 */
class JWT
{
    protected $user;
    protected $issued;
    protected $secret;

    /**
     * Create a new JWT object
     *
     * Use validate() or create() to create a new instance
     *
     * @param string $user
     * @param int $issued
     */
    protected function __construct($user, $issued)
    {
        $this->user = $user;
        $this->issued = $issued;
    }

    /**
     * Load the cookiesalt as secret
     *
     * @return string
     */
    protected static function getSecret()
    {
        return auth_cookiesalt(false, true);
    }

    /**
     * Create a new instance from a token
     *
     * @param $token
     * @return self
     * @throws \Exception
     */
    public static function validate($token)
    {
        [$header, $payload, $signature] = sexplode('.', $token, 3, '');
        $signature = base64_decode($signature);

        if (!hash_equals($signature, hash_hmac('sha256', "$header.$payload", self::getSecret(), true))) {
            throw new \Exception('Invalid JWT signature');
        }

        try {
            $header = json_decode(base64_decode($header), true, 512, JSON_THROW_ON_ERROR);
            $payload = json_decode(base64_decode($payload), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            throw new \Exception('Invalid JWT', $e->getCode(), $e);
        }

        if (!$header || !$payload || !$signature) {
            throw new \Exception('Invalid JWT');
        }

        if ($header['alg'] !== 'HS256') {
            throw new \Exception('Unsupported JWT algorithm');
        }
        if ($header['typ'] !== 'JWT') {
            throw new \Exception('Unsupported JWT type');
        }
        if ($payload['iss'] !== 'dokuwiki') {
            throw new \Exception('Unsupported JWT issuer');
        }
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new \Exception('JWT expired');
        }

        $user = $payload['sub'];
        $file = self::getStorageFile($user);
        if (!file_exists($file)) {
            throw new \Exception('JWT not found, maybe it expired?');
        }

        if (file_get_contents($file) !== $token) {
            throw new \Exception('JWT invalid, maybe it expired?');
        }

        return new self($user, $payload['iat']);
    }

    /**
     * Create a new instance from a user
     *
     * Loads an existing token if available
     *
     * @param $user
     * @return self
     */
    public static function fromUser($user)
    {
        $file = self::getStorageFile($user);

        if (file_exists($file)) {
            try {
                return self::validate(io_readFile($file));
            } catch (\Exception $ignored) {
            }
        }

        $token = new self($user, time());
        $token->save();
        return $token;
    }


    /**
     * Get the JWT token for this instance
     *
     * @return string
     */
    public function getToken()
    {
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT',
        ];
        $header = base64_encode(json_encode($header));

        $payload = [
            'iss' => 'dokuwiki',
            'sub' => $this->user,
            'iat' => $this->issued,
        ];
        $payload = base64_encode(json_encode($payload, JSON_THROW_ON_ERROR));

        $signature = hash_hmac('sha256', "$header.$payload", self::getSecret(), true);
        $signature = base64_encode($signature);
        return "$header.$payload.$signature";
    }

    /**
     * Save the token for the user
     *
     * Resets the issued timestamp
     */
    public function save()
    {
        $this->issued = time();
        io_saveFile(self::getStorageFile($this->user), $this->getToken());
    }

    /**
     * Get the user of this token
     *
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get the issued timestamp of this token
     *
     * @return int
     */
    public function getIssued()
    {
        return $this->issued;
    }

    /**
     * Get the storage file for this token
     *
     * Tokens are stored to be able to invalidate them
     *
     * @param string $user The user the token is for
     * @return string
     */
    public static function getStorageFile($user)
    {
        global $conf;
        $hash = hash('sha256', $user);
        $file = $conf['metadir'] . '/jwt/' . $hash[0] . '/' . $hash . '.token';
        io_makeFileDir($file);
        return $file;
    }
}
