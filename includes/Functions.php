<?php

namespace RRZE\Lectures;
use function RRZE\Lectures\Config\getConstants;

defined('ABSPATH') || exit;

class Functions {

    protected $pluginFile;

    public function __construct($pluginFile) {
        $this->pluginFile = $pluginFile;
    }

    public function onLoaded() {
        add_action('admin_enqueue_scripts', [$this, 'adminEnqueueScripts']);
        add_action('wp_ajax_GetFAUOrgNr', [$this, 'ajaxGetFAUOrgNr']);
        add_action('wp_ajax_nopriv_GetFAUOrgNr', [$this, 'ajaxGetFAUOrgNr']);
        
        add_action('wp_ajax_GetLecturerIdentifier', [$this, 'ajaxGetLecturerIdentifier']); 
        add_action('wp_ajax_nopriv_GetLecturerIdentifier', [$this, 'ajaxGetLecturerIdentifier']);

        add_filter( 'update_option_rrze-lectures',  [$this, 'checkAPIKey'], 10, 1 );


        // add_action('wp_ajax_GetLectureDataForBlockelements', [$this, 'ajaxGetDIPDataForBlockelements']);
        // add_action('wp_ajax_nopriv_GetLectureDataForBlockelements', [$this, 'ajaxGetDIPDataForBlockelements']);
        // add_action('wp_ajax_GenerateICS', [$this, 'ajaxGenerateICS']);
        // add_action('wp_ajax_nopriv_GenerateICS', [$this, 'ajaxGenerateICS']);
    }


    public function adminEnqueueScripts()  {
        wp_enqueue_script(
            'rrze-lectures-ajax',
            plugins_url('js/rrze-lectures.js', plugin_basename($this->pluginFile)),
            ['jquery'],
            RRZE_PLUGIN_VERSION
        );

        wp_localize_script('rrze-lectures-ajax', 'lecture_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('lecture-ajax-nonce'),
        ]);
    }

    /*
     * Gets the Errormessage from the config by the given errorkey/code
     */
    public static function getErrorMessage(string|int $errorkey, string $userstring = ''): string {
        if (isset($userstring) && (!empty($userstring))) {
            return $userstring;
        }
        $constants = getConstants();
        if (isset($constants['errors'][$errorkey])) {
             return $constants['errors'][$errorkey];
        }
        return $constants['errors']['default'];
    }

    
    
    public static function getSemester(int $iSem = 0): string  {
        // Bei Campo ist das Sommersemester immer im zweiten und dritten Quartal des Jahres. (1.4.-30.9.)
        // Das Wintersemester entsprechend im vierten des Jahres und ersten Quartal des folgenden Jahres. (1.10.-31.3.)
        $SS = 'SoSe';
        $WS = 'WiSe';
        $curQuarter = ceil(date('m') / 3);
        $year = date('Y');
        $sem = $SS;


        switch ($curQuarter) {
            case 1:
                $year -= 1;
            case 4:
                $sem = $WS;
            // 2 and 3 => SoSe and currYear, therefore no changes.
        }

        if ($iSem) {
            // check if -2, -1, 1 or 2 and casting to int is already done in Shortcode->normalize()

            switch ($iSem) {
                case 1:
                    // Next semester
                    switch ($sem) {
                        case $WS:
                            $sem = $SS;
                            $year += 1;
                            break;
                        case $SS:
                            $sem = $WS; // $year does not change
                            break;
                    }
                    break;
                case 2:
                    // Same semester, but next year
                    $year +=1;
                    break;
                case -1:
                    // previous semester
                    switch ($sem) {
                        case $WS:
                            $sem = $SS;
                            break;
                        case $SS:
                            $sem = $WS;
                            $year -= 1;
                            break;
                    }
                    break;
                case -2:
                    // Same semester, but previous year
                    $year -= 1;
                    break;
            }
        }

        return $sem . $year;
    }

    public static function isLastElement(array $aArr): bool {
        return next($aArr) !== false ?: key($aArr) !== null;
    }

   
    // TODO: Move to sanitizer
    public static function convertDate(string $tz, string $format): string
    {
        $ret = get_date_from_gmt($tz, $format);

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

    

   

    public function getTableHTML(array|string $aIn, array $aFieldnames): array|string
    {
        if (!is_array($aIn)) {
            return $aIn;
        }

        $ret = '<table class="wp-list-table widefat striped"><thead><tr>';

        foreach($aFieldnames as $fieldname){
            $ret .= '<td><strong>' . $fieldname . '</strong></td>';
        }
        $ret .= '</tr></thead>';
        
        foreach ($aIn as $aVal) {
            $ret .= '<tr>';
            foreach($aVal as $val){
                $ret .= '<td style="word-wrap: break-word;">' . $val . '</td>';
            }
            $ret .= '</tr>';
        }
        $ret .= '</table>';

        return $ret;
    }

    public function ajaxGetFAUOrgNr()
    {
        check_ajax_referer('lecture-ajax-nonce', 'nonce');

        $input = array_map(function($a){
            return sanitize_text_field($a);
        }, $_POST['data']);

        $aFieldnames = [
            __('FAU Org Number', 'rrze-lectures'),
            __('Name of organization', 'rrze-lectures')
        ];

        $response = $this->getTableHTML($this->getFAUOrgNr($input['keyword']), $aFieldnames);
        wp_send_json($response);
    }

    public static function checkAPIKey( $options ){
        $oDIP = new DIPAPI();
        $response = $oDIP->getResponse('organizations', '');

        if (!$response['valid'] && $response['code'] == 401) {
            add_settings_error( 'basic_ApiKey', 'dip_api_key_error', self::getErrorMessage('apikeymissing'), 'error' );        
        }

        return $options;
    }

    // TODO: Move to DIPAPI class
    public function getFAUOrgNr(string $keyword = null): array|string {
        $dipParams = '?sort=' . urlencode('name=1') . '&attrs=' . urlencode('disambiguatingDescription;name') . '&q=' . urlencode($keyword);

        $oDIP = new DIPAPI();
        $response = $oDIP->getResponse('organizations', $dipParams);

        if (!$response['valid'] && $response['code'] == 401) {
            return self::getErrorMessage('apikeymissing');
        } else {
            $data = $response['content']['data'];

            if (empty($data)) {
                return self::getErrorMessage('204');
            }

            $ret = [];

            foreach ($data as $aDetails) {
                $ret[] = [
                    $aDetails['disambiguatingDescription'],
                    $aDetails['name'],
                ];
            }
        }

        return $ret;
    }

    public function ajaxGetLecturerIdentifier()  {
        check_ajax_referer('lecture-ajax-nonce', 'nonce');

        $aInputs = array_map(function($a){
            return sanitize_text_field($a);
        }, $_POST['data']);

        $aFieldnames = [
            __('Identifier', 'rrze-lectures'),
            __('Name', 'rrze-lectures'),
            __('Name of organization', 'rrze-lectures')
        ];

        $response = $this->getTableHTML($this->getLecturerIdentifier($aInputs), $aFieldnames);
        wp_send_json($response);
    }

    // TODO: Move to DIPAPI class
    public function getLecturerIdentifier(array $aParams = []): array|string {
        $oDIP = new DIPAPI();
        $lq = $oDIP->makeLQ($aParams);
        $dipParams = '?sort=' . urlencode('familyName=1&givenName=1') . '&attrs=' . urlencode('identifier;familyName;givenName;memberOf.memberOf.name') . '&lq=' . urlencode($lq);
        $response = $oDIP->getResponse('persons', $dipParams);


        if (!$response['valid'] && $response['code'] == 401) {
            return self::getErrorMessage('apikeymissing');
        } else {
            $data = $response['content']['data'];

            if (empty($data)) {
                return self::getErrorMessage('204');
            }

            $ret = [];

            foreach ($data as $aDetails) {
                $ret[] = [
                    $aDetails['identifier'],
                    $aDetails['familyName'] . ', ' . $aDetails['givenName'],
                    $aDetails['memberOf'][0]['memberOf']['name']
                ];
            }
        }

        return $ret;
    }

    public static function isMaintenanceMode(): bool
    {
        if (is_multisite()) {
            $settingsOptions = get_site_option('rrze_settings');
            if (!empty($settingsOptions->plugins->dip_maintenance_mode)) {
                return true;
            }
        }
        return false;
    }



    public function getSelectHTML(array $aIn): string
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

    public static function makeLinkToICS(string $type, array $lecture, array $term, array $t): array
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