<!---
This template is used to display a filter for the catalog in the admin panel.
It includes a dropdown menu that allows the user to select a specific catalog to filter by.
The selected catalog will be used to filter the products displayed in the admin panel..
-->
<div class="col-lg-6">
    <div class="form-group">
        <label class="control-label col-lg-3">{l s='Filter by Categories' mod='client'}</label>
        <div class="col-lg-9">
            <div class="checkbox-group">
                {foreach from=$catalogs item=catalog}
                    <div class="checkbox">
                        <label>
                            <input type="checkbox"
                                   name="catalog_filter[]"
                                   value="{$catalog.value}"
                                   {if $catalog.value & $selected_catalogs}checked="checked"{/if}
                                   class="catalog-filter-checkbox">
                            {$catalog.name|escape:'html':'UTF-8'}
                        </label>
                    </div>
                {/foreach}
            </div>
            <div class="filter-buttons">
                <button type="button" class="btn btn-default" id="apply-catalog-filter">
                    <i class="icon-search"></i> {l s='Search' mod='client'}
                </button>
                <button type="button" class="btn btn-default" id="reset-catalog-filter">
                    <i class="icon-refresh"></i> {l s='Reset' mod='client'}
                </button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
document.getElementById('apply-catalog-filter').addEventListener('click', function() {
    var checkboxes = document.getElementsByClassName('catalog-filter-checkbox');
    var value = 0;

    for (var i = 0; i < checkboxes.length; i++) {
        if (checkboxes[i].checked) {
            value |= parseInt(checkboxes[i].value);
        }
    }

    window.location.href = '{$currentIndex|escape:'javascript':'UTF-8'}&token={$token|escape:'javascript':'UTF-8'}&catalog_filter=' + value;
});

document.getElementById('reset-catalog-filter').addEventListener('click', function() {
    window.location.href = '{$currentIndex|escape:'javascript':'UTF-8'}&token={$token|escape:'javascript':'UTF-8'}';
});
</script>