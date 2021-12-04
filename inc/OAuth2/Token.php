<?php
/**
 * @brief oAuthManager2, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugin
 *
 * @author Jean-Christian Denis and contributors
 *
 * @copyright Jean-Christian Denis
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

namespace OAuth2;

class Token
{
    /** @var string Access token */
    protected $access_token = '';

    /** @var string Refresh token */
    protected $refresh_token = '';

    /** @var integer Expiry timestamp */
    protected $expires = 0;

    /** @var array Scope user accpet */
    protected $scope = [];

    /**
     * Token constructor
     * 
     * @param array $config Token configuration
     */
    public function __construct(array $config)
    {
        if (empty($config['access_token']) || !is_string($config['access_token'])) {
            throw new Exception('Invalid access token');
        }
        $this->access_token = $config['access_token'];

        if (isset($config['refresh_token']) && is_string($config['refresh_token'])) {
            $this->refresh_token = $config['refresh_token'];
        }

        // Provider must clean up "expires_in" value in Provider::parseToken() as some API return "expires" key
        if (isset($config['expires_in'])) {
            $this->expires = $config['expires_in'] == 0 ? 0 : time() + $config['expires_in'];
        }

        if (!empty($config['scope'])) {
            $this->scope = is_array($config['scope']) ? $config['scope'] : explode(',', $config['scope']);
        }
    }

    public function getAccessToken(): string
    {
        return $this->access_token;
    }

    public function getRefreshToken(): string
    {
        return $this->refresh_token;
    }

    public function getExpires(): int
    {
        return $this->expires;
    }

    public function isExpired(): bool
    {
        return $this->expires != 0 && $this->expires < time();
    }

    public function getScope(): array
    {
        return $this->scope;
    }
}
