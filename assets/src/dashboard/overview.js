gapi.analytics.ready(function() {
    var charts = {};

    if (! window.ga_access_token) {
        return;
    }

    /**
    * Authorize the user with an access token obtained server side.
    */
    gapi.analytics.auth.authorize({
        'serverAuth': {
        'access_token': window.ga_access_token.access_token
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
            'pollingInterval': 5,
            'title':  window.component_translations.activeUsers
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
            'title': window.component_translations.bounceRate,
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
            'title': window.component_translations.visitDuration,
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
            'title':  window.component_translations.pageLoadTime,
            'container': 'avgPageLoad',
            'mutator': function(value) { return value+'s'; },
        }
    };

    /*
    * Bind sucessful load callbacks to a chart.
    */
    function bindSuccessListener(i)
    {
        var timeout;

        charts[i].instance.on('error', function(response) {
            $('#'+charts[i].chart.container).html(
                '<div class="loading"><span><i class="fa fa-exclamation-triangle"></i>Error loading</span></div>'
            );
        });

        charts[i].instance.on('success', function(response) {
            var clearChart = function() {
                $('#'+charts[i].chart.container).html(
                    '<div class="loading"><span><i class="fa fa-spin fa-refresh"></i>Refreshing view</span></div>'
                );

                clearTimeout(timeout);
                timeout = setTimeout(function() { bootChart(i); }, 500);
            };

            var bootChart = function(i) {
                charts[i].chart.instance = new gapi.analytics.googleCharts.DataChart(charts[i]);
                charts[i].chart.instance.execute();
            };

            $(window).resize(clearChart);
            Concrete.event.subscribe('PanelClose', clearChart);
            $('a').click(function() {
                if ($('html').hasClass('ccm-panel-open')) {
                    clearChart();
                }
            });
        });
    }

    /*
     * Loop through each chart creating & executing it.
     */
    for (var i in charts) {
        // Token Expired
        var token_expired = (window.ga_access_token.expires <= Math.round((new Date().getTime()) / 1000));

        // Chart Type
        if (charts[i].chart) {
            charts[i].instance = new gapi.analytics.googleCharts.DataChart(charts[i]);
            bindSuccessListener(i);
        } 

        // Custom component type
        else if(charts[i].component) {
            charts[i].instance = new gapi.analytics.ext[charts[i].component](charts[i].opts);
        }

        if (! token_expired) {
            charts[i].instance.execute();
        } else {
            charts[i].instance.emit('expired.token', window.ga_access_token.expires);
        }
    }
});