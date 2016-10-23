<?php
namespace Concrete\Package\GoogleAnalytics\Controller\SinglePage\Dashboard\System\Seo;

use Core;
use Concrete\Core\Page\Controller\DashboardPageController;

class GoogleAnalytics extends DashboardPageController
{
    public function view($status = '')
    {
        $this->requireAsset('javascript', 'ga-embed-api/core');
        $this->requireAsset('javascript', 'ga-embed-api/dashboard-settings');

        $api = Core::make('google-analytics.client');
        $config = Core::make(\Concrete\Core\Config\Repository\Repository::class);
        $form_helper = Core::make('helper/form');
        $config = $config->get('concrete.seo.analytics.google', []);

        // Show the successful save message.
        if ('token-saved' === $status) {
            $this->set('message', ('Access token saved.'));
        } elseif ('settings-saved' === $status) {
            $this->set('message', ('Settings saved.'));
        }

        if ($api->hasCurrentAccessToken()) {
            // Get the current account.
            $account = $api->user();
            $this->set('account', $account);

            // Get all of the profiles.
            $profiles = $api->resource('/management/accounts/~all/webproperties/~all/profiles');
            $this->set('profiles', $profiles);
        }

        $this->set('config', $config);
        $this->set('fh', $form_helper);
        $this->set('pageTitle', t('Google Analytics'));
    }

    public function save_token()
    {
        if ($this->isPost()) {
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
                    $likenesses = [];
                    
                    foreach ($profiles['items'] as $key => $profile) {
                        similar_text($_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'], $profile['websiteUrl'], $percent); 
                        $likenesses[(string) $percent] = $key;
                    }

                    $most_similar = max(array_keys($likenesses));

                    $config = Core::make(\Concrete\Core\Config\Repository\Repository::class);
                    $profile = $profiles['items'][$likenesses[$most_similar]];
                    $config->save('concrete.seo.analytics.google.profile_id', $profile['id']);
                    $config->save('concrete.seo.analytics.google.account_id', $profile['accountId']);
                    $config->save('concrete.seo.analytics.google.property_id', $profile['webPropertyId']);
                }
            }

            if (! $this->error->has()) {
                // Save the configuration.
                $api->saveCurrentAccessToken();

                return $this->redirect('/dashboard/system/seo/google-analytics', 'token-saved');
            }
        }

        $this->view();
    }

    public function save_configuration()
    {
        if ($this->isPost()) {
            // Validate token.
            if (! $this->token->validate('save_configuration')) {
                $this->error->add($this->token->getErrorMessage());
            }

            if (! $this->error->has()) {                
                $config = $this->app->make(\Illuminate\Config\Repository::class);

                $data = $config->get('concrete.seo.analytics.google');
                $data = array_merge($data, array_only($_POST['concrete']['seo']['ga'], ['profile_id', 'account_id', 'property_id']));

                $config->save('concrete.seo.analytics.google', $data);

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
            $config = $this->app->make(\Illuminate\Config\Repository::class);

            $data = $config->get('concrete.seo');
            unset($data['analytics']['google']);

            $config->save('concrete.seo', $data);

            return $this->redirect('/dashboard/system/seo/google-analytics');
        }

        $this->view();
    }
}