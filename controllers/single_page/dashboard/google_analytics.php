<?php
/**
 * Google Analytics Dashboard Overview Page Controller
 *
 * @author   Oliver Green <oliver@c5labs.com>
 * @license  See attached license file
 */
namespace Concrete\Package\GoogleAnalytics\Controller\SinglePage\Dashboard;

use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\Page\Page;
use Core;

class GoogleAnalytics extends DashboardPageController
{
    /**
     * GA Helper instance.
     * 
     * @var GoogleAnalyticsHelper
     */
    public $helper;

    /**
     * GA API client instance.
     * 
     * @var GoogleAnalyticsApiClient
     */
    protected $api;

    /**
     * Constructor.
     * 
     * @param \Concrete\Core\Page\Page $c [description]
     */
    public function __construct(Page $c)
    {
        parent::__construct($c);

        $this->helper = Core::make('google-analytics.helper');

        $this->api = Core::make('google-analytics.client');
    }

    /**
     * Setup the view template.
     * 
     * @return void
     */
    public function view()
    {
        $config = $this->helper->getConfiguration();

        // Get the current profile data from the API and give it to the view.
        if ($this->api->hasCurrentAccessToken()) {
            $resource_path = sprintf(
                '/management/accounts/%s/webproperties/%s/profiles/%s', 
                $config['account_id'], $config['property_id'], $config['profile_id']
            );
            $profile = $this->api->resource($resource_path);
            $this->set('profile', $profile);
        }

        $this->set('config', $config);
        $this->set('pageTitle', t('Google Analytics - 30 Day Overview'));

        // Require our assets & add the header items.
        $this->helper->queueCoreAssets($this);
        $this->requireAsset('javascript', 'google-analytics/dashboard-overview');

        // Add a classname to the body tag to allow proper scoping of CSS selectors.
        $this->addFooterItem('<script>(function() { var element = document.getElementsByTagName("body")[0]; element.className += " ga-page"; }());</script>');
    }
}