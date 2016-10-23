gapi.analytics.ready(function() {
    var charts = {};

    /**
    * Authorize the user with an access token obtained server side.
    */
    gapi.analytics.auth.authorize({
        'serverAuth': {
        'access_token': window.ga_access_token
        }
    });

    /**
    * Site Traffic
    */
    charts.traffic = {
        query: {
            'ids': 'ga:'+window.ga_profile_id,
            'start-date': '30daysAgo',
            'end-date': 'yesterday',
            'metrics': 'ga:sessions,ga:users',
            'dimensions': 'ga:date'
        },
        chart: {
            'container': 'siteTrafficChart',
            'type': 'LINE',
            'options': {
                'width': '100%'
            }
        }
    };

    /*
     * Referrals
     */
    charts.pages = {
        query: {
            'ids': 'ga:'+window.ga_profile_id, 
            'start-date': '30daysAgo',
            'end-date': 'yesterday',
            'metrics': 'ga:uniquePageviews',
            'dimensions': 'ga:pageTitle, ga:pagePath',
            'sort': '-ga:uniquePageviews',
            'max-results': 10,
        },
        chart: {
            'container': 'topPagesChart',
            'type': 'TABLE',
            'options': {
                'width': '100%'
            }
        }
    };

    /*
     * Referrals
     */
    charts.referrals = {
        query: {
            'ids': 'ga:'+window.ga_profile_id, 
            'start-date': '30daysAgo',
            'end-date': 'yesterday',
            'metrics': 'ga:users',
            'dimensions': 'ga:source, ga:referralPath',
            'sort': '-ga:users',
            'max-results': 20,
        },
        chart: {
            'container': 'trafficSourcesChart',
            'type': 'TABLE',
            'options': {
                'width': '100%'
            }
        }
    };

    /*
     * Active Users
     */
    charts.activeUsers = {
        component: 'ActiveUsers',
        opts: {
            'ids': 'ga:'+window.ga_profile_id,
            'container': 'activeUsers',
            'pollingInterval': 5
        }
    };

    /*
     * Bounce Rate
     */
    charts.bounceRate = {
        component: 'HeroMetric',
        opts: {
            'ids': 'ga:'+window.ga_profile_id,
            'start-date': '30daysAgo',
            'end-date': 'yesterday',
            'metrics': 'ga:bounceRate',
            'title': 'Bounce Rate',
            'container': 'bounceRate',
            'mutator': function(value) { return value+'%'; },
        }
    };

    /*
     * Session Duration
     */
    charts.sessionDuration = {
        component: 'HeroMetric',
        opts: {
            'ids': 'ga:'+window.ga_profile_id,
            'start-date': '30daysAgo',
            'end-date': 'yesterday',
            'metrics': 'ga:avgSessionDuration',
            'title': 'Visit Duration (mins)',
            'container': 'avgVisitDuration',
            'mutator': function(value) { return parseFloat(value / 60).toFixed(2); },
        }
    };

    /*
     * Page Load Time
     */
    charts.loadTime = {
        component: 'HeroMetric',
        opts: {
            'ids': 'ga:'+window.ga_profile_id,
            'start-date': '30daysAgo',
            'end-date': 'yesterday',
            'metrics': 'ga:avgPageLoadTime',
            'title': 'Page Load Time (secs)',
            'container': 'avgPageLoad',
        }
    };

    /*
    * Bind sucessful load callbacks to a chart.
    */
    function bindSuccessListener(i)
    {
        charts[i].instance.on('success', function(response) {
            $(window).resize(function() {
                $('#'+charts[i].chart.container).empty();
                charts[i].chart.instance = new gapi.analytics.googleCharts.DataChart(charts[i]);
                charts[i].chart.instance.execute();
            });
        });
    }

    /*
     * Loop through each chart creating & executing it.
     */
    for (var i in charts) {
        // Chart Type
        if (charts[i].chart) {
            charts[i].instance = new gapi.analytics.googleCharts.DataChart(charts[i]);
            bindSuccessListener(i);
            charts[i].instance.execute();
        } 

        // Custom component type
        else if(charts[i].component) {
            charts[i].instance = new gapi.analytics.ext[charts[i].component](charts[i].opts);
            charts[i].instance.execute();
        }
    }
});