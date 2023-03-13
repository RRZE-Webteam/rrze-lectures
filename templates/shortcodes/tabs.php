{{first}}
    <div class="rrze-lectures">
        <div itemscope="" itemtype="https://schema.org/Course">

        {{do_accordion}}
            [collapsibles hstart="{{=hstart}}"]
        {{/do_accordion}}
{{/first}}
{{do_degree_accordion}}
    {{degree_title}}
        [collapse title="{{=degree_title}}" id="{{=degree_title}}_{{=identifier}}"]
    {{/degree_title}}
{{:do_degree_accordion}}
    {{degree_title}}
        {{show_degree_title}}
            <H{{=degree_hstart}}>{{=degree_title}}</H{{=degree_hstart}}>
        {{:show_degree_title}}
            <br>&nbsp;<br>
        {{/show_degree_title}}
    {{/degree_title}}
{{/do_degree_accordion}}
{{do_type_accordion}}
    {{type_title}}
        {{do_degree_accordion}}
            {{degree_start}}
                [accordion]
            {{/degree_start}}
            [accordion-item title="{{=type_title}}" id="{{=type_title}}_{{=identifier}}"]
        {{:do_degree_accordion}}
            [collapse title="{{=type_title}}" id="{{=type_title}}_{{=identifier}}"]
        {{/do_degree_accordion}}        
    {{/type_title}}
{{:do_type_accordion}}
    {{type_title}}
        <H{{=type_hstart}}>{{=type_title}}</H{{=type_hstart}}>
    {{/type_title}}
{{/do_type_accordion}}
{{type_start}}
    <ul>
{{/type_start}}

        <h2><span itemprop="name">{{=name}}</span></h2>

        [tabs]
        [tab title="Grunddaten"]

        Titel: {{=providerValues.event.title}}
        Kurztext: {{=providerValues.event.shorttext}}

        {{providerValues.event_orgunit}}
        Organisationseinheit:
        <ul>
            {{/providerValues.event_orgunit}}
            {{@providerValues.event_orgunit}}
            <li>{{=_val.orgunit}}</li>
            {{/@providerValues.event_orgunit}}
            {{providerValues.event_orgunit}}
        </ul>
        {{/providerValues.event_orgunit}}

        <p><? echo __('Type of event', 'rrze-lectures'); ?>: {{=providerValues.event.eventtype}}</p>
        <p><? echo __('Frequency of the offer', 'rrze-lectures'); ?>: Feld FEHLT (Stand: 2023-03-10)</p>

        {{providerValues.event.comment}}[alert style="warning"]<strong>Kommentar</strong><br>{{=providerValues.event.comment}}[/alert]{{/providerValues.event.comment}}


        [/tab]
        [tab title="Parallelgruppen / Termine"]



        <br>&nbsp;<br>

        {{@providerValues.courses}}

        <a href="{{=_val.url}}" target="rrze-campo" class="rrze-lectures-linklist">Link zu Campo</a>

        <p>Semesterwochenstunden: {{=_val.hours_per_week}}</p>

        <p>Lehrsprache: {{=_val.teaching_language_txt}}</p>

        Verantwortliche/-r
        <ul>
            {{@_val.event_responsible}}
            <li itemprop="provider" itemscope="" itemtype="http://schema.org/Person">
                <a href="hier fehlt der Link zu IdM oder DIP liefert ID für rrze-contact oder event_responsible hat unter location einen URL">{{_val.prefixTitle}}<span itemprop="honorificPrefix">{{=_val.prefixTitle}}</span> {{/_val.prefixTitle}}{{_val.firstname}}<span itemprop="givenName">{{=_val.firstname}}</span> {{/_val.firstname}}{{_val.surname}}<span itemprop="familyName">{{=_val.surname}}</span>{{:_val.surname}}! surname (schema: familyName) ist LEER !{{/_val.surname}}</a>
            </li>
            {{/@_val.event_responsible}}
        </ul>

        <h4>Inhalt</h4>
        <p itemprop="description">{{_val.contents}}{{=_val.contents}}{{:_val.contents}}_val.contents ist NULL, sollte aber Inhalt haben (Stand: 2023-02-21){{/_val.contents}}</p>

        <h4>Literaturhinweise</h4>
        <p>{{_val.literature}}{{=_val.literature}}{{:_val.literature}}_val.literature ist NULL, sollte aber Inhalt haben (Stand: 2023-02-21){{/_val.literature}}</p>

        <h4>Empfohlene Voraussetzungen / Organisatorisches</h4>
        <p>{{_val.compulsory_requirement}}{{=_val.compulsory_requirement}}{{:_val.compulsory_requirement}}_val.compulsory_requirement ist NULL, sollte aber Inhalt haben (Stand: 2023-02-21){{/_val.compulsory_requirement}}</p>

        <h4>Maximale Anzahl Teilnehmer/-innen</h4>
        <p>{{_val.attendee_maximum}}{{=_val.attendee_maximum}}{{:_val.attendee_maximum}}_val.attendee_maximum ist NULL, sollte aber Inhalt haben (Stand: 2023-02-21){{/_val.attendee_maximum}}</p>
        <h4>Minimum der Teilnehmer/-innen für das Stattfinden der Veranstaltung</h4>
        <p>{{_val.attendee_minimum}}{{=_val.attendee_minimum}}{{:_val.attendee_minimum}}Feld FEHLT (Stand: 2023-02-21) (Vorschlag: _val.attendee_minimum){{/_val.attendee_minimum}}</p>

        {{maximumAttendeeCapacity}}<p>Maximale Anzahl Teilnehmer/-innen: {{=maximumAttendeeCapacity}}</p>{{/maximumAttendeeCapacity}}
        <p>Minimum der Teilnehmer/-innen für das Stattfinden der Veranstaltung: Feld FEHLT (Stand: 2023-02-21) (Vorschlag: minimumAttendeeCapacity)</p>


        <table>
            <tr>
                <td>Rhythmus</td>
                <td>Wochentag</td>
                <td>Von - Bis</td>
                <td>Ausfalltermin (2DO: sort - via API request!</td>
                <td>Startdatum - Enddatum</td>
                <td>Erw. Tn.</td>
                <td>Bemerkung</td>
                <td>Durchführende/-r</td>
                <td>Raum</td>
            </tr>
            {{@_val.planned_dates}}
            <tr>
                <td>{{_val.rhythm}}{{=_val.rhythm}}{{/_val.rhythm}}</td>
                <td>{{_val.weekday}}{{=_val.weekday}}{{/_val.weekday}}</td>
                <td>{{_val.starttime}}{{=_val.starttime}}{{/_val.starttime}}
                    -
                    {{_val.endtime}}{{=_val.endtime}}{{/_val.endtime}}
                </td>
                <td>{{@_val.misseddates}}{{=_val}}<br>{{/@_val.misseddates}}</td>
                <td>{{_val.startdate}}{{=_val.startdate}}{{/_val.startdate}}
                    -
                    {{_val.enddate}}{{=_val.enddate}}{{/_val.enddate}}
                </td>
                <td>{{_val.expected_attendees_count}}{{=_val.expected_attendees_count}}{{/_val.expected_attendees_count}}
                </td>
                <td>{{_val.comment}}{{=_val.comment}}{{/_val.comment}}
                </td>
                <td>individual_instructor => falls Array gefüllt ist (2DO: LV finden mit !empty(roviderValues.courses.planned_dates.instructor)</td>
                <td>{{_val.famos_request}}{{=_val.famos_request}}{{/_val.famos_request}} (2DO: famos_request URL FEHLT oder kann Link mit dieser Nr auf Map / Details-Page gesetzt werden?)
                </td>
            </tr>
            {{/@_val.planned_dates}}

        </table>
        {{:@providerValues.courses}}
        Es sind noch keine Termine geplant. (2DO: Text von Campo)
        {{/@providerValues.courses}}

        [/tab]

        {{providerValues.modules}}

        [tab title="Module / Studiengänge"]

        <table>
            <tr>
                <td>Standardtext</td>
                <td>Typ</td>
                <td>Abschluss</td>
                <td>Fach</td>
                <td>Vertiefung</td>
                <td>Schwerpunkt</td>
                <td>Fachkennzeichen</td>
                <td>Prüfungsordnungsversion</td>
                <td>Studienform</td>
                <td>Studienort</td>
                <td>Studienart</td>
            </tr>

            {{@providerValues.modules}}
            {{@_val.modules_cos}}

            <tr>
                <td>FEHLT (Stand: 2023-02-21)</td>
                <td>FEHLT (Stand: 2023-02-21)</td>
                <td>{{=_val.degree}}</td>
                <td>{{=_val.subject}}</td>
                <td>{{=_val.major}}</td>
                <td>FEHLT (Stand: 2023-02-21)</td>
                <td>{{=_val.subject_indicator}}</td>
                <td>{{=_val.version}}</td>
                <td>FEHLT (Stand: 2023-02-21)</td>                
                <td>FEHLT (Stand: 2023-02-21)</td>
                <td>FEHLT (Stand: 2023-02-21)</td>                
            </tr>
            {{/@_val.modules_cos}}
            {{/@providerValues.modules}}
        </table>

        [/tab]

        {{/providerValues.modules}}
    [/tabs]



        {{do_type_accordion}}
    {{type_end}}
        {{do_degree_accordion}}
            [/accordion-item]
            {{degree_end}}
                [/accordion]
            {{/degree_end}}
        {{:do_degree_accordion}}
            [/collapse]
        {{/do_degree_accordion}}        
    {{/type_end}}
{{/do_type_accordion}}
{{do_degree_accordion}}
    {{degree_end}}
        [/collapse]
    {{/degree_end}}
{{/do_degree_accordion}}
{{last}}
    {{do_accordion}}
        [/collapsibles]
    {{/do_accordion}}
    </div>
{{/last}}
