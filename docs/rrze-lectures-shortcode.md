# RRZE Lectures Shortcode

* Plugin Version: 2.3.31
* Public Documentation: https://www.wordpress.rrze.fau.de/plugins/externe-daten-einbinden/lehrveranstaltungen/


To render the lectures, use the `[lectures]` shortcode.
See below for a list of supported attributes.

## Attributes

| Attribute       | Description                                       |  Default | Valid options                       |
|-----------------|---------------------------------------------------|----------|-------------------------------------|
| `format`        | Sets the output view                              | linklist | linklist, tabs                      |
| `fauorgnr`      | Optional filter for FAU-ORG Nummer                | setting  | Comma-separated FAU-ORG Numbers     |
| `sem`           | optional filter for semester                      |          | SoSe`YYYY`, WiSe`YYYY`              |
| `type`          | filters the lists of educationalEvent types       |          | Comma-separated list below          |
| `degree`        | filters the lists of educationalEvent by degrees  |          | Comma-separated list of string      |
| `degree_key`    | filters the lists of educationalEvent by degrees  |          | HIS Key                             |
| `lecture_name`  | filters by the name of the lecture event          |          | String                              |
| `hide`          | hides fields in output                            |          | Comma-separated list below          |
| `show`          | shows fields in output                            |          | module, orgunit                     |
| `lecturer_idm`  | filters by responsible people with login          |          | Comma-separated list of login names |
| `lecturer_identifier`  | filters by responsible people with id      |          | Comma-separated list of identifier  |
| `guest`         | filters by events that are avaible for guests     |          | 1                                   |
| `sortby`	  | Sorts lectures of a given type by an attribut     | title    | title, nextdate                     |  


## Special commands for developing purpose only

Its possible to get debug informations by adding an URI Parameter.

| URI-Parameter                | Description                                                          |
|------------------------------|----------------------------------------------------------------------|
| `debug=1`                    | shows the API request without AUTH Key                               |
| `nocache=1`                  | deactivates the cache                                                |  


If `?debug` ist set, debug informations will also be piped into the JS console.

## List of types

Valid types are (as found on campo at 01.09.2023):
    Vorlesung mit Übung  
    Vorlesung  
    Seminar  
    Praktikum  
    Kurs  
    Online-Kurs  
    Übung  
    Einzelunterricht  
    Kleingruppenunterricht  
    interne Veranstaltung  
    Anleitung zu wiss. Arbeiten  
    Arbeitsgemeinschaft  
    Aufbaukurs  
    Aufbauseminar  
    Begleitseminar  
    Einführungskurs  
    Examensseminar 
    Exkursion  
    Grundkurs  
    Grundseminar  
    Hauptseminar  
    Hauptvorlesung  
    Klausurenkurs  
    Klinische Visite  
    Kolleg  
    Kolloquium  
    Kombiseminar  
    Masterseminar  
    Mittelseminar  
    Mittelseminar (Hauptseminar, PO 2020)  
    Oberseminar  
    Ober- und Hauptseminar  
    PG Masterseminar  
    Praktikum/Projekt  
    Praxisseminar  
    Projekt  
    Projektseminar  
    Propädeutische Übung  
    Proseminar  
    Proseminar (Mittelseminar, PO 2020)  
    Repetitorium  
    Seminar und Übung  
    Sonstige Lehrveranstaltung  
    Sprachhistorisches Seminar  
    Theorieseminar  
    Tutorium  
    Übungsseminar  
    Workshop 

## List for fields to `hide`

Valid fields that can be suppressed for the output are:

* accordion – Unterdrückt Akkordeons
* type_accordion – Unterdrückt Akkordeons für Lehrveranstaltungstypen
* degree_accordion – Unterdrückt Akkordeons für Studiengänge
* type – Unterdrückt Überschriften für Lehrveranstaltungstypen, die erscheinen, wenn die Akkordeons für diese unterdrückt werden
* degree – Unterdrückt Überschriften für Studiengänge, die erscheinen, wenn die Akkordeons unterdrückt werden
* lecture_name – Unterdrückt die Anzeige des Lehrveranstaltungsnamens. Ausnahme: wenn die Linkliste angezeigt wird, hat dieses Attribut keine Auswirkung auf die Darstellung, da die Linkliste nur aus Links mit Lehrveranstaltungsnamen besteht, die auf die Lehrveranstaltungen auf Campo verweisen.



### Examples 