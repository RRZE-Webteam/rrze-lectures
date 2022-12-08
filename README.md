# rrze-lectures

- Liefert aufbereitete Daten zu Lehrveranstaltungen von DIP
- WordPress Plugin mit Shortcode, Gutenberg-ready, TinyMCE compatible, Widget (Block und Classic)
- Generiert im Fehlerfall Logs, die rrze-log nutzen kann
- Single site und multisite kompatibel
- Shortcodes sind abwärtskompatibel bis zur aktuellen Version von rrze-univis bei Lehrveranstaltungen
- Funktioniert auch bei Störungen der API zu DIP (Cache mit optionaler Ausgabe des Timestamps)

## Download
GITHub-Repo: https://github.com/RRZE-Webteam/rrze-lectures

## Autor
RRZE-Webteam, http://www.rrze.fau.de

## Copryright
GNU General Public License (GPL) Version 3

## Verwendung

als Shortcode:

a) benutzt das Template: 
`[lecture view="" id/name/lecturerID="" show="" hide=""]`

b) freie Formatierung:
`[lecture id="123"] <strong>$title</strong><br />Hier steht ein zusätzlicher Text<br />Bitte beachten Sie die Anmeldefrist bis zum $start_date</strong> [/lecture]`

Freie Formatierung wird noch umgebaut, damit es mit der Template engine kompatibel ist.

Parameter:

|Parameter|Plichtfeld|Werte|Default|Beispiele|
|-|-|-|-|-|
|**view**|ja|derzeit nur dieser Wert: lecture|
|**id**|nein|Die ID der Lehrveranstaltung: durch Komma getrennte Zahlen||"123, 987" oder "456"|
|**name**|nein|Der Name des/der DozentIn: durch Komma getrennt: Nachname, Vorname||"Mustermann, Manfred" oder "Musterfrau, Monika"|
|**lecturerID**|nein|Die ID der/des DozentIn: durch Komma getrennte Zahlen||"123, 987" oder "456"|
|**show**|nein|durch Komma getrennte Werte: accordion, med oder nat oder rw oder phil oder tk (=Farben des Akkordeons), open, openall, jumpmarks, ics, phone, mobile, fax, url, address, office, call, alle Felder der API (2Do: eigene Tabelle mit Erklärung, was die Feldnamen enthalten und was die anderen Felder bedeuten)||"ics, tel" oder "address"|
|**hide**|nein|siehe show|
|**sem**|nein|Zahl oder Jahreszahl mit Semesterkürzel||"-2" oder "1" oder "2022s"|
|**order**|nein|durch Komma getrennte Berufsbezeichnungen oder Lehrveranstaltungstypen||"UnivIS-Beauftragter" oder "Webmaster, UnivIS-Beauftragter"|
|**hstart**|nein|Zahl von 1 bis 6|2|"2" oder "4"|
|**nodata**|nein|Eine beliebige Zeichenkette|No matching entries found.|Es wurden keine Lehrveranstaltungen gefunden.|

