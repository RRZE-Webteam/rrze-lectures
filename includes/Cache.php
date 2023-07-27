<?php

namespace RRZE\Lectures;

defined('ABSPATH') || exit;
use function RRZE\Lectures\Config\getConstants;


/**
 * Methide zur Zwischenspeicherung von Ergebnissen
 */
class Cache  {
    protected $pluginFile;
    private $settings = '';
    
    public function __construct() {
        $this->constants = getConstants();
    }

    public function onLoaded() {
        return true;
    }
    
    public function set_cachetime($time) {
        // 'Transient_Seconds' =>  3 * HOUR_IN_SECONDS,
        // Transient Time for generated Outpzut. Smaller as all. 10 - 60 minutes would fit
        // 'Transient_Seconds_Output' =>  1 * HOUR_IN_SECONDS,
        // Transient Time for raw data we got from the API
        //  'Transient_Seconds_Rawdata' =>  6 * HOUR_IN_SECONDS,
    
        if ((isset($time)) && (intval($time) > 0)) {
            $this->constants['Transient_Seconds'] = $time;
        }
        return $this->constants['Transient_Seconds'];
    }
    
    
    private function get_transient_name(array $aAtts = []) {    
        $prefix = $this->constants['Transient_Prefix'];
        // bestandteile des Namen:
        // - fauorgnr        
        // - degree|degree_his_identifier
        // - lecture_name|lecture_id
        // - lecturer_identifier| lecturer_idm,
        // für später auch:  
        // - modul_name|modul_id ,
        
        
        // Note: Der Transient Name darf nicht länger als 172 Zeichen sein. 
        // Wenn der Transientenname länger als 172 Zeichen ist, wird er in 
        // der Datenbank nicht gespeichert, und der Transient wird nicht 
        // ordnungsgemäß funktionieren. 
        // Wenn also z.B. bei ORG zu viele Orgs eingegeben werden, müssen wir
        // ein Checksum bilden oder den Cache sein lassen ..
        
        $orgnr = 'no';
        if (!empty($aAtts['fauorgnr'])) {
            $orgnr = $aAtts['fauorgnr'];
            $orgnr = preg_replace('/[^a-z0-9]+/i', '', $orgnr);
            if (strlen($orgnr) > 40) {
                // maximal 5 Tupel
                $orgnr = md5($orgnr);
            }
        }
        $degree = 'nd';
        if (!empty($aAtts['degree_his_identifier'])) {
            $degree = $aAtts['degree_his_identifier'];
            $degree = preg_replace('/[^a-z0-9]+/i', '', $degree);
        } elseif (!empty($aAtts['degree'])) {
            $degree = $aAtts['degree'];
            $degree = preg_replace('/[^a-z0-9]+/i', '', $degree);
             if (mb_strlen($degree) > 60) {
                $degree = md5($degree);
            }
        }
        $lecture = 'nl';
        if (!empty($aAtts['lecture_id'])) {
            $lecture = $aAtts['lecture_id'];
            $lecture = preg_replace('/[^a-z0-9]+/i', '', $lecture);
        } elseif (!empty($aAtts['lecture_name'])) {
            $lecture = $aAtts['lecture_name'];
            $lecture = preg_replace('/[^a-z0-9]+/i', '', $lecture);
             if (mb_strlen($lecture) > 60) {
                $lecture = md5($lecture);
            }
        }
        $dozent = 'nd';
        if (!empty($aAtts['lecturer_identifier'])) {
            $dozent = $aAtts['lecturer_identifier'];
            $dozent = preg_replace('/[^a-z0-9]+/i', '', $dozent);
        } elseif (!empty($aAtts['lecturer_idm'])) {
            $dozent = $aAtts['lecturer_idm'];
            $dozent = preg_replace('/[^a-z0-9]+/i', '', $dozent);
             if (mb_strlen($dozent) > 20) {
                $dozent = md5($dozent);
            }
        }
        $module = 'nm';
        if (!empty($aAtts['modul_id'])) {
            $module = $aAtts['modul_id'];
            $module = preg_replace('/[^a-z0-9]+/i', '', $module);
        } elseif (!empty($aAtts['modul_name'])) {
            $module = $aAtts['modul_name'];
            $module = preg_replace('/[^a-z0-9]+/i', '', $module);
        }
        $format = 'raw';
        if (!empty($aAtts['cachetype'])) {
            $format = $aAtts['cachetype'];
            $format = preg_replace('/[^a-z0-9]+/i', '', $format);
        }
        if ($format == 'html') {
            // add the format-type in case someuse uses the same content for different views
            if (!empty($aAtts['format'])) {
                $format .= '-'.$aAtts['format'];
            }
        }
        
        return $prefix."-".$orgnr."-".$degree."-".$lecture."-".$dozent."-".$module."-".$format;
    }
    
    public function get_cached_data(array $aAtts = []) {
        if (empty($aAtts)) {
            return false;
        }
        $transient_name = $this->get_transient_name($aAtts);
        $value = get_transient( $transient_name );

        if ( false === $value ) {
            return false;
        } else {
            return $value;
        }
    }
    
    
    public function set_cached_data($content, array $aAtts = [], int $cachetimeoverwrite = 0 ) {	
        if (empty($aAtts)) {
            return false;
        }
        if (empty($content)) {
            return false;
        }
        $transient_name = $this->get_transient_name($aAtts);
        
        $cachetime = $this->constants['Transient_Seconds'];
        if (!empty($aAtts['cachetype'])) {
            if ($aAtts['cachetype'] == 'data') {
                 $cachetime = $this->constants['Transient_Seconds_Rawdata'];
            } elseif ($aAtts['cachetype'] == 'output') {
                 $cachetime = $this->constants['Transient_Seconds_Output'];
            }
            
        }
        
        if ((!empty($cachetimeoverwrite)) && (intval($cachetimeoverwrite)>0)) {
            $cachetime = $cachetimeoverwrite;
        }

        set_transient( $transient_name, $content, $cachetime);
        return true;
    }
    
}
