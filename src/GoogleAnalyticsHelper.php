<?php
/**
 * Demo Helper Service Provider File.
 *
 * @author   Oliver Green <oliver@c5labs.com>
 * @license  See attached license file
 */
namespace Concrete\Package\GoogleAnalytics\Src;

use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Package\Package;
use Concrete\Core\Page\Page;
use Concrete\Core\Permission\Checker as Permissions;
use Core;
use View;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Demo Helper Service Provider.
 */
class GoogleAnalyticsHelper
{
    protected $config;

    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    public function getPackageHandle()
    {
        return 'google-analytics';
    }

    public function getConfiguration($defaults = [])
    {
        return $this->config->get('concrete.seo.analytics.google', $defaults);
    }

    public function saveConfiguration($data, $merge = true, $defaults = [])
    {
        if ($merge) {
            $existing = $this->getConfiguration($defaults);
            $data = array_merge($existing, $data);
        }

        $this->config->save('concrete.seo.analytics.google', $data);
    }

    public function saveConfigurationKey($key, $value)
    {
        $this->config->save('concrete.seo.analytics.google.'.$key, $value);
    }

    public function saveConfigurationKeys($data)
    {
        foreach ($data as $key => $value) {
            $this->saveConfigurationKey($key, $value);
        }
    }

    public function forgetAccount()
    {
        $data = $this->getConfiguration();

        unset($data['oauth_token']);
        unset($data['profile_id']);
        unset($data['account_id']);
        unset($data['property_id']);

        $this->saveConfiguration($data, false);
    }

    public function getDashboardSettingsPath()
    {
        return '';
    }

    public function getDashboardOverviewPagePath()
    {
        return '/dashboard/google-analytics';
    }

    public function getDashboardSettingsUrl()
    {
        return View::url($this->getDashboardSettingsPath());
    }

    public function getDashboardOverviewPageUrl()
    {
        return View::url($this->getDashboardOverviewPagePath());
    }

    public function isDashboardOverviewEnabled()
    {
        $page = Page::getByPath($this->getDashboardOverviewPagePath());

        if ($page->getCollectionID()) {
            return true;
        }

        return false;
    }

    public function disableDashboardOverview()
    {
        $page = Page::getByPath($this->getDashboardOverviewPagePath());

        if ($page->getCollectionID()) {
            $page->delete();
        }
    }

    public function enableDashboardOverview()
    {
        $page_path = $this->getDashboardOverviewPagePath();
        $page = Page::getByPath($page_path);

        if (! $page->getCollectionID()) {
            $package = Package::getByHandle($this->getPackageHandle());
            $sp = \Concrete\Core\Page\Single::add($page_path, $package);
            $sp->update([
                'cName' => 'Google Analytics Overview',
            ]);
        }
    }

    public function canViewAnalytics()
    {
        $page = Page::getByPath($this->getDashboardOverviewPagePath());

        if (! $page->getCollectionID()) {
            return true;
        }

        if (! \User::isLoggedIn()) {
            return false; 
        }

        $permissions = new Permissions($page);

        return $permissions->canView();
    }

    public function canViewToolbarButton()
    {
        $config = $this->getConfiguration();

        return $this->canViewAnalytics() && ! empty($config['show_toolbar_button']);
    }

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

    public function guessBestProfile($profiles)
    {
        $likenesses = [];
                    
        foreach ($profiles as $key => $profile) {
            similar_text($_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'], $profile['websiteUrl'], $percent); 
            $likenesses[(string) $percent] = $key;
        }

        $most_similar = max(array_keys($likenesses));

        return $profiles['items'][$likenesses[$most_similar]];
    }

    public function isTrackingEnabled()
    {
        $config = $this->getConfiguration();
        
        return isset($config['enable_tracking_code']);
    }

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