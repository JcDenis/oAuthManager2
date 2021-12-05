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

namespace OAuth2;

class Services
{
    /** @var Http Http instance */
    protected $http;

    /** @var array Providers id/classname */
    protected $providers = [];

    /** @var array Disabled providers id */
    protected $disabled = [];

    /**
     * Services constructor
     *
     * @param array     $disabled List of disabled providers by id
     * @param Http|null $http     Http instance
     */
    public function __construct(array $disabled = [], Http $http = null)
    {
        $this->http = $http ?? new Http();

        if (defined('OAUTH2_DEFAULT_PROVIDERS') && is_array(OAUTH2_DEFAULT_PROVIDERS)) {
            foreach (OAUTH2_DEFAULT_PROVIDERS as $id => $class) {
                $this->addProvider($id, $class);
            }
        }

        foreach ($disabled as $id) {
            $this->addDisabledProvider($id);
        }
    }

    /**
     * Disabled usage of a provider (only from this class)
     *
     * But you can still call static method if provider exists.
     *
     * @param  string $id Provider id
     */
    public function addDisabledProvider(string $id): void
    {
        $this->disabled[] = $id;
    }

    /**
     * Check if a provider is disabled
     *
     * @param  string       $id    provider id
     * @param  bool|boolean $throw Throw exception if it is disabled
     *
     * @return boolean             Is disabled
     */
    public function hasDisabledProvider(string $id, bool $throw = false): bool
    {
        $exists = in_array($id, $this->disabled);
        if ($exists && $throw) {
            throw new Exception('Provider ' . $id . ' is disabled');
        }

        return $exists;
    }

    /**
     * Add a provider by id/classname
     *
     * @param string $id    provider id
     * @param string $class provider classname
     */
    public function addProvider(string $id, string $class): void
    {
        if (!$this->hasDisabledProvider($id) && is_subclass_of($class, 'OAuth2\\Provider')) {
            $this->providers[$id] = $class;
        }
    }

    /**
     * Check if a provider is added
     *
     * @param  string       $id    provider id
     * @param  bool|boolean $throw Throw exception if it not is added
     *
     * @return boolean             Is added (even if it is disabled)
     */
    public function hasProvider(string $id, bool $throw = false): bool
    {
        $exists = isset($this->providers[$id]);
        if (!$exists && $throw) {
            throw new Exception('Provider ' . $id . ' doest not exist');
        }

        return $exists;
    }

    /**
     * Load new instance of a provider
     *
     * @param  string   $id         Provider id
     * @param  array    $config     Provider configuration
     * @param  Http     $http       Http instance
     *
     * @return Provider       Provider instance
     */
    public function getProvider(string $id, array $config = [], Http $http = null): Provider
    {
        $this->hasProvider($id, true);
        $this->hasDisabledProvider($id, true);

        $class = $this->providers[$id];

        try {
            /** @var Provider $provider */
            $provider = new $class($config, $http ?? $this->http);
        } catch (\Exception $e) {
            throw new Exception('Failed to load provider ' . $id);
        }

        return $provider;
    }

    /**
     * Get all providers id/classname
     *
     * @return array Providers classname by id
     */
    public function getProviders(): array
    {
        return $this->providers;
    }
}
