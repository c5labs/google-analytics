<?php
/**
 * Google Analytics Service Provider.
 *
 * @author   Oliver Green <oliver@c5labs.com>
 * @license  See attached license file
 */
namespace Concrete\Package\GoogleAnalytics\Src;

use Concrete\Core\Foundation\Service\Provider;
use Concrete\Core\User\User;
use Core;
use Events;

defined('C5_EXECUTE') or die('Access Denied.');

class GoogleAnalyticsServiceProvider extends Provider
{
    /**
     * Register the services.
     */
    public function register()
    {
        $this->registerHelper();
        $this->registerApiClient();

        Events::addListener('on_page_view', [$this, 'addToolbarIcon']);
        Events::addListener('on_page_view', [$this, 'injectTrackingCode']);
    }

    /**
     * Register the helper class.
     */
    public function registerHelper()
    {
        $this->app->singleton('google-analytics.helper', function () {
            $config = Core::make(\Concrete\Core\Config\Repository\Repository::class);

            return new GoogleAnalyticsHelper($config);
        });
    }

    /**
     * Register the API client.
     */
    public function registerApiClient()
    {
        $this->app->singleton('google-analytics.client', function () {

            $config = $this->app->make(\Illuminate\Config\Repository::class);
            $helper = $this->app->make('google-analytics.helper');

            $params = array(
                'applicationName' => 'Concrete5 Google Analytics Addon',
                'clientId' => $config->get(
                    $helper->getConfigurationKey('client_id'),
                    '460634522959-js9hc2psn2toa9fcel4hlntuok0oposn.apps.googleusercontent.com'
                ),
                'clientSecret' => $config->get(
                    $helper->getConfigurationKey('client_secret'),
                    'mNb3AlCwAvXm2QOfl7qS7oq_'
                ),
                'redirectUri' => 'urn:ietf:wg:oauth:2.0:oob',
                'scopes' => [
                    'https://www.googleapis.com/auth/analytics.readonly',
                    'email',
                    'openid',
                    'profile',
                ],
            );
            $cache = $this->app->make(\Concrete\Core\Cache\Level\ExpensiveCache::class);

            return new GoogleAnalyticsApiClient($params, [], $config, $cache);
        });
    }

    /**
     * Register the tracking code injection handler.
     */
    public function injectTrackingCode()
    {
        $helper = Core::make('google-analytics.helper');

        if ($helper->isTrackingEnabled() && $helper->shouldTrack(new User())) {
            $view = \View::getInstance();
            $view->requireAsset('javascript-inline', 'google-analytics/tracking-code');
        }
    }

    /**
     * Register the toolbar icon addition handler.
     *
     * @todo  We also need to deal with refreshing expired tokens
     * @todo  handle component errors
     */
    public function addToolbarIcon()
    {
        $helper = \Core::make('google-analytics.helper');

        if ($helper->canViewToolbarButton()) {
            $v = \View::getInstance();
            $v->requireAsset('google-analytics/core');

            $icon = array(
                'icon' => 'refresh fa-spin',
                'label' => t('Google Analytics Dashboard'),
                'position' => 'right',
                'target' => $helper->isDashboardOverviewEnabled() ? '_self' : '_blank',
                'href' => $helper->isDashboardOverviewEnabled() ? $helper->getDashboardOverviewPageUrl() : 'http://www.google.com/analytics',
                'linkAttributes' => array('title' => t('Google Analytics Dashboard')),
            );
            $mh = Core::make('helper/concrete/ui/menu');
            $mh->addPageHeaderMenuItem('ga-button', 'google-analytics', $icon);
        }
    }
}
