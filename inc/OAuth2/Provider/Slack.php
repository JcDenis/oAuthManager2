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

class Slack extends Provider
{
    // todo: move this to Client
    protected $scope = ['chat:write', 'channels:history', 'channels:join'];

    public static function getId(): string
    {
        return 'slack';
    }

    public static function getName(): string
    {
        return 'Slack';
    }

    public static function getDescription(): string
    {
        return 'Link your Slack workspace.';
    }

    // work from bot point of view
    public static function getConsoleUrl(): string
    {
        return 'https://api.slack.com/apps';
    }

    public function getAuthorizeUri(): string
    {
        return 'https://slack.com/oauth/v2/authorize';
    }

    public function getAccessTokenUri(): string
    {
        return 'https://slack.com/api/oauth.v2.access';
    }

    public function getRequestUri(): string
    {
        return 'https://slack.com/api/';
    }

    protected function getRequestParameters(string $method, string $endpoint, array $query, string $access_token): array
    {
        return $query;
    }

    protected function getRequestHeaders(string $method, string $endpoint, array $query, string $access_token): array
    {
        return empty($access_token) ? ['Authorization' => 'Bearer ' . $access_token] : [];
    }
}
