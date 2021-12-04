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

class Github extends Provider
{
    // todo: move this to Client
    protected $scope = ['public_repo', 'read:user', 'notifications'];

    public static function getId(): string
    {
        return 'github';
    }

    public static function getName(): string
    {
        return 'Github';
    }

    public static function getDescription(): string
    {
        return 'Connect your Github profile and repo.';
    }

    public static function getConsoleUrl(): string
    {
        return 'https://github.com/settings/applications/';
    }

    public function getAuthorizeUri(): string
    {
        return 'https://github.com/login/oauth/authorize';
    }

    public function getAccessTokenUri(): string
    {
        return 'https://github.com/login/oauth/access_token';
    }

    public function getRequestUri(): string
    {
        return 'https://api.github.com/user';
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
