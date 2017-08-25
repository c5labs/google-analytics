<?php
/**
 * Google Analytics API Client.
 *
 * @author   Oliver Green <oliver@c5labs.com>
 * @license  See attached license file
 */
namespace Concrete\Package\GoogleAnalytics\Src;

use Exception;
use Concrete\Core\Cache\Cache;
use Illuminate\Config\Repository;
use League\OAuth2\Client\Grant\RefreshToken;
use League\OAuth2\Client\Token\AccessToken;

defined('C5_EXECUTE') or die('Access Denied.');

class GoogleAnalyticsApiClient extends \League\OAuth2\Client\Provider\Google
{
    /**
     * The current acecess token in use.
     *
     * @var AccessToken
     */
    protected $access_token;

    /**
     * oAuth permission scopes.
     *
     * @var array
     */
    protected $scopes = [];

    /**
     * Base endpoint URI.
     *
     * @var string
     */
    protected $base_url = 'https://www.googleapis.com/analytics/v3';

    /**
     * Base configuration key.
     *
     * @var string
     */
    protected $config_key = 'concrete.seo.analytics.google.oauth_token';

    /**
     * Loaded configuration.
     *
     * @var array
     */
    protected $config;

    /**
     * Cache instance.
     *
     * @var Repository
     */
    protected $cache;

    /**
     * Default cache TTL.
     *
     * @var int
     */
    protected $cache_ttl = 3600;

    /**
     * Constructor.
     *
     * @param array                         $options
     * @param array                         $collaborators
     * @param \Illuminate\Config\Repository $config
     * @param \Concrete\Core\Cache\Cache    $cache
     */
    public function __construct(array $options, array $collaborators, Repository $config, Cache $cache)
    {
        parent::__construct($options, $collaborators);

        $this->config = $config;

        $this->cache = $cache;

        if ($this->hasSavedAccessToken()) {
            $this->loadSavedAccessToken();
        }
    }

    /**
     * Sets the current access token.
     *
     * @param AccessToken $access_token
     */
    public function setCurrentAccessToken(AccessToken $access_token)
    {
        $this->access_token = $access_token;
    }

    /**
     * Gets the current access token.
     *
     * @return AccessToken
     */
    public function getCurrentAccessToken()
    {
        return $this->access_token;
    }

    /**
     * Have we got a valid current access token?
     *
     * @return bool
     */
    public function hasCurrentAccessToken()
    {
        if (empty($this->access_token) || !($this->access_token instanceof AccessToken)) {
            return false;
        }

        return true;
    }

    /**
     * Saves the current access token to configuration.
     *
     * @return bool
     */
    public function saveCurrentAccessToken()
    {
        if ($this->hasCurrentAccessToken()) {
            return $this->config->save(
                $this->config_key,
                $this->getCurrentAccessToken()->jsonSerialize()
            );
        }

        return false;
    }

    /**
     * Refreshes the current access token.
     * 
     * @return bool
     */
    public function refreshCurrentAccessToken()
    {
        if ($this->hasCurrentAccessToken()) {
            $this->getAccessToken(new RefreshToken(), [
                'refresh_token' => $this->getCurrentAccessToken()->getRefreshToken(),
            ]);

            if (!$this->saveCurrentAccessToken()) {
                throw new Exception('Failed to refresh access token.');
            }

            return $this->hasCurrentAccessToken();
        }

        return false;
    }

    /**
     * Loads the current access token from configuration.
     *
     * @return bool
     */
    public function loadSavedAccessToken()
    {
        $data = $this->config->get($this->config_key, []);

        $this->setCurrentAccessToken(new AccessToken($data));

        return $this->hasCurrentAccessToken();
    }

    /**
     * Is there an access token saved in configuration?
     *
     * @return bool
     */
    public function hasSavedAccessToken()
    {
        return $this->config->has($this->config_key);
    }

    /**
     * Gets or refreshes an access token from Google.
     *
     * @param mixed $grant
     * @param array $options
     *
     * @return AccessToken
     */
    public function getAccessToken($grant, array $options = [])
    {
        $token = parent::getAccessToken($grant, $options);

        // Handle a token refresh.
        if ($grant instanceof RefreshToken && $this->hasCurrentAccessToken()) {
            $params = $this->getCurrentAccessToken()->jsonSerialize();
            $params['expires'] = $token->getExpires();
            $params['access_token'] = $token->getToken();
            $token = new AccessToken($params);
        }

        $this->setCurrentAccessToken($token);

        return $token;
    }

    /**
     * Get the default permissions scopes.
     * 
     * @return array
     */
    public function getDefaultScopes()
    {
        return $this->scopes;
    }

    /**
     * Make a request to the analytics API endpoint.
     * 
     * @param string $resource
     * @param string $method
     *
     * @return mixed
     */
    public function resource($resource, $method = self::METHOD_GET)
    {
        return $this->request($this->base_url.$resource, $method);
    }

    /**
     * Request the authenticated users details.
     * 
     * @return mixed
     */
    public function user()
    {
        $fields = array_merge($this->defaultUserFields, $this->userFields);

        $url = 'https://www.googleapis.com/plus/v1/people/me?'.http_build_query([
            'fields' => implode(',', $fields),
            'alt' => 'json',
        ]);

        return $this->request($url, self::METHOD_GET);
    }

    /**
     * Make a request to the an endpoint.
     * 
     * @param string $url
     * @param string $method
     *
     * @return mixed
     */
    public function request($url, $method)
    {
        // Oops, we have no token set.
        if (!$this->hasCurrentAccessToken()) {
            throw new Exception('No access token set.');
        }

        // Refresh the current access token if it has expired before making the call.
        elseif ($this->getCurrentAccessToken()->hasExpired()) {
            $this->refreshCurrentAccessToken();
        }

        $access_token = $this->access_token->jsonSerialize();

        // Form the cache key and get the cache item.
        $cache_key = md5($method.':'.$url.':'.$access_token['access_token']);
        $item = $this->cache->getItem($cache_key);

        // We have no cache so make the request and cache it.
        if ($item->isMiss()) {
            $request = $this->getAuthenticatedRequest(
                $method,
                $url,
                $access_token['access_token']
            );

            $response = $this->getResponse($request);
            $item->set($response, $this->cache_ttl);
        } else {
            $response = $item->get();
        }

        return $response;
    }
}
