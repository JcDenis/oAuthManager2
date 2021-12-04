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

class Install extends Config
{
    private static function getDotclearVersion(\dcCore $core): bool
    {
        return method_exists('\dcUtils', 'versionsCompare')
            && \dcUtils::versionsCompare(DC_VERSION, self::getDotclearMin(), '>=', false);
    }

    private static function getPluginVersion(\dcCore $core): string
    {
        $version = $core->getVersion(self::getPluginId());

        return is_string($version) ? $version : '0';
    }

    private static function setPluginVersion(\dcCore $core): void
    {
        $core->setVersion(
            self::getPluginId(),
            $core->plugins->moduleInfo(self::getPluginId(), 'version')
        );
    }

    private static function checkPluginVersion(\dcCore $core): bool
    {
        return version_compare(
            self::getPluginVersion($core),
            $core->plugins->moduleInfo(self::getPluginId(), 'version'),
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
                self::getPluginId(),
                self::getDotclearMin()
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
