{{first}}
    <div class="rrze-lectures">
        {{degree_title}}
        {{atts_show_degree_title}}
            <h{{=degree_hstart}}>{{=degree_title}}</h{{=degree_hstart}}>
        {{:atts_show_degree_title}}
            <br>&nbsp;<br>
        {{/atts_show_degree_title}}
        {{/degree_title}}
        {{atts_do_accordion}}
            [collapsibles hstart="{{=atts_hstart}}"]
        {{/atts_do_accordion}}
{{/first}}
{{atts_do_type_accordion}}
    {{type_title}}
        {{atts_do_degree_accordion}}
            {{degree_start}}
                [accordion]
            {{/degree_start}}
            [accordion-item title="{{=type_title}}" id="{{=type_title}}_{{=identifier}}"]
        {{:atts_do_degree_accordion}}
            [collapse title="{{=type_title}}" id="{{=type_title}}_{{=identifier}}"]
        {{/atts_do_degree_accordion}}        
    {{/type_title}}
{{:atts_do_type_accordion}}
    {{type_title}}
        <h{{=type_hstart}}>{{=type_title}}</h{{=type_hstart}}>
    {{/type_title}}
{{/atts_do_type_accordion}}
{{type_start}}
    <ul>
{{/type_start}}

<li><a href="{{=campo_url}}" target="rrze-campo" class="rrze-lectures-linklist">{{=name}}</a></li>

{{type_end}}
    </ul>
{{/type_end}}    
{{atts_do_type_accordion}}
    {{type_end}}
        {{atts_do_degree_accordion}}
            [/accordion-item]
            {{degree_end}}
                [/accordion]
            {{/degree_end}}
        {{:atts_do_degree_accordion}}
            [/collapse]
        {{/atts_do_degree_accordion}}        
    {{/type_end}}
{{/atts_do_type_accordion}}

{{last}}
    {{atts_do_accordion}}
        [/collapsibles]
    {{/atts_do_accordion}}
    </div>
{{/last}}
