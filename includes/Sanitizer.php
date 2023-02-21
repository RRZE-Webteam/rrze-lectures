<?php

namespace RRZE\Lectures;

defined('ABSPATH') || exit;

use function RRZE\Lectures\Config\getSanitizerMap;

class Sanitizer
{
    protected $aMap;


    public function __construct()
    {
        $this->aMap = getSanitizerMap();
    }

    public function sanitizeArray($aIn)
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

    public function sanitizeField($value, $type = 'string')
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

    public static function sanitizeLectures(&$data)
    {
        foreach ($data as $nr => $aEntries) {

            // 2DO: sanitize_text_field() all other fields for output
            if (!empty($data[$nr]['providerValues']['event_orgunit'])) {
                foreach ($data[$nr]['providerValues']['event_orgunit'] as $oNr => $aOrgunit) {
                    if (!empty($aOrgunit[$oNr]['orgunit'])) {
                        $data[$nr]['providerValues']['event_orgunit'][$oNr]['orgunit'] = sanitize_text_field($data[$nr]['providerValues']['event_orgunit'][$oNr]['orgunit']);
                    }
                }
            }

            // event type
            if (!empty($data[$nr]['providerValues']['event']['eventtype'])) {
                $data[$nr]['providerValues']['event']['eventtype'] = sanitize_text_field($data[$nr]['providerValues']['event']['eventtype']);
            }

            // set teaching_language_txt
            if (!empty($data[$nr]['providerValues']['courses']['teaching_language'])) {
                $data[$nr]['providerValues']['courses']['teaching_language_txt'] = implode(' or ', $data[$nr]['providerValues']['courses']['teaching_language']);
            }

            // convert dates
            if (!empty($data[$nr]['providerValues']['courses']['planned_dates'])) {
                foreach ($data[$nr]['providerValues']['courses']['planned_dates'] as $cNr => $aDetails) {
                    if (!empty($data[$nr]['providerValues']['courses']['planned_dates'][$cNr]['startdate'])) {
                        $data[$nr]['providerValues']['courses']['planned_dates'][$cNr]['weekday'] = Functions::convertDate($data[$nr]['providerValues']['courses']['planned_dates'][$cNr]['startdate'], 'Europe/Berlin', 'N');
                        $data[$nr]['providerValues']['courses']['planned_dates'][$cNr]['startdate'] = Functions::convertDate($data[$nr]['providerValues']['courses']['planned_dates'][$cNr]['startdate'], 'Europe/Berlin', 'd.m.Y');
                    }
                    if (!empty($data[$nr]['providerValues']['courses']['planned_dates'][$cNr]['startdate'])) {
                        $data[$nr]['providerValues']['courses']['planned_dates'][$cNr]['weekday'] = Functions::convertDate($data[$nr]['providerValues']['courses']['planned_dates'][$cNr]['startdate'], 'Europe/Berlin', 'N');
                        $data[$nr]['providerValues']['courses']['planned_dates'][$cNr]['startdate'] = Functions::convertDate($data[$nr]['providerValues']['courses']['planned_dates'][$cNr]['startdate'], 'Europe/Berlin', 'd.m.Y');
                    }
                    if (!empty($data[$nr]['providerValues']['courses']['planned_dates'][$cNr]['enddate'])) {
                        $data[$nr]['providerValues']['courses']['planned_dates'][$cNr]['enddate'] = Functions::convertDate($data[$nr]['providerValues']['courses']['planned_dates'][$cNr]['enddate'], 'Europe/Berlin', 'd.m.Y');
                    }
                    if (!empty($data[$nr]['providerValues']['courses']['planned_dates'][$cNr]['starttime'])) {
                        $data[$nr]['providerValues']['courses']['planned_dates'][$cNr]['starttime'] = Functions::convertDate($data[$nr]['providerValues']['courses']['planned_dates'][$cNr]['starttime'], 'Europe/Berlin', 'H:i');
                    }
                    if (!empty($data[$nr]['providerValues']['courses']['planned_dates'][$cNr]['endtime'])) {
                        $data[$nr]['providerValues']['courses']['planned_dates'][$cNr]['endtime'] = Functions::convertDate($data[$nr]['providerValues']['courses']['planned_dates'][$cNr]['endtime'], 'Europe/Berlin', 'H:i');
                    }

                    // get "Ausfalltermine"
                    if (!empty($aDetails['individual_dates'])) {
                        foreach ($aDetails['individual_dates'] as $iNr => $aIndividuals) {
                            if (!empty($aIndividuals['cancelled']) && !empty($aIndividuals['date']) && $aIndividuals['cancelled'] == 1) {
                                $data[$nr]['providerValues']['courses']['planned_dates'][$cNr]['misseddates'][] = Functions::convertDate($aIndividuals['date'], 'Europe/Berlin', 'd.m.Y');
                            }
                        }
                    }
                }
            }
        }
    }

}