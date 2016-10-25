<?php
/**
 * Google Analytics Helper Class.
 *
 * @author   Oliver Green <oliver@c5labs.com>
 * @license  See attached license file
 */
namespace Concrete\Package\GoogleAnalytics\Src;

use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Package\Package;
use Concrete\Core\Page\Page;
use Concrete\Core\Permission\Checker as Permissions;
use Concrete\Core\User\User;
use Core;
use View;

defined('C5_EXECUTE') or die('Access Denied.');

class GoogleAnalyticsHelper
{
    /**
     * Configuration instance.
     *
     * @var Repository
     */
    protected $config;

    /**
     * Constructor.
     *
     * @param Repository $config
     */
    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    /**
     * Get this packages handle.
     *
     * @return string
     */
    public function getPackageHandle()
    {
        return 'google-analytics';
    }

    /**
     * Get the addons default configuration.
     *
     * @param array $additional_defaults
     *
     * @return array
     */
    public function getDefaultConfiguration($additional_defaults = [])
    {
        $defaults = [
            'show_toolbar_button' => true, 'no_track_groups' => [],
            'enable_dashboard_overview' => true,
        ];

        return array_merge_recursive($defaults, $additional_defaults);
    }

    /**
     * Get the current configuration.
     *
     * @param array $defaults
     *
     * @return array
     */
    public function getConfiguration($defaults = [])
    {
        return $this->config->get('concrete.seo.analytics.google', $defaults);
    }

    /**
     * Save a data array to configuration.
     *
     * @param array $data
     * @param bool  $merge
     * @param array $defaults
     *
     * @return bool
     */
    public function saveConfiguration(array $data, $merge = true, $defaults = [])
    {
        if ($merge) {
            $existing = $this->getConfiguration($defaults);
            $data = array_merge($existing, $data);
        }

        return $this->config->save('concrete.seo.analytics.google', $data);
    }

    /**
     * Save a value to a specific configuration key.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return bool
     */
    public function saveConfigurationKey($key, $value)
    {
        return $this->config->save('concrete.seo.analytics.google.'.$key, $value);
    }

    /**
     * Save an array of keys & values to configuration.
     * 
     * @param array $data
     */
    public function saveConfigurationKeys(array $data)
    {
        foreach ($data as $key => $value) {
            $this->saveConfigurationKey($key, $value);
        }
    }

    /**
     * Remove the current token, account, profile & property configuration.
     */
    public function forgetAccount()
    {
        $data = $this->getConfiguration();

        unset($data['oauth_token']);
        unset($data['profile_id']);
        unset($data['account_id']);
        unset($data['property_id']);

        $this->saveConfiguration($data, false);
    }

    /**
     * Get the dashboard settings page path.
     * 
     * @return string
     */
    public function getDashboardSettingsPagePath()
    {
        return '/dashboard/system/seo/google-analytics';
    }

    /**
     * Get the dashboard overview page path.
     * 
     * @return string
     */
    public function getDashboardOverviewPagePath()
    {
        return '/dashboard/google-analytics';
    }

    /**
     * Get the dashboard settings page URL.
     * 
     * @return string
     */
    public function getDashboardSettingsPageUrl()
    {
        return View::url($this->getDashboardSettingsPagePath());
    }

    /**
     * Get the dashboard overview page URL.
     * 
     * @return string
     */
    public function getDashboardOverviewPageUrl()
    {
        return View::url($this->getDashboardOverviewPagePath());
    }

    /**
     * Install the configuration page.
     */
    public function installConfigurationPage()
    {
        $package = Package::getByHandle($this->getPackageHandle());

        $sp = \Concrete\Core\Page\Single::add($this->getDashboardSettingsPagePath(), $package);
        $sp->update([
            'cName' => t('Google Analytics'),
        ]);
    }

    /**
     * Is the dashboard overview page enabled?
     * 
     * @return bool
     */
    public function isDashboardOverviewEnabled()
    {
        $page = Page::getByPath($this->getDashboardOverviewPagePath());

        if ($page->getCollectionID()) {
            return true;
        }

        return false;
    }

    /**
     * Disable the dashboard overview page.
     */
    public function disableDashboardOverview()
    {
        $page = Page::getByPath($this->getDashboardOverviewPagePath());

        if ($page->getCollectionID()) {
            $page->delete();
        }
    }

    /**
     * Enable the dashboard overview page.
     */
    public function enableDashboardOverview()
    {
        $page_path = $this->getDashboardOverviewPagePath();
        $page = Page::getByPath($page_path);

        if (!$page->getCollectionID()) {
            $package = Package::getByHandle($this->getPackageHandle());
            $sp = \Concrete\Core\Page\Single::add($page_path, $package);
            $sp->update([
                'cName' => t('Google Analytics Overview'),
            ]);
        }
    }

    /**
     * Can the current user view the dashboard overview page?
     * 
     * @return bool
     */
    public function canViewAnalytics()
    {
        $page = Page::getByPath($this->getDashboardOverviewPagePath());

        if (!$page->getCollectionID()) {
            return true;
        }

        if (!\User::isLoggedIn()) {
            return false;
        }

        $permissions = new Permissions($page);

        return $permissions->canView();
    }

    /**
     * Can the current user view the toolbar button?
     */
    public function canViewToolbarButton()
    {
        $config = $this->getConfiguration();

        return $this->canViewAnalytics() && !empty($config['show_toolbar_button']);
    }

    /**
     * Queue the addons core assets to a view.
     * 
     * @param mixed $o
     */
    public function queueCoreAssets($o)
    {
        $config = $this->getConfiguration();

        $o->requireAsset('javascript', 'ga-embed-api/core');
        $o->requireAsset('css', 'google-analytics/core');

        $o->addFooterItem(sprintf(
            '<script>var ga_access_token = %s, ga_profile_id = "%s";</script>',
            json_encode($config['oauth_token']),
            $config['profile_id']
        ));
    }

    /**
     * Guess the best profile to use from a list of profiles 
     * by looking at the current host name.
     * 
     * @param array $profiles
     *
     * @return array $profile
     */
    public function bestGuessProfile($profiles)
    {
        $likenesses = [];

        foreach ($profiles as $key => $profile) {
            similar_text($_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'], $profile['websiteUrl'], $percent);
            $likenesses[(string) $percent] = $key;
        }

        $most_similar = max(array_keys($likenesses));

        return $profiles[$likenesses[$most_similar]];
    }

    /**
     * Is tracking enabled in the configuration?
     * 
     * @return bool
     */
    public function isTrackingEnabled()
    {
        $config = $this->getConfiguration();

        return !empty($config['enable_tracking_code']);
    }

    /**
     * Should we track a specific user?
     * 
     * @param User $user
     *
     * @return bool
     */
    public function shouldTrack($user)
    {
        $groups = (new User())->getUserGroups();
        $config = $this->getConfiguration();

        $matches = array_intersect($groups, $config['no_track_groups']);

        return 0 === count($matches);
    }

    /**
     * Get the tracking code for the configured property.
     * 
     * @return string
     */
    public function getTrackingCode()
    {
        $config = $this->getConfiguration();

        return <<<EOT
<!-- Google Analytics Tracking Code !-->
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

  ga('create', '$config[property_id]', 'auto');
  ga('send', 'pageview');

</script>
EOT;
    }
}
