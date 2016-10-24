<?php
namespace Concrete\Package\GoogleAnalytics\Controller\SinglePage\Dashboard;

use Core;
use Concrete\Core\Page\Controller\DashboardPageController;

class GoogleAnalytics extends DashboardPageController
{
    public function view()
    {
        $api = Core::make('google-analytics.client');
        $helper = Core::make('google-analytics.helper');
        $config = $helper->getConfiguration();

        if ($api->hasCurrentAccessToken()) {
            $resource_path = sprintf(
                '/management/accounts/%s/webproperties/%s/profiles/%s', 
                $config['account_id'], $config['property_id'], $config['profile_id']
            );
            $profile = $api->resource($resource_path);
            $this->set('profile', $profile);
        }

        $this->set('config', $config);
        $this->set('pageTitle', t('Google Analytics - 30 Day Overview'));

        $helper->queueCoreAssets($this);
        $this->requireAsset('javascript', 'google-analytics/dashboard-overview');
        $this->addFooterItem('<script>(function() { var element = document.getElementsByTagName("body")[0]; element.className += " ga-page"; }());</script>');
    }
}