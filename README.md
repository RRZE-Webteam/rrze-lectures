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

`[lecture format="" type="" fauorgnr="" lecturer_id="" max="" hide="" color="" nodata=""]`


Parameter:

|Parameter|Plichtfeld|Werte|Default|Beispiele|
|-|-|-|-|-|
|**format**|nein|derzeit nur dieser Wert: format="linklist"|linklist||
|**type**|nein|Art der Lehrveranstaltung. Mehrere Werte müssen durch Komma getrennt werden. Die Reihenfolge bestimmt die Sortierung|alle Typen werden ausgegeben|type="Vorlesung, Vorlesung mit Übung, Tutorium"|
|**fauorgnr**|nein|ist dieser Parameter gesetzt, wird er anstelle der FAU Org Nr in den Settings verwendet|Wert, der in den Einstellungen gesetzt ist|fauorgnr="123"|
|**lecturer_id**|nein|IDM-Kennung einer oder mehrere verantwortlicher Personen. Mehrere Werte müssen durch Komma getrennt werden.||lecturer_id="idm1abc, idm2def"|
|**max**|nein|Maximale Anzahl an Lehrveranstaltungen.||max="5"|
|**hide**|nein|Die Anzeige von Teilen der Ausgabe können hiermit unterbunden werden. Derzeit: accordion||hide="accordion"|
|**color**|nein|med oder phil oder tf oder nat oder rw oder fau|fau|color="med"|
|**nodata**|nein|Eine beliebige Zeichenkette|No matching entries found.|nodata="Es wurden keine Lehrveranstaltungen gefunden."|

