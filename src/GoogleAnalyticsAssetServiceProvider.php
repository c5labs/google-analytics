<?php
/**
 * Google Analytics Asset Service Provider.
 *
 * @author   Oliver Green <oliver@c5labs.com>
 * @license  See attached license file
 */
namespace Concrete\Package\GoogleAnalytics\Src;

use Concrete\Core\Asset\Asset;
use Concrete\Core\Asset\AssetList;
use Concrete\Core\Foundation\Service\Provider;
use Concrete\Core\Package\Package;
use Core;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Demo Helper Service Provider.
 */
class GoogleAnalyticsAssetServiceProvider extends Provider
{
    public function register()
    {
        $al = AssetList::getInstance();
        $helper = Core::make('google-analytics.helper');
        $package = Package::getByHandle($helper->getPackageHandle());

        $al->register(
            'javascript',
            'ga-embed-api/core',
            'assets/bundle.min.js',
            [
                'version' => '0.9.0',
                'position' => Asset::ASSET_POSITION_FOOTER,
                'minify' => true,
                'combine' => true,
            ],
            $package
        );

        $al->register(
            'javascript',
            'google-analytics/dashboard-settings',
            'assets/settings.min.js',
            [
                'version' => '0.9.0',
                'position' => Asset::ASSET_POSITION_FOOTER,
                'minify' => true,
                'combine' => true,
            ],
            $package
        );

        $al->register(
            'javascript',
            'google-analytics/dashboard-overview',
            'assets/overview.min.js',
            [
                'version' => '0.9.0',
                'position' => Asset::ASSET_POSITION_FOOTER,
                'minify' => true,
                'combine' => true,
            ],
            $package
        );

        $al->register(
            'javascript',
            'google-analytics/toolbar-button',
            'assets/toolbar-button.min.js',
            [
                'version' => '0.9.0',
                'position' => Asset::ASSET_POSITION_FOOTER,
                'minify' => true,
                'combine' => false,
            ],
            $package
        );

        $al->register(
            'css',
            'google-analytics/core',
            'assets/bundle.min.css',
            [
                'version' => '0.9.0',
                'position' => Asset::ASSET_POSITION_HEADER,
                'minify' => true,
                'combine' => true,
            ],
            $package
        );

        $al->register(
            'javascript-inline',
            'ga-embed-api/config',
            $helper->getJavascriptConfigString(),
            [
                'version' => '0.9.0',
                'position' => Asset::ASSET_POSITION_FOOTER,
                'minify' => true,
                'combine' => true,
            ],
            $package
        );

        $al->registerGroup('google-analytics/core', [
            ['javascript-inline', 'ga-embed-api/config'],
            ['javascript', 'ga-embed-api/core'],
            ['css', 'google-analytics/core'],
        ]);
    }
}
