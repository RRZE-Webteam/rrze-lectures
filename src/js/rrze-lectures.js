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
    });

function getFAUOrgNr() {
    var $keyword = jQuery('input#keyword');
    var $keywordVal = $keyword.val();
    var $resultTab = jQuery('div#dip-search-result');

    // alert($keywordVal);

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
