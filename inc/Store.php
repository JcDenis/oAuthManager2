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

class Store
{
    /** @var \dcCore dcCore instance */
    public $core;

    /** @var string Redirect URI */
    protected $redirect_uri;

    public function __construct(\dcCore $core, string $redirect_uri)
    {
        $this->core         = $core;
        $this->redirect_uri = $redirect_uri;
        $this->core->blog->settings->addNamespace(PLUGIN_ID);
        $this->core->auth->user_prefs->addWorkspace(PLUGIN_ID);
    }

    public function getConsumer(string $provider): array
    {
        $res = [
            'key'    => '',
            'secret' => '',
            'domain' => '',
        ];

        $rs = $this->core->blog->settings->{PLUGIN_ID}->get($provider);
        if (is_string($rs)) {
            $rs = json_decode($rs, true);
        }
        if (is_array($rs)) {
            $res = [
                'key'    => $rs['key'] ?? '',
                'secret' => $rs['secret'] ?? '',
                'domain' => $rs['domain'] ?? '',
            ];
        } else {
            $this->setConsumer($provider);
        }

        return $res;
    }

    public function setConsumer(string $provider, string $key = '', string $secret = '', string $domain = ''): void
    {
        $this->core->blog->settings->{PLUGIN_ID}->put(
            $provider,
            json_encode(['key' => $key, 'secret' => $secret, 'domain' => $domain]),
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

        $rs = $this->core->auth->user_prefs->{PLUGIN_ID}->get($provider);
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
        $this->core->auth->user_prefs->{PLUGIN_ID}->put($provider, [
            'provider'      => $provider,
            'access_token'  => $access_token,
            'refresh_token' => $refresh_token,
            'expiry'        => $expiry,
            'scope'         => $scope,
        ]);
    }

    public function getState(string $state): string
    {
        if (isset($_SESSION[PLUGIN_ID])
            && isset($_SESSION[PLUGIN_ID]['state'])
            && is_array($_SESSION[PLUGIN_ID]['state'])
            && array_key_exists($state, $_SESSION[PLUGIN_ID]['state'])
        ) {
            return $_SESSION[PLUGIN_ID]['state'][$state];
        }

        return '';
    }

    public function setState(string $provider, string $state): void
    {
        $_SESSION[PLUGIN_ID]['state'][$state] = $provider;
    }

    public function delState(string $provider): void
    {
        if (isset($_SESSION[PLUGIN_ID])
            && isset($_SESSION[PLUGIN_ID]['state'])
            && is_array($_SESSION[PLUGIN_ID]['state'])
            && false !== ($state = array_search($provider, $_SESSION[PLUGIN_ID]['state']))
        ) {
            unset($_SESSION[PLUGIN_ID]['state'][$state]);
        }
    }

    public function delStates(): void
    {
        if (isset($_SESSION[PLUGIN_ID])
            && isset($_SESSION[PLUGIN_ID]['state'])
        ) {
            unset($_SESSION[PLUGIN_ID]['state']);
        }
    }

    public function setRedir(string $redir): void
    {
        $_SESSION[PLUGIN_ID]['redir'] = $redir;
    }

    public function getRedir(): string
    {
        if (isset($_SESSION[PLUGIN_ID])
            && isset($_SESSION[PLUGIN_ID]['redir'])
        ) {
            return $_SESSION[PLUGIN_ID]['redir'];
        }

        return self::getRedirectUri();
    }

    public function delRedir(): void
    {
        if (isset($_SESSION[PLUGIN_ID])
            && isset($_SESSION[PLUGIN_ID]['redir'])
        ) {
            unset($_SESSION[PLUGIN_ID]['redir']);
        }
    }
}
