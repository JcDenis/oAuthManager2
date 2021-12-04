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

class Tumblr extends Provider
{
    // todo: move this to Client
    protected $scope = ['write', 'offline_access'];

    public static function getId(): string
    {
        return 'tumblr';
    }

    public static function getName(): string
    {
        return 'Tumblr';
    }

    public static function getDescription(): string
    {
        return 'Share on your Tumblr blog.';
    }

    public static function getConsoleUrl(): string
    {
        return 'https://www.tumblr.com/oauth/apps';
    }

    public function getDelimiterScope(): string
    {
        return ' ';
    }

    public function getAuthorizeUri(): string
    {
        return 'https://www.tumblr.com/oauth2/authorize';
    }

    public function getAccessTokenUri(): string
    {
        return 'https://api.tumblr.com/v2/oauth2/token';
    }

    public function getRequestUri(): string
    {
        return 'https://api.tumblr.com/v2/';
    }
}
