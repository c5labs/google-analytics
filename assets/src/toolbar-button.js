$('#ccm-toolbar .ccm-toolbar-item-list').append($('#gaToolbarButtonTemplate').html());

gapi.analytics.ready(function() {
    /*
    * Authorize the user with an access token obtained server side.
    */
    gapi.analytics.auth.authorize({
        'serverAuth': {
        'access_token': window.ga_access_token
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