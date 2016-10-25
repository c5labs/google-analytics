<?php if (empty($profile)) { ?>
<div class="ga-content no-profile">
    <div class="row">
        <div class="col-sm-12">
            <div class="boxed no-profile">
                <h3><?php echo t('No profile configured'); ?></h3>
                <p><?php echo t('To view your analytics here you need to setup the addon with your Google Analytics profile'); ?>.</p>
                <a href="<?php echo $this->controller->helper->getDashboardSettingsPageUrl(); ?>" class="btn btn-primary"><?php echo t('Get Started'); ?></a>
            </div>
        </div>
    </div>
<?php } else { ?>
<div id="gaContent" class="ga-content">
    <div class="row">
        <div class="col-sm-12">
            <div class="boxed line-chart">
                <h3><?php echo t('Site Traffic'); ?></h3>
                <span class="sub-title"><?php echo t('Sessions vs. Users'); ?> - <?php echo $profile['websiteUrl']; ?></span>
                <div class="chart-container" id="siteTrafficChart">
                    <div class="loading"><span><i class="fa fa-spin fa-refresh"></i><?php echo t('Fetching data'); ?></span></div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="boxed hero-metric">
                <div id="activeUsersMarker" class="active-marker off"></div>
                <div class="chart-container" id="activeUsers">
                    <div class="loading"><span><i class="fa fa-spin fa-refresh"></i><?php echo t('Fetching data'); ?></span></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="boxed hero-metric">
                <div class="chart-container" id="bounceRate">
                    <div class="loading"><span><i class="fa fa-spin fa-refresh"></i><?php echo t('Fetching data'); ?></span></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="boxed hero-metric">
                <div class="chart-container" id="avgVisitDuration">
                    <div class="loading"><span><i class="fa fa-spin fa-refresh"></i><?php echo t('Fetching data'); ?></span></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="boxed hero-metric">
                <div class="chart-container" id="avgPageLoad">
                    <div class="loading"><span><i class="fa fa-spin fa-refresh"></i><?php echo t('Fetching data'); ?></span></div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="boxed table-chart">
                <h3><?php echo t('Top 10 Pages'); ?></h3>
                <div class="chart-container" id="topPagesChart">
                    <div class="loading"><span><i class="fa fa-spin fa-refresh"></i><?php echo t('Fetching data'); ?></span></div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="boxed table-chart">
                <h3><?php echo t('Traffic Sources'); ?></h3>
                <span class="sub-title"><?php echo t('Top 20 Referrers'); ?></span>
                <div class="chart-container" id="trafficSourcesChart">
                    <div class="loading"><span><i class="fa fa-spin fa-refresh"></i><?php echo t('Fetching data'); ?></span></div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php } ?>
<div class="row">
    <div class="col-sm-12 c5labs-tagline">
        A <a href="https://c5labs.com"><img src="<?php echo DIR_REL; ?>/packages/google-analytics/assets/c5labs.png" alt="c5labs.com"></a> creation
    </div>
</div>
<script>
    var ga_access_token = "<?php echo $config["oauth_token"]; ?>",
        ga_profile_id = '<?php echo $config["profile_id"]; ?>',
        component_translations = {
            bounceRate: '<?php echo t("Bounce Rate"); ?>',
            activeUsers: '<?php echo t("Active Users"); ?>',
            visitDuration: '<?php echo t("Visit Duration (mins)"); ?>',
            pageLoadTime: '<?php echo t("Page Load Time"); ?>',
        };
</script>