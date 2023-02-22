"use strict";


jQuery(document).ready(function ($) {
    var loading1 = $('div#loading1').hide();
    var loading2 = $('div#loading2').hide();

    $(document)
        .ajaxStop(function () {
            loading1.hide();
            loading2.hide();
        });

    $('#searchFAUOrgNr').click(getFAUOrgNr);
    $('#searchLecturerIdentifier').click(getLecturerIdentifier);

});

function getFAUOrgNr() {
    var keyword = jQuery('input#keyword');
    var keywordVal = keyword.val();
    var resultTab = jQuery('div#dip-fauorgnr-result');

    if (keywordVal) {
        var loading = jQuery('div#loading1');
        loading.show();
        resultTab.html();
        keyword.val('');

        jQuery.post(lecture_ajax.ajax_url, {
            _ajax_nonce: lecture_ajax.nonce,
            action: 'GetFAUOrgNr',
            data: { 'keyword': keywordVal },
        }, function (result) {
            resultTab.html(result);
            jQuery('div#loading').hide();
        });
    }
}

function getLecturerIdentifier() {
    var familyName = jQuery('input#familyName');
    var familyNameVal = familyName.val();
    var givenName = jQuery('input#givenName');
    var givenNameVal = givenName.val();
    var email = jQuery('input#email');
    var emailVal = email.val();
    var resultTab = jQuery('div#dip-identifier-result');

    if (familyNameVal || emailVal) {
        // we don't want users to search by givenName only
        var loading = jQuery('div#loading2');
        loading.show();
        resultTab.html();
        familyName.val('');
        givenName.val('');
        email.val('');

        var aIn = {
            "familyName": familyNameVal,
            "givenName": givenNameVal,
            "email": emailVal
        };

        jQuery.post(lecture_ajax.ajax_url, {
            _ajax_nonce: lecture_ajax.nonce,
            action: 'GetLecturerIdentifier',
            data: aIn,
        }, function (result) {
            resultTab.html(result);
            jQuery('div#loading').hide();
        });
    }
}
