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

/** Dirty way to autoload OAuth2 class */
function oauth2_autoload(string $name): void
{
    $name = implode('/', explode('\\', $name));
    $file = substr(dirname(__FILE__), 0, -6) . $name . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
}
spl_autoload_register('oauth2_autoload');

if (!defined('OAUTH2_DEFAULT_PROVIDERS')) {
    $default_providers = [
        OAuth2\Provider\Facebook::getId()  => OAuth2\Provider\Facebook::class,
        OAuth2\Provider\Github::getId()    => OAuth2\Provider\Github::class,
        OAuth2\Provider\Instagram::getId() => OAuth2\Provider\Instagram::class,
        OAuth2\Provider\Slack::getId()     => OAuth2\Provider\Slack::class,
        OAuth2\Provider\Tumblr::getId()    => OAuth2\Provider\Tumblr::class,
    ];

    define('OAUTH2_DEFAULT_PROVIDERS', $default_providers);
}
