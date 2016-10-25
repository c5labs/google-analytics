<?php
// We also need to deal with refreshing expired tokens
defined('C5_EXECUTE') or die("Access Denied.");
?>
<div class="ga-settings">
<?php if (! isset($profiles)) { ?>
<form id="authorizeForm" method="post" action="<?php echo $view->action('save_token'); ?>">
    <?php echo $this->controller->token->output('save_token'); ?>
    <fieldset>
        <legend>Let's Get Started</legend>
        <div class="form-group">
            <p>To enable this addon you need to authorize it to connect to your Google Analytics account. Click on the 'Get Access Token' button below to get an authorisation token, then copy it into the box below and click 'Save Token'.</p>
            <label class="control-label" style="margin-top: 10px;"><?php echo t('Access Token')?></label>
            <div class="row">
                <div class="col-xs-6">
                    <input id="configureAuthToken" name="concrete[seo][ga][auth_code]" class="form-control" value="<?php echo $config['auth_code']; ?>">
                </div>
                <div class="col-xs-6">
                    <a href="#" id="authorizeButton" class="btn btn-default">Get Access Token</a>
                </div>
            </div>
            <button id="saveTokenButton" class="btn btn-primary disabled" style="margin-top: 25px;" type="submit">Save Token</button>
        </div>
    </fieldset>
</form>
<script>var ga_auth_url = '<?php echo \Core::make('google-analytics.client')->getAuthorizationUrl(); ?>';</script>
<?php } else { ?>
<form id="configureForm" method="post" action="<?php echo $view->action('save_configuration'); ?>">
    <?php echo $this->controller->token->output('save_configuration'); ?>
    <fieldset>
        <legend>Tracking Code</legend>

        <div class="checkbox">
            <label for="enable_tracking_code">
            <input id="enable_tracking_code" name="concrete[seo][ga][enable_tracking_code]" type="checkbox" value="1" <?php echo (! empty($config['enable_tracking_code']) ? 'checked="checked"' : ''); ?>> Add tracking code for the selected profile to the site
            </label>
        </div>

        <div id="no_track_groups_wrapper" style="display: none;">
            <label>Do not track users that are in the groups</label>
            <select id="groups" name="concrete[seo][ga][no_track_groups][]" multiple="multiple" style="width: 50%; display: block;">
                <?php foreach ($groups as $group) { ?>
                    <option value="<?php echo $group->getGroupId(); ?>" <?php echo (in_array($group->getGroupId(), $config['no_track_groups'])) ? 'selected="selected"' : ''; ?>>
                        <?php echo $group->getGroupName(); ?>
                    </option>
                <?php } ?>
            </select>
            <script>
                $(function() {
                    $('#groups').select2();

                    $('#enable_tracking_code').change(function() {
                        var $ele = $('#no_track_groups_wrapper');
                        $ele.hide();

                        if ($(this).prop('checked')) {
                            $ele.show();
                        }
                    }).trigger('change');
                });
            </script>
        </div>
    </fieldset>

    <fieldset>
        <legend>Additional Settings</legend>

        <div class="checkbox">
            <label for="enable_dashboard_overview">
            <input id="enable_dashboard_overview" name="concrete[seo][ga][enable_dashboard_overview]" type="checkbox" value="1" <?php echo (! empty($config['enable_dashboard_overview']) ? 'checked="checked"' : ''); ?>> Enable the 'Google Analytics Overview' page in the dashboard
            </label>
        </div>

        <div class="checkbox">
            <label for="show_toolbar_button">
            <input id="show_toolbar_button" name="concrete[seo][ga][show_toolbar_button]" type="checkbox" value="1" <?php echo (! empty($config['show_toolbar_button']) ? 'checked="checked"' : ''); ?>> Show real time site visitor count in toolbar
            </label>
        </div>

    </fieldset>
    <fieldset>
        <legend>Analytics Account</legend>
        <label for="account">Google Account</label>
        <div style="margin-bottom: 20px;">
            <div style="float: left;">
                <img src="<?php echo $account['image']['url']; ?>" alt="<?php echo $account['displayName']; ?>" style="max-width: 100%;">
            </div>
            <div style="float: left; margin-left: 20px; margin-top: 5px;">
                <?php echo $account['displayName']; ?>
                <div style="color: #999;"><?php echo head($account['emails'])['value']; ?></div>
            </div>
            <div style="float: left; margin-left: 20px; margin-top: 5px;">
                <a class="btn btn-primary btn-sm" href="<?php echo $view->action('remove_account'); ?>?ccm_token=<?php echo $this->controller->token->generate('remove_account'); ?>">Remove Account</a>
            </div>
            <br style="float:none; clear: both;">
        </div>
        <label for="configureProfileId">Google Analytics Profile</label>
        <select id="configureProfileId" class="form-control" name="concrete[seo][ga][profile_id]">
            <?php foreach ($profiles['items'] as $profile) { ?>
                <option value="<?php echo $profile['id']; ?>" data-account-id="<?php echo $profile['accountId']; ?>" data-property-id="<?php echo $profile['webPropertyId']; ?>" <?php echo ($profile['id'] === $config['profile_id']) ? 'selected="selected"' : ''; ?>>
                    <?php echo $profile['websiteUrl']; ?>
                </option>
            <?php } ?>
        </select>
        <input id="configureAccountId" type="hidden" name="concrete[seo][ga][account_id]" value="<?php echo $config['account_id']; ?>">
        <input id="configurePropertyId" type="hidden" name="concrete[seo][ga][property_id]" value="<?php echo $config['property_id']; ?>">
    </fieldset>
    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <?php echo $interface->submit(t('Save'), 'url-form', 'right', 'btn-primary'); ?>
        </div>
    </div>
</form>
<?php } ?>
<div class="row">
    <div class="col-sm-12 c5labs-tagline">
        A <a href="https://c5labs.com"><img src="<?php echo DIR_REL; ?>/packages/google-analytics/assets/c5labs.png" alt="c5labs.com"></a> creation
    </div>
</div>
</div>
