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
        $this->app->singleton('google-analytics.client', function() use ($redirect_path) {
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
            $page = $event->getPageObject();
            $cp = new \Permissions($page);
            $dh = \Loader::helper('concrete/dashboard');

            if (isset($cp) && $cp->canViewToolbar() && (!$dh->inDashboard())) {
                $config = Core::make(\Concrete\Core\Config\Repository\Repository::class);
                $config = $config->get('concrete.seo.analytics.google', []);
                $access_token = $config['oauth_token']['access_token'];
                $dashboard_url = \URL::to('/dashboard/google-analytics');
                // can we access the analytics page? if so show the button, otherwise hide it.
                // We also need to del with refreshing expired tokens
                // And handle component errors
                $template = require __DIR__.'/../elements/ga-toolbar-button-template.php';

                $v = \View::getInstance();
                $v->addFooterItem($template);
                $v->addFooterItem('<script>var ga_access_token = "'.$access_token.'", ga_profile_id = "'.$config['profile_id'].'";</script>');
                $v->requireAsset('javascript', 'ga-embed-api/core');
                $v->requireAsset('javascript', 'ga-embed-api/toolbar-button');
            }
        });
    }

    public function boot()
    {
        // Code included here will be executed after all service providers have been 
        // registered and the CMS is booting.
    }
}
