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

    /**
     * Consumer constructor
     *
     * @param string    $key            Consumer key
     * @param string    $secret         Consumer secret
     */
    public function __construct(string $key, string $secret)
    {
        $this->key    = $key;
        $this->secret = $secret;
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
}
