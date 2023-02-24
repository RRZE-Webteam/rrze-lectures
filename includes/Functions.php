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
        add_action('admin_enqueue_scripts', [$this, 'adminEnqueueScripts']);
        add_action('wp_ajax_GetFAUOrgNr', [$this, 'ajaxGetFAUOrgNr']);
        add_action('wp_ajax_nopriv_GetFAUOrgNr', [$this, 'ajaxGetFAUOrgNr']);
        // add_action('wp_ajax_GetLecturerIdentifier', [$this, 'ajaxGetLecturerIdentifier']); // deactivated 2023-02-23 because it's not yet settled by CIO if we can present identifiers 
        // add_action('wp_ajax_nopriv_GetLecturerIdentifier', [$this, 'ajaxGetLecturerIdentifier']); // deactivated 2023-02-23 because it's not yet settled by CIO if we can present identifiers 
        // add_action('wp_ajax_GetLectureDataForBlockelements', [$this, 'ajaxGetDIPDataForBlockelements']);
        // add_action('wp_ajax_nopriv_GetLectureDataForBlockelements', [$this, 'ajaxGetDIPDataForBlockelements']);
        // add_action('wp_ajax_GenerateICS', [$this, 'ajaxGenerateICS']);
        // add_action('wp_ajax_nopriv_GenerateICS', [$this, 'ajaxGenerateICS']);
    }

    public static function console_log($msg = '', $tsStart = 0)
    {
        if (isset($_GET['debug'])) {
            $msg .= ' execTime: ' . sprintf('%.2f', microtime(true) - $tsStart) . ' s';
            echo '<script>console.log(' . json_encode($msg, JSON_HEX_TAG) . ');</script>';
        }
    }


    public static function getSemester($iSem = 0)
    {
        // Bei Campo ist das Sommersemester immer vom 01.04. bis zum 30.09. des Jahres. 
        // Das Wintersemester entsprechend vom 01.10. des Jahres bis zum 31.03. des folgenden Jahres
        $today = date('Y-m-d');
        $curMonth = date('m');
        $curQuarter = ceil($curMonth / 3);
        $year = date('Y');
        $sem = 'SoSe';
        $ret = '';
        $iSem = (int) $iSem;

        $soseStart = date('Y-m-d', strtotime($year . '-04-01'));
        $soseEnd = date('Y-m-d', strtotime($year . '-09-30'));

        if (($today >= $soseStart) && ($today <= $soseEnd)) {
            $ret = $sem . $year;
        } else {
            if ($curQuarter == 1) {
                $year -= 1;
            }
            $sem = 'WiSe';
            $ret = $sem . $year;
        }

        if ($iSem) {
            // check if -2, -1, 1 or 2 and casting to int is already done in Shortcode->normalize()
            $curQuarter = ceil($curMonth / 3);

            switch ($iSem) {
                case 1:
                    switch ($curQuarter) {
                        case 1:
                            $sem = 'SoSe';
                            $year += 1;
                            break;
                        case 2:
                        case 3:
                            $sem = 'WiSe'; // $year does not change
                            break;
                        // case 4: // neither $sem nor $year do change
                    }
                    break;
                case 2:
                    switch ($curQuarter) {
                        case 1:
                            $year += 1; // $sem does not change
                            break;
                        case 2:
                        case 3:
                            $sem = 'WiSe'; // $year does not change
                            break;
                        case 4: 
                            $sem = 'SoSe';
                            $year += 1;

                            break;
                    }
                    break;
                case -1:
                    switch ($curQuarter) {
                        // case 1: // neither $sem nor $year do change
                        case 2:
                        case 3:
                            $sem = 'WiSe';
                            $year -= 1;
                            break;
                        case 4:
                            $sem = 'SoSe'; // $year does not change
                            break;
                    }
                    break;
                case -2:
                    switch ($curQuarter) {
                        case 1:
                            $sem = 'SoSe'; // $year does not change
                            break;
                        case 2:
                        case 3:
                            $sem = 'WiSe';
                            $year -= 1;
                            break;
                        case 4: // $sem does not change
                            $year -= 1;
                            break;
                    }
                    break;
            }
            $ret = $sem . $year;
        }

        return $ret;
    }

    public static function isLastElement(array $aArr)
    {
        return next($aArr) !== false ?: key($aArr) !== null;
    }

    public static function makeLQ($aIn)
    {
        $aLQ = [];
        foreach ($aIn as $dipField => $attVal) {
            if (!empty($attVal)){
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

    public function adminEnqueueScripts()
    {
        
        wp_enqueue_script(
            'rrze-lectures-ajax',
            plugins_url('js/rrze-lectures.js', plugin_basename($this->pluginFile)),
            ['jquery'],
            '1.6.4'
        );

        wp_localize_script('rrze-lectures-ajax', 'lecture_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('lecture-ajax-nonce'),
        ]);
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

    public function getTableHTML($aIn, $aFieldnames)
    {
        if (!is_array($aIn)) {
            return $aIn;
        }

        $ret = '<table class="wp-list-table widefat striped"><thead><tr><td><strong>' . $aFieldnames[0] . '</strong></td></td><td><strong>' . $aFieldnames[1] . '</strong></td></tr></thead>';

        foreach ($aIn as $ID => $val) {
            $ret .= "<tr><td>$ID</td><td style='word-wrap: break-word;'>$val</td></tr>";
        }
        $ret .= '</table>';

        return $ret;
    }

    public function ajaxGetFAUOrgNr()
    {
        check_ajax_referer('lecture-ajax-nonce', 'nonce');
        $inputs = filter_input(INPUT_POST, 'data', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);

        $aFieldnames = [
            __('FAU Org Number', 'rrze-lectures'),
            __('Name of organization', 'rrze-lectures')
        ];

        $response = $this->getTableHTML($this->getFAUOrgNr($inputs['keyword']), $aFieldnames);
        wp_send_json($response);
    }

    public function getFAUOrgNr($keyword = null)
    {
        $ret = __('No matching entries found.', 'rrze-lectures');

        $dipParams = '?sort=' . urlencode('name=1') . '&attrs=' . urlencode('disambiguatingDescription;name') . '&q=' . urlencode(sanitize_text_field($keyword));

        $oDIP = new DIPAPI();
        $response = $oDIP->getResponse('organizations', $dipParams);

        if (!$response['valid']) {
            return $ret;
        } else {
            $data = $response['content']['data'];

            if (empty($data)) {
                return $ret;
            }

            $ret = [];

            foreach ($data as $aDetails) {
                $ret[$aDetails['disambiguatingDescription']] = $aDetails['name'];
            }


        }

        return $ret;
    }

    // public function ajaxGetLecturerIdentifier()
    // {
    //     check_ajax_referer('lecture-ajax-nonce', 'nonce');
    //     $aInputs = filter_input(INPUT_POST, 'data', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);

    //     $aFieldnames = [
    //         __('Identifier', 'rrze-lectures'),
    //         __('Name', 'rrze-lectures')
    //     ];

    //     $response = $this->getTableHTML($this->getLecturerIdentifier($aInputs), $aFieldnames);
    //     wp_send_json($response);
    // }


    // public function getLecturerIdentifier($aParams = [])
    // {
    //     $ret = __('No matching entries found.', 'rrze-lectures');
    //     $lq = self::makeLQ($aParams);

    //     $dipParams = '?sort=' . urlencode('familyName=1&givenName=1') . '&attrs=' . urlencode('identifier;familyName;givenName') . '&lq=' . urlencode($lq);

    //     $oDIP = new DIPAPI();
    //     $response = $oDIP->getResponse('persons', $dipParams);

    //     if (!$response['valid']) {
    //         return $ret;
    //     } else {
    //         $data = $response['content']['data'];

    //         if (empty($data)) {
    //             return $ret;
    //         }

    //         $ret = [];

    //         foreach ($data as $aDetails) {
    //             $ret[$aDetails['identifier']] = $aDetails['familyName'] . ', ' . $aDetails['givenName'];
    //         }
    //     }

    //     return $ret;
    // }

    public static function isMaintenanceMode(){        
        if(is_multisite()){
            $settingsOptions = get_site_option('rrze_settings');
            if (!empty($settingsOptions->plugins->dip_maintenance_mode)){
                return true;
            }
        }
        return false;
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


    public function ajaxGetDIPDataForBlockelements()
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
            $curMonth = date('m');

            if ($curMonth > 2 && $curMonth < 8) {
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