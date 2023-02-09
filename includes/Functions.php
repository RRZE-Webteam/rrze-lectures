<?php

namespace RRZE\Lectures;

defined('ABSPATH') || exit;

class Functions
{

    protected $pluginFile;
    const TRANSIENT_PREFIX = 'rrze_lecture_cache_';
    const TRANSIENT_OPTION = 'rrze_lecture_cache_transients';
    const TRANSIENT_EXPIRATION = DAY_IN_SECONDS;


    public function __construct($pluginFile)
    {
        $this->pluginFile = $pluginFile;
    }

    public function onLoaded()
    {
        // add_action('admin_enqueue_scripts', [$this, 'adminEnqueueScripts']);
        add_action('wp_ajax_GetLectureData', [$this, 'ajaxGetLectureData']);
        add_action('wp_ajax_nopriv_GetLectureData', [$this, 'ajaxGetLectureData']);
        add_action('wp_ajax_GetLectureDataForBlockelements', [$this, 'ajaxGetLectureDataForBlockelements']);
        add_action('wp_ajax_nopriv_GetLectureDataForBlockelements', [$this, 'ajaxGetLectureDataForBlockelements']);
        add_action('wp_ajax_GenerateICS', [$this, 'ajaxGenerateICS']);
        add_action('wp_ajax_nopriv_GenerateICS', [$this, 'ajaxGenerateICS']);
    }

    public static function console_log($msg = '', $tsStart = 0)
    {
        if (isset($_GET['debug'])) {
            $msg .= ' execTime: ' . sprintf('%.2f', microtime(true) - $tsStart) . ' s';
            echo '<script>console.log(' . json_encode($msg, JSON_HEX_TAG) . ');</script>';
        }
    }


    public static function getSemester($iSem = 0){
        // Bei Campo ist das Sommersemester immer vom 01.04. bis zum 30.09. des Jahres. 
        // Das Wintersemester entsprechend vom 01.10. des Jahres bis zum 31.03. des folgenden Jahres
        $today = date('Y-m-d');
        $thisYear = date('Y');
        $thisMonth = date('m');
        $nextYear = date('Y', strtotime('+1 year'));
        $soseStart = date('Y-m-d', strtotime($thisYear . '-04-01'));
        $soseEnd = date('Y-m-d', strtotime($thisYear . '-09-30'));

        $year = $thisYear;

        if (($today >= $soseStart) && ($today <= $soseEnd)){
            $sem = 'SoSe';
        }else{
            $sem = 'WiSe';
            if ($thisMonth >= 10){
                $year = $nextYear;
            }
        }

        // 2DO: calc year based on $thisQuarter and $iSem
        if (!empty($iSem)){
            $thisQuarter = ceil($thisMonth / 3);

            if ($iSem % 2 != 0) {
                $sem = ($sem == 'SoSe' ? 'WiSe' : 'SoSe');
            }

            switch($thisQuarter){
                case 1:
                    $iDiff = 2;
                    break;
                case 2:
                case 3:
                    $iDiff = 1;
                    break;
                case 4:
                    $iDiff = 0;
                    break;
            }

            $year += (($iSem - $iDiff) % 2) + 1;
        }

        return $sem . $year;
    }

    public static function makeLQ($aIn)
    {
        $aLQ = [];
        foreach ($aIn as $dipField => $attVal) {
            $attVal = sanitize_text_field($attVal);
            $aTmp = array_map('trim', explode(',', $attVal));

            // check if 10 figures hex 
            if ($dipField == 'providerValues.courses.course_responsible.identifier') {
                foreach ($aTmp as $nr => $val) {
                    if (!(ctype_xdigit($val) && strlen($val) == 10)) {
                        unset($aTmp[$nr]);
                    }
                }
            }

            $aLQ[] = $dipField . (count($aTmp) > 1 ? '[in]=' : '=') . implode(';', $aTmp);
        }

        return implode('&', $aLQ);
    }

    public static function convertDate($tz, $timezone, $format)
    {
        $dt = new \DateTime($tz, new \DateTimeZone($timezone));
        $dt->setTimezone(new \DateTimeZone('Europe/Berlin'));
        $ret = $dt->format($format);
        if ($format == "N") {
            switch ($ret) {
                case 1:
                    $ret = __('Mon', 'rrze-lectures');
                    break;
                case 2:
                    $ret = __('Tue', 'rrze-lectures');
                    break;
                case 3:
                    $ret = __('Wed', 'rrze-lectures');
                    break;
                case 4:
                    $ret = __('Thu', 'rrze-lectures');
                    break;
                case 5:
                    $ret = __('Fri', 'rrze-lectures');
                    break;
                case 6:
                    $ret = __('Sat', 'rrze-lectures');
                    break;
                case 7:
                    $ret = __('Sun', 'rrze-lectures');
                    break;
            }
        }
        return $ret;
    }


    public function ajaxGenerateICS()
    {
        check_ajax_referer('lecture-ajax-ics-nonce', 'ics_nonce');
        $inputs = filter_input(INPUT_GET, 'data', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
        $aProps = json_decode(openssl_decrypt(base64_decode($inputs['v']), 'AES-256-CBC', hash('sha256', AUTH_KEY), 0, substr(hash('sha256', AUTH_SALT), 0, 16)), true);

        $ics = new ICS($aProps);
        $response = [
            'icsData' => $ics->toString(),
            'filename' => sanitize_file_name($aProps['FILENAME'] . '.ics')
        ];

        wp_send_json($response);
    }

    public function enqueueScripts()
    {
        wp_enqueue_script(
            'rrze-lectures-ajax-frontend',
            plugins_url('js/rrze-lectures-frontend.js', plugin_basename($this->pluginFile)),
            ['jquery'],
            null
        );

        wp_localize_script('rrze-lectures-ajax-frontend', 'lecture_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'ics_nonce' => wp_create_nonce('lecture-ajax-ics-nonce'),
        ]);
    }

    public function adminEnqueueScripts()
    {
        wp_enqueue_script(
            'rrze-lectures-ajax',
            plugins_url('js/rrze-lectures.js', plugin_basename($this->pluginFile)),
            ['jquery'],
            null
        );

        wp_localize_script('rrze-lectures-ajax', 'lecture_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('lecture-ajax-nonce'),
        ]);
    }


    public function getTableHTML($aIn)
    {
        if (!is_array($aIn)) {
            return $aIn;
        }
        $ret = '<table class="wp-list-table widefat striped"><thead><tr><td><b><i>Univ</i>IS</b> ID</td><td><strong>Name</strong></td></tr></thead>';
        foreach ($aIn as $ID => $val) {
            $ret .= "<tr><td>$ID</td><td style='word-wrap: break-word;'>$val</td></tr>";
        }
        $ret .= '</table>';
        return $ret;
    }

    public static function setDataToCache($data, $aAtts = [])
    {
        $ret = set_transient(self::TRANSIENT_PREFIX . md5(json_encode($aAtts)), $data, self::TRANSIENT_EXPIRATION);

        // if ($ret){
        //     // lets store $transient in an option to delete them on save using deleteTransients()
        //     $aOptions = get_option(self::TRANSIENT_OPTION);

        //     if (!empty($aOptions)) {
        //         $aOptions[] = $transient;
        //     } else {
        //         $aOptions = [$transient];
        //     }

        //     update_option(self::TRANSIENT_OPTION, $aOptions);
        // }
    }

    public static function getDataFromCache($aAtts = [])
    {
        return get_transient(self::TRANSIENT_PREFIX . md5(json_encode($aAtts)));
    }


    public function deleteTransients()
    {
        $aTransients = get_option(self::TRANSIENT_OPTION);
        foreach ($aTransients as $transient) {
            delete_transient($transient);
        }
        update_option(self::TRANSIENT_OPTION, '');
    }


    public function ajaxGetLectureData()
    {
        check_ajax_referer('lecture-ajax-nonce', 'nonce');
        $inputs = filter_input(INPUT_POST, 'data', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
        $response = $this->getTableHTML($this->getLectureData(null, $inputs['dataType'], $inputs['keyword']));
        wp_send_json($response);
    }

    public function getSelectHTML($aIn)
    {
        if (!is_array($aIn)) {
            return "<option value=''>$aIn</option>";
        }
        $ret = '<option value="">' . __('-- All --', 'rrze-lectures') . '</option>';
        natsort($aIn);
        foreach ($aIn as $ID => $val) {
            $ret .= "<option value='$ID'>$val</option>";
        }
        return $ret;
    }

    public function getLectureData($lectureOrgID = null, $dataType = '', $keyword = null)
    {
        $data = false;
        $ret = __('No matching entries found.', 'rrze-lectures');

        $options = get_option('rrze-lectures');
        $data = 0;
        $DIPURL = (!empty($options['basic_lecture_url']) ? $options['basic_lecture_url'] : 'https://lecture.uni-erlangen.de');
        $lectureOrgID = (!empty($lectureOrgID) ? $lectureOrgID : (!empty($options['basic_DIPOrgNr']) ? $options['basic_DIPOrgNr'] : 0));

        if ($DIPURL) {
            $lecture = new DIPAPI($DIPURL, $lectureOrgID, null);
            $data = $lecture->getData($dataType, $keyword);
        } elseif (!$DIPURL) {
            $ret = __('Link to DIP is missing.', 'rrze-lectures');
        }

        if ($data) {
            $ret = [];
            switch ($dataType) {
                // case 'departmentByName':
                //     foreach ($data as $entry) {
                //         if (isset($entry['orgnr'])) {
                //             $ret[$entry['orgnr']] = $entry['name'];
                //         }
                //     }
                //     break;
                // case 'personByName':
                //     foreach ($data as $entry) {
                //         if (isset($entry['person_id'])) {
                //             $ret[$entry['person_id']] = $entry['lastname'] . ', ' . $entry['firstname'];
                //         }
                //     }
                //     break;
                // case 'personAll':
                //     foreach ($data as $position => $entries) {
                //         foreach ($entries as $entry) {
                //             if (isset($entry['person_id'])) {
                //                 $ret[$entry['person_id']] = $entry['lastname'] . ', ' . $entry['firstname'];
                //             }
                //         }
                //     }
                //     break;
                case 'lectureByName':
                    foreach ($data as $entry) {
                        if (isset($entry['lecture_id'])) {
                            $ret[$entry['lecture_id']] = $entry['name'];
                        }
                    }
                    break;
                case 'lectureByDepartment':
                    foreach ($data as $type => $entries) {
                        foreach ($entries as $entry) {
                            if (isset($entry['lecture_id'])) {
                                $ret[$entry['lecture_id']] = $entry['name'];
                            }
                        }
                    }
                    break;
                default:
                    $ret = 'unknown dataType';
                    break;
            }
        }

        return $ret;
    }

    public function ajaxGetLectureDataForBlockelements()
    {
        check_ajax_referer('lecture-ajax-nonce', 'nonce');
        $inputs = filter_input(INPUT_POST, 'data', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
        $response = $this->getSelectHTML($this->getLectureData($inputs['lectureOrgID'], $inputs['dataType']));
        wp_send_json($response);
    }

    public static function makeLinkToICS($type, $lecture, $term, $t)
    {
        $aProps = [
            'SUMMARY' => $lecture['title'],
            'LOCATION' => (!empty($t['room']) ? $t['room'] : null),
            'DESCRIPTION' => (!empty($lecture['comment']) ? $lecture['comment'] : null),
            'URL' => get_permalink(),
            'MAP' => (!empty($term['room']['north']) && !empty($term['room']['east']) ? 'https://karte.fau.de/api/v1/iframe/marker/' . $term['room']['north'] . ',' . $term['room']['east'] . '/zoom/16' : ''),
            'FILENAME' => sanitize_file_name($type),
        ];

        if (empty($term['startdate']) || empty($term['enddate'])) {
            $thisMonth = date('m');

            if ($thisMonth > 2 && $thisMonth < 8) {
                $sem = 'ss';
            } else {
                $sem = 'ws';
            }

            $options = get_option('rrze-lectures');
            $semStart = (!empty($options['basic_' . $sem . 'Start']) ? $options['basic_' . $sem . 'Start'] : null);
            $semEnd = (!empty($options['basic_' . $sem . 'End']) ? $options['basic_' . $sem . 'End'] : null);

            if (empty($semStart) || empty($semEnd)) {
                $defaults = getFields();
                foreach ($defaults['basic'] as $nr => $aVal) {
                    if ($aVal['name'] == $sem . 'Start') {
                        $semStart = $aVal['default'];
                        break;
                    } elseif ($aVal['name'] == $sem . 'End') {
                        $semEnd = $aVal['default'];
                        break;
                    }
                }

                $semStart = (!empty($semStart) ? $semStart : $defaults['basic'][$sem . 'Start']['default']);
                $semEnd = (!empty($semEnd) ? $semEnd : $defaults['basic'][$sem . 'End']['default']);
            }
        }

        $aFreq = [
            "w1" => 'WEEKLY;INTERVAL=1',
            "w2" => 'WEEKLY;INTERVAL=2',
            "w3" => 'WEEKLY;INTERVAL=3',
            "w4" => 'WEEKLY;INTERVAL=4',
            "m1" => 'MONTHLY;INTERVAL=1',
            "m2" => 'MONTHLY;INTERVAL=2',
            "m3" => 'MONTHLY;INTERVAL=3',
            "m4" => 'MONTHLY;INTERVAL=4',
        ];

        $aWeekdays = [
            '1' => [
                'short' => 'MO',
                'long' => 'Monday',
            ],
            '2' => [
                'short' => 'TU',
                'long' => 'Tuesday',
            ],
            '3' => [
                'short' => 'WE',
                'long' => 'Wednesday',
            ],
            '4' => [
                'short' => 'TH',
                'long' => 'Thursday',
            ],
            '5' => [
                'short' => 'FR',
                'long' => 'Friday',
            ],
        ];

        $aGivenDays = [];

        if (!empty($term['repeatNr'])) {
            $aParts = explode(' ', $term['repeatNr']);
            if (!empty($aFreq[$aParts[0]])) {
                $aProps['FREQ'] = $aFreq[$aParts[0]];
                $aGivenDays = explode(',', $aParts[1]);
                $aProps['REPEAT'] = '';
                foreach ($aWeekdays as $nr => $val) {
                    if (in_array($nr, $aGivenDays)) {
                        $aProps['REPEAT'] .= $val['short'] . ',';
                    }
                }
                $aProps['REPEAT'] = rtrim($aProps['REPEAT'], ',');
            }
        }

        $tStart = (empty($term['starttime']) ? '00:00' : $term['starttime']);
        $tEnd = (empty($term['endtime']) ? '23:59' : $term['endtime']);
        $dStart = (empty($term['startdate']) ? $semStart : $term['startdate']);
        $dEnd = (empty($term['startdate']) ? $semEnd : $term['enddate']);
        $aProps['DTSTART'] = date('Ymd\THis', strtotime(date('Ymd', strtotime($dStart)) . date('Hi', strtotime($tStart))));
        $aProps['DTEND'] = date('Ymd\THis', strtotime(date('Ymd', strtotime($dStart)) . date('Hi', strtotime($tEnd))));
        $aProps['UNTIL'] = date('Ymd\THis', strtotime(date('Ymd', strtotime($dEnd)) . date('Hi', strtotime($tEnd))));

        if (!empty($aGivenDays)) {
            // check if day of week of DTSTART is a member of the REPEAT days
            $givenWeekday = date('N', strtotime($aProps['DTSTART']));
            if (!in_array($givenWeekday, $aGivenDays)) {
                // move to next possible date
                while (!in_array($givenWeekday, $aGivenDays)) {
                    $givenWeekday++;
                    $givenWeekday = ($givenWeekday > 5 ? 1 : $givenWeekday);
                    if (in_array($givenWeekday, $aGivenDays)) {
                        $aProps['DTSTART'] = date('Ymd', strtotime("next " . $aWeekdays[$givenWeekday]['long'], strtotime($aProps['DTSTART'])));
                        $aProps['DTEND'] = $aProps['DTSTART'] . date('\THis', strtotime($tEnd));
                        $aProps['DTSTART'] .= date('\THis', strtotime($tStart));
                        break;
                    }
                }
            }
        }

        $propsEncoded = base64_encode(openssl_encrypt(json_encode($aProps), 'AES-256-CBC', hash('sha256', AUTH_KEY), 0, substr(hash('sha256', AUTH_SALT), 0, 16)));
        $linkParams = [
            'v' => $propsEncoded,
            'h' => hash('sha256', $propsEncoded),
        ];

        return [
            'link' => http_build_query($linkParams),
            'linkTxt' => __('ICS', 'rrze-lectures') . ': ' . __('Date', 'rrze-lectures') . ' ' . (!empty($t['repeat']) ? $t['repeat'] : '') . ' ' . (!empty($t['date']) ? $t['date'] . ' ' : '') . $t['time'] . ' ' . __('import to calendar', 'rrze-lectures'),
        ];
    }

}