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
                                    <div class="col-lg-1">
                                        <div class="form-group">
                                            {if $competitor.logo}
                                                <label>{l s='Logo'}</label>
                                                <img src="{$competitor.logo|escape:'html':'UTF-8'}" alt="Logo"
                                                     class="img-responsive" style="max-width: 50px;">
                                            {/if}
                                        </div>
                                    </div>
                                    <div class="col-lg-10">
                                        <div class="form-group">
                                            <label>{l s='URL'}</label>
                                            <input type="url" class="form-control"
                                                   name="competitors[{$competitor.id_product}][url]"
                                                   value="{$competitor.url}">
                                            <input type="hidden"
                                                   name="competitors[{$competitor.id_product}][id_product]"
                                                   value="{$competitor.id_product}">
                                        </div>
                                    </div>
                                    <div class="col-lg-1">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <div class="input-group">
                                                <button type="button" class="btn btn-danger delete-competitor"
                                                        data-id="{$competitor.id_product}">
                                                    <i class="icon-trash"></i>
                                                </button>
                                            </div>
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
                // Update the competitor row template in the JavaScript
                    var newRow = $('<div class="competitor-row well clearfix">' +
                        '<div class="row">' +
                        '<div class="col-lg-10">' +
                        '<div class="form-group">' +
                        '<label>{l s="URL"}</label>' +
                        '<input type="url" class="form-control url-input" name="new_competitors[' + competitorIndex + '][url]" value="">' +
                        '</div>' +
                        '</div>' +
                        '<div class="col-lg-1 col-lg-offset-1">' +
                        '<div class="form-group">' +
                        '<label>&nbsp;</label>' +
                        '<div class="input-group">' +
                        '<button type="button" class="btn btn-danger delete-competitor">' +
                        '<i class="icon-trash"></i>' +
                        '</button>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '</div>');

                    $('#competitors-container').append(newRow);



                    competitorIndex++;
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
        <style>
            .competitor-row .col-lg-10 {
                padding-right: 15px;
            }


        </style>
    {elseif $input.type == 'search'}
        <div class="form-group">
            <label class="control-label col-lg-3">{l s='Search Target Products'}</label>
            <div class="col-lg-8">
                <div class="input-groups">
                    <input type="text" class="form-control col-lg-12" placeholder="{l s='Type to search...'}"
                           value="{if isset($fields_value.search)}{$fields_value.search|escape:'html':'UTF-8'}{/if}"
                           autocomplete="off" name="search">
                    <div class="target-search-results"></div>
                </div>
            </div>
        </div>
        <script>
            $(document).ready(function () {
                const $searchInput = $('.input-groups input');
                const $searchResults = $('.target-search-results');
                let searchTimeout = null;

                $searchInput.on('input', function () {
                    const query = $(this).val();
                    clearTimeout(searchTimeout);

                    if (query.length >= 3) {
                        searchTimeout = setTimeout(function () {
                            $.ajax({
                                url: currentIndex + '&ajax=1&action=searchTargetProducts&token=' + token,
                                type: 'POST',
                                dataType: 'json',
                                data: {
                                    q: query
                                },
                                success: function (response) {
                                    if (response.success) {
                                        if (response.html) {
                                            $searchResults.html('<ul>' + response.html + '</ul>').show();
                                        } else {
                                            $searchResults.hide();
                                        }
                                    }
                                }
                            });
                        }, 300);
                    } else {
                        $searchResults.empty().hide();
                    }
                });

                $(document).on('click', '.search-item', function () {
                    const url = $(this).data('url');
                    const price = $(this).find('small:contains("Price:")').text().replace('Price:', '').trim();
                    const name = $(this).find('.product-name').text();
                    $searchInput.val(name);


                    // $('input[name="url"]').val(url);
                    $searchResults.empty().hide();
                });

                $(document).on('click', function (e) {
                    if (!$(e.target).closest('.input-group').length) {
                        $searchResults.empty().hide();
                    }
                });
            });
        </script>
        <style>
            .target-search-results {
                position: absolute;
                top: 100%;
                left: 0;
                z-index: 1000;
                width: 100%;
                max-height: 300px;
                overflow-y: auto;
                background: #fff;
                border: 1px solid #ddd;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            }

            .target-search-results li {
                padding: 8px 15px;
                cursor: pointer;
                transition: background 0.2s;
            }

            .target-search-results li:hover {
                background: #f5f5f5;
            }

            .target-search-results .no-results {
                color: #999;
                font-style: italic;
            }

            .target-search-results .product-name {
                display: block;
                color: #333;
            }
        </style>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}
