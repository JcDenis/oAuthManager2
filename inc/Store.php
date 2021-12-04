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

namespace plugins\oAuthManager2;

class Store extends Config
{
    /** @var \dcCore dcCore instance */
    public $core;

    public function __construct(\dcCore $core)
    {
        $this->core = $core;
        $this->core->blog->settings->addNamespace(self::getPluginId());
        $this->core->auth->user_prefs->addWorkspace(self::getPluginId());
    }

    public function getConsumer(string $provider): array
    {
        $res = [
            'key'    => '',
            'secret' => '',
        ];

        $rs = $this->core->blog->settings->{self::getPluginId()}->get($provider);
        if (is_string($rs)) {
            $rs = json_decode($rs, true);
        }
        if (is_array($rs)) {
            $res = [
                'key'    => $rs['key'] ?? '',
                'secret' => $rs['secret'] ?? '',
            ];
        } else {
            $this->setConsumer($provider);
        }

        return $res;
    }

    public function setConsumer(string $provider, string $key = '', string $secret = ''): void
    {
        $this->core->blog->settings->{self::getPluginId()}->put(
            $provider,
            json_encode(['key' => $key, 'secret' => $secret]),
            'string',
            'consumer credentials',
            true,
            true
        );
    }

    public function getUser(string $provider, string $user): array
    {
        $res = [
            'provider'     => '',
            'access_token' => '',
            'refesh_token' => '',
            'expiry'       => 0,
            'scope'        => [],
        ];

        $rs = $this->core->auth->user_prefs->{self::getPluginId()}->get($provider);
        if (!empty($rs) && is_array($rs)) {
            $res = [
                'provider'     => $rs['provider'] ?? '',
                'access_token' => $rs['access_token'] ?? '',
                'refesh_token' => $rs['refesh_token'] ?? '',
                'expiry'       => $rs['expiry'] ?? 0,
                'scope'        => $rs['scope'] ?? [],
            ];
        } else {
            $this->setUser($provider, $user);
        }

        return $res;
    }

    public function setUser(string $provider, string $user, string $access_token = '', string $refresh_token = '', int $expiry = 0, array $scope = []): void
    {
        $this->core->auth->user_prefs->{self::getPluginId()}->put($provider, [
            'provider'      => $provider,
            'access_token'  => $access_token,
            'refresh_token' => $refresh_token,
            'expiry'        => $expiry,
            'scope'         => $scope,
        ]);
    }

    public function getState(string $state): string
    {
        if (isset($_SESSION[self::getPluginId()])
            && isset($_SESSION[self::getPluginId()]['state'])
            && is_array($_SESSION[self::getPluginId()]['state'])
            && array_key_exists($state, $_SESSION[self::getPluginId()]['state'])
        ) {
            return $_SESSION[self::getPluginId()]['state'][$state];
        }

        return '';
    }

    public function setState(string $provider, string $state): void
    {
        $_SESSION[self::getPluginId()]['state'][$state] = $provider;
    }

    public function delState(string $provider): void
    {
        if (isset($_SESSION[self::getPluginId()])
            && isset($_SESSION[self::getPluginId()]['state'])
            && is_array($_SESSION[self::getPluginId()]['state'])
            && false !== ($state = array_search($provider, $_SESSION[self::getPluginId()]['state']))
        ) {
            unset($_SESSION[self::getPluginId()]['state'][$state]);
        }
    }

    public function delStates(): void
    {
        if (isset($_SESSION[self::getPluginId()])
            && isset($_SESSION[self::getPluginId()]['state'])
        ) {
            unset($_SESSION[self::getPluginId()]['state']);
        }
    }

    public function setRedir(string $redir): void
    {
        $_SESSION[self::getPluginId()]['redir'] = $redir;
    }

    public function getRedir(): string
    {
        if (isset($_SESSION[self::getPluginId()])
            && isset($_SESSION[self::getPluginId()]['redir'])
        ) {
            return $_SESSION[self::getPluginId()]['redir'];
        }

        return self::getRedirectUri();
    }

    public function delRedir(): void
    {
        if (isset($_SESSION[self::getPluginId()])
            && isset($_SESSION[self::getPluginId()]['redir'])
        ) {
            unset($_SESSION[self::getPluginId()]['redir']);
        }
    }
}
