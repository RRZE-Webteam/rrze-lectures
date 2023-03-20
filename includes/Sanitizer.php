<?php

namespace RRZE\Lectures;

defined('ABSPATH') || exit;

use function RRZE\Lectures\Config\getSanitizerMap;

class Sanitizer
{
    protected $aMap;


    public function __construct()
    {
    }

    public function onLoaded()
    {
        $this->aMap = getSanitizerMap();
    }

    public static function wp_kses_custom(string $str): string
    {
        $allowed_html = [
            'a' => [
                'href' => true,
            ],
            'ul' => [],
            'ol' => [],
            'li' => [],
        ];


        $allowed_protocols = [
            'http',
            'https',
            'mailto',
        ];

        return wp_kses($str, $allowed_html, $allowed_protocols);
    }

    public function sanitizeArray(array $aIn): array
    {
        foreach ($aIn as $field => $value) {
            if (is_array($value)) {
                $aIn[$field] = $this->sanitizeArray($value);
            } else {
                $key = $field;
            }

            if (!empty($this->aMap[$field])) {
                $aIn[$field] = $this->sanitizeField($aIn[$field], $this->aMap[$field]);
            }
        }

        return $aIn;
    }

    public function sanitizeField(string $value, string $type = 'string'): string
    {
        switch ($type) {
            case 'date':
                return date("d.m.Y", strtotime($value));
                break;
            case 'time':
                return date("H:i", strtotime($value));
                break;
            default:
                return sanitize_text_field($value);
                break;
        }
    }


    public static function sanitizeLectures(array &$data, array &$aLanguages)
    {
        array_walk_recursive($data, 'sanitize_text_field');

        foreach ($data as $iEntry => $aEntries) {

            if (!empty($data[$iEntry]['providerValues']['event']['comment'])){
                foreach($data[$iEntry]['providerValues']['event']['comment'] as $lang => $val){
                    $data[$iEntry]['providerValues']['event']['comment'][$lang] = self::wp_kses_custom($data[$iEntry]['providerValues']['event']['comment'][$lang]);
                }
            }

            if (!empty($data[$iEntry]['providerValues']['courses'])) {
                foreach ($data[$iEntry]['providerValues']['courses'] as $iCourse => $aCourse) {
                    // set teaching_language_txt
                    if (!empty($aCourse['teaching_language'])) {
                        array_walk($aCourse['teaching_language'], function (&$val, $key) use ($aLanguages) {
                            if (!empty($aLanguages[$val])) {
                                $val = $aLanguages[$val];
                            }
                        });
                        $data[$iEntry]['providerValues']['courses'][$iCourse]['teaching_language_txt'] = implode(' ' . __('or', 'rrze-lectures') . ' ', $aCourse['teaching_language']);
                    }


                    // convert dates
                    if (!empty($aCourse['planned_dates'])) {
                        foreach ($aCourse['planned_dates'] as $iDate => $aDates) {
                            if (!empty($aDates['startdate'])) {
                                $data[$iEntry]['providerValues']['courses'][$iCourse]['planned_dates'][$iDate]['weekday'] = Functions::convertDate($aDates['startdate'], 'Europe/Berlin', 'N');
                                $data[$iEntry]['providerValues']['courses'][$iCourse]['planned_dates'][$iDate]['startdate'] = Functions::convertDate($aDates['startdate'], 'Europe/Berlin', 'd.m.Y');
                            }
                            if (!empty($aDates['startdate'])) {
                                $data[$iEntry]['providerValues']['courses'][$iCourse]['planned_dates'][$iDate]['weekday'] = Functions::convertDate($aDates['startdate'], 'Europe/Berlin', 'N');
                                $data[$iEntry]['providerValues']['courses'][$iCourse]['planned_dates'][$iDate]['startdate'] = Functions::convertDate($aDates['startdate'], 'Europe/Berlin', 'd.m.Y');
                            }
                            if (!empty($aDates['enddate'])) {
                                $data[$iEntry]['providerValues']['courses'][$iCourse]['planned_dates'][$iDate]['enddate'] = Functions::convertDate($aDates['enddate'], 'Europe/Berlin', 'd.m.Y');
                            }
                            if (!empty($aDates['starttime'])) {
                                $data[$iEntry]['providerValues']['courses'][$iCourse]['planned_dates'][$iDate]['starttime'] = Functions::convertDate($aDates['starttime'], 'Europe/Berlin', 'H:i');
                            }
                            if (!empty($aDates['endtime'])) {
                                $data[$iEntry]['providerValues']['courses'][$iCourse]['planned_dates'][$iDate]['endtime'] = Functions::convertDate($aDates['endtime'], 'Europe/Berlin', 'H:i');
                            }

                            // get "Ausfalltermine"
                            if (!empty($aDates['individual_dates'])) {
                                foreach ($aDates['individual_dates'] as $iIndividual => $aIndividuals) {
                                    if (!empty($aIndividuals['cancelled']) && !empty($aIndividuals['date']) && $aIndividuals['cancelled'] == 1) {
                                        $data[$iEntry]['providerValues']['courses'][$iCourse]['planned_dates'][$iDate]['misseddates'][] = Functions::convertDate($aIndividuals['date'], 'Europe/Berlin', 'd.m.Y');
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}