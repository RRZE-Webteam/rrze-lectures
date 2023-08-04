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
| `degree`        | filters the lists of educationalEvent by degrees  |          | Comma-separated list                |
| `lecture_name`  | filters by the name of the lecture event          |          | String                              |
| `hide`          | hides fields in output                            |          | Comma-separated list below          |
| `show`          | shows fields in output                            |          | module, orgunit                     |
| `lecturer_idm`  | filters by responsible people with login          |          | Comma-separated list of login names |
| `lecturer_identifier`  | filters by responsible people with id      |          | Comma-separated list of identifier  |
| `guest`         | filters by events that are avaible for guests     |          | 1                                   |


## Special commands for developing purpose only

Its possible to get debug informations by adding an URI Parameter.

| URI-Parameter                | Description                                                          |
|------------------------------|----------------------------------------------------------------------|
| `debug=1`                    | shows the API request without AUTH Key                               |
| `nocache=1`                  | deactivates the cache                                                |  


If `?debug` ist set, debug informations will also be piped into the JS console.

## List of types

Valid types are:
* `Vorlesung`
* `Vorlesung mit Übung`
* `Übung`


## List for fields to `hide`

Valid fields that can be suppressed for the output are:

* accordion – Unterdrückt Akkordeons
* type_accordion – Unterdrückt Akkordeons für Lehrveranstaltungstypen
* degree_accordion – Unterdrückt Akkordeons für Studiengänge
* type – Unterdrückt Überschriften für Lehrveranstaltungstypen, die erscheinen, wenn die Akkordeons für diese unterdrückt werden
* degree – Unterdrückt Überschriften für Studiengänge, die erscheinen, wenn die Akkordeons unterdrückt werden
* lecture_name – Unterdrückt die Anzeige des Lehrveranstaltungsnamens. Ausnahme: wenn die Linkliste angezeigt wird, hat dieses Attribut keine Auswirkung auf die Darstellung, da die Linkliste nur aus Links mit Lehrveranstaltungsnamen besteht, die auf die Lehrveranstaltungen auf Campo verweisen.



### Examples 