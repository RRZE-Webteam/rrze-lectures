"use strict";

jQuery(document).ready(function ($) {
    var loading1 = $('div#search-fauorgnr-loading').hide();
    var loading2 = $('div#search-identifier-loading').hide();

    $(document)
        .ajaxStop(function () {
            loading1.hide();
            loading2.hide();
        });

    $('#search-fauorgnr-button').click(getFAUOrgNr);
    $('#search-identifier-button').click(getLecturerIdentifier);

});

function getFAUOrgNr() {
    var keyword = jQuery('input#keyword');
    var keywordVal = keyword.val();
    var resultTab = jQuery('div#search-fauorgnr-result');

    if (keywordVal) {
        var loading = jQuery('div#search-fauorgnr-loading');
        loading.show();
        resultTab.html();
        // keyword.val('');

        jQuery.post(lecture_ajax.ajax_url, {
            _ajax_nonce: lecture_ajax.nonce,
            action: 'GetFAUOrgNr',
            data: { 'keyword': keywordVal },
        }, function (result) {
            resultTab.html(result);
            loading.hide();
        });
    }
}

function getLecturerIdentifier() {
    var familyName = jQuery('input#familyName');
    var familyNameVal = familyName.val();
    var givenName = jQuery('input#givenName');
    var givenNameVal = givenName.val();
    var resultTab = jQuery('div#search-identifier-result');

    if (familyNameVal) {
        // we don't want users to search by givenName only
        var loading = jQuery('div#search-identifier-loading');
        loading.show();
        resultTab.html();
        // familyName.val('');
        // givenName.val('');

        var aIn = {
            "familyName": familyNameVal,
            "givenName": givenNameVal
        };

        jQuery.post(lecture_ajax.ajax_url, {
            _ajax_nonce: lecture_ajax.nonce,
            action: 'GetLecturerIdentifier',
            data: aIn,
        }, function (result) {
            resultTab.html(result);
            loading.hide();
        });
    }
}
