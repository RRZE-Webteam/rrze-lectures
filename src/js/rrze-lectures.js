"use strict";

jQuery(document).ready(function($){
    var $loading = $('div#loading').hide();

    $(document)
        .ajaxStart(function () {
            $loading.show();
        })
        .ajaxStop(function () {
         $loading.hide();
        });

        $('#searchFAUOrgNr').click(getFAUOrgNr);
        $('#searchLecturerIdentifier').click(getLecturerIdentifier);
        
    });

function getFAUOrgNr() {
    var $keyword = jQuery('input#keyword');
    var $keywordVal = $keyword.val();
    var $resultTab = jQuery('div#dip-search-result');

    if ($keywordVal){
        $resultTab.html();
        $keyword.val();
        
        jQuery.post(lecture_ajax.ajax_url, { 
            _ajax_nonce: lecture_ajax.nonce,
            action: 'GetFAUOrgNr',
            data: {'keyword':$keywordVal},               
        }, function(result) {
            $resultTab.html(result);
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
    var resultTab = jQuery('div#dip-search-result');

    if (familyNameVal || emailVal){
        // we don't want users to search by givenName only
        resultTab.html();
        familyName.val();
        givenName.val();
        email.val();

        var data = { 
            "familyName": familyNameVal, 
            "givenName": givenNameVal, 
            "email": emailVal
        };

        jQuery.post(lecture_ajax.ajax_url, { 
            _ajax_nonce: lecture_ajax.nonce,
            action: 'GetLecturerIdentifier',
            data: {'data' : data},               
        }, function(result) {
            $resultTab.html(result);
            jQuery('div#loading').hide();
        });
    }
}
