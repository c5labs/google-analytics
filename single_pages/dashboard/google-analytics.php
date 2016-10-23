<style>
    div#ccm-dashboard-content.container-fluid {
        padding-right: 97px;
    }

    div#ccm-dashboard-content>header {
        margin-right: -97px;
    }

    @media (max-width:992px) {
        div#ccm-dashboard-content.container-fluid {
            padding-right: 20px
        }
    }

    #ccm-dashboard-content {
        background-color: #f9f9f9;
    }

    .boxed {
        padding: 15px 30px;
        background-color: #fff;
        margin-bottom: 30px;
    }

    .boxed .sub-title {
        color: #999;
    }

    .boxed.hero-metric .chart-container,
    .boxed.table-chart .chart-container {
        margin-top: 20px; 
        margin-bottom: 20px;
    }

    .boxed.hero-metric {
        text-align: center;
    }

    .boxed.hero-metric .hero-metric-value {
        display:block;
        font-size: 3em;
    }

    .boxed.hero-metric .hero-sub-title {
        color: #999;
        font-size: .9em;
    }

    .chart-container .loading {
        min-height: 300px;
        text-align: center;
        font-size: 2em;
        font-weight: normal;
        color: #188ec5;
        position: relative;
    }

    .chart-container .loading span {
        display: block;
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        width: 100%;
    }

    .boxed.hero-metric .chart-container .loading {
        min-height: 80px;
        font-size: 1em;
    }

    .chart-container .loading .fa {
        display: block;
        margin-bottom: 5px;
        font-size: 1.5em;
    }

    .boxed .active-marker {
        border-radius: 50%;
        width: 10px;
        height: 10px;
        background: green;
        position: absolute;
        top: 15px;
        right: 30px;
        animation: flash 2s infinite;
    }

    @keyframes flash {
        0% { opacity: .3; }
        50% { opacity: 1; }
        100% { opacity: .3; }
    }


</style>
<div class="row">
    <div class="col-sm-12">
        <div class="boxed line-chart">
            <h3>Site Traffic</h3>
            <span class="sub-title">Sessions vs. Users - <?php echo $profile['websiteUrl']; ?></span>
            <div class="chart-container" id="siteTrafficChart">
                <div class="loading"><span><i class="fa fa-spin fa-refresh"></i>Fetching data</span></div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-3 col-md-6">
        <div class="boxed hero-metric">
            <div class="active-marker"></div>
            <div class="chart-container" id="activeUsers">
                <div class="loading"><span><i class="fa fa-spin fa-refresh"></i>Fetching data</span></div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="boxed hero-metric">
            <div class="chart-container" id="bounceRate">
                <div class="loading"><span><i class="fa fa-spin fa-refresh"></i>Fetching data</span></div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="boxed hero-metric">
            <div class="chart-container" id="avgVisitDuration">
                <div class="loading"><span><i class="fa fa-spin fa-refresh"></i>Fetching data</span></div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="boxed hero-metric">
            <div class="chart-container" id="avgPageLoad">
                <div class="loading"><span><i class="fa fa-spin fa-refresh"></i>Fetching data</span></div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-12">
        <div class="boxed table-chart">
            <h3>Top 10 Pages</h3>
            <div class="chart-container" id="topPagesChart">
                <div class="loading"><span><i class="fa fa-spin fa-refresh"></i>Fetching data</span></div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-12">
        <div class="boxed table-chart">
            <h3>Traffic Sources</h3>
            <span class="sub-title">Top 20 Referrers</span>
            <div class="chart-container" id="trafficSourcesChart">
                <div class="loading"><span><i class="fa fa-spin fa-refresh"></i>Fetching data</span></div>
            </div>
        </div>
    </div>
</div>
<script>
    var ga_access_token = "<?php echo $config["oauth_token"]["access_token"]; ?>",
        ga_profile_id = '<?php echo $config["profile_id"]; ?>';
</script>