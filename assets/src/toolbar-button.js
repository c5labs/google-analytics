gapi.analytics.ready(function() {
    /*
     * Check that our oauth token is still valid.
     */
    if (! window.ga_access_token || window.ga_access_token.expires <= Math.round((new Date().getTime()) / 1000)) {
        $('#gaActiveUsers').prev().removeClass('fa-spin fa-refresh').addClass('fa-exclamation-triangle');
        return;
    }

    /*
    * Authorize the user with an access token obtained server side.
    */
    gapi.analytics.auth.authorize({
        'serverAuth': {
        'access_token': window.ga_access_token.access_token
        }
    });

    /*
     * Active User Count
     */
    var activeUsers = new gapi.analytics.ext.ActiveUsers({
        'ids': 'ga:'+window.ga_profile_id,
        'container': 'gaActiveUsers',
        'pollingInterval': 5,
        'template': '<div><span class="hero-metric-value"></span></div>'
    });

    activeUsers.on("success", function(response) {
        $('#gaActiveUsers').css('display', 'inline-block');
        $('#gaActiveUsers').prev().removeClass('fa-spin fa-refresh').addClass('fa-male');
    });

    activeUsers.on("error", function(response) {
        $('#gaActiveUsers').prev().removeClass('fa-spin fa-refresh').addClass('fa-exclamation-triangle');
    });

    activeUsers.execute();
});