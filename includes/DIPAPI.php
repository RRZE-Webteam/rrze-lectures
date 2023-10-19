<?php

namespace RRZE\Lectures;
use function RRZE\Lectures\Config\getConstants;
defined('ABSPATH') || exit;

if (!function_exists('__')) {
    function __($txt, $domain)
    {
        return $txt;
    }
}

class DIPAPI {

    protected $api;
    protected $atts;
    protected $lectureParam;
    protected $sem;
    protected $gast;

    // public function __construct($api, $orgID, $atts)
    public function __construct() {
        $this->api = 'https://api.fau.de/pub/v2/vz/';
        $constants = getConstants();
        $this->api_timeout = $constants['DIPAPI_timeout'];
        $this->api_maxbytes = $constants['DIPAPI_max_response_bytes'];
        
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

     public function getDataCount(string $endpoint = 'educationEvents', array $atts = []): array {
        $aRet = [
            'valid' => FALSE, 
            'content' => ''
        ];

        $aGetArgs = [
            'timeout' => $this->api_timeout,
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Api-Key' => $this->getKey(),
                ]
            ];
            
        $atts['max'] = 1;
        $atts['format'] = '_countresults';
        $dipParams = $this->getAPIParamsPrefix($atts);
        
        $apirequest =  $this->api . $endpoint . '/' . $dipParams;
        $apiResponse = wp_remote_get($this->api . $endpoint . '/' . $dipParams, $aGetArgs);
        if ( is_array( $apiResponse ) && ! is_wp_error( $apiResponse ) ) {
           if ($apiResponse['response']['code'] != 200){
                $aRet = [
                    'valid' => FALSE, 
                    'content' => $apiResponse['response']['message'],
                    'code' => $apiResponse['response']['code'],
                    'request_string'    => $apirequest,
                    'size'  => 0
                ];    
            } else {
                $headers = wp_remote_retrieve_headers($apiResponse);
                  // Die Content-Length-Header verwenden, um die Größe in Bytes zu erhalten
                $size_in_bytes = isset($headers['content-length']) ? (int) $headers['content-length'] : 0;
                
                $aRet = [
                        'valid'     => TRUE, 
                        'content'   => '',
                        'code'      => 200,
                        'request_string'    => $apirequest,
                        'size'      => $size_in_bytes
                 ];  
                 
                 
                if (empty($apiResponse['body'])) {
                    $aRet['valid']  = FALSE;
                    $aRet['code']   = 404;
                    

                } else {
                    if ($size_in_bytes==0) {
                        $size_in_bytes = strlen($apiResponse['body']);
                        $aRet['size'] = $size_in_bytes;
                    }
                    
                    
                    if ($size_in_bytes > $this->api_maxbytes) {
                        $aRet['valid'] = FALSE;
                        $aRet['code'] = 'oversize';
                    } else {
                        $content = json_decode($apiResponse['body'], true);

                        if (empty($content['data'])) {
                            $aRet['valid'] = FALSE;
                            $aRet['code'] = 404;

                        } else {
                            $aRet['content'] = $content;
                        }
                    }
                }
            }
        } else {
            $aRet = [
                'valid'     => FALSE, 
                'content'   => $apiResponse->get_error_message(),
                'code'      =>  $apiResponse->get_error_code(),
                'request_string'    => $apirequest,
                'size'      => 0
            ];   
        }
        
        /*
         * Please do not ask me for the reason for all these tests :) 
         */

        return $aRet;    
     }

    public function getResponse(string $endpoint = 'educationEvents', string $sParam = NULL): array {
        $aRet = [
            'valid' => FALSE, 
            'content' => ''
        ];

        $aGetArgs = [
            'timeout' => $this->api_timeout,
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Api-Key' => $this->getKey(),
                ]
            ];
            
        $apirequest =  $this->api . $endpoint . '/' . $sParam;
        $apiResponse = wp_remote_get($this->api . $endpoint . '/' . $sParam, $aGetArgs);
        if ( is_array( $apiResponse ) && ! is_wp_error( $apiResponse ) ) {
           if ($apiResponse['response']['code'] != 200){
                $aRet = [
                    'valid' => FALSE, 
                    'content' => $apiResponse['response']['message'],
                    'code' => $apiResponse['response']['code'],
                    'request_string'    => $apirequest,
                    'size'  => 0
                ];    
            } else {
                $headers = wp_remote_retrieve_headers($apiResponse);
                  // Die Content-Length-Header verwenden, um die Größe in Bytes zu erhalten
                $size_in_bytes = isset($headers['content-length']) ? (int) $headers['content-length'] : 0;
                
                $aRet = [
                        'valid'     => TRUE, 
                        'content'   => '',
                        'code'      => 200,
                        'request_string'    => $apirequest,
                        'size'      => $size_in_bytes
                 ];  
                 
                 
                if (empty($apiResponse['body'])) {
                    $aRet['valid']  = FALSE;
                    $aRet['code']   = 404;
                    

                } else {
                    if ($size_in_bytes==0) {
                        $size_in_bytes = strlen($apiResponse['body']);
                        $aRet['size'] = $size_in_bytes;
                    }
                    
                    
                    if ($size_in_bytes > $this->api_maxbytes) {
                        $aRet['valid'] = FALSE;
                        $aRet['code'] = 'oversize';
                    } else {
                        $content = json_decode($apiResponse['body'], true);

                        if (empty($content['data'])) {
                            $aRet['valid'] = FALSE;
                            $aRet['code'] = 404;

                        } else {
                            $aRet['content'] = $content;
                        }
                    }
                }
            }
        } else {
            $aRet = [
                'valid'     => FALSE, 
                'content'   => $apiResponse->get_error_message(),
                'code'      =>  $apiResponse->get_error_code(),
                'request_string'    => $apirequest,
                'size'      => 0
            ];   
        }
        

        return $aRet;
    }



    
    /*
     * Builds the list of parameters we ask the API to response with
     */
    public function getAPIResponseArgs(array $atts = []): string {
        $attrs = '';
        switch ($atts['format']) {
                case '_countresults':
                    // this call is used to make a pre-test to look how many 
                    // results i get by the query. This may help to reduce
                    // load for the real request.
                    $attrs = 'identifier;name;';
                    break;
                case 'degree-linklist':
                case 'linklist':
                    $attrs = 'identifier;name;'
                        . 'providerValues.event.eventtype;'
                        . 'providerValues.courses;';
                    
                    // TODO: Einschränken, wenn notwendig.
                    // Aber vorher noch die anderen Sortierkriterien einbauen, insbes. 
                    // nach Datum oder Leuten...
                    
//                        . 'providerValues.courses.title;'
//                        . 'providerValues.courses.shorttext;'
//                        . 'providerValues.courses.url;'
//                        . 'providerValues.courses.teaching_language;'
//                        . 'providerValues.courses.course_responsible.sortorder;'
//                        . 'providerValues.courses.course_responsible.identifier';
                            // von den Personen holen wir nur noch die identifier
                            // um Berge an redundanten Daten bei jedem Kurs zu vermeiden
                            // Die Daten der Personen holen wir danach aus dem 
                            // eigenen Persons Endpoint, wenn wir sie wirklich brauchen.
                    
                    
            //        if (!empty($atts['degree'])) {
                            // wenn eine Suche nach degree erfolgte,
                            // ist auch möglich, dass der Suchsttring nur ein Teil des
                            // Namens enthielt. Daher muss ich bei der Abfrage dann auch 
                            // en Abschlussnamen holen um danach ggf. zu filtern
              //              $attrs .= ';providerValues.modules.module_cos.subject;'
                                       // subject = Name
               //                     . 'providerValues.modules.module_nr;'
               //                     . 'providerValues.modules.module_cos.major;'
                //                    . 'providerValues.modules.module_cos.degree';
                                        // degree hier = Abschluss, wie Master of Arts oder Bachelor of Arts
                   
                  //  }
                    break;
                case 'tabs':
                    $attrs = 'identifier;name;description;'
                        . 'providerValues.courses;'
                        . 'providerValues.event;'
                        . 'providerValues.event_orgunit;'
                        . 'providerValues.event_responsible;'
                        // TODO: Hier auch die Daten aus den persons Endpoint holen
                        . 'providerValues.modules.module_nr;'
                        . 'providerValues.modules.module_name;'
                        . 'providerValues.modules.his_key;'
                        . 'providerValues.modules.module_cos;';
                    // Mit modules: $attrs = 'identifier;name;providerValues.event.eventtype;providerValues.courses.url;providerValues.courses.semester;providerValues.event.title;providerValues.event.shorttext;providerValues.event_orgunit.orgunit;providerValues.event.comment;providerValues.courses.hours_per_week;providerValues.courses.teaching_language;providerValues.courses.course_responsible.prefixTitle;providerValues.courses.course_responsible.firstname;providerValues.courses.course_responsible.surname;providerValues.courses.contents;providerValues.courses.literature;providerValues.courses.compulsory_requirement;providerValues.courses.attendee_maximum;providerValues.courses.attendee_minimum;providerValues.courses.planned_dates.rhythm;providerValues.courses.planned_dates.weekday;providerValues.courses.planned_dates.starttime;providerValues.courses.planned_dates.endtime;providerValues.courses.planned_dates.individual_dates.cancelled;providerValues.courses.planned_dates.individual_dates.date;providerValues.courses.planned_dates.startdate;providerValues.courses.planned_dates.enddate;providerValues.courses.planned_dates.expected_attendees_count;providerValues.courses.planned_dates.comment;providerValues.courses.planned_dates.instructor.prefixTitle;providerValues.courses.planned_dates.instructor.firstname;providerValues.courses.planned_dates.instructor.surname;providerValues.courses.planned_dates.famos_code;providerValues.modules.module_cos.degree;providerValues.modules.module_cos.subject;providerValues.modules.module_cos.major;providerValues.modules.module_cos.subject_indicator;providerValues.modules.module_cos.version;providerValues.event.frequency;providerValues.event.semester_hours_per_week;providerValues.courses.parallelgroup';
               //     $attrs = 'identifier;name;providerValues.event.eventtype;providerValues.courses.url;providerValues.courses.semester;providerValues.event.title;providerValues.event.shorttext;providerValues.event_orgunit.orgunit;providerValues.event.comment;providerValues.courses.hours_per_week;providerValues.courses.teaching_language;providerValues.courses.course_responsible.prefixTitle;providerValues.courses.course_responsible.firstname;providerValues.courses.course_responsible.surname;providerValues.courses.contents;providerValues.courses.literature;providerValues.courses.compulsory_requirement;providerValues.courses.attendee_maximum;providerValues.courses.attendee_minimum;providerValues.courses.planned_dates.rhythm;providerValues.courses.planned_dates.weekday;providerValues.courses.planned_dates.starttime;providerValues.courses.planned_dates.endtime;providerValues.courses.planned_dates.individual_dates.cancelled;providerValues.courses.planned_dates.individual_dates.date;providerValues.courses.planned_dates.startdate;providerValues.courses.planned_dates.enddate;providerValues.courses.planned_dates.expected_attendees_count;providerValues.courses.planned_dates.comment;providerValues.courses.planned_dates.instructor.prefixTitle;providerValues.courses.planned_dates.instructor.firstname;providerValues.courses.planned_dates.instructor.surname;providerValues.courses.planned_dates.famos_code;providerValues.event.frequency;providerValues.event.semester_hours_per_week;providerValues.courses.parallelgroup;providerValues.modules.module_cos.subject';
                    
      //              $attrs = '';
                        // debug
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
            //    $aLQ['providerValues.modules.stud.subject'] = $atts['degree'];
            //    Nach Absprache vom 29.08.23 mit DIP-Team nicht mehr providerValues.modules.stud.* verwenden, da dies
            //    nur für den Raumplanungstool der TF entwickelt wurde und daher auch nicht alle Events zeigt. Soll aus der API entfernt werden
            //
            }
            if (!empty($atts['degree_key'])) {
                   $aLQ['providerValues.modules.module_cos.his_key'] = $atts['degree_key'];
            }
            // Filter for FAUOrg 
            if (!empty($atts['fauorgnr'])) {
                $aLQ['providerValues.event_orgunit.fauorg'] = $atts['fauorgnr'];
            }
           
              // Filter for Orgunit String 
            if (!empty($atts['orgunit'])) {
                $aLQ['providerValues.event_orgunit.orgunit'] = $atts['orgunit'];
            }
            
            
            // Now all the other filters
            
            // no cancelled courses
            $aLQ['providerValues.courses.cancelled'] = 0;

            // sem
            $aLQ['providerValues.courses.semester'] = $atts['sem'];

            // type
            if (!empty($atts['type'])) {
                $aLQ['providerValues.event.eventtypes'] = $atts['type'];
            }

            // guest
            if (isset($atts['guest']) && $atts['guest'] != '') {
                // we cannot use empty() because it can contain 0
                $aLQ['providerValues.event.guest'] = (int) $atts['guest'];
            }

           
            // teaching_language (display_language works differently and is not an attribute for the DIP-Campo-API)
            if (!empty($atts['teaching_language'])) {
                $aLQ['providerValues.courses.teaching_language'] = $atts['teaching_language'];
            }

            
            // we cannot use API parameter "sort" because it sorts per page not the complete dataset -> 2DO: check again, API has changed
            $dipParams = '?limit=' . $atts['max'];
            
            //  Umsetzungshinweis zur API:
            //  Die Suche nach rq > lq > q erfolgt nach der Reihenfolge. 
            //  Es wird nur eine Form der Suche ausgeführt, nicht in Kombination!
            //  Wenn z.B. rq angegeben wurde, wird rq ausgführt, aber lq und q 
            //  dann nicht mehr.

            $dipParams .= '&lq=' . urlencode($this->makeLQRQ($aLQ));
            
            // Filter auf die Suchergebnisse
            $dipParams .= '&lf=' . urlencode($this->makeLF($aLQ));
   
            
            // etwaige EInschränkung der Rückgabewerte zur Performanceverbesserung
            $attrs = $this->getAPIResponseArgs($atts);
            if (!empty($attrs)) {
                $dipParams .= '&attrs=' . urlencode($attrs);             
            }

            return $dipParams;

   } 
   
   
   // zusätzlicher Filter für den Fall, dass die API dann doch zu viele Daten liefert
   // aufgrund von Fallbacks
    private function makeLF(array $aIn): string {
        $res = '';
        if ($aIn['providerValues.courses.semester']) {
            $res .= 'providerValues.courses.semester='.$aIn['providerValues.courses.semester'];
        }
        return $res;
    }
    
     //  RQ-Suche (nur diese kann OR-Bedingungen
    private function makeLQRQ(array $aIn): string {
        $aRQ = [];
        foreach ($aIn as $dipField => $attVal) {
            if (!empty($attVal) || $attVal == 0 ) {
                if ($dipField == 'lecturerName') {
                    $aLecturers = array_map('trim', explode(';', $attVal));
                    foreach($aLecturers as $lectureName){
                        $aParts = array_map('trim', explode(',', $lectureName));
                        
                        
                        $queryfield = 'providerValues.courses.course_responsible.surname' . (count($aLecturers) > 1 ? '[in]=' : '=') . rawurlencode($aParts[0]);
                        
                        $aRQ[]  = $queryfield;
                        if (!empty($aParts[1])){
                            $queryfield = 'providerValues.courses.course_responsible.firstname' . (count($aLecturers) > 1 ? '[in]=' : '=') . rawurlencode($aParts[1]);
                            
                            $aRQ[]  = $queryfield;
                        }
                    }

                    // 2DO:
                    // (lastname1 AND firstname1) OR (lastname2) OR (lastname3 AND firstname3)
                    // see:
                    // use [or] to or value criteria
                    // example value: givenName=in:Uwe;Thomas&gender=1&familyName=lte:Nacht&familyName=gte:Bach[and]lte:Wolf&birthdate=gte:1998-04-16T22:00:00Z[or]lte:1955-04-16T22:00:00Z&gender=1
                
                    
               
                } elseif ($dipField == 'providerValues.courses.course_responsible.identifier') {
                    $aTmp = array_map(function ($val) {
                        return rawurlencode(trim($val));
                    }, explode(',', $attVal));

                    // check if 10 figures hex 
                    foreach ($aTmp as $nr => $val) {
                        if (!(ctype_xdigit($val) && strlen($val) == 10)) {
                            unset($aTmp[$nr]);
                        }
                    }

                    $queryfield  = $dipField . (count($aTmp) > 1 ? '[in]=' : '=') . implode(urlencode(';'), $aTmp);
                    $aRQ[] = $queryfield;

                } elseif (($dipField == 'providerValues.modules.module_cos.subject')) {  
                    $aRQ[] = 'providerValues.modules.module_cos.subject[ireg]='.$aIn['providerValues.modules.module_cos.subject'];    
                } elseif (($dipField == 'providerValues.event_orgunit.orgunit')) {  
                    $aRQ[] = 'providerValues.event_orgunit.orgunit.de[in]='.$aIn['providerValues.event_orgunit.orgunit'];
                    
                } elseif (($dipField == 'providerValues.courses.cancelled') && ($attVal == 0)) {  
                    
                    if (!isset($aIn['providerValues.courses.semester'])) {
                        $aRQ[] = 'providerValues.courses[em]=cancelled%3D0%3B';
                    } else {
                        $aRQ[] = 'providerValues.courses[em]=cancelled%3D0%3Bsemester%3D'.$aIn['providerValues.courses.semester'];
                    }
                } elseif (($dipField == 'providerValues.courses.semester') && ($aIn['providerValues.courses.cancelled'] == 0)) {
                    continue;
                } else {
                    $aTmp = array_map(function ($val) {
                        return rawurlencode(trim($val));
                    }, explode(',', $attVal));

                   

                    // $aLQ[] = $dipField . (count($aTmp) > 1 ? '%5Bin%5D%3D' : '%3D') . implode('%3B', $aTmp);
                    $aRQ[] = $dipField . (count($aTmp) > 1 ? '[in]=' : '=') . implode(urlencode(';'), $aTmp);
                }
            }
        }

        // return implode('%26', $aLQ);
        return implode('&', $aRQ);
    }
    
    
   
}
