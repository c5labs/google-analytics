<?php
/**
 * Demo Helper Service Provider File.
 *
 * @author   Oliver Green <oliver@c5labs.com>
 * @license  See attached license file
 */
namespace Concrete\Package\GoogleAnalytics\Src;

use Core;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Demo Helper Service Provider.
 */
class GoogleAnalyticsApiClient extends \League\OAuth2\Client\Provider\Google
{
    protected $access_token;

    protected $scopes;

    protected $base_url = 'https://www.googleapis.com/analytics/v3';

    protected $config_key = 'concrete.seo.analytics.google.oauth_token';

    protected $config;

    protected $cache;

    protected $cache_ttl = 3600;

    public function __construct(array $options = [], array $collaborators = [], \Illuminate\Config\Repository $config, \Concrete\Core\Cache\Cache $cache)
    {
        parent::__construct($options, $collaborators);

        $this->config = $config;

        $this->cache = $cache;

        if ($this->hasSavedAccessToken()) {
            $this->loadSavedAccessToken();
        }
    }

    public function setCurrentAccessToken($access_token)
    {
        $this->access_token = $access_token;
    }

    public function getCurrentAccessToken()
    {
        return $this->access_token;
    }

    public function hasCurrentAccessToken()
    {
        if (empty($this->access_token) || ! ($this->access_token instanceof \League\OAuth2\Client\Token\AccessToken)) {
            return false;
        }

        $data = $this->access_token->jsonSerialize();

        foreach (['token_type', 'access_token', 'refresh_token', 'expires'] as $key) {
            if (! isset($data[$key]) || empty($data[$key])) {
                return false;
            }
        }

        return true;
    }

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

    public function loadSavedAccessToken()
    {
        $data = $this->config->get($this->config_key, []);

        $this->setCurrentAccessToken(new \League\OAuth2\Client\Token\AccessToken($data));

        return $this->hasCurrentAccessToken();
    }

    public function hasSavedAccessToken()
    {
        return $this->config->has($this->config_key);
    }

    public function getAccessToken($grant, array $options = [])
    {
        $token = parent::getAccessToken($grant, $options);

        if ($this->hasCurrentAccessToken()) {
            $params = $this->getCurrentAccessToken()->jsonSerialize();
            $params['expires'] = $token->getExpires();
            $params['access_token'] = $token->getToken();
            $token = new \League\OAuth2\Client\Token\AccessToken($params);
        }

        $this->setCurrentAccessToken($token);

        return $token;
    }

    public function getDefaultScopes()
    {
        return $this->scopes;
    }

    public function request($url, $method)
    {
        if (! $this->hasCurrentAccessToken()) {
            throw new \Exception('No access token set.');
        } elseif ($this->getCurrentAccessToken()->hasExpired()) {
            $this->getAccessToken(new \League\OAuth2\Client\Grant\RefreshToken(), [
                'refresh_token' => $this->getCurrentAccessToken()->getRefreshToken()
            ]);

            if (! $this->saveCurrentAccessToken()) {
                throw new \Exception('Failed to refresh access token.');
            }
        }

        $access_token = $this->access_token->jsonSerialize();

        $cache_key = md5($method.':'.$url.':'.$access_token['access_token']);

        $item = $this->cache->getItem($cache_key);

        if ($item->isMiss()) {
            $request = $this->getAuthenticatedRequest(
                $method, $url, 
                $access_token['access_token']
            );

            $response = $this->getResponse($request);

            $item->set($response, $this->cache_ttl);
        }

        return $item->get();
    }

    public function resource($resource, $method = self::METHOD_GET)
    {
        return $this->request($this->base_url.$resource, $method);
    }

    public function user()
    {
        $fields = array_merge($this->defaultUserFields, $this->userFields);

        $url = 'https://www.googleapis.com/plus/v1/people/me?' . http_build_query([
            'fields' => implode(',', $fields),
            'alt'    => 'json',
        ]);

        return $this->request($url, self::METHOD_GET);
    }
}