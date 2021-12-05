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

class Consumer
{
    /** @var string Consumer key */
    private $key = '';

    /** @var string Consumer secret */
    private $secret = '';

    /** @var string Consumer domain uri */
    private $domain = '';

    /**
     * Consumer constructor
     *
     * @param string    $key            Consumer key
     * @param string    $secret         Consumer secret
     * @param string    $domain         Consumer domain
     */
    public function __construct(string $key, string $secret, string $domain = '')
    {
        $this->key    = $key;
        $this->secret = $secret;
        $this->domain = $domain;
    }

    /**
     * Check if consumer is configured
     *
     * @return boolean Consumer key and secret is filled
     */
    public function isConfigured(): bool
    {
        return !empty($this->key) && !empty($this->secret);
    }

    /**
     * Get consumer key
     *
     * @return string Consumer key
     */
    public function getKey(): string
    {
        return (string) $this->key;
    }

    /**
     * Get Consumer secret
     *
     * @return string Consumer secret
     */
    public function getSecret(): string
    {
        return (string) $this->secret;
    }

    /**
     * Get Consumer domain
     *
     * @return string Consumer domain
     */
    public function getDomain(): string
    {
        return (string) $this->domain;
    }
}
