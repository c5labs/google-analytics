<?php
namespace Concrete\Package\GoogleAnalytics\Controller\SinglePage\Dashboard;

use Core;
use Concrete\Core\Page\Controller\DashboardPageController;

class GoogleAnalytics extends DashboardPageController
{
    public function view()
    {
        $this->requireAsset('javascript', 'ga-embed-api/core');
        $this->requireAsset('javascript', 'ga-embed-api/dashboard-overview');

        $config = Core::make(\Concrete\Core\Config\Repository\Repository::class);
        $config = $config->get('concrete.seo.analytics.google', []);
        $this->set('config', $config);

        $api = Core::make('google-analytics.client');
        $resource_path = sprintf(
            '/management/accounts/%s/webproperties/%s/profiles/%s', 
            $config['account_id'], $config['property_id'], $config['profile_id']
        );
        $profile = $api->resource($resource_path);
        $this->set('profile', $profile);

        $this->set('pageTitle', t('Google Analytics - 30 Day Overview'));
    }
}