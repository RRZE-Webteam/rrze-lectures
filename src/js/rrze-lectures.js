"use strict";

jQuery(document).ready(function ($) {
    var loading1 = $('div#search-fauorgnr-loading').hide();

    $(document)
        .ajaxStop(function () {
            loading1.hide();
        });

    $('#search-fauorgnr-button').click(getFAUOrgNr);

    $('form#search-fauorgnr-form').each(function() {
        $(this).find('input').keypress(function(e) {
            // Enter pressed?
            if(e.which == 10 || e.which == 13) {
                getFAUOrgNr();
                e.preventDefault();
            }
        });
    });
});

function getFAUOrgNr() {
    var keyword = jQuery('input#keyword');
    var keywordVal = keyword.val();
    var resultTab = jQuery('div#search-fauorgnr-result');

    if (keywordVal) {
        var loading = jQuery('div#search-fauorgnr-loading');
        loading.show();
        resultTab.html();
        keyword.val('');

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
