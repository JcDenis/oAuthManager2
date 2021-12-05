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

const PLUGIN_ID = 'oAuthManager2';

if (!defined('DC_RC_PATH') || !defined('DC_CONTEXT_ADMIN')) {
    return;
}

/* Use library autoload. could move elsewhere one day... */
require_once dirname(__FILE__) . '/inc/OAuth2/_common.php';

/* Plugin autoloader */
foreach (['Store', 'Core'] as $class) {
    $__autoload['plugins\\oAuthManager2\\' . $class] = dirname(__FILE__) . '/inc/' . $class . '.php';
}
