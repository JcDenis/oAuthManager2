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

use html;
use form;
use http;
use OAuth2\Provider;

/**
 * oAuthClient page class
 *
 * Display page and configure modules.
 */
class Index extends Core
{
    /** @var Provider|null Provider classname */
    private $provider = null;

    public function __construct(\dcCore $core)
    {
        \dcPage::checkSuper();

        parent::__construct($core);

        $this->provider = $this->getProvider();
        $this->actionPage();
        $this->displayPage();
    }

    private function getProvider(): ?Provider
    {
        return !$this->checkProvider($_REQUEST['config']) ? null : $this->services->getProvider($_REQUEST['config']);
    }

    private function actionPage(): void
    {
        if (empty($_POST['save']) || !empty($_REQUEST['config'])) {
            return;
        }

        try {
            $disabled = !empty($_POST['disabled']) && is_array($_POST['disabled']) ? $_POST['disabled'] : [];
            $this->setDisabledProviders($disabled);

            \dcPage::addSuccessNotice(__('Configuration successfully updated'));
            $this->core->adminurl->redirect('admin.plugin', ['p' => PLUGIN_ID]);
        } catch (\Exception $e) {
            $this->core->error->add($e->getMessage());
        }
    }

    private function displayPage(): void
    {
        $bc = empty($_REQUEST['config']) ?
            [__('Providers') => ''] :
            [
                __('Providers')          => $this->core->adminurl->get('admin.plugin.' . PLUGIN_ID),
                __('Configure Provider') => '',
            ];

        echo
        '<html><head><title>' . __($this->core->plugins->moduleInfo(PLUGIN_ID, 'name')) . '</title></head><body>' . PLUGIN_ID .
        \dcPage::notices() .
        \dcPage::breadcrumb(array_merge([
            __('Plugins') => '',
            __($this->core->plugins->moduleInfo(PLUGIN_ID, 'name')) => '',
        ], $bc));

        if (empty($_REQUEST['config'])) {
            $this->displayList();
        } else {
            $this->displayConfigurator();
        }

        echo '</body></html>';
    }

    private function displayList(): void
    {
        if (!$this->checkRedirectUri(true)) {
            echo '<p class="warning">' . sprintf(
                __('OAuth2 redirect URI is based on DC_AMDIN_URL or OAUTH2_REDIRECT_URI from Dotclear configuration file and is set to %s, please fix error.'),
                self::getRedirectUri()
            ) . '</p>';

            return;
        }

        echo
        '<form action="' . $this->core->adminurl->get('admin.plugin.' . PLUGIN_ID) . '" method="post" id="form-actions">' .
        '<table><caption class="hidden">' . __('Providers') . '</caption><thead><tr>' .
        '<th class="first" colspan="2">' . __('Provider') . '</td>' .
        '<th scope="col">' . __('Credentials') . '</td>' .
        '<th scope="col">' . __('Description') . '</td>' .
        '<th scope="col">' . __('Id') . '</td>' .
        '</tr></thead><tbody>';

        foreach ($this->services->getProviders() as $provider) {
            $consumer      = $this->store->getConsumer($provider::getId());
            $is_configured = !empty($consumer['key']) && !empty($consumer['secret']);

            echo
            '<tr class="line' . ($is_configured ? '' : ' offline') . '">' .
            '<td class="minimal">' . form::checkbox(
                ['disabled[]', 'disabled_' . $provider::getId()],
                $provider::getId(),
                ['checked' => $this->services->hasDisabledProvider($provider::getId())]
            ) . '</td>' .
            '<td class="minimal nowrap"><label for="disabled_' . $provider::getId() . '">' .
                $this->getProviderLogo($provider::getId()) . html::escapeHTML($provider::getName()) .
            '</label></td>' .
            '<td class="minimal nowrap">' . (
                $this->services->hasDisabledProvider($provider::getId()) ? '' :
                '<a class="module-config" href="' . $this->core->adminurl->get('admin.plugin.' . PLUGIN_ID, ['config' => $provider::getId()]) .
                '" title="' . sprintf(__("Configure provider '%s'"), $provider::getName()) . '">' . html::escapeHTML(__('Configure')) . '</a>'
            ) . '</td>' .
            '<td class="maximal">' . html::escapeHTML($provider::getDescription()) . '</td>' .
            '<td class="minimal nowrap modules">' . html::escapeHTML($provider::getId()) . '</td>' .
            '</tr>';
        }

        echo
        '</tbody></table>
        <div>
        <p><input type="submit" name="save" value="' . __('Disable selected providers') . '" />' .
        $this->core->formNonce() . '
        </p>
        </div>
        <br class="clear" />
        </form>';
    }

    private function displayConfigurator(): void
    {
        $back_url = $_REQUEST['redir'] ?? $this->core->adminurl->get('admin.plugin.' . PLUGIN_ID);

        if (!$this->checkRedirectUri() || null === $this->provider) {
            echo
            '<p class="warning">' . __('Unknow provider') . '</p>' .
            '<p><a class="back" href="' . $back_url . '">' . __('Back') . '</a></p>';
        } else {
            $redir = $_REQUEST['redir'] ?? $this->core->adminurl->get('admin.plugin.' . PLUGIN_ID, ['config' => $this->provider::getId()]);

            switch (strtolower($this->provider::getProtocol())) {
                case 'oauth2':
                    $res = $this->oauth2Configuration($redir);

                    break;

                default:
                    $res = sprintf(__('Unsupported configuration for protocol" %s"'), $this->provider::getProtocol());
            }

            echo '
            <h3>' . sprintf(__('"%s" application credentials'), $this->provider::getName()) . '</h3>
            <p><a class="back" href="' . $back_url . '">' . __('Back') . '</a></p>
            <form action="' . $this->core->adminurl->get('admin.plugin.' . PLUGIN_ID) . '" method="post" id="form-actions">' .
            $res .
            '<p class="clear"><input type="submit" name="save" value="' . __('Save') . '" />' .
            form::hidden('config', $this->provider::getId()) .
            form::hidden('redir', $redir) .
            $this->core->formNonce() . '</p>' .
            '</form>';
        }
    }

    private function oauth2Configuration(string $redir): string
    {
        if (null === $this->provider) {
            return '';
        }
        $consumer = $this->store->getConsumer($this->provider::getId());
        if (!empty($_POST['save'])) {
            $this->store->setConsumer($this->provider::getId(), $_POST['consumer_key'], $_POST['consumer_secret'], $_POST['consumer_domain']);
            \dcPage::addSuccessNotice(__('Configuration successfully updated'));
            http::redirect($redir);
        }
        $scope = implode(', ', $this->provider->getScope());

        return
        '<div class="two-boxes">' .
        '<h5>' . __('Credentials:') . '</h5>' .
        (!$this->provider->requireDomain() ? form::hidden('consumer_domain', '') :
            '<p><label class="classic" for="consumer_domain">' .
            __('Domain: (Request base URL)') . '<br />' .
            form::field('consumer_domain', 80, 255, html::escapeHTML($consumer['domain'])) . '</label>' .
            '</p>'
        ) .
        '<p><label class="classic" for="consumer_key">' .
        __('Client ID: (Consumer key)') . '<br />' .
        form::field('consumer_key', 80, 255, html::escapeHTML($consumer['key'])) . '</label>' .
        '</p>' .
        '<p><label class="classic" for="consumer_secret">' .
        __('Client secret: (Consumer secret)') . '<br />' .
        form::password('consumer_secret', 80, 255, html::escapeHTML($consumer['secret'])) . '</label>' .
        '</p>' .
        '</div><div class="two-boxes">' .
        '<h5>' . __('Configuration:') . '</h5><ul class="nice">' .
        '<li><a href="' . $this->provider::getConsoleUrl() . '">' . __('Go to client console to configure app.') . '</a></li>' .
        '<li><strong>' . __('Callback URL:') . '</strong> ' . self::getRedirectUri() . '</li>' .
        '<li><strong>' . __('Default scope:') . '</strong> ' . $scope . '</li>' .
        '</ul>' .
        '</div>';
    }
}

/* process */
new Index($core);
