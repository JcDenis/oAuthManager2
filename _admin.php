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

if (!defined('DC_CONTEXT_ADMIN')) {
    return;
}

/**
 * oAuthClient admin class
 *
 * Add menu and dashboard icons.
 */
class Admin extends Core
{
    public function __construct(\dcCore $core, \ArrayObject $_menu)
    {
        parent::__construct($core);

        $this->requestAction();
        $this->addCoreBehaviors();
        $this->addAdminMenuItem($_menu);
    }

    private function addCoreBehaviors(): void
    {
        $this->core->addBehavior('adminDashboardFavorites', __NAMESPACE__ . '\admin::registerDashboardFavorites');
        $this->core->addBehavior('adminPreferencesForm', __NAMESPACE__ . '\admin::addUserPreferencesForm');
    }

    private function addAdminMenuItem(\ArrayObject $_menu): void
    {
        $_menu['Plugins']->addItem(
            self::getPluginName(),
            $this->core->adminurl->get('admin.plugin.' . self::getPluginId()),
            \dcPage::getPF(self::getPluginId() . '/icon.png'),
            preg_match('/' . preg_quote($this->core->adminurl->get('admin.plugin.' . self::getPluginId())) . '(&.*)?$/', $_SERVER['REQUEST_URI']),
            $this->core->auth->isSuperAdmin()
        );
    }

    public static function registerDashboardFavorites(\dcCore $core, \dcFavorites $favs): void
    {
        $favs->register(
            self::getPluginId(),
            [
                'title'       => self::getPluginName(),
                'url'         => $core->adminurl->get('admin.plugin.' . self::getPluginId()),
                'small-icon'  => \dcPage::getPF(self::getPluginId() . '/icon.png'),
                'large-icon'  => \dcPage::getPF(self::getPluginId() . '/icon-b.png'),
                'permissions' => null,
            ]
        );
    }

    public static function addUserPreferencesForm(\dcCore $core): void
    {
        $lines = [];
        $auth  = new Core($core);
        foreach ($auth->services()->getProviders() as $service) {
            if ($auth->services()->hasDisabledProvider($service::getId())) {
                continue;
            }
            $link = $auth->getActionLink($service::getId(), $core->adminurl->get('admin.user.preferences') . '#user-options');
            if (empty($link)) {
                continue;
            }
            $lines[] = '<div class="box fieldset">' .
            '<h5>' . $service::getName() . '</h5>' .
            '<p>' . html::escapeHTML($service::getDescription()) . '</p>' .
            '<p>' . $link . '</p>' .
            '</ul></div>';
        }
        if (!empty($lines)) {
            echo
            '<div class="fieldset"><h5 id="' . self::getPluginId() . '_prefs">' . self::getPluginName() . '</h5>' .
            '<div>' . implode('', $lines) . '</div>' .
            '</div>';
        }
    }
}

/* process */
new Admin($core, $_menu);
