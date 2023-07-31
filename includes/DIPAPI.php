<?php

namespace RRZE\Lectures;

defined('ABSPATH') || exit;

if (!function_exists('__')) {
    function __($txt, $domain)
    {
        return $txt;
    }
}

class DIPAPI {

    protected $api;
    // protected $orgID;
    protected $atts;
    protected $lectureParam;
    protected $sem;
    protected $gast;

    // public function __construct($api, $orgID, $atts)
    public function __construct() {
        $this->setAPI();
    }

    private function getKey(){
        $lectureOptions = get_option('rrze-lectures');

        if (!empty($lectureOptions['basic_ApiKey'])){
            return $lectureOptions['basic_ApiKey'];
        }elseif(is_multisite()){
            $settingsOptions = get_site_option('rrze_settings');
            if (!empty($settingsOptions->plugins->dip_apiKey)){
                return $settingsOptions->plugins->dip_apiKey;
            }
        }else{
            return '';
        }
    }

    public function getResponse(string $endpoint = 'educationEvents', string $sParam = NULL): array {
        $aRet = [
            'valid' => FALSE, 
            'content' => ''
        ];

        $aGetArgs = [
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Api-Key' => $this->getKey(),
                ]
            ];
            
        $apirequest =  $this->api . $endpoint . '/' . $sParam;
        $apiResponse = wp_remote_get($this->api . $endpoint . '/' . $sParam, $aGetArgs);

        if ($apiResponse['response']['code'] != 200){
            $aRet = [
                'valid' => FALSE, 
                'content' => $apiResponse['response']['message'],
                'code' => $apiResponse['response']['code'],
                'request_string'    => $apirequest
            ];    
        } else {
            $content = json_decode($apiResponse['body'], true);
            $aRet = [
                'valid' => TRUE, 
                'content' => $content,
                'code' => 200,
                'request_string'    => $apirequest
            ];
        }

        return $aRet;
    }


    private function setAPI() {
        $this->api = 'https://api.fau.de/pub/v2/vz/';
    }
    
    /*
     * Builds the list of parameters we ask the API to response with
     */
    public function getAPIResponseArgs(array $atts = []): string {
        $attrs = '';
        switch ($atts['format']) {
                case 'linklist':
                    $attrs = 'identifier;name;providerValues.event.eventtype;providerValues.courses.url;providerValues.courses.semester';
                    if (!empty($atts['degree'])) {
                        $attrs .= ';providerValues.modules.module_cos.subject';
                    }
                    break;
                case 'tabs':
                    // Mit modules: $attrs = 'identifier;name;providerValues.event.eventtype;providerValues.courses.url;providerValues.courses.semester;providerValues.event.title;providerValues.event.shorttext;providerValues.event_orgunit.orgunit;providerValues.event.comment;providerValues.courses.hours_per_week;providerValues.courses.teaching_language;providerValues.courses.course_responsible.prefixTitle;providerValues.courses.course_responsible.firstname;providerValues.courses.course_responsible.surname;providerValues.courses.contents;providerValues.courses.literature;providerValues.courses.compulsory_requirement;providerValues.courses.attendee_maximum;providerValues.courses.attendee_minimum;providerValues.courses.planned_dates.rhythm;providerValues.courses.planned_dates.weekday;providerValues.courses.planned_dates.starttime;providerValues.courses.planned_dates.endtime;providerValues.courses.planned_dates.individual_dates.cancelled;providerValues.courses.planned_dates.individual_dates.date;providerValues.courses.planned_dates.startdate;providerValues.courses.planned_dates.enddate;providerValues.courses.planned_dates.expected_attendees_count;providerValues.courses.planned_dates.comment;providerValues.courses.planned_dates.instructor.prefixTitle;providerValues.courses.planned_dates.instructor.firstname;providerValues.courses.planned_dates.instructor.surname;providerValues.courses.planned_dates.famos_code;providerValues.modules.module_cos.degree;providerValues.modules.module_cos.subject;providerValues.modules.module_cos.major;providerValues.modules.module_cos.subject_indicator;providerValues.modules.module_cos.version;providerValues.event.frequency;providerValues.event.semester_hours_per_week;providerValues.courses.parallelgroup';
               //     $attrs = 'identifier;name;providerValues.event.eventtype;providerValues.courses.url;providerValues.courses.semester;providerValues.event.title;providerValues.event.shorttext;providerValues.event_orgunit.orgunit;providerValues.event.comment;providerValues.courses.hours_per_week;providerValues.courses.teaching_language;providerValues.courses.course_responsible.prefixTitle;providerValues.courses.course_responsible.firstname;providerValues.courses.course_responsible.surname;providerValues.courses.contents;providerValues.courses.literature;providerValues.courses.compulsory_requirement;providerValues.courses.attendee_maximum;providerValues.courses.attendee_minimum;providerValues.courses.planned_dates.rhythm;providerValues.courses.planned_dates.weekday;providerValues.courses.planned_dates.starttime;providerValues.courses.planned_dates.endtime;providerValues.courses.planned_dates.individual_dates.cancelled;providerValues.courses.planned_dates.individual_dates.date;providerValues.courses.planned_dates.startdate;providerValues.courses.planned_dates.enddate;providerValues.courses.planned_dates.expected_attendees_count;providerValues.courses.planned_dates.comment;providerValues.courses.planned_dates.instructor.prefixTitle;providerValues.courses.planned_dates.instructor.firstname;providerValues.courses.planned_dates.instructor.surname;providerValues.courses.planned_dates.famos_code;providerValues.event.frequency;providerValues.event.semester_hours_per_week;providerValues.courses.parallelgroup;providerValues.modules.module_cos.subject';
                    break;
                default:
                    $attrs = ''; // send all
        }
        return $attrs;
    }

    
    /*
     * Builds the search request for the api
     */
    public function getAPIParamsPrefix(array $atts = []): string {
         
            $aLQ = [];
            // First the required parameters
            
            // Filter for dozent
            if (!empty($atts['lecturer_identifier'])) {
                $aLQ['providerValues.courses.course_responsible.identifier'] = $atts['lecturer_identifier'];
            } elseif (!empty($atts['lecturer_idm'])) {
                $aLQ['providerValues.courses.course_responsible.idm_uid'] = $atts['lecturer_idm'];
            } 

            // Filter for lecture
            if (!empty($atts['lecture_identifier'])) {
                $aLQ['identifier'] = $atts['lecture_identifier'];
            } elseif (!empty($atts['lecture_name'])) {
                $aLQ['names'] = $atts['lecture_name'];
            }
            
            // filter for degree
            if (!empty($atts['degree'])) {
                $aLQ['providerValues.modules.module_cos.subject'] = $atts['degree'];
            }

            // Filter for FAUOrg 
            if (!empty($atts['fauorgnr'])) {
                $aLQ['providerValues.event_orgunit.fauorg'] = $atts['fauorgnr'];
            }
            
            // Now all the other filters
            
            // no cancelled courses
            $aLQ['providerValues.courses.cancelled'] = 0;

            // sem
            $aLQ['providerValues.courses.semester'] = $atts['sem'];

            // type
            if (!empty($this->atts['type'])) {
                $aLQ['providerValues.event.eventtypes'] = $atts['type'];
            }

            // guest
            if (isset($atts['guest']) && $atts['guest'] != '') {
                // we cannot use empty() because it can contain 0
                $aLQ['providerValues.event.guest'] = (int) $tatts['guest'];
            }

           
            // teaching_language (display_language works differently and is not an attribute for the DIP-Campo-API)
            if (!empty($atts['teaching_language'])) {
                $aLQ['providerValues.courses.teaching_language'] = $atts['teaching_language'];
            }

            // we cannot use API parameter "sort" because it sorts per page not the complete dataset -> 2DO: check again, API has changed
            $dipParams = '?limit=' . $atts['max'];
            $dipParams .= '&lq=' . urlencode(Functions::makeLQ($aLQ));
            $dipParams .= '&lf=' . urlencode('providerValues.courses.semester=' . $atts['sem']);
                // brauchen wir den lf, wenn das Semester oben schon in lq steht?
            
        //    $attrs = '';
            $attrs = $this->getAPIResponseArgs($atts);
            // aus Debugging zwecken und weil es nicht schadet, nehmen wir 
            // erstmal alles was wir kriegen können ;) 
            if (!empty($attrs)) {
                $dipParams .= '&attrs=' . urlencode($attrs);             
            }

            return $dipParams;

   } 
    
}
