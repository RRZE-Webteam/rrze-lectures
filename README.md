# rrze-lectures

Aktuelle Informationen hierzu finden Sie stets auf https://www.wordpress.rrze.fau.de/plugins/externe-daten-einbinden/lehrveranstaltungen/


## Download
GITHub-Repo: https://github.com/RRZE-Webteam/rrze-lectures

## Autor
RRZE-Webteam, http://www.rrze.fau.de

## Copryright
GNU General Public License (GPL) Version 3

## Verwendung

als Shortcode:

Standard Ausgabe:

`[lectures]`


Alle Attribute:

`[lectures format="" type="" degree="" fauorgnr="" lecturer_identifier="" lecturer_idm="" lecture_name="" sem="" teaching_language="" display_language="" guest="" max="" hide="" hstart="" color="" nodata=""]`


Attribute:

|Attribute|Hinweis|Plichtfeld|Werte|Default|Beispiele|
|-|-|-|-|-|-|
|**format**||nein|"linklist" oder "tabs"|linklist|format="tabs"|
|**type**||nein|Art der Lehrveranstaltung. Mehrere Werte müssen durch Komma getrennt werden. Die Reihenfolge bestimmt die Sortierung. Wird jedoch die Ausgabe von "Art der Lehrveranstaltung" mit dem Attribut "hide" unterbunden, erfolgt die Sortierung alphabetisch, da ohne Information zu "Art der Lehrveranstaltung" die Sortierung der Lehrveranstaltungen willkürlich erscheinen würde.|alle Typen werden ausgegeben||type="Vorlesung, Vorlesung mit Übung, Tutorium"|
|**degree**||nein|Studiengänge. Mehrere Werte müssen durch Komma getrennt werden. Die Reihenfolge bestimmt die Sortierung. Wird degree angegeben, wird zuerst nach Studiengängen gruppiert und innerhalb dieser nach Lehrveranstaltungstypen.|alle Studiengänge werden ausgegeben|degree="Informatik, Artificial Intelligence, Mathematik"|
|**fauorgnr**|Eine Suchmaske dazu finden Sie unter Einstellungen: /wp-admin/options-general.php?page=rrze-lectures |nein|ist dieser Parameter gesetzt, wird er anstelle der FAU Org Nr in den Settings verwendet|Wert, der in den Einstellungen gesetzt ist|fauorgnr="123"|
|**lecture_name**||nein|Name der Lehrveranstaltung||lecture_name="Diskrete Optimierung I"|
|**lecturer_identifier**|Eine Suchmaske dazu finden Sie unter Einstellungen: /wp-admin/options-general.php?page=rrze-lectures |nein|Identifier einer oder mehrere verantwortlicher Personen. Mehrere Werte müssen durch Komma getrennt werden.||lecturer_identifier="1234567890, 0987654321"|
|**lecturer_idm**||nein|IdM-Kennung einer oder mehrere verantwortlicher Personen. Mehrere Werte müssen durch Komma getrennt werden. Eine Suche nach IdM-Kennungen stellt das Plugin nicht bereit.||lecturer_idm="abc123yz, 42asdf8"|
|**sem**||nein|Kürzel SoSe bzw WiSe und 4-stellige Jahreszahl|das aktuelle Semester wird verwendet|sem="WiSe2024" oder sem="SoSe2023"|
|**teaching_language**||nein|Das Sprachkürzel mit zwei Buchstaben. Mehrere Werte müssen mit Komma getrennt werden. Ist dieses Attribut gesetzt, werden nur die Lehrveranstaltungen ausgeben, die mindestens in einer der gegebenen Unterrichtssprachen angeboten werden.|Es werden alle Lehrveranstaltungen unabhängig von der Unterrichtssprache ausgegeben.|teaching_language="en" oder teaching_language="en, fr, de"|
|**display_language**||nein|Das Sprachkürzel mit zwei Buchstaben. Fallback ist Deutsch, weil die Daten auf Campo zunächst auf Deutsch eingetragen werden und dann ggfalls in weiteren Sprachen.|Es wird die Sprache verwendet, in der die Website eingestellt ist. Existieren in Campo dafür keine Übersetzungen, so erfolgt die Ausgabe auf Deutsch.|display_language="en" oder display_language="de" oder display_language="fr" ...|
|**guest**||nein|Für Gaststudium geeignet = 1 / nicht geeignet = 0|alle werden ausgegeben|guest="1"|
|**max**||nein|Maximale Anzahl an Lehrveranstaltungen.||max="5"|
|**hide**||nein|Die Anzeige von Teilen der Ausgabe können hiermit unterbunden werden. Derzeit: accordion, type_accordion, degree_accordion, type, degree||hide="accordion" oder hide="accordion, type" oder hide="type_accordion"|
|**hstart**||nein|Dieser Wert wird als Ausgangswert für die Hierachie-Ebene der Überschriften in Abhängigkeit der mit "hide" unterbundenen Akkordeons (accordion, type_accordion, degree_accordion) genutzt|2|hstart="3"|
|**color**|mehrere Farbwerte für die beiden ineinander verschachtelten Akkordeons: in Entwicklung|nein|med oder phil oder tf oder nat oder rw oder fau|fau|color="med"|
|**nodata**||nein|Eine beliebige Zeichenkette|Der in den Settings vorgegebene Eintrag. Siehe /wp-admin/options-general.php?page=rrze-lectures |nodata="Es wurden keine Lehrveranstaltungen gefunden."|

