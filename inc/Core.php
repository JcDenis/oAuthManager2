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

use http;
use OAuth2\Services;

class Core extends Config
{
    /** @var \dcCore dcCore instance */
    protected $core;

    /** @var Store Store instance */
    protected $store;

    /** @var Services Services instance */
    protected $services;

    public function __construct(\dcCore $core)
    {
        $this->core     = $core;
        $this->store    = new Store($core);
        $this->services = new Services($this->getDisabledProviders());

        try {
            $providers = new \arrayObject();

            $this->core->callBehavior('addOAuth2Provider', $providers, $this->core);

            foreach ($providers as $id => $class) {
                $parent = class_parents($class);
                if (false !== $parent && in_array('OAuth2\Provider', $parent)) {
                    $this->services->addProvider($id, $class);
                }
            }
        } catch (\Exception $e) {
            $core->error->add($e->getMessage());
        }

        if (!$this->checkRedirectUri()) {
            foreach ($this->services->getProviders() as $id => $class) {
                $this->services->addDisabledProvider($id);
            }
        }
    }

    public function store(): Store
    {
        return $this->store;
    }

    public function services(): Services
    {
        return $this->services;
    }

    public function checkProvider(?string $provider): bool
    {
        return !empty($provider)
            && $this->services->hasProvider($provider)
            && !$this->services->hasDisabledProvider($provider);
    }

    public function checkRedirectUri(bool $add_error = false): bool
    {
        $https = false !== strpos($this->getRedirectUri(), 'https://');
        if (!$https && $add_error) {
            $this->core->error->add(__('Oauth2 redirect URI must use secured protocol.'));
        }

        $no_local = false === strpos($this->getRedirectUri(), 'localhost');
        if (!$no_local && $add_error) {
            $this->core->error->add(__('Oauth2 services does not work on local network.'));
        }

        return $https && $no_local;
    }

    public function getActionLink(string $service, string $redir, bool $full = true): string
    {
        if (!$this->checkProvider($service)) {
            return '';
        }

        try {
            $config = array_merge(
                $this->store->getConsumer($service),
                ['redirect_uri' => self::getRedirectUri()]
            );

            $provider = $this->services->getProvider($service, $config);
            $user     = $this->store->getUser($service, $this->core->auth->userID());
            $url      = self::getRedirectUri() . (empty($user['access_token']) ? '?authorize=' : '?revoke=') . $service . '&redir=' . urlencode($redir);
        } catch (\Exception $e) {
            return '';
        }

        return !$full ? $url :
            '<a class="button' . (empty($user['access_token']) ? '' : ' delete') . '" href="' . $url . '">' .
            self::getProviderLogo($service) .
            sprintf((empty($user['access_token']) ? __('Connect to %s') : __('Disconnect from %s')), $provider->getName()) . '</a>';
    }

    public function requestAction(): void
    {
        try {
            if (!empty($_REQUEST['authorize'])) {
                $this->requestAuthorizationCode();
            } elseif (!empty($_REQUEST['state'])) {
                $this->requestAccessToken();
            } elseif (!empty($_REQUEST['refresh'])) {
                $this->refreshAccessToken();
            } elseif (!empty($_REQUEST['revoke'])) {
                $this->revokeAccessToken();
            }
        } catch (\Exception $e) {
            if (empty($_REQUEST[self::getPluginId() . 'error'])) { //prevent loop on php error
                \dcPage::addErrorNotice($e->getMessage());
                http::redirect($this->store->getRedir() . (strpos($this->store->getRedir(), '?') ? '&' : '?') . self::getPluginId() . 'error=1');
            }
        }
    }

    protected function requestAuthorizationCode(): void
    {
        $service = $_REQUEST['authorize'];
        if (!$this->checkProvider($service)) {
            return;
        }
        $this->store->delStates();

        $config = array_merge(
            $this->store->getConsumer($service),
            ['redirect_uri' => self::getRedirectUri()]
        );

        $provider = $this->services->getProvider($service, $config);

        $this->store->setState($service, $provider->getState());
        $this->store->setRedir($_REQUEST['redir'] ?? self::getRedirectUri());

        http::redirect($provider->getAuthorizeUrl());
    }

    protected function requestAccessToken(): void
    {
        $service = $this->store->getState($_REQUEST['state']);
        if (!$this->checkProvider($service)) {
            return;
        }

        $consumer = $this->store->getConsumer($service);
        $provider = $this->services->getProvider($service, [
            'state'        => $_REQUEST['state'],
            'key'          => $consumer['key'],
            'secret'       => $consumer['secret'],
            'redirect_uri' => self::getRedirectUri(),
        ]);
        $token = $provider->requestAccessToken($_REQUEST);

        $this->store->setUser($service, $this->core->auth->userID(), $token->getAccessToken(), $token->getRefreshToken(), $token->getExpires(), $token->getScope());
        $this->store->delStates();

        \dcPage::addSuccessNotice('Service successfully connected');
        http::redirect($this->store->getRedir());
    }

    protected function refreshAccessToken(): void
    {
        //todo
    }

    protected function revokeAccessToken(): void
    {
        $service = $_REQUEST['revoke'] ?? '';
        if (!$this->checkProvider($service)) {
            return;
        }

        $this->store->setUser($service, $this->core->auth->userID());
        $this->store->delStates();
        $this->store->setRedir($_REQUEST['redir'] ?? self::getRedirectUri());

        \dcPage::addSuccessNotice('Service successfully disconnected');
        http::redirect($this->store->getRedir());
    }

    public function getDisabledProviders(): array
    {
        $disabled = $this->core->blog->settings->{self::getPluginId()}->disabled;
        if (is_string($disabled)) {
            $disabled = json_decode($disabled);
        }
        if (!is_array($disabled)) {
            $disabled = [];
        }

        return $disabled;
    }

    public function setDisabledProviders(array $providers): void
    {
        $this->core->blog->settings->{self::getPluginId()}->put('disabled', json_encode($providers));
    }

    public function getProviderLogo(string $provider, bool $big = false, bool $full = true): string
    {
        $big   = $big ? '-b' : '';
        $image = '/img/' . $provider . $big . '.png';

        $logo             = new \arrayObject();
        $logo['provider'] = $provider;
        $logo['big']      = $big;
        $logo['file']     = $this->core->plugins->moduleRoot(self::getPluginId()) . $image;
        $logo['url']      = $this->core->adminurl->get('load.plugin.file', ['pf' => self::getPluginId() . $image]);

        $this->core->callBehavior('getOAuth2ProviderLogo', $logo, $this->core);

        $url = file_exists($logo['file']) ?
            $logo['url'] :
            $this->core->adminurl->get('load.plugin.file', ['pf' => self::getPluginId() . '/icon' . $big . '.png']);

        return !$full ? $url : '<img alt="' . $provider . '" src="' . $url . '" /> ';
    }
}
