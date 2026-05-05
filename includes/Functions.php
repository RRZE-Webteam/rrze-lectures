<?php

namespace RRZE\Lectures;

defined('ABSPATH') || exit;

class Functions {

    protected $pluginFile;

    public function __construct($pluginFile) {
        $this->pluginFile = $pluginFile;
    }

    public function onLoaded() {
        add_action('admin_enqueue_scripts', [$this, 'adminEnqueueScripts']);
        add_action('wp_ajax_GetFAUOrgNr', [$this, 'ajaxGetFAUOrgNr']);
        add_filter( 'update_option_rrze-lectures',  [$this, 'checkAPIKey'], 10, 1 );
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
        $constants = Config::getConstants();
        if (isset($constants['errors'][$errorkey])) {
             return $constants['errors'][$errorkey];
        }
        return $constants['errors']['default'];
    }

    public static function log(string $level, mixed $message, array $context = []): void {
        if (!Config::shouldLog($level)) {
            return;
        }

        do_action('rrze.log.' . $level, $message, $context);
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
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You are not allowed to perform this action.', 'rrze-lectures'), 403);
        }

        check_ajax_referer('lecture-ajax-nonce', 'nonce');

        $input = [];
        $postData = isset($_POST['data']) && is_array($_POST['data']) ? wp_unslash($_POST['data']) : [];

        foreach ($postData as $key => $value) {
            $input[$key] = sanitize_text_field($value);
        }

        $aFieldnames = [
            __('FAUorg Number', 'rrze-lectures'),
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
    public function getFAUOrgNr(?string $keyword = null): array|string {
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

}
