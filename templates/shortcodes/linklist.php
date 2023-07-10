{{first}}
    <div class="rrze-lectures">
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

<li><a href="{{=campo_url}}" target="rrze-campo" class="rrze-lectures-linklist">{{=name}}</a></li>

{{type_end}}
    </ul>
{{/type_end}}    
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
