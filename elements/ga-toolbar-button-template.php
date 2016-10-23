<script id="gaToolbarButtonTemplate" type="text/template">
    <li data-guide-toolbar-action="google-analytics" class="pull-right hidden-xs">
        <a href="<?php echo $dashboard_url; ?>" title="<?php echo t('Google Analytics Dashboard') ?>" style="border-right: none;">
            <div style="position: absolute; top: 50%; left: 0; right: 0; text-align: center; transform: translateY(-50%);">
            <i class="fa fa-refresh fa-spin" style="position: relative; top: 0; left: 0;"></i>
            <div id="gaActiveUsers" style="font-weight: bold; display: none;"></div>
            </div>
            <span class="ccm-toolbar-accessibility-title ccm-toolbar-accessibility-title-site-settings">
                <?php echo tc('toolbar', 'Google Analytics') ?>
            </span>
        </a>
    </li>
</script>