<div class="panel">
    <div class="panel-heading">
        <i class="icon-filter"></i> {l s='Filter Options' mod='client'}
        <span class="panel-heading-action">
            <a class="list-toolbar-btn" data-toggle="collapse" href="#filterPanel">
                <i class="icon-caret-down"></i>
            </a>
        </span>
    </div>
    <div id="filterPanel" class="panel-collapse collapse {if $selected_catalogs}in{/if}">
        <div class="row">
            {$catalog_filter_html nofilter}
        </div>
    </div>
</div>