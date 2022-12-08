<?php

namespace RRZE\Lectures;

defined('ABSPATH') || exit;

if (!function_exists('__')) {
    function __($txt, $domain)
    {
        return $txt;
    }
}

class DIPAPI
{

    protected $api;
    // protected $orgID;
    protected $atts;
    protected $lectureParam;
    protected $sem;
    protected $gast;

    // public function __construct($api, $orgID, $atts)
    public function __construct($atts)
    {
        $this->setAPI();
        // $this->orgID = $orgID;
        $this->atts = $atts;
        $this->sem = (!empty($this->atts['sem']) && self::checkSemester($this->atts['sem']) ? $this->atts['sem'] : '');
        $this->gast = (!empty($this->atts['gast']) ? __('Allowed for guest students', 'rrze-univis') : '');
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

    public function getResponse($sParam = NULL){
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

        $apiResponse = wp_remote_get($this->api . $sParam, $aGetArgs);

        if ($apiResponse['response']['code'] != 200){
            $aRet = [
                'valid' => FALSE, 
                'content' => $apiResponse['response']['code'] . ': ' . $apiResponse['response']['message']
            ];    
        }else{
            $content = json_decode($apiResponse['body'], true);
            echo '<pre>';
            var_dump($content);
            exit;
            $aRet = [
                'valid' => TRUE, 
                'content' => $content['data']
            ];
        }

        return $aRet;
    }


    private function setAPI()
    {
        $this->api = 'https://api.fau.de/pub/v1/vz/educationEvents/'; // 2DO: use from settings
    }

    private static function log(string $method, string $logType = 'error', string $msg = '')
    {
        // uses plugin rrze-log
        $pre = __NAMESPACE__ . ' ' . $method . '() : ';
        if ($logType == 'DB') {
            global $wpdb;
            do_action('rrze.log.error', $pre . '$wpdb->last_result= ' . json_encode($wpdb->last_result) . '| $wpdb->last_query= ' . json_encode($wpdb->last_query . '| $wpdb->last_error= ' . json_encode($wpdb->last_error)));
        } else {
            do_action('rrze.log.' . $logType, __NAMESPACE__ . ' ' . $method . '() : ' . $msg);
        }
    }

    public function getData($dataType, $lectureParam = null)
    {
        $this->lectureParam = urlencode($lectureParam);

        if (!$url) {
            return 'Set DIP Org ID in settings.';
        }
        $data = file_get_contents($url);
        if (!$data) {
            DIPAPI::log('getData', 'error', "no data returned using $url");
            return false;
        }
        $data = json_decode($data, true);
        $data = $this->mapIt($dataType, $data);
        $data = $this->dict($data);
        $data = $this->sortGroup($dataType, $data);
        return $data;
    }


    public function getMap($dataType)
    {
        $map = [];

        switch ($dataType) {
            case 'personByID':
            case 'personByOrga':
            case 'personByOrgaPhonebook':
            case 'personByName':
            case 'personAll':
                $map = [
                    'node' => 'Person',
                    'fields' => [
                        'person_id' => 'id',
                        'key' => 'key',
                        'title' => 'title',
                        'atitle' => 'atitle',
                        'firstname' => 'firstname',
                        'lastname' => 'lastname',
                        'work' => 'work',
                        'officehours' => 'officehour',
                        'department' => 'orgname',
                        'organization' => ['orgunit', 1],
                        'locations' => 'location',
                    ],
                ];
                break;
            case 'publicationByAuthor':
            case 'publicationByAuthorID':
            case 'publicationByDepartment':
                $map = [
                    'node' => 'Pub',
                    'fields' => [
                        'publication_id' => 'id',
                        'journal' => 'journal',
                        'pubtitle' => 'pubtitle',
                        'year' => 'year',
                        'author' => 'author',
                        'publication_type' => 'type',
                        'hstype' => 'hstype',
                    ],
                ];
                break;
            case 'lectureByID':
            case 'lectureByDepartment':
            case 'lectureByLecturer':
            case 'lectureByLecturerID':
            case 'lectureByName':
                $map = [
                    'node' => 'Lecture',
                    'fields' => [
                        'lecture_id' => 'id',
                        'name' => 'name',
                        'ects_name' => 'ects_name',
                        'comment' => 'comment',
                        'leclanguage' => 'leclanguage',
                        'key' => 'key',
                        'courses' => 'term',
                        'course_keys' => 'course',
                        'lecture_type' => 'type',
                        'keywords' => 'keywords',
                        'maxturnout' => 'maxturnout',
                        'url_description' => 'url_description',
                        'organizational' => 'organizational',
                        'summary' => 'summary',
                        'schein' => 'schein',
                        'sws' => 'sws',
                        'ects' => 'ects',
                        'ects_cred' => 'ects_cred',
                        'beginners' => 'beginners',
                        'fruehstud' => 'fruehstud',
                        'gast' => 'scientia',
                        'evaluation' => 'evaluation',
                        'doz' => 'doz',
                    ],
                ];
                break;
            case 'courses':
                $map = [
                    'node' => 'Lecture',
                    'fields' => [
                        'term' => 'term',
                        'coursename' => 'coursename',
                        'course_key' => 'key',
                        'doz' => 'doz',
                    ],
                ];
                break;
            case 'jobByID':
            case 'jobAll':
                $map = [
                    'node' => 'Position',
                    'fields' => [
                        'job_id' => 'id',
                        'application_end' => 'enddate',
                        'application_link' => 'desc6',
                        'job_intern' => 'intern',
                        'job_title' => 'title',
                        'job_start' => 'start',
                        'job_limitation' => 'type1',
                        'job_limitation_duration' => 'befristet',
                        'job_limitation_reason' => 'type3',
                        'job_salary_from' => 'vonbesold',
                        'job_salary_to' => 'bisbesold',
                        'job_qualifications' => 'desc2',
                        'job_qualifications_nth' => 'desc3',
                        'job_employmenttype' => 'type2',
                        'job_workhours' => 'wstunden',
                        'job_category' => 'group',
                        'job_description' => 'desc1',
                        'job_description_introduction' => 'desc5',
                        'job_experience' => 'desc2',
                        'job_benefits' => 'desc4',
                        'person_key' => 'acontact',
                    ],
                ];
                break;
            case 'roomByID':
            case 'roomByName':
                $map = [
                    'node' => 'Room',
                    'fields' => [
                        'room_id' => 'id',
                        'key' => 'key',
                        'name' => 'name',
                        'short' => 'short',
                        'roomno' => 'roomno',
                        'buildno' => 'buildno',
                        'north' => 'north',
                        'east' => 'east',
                        'address' => 'address',
                        'size' => 'size',
                        'description' => 'description',
                        'blackboard' => 'tafel',
                        'flipchart' => 'flip',
                        'beamer' => 'beam',
                        'microphone' => 'mic',
                        'audio' => 'audio',
                        'overheadprojector' => 'ohead',
                        'tv' => 'tv',
                        'internet' => 'inet',
                    ],
                ];
                break;
            case 'orga':
                $map = [
                    'node' => 'Org',
                    'fields' => [
                        'orga_positions' => 'job',
                    ],
                ];
                break;
            case 'departmentByName':
            case 'departmentAll':
                $map = [
                    'node' => 'Org',
                    'fields' => [
                        'orgnr' => 'orgnr',
                        'name' => 'name',
                    ],
                ];
                break;
        }

        return $map;
    }


    public function mapIt($dataType, &$data)
    {
        $map = $this->getMap($dataType);

        if (empty($map)) {
            return $data;
        }

        $ret = [];
        $show = true;

        if (isset($data[$map['node']])) {
            foreach ($data[$map['node']] as $nr => $entry) {
                foreach ($map['fields'] as $k => $v) {
                    if (is_array($v)) {
                        if (is_int($v[1])) {
                            if (isset($data[$map['node']][$nr][$v[0]][$v[1]])) {
                                $ret[$nr][$k] = $data[$map['node']][$nr][$v[0]][$v[1]];
                            } elseif (isset($data[$map['node']][$nr][$v[0]][0])) {
                                $ret[$nr][$k] = $data[$map['node']][$nr][$v[0]][0];
                            }
                        } else {
                            $y = 0;
                            while (isset($data[$map['node']][$nr][$v[0]][$y][$v[1]])) {
                                $ret[$nr][$k] = $data[$map['node']][$nr][$v[0]][$y][$v[1]];
                                $y++;
                            }
                        }
                    } else {
                        if (isset($data[$map['node']][$nr][$v])) {
                            $ret[$nr][$k] = $data[$map['node']][$nr][$v];
                        }
                    }
                }
            }
        }

        switch ($dataType) {
            case 'lectureByLecturerID':
                // $lecturer_key is used in template to filter courses that are not by this lecturer
                $lecturer = $this->getData('personByID', $this->lectureParam);
                if (isset($lecturer[0]['key'])) {
                    $subs = explode('Person.', $lecturer[0]['key']);
                }
                $lecturer_key = (isset($subs[1]) ? $subs[1] : '');
            case 'lectureByLecturer':
                // $lecturer_key is used in template to filter courses that are not by this lecturer
                $lecturer = $this->getData('personByName', $this->lectureParam);
                if (isset($lecturer[0]['key'])) {
                    $subs = explode('Person.', $lecturer[0]['key']);
                }
                $lecturer_key = (isset($subs[1]) ? $subs[1] : '');
            case 'lectureByID':
            case 'lectureByDepartment':
                // add details
                $courses = $this->mapIt('courses', $data);
                $persons = $this->mapIt('personByID', $data);
                $delNr = [];
                foreach ($ret as $e_nr => $entry) {
                    $ret[$e_nr]['lecturer_key'] = (!empty($lecturer_key) ? $lecturer_key : '');
                    // add course details
                    if (isset($entry['course_keys'])) {
                        foreach ($entry['course_keys'] as $course_key) {
                            foreach ($courses as $c_nr => $course) {
                                if (($course['course_key'] == 'Lecture.' . $course_key) && (isset($course['term']))) {
                                    unset($course['course_key']);
                                    $ret[$e_nr]['courses'][] = $course;
                                    // delete entry of this course
                                    foreach ($ret as $nr => $val) {
                                        if ($val['key'] == 'Lecture.' . $course_key) {
                                            $delNr[] = $nr;
                                        }
                                    }
                                }
                            }
                        }
                        unset($ret[$e_nr]['course_keys']);
                    } elseif (isset($entry['courses'])) {
                        unset($ret[$e_nr]['courses']);
                        $ret[$e_nr]['courses'][] = ['term' => $entry['courses']];
                    }
                    // add person details
                    if (isset($entry['doz'])) {
                        foreach ($entry['doz'] as $doz_key) {
                            foreach ($persons as $p_nr => $person) {
                                if ($person['key'] == 'Person.' . $doz_key) {
                                    // unset($person['key']);
                                    $ret[$e_nr]['lecturers'][] = $person;
                                    unset($person[$p_nr]);
                                }
                            }
                        }
                        unset($ret[$e_nr]['doz']);
                    }
                }
                foreach ($delNr as $nr) {
                    unset($ret[$nr]);
                }
                // add room details
                $rooms = $this->mapIt('roomByID', $data);
                foreach ($ret as $nr => $entry) {
                    if (isset($entry['courses'])) {
                        foreach ($entry['courses'] as $c_nr => $course) {
                            foreach ($course['term'] as $t_nr => $term) {
                                foreach ($rooms as $room) {
                                    if (isset($term['room']) && $term['room'] == $room['key']) {
                                        $ret[$nr]['courses'][$c_nr]['term'][$t_nr]['room'] = $room;
                                    }
                                }
                            }
                        }
                    }
                }
                break;
        }

        return $ret;
    }

    public function sortGroup($dataType, &$data)
    {
        if (empty($data)) {
            return [];
        }
        // group by lecture_type_long
        if (in_array($dataType, ['lectureByID', 'lectureByLecturerID', 'lectureByLecturer', 'lectureByDepartment'])) {

            // 2021-09-23 quickfix because there is a bug in DIP-API's filtering by language
            if (!empty($this->atts['lang'])) {
                $data = $this->filterByLang($data);
            }

            // 2021-10-01 quickfix because there is a bug in DIP-API's filtering by type
            if (!empty($this->atts['type'])) {
                $data = $this->filterByType($data);
            }

            // 2022-01-13 DIP-API's does not support filtering by gast ("für Gaststudium geeignet")
            if (!empty($this->atts['gast'])) {
                $data = $this->filterByGast($data);
            }

            $data = $this->groupBy($data, 'lecture_type_long');

            // sort by attribute "order"
            if (!empty($this->atts['order'])) {
                $aOrder = explode(',', $this->atts['order']);
                $sortedData = [];
                foreach ($aOrder as $order) {
                    foreach ($data as $lecture_type_long => $lectures) {
                        foreach ($lectures as $lecture) {
                            if ($lecture['lecture_type'] == trim($order)) {
                                $sortedData[$lecture_type_long] = $data[$lecture_type_long];
                                unset($data[$lecture_type_long]);
                                break 1;
                            }
                        }
                    }
                }
                $data = $sortedData;
            }
        }
        // sort by name
        if (in_array($dataType, ['departmentByName', 'departmentAll'])) {
            usort($data, [$this, 'sortByName']);
        }

        return $data;
    }

    private function filterByGast($arr)
    {
        $ret = [];
        foreach ($arr as $key => $val) {
            if (!empty($val['gast']) && ($val['gast'] == $this->gast)) {
                $ret[$key] = $val;
            }
        }
        return $ret;
    }

    private function filterByLang($arr)
    {
        $ret = [];
        foreach ($arr as $key => $val) {
            if (!empty($val['leclanguage']) && ($val['leclanguage'] == $this->atts['lang'])) {
                $ret[$key] = $val;
            }
        }
        return $ret;
    }

    private function multiMap($val)
    {
        return trim(strtolower($val));
    }

    private function filterByType($arr)
    {
        $ret = [];
        $aTypes = array_map([$this, 'multiMap'], explode(',', $this->atts['type']));

        foreach ($arr as $key => $val) {
            if (!empty($val['lecture_type']) && in_array($val['lecture_type'], $aTypes)) {
                $ret[$key] = $val;
            }
        }

        return $ret;
    }

    private function groupBy($arr, $key)
    {
        $ret = [];
        foreach ($arr as $val) {
            if (!empty($val[$key])) {
                $ret[$val[$key]][] = $val;
            }
        }
        return $ret;
    }

    private function sortByLastname($a, $b)
    {
        return strcasecmp($a["lastname"], $b["lastname"]);
    }

    private function sortByName($a, $b)
    {
        return strcasecmp($a["name"], $b["name"]);
    }

    private function sortByYear($a, $b)
    {
        return strcasecmp($b["year"], $a["year"]);
    }

    public static function checkSemester($sem)
    {
        return preg_match('/[12]\d{3}[ws]/', $sem);
    }

    public static function correctPhone($phone)
    {
        if ((strpos($phone, '+49 9131 85-') !== 0) && (strpos($phone, '+49 911 5302-') !== 0)) {
            if (!preg_match('/\+49 [1-9][0-9]{1,4} [1-9][0-9]+/', $phone)) {
                $phone_data = preg_replace('/\D/', '', $phone);
                $vorwahl_erl = '+49 9131 85-';
                $vorwahl_erl_p1_p6 = '+49 9131 81146-'; // see: https://github.com/RRZE-Webteam/fau-person/issues/353
                $vorwahl_nbg = '+49 911 5302-';

                switch (strlen($phone_data)) {
                    case '3':
                        $phone = $vorwahl_nbg . $phone_data;
                        break;

                    case '5':
                        if (strpos($phone_data, '06') === 0) {
                            $phone = $vorwahl_nbg . substr($phone_data, -3);
                            break;
                        }
                        $phone = $vorwahl_erl . $phone_data;
                        break;

                    case '7':
                        if (strpos($phone_data, '85') === 0 || strpos($phone_data, '06') === 0) {
                            $phone = $vorwahl_erl . substr($phone_data, -5);
                            break;
                        }

                        if (strpos($phone_data, '5302') === 0) {
                            $phone = $vorwahl_nbg . substr($phone_data, -3);
                            break;
                        }

                    // no break
                    default:
                        if (strpos($phone_data, '9115302') !== false) {
                            $durchwahl = explode('9115302', $phone_data);
                            if (strlen($durchwahl[1]) === 3 || strlen($durchwahl[1]) === 5) {
                                $phone = $vorwahl_nbg . $durchwahl[1];
                            }
                            break;
                        }

                        if (strpos($phone_data, '913185') !== false) {
                            $durchwahl = explode('913185', $phone_data);
                            if (strlen($durchwahl[1]) === 5) {
                                $phone = $vorwahl_erl . $durchwahl[1];
                            }
                            break;
                        }

                        // see: https://github.com/RRZE-Webteam/fau-person/issues/353
                        if (strpos($phone_data, '913181146') !== FALSE) {
                            $durchwahl = explode('913181146', $phone_data);
                            $phone = $vorwahl_erl_p1_p6 . $durchwahl[1];
                            break;
                        }

                        if (strpos($phone_data, '09131') === 0 || strpos($phone_data, '499131') === 0) {
                            $durchwahl = explode('9131', $phone_data);
                            $phone = "+49 9131 " . $durchwahl[1];
                            break;
                        }

                        if (strpos($phone_data, '0911') === 0 || strpos($phone_data, '49911') === 0) {
                            $durchwahl = explode('911', $phone_data);
                            $phone = "+49 911 " . $durchwahl[1];
                            break;
                        }
                }
            }
        }
        return $phone;
    }

    public function getInt($str)
    {
        preg_match_all('/\d+/', $str, $matches);
        return implode('', $matches[0]);
    }

    public function formatDIP($txt)
    {
        $subs = array(
            '/^\-+\s+(.*)?/mi' => '<ul><li>$1</li></ul>', // list
            '/(<\/ul>\n(.*)<ul>*)+/' => '', // list
            '/\*{2}/m' => '/\*/', // **
            '/_{2}/m' => '/_/', // __
            '/\|(.*)\|/m' => '<i>$1</i>', // |itallic|
            '/_(.*)_/m' => '<sub>$1</sub>', // H_2_O
            '/\^(.*)\^/m' => '<sup>$1</sup>', // pi^2^
            '/\[([^\]]*)\]\s{0,1}((http|https|ftp|ftps):\/\/\S*)/mi' => '<a href="$2">$1</a>', // [link text] http...
            '/\[([^\]]*)\]\s{0,1}(mailto:)([^")\s<>]+)/mi' => '<a href="mailto:$3">$1</a>', // find [link text] mailto:email@address.tld but not <a href="mailto:email@address.tld">mailto:email@address.tld</a>
            '/\*(.*)\*/m' => '<strong>$1</strong>', // *bold*
        );

        $txt = preg_replace(array_keys($subs), array_values($subs), $txt);
        $txt = nl2br($txt);
        $txt = make_clickable($txt);
        return $txt;
    }

    private function dict(&$data)
    {
        $fields = [
            'title' => [
                "Dr." => __('Doctor', 'rrze-univis'),
                "Prof." => __('Professor', 'rrze-univis'),
                "Dipl." => __('Diploma', 'rrze-univis'),
                "Inf." => __('Computer Science', 'rrze-univis'),
                "Wi." => __('Business Informatics', 'rrze-univis'),
                "Ma." => __('Math', 'rrze-univis'),
                "Ing." => __('Engineering', 'rrze-univis'),
                "B.A." => __('Bachelor', 'rrze-univis'),
                "M.A." => __('Magister Artium', 'rrze-univis'),
                "phil." => __('Humanities', 'rrze-univis'),
                "pol." => __('Political Science', 'rrze-univis'),
                "nat." => __('Natural Science', 'rrze-univis'),
                "soc." => __('Social Science', 'rrze-univis'),
                "techn." => __('Technical Sciences', 'rrze-univis'),
                "vet.med." => __('Veterinary Medicine', 'rrze-univis'),
                "med.dent." => __('Dentistry', 'rrze-univis'),
                "h.c." => __('honorary', 'rrze-univis'),
                "med." => __('medicine', 'rrze-univis'),
                "jur." => __('law', 'rrze-univis'),
                "rer." => "",
            ],
            'lecture_type' => [
                "awa" => __('Instructions for scientific work (AWA)', 'rrze-univis'),
                "ku" => __('Course (KU)', 'rrze-univis'),
                "ak" => __('Advanced course (AK)', 'rrze-univis'),
                "ex" => __('Excursion (EX)', 'rrze-univis'),
                "gk" => __('Basic course (GK)', 'rrze-univis'),
                "sem" => __('Seminar (SEM)', 'rrze-univis'),
                "es" => __('Exam seminar (ES)', 'rrze-univis'),
                "ts" => __('Theory Seminar (TS)', 'rrze-univis'),
                "ag" => __('Working group (AG)', 'rrze-univis'),
                "mas" => __('Master seminar (MAS)', 'rrze-univis'),
                "gs" => __('Basic seminar (GS)', 'rrze-univis'),
                "us" => __('Training seminar (US)', 'rrze-univis'),
                "as" => __('Advanced seminar (AS)', 'rrze-univis'),
                "hs" => __('Main seminar (HS)', 'rrze-univis'),
                "re" => __('Repetitorium (RE)', 'rrze-univis'),
                "kk" => __('Exam course (KK)', 'rrze-univis'),
                "klv" => __('Clinical visit (KLV)', 'rrze-univis'),
                "ko" => __('Colloquium (KO)', 'rrze-univis'),
                "ks" => __('Combined seminar (KS)', 'rrze-univis'),
                "ek" => __('Introductory course (EK)', 'rrze-univis'),
                "ms" => __('Middle seminar (MS)', 'rrze-univis'),
                "os" => __('Upper seminar (OS)', 'rrze-univis'),
                "pr" => __('Internship (PR)', 'rrze-univis'),
                "prs" => __('Practice seminar (PRS)', 'rrze-univis'),
                "pjs" => __('Project Seminar (PJS)', 'rrze-univis'),
                "ps" => __('Pro seminar (PS)', 'rrze-univis'),
                "sl" => __('Other courses (SL)', 'rrze-univis'),
                "tut" => __('Tutorial (TUT)', 'rrze-univis'),
                "v-ue" => __('Lecture with exercise (V/UE)', 'rrze-univis'),
                "ue" => __('Exercise (UE)', 'rrze-univis'),
                "vorl" => __('Lecture (VORL)', 'rrze-univis'),
                "hvl" => __('Main Lecture (HVL)', 'rrze-univis'),
                "pf" => __('Examination (PF)', 'rrze-univis'),
                "gsz" => __('Committee meeting (GSZ)', 'rrze-univis'),
                "ppu" => __('Propaedeutic Exercise (PPU)', 'rrze-univis'),
                "his" => __('History of Languages Seminar (HIS)', 'rrze-univis'),
                "bsem" => __('Accompanying seminar (BSEM)', 'rrze-univis'),
                "kol" => __('College (KOL)', 'rrze-univis'),
                "mhs" => __('MS (HS, PO 2020) (MHS)', 'rrze-univis'),
                "pgmas" => __('PG Master Seminar (PGMAS)', 'rrze-univis'),
                "pms" => __('PS (MS, PO 2020) (PMS)', 'rrze-univis'),
            ],
            'repeat' => [
                "w1" => "",
                "w2" => __('Every other week', 'rrze-univis'),
                "w3" => __('Every third week', 'rrze-univis'),
                "w4" => __('Every fourth week', 'rrze-univis'),
                "w5" => "",
                "m1" => "",
                "s1" => __('single appointment on', 'rrze-univis'),
                "bd" => __('block event', 'rrze-univis'),
                '0' => __(' Su', 'rrze-univis'),
                '1' => __(' Mo', 'rrze-univis'),
                '2' => __(' Tue', 'rrze-univis'),
                '3' => __(' Wed', 'rrze-univis'),
                '4' => __(' Thu', 'rrze-univis'),
                '5' => __(' Fr', 'rrze-univis'),
                '6' => __(' Sa', 'rrze-univis'),
                '7' => __(' Su', 'rrze-univis'),
            ],
            'publication_type' => [
                "artmono" => __('Article in anthology', 'rrze-univis'),
                "arttagu" => __('Article in proceedings', 'rrze-univis'),
                "artzeit" => __('Article in magazine', 'rrze-univis'),
                "techrep" => __('Internal Report (Technical Report, Research Report)', 'rrze-univis'),
                "hschri" => __('University thesis (dissertation, habilitation thesis, diploma thesis etc.)', 'rrze-univis'),
                "dissvg" => __('Thesis (also published by the publisher)', 'rrze-univis'),
                "monogr" => __('Monograph', 'rrze-univis'),
                "tagband" => __('Conference volume (not published by the publisher)', 'rrze-univis'),
                "schutzr" => __('IPR', 'rrze-univis'),
                ],
            'hstype' => [
                "diss" => __('Dissertation', 'rrze-univis'),
                "dipl" => __('Diploma', 'rrze-univis'),
                "mag" => __('Master\'s thesis', 'rrze-univis'),
                "stud" => __('Study paper', 'rrze-univis'),
                "habil" => __('Habilitation thesis', 'rrze-univis'),
                "masth" => __('Master\'s thesis', 'rrze-univis'),
                "bacth" => __('Bachelor thesis', 'rrze-univis'),
                "intber" => __('Internal Report', 'rrze-univis'),
                "diskus" => __('Discussion paper', 'rrze-univis'),
                "discus" => __('Discussion paper', 'rrze-univis'),
                "forber" => __('Research report', 'rrze-univis'),
                "absber" => __('Final report', 'rrze-univis'),
                "patschri" => __('Patent specification', 'rrze-univis'),
                "offenleg" => __('Disclosure document', 'rrze-univis'),
                "patanmel" => __('Patent application', 'rrze-univis'),
                "gebrmust" => __('Utility model', 'rrze-univis'),
                ],
            'leclanguage' => [
                0 => __('Lecture\'s language German', 'rrze-univis'),
                "D" => __('Lecture\'s language German', 'rrze-univis'),
                "E" => __('Lecture\'s language English', 'rrze-univis'),
                ],
            'sws' => __(' SWS', 'rrze-univis'),
            'schein' => __('Certificate', 'rrze-univis'),
            'ects' => __('ECTS studies', 'rrze-univis'),
            'ects_cred' => __('ECTS credits: ', 'rrze-univis'),
            'beginners' => __('Suitable for beginners', 'rrze-univis'),
            'fruehstud' => __('Early study', 'rrze-univis'),
            'gast' => __('Allowed for guest students', 'rrze-univis'),
            'evaluation' => __('Evaluation', 'rrze-univis'),
            'locations' => '',
            'organizational' => '',
            ];

        foreach ($data as $nr => $row) {
            foreach ($fields as $field => $values) {
                if (isset($data[$nr][$field]) && ($field == 'locations')) {
                    foreach ($data[$nr]['locations'] as $l_nr => $location) {
                        if (!empty($location['tel'])) {
                            $data[$nr]['locations'][$l_nr]['tel'] = self::correctPhone($data[$nr]['locations'][$l_nr]['tel']);
                            $data[$nr]['locations'][$l_nr]['tel_call'] = '+' . self::getInt($data[$nr]['locations'][$l_nr]['tel']);
                        }
                        if (!empty($location['fax'])) {
                            $data[$nr]['locations'][$l_nr]['fax'] = self::correctPhone($data[$nr]['locations'][$l_nr]['fax']);
                        }
                        if (!empty($location['mobile'])) {
                            $data[$nr]['locations'][$l_nr]['mobile'] = self::correctPhone($data[$nr]['locations'][$l_nr]['mobile']);
                            $data[$nr]['locations'][$l_nr]['mobile_call'] = '+' . self::getInt($data[$nr]['locations'][$l_nr]['mobile']);
                        }
                    }
                } elseif ($field == 'repeat') {
                    if (isset($data[$nr]['courses'])) {
                        foreach ($data[$nr]['courses'] as $c_nr => $course) {
                            foreach ($course['term'] as $m_nr => $meeting) {
                                if (isset($data[$nr]['courses'][$c_nr]['term'][$m_nr]['repeat'])) {
                                    $data[$nr]['courses'][$c_nr]['term'][$m_nr]['repeat'] = str_replace(array_keys($values), array_values($values), $data[$nr]['courses'][$c_nr]['term'][$m_nr]['repeat']);
                                }
                            }
                        }
                    } elseif (isset($data[$nr]['officehours'])) {
                        foreach ($data[$nr]['officehours'] as $c_nr => $entry) {
                            if (isset($data[$nr]['officehours'][$c_nr]['repeat'])) {
                                $data[$nr]['officehours'][$c_nr]['repeat'] = trim(str_replace(array_keys($values), array_values($values), $data[$nr]['officehours'][$c_nr]['repeat']));
                            }
                        }
                    }
                } elseif ($field == 'organizational') {
                    if (isset($data[$nr][$field])) {
                        $data[$nr][$field] = self::formatDIP($data[$nr][$field]);
                    }
                } elseif (isset($data[$nr][$field])) {
                    if (in_array($field, ['title'])) {
                        // multi replace
                        $data[$nr][$field . '_long'] = str_replace(array_keys($values), array_values($values), $data[$nr][$field]);
                    } else {
                        if (!is_array($values)) {
                            if ($field == 'sws') {
                                $data[$nr][$field] .= $values;
                            } elseif ($field == 'ects_cred') {
                                $data[$nr][$field] = $values . $data[$nr][$field];
                            } else {
                                $data[$nr][$field] = $values;
                            }
                        } else {
                            if (isset($row[$field]) && isset($values[$row[$field]])) {
                                $data[$nr][$field . '_long'] = $values[$row[$field]];
                                if ($field == 'lecture_type') {
                                    $data[$nr][$field . '_short'] = trim(substr($values[$row[$field]], 0, strpos($values[$row[$field]], '(')));
                                }
                            }
                        }
                    }
                }
            }
        }
        return $data;
    }

}