# rrze-lectures

- Version 1.x.x liefert nur eine Liste an Links zu Campo

## Download
GITHub-Repo: https://github.com/RRZE-Webteam/rrze-lectures

## Autor
RRZE-Webteam, http://www.rrze.fau.de

## Copryright
GNU General Public License (GPL) Version 3

## Verwendung

als Shortcode:

`[lecture format="" type="" degree="" fauorgnr="" lecturer_idm="" lecturer_identifier="" lecture_name="" sem="" guest="" max="" hide="" hstart="" color="" nodata=""]`


Parameter:

|Parameter|Plichtfeld|Werte|Default|Beispiele|
|-|-|-|-|-|
|**format**|nein|derzeit nur dieser Wert: format="linklist"|linklist||
|**type**|nein|Art der Lehrveranstaltung. Mehrere Werte müssen durch Komma getrennt werden. Die Reihenfolge bestimmt die Sortierung|alle Typen werden ausgegeben|type="Vorlesung, Vorlesung mit Übung, Tutorium"|
|**degree**|nein|Studiengänge. Mehrere Werte müssen durch Komma getrennt werden. Die Reihenfolge bestimmt die Sortierung. Wird degree angegeben, wird zuerst nach Studiengängen gruppiert und innerhalb dieser nach Lehrveranstaltungstypen.|alle Studiengänge werden ausgegeben|degree="Informatik, Artificial Intelligence, Mathematik"|
|**fauorgnr**|nein|ist dieser Parameter gesetzt, wird er anstelle der FAU Org Nr in den Settings verwendet|Wert, der in den Einstellungen gesetzt ist|fauorgnr="123"|
|**lecture_name**|nein|Name der Lehrveranstaltung||lecture_name="Diskrete Optimierung I"|
|**lecturer_idm**|nein|IDM-Kennung einer oder mehrere verantwortlicher Personen. Mehrere Werte müssen durch Komma getrennt werden.||lecturer_idm="idm1abc, idm2def"|
|**lecturer_identifier**|nein|Identifier einer oder mehrere verantwortlicher Personen. Mehrere Werte müssen durch Komma getrennt werden.||lecturer_identifier="1234567890, 0987654321"|
|**lecturer_name**|nein|Name einer oder mehrerer veranwortlicher Personen. Mehrere müssen mit Semikolon getrennt werden. Nachname von Vorname mit Komma trennen.||lecturer_name="Nachname A, Vorname A; Nachname B; Nachname C, Vorname C"|
|**sem**|nein|Kürzel SoSe bzw WiSe und 4-stellige Jahreszahl|das aktuelle Semester wird verwendet|sem="WiSe2024" oder sem="SoSe2023"|
|**teaching_language**|nein|Das Sprachkürzel mit zwei Buchstaben. Mehrere Werte müssen mit Komma getrennt werden. Ist dieses Attribut gesetzt, werden nur die Lehrveranstaltungen ausgeben, die mindestens in einer der gegebenen Unterrichtssprachen angeboten werden.|Es werden alle Lehrveranstaltungen unabhängig von der Unterrichtssprache ausgegeben.|teaching_language="en" oder teaching_language="en, fr, de"|
|**display_language**|nein|Das Sprachkürzel mit zwei Buchstaben. Fallback kann definiert werden. Eine Verkettung von Fallbacks ist nicht möglich. Beispiele: "de" oder "en" oder "en:de" oder "fr:en" - Mit "en" werden die Felder nicht angezeigt, zu denen keine englischsprachige Übersetzung vorliegt. Soll in diesem Fall der deutsche Inhalt ausgeben werden, muss "en:de" verwendet werden.|Es wird die Sprache verwendet, in der die Website eingestellt ist. Existieren in Campo dafür keine ÜBersetzungen, so erfolgt die Ausgabe auf Deutsch.|display_language="en" oder display_language="de" oder display_language="en:de" oder display_language="fr:en" (sollten weder französische noch englische Übersetzunge in Campo eingetragen sein, so erfolgt für diese Felder keine Ausgabe.)|
|**guest**|nein|Für Gaststudium geeignet = 1 / nicht geeignet = 0|alle werden ausgegeben|guest="1"|
|**max**|nein|Maximale Anzahl an Lehrveranstaltungen.||max="5"|
|**hide**|nein|Die Anzeige von Teilen der Ausgabe können hiermit unterbunden werden. Derzeit: accordion, type_accordion, degree_accordion, type, degree||hide="accordion" oder hide="accordion, type" oder hide="type_accordion"|
|**hstart**|nein|Dieser Wert wird als Ausgangswert für die Hierachie-Ebene der Überschriften in Abhängigkeit der mit "hide" unterbundenen Akkordeons (accordion, type_accordion, degree_accordion) genutzt|2|hstart="3"|
|**color**|nein|med oder phil oder tf oder nat oder rw oder fau|fau|color="med"|
|**nodata**|nein|Eine beliebige Zeichenkette|Der in den Settings vorgegebene Eintrag. Siehe /wp-admin/options-general.php?page=rrze-lectures |nodata="Es wurden keine Lehrveranstaltungen gefunden."|

"family name A, given nameA; family name B, given name B; given name C"