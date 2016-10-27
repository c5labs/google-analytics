<?php
/**
 * Google Analytics Settings Page Controller.
 *
 * @author   Oliver Green <oliver@c5labs.com>
 * @license  See attached license file
 */
namespace Concrete\Package\GoogleAnalytics\Controller\SinglePage\Dashboard\System\Seo;

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
    protected $helper;

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
     * @param string $status
     */
    public function view($status = '')
    {
        $form_helper = Core::make('helper/form');

        // Show the successful save messages.
        if ('token-saved' === $status) {
            $this->set('message', t('Access token saved.'));
        } elseif ('settings-saved' === $status) {
            $this->set('message', t('Settings saved.'));
        }

        // If the user ha authorized the addon with Google 
        // give the account and profile list to the view.
        if ($this->api->hasCurrentAccessToken()) {
            $account = $this->api->user();
            $this->set('account', $account);

            $profiles = $this->api->resource('/management/accounts/~all/webproperties/~all/profiles');
            $this->set('profiles', $profiles);
        }

        // Give the view the available group list to the view.
        $groups = new \GroupList();
        $groups->includeAllGroups();
        $this->set('groups', $groups->get());

        // Load the required assets.
        $this->requireAsset('google-analytics/core');
        $this->requireAsset('javascript', 'google-analytics/dashboard-settings');
        $this->requireAsset('select2');

        $this->set('config', $this->helper->getConfiguration());
        $this->set('fh', $form_helper);
        $this->set('pageTitle', t('Google Analytics'));
    }

    /**
     * Save Token
     * Initial form post after authorising account.
     */
    public function save_token()
    {
        if ($this->isPost()) {
            // Validate token.
            if (!$this->token->validate('save_token')) {
                $this->error->add($this->token->getErrorMessage());
            }

            $data = array_only($this->post('concrete')['seo']['ga'], ['auth_code']);

            if (!isset($data['auth_code'])) {
                $this->error->add(t('The oAuth token is not valid.'));
            }

            // Exchange the auth code for an access token.
            $this->api->getAccessToken('authorization_code', [
                'code' => $data['auth_code'],
            ]);

            // If the exchange was not sucessful.
            if (!$this->api->hasCurrentAccessToken()) {
                $this->error->add(t('Failed to exchange authorization token for access token.'));
            }

            // If the exchange was sucessful and we have a valid access token.
            else {
                // Get all of the profiles for the user.
                $profiles = $this->api->resource('/management/accounts/~all/webproperties/~all/profiles');

                // The authorised account has no profiles.
                if (0 === count($profiles['items'])) {
                    $this->error->add(t('This account has no Google Analytics profiles.'));
                }

                // Best guess the profile from the availble list and set the default the configuration.
                else {
                    $profile = $this->helper->bestGuessProfile($profiles['items']);

                    $config = $this->helper->getDefaultConfiguration([
                        'profile_id' => $profile['id'],
                        'account_id' => $profile['accountId'],
                        'property_id' => $profile['webPropertyId'],
                    ]);

                    $this->helper->saveConfigurationKeys($config);
                }
            }

            // If we don't have any errors redirect to the settings form, otherwise 
            // show the authorisation form and the errors.
            if (!$this->error->has()) {
                $this->api->saveCurrentAccessToken();

                return $this->redirect($this->helper->getDashboardSettingsPagePath(), 'token-saved');
            }
        }

        $this->view();
    }

    /**
     * Save the configuration form.
     */
    public function save_configuration()
    {
        if ($this->isPost()) {
            // Validate token.
            if (!$this->token->validate('save_configuration')) {
                $this->error->add($this->token->getErrorMessage());
            }

            if (!$this->error->has()) {
                $defaults = $this->helper->getDefaultConfiguration();
                $keys = [
                    'show_toolbar_button', 'enable_dashboard_overview', 'property_id',
                    'profile_id', 'account_id', 'enable_tracking_code', 'no_track_groups',
                ];
                $data = array_only($_POST['concrete']['seo']['ga'], $keys);

                // Reset the check boxes & excluded user groups configuration values to 
                // false if there is no value recieved in the POST array.
                $data['show_toolbar_button'] = isset($data['show_toolbar_button']);
                $data['enable_dashboard_overview'] = isset($data['enable_dashboard_overview']);
                $data['enable_tracking_code'] = isset($data['enable_tracking_code']);
                $data['no_track_groups'] = (0 === count($data['no_track_groups'])) ? [] : $data['no_track_groups'];

                if ($data['enable_dashboard_overview']) {
                    $this->helper->enableDashboardOverview();
                } else {
                    $this->helper->disableDashboardOverview();
                }

                $this->helper->saveConfiguration($data, true, $defaults);

                return $this->redirect($this->helper->getDashboardSettingsPagePath(), 'settings-saved');
            }
        }
        $this->view();
    }

    /**
     * Unassociate an Google account from the addon.
     */
    public function remove_account()
    {
        // Validate token.
        if (!$this->token->validate('remove_account')) {
            $this->error->add($this->token->getErrorMessage());
        }

        if (!$this->error->has()) {
            $this->helper->forgetAccount();

            return $this->redirect($this->helper->getDashboardSettingsPagePath());
        }

        $this->view();
    }
}
