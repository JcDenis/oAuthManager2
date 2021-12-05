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

class Install
{
    private static function getDotclearMinimum(\dcCore $core): string
    {
        $requires = $core->plugins->moduleInfo(PLUGIN_ID, 'requires');
        if (is_array($requires)) {
            foreach ($requires as $dep) {
                if (is_array($dep) && $dep[0] == 'core') {
                    return $dep[1];
                }
            }
        }

        return '2.0';
    }

    private static function getDotclearVersion(\dcCore $core): bool
    {
        return method_exists('\dcUtils', 'versionsCompare')
            && \dcUtils::versionsCompare(DC_VERSION, self::getDotclearMinimum($core), '>=', false);
    }

    private static function getPluginVersion(\dcCore $core): string
    {
        $version = $core->getVersion(PLUGIN_ID);

        return is_string($version) ? $version : '0';
    }

    private static function setPluginVersion(\dcCore $core): void
    {
        $core->setVersion(
            PLUGIN_ID,
            $core->plugins->moduleInfo(PLUGIN_ID, 'version')
        );
    }

    private static function checkPluginVersion(\dcCore $core): bool
    {
        return version_compare(
            self::getPluginVersion($core),
            $core->plugins->moduleInfo(PLUGIN_ID, 'version'),
            '<'
        );
    }

    public static function process(\dcCore $core): ?bool
    {
        if (!self::checkPluginVersion($core)) {
            return null;
        }
        if (!self::getDotclearVersion($core)) {
            throw new \Exception(sprintf(
                '%s requires Dotclear %s',
                PLUGIN_ID,
                self::getDotclearMinimum($core)
            ));
        }
        self::setPluginVersion($core);

        return true;
    }
}

try {
    return Install::process($core);
} catch (\Exception $e) {
    $core->error->add($e->getMessage());

    return false;
}
