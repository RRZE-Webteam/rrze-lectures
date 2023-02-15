"use strict";
 
wp.domReady( 
    function(){
        jQuery(document).ready(function($){
            jQuery(document).on('change', 'input#number', function(){
                getLectureDataForBlockelements('personAll', 'select#lectureid');
                getLectureDataForBlockelements('lectureByDepartment', 'select#id');
            });
            // jQuery(document).on('change', 'select#lectureid', function(){
            //     jQuery(document).ready(function($){
            //         if (jQuery('select#id') == 'undefined'){
            //             var task = 'mitarbeiter-' + (jQuery('select#lectureid').val() == '' ? 'alle' : 'einzeln');
            //             jQuery('select#task').val(task);
            //         }
            //     });
            // });
            // jQuery(document).on('change', 'select#id', function(){
            //     jQuery(document).ready(function($){
            //         // var task = 'lectures-' + (jQuery('select#id').val() == '' ? 'alle' : 'einzeln');
            //         jQuery('select#task').val(task).trigger('change');
            //     });
            // });
        });
    });

function getLectureDataForBlockelements($dataType, $output) {
    var $lectureOrgID = jQuery('input#number').val();
    var $output = jQuery($output);

    if ($lectureOrgID){
        $output.html('<option value="">loading... </option>');
    
        jQuery.post(lecture_ajax.ajax_url, { 
            _ajax_nonce: lecture_ajax.nonce,
            action: 'GetLectureDataForBlockelements',
            data: {'lectureOrgID':$lectureOrgID, 'dataType':$dataType},               
        }, function(result) {
            $output.html(result);
        });
    }
}
