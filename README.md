# rrze-lectures

- Version 1.x.x liefet nur eine Liste an Links zu Campo

## Download
GITHub-Repo: https://github.com/RRZE-Webteam/rrze-lectures

## Autor
RRZE-Webteam, http://www.rrze.fau.de

## Copryright
GNU General Public License (GPL) Version 3

## Verwendung

als Shortcode:

`[lecture format="" type="" degree="" fauorgnr="" lecturer_id="" lecture_name="" guest="" max="" hide="" color="" nodata=""]`


Parameter:

|Parameter|Plichtfeld|Werte|Default|Beispiele|
|-|-|-|-|-|
|**format**|nein|derzeit nur dieser Wert: format="linklist"|linklist||
|**type**|nein|Art der Lehrveranstaltung. Mehrere Werte müssen durch Komma getrennt werden. Die Reihenfolge bestimmt die Sortierung|alle Typen werden ausgegeben|type="Vorlesung, Vorlesung mit Übung, Tutorium"|
|**degree**|nein|Studiengänge. Mehrere Werte müssen durch Komma getrennt werden. Die Reihenfolge bestimmt die Sortierung|alle Studiengänge werden ausgegeben|degree="Informatik, Artificial Intelligence, Mathematik"|
|**fauorgnr**|nein|ist dieser Parameter gesetzt, wird er anstelle der FAU Org Nr in den Settings verwendet|Wert, der in den Einstellungen gesetzt ist|fauorgnr="123"|
|**lecturer_id**|nein|IDM-Kennung einer oder mehrere verantwortlicher Personen. Mehrere Werte müssen durch Komma getrennt werden.||lecturer_id="idm1abc, idm2def"|
|**lecture_name**|nein|Name der Lehrveranstaltung||lecture_name="Diskrete Optimierung I"|
|**guest**|nein|Für Gaststudium geeignet = 1 / nicht geeignet = 0|alle werden ausgegeben|guest="1"|
|**max**|nein|Maximale Anzahl an Lehrveranstaltungen.||max="5"|
|**hide**|nein|Die Anzeige von Teilen der Ausgabe können hiermit unterbunden werden. Derzeit: accordion||hide="accordion"|
|**color**|nein|med oder phil oder tf oder nat oder rw oder fau|fau|color="med"|
|**nodata**|nein|Eine beliebige Zeichenkette|No matching entries found.|nodata="Es wurden keine Lehrveranstaltungen gefunden."|

