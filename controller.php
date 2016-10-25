<?php
/**
 * Google Analytics Controller File.
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
    protected $appVersionRequired = '5.7.5';

    /**
     * Does the package provide a full content swap?
     * This feature is often used in theme packages to install 'sample' content on the site.
     *
     * @see https://goo.gl/C4m6BG
     * @var bool
     */
    protected $pkgAllowsFullContentSwap = false;

    /**
     * Does the package provide thumbnails of the files 
     * imported via the full content swap above?
     *
     * @see https://goo.gl/C4m6BG
     * @var bool
     */
    protected $pkgContentProvidesFileThumbnails = false;

    /**
     * Should we remove 'Src' from classes that are contained 
     * ithin the packages 'src/Concrete' directory automatically?
     *
     * '\Concrete\Package\MyPackage\Src\MyNamespace' becomes '\Concrete\Package\MyPackage\MyNamespace'
     *
     * @see https://goo.gl/4wyRtH
     * @var bool
     */
    protected $pkgAutoloaderMapCoreExtensions = false;

    /**
     * Package class autoloader registrations
     * The package install helper class, included with this boilerplate, 
     * is activated by default.
     *
     * @see https://goo.gl/4wyRtH
     * @var array
     */
    protected $pkgAutoloaderRegistries = [
        //'src/MyVendor/Statistics' => '\MyVendor\ConcreteStatistics'
    ];

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
    protected $pkgVersion = '0.9.0';

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

        $this->registerAssets();

        // Add custom logic here that needs to be executed during CMS boot, things
        // such as registering services, assets, etc.
    }

    protected function registerAssets()
    {
        $al = \Concrete\Core\Asset\AssetList::getInstance();

        $al->register(
            'javascript',
            'ga-embed-api/core',
            'assets/bundle.min.js',
            [
                'version' => '0.9.0',
                'position' => \Concrete\Core\Asset\Asset::ASSET_POSITION_FOOTER,
                'minify' => true,
                'combine' => true,
            ],
            $this
        );

        $al->register(
            'javascript',
            'google-analytics/dashboard-settings',
            'assets/settings.min.js',
            [
                'version' => '0.9.0',
                'position' => \Concrete\Core\Asset\Asset::ASSET_POSITION_FOOTER,
                'minify' => true,
                'combine' => true,
            ],
            $this
        );

        $al->register(
            'javascript',
            'google-analytics/dashboard-overview',
            'assets/overview.min.js',
            [
                'version' => '0.9.0',
                'position' => \Concrete\Core\Asset\Asset::ASSET_POSITION_FOOTER,
                'minify' => true,
                'combine' => true,
            ],
            $this
        );

        $al->register(
            'javascript',
            'google-analytics/toolbar-button',
            'assets/toolbar-button.min.js',
            [
                'version' => '0.9.0',
                'position' => \Concrete\Core\Asset\Asset::ASSET_POSITION_FOOTER,
                'minify' => true,
                'combine' => false,
            ],
            $this
        );

        $al->register(
            'css',
            'google-analytics/core',
            'assets/bundle.min.css',
            [
                'version' => '0.9.0',
                'position' => \Concrete\Core\Asset\Asset::ASSET_POSITION_HEADER,
                'minify' => true,
                'combine' => true,
            ],
            $this
        );

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

        // Install the dashboard config page.
        $sp = \Concrete\Core\Page\Single::add('/dashboard/system/seo/google-analytics', $pkg);
        $sp->update([
            'cName' => 'Google Analytics',
        ]);

        // Install the dashboard overview page.
        $helper = Core::make('google-analytics.helper');
        $helper->enableDashboardOverview();

        return $pkg;
    }

    /**
     * The packages upgrade routine.
     * 
     * @return void
     */
    public function upgrade()
    {
        // Add your custom logic here that needs to be executed BEFORE package install.

        parent::upgrade();

        // Add your custom logic here that needs to be executed AFTER package upgrade.
    }

    /**
     * The packages uninstall routine.
     * 
     * @return void
     */
    public function uninstall()
    {
        // Add your custom logic here that needs to be executed BEFORE package uninstall.

        parent::uninstall();

        // Add your custom logic here that needs to be executed AFTER package uninstall.
    }
}
