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

class Facebook extends Provider
{
    // todo: move this to Client
    protected $scope = ['public_profile'];

    public static function getId(): string
    {
        return 'facebook';
    }

    public static function getName(): string
    {
        return 'Facebook';
    }

    public static function getDescription(): string
    {
        return 'Share on your Facebook profile.';
    }

    //https://developers.facebook.com/apps/xxx/fb-login/settings/
    public static function getConsoleUrl(): string
    {
        return 'https://developers.facebook.com/apps';
    }

    public function getAuthorizeUri(): string
    {
        return 'https://www.facebook.com/dialog/oauth';
    }

    public function getAccessTokenUri(): string
    {
        return 'https://graph.facebook.com/oauth/access_token';
    }

    public function getRequestUri(): string
    {
        return 'https://graph.facebook.com/v12/';
    }

    protected function getRequestParameters(string $method, string $endpoint, array $query, string $access_token): array
    {
        return $query;
    }

    protected function getRequestHeaders(string $method, string $endpoint, array $query, string $access_token): array
    {
        return empty($access_token) ? ['Authorization' => 'bearer ' . $access_token] : [];
    }
}
