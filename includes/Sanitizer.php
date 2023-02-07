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
            if (!empty($data[$nr]['providerValues']['event_orgunit'])){
                foreach ($data[$nr]['providerValues']['event_orgunit'] as $oNr => $aOrgunit){
                    if(!empty($aOrgunit[$oNr]['orgunit'])){
                        $data[$nr]['providerValues']['event_orgunit'][$oNr]['orgunit'] = sanitize_text_field($data[$nr]['providerValues']['event_orgunit'][$oNr]['orgunit']);
                    }
                }
            }

            if (!empty($data[$nr]['providerValues']['event']['eventtype'])) {
                $data[$nr]['providerValues']['event']['eventtype'] = sanitize_text_field($data[$nr]['providerValues']['event']['eventtype']);
            }

            // convert dates
            if (!empty($data[$nr]['providerValues']['planned_dates']['startdate'])) {
                $data[$nr]['providerValues']['planned_dates']['weekday'] = Functions::convertDate($data[$nr]['providerValues']['planned_dates']['startdate'], $data[$nr]['eventSchedule']['scheduleTimezone'], 'N');
                $data[$nr]['providerValues']['planned_dates']['startdate'] = Functions::convertDate($data[$nr]['providerValues']['planned_dates']['startdate'], $data[$nr]['eventSchedule']['scheduleTimezone'], 'd.m.Y');
            }
            if (!empty($data[$nr]['providerValues']['planned_dates']['enddate'])) {
                $data[$nr]['providerValues']['planned_dates']['enddate'] = Functions::convertDate($data[$nr]['providerValues']['planned_dates']['enddate'], $data[$nr]['eventSchedule']['scheduleTimezone'], 'd.m.Y');
            }
            if (!empty($data[$nr]['providerValues']['planned_dates']['starttime'])) {
                $data[$nr]['providerValues']['planned_dates']['starttime'] = Functions::convertDate($data[$nr]['providerValues']['planned_dates']['starttime'], $data[$nr]['eventSchedule']['scheduleTimezone'], 'H:i');
            }
            if (!empty($data[$nr]['providerValues']['planned_dates']['endtime'])) {
                $data[$nr]['providerValues']['planned_dates']['endtime'] = Functions::convertDate($data[$nr]['providerValues']['planned_dates']['endtime'], $data[$nr]['eventSchedule']['scheduleTimezone'], 'H:i');
            }

            // get "Ausfalltermine"
            if (!empty($aEntries['providerValues']['individual_dates'])) {

                // 2DO: empty() prüfen
                foreach ($aEntries['providerValues']['individual_dates'] as $eNr => $aDetails) {
                    // $aDetails['cancelled'] $aDetails['date'], $data[$nr]['eventSchedule']['scheduleTimezone']
                    if (!empty($aDetails['cancelled']) && !empty($aDetails['date']) && !empty($data[$nr]['eventSchedule']['scheduleTimezone'])){
                        if ($aDetails['cancelled'] == 1) {
                            $data[$nr]['providerValues']['planned_dates']['misseddates'][] = Functions::convertDate($aDetails['date'], $data[$nr]['eventSchedule']['scheduleTimezone'], 'd.m.Y');
                        }    
                    }
                }
            }
        }
    }

}