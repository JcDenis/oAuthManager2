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

namespace OAuth2\Provider;

use OAuth2\Provider;

class Auth0 extends Provider
{
    public static function getId(): string
    {
        return 'auth0';
    }

    public static function getName(): string
    {
        return 'Auth0';
    }

    public static function getDescription(): string
    {
        return 'Centralize your auth access.';
    }

    //https://manage.auth0.com/dashboard/eu/xxx/applications
    public static function getConsoleUrl(): string
    {
        return 'https://manage.auth0.com/dashboard/';
    }

    public function getDelimiterScope(): string
    {
        return ' ';
    }

    public function requireDomain(): bool
    {
        return true;
    }

    public function getAuthorizeUri(): string
    {
        return $this->getDomain() . '/authorize';
    }

    public function getAccessTokenUri(): string
    {
        return $this->getDomain() . '/oauth/token';
    }

    public function getRequestUri(): string
    {
        return $this->getDomain() . '/api/v2/';
    }
}
