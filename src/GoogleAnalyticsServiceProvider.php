<?php
/**
 * Demo Helper Service Provider File.
 *
 * @author   Oliver Green <oliver@c5labs.com>
 * @license  See attached license file
 */
namespace Concrete\Package\GoogleAnalytics\Src;

use Core;
use Concrete\Core\Foundation\Service\Provider;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Demo Helper Service Provider.
 */
class GoogleAnalyticsServiceProvider extends Provider
{
    public function register()
    {
        $this->app->singleton('google-analytics.helper', function() {
            $config = Core::make(\Concrete\Core\Config\Repository\Repository::class);

            return new GoogleAnalyticsHelper($config);
        });

        $this->app->singleton('google-analytics.client', function() {
            $params = array(
                'applicationName'   => 'Concrete5 Google Analytics Addon',
                'clientId'          =>  '460634522959-js9hc2psn2toa9fcel4hlntuok0oposn.apps.googleusercontent.com',
                'clientSecret'      => 'mNb3AlCwAvXm2QOfl7qS7oq_',
                'redirectUri'       => 'urn:ietf:wg:oauth:2.0:oob',
                'scopes'            => [
                    'https://www.googleapis.com/auth/analytics.readonly',
                    'email',
                    'openid',
                    'profile',
                ],
            );

            $config = $this->app->make(\Illuminate\Config\Repository::class);
            $cache = $this->app->make(\Concrete\Core\Cache\Level\ExpensiveCache::class);

            return new GoogleAnalyticsApiClient($params, [], $config, $cache);
        });

        \Events::addListener('on_page_view', function($event) {
            $helper = \Core::make('google-analytics.helper');
            $page = $event->getPageObject();

            /*
             * Add the toolbar item.
             */
            if ($helper->canViewToolbarButton()) {
                // We also need to del with refreshing expired tokens
                // And handle component errors

                /*
                 * Queue the core CSS & JS assets.
                 */
                $v = \View::getInstance();
                $helper->queueCoreAssets($v);
            
                /*
                 * Add the toolbar icon.
                 */
                $icon = array(
                    'icon' => 'refresh fa-spin',
                    'label' => t('Google Analytics Dashboard'),
                    'position' => 'right',
                    'target' => $helper->isDashboardOverviewEnabled() ? '_self' : '_blank',
                    'href' => $helper->isDashboardOverviewEnabled() ? $helper->getDashboardOverviewPageUrl() : 'http://www.google.com/analytics',
                    'linkAttributes' => array('title'=>t('Google Analytics Dashboard'))
                );
                $mh = Core::make('helper/concrete/ui/menu');
                $mh->addPageHeaderMenuItem('ga-button', 'google-analytics', $icon);
            }

            /*
             * Enable tracking.
             */
            if ($helper->isTrackingEnabled() && $helper->shouldTrack(new \User())) {
                $view = \View::getInstance();
                $view->addHeaderItem($helper->getTrackingCode());
            }
        });
    }

    public function boot()
    {
        // Code included here will be executed after all service providers have been 
        // registered and the CMS is booting.
    }
}
