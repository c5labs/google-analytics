<?php
/**
 * Google Analytics Package Controller File.
 *
 * @author   Oliver Green <oliver@c5labs.com>
 * @license  See attached license file
 */
namespace Concrete\Package\GoogleAnalytics;

use Core;
use Concrete\Core\Foundation\Service\ProviderList;
use Concrete\Core\Package\Package;
use Illuminate\Filesystem\Filesystem;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Package Controller Class.
 *
 * Adds Google Analytics overview & tracking code to your site.
 *
 * @author   Oliver Green <oliver@c5labs.com>
 * @license  See attached license file
 */
class Controller extends Package
{
    /**
     * Minimum version of concrete5 required to use this package.
     * 
     * @var string
     */
    protected $appVersionRequired = '5.7.4';

    /**
     * The packages handle.
     * Note that this must be unique in the 
     * entire concrete5 package ecosystem.
     * 
     * @var string
     */
    protected $pkgHandle = 'google-analytics';

    /**
     * The packages version.
     * 
     * @var string
     */
    protected $pkgVersion = '0.9.4';

    /**
     * The packages name.
     * 
     * @var string
     */
    protected $pkgName = 'Google Analytics';

    /**
     * The packages description.
     * 
     * @var string
     */
    protected $pkgDescription = 'Adds Google Analytics overview & tracking code to your site.';

    /**
     * Package service providers to register.
     * 
     * @var array
     */
    protected $providers = [
        'Concrete\Package\GoogleAnalytics\Src\GoogleAnalyticsServiceProvider',
        'Concrete\Package\GoogleAnalytics\Src\GoogleAnalyticsAssetServiceProvider',
    ];

    /**
     * Register the packages defined service providers.
     * 
     * @return void
     */
    protected function registerServiceProviders()
    {
        $list = new ProviderList(Core::getFacadeRoot());

        foreach ($this->providers as $provider) {
            $list->registerProvider($provider);

            if (method_exists($provider, 'boot')) {
                Core::make($provider)->boot($this);
            }
        }
    }

    /**
     * Boot the packages composer autoloader if it's present.
     * 
     * @return void
     */
    protected function bootComposer()
    {
        $filesystem = new Filesystem();
        $path = __DIR__.'/vendor/autoload.php';

        if ($filesystem->exists($path)) {
            $filesystem->getRequire($path);
        }
    }

    /**
     * The packages on start hook that is fired as the CMS is booting up.
     * 
     * @return void
     */
    public function on_start()
    {
        // Boot composer
        $this->bootComposer();

        // Register defined service providers
        $this->registerServiceProviders();
    }

    /**
     * The packages install routine.
     * 
     * @return \Concrete\Core\Package\Package
     */
    public function install()
    {
        $this->bootComposer();

        $pkg = parent::install();

        $this->registerServiceProviders();

        // Install the dashboard overview & settings pages.
        $helper = Core::make('google-analytics.helper');
        $helper->enableDashboardOverview();
        $helper->installConfigurationPage();

        return $pkg;
    }

    /**
     * The packages upgrade routine.
     * 
     * @return void
     */
    public function upgrade()
    {
        parent::upgrade();
    }

    /**
     * The packages uninstall routine.
     * 
     * @return void
     */
    public function uninstall()
    {
        parent::uninstall();
    }
}
