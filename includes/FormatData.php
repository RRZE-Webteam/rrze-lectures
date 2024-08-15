<?php
/*
 * Verschiedene Funktionen zur Sortierung, Gruppierung und 
 * Übersetzung der Daten, die wir von DIP erhielten.
 * 
 * Diese Class übernimmt die vorherige Class Translator aus V2.0 und
 * auch verschiende Sortierungsfunktionen der Daten, die bisher in
 * Shortcode.php waren.
 * Damit erreichen wir langfristig eine bessere Übersicht über die Funktionen
 * und können Sie dann ausführen, wenn wir sie wirklich brauchen.
 * So kann dann auch auf die Ausführung von umfangreichen RAM-belastenden
 * Sortierungen verzichtet werden, wenn wir dafür gar keine Ausgaben 
 * anzeigen.
 * Ausserdem wollen wir mit dem Plugin zukünftig auch andere Dinge anzeigen
 * als nur Lehrveranstaltungen, so daß wir auch dann die Funktionen
 * gesondert aufrufen können müssen. 
 */
namespace RRZE\Lectures;

defined('ABSPATH') || exit;

use function RRZE\Lectures\Config\getSanitizerMap;


class FormatData {
    protected $display_language;
    protected $display_language_fallback;
    protected $all_language_codes = [];
    protected $aPath = '';

    // protected $varFunc;


    public function __construct(string $display_language = 'de') {
        $this->display_language = $display_language;

        // Input values in Campo are made in GERMAN. There could also be other languages, but default = 'de'
        $this->display_language_fallback = 'de';

        // Keep it simple, stupid!!
        $this->all_language_codes = ['de', 'en'];

    }

    
    /*
     * Suche die Campo- und die StudOn URL aus den Course Einträgen und 
     * fügt diese den Bereich der direkt unter der ID zu.
     * Erwartet ein EventTypeArray und gibt das geänderte Array zurück
     */
    public function searchPortalURLsforEvent(array $data): array {
        
        foreach ($data as $eventtype => $events) {
            foreach ($events as $id => $eventdata) {
                $studonurl = '';
                
                if (!empty($eventdata['description'])) {
                    
                    if (!is_array($eventdata['description'])) {
                        $studonurl = self::findStudOnURLinText($eventdata['description']);
                    } else {
                        if (!empty($eventdata['description']['de'])) {
                            $studonurl = self::findStudOnURLinText($eventdata['description']['de']);
                        }
                        if (empty($studonurl) && (!empty($eventdata['description']['en']))) {
                             $studonurl = self::findStudOnURLinText($eventdata['description']['en']); 
                        }
                    }
                }
                
                
                if (!empty($studonurl)) {    
                     $data[$eventtype][$id]['studon_url'] = $studonurl;
                } else {
                
                    if (!empty($eventdata['providerValues']['courses'])) {

                          // get Campo Link from first Course
                        $first_course = array_key_first($eventdata['providerValues']['courses']);       
                        if (isset($eventdata['providerValues']['courses'][$first_course]['url'])) {
                            $data[$eventtype][$id]['campo_url'] = $eventdata['providerValues']['courses'][$first_course]['url'];
                        }




                        if (isset($eventdata['providerValues']['event']['link_studon'])) {
                            // schaue ob der Link zu STudOn in den Eventdaten drin ist
                            $data[$eventtype][$id]['studon_url'] = $eventdata['providerValues']['event']['link_studon'];                
                        } elseif (isset($eventdata['providerValues']['courses'][$first_course]['studon_url'])) {
                            // schau ob es in ProviderValues eine StudOnURL gibt
                            $data[$eventtype][$id]['studon_url'] = $eventdata['providerValues']['courses'][$first_course]['studon_url'];
                        } else {
                            // leider keine URL übergeben.
                            // Daher suche ich nun in dem Beschreibungstext der Kurse. Nehme da den ersten
                            // Treffer

                            foreach ($eventdata['providerValues']['courses'] as $course => $coursedata) {
                                if (!empty($coursedata['contents'])) {
                                    $studonurl = self::findStudOnURLinText($coursedata['contents']);
                                    if (!empty($studonurl)) {    
                                        $data[$eventtype][$id]['studon_url'] = $studonurl;
                                        break;
                                    }
                                }
                            }

                        }



                    }
                }
            }
        }
        return $data;

    }
    
    // private Funktion für searchPortalURLsforEvent()
    // Sucht in einem Text nach einer URL der Form
    //       https://www.studon.fau.de/[a-z]+[0-9]+\.html
    // und gibt den ersten Treffer zurück
    private function findStudOnURLinText(string $content): string {
        $result = '';
        if (empty($content)) {
            return $result;
        }
        preg_match_all('/https:\/\/www\.studon\.fau\.de\/[a-z]+[0-9]+\.html/i', $content, $output_array);
        if (!empty($output_array)) {
            if (isset($output_array[0][0])) {
                return $output_array[0][0];
            }
        }
        
          return $result;
    }
    
    /*
     * Gruppiere Daten nach Event-Typ
     */ 
    public function groupbyEventType(array $data): array {
        // First find all event types and put them into together
        $data_by_types = [];
        foreach ($data as $nr => $aEntries) {
                $id = $aEntries['identifier'];
                $thistype = '_unset_type';
                if (!empty($aEntries['providerValues']['event']['eventtype'])) {
                    if (is_array($aEntries['providerValues']['event']['eventtype'])) {
                        if (isset($aEntries['providerValues']['event']['eventtype']['de'])) {
                            $thistype = $aEntries['providerValues']['event']['eventtype']['de'];
                        } elseif (isset($aEntries['providerValues']['event']['eventtype']['en'])) { 
                            $thistype = $aEntries['providerValues']['event']['eventtype']['en'];
                        } else {
                            $thisfirst = array_key_first($aEntries['providerValues']['event']['eventtype']);
                            $thistype = $aEntries['providerValues']['event']['eventtype'][$thisfirst];
                        }
                    } elseif (is_string($aEntries['providerValues']['event']['eventtype'])) {
                         $thistype  = $aEntries['providerValues']['event']['eventtype'];
                    }
                }
                unset($aEntries['providerValues']['event']['eventtype']);
                if (empty($aEntries['providerValues']['event'])) {
                    unset($aEntries['providerValues']['event']);
                }
                unset($aEntries['identifier']);
                
                $data_by_types[$thistype][$id] = $aEntries;
        }
        return $data_by_types;
    }
    
    /*
     * Sortiere nach EventTypes
     * Diese Typen werden nicht nach Alphabet sortiert, sondern nach ihrer
     * Bedeutung. Eine "Vorlesung" ist immer vor einem "Seminar" aufzuführen 
     */
    public function sortEventTypeArraybyEvent(array $array, $customOrder = []): array {
        if (empty($array)) {
            return [];
        }
        if (count($array) == 1) {
            // hier brauch ich nichts zu tun
            return $array;
        }
                
        if ((!isset($customOrder)) || (empty($customOrder))) {
            $customOrder = ["Hauptvorlesung", "Vorlesung", "Vorlesung mit Übung", "Masterseminar", "Hauptseminar", "Seminar"];
        }
    
        
        uksort($array, function($a, $b) use ($customOrder) {
            $aIndex = array_search($a, $customOrder);
            $bIndex = array_search($b, $customOrder);

            if ($aIndex === false && $bIndex === false) {
                // Wenn beide Elemente nicht im $customOrder sind, alphabetisch sortieren
                return strnatcmp($a, $b);
            } elseif ($aIndex === false) {
                // Wenn nur $a nicht im $customOrder ist, $b zuerst platzieren
                return 1;
            } elseif ($bIndex === false) {
                // Wenn nur $b nicht im $customOrder ist, $a zuerst platzieren
                return -1;
            } else {
                // Wenn beide im $customOrder sind, nach der Reihenfolge im $customOrder sortieren
                return $aIndex - $bIndex;
            }
        });

        return $array;
        
    }
    
    
    /*
     * Je nach Abfrage sind die Course Arrays mehrfach mit denselben Daten besetzt.
     * Das macht keinen Sinn und macht unsere Sortierung und Folgebearbeitungen 
     * unnötig langsam. Daher mergen wir Courses mit selben Daten.
     * Notiz: Von der API kommen viele derartige scheinbar identische Couse
     * deswegen, weil intern dort noch jeweils andere Zusatzdaten vorhanden wären. 
     * Da wir diese aber nicht verwenden, sehen und brauchen wir das nicht.
     */
    public function removeDuplicateCourses(array $data): array {
        foreach ($data as $eventtype => $events) {
            foreach ($events as $id => $eventdata) {
                if (!empty($eventdata['providerValues']['courses'])) {
                    // Notiz: array_unique() ist nicht gedacht für 
                    // mehrdimensionale arrays und kann daher hier nicht
                    //  einfach eingesetzt werden.
                    if ((is_array($eventdata['providerValues']['courses'])) && ((count($eventdata['providerValues']['courses']))==1)) {
                        // tu nichts, keine duplicate moglich
                    } else {
                        $courses = self::makearrayunique($eventdata['providerValues']['courses'], true);
                        $data[$eventtype][$id]['providerValues']['courses'] = $courses;
                    }
                }
            }
        }
        return $data;
    }
    
    private static function makearrayunique(array $input, bool $ignoreurl): array {
        $result = [];
        $dup = [];
        
        foreach ($input as $key => $value) {
            if (isset($dup[$key])) {
                continue;
            }
            foreach ($input as $secondkey => $secondvalue) {
                if ($key == $secondkey) {
                    continue;
                }
                if (isset($dup[$secondkey])) {
                    continue;
                }
                
                $same = true;
                foreach ($value as $feld => $datensatz) {
                    if (($ignoreurl) && ($feld == 'url')) {                     
                        continue;
                    }
                    if (isset($value[$feld]) && isset($secondvalue[$feld])) {
                         if ($secondvalue[$feld] !== $value[$feld]) {
                            // not same
                             $same = false;
                             break;
                        }
                    }
                }
                if ($same) {
                    // gleich, also weglassen
                    $dup[$secondkey] = $key;
                }
            } 
        }
        foreach ($input as $key => $value) {
            if (isset($dup[$key])) {
                continue;
            }
            $result[] = $value;
        }
        return $result;
    }
    
    
    /*
     * Sortiert ein EventType-Array nach einem gegebenen Attribut 
     * Das Attribut bezieht sich auf das erste Subelement des Arrays, nach dessen Key.
     * Zum Beispiel 'name' (default). Dieses bezieht sich dann auf
     *    $typename.(*.)name
     * 
     * TODO/NOT READY:
     * Wenn es sich auf ein Subelement beziehen soll, dann ist dieses via Punkt zu trennen:
     *    providerValues.courses.title
     * Dies bzeiht sich dann auf
     *    $typename.*.providerValues.(*.)courses.(*.)title. 
     */
    public function sortEventTypeArraybyAttribut(array $data, string $attribut = 'name', string $order = 'asc'): array {
        if ((empty($data)) || (empty($attribut))) {
            return [];
        }
        
        $searchparts = explode('.',trim($attribut));
        $result = [];
        $resultgroup = [];
        if (count($searchparts)==1) {
            foreach ($data as $groupid => $events) {
                 $data[$groupid] = self::sortArrayByField($events,$attribut,$order);
            }  
            return $data;
        } else {
            // Not ready yet
             return $data;
        }
    }
    
    
    /*
     * Allgemeine Sortierroutine
     */
    public static function sortArrayByField(array $inputArray, string $field, string $order = 'asc'): array {
        $sortedArray = $inputArray;

        uasort($sortedArray, function($a, $b) use ($field, $order) {
            $stringA = preg_replace('/[^\p{L}\p{M}\s]/u', '', $a[$field]);
            $stringB = preg_replace('/[^\p{L}\p{M}\s]/u', '', $b[$field]);
        
            
            $result = strnatcmp($stringA, $stringB);
            return ($order === 'asc') ? $result : -$result;
        });

        return $sortedArray;
    }



    /*
     * sortiere innerhalb der Courses
     */
    public function sortbyCourses(array $data): array {

       
        // sort entries
        $iAllEntries = 0;
        $aTmp = [];

        foreach ($data as $group => $aDetails) {
            $aTmp2 = [];
            foreach ($aDetails as $identifier => $aEntries) {
                $name = $aEntries['name'];
                $aTmp2[$name] = $aEntries;
                $aTmp3 = [];
                foreach ($aEntries['providerValues']['courses'] as $nr => $aCourses){
                    // BK 2023-06-28 : explicitely delete cancelled parallelgroups (API ignores this parameter sometimes)
                    if ((isset($aCourses['cancelled']) && ($aCourses['cancelled'] == false))) {
                        // sort by parallelgroup
                        $parallelgroup = $aCourses['parallelgroup'];
                        $aTmp3[$parallelgroup] = $aCourses;    
                    }

                }

                $arrayKeys = array_keys($aTmp3);
                if (count($arrayKeys) > 1){
                    array_multisort($arrayKeys, SORT_NATURAL | SORT_FLAG_CASE, $aTmp3);
                }else{
                    $aTmp3[array_key_first($aTmp3)]['parallelgroup'] = '';
                }

                $aTmp2[$name]['providerValues']['courses'] = $aTmp3;
                $aTmp3 = null;    
            }

            $arrayKeys = array_keys($aTmp2);
            array_multisort($arrayKeys, SORT_NATURAL | SORT_FLAG_CASE, $aTmp2);
            $iAllEntries += count($aTmp2);
            $aTmp[$group] = $aTmp2;
            // unset($aTmp2); // free memory
            $aTmp2 = null;
        }

        return $aTmp;
    }
    
    
    /* returns translations by language (given attribute and/or settings value) or '' */
    private function getTranslation(string|array|null &$aIn): string|array|null {
        if (!is_array($aIn)) {
            // DIP-Field is not a mulitlang-field (== string (and not array with language codes) (["en" => "english text", "de" => "deutscher Text"])
            return $aIn;
        }

        if (!empty($aIn[$this->display_language])) {
            return $aIn[$this->display_language];
        } elseif (!empty($this->display_language_fallback) && !empty($aIn[$this->display_language_fallback])) {
            return $aIn[$this->display_language_fallback];
        } else {
            return '';
        }
    }


    public function setTranslations(array &$aData)  {
        foreach ($aData as $nr => $aLecture) {
            foreach ($aLecture as $fieldName => $field) {


                // main part
                if (is_array($field)) {
                    foreach ($field as $fKey => $val) {
                        if (in_array($fKey, $this->all_language_codes)) {
                            $translated = $this->getTranslation($aData[$nr][$fieldName]);
                            unset($aData[$nr][$fieldName]); // drop array with all languages
                            $aData[$nr][$fieldName] = $translated;
                        }
                    }
                }
            }

            if (!empty($aLecture['providerValues']['event_orgunit'])){
                foreach ($aLecture['providerValues']['event_orgunit'] as $orgunitNr => $aOrgunit) {
                    // event_orgunit part
                    foreach ($aOrgunit as $fieldName => $field) {
                        if (is_array($field)) {
                            foreach ($field as $fKey => $val) {
                                if (in_array($fKey, $this->all_language_codes)) {
                                    $translated = $this->getTranslation($aData[$nr]['providerValues']['event_orgunit'][$orgunitNr][$fieldName]);
                                    $aData[$nr]['providerValues']['event_orgunit'][$fieldName][] = $translated;
                                }
                            }
                        }
                    }
                    unset($aData[$nr]['providerValues']['event_orgunit'][$orgunitNr]); // drop array with all languages
                }
            }


            foreach ($aLecture['providerValues']['event'] as $fieldName => $field) {
                // event part
                if (is_array($field)) {
                    foreach ($field as $fKey => $val) {
                        if (in_array($fKey, $this->all_language_codes)) {
                            $translated = $this->getTranslation($aData[$nr]['providerValues']['event'][$fieldName]);
                            unset($aData[$nr]['providerValues']['event'][$fieldName]); // drop array with all languages
                            $aData[$nr]['providerValues']['event'][$fieldName] = $translated;
                        }
                    }
                }
            }

            $aSubs = [
                'courses',
                'modules', // depending on $format not all DIP fields are avaliable
            ];

            foreach ($aSubs as $subName) {
                if (!empty($aLecture['providerValues'][$subName])) { // depending on $format not all DIP fields are avaliable
                    foreach ($aLecture['providerValues'][$subName] as $cNr => $aCourse) {
                        foreach ($aCourse as $coursefieldName => $aTranslatable) {
                            if (is_array($aTranslatable)) {
                                foreach ($aTranslatable as $lang => $val) {
                                    if (in_array($lang, $this->all_language_codes)) {
                                        $translated = $this->getTranslation($aLecture['providerValues'][$subName][$cNr][$coursefieldName]);
                                        unset($aData[$nr]['providerValues'][$subName][$cNr][$coursefieldName]); // drop all other languages
                                        $aData[$nr]['providerValues'][$subName][$cNr][$coursefieldName] = $translated;
                                    }
                                }
                            }

                            if ($coursefieldName == 'planned_dates') {
                                foreach ($aTranslatable as $planed_datesNr => $aPlanedDatesFields) {
                                    foreach ($aPlanedDatesFields as $fieldName => $aVals) {
                                        if (is_array($aVals)) {
                                            foreach ($aVals as $lang => $val) {
                                                if (in_array($lang, $this->all_language_codes)) {
                                                    $translated = $this->getTranslation($aLecture['providerValues']['courses'][$cNr][$coursefieldName][$planed_datesNr][$fieldName]);
                                                    unset($aData[$nr]['providerValues']['courses'][$cNr][$coursefieldName][$planed_datesNr][$fieldName]); // drop all other languages
                                                    $aData[$nr]['providerValues']['courses'][$cNr][$coursefieldName][$planed_datesNr][$fieldName] = $translated;
                                                }
                                            }
                                        }
                                        if ($fieldName == 'individual_dates') {
                                            foreach ($aVals as $dateNr => $aDateDetails) {
                                                foreach ($aDateDetails as $fN => $aV) {
                                                    if (is_array($aV)) {
                                                        foreach ($aV as $lang => $val) {
                                                            if (in_array($lang, $this->all_language_codes)) {
                                                                $translated = $this->getTranslation($aLecture['providerValues']['courses'][$cNr][$coursefieldName][$planed_datesNr]['individual_dates'][$dateNr][$fN]);
                                                                unset($aData[$nr]['providerValues']['courses'][$cNr][$coursefieldName][$planed_datesNr]['individual_dates'][$dateNr][$fN]); // drop all other languages
                                                                $aData[$nr]['providerValues']['courses'][$cNr][$coursefieldName][$planed_datesNr]['individual_dates'][$dateNr][$fN] = $translated;
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
                    }
                }
            }
        }
    }
}