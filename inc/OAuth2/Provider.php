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

abstract class Provider
{
    /** @var Http Http instance */
    protected $http;

    /** @var Consumer Consumer instance */
    protected $consumer;

    /** @var array List of scope */
    protected $scope = [];

    /** @var string State key */
    protected $state = '';

    /** @var string Redirect uri */
    protected $redirect_uri = '';

    /**
     * Provider constructor
     *
     * @param array     $config     Provider configuration
     * @param Http      $http       Http instance
     */
    public function __construct(array $config, Http $http)
    {
        $this->http = new Http();

        if (empty($config)) {
            $config = [
                'key'    => '',
                'secret' => '',
            ];
        }

        $this->consumer = $this->setConsumer($config);

        if (isset($config['state'])) {
            $this->setState($config['state']);
        }
        if (isset($config['scope']) && is_array($config['scope'])) {
            $this->setScope($config['scope']);
        }
        if (isset($config['redirect_uri'])) {
            $this->setRedirectUri($config['redirect_uri']);
        }
    }

    /**
     * Get provider protocol
     *
     * @return string Protocol
     */
    public static function getProtocol(): string
    {
        return 'OAuth2';
    }

    /**
     * Get provider id
     *
     * @return string Id
     */
    abstract public static function getId(): string;

    /**
     * Get provider name
     *
     * @return string Name
     */
    abstract public static function getName(): string;

    /**
     * Get provider short decription
     *
     * @return string Description
     */
    abstract public static function getDescription(): string;

    /**
     * Get provider console url
     *
     * This is the URL where you can setup your apps.
     *
     * @return string Console url
     */
    abstract public static function getConsoleUrl(): string;

    /**
     * Set consumer
     *
     * @param array $config configuration
     *
     * @return Consumer Consumer instance
     */
    protected function setConsumer(array $config): Consumer
    {
        if (!isset($config['key']) || !isset($config['secret'])) {
            throw new Exception('Consumer is not configured');
        }

        return new Consumer($config['key'], $config['secret'], $config['domain'] ?? '');
    }

    /**
     * Get state parameter value
     *
     * @return string State value
     */
    public function getState()
    {
        if (empty($this->state)) {
            $this->state = bin2hex(random_bytes(16));
        }

        return $this->state;
    }

    /**
     * Set state parameter from outside of provider class
     *
     * @param string $state State value
     */
    public function setState(string $state): void
    {
        $this->state = $state;
    }

    /**
     * Check state parameter value
     *
     * @param  string $state State value
     * @return boolean       is equal
     */
    public function checkState(string $state): bool
    {
        return $this->state == $state;
    }

    /**
     * Get scope
     *
     * @return array Scope
     */
    public function getScope(): array
    {
        return $this->scope;
    }

    /**
     * Set scope
     *
     * @param array $scope Scope
     */
    protected function setScope(array $scope): void
    {
        $this->scope = $scope;
    }

    /**
     * Check scope
     *
     * @param string $scope Scope
     *
     * @return boolean Exists
     */
    public function hasScope(string $scope): bool
    {
        return in_array($scope, $this->getScope());
    }

    /**
     * Get scope delimiter
     *
     * @return string Scope delimiter
     */
    public function getDelimiterScope(): string
    {
        return ',';
    }

    /**
     * Get array scope to string
     *
     * @return string Scope in string format
     */
    public function toStringScope(array $scope): string
    {
        return implode($this->getDelimiterScope() ?: ' ', $scope);
    }

    /**
     * Get string scope to array
     *
     * @return array Scope in string format
     */
    public function toArrayScope(string $scope): array
    {
        return explode($this->getDelimiterScope() ?: ' ', $scope);
    }

    /**
     * Does provider required domain
     *
     * This is a custom base URI for request.
     *
     * @return boolean Domain is required
     */
    public function requireDomain(): bool
    {
        return false;
    }

    /**
     * Get provider (consumer) domain
     *
     * @return string Domain
     */
    public function getDomain(): string
    {
        return $this->consumer->getDomain();
    }

    /**
     * Set redirect uri
     *
     * @param string $redirect_uri Redirect URI
     */
    protected function setRedirectUri(string $redirect_uri): void
    {
        $this->redirect_uri = $redirect_uri;
    }

    /**
     * Get parsed redirect URL
     *
     * @return string Redirect URL
     */
    public function getRedirectUrl(): string
    {
        return str_replace('PROVIDER', $this->getId(), $this->redirect_uri);
    }

    /**
     * Get built authorize URL
     *
     * @return string Authorize URL
     */
    public function getAuthorizeUrl(): string
    {
        $parameters          = $this->getAuthorizeParameters();
        $parameters['state'] = $this->getState();
        if (count($this->getScope())) {
            $parameters['scope'] = $this->toStringScope($this->getScope());
        }

        return $this->getAuthorizeUri() . '?' . http_build_query($parameters);
    }

    /**
     * Get authorize request parameters
     *
     * Could be overwriten by child class
     *
     * @return array Authorize request parameters
     */
    protected function getAuthorizeParameters(): array
    {
        return [
            'client_id'     => $this->consumer->getKey(),
            'redirect_uri'  => $this->getRedirectUrl(),
            'response_type' => 'code',
        ];
    }

    /**
     * Get authorization uri
     *
     * @return string Authorization uri
     */
    abstract public function getAuthorizeUri(): string;

    /**
     * Request access token
     *
     * @param  array  $rsp Reponse from redirect url reponse
     *
     * @return Token       Token instance
     */
    public function requestAccessToken(array $rsp): Token
    {
        if (!empty($rsp['error'])) {
            throw new Exception('Unauthorized: ' . $rsp['error']);
        }
        if (empty($rsp['state'])) {
            throw new Exception('Unknow response state');
        }
        if ($rsp['state'] !== $this->getState()) {
            throw new Exception('Invalid reponse state');
        }
        if (empty($rsp['code'])) {
            throw new Exception('Invalid response code');
        }

        return $this->getAccessToken(
            $this->getAccessTokenParameters($rsp['code']),
            $this->getAccessTokenHeaders($rsp['code'])
        );
    }

    /**
     * Get access token request parameters
     *
     * Could be overwriten by child class
     *
     * @param  string $code Code from redirect url repsonse
     *
     * @return array        Access token request parameters
     */
    protected function getAccessTokenParameters(string $code): array
    {
        return [
            'client_id'     => $this->consumer->getKey(),
            'client_secret' => $this->consumer->getSecret(),
            'redirect_uri'  => $this->getRedirectUrl(),
            'grant_type'    => 'authorization_code',
            'code'          => $code,
        ];
    }

    /**
     * Get access token request headers
     *
     * Could be overwriten by child class
     *
     * @param  string $code Code from redirect url repsonse
     *
     * @return array        Access token request headers
     */
    protected function getAccessTokenHeaders(string $code): array
    {
        return ['Accept' => 'application/json'];
    }

    /**
     * Get access token (request)
     *
     * @param  array  $parameters request parameters
     * @param  array  $headers    request headers
     * @return Token              Token instance
     */
    public function getAccessToken(array $parameters, array $headers): Token
    {
        $response = $this->http->request('POST', $this->getAccessTokenUri(), $parameters, $headers);

        if (empty($response['content'])) {
            throw new Exception('empty response content');
        }

        return $this->parseToken($response['content']);
    }

    /**
     * Get request token uri
     *
     * @return string Request token uri
     */
    abstract public function getAccessTokenUri(): string;

    /**
     * Request refresh token
     *
     * @param  string $refresh_token Refresh token
     *
     * @return Token       Token instance
     */
    public function requestRefreshToken(string $refresh_token): Token
    {
        return $this->getRefreshToken(
            $this->getRefreshTokenParameters($refresh_token),
            $this->getRefreshTokenHeaders($refresh_token)
        );
    }

    /**
     * Get refresh token request parameters
     *
     * Could be overwriten by child class
     *
     * @param  string $refresh_token Refresh token
     *
     * @return array        Access token request parameters
     */
    protected function getRefreshTokenParameters(string $refresh_token): array
    {
        return [
            'client_id'     => $this->consumer->getKey(),
            'client_secret' => $this->consumer->getSecret(),
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refresh_token,
        ];
    }

    /**
     * Get refresh token request headers
     *
     * Could be overwriten by child class
     *
     * @param  string $refresh_token Refresh token
     *
     * @return array        Refresh token request headers
     */
    protected function getRefreshTokenHeaders(string $refresh_token): array
    {
        return ['Accept' => 'application/json'];
    }

    /**
     * Get refresh token (request)
     *
     * @param  array  $parameters request parameters
     * @param  array  $headers    request headers
     *
     * @return Token              Token instance
     */
    public function getRefreshToken(array $parameters, array $headers): Token
    {
        $response = $this->http->request('POST', $this->getRefreshTokenUri(), $parameters, $headers);

        if (empty($response['content'])) {
            throw new Exception('empty response content');
        }

        return $this->parseToken($response['content']);
    }

    /**
     * Get refresh token uri
     *
     * @return string Refresh token uri
     */
    public function getRefreshTokenUri(): string
    {
        return $this->getAccessTokenUri();
    }

    /**
     * Parse request token response
     *
     * Could be overwriten by child class
     *
     * @param  string $content Response content
     *
     * @return Token           Token instance
     */
    protected function parseToken(string $content): Token
    {
        $token = json_decode($content, true);
        if (!$token || !is_array($token)) {
            throw new Exception('invalid response format');
        }

        $this->parseResponseError($token);

        if (!empty($token['scope']) && is_string($token['scope'])) {
            $token['scope'] = $this->toArrayScope($token['scope']);
        }

        return new Token($token);
    }

    /**
     * Parse request token response error
     *
     * Could be overwriten by child class
     *
     * @param  array $response Response content
     */
    protected function parseResponseError(array $response): void
    {
        $message = '';
        if (!empty($response['error'])) {
            if (empty($response['error_description'])) {
                $message = $response['error_description'];
            } elseif (empty($response['error_reason'])) {
                $message = $response['error_reason'];
            } else {
                $message = $response['error'];
            }
        } elseif (!empty($response['error_message'])) {
            $message = $response['error_message'];
        }

        if (!empty($message)) {
            throw new Exception('Unauthorized: ' . $message);
        }
    }

    public function request(string $method, string $endpoint, array $query, string $access_token): string
    {
        return $this->getRequest(
            $method,
            $endpoint,
            $this->getRequestParameters($method, $endpoint, $query, $access_token),
            $this->getRequestHeaders($method, $endpoint, $query, $access_token)
        );
    }

    protected function getRequestParameters(string $method, string $endpoint, array $query, string $access_token): array
    {
        if (!empty($access_token)) {
            $query['access_token'] = $access_token;
        }

        return $query;
    }

    protected function getRequestHeaders(string $method, string $endpoint, array $query, string $access_token): array
    {
        return [];
    }

    public function getRequest(string $method, string $endpoint, array $parameters, array $headers): string
    {
        $response = $this->http->request($method, $this->getRequestUri() . $endpoint, $parameters, $headers);

        if (empty($response['content'])) {
            throw new Exception('empty response content');
        }

        return $this->parseRequest($response['content']);
    }

    /**
     * Get API base uri
     *
     * @return string API base uri
     */
    abstract public function getRequestUri(): string;

    protected function parseRequest(string $content): string
    {
        return $content;
    }
}
