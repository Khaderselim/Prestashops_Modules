{extends file="helpers/form/form.tpl"}
<!---
    This template is used to render the form for the Target Tracking module.
    It includes a field for competitors and a search input for target products.
    The JavaScript handles adding and removing competitors dynamically.
    The search input provides AJAX functionality to search for target products.
    -->
{block name="field"}
    {if $input.type == 'competitors'}
        <div class="form-group">
            <label class="control-label col-lg-3">{l s='Competitors'}</label>
            <div class="col-lg-8">
                <div id="competitors-container">
                    {if isset($fields_value.competitors) && $fields_value.competitors|count > 0}
                        {foreach from=$fields_value.competitors item=competitor}
                            <div class="competitor-row well clearfix">
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>{l s='Competitor'}</label>
                                            <select class="form-control competitor-select"
                                                    name="competitors[{$competitor.id_competitor}][id_competitor]">
                                                {foreach from=$list_competitors item=comp}
                                                    <option value="{$comp.id_target_competitor}"
                                                            {if $comp.id_target_competitor == $competitor.id_competitor}selected{/if}>
                                                        {$comp.name|capitalize|escape:'html':'UTF-8'}
                                                    </option>
                                                {/foreach}
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-2 col-lg-offset-1">
                                        <div class="form-group">
                                            <label>{l s='Priority'}</label>
                                            <select class="form-control"
                                                    name="competitors[{$competitor.id_competitor}][priority]">
                                                <option value="1" {if $competitor.priority == 1}selected{/if}>1</option>
                                                <option value="2" {if $competitor.priority == 2}selected{/if}>2</option>
                                                <option value="3" {if $competitor.priority == 3}selected{/if}>3</option>
                                                <option value="4" {if $competitor.priority == 4}selected{/if}>4</option>
                                                <option value="5" {if $competitor.priority == 5}selected{/if}>5</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-1 col-lg-offset-4">
                                        <div class="form-group">
                                            <!-- Remove this empty label that's causing the vertical offset -->
                                            <!-- <label>&nbsp;</label> -->
                                            <!-- Remove the unnecessary input-group div -->
                                            <button type="button" class="btn btn-danger delete-competitor"
                                                    data-id="{$competitor.id_competitor}" style="margin-top: 25px;">
                                                <i class="icon-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {/foreach}
                    {/if}
                </div>
                <button type="button" class="btn btn-default" id="add-competitor">
                    <i class="icon-plus"></i> {l s='Add Competitor'}
                </button>
            </div>
        </div>
        <script type="text/javascript">
            $(document).ready(function () {
                var competitorIndex = {$fields_value.competitors|count|default:0};


                $('#add-competitor').click(function () {
                    var newRow = $('<div class="competitor-row well clearfix">' +
                        '<div class="row">' +
                        '<div class="col-lg-4" >' +
                        '<div class="form-group">' +
                        '<label>{l s="Competitor"}</label>' +
                        '<select class="form-control competitor-select" name="new_competitors[' + competitorIndex + '][id_competitor]">' +
                        '<option value="">{l s="Select a competitor"}</option>' +
                        '{foreach from=$list_competitors item=competitor}' +
                        '<option value="{$competitor.id_target_competitor}" data-logo="{$competitor.logo|escape:'html':'UTF-8'}">' +
                        '{$competitor.name|capitalize|escape:'html':'UTF-8'}</option>' +
                        '{/foreach}' +
                        '</select>' +
                        '</div>' +
                        '</div>' +
                        '<div class="col-lg-2 col-lg-offset-1" >' +
                        '<div class="form-group">' +
                        '<label>{l s="Priority"}</label>' +
                        '<select class="form-control" name="new_competitors[' + competitorIndex + '][priority]">' +
                        '<option value="1">1</option>' +
                        '<option value="2">2</option>' +
                        '<option value="3">3</option>' +
                        '<option value="4">4</option>' +
                        '<option value="5">5</option>' +
                        '</select>' +
                        '</div>' +
                        '</div>' +
                        '<div class="col-lg-1 col-lg-offset-4" >' +
                        '<div class="form-group">' +
                        '<button type="button" class="btn btn-danger delete-competitor" style="margin-top: 25px;">' +
                        '<i class="icon-trash"></i>' +
                        '</button>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '</div>');

                    competitorIndex++;


                    $('#competitors-container').append(newRow);


                });


                $(document).on('click', '.delete-competitor', function () {
                    if (confirm('{l s="Are you sure you want to delete this competitor?"}')) {
                        var $row = $(this).closest('.competitor-row');
                        var competitorId = $(this).data('id');
                        if (competitorId) {
                            $row.append('<input type="hidden" name="delete_competitors[]" value="' + competitorId + '">');
                            $row.hide();
                        } else {
                            $row.remove();
                        }
                    }
                });
            });
        </script>
    {else}
        {$smarty.block.parent}

    {/if}
{/block}
