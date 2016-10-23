$(function() {
    var windowObjectReference;

    /*
     * Set profile property & account form fields on profile change.
     */
    $('#configureProfileId').change(function() {
        var $item = $(this).find('option:selected');
        $('#configurePropertyId').val($item.data('property-id'));
        $('#configureAccountId').val($item.data('account-id'));
    });

    /*
     * Show Google authorization screen.
     */
    $('#authorizeButton').click(function(e) {
        e.preventDefault();

        var strWindowFeatures = "width=500,height=650";
        windowObjectReference = window.open(
            window.ga_auth_url, 
            "Google Analytics Authorisation", 
            strWindowFeatures
        );
    });

    /*
     * Enable save configuration button after token is added.
     */
    $('#configureAuthToken').keypress(function() {
        $('#saveTokenButton').removeClass('disabled');
    });
});