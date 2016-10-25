<?php
namespace Concrete\Package\GoogleAnalytics\Controller\SinglePage\Dashboard\System\Seo;

use Core;
use Concrete\Core\Page\Controller\DashboardPageController;

class GoogleAnalytics extends DashboardPageController
{
    protected $helper;

    public function __construct(\Concrete\Core\Page\Page $c)
    {
        parent::__construct($c);

        $this->helper = Core::make('google-analytics.helper');
    }

    public function view($status = '')
    {
        $api = Core::make('google-analytics.client');
        $form_helper = Core::make('helper/form');

        // Show the successful save message.
        if ('token-saved' === $status) {
            $this->set('message', t('Access token saved.'));
        } elseif ('settings-saved' === $status) {
            $this->set('message', t('Settings saved.'));
        }

        if ($api->hasCurrentAccessToken()) {
            // Get the current account.
            $account = $api->user();
            $this->set('account', $account);

            // Get all of the profiles.
            $profiles = $api->resource('/management/accounts/~all/webproperties/~all/profiles');
            $this->set('profiles', $profiles);
        }

        $this->set('config', $this->helper->getConfiguration());
        $this->set('fh', $form_helper);
        $this->set('pageTitle', t('Google Analytics'));

        $groups = new \GroupList();
        $groups->includeAllGroups();
        $this->set('groups', $groups->get());

        $this->helper->queueCoreAssets($this);
        $this->requireAsset('javascript', 'google-analytics/dashboard-settings');
        $this->requireAsset('select2');
    }

    public function save_token()
    {
        if ($this->isPost()) {
            $this->helper = Core::make('google-analytics.helper');

            // Validate token.
            if (! $this->token->validate('save_token')) {
                $this->error->add($this->token->getErrorMessage());
            }

            $data = array_only($this->post('concrete')['seo']['ga'], ['oauth_token']);

            if (! isset($data['oauth_token'])) {
                $this->error->add(t('The oAuth token is not valid.'));
            }

            $api = Core::make('google-analytics.client');
            $api->getAccessToken('authorization_code', [
                'code' => $data['oauth_token']
            ]);

            if (! $api->hasCurrentAccessToken()) {
                $this->error->add(t('Failed to exchange authorization token for access token.'));
            } else {
                // Get all of the profiles
                $profiles = $api->resource('/management/accounts/~all/webproperties/~all/profiles');

                // Guess the profile if we don't have one set.
                if (0 === count($profiles)) {
                    $this->error->add(t('This account has no Google Analytics profiles.'));
                } else {
                    $profile = $this->helper->bestGuessProfile($profiles['items']);

                    $config = $this->helper->getDefaultConfiguration([
                        'profile_id' => $profile['id'],
                        'account_id' => $profile['accountId'],
                        'property_id' => $profile['webPropertyId']
                    ]);

                    $this->helper->saveConfigurationKeys($config);
                }
            }

            if (! $this->error->has()) {
                $api->saveCurrentAccessToken();
                return $this->redirect('/dashboard/system/seo/google-analytics', 'token-saved');
            }
        }

        $this->view();
    }

    public function save_configuration()
    {
        if ($this->isPost()) {
            $this->helper = Core::make('google-analytics.helper');

            // Validate token.
            if (! $this->token->validate('save_configuration')) {
                $this->error->add($this->token->getErrorMessage());
            }

            if (! $this->error->has()) {

                $defaults = $this->helper->getDefaultConfiguration();
                $keys = [
                    'show_toolbar_button', 'enable_dashboard_overview', 'property_id', 
                    'profile_id', 'account_id', 'enable_tracking_code', 'no_track_groups',
                ];
                $data = array_only($_POST['concrete']['seo']['ga'], $keys);

                $data['show_toolbar_button'] = isset($data['show_toolbar_button']);
                $data['enable_dashboard_overview'] = isset($data['enable_dashboard_overview']);
                $data['enable_tracking_code'] = isset($data['enable_tracking_code']);
                $data['no_track_groups'] = (! isset($data['no_track_groups']) || 0 === count($data['no_track_groups'])) ? [] : $data['no_track_groups'];

                if ($data['enable_dashboard_overview']) {
                    $this->helper->enableDashboardOverview();
                } else {
                    $this->helper->disableDashboardOverview();
                }

                $this->helper->saveConfiguration($data, true, $defaults);

                return $this->redirect('/dashboard/system/seo/google-analytics', 'settings-saved');
            }
        }
        $this->view();
    }

    public function remove_account()
    {
        // Validate token.
        if (! $this->token->validate('remove_account')) {
            $this->error->add($this->token->getErrorMessage());
        }

        if (! $this->error->has()) {
            $this->helper->forgetAccount();

            return $this->redirect('/dashboard/system/seo/google-analytics');
        }

        $this->view();
    }
}