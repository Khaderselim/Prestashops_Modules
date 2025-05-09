{extends file="helpers/form/form.tpl"}

{block name="field"}
    {if $input.type == 'search'}
        <div class="form-group">
            <label class="control-label col-lg-3">{l s='Search Target Products'}</label>
            <div class="col-lg-8">
                <div class="input-groups">
                    <input type="text" class="form-control col-lg-12" placeholder="{l s='Type to search...'}"
                           value="{if isset($fields_value.search)}{$fields_value.search|escape:'html':'UTF-8'}{/if}"
                           autocomplete="off" name="search" id="product_search">
                    <div class="target-search-results"></div>
                </div>
            </div>
        </div>
        <input type="hidden" name="product_id" id="selected_product_id" value="{if isset($fields_value.product_id)}{$fields_value.product_id|escape:'html':'UTF-8'}{/if}">
        <script>
            $(document).ready(function () {
                const $searchInput = $('#product_search');
                const $searchResults = $('.target-search-results');
                let searchTimeout = null;
                function removeHtmlTags(text) {
                    if (typeof text === 'string') {
                        return text.replace(/<[^>]*>/g, '');
                    }
                    return text;
                }
                $searchInput.on('input', function () {
                    const query = $(this).val();
                    clearTimeout(searchTimeout);

                    if (query.length >= 3) {
                        searchTimeout = setTimeout(function () {
                            // AJAX call to search for target products
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

                // Fix for product selection
                $(document).on('click', '.search-item', function () {
                    const productId = $(this).data('product-id');
                    const name = $(this).find('.product-name').text();

                    $searchInput.val(name);
                    $('#selected_product_id').val(productId);

                    // AJAX call to fetch product details
                    $.ajax({
                        url: currentIndex + '&ajax=1&action=fetchProductInfo&token=' + token,
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            id_product: productId,
                            product_name: name
                        },
                        success: function(response) {
                            if (response.success) {
                                // Update product data in the table
                                updateProductInTable(response.data.name, response.data.price, response.data.description);

                                // Show success message
                                showSuccessMessage('{l s='Product data fetched successfully'}');
                            } else {
                                // Show error
                                showErrorMessage(response.error || '{l s='Error fetching product data'}');
                            }
                        },
                        error: function() {
                            showErrorMessage('{l s='Error fetching product data'}');
                        }
                    });

                    // Hide search results
                    $searchResults.empty().hide();
                });

                $(document).on('click', function (e) {
                    if (!$(e.target).closest('.input-groups').length) {
                        $searchResults.empty().hide();
                    }
                });
                function calculateSimilarity() {
                    const originalProduct = {
                        name: $('#your_product_name').text().trim(),
                        price: $('#your_product_price').text().trim(),
                        description: $('#your_product_description').text().trim()
                    };

                    const competitorProduct = {
                        name: $('#competitor_product_name').text().trim(),
                        price: $('#competitor_product_price').text().trim(),
                        description: $('#competitor_product_description').text().trim(),
                        url: $('#competitor_url').val().trim()
                    };

                    // Check if both products have data
                    if (originalProduct.name && competitorProduct.name) {
                        $.ajax({
                            url: currentIndex + '&ajax=1&action=calculateSimilarity&token=' + token,
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                original_product: JSON.stringify(originalProduct),
                                competitor_product: JSON.stringify(competitorProduct)
                            },
                            success: function(response) {
                                if (response.success) {
                                    const similarityHtml = '<tr>' +
                                        '<td><strong>{l s="Similarity Score"}</strong></td>' +
                                        '<td colspan="2">' + (response.similarity*100).toFixed(2) + '%</td>' +
                                        '</tr>';
                                    $('#comparison_results_table tbody').append(similarityHtml);
                                } else {
                                    showErrorMessage(response.error || '{l s='Error calculating similarity'}');
                                }
                            },
                            error: function() {
                                showErrorMessage('{l s='Error calculating similarity'}');
                            }
                        });
                    } else {
                        showErrorMessage('{l s='Please ensure both products have data'}');
                    }
                }
                // Function to update product data in the results table
                function updateProductInTable(name, price, description) {
                    const $resultsTable = $('#comparison_results_table');

                    if ($resultsTable.length) {
                        // Update product data
                        $('#your_product_name').text(name);
                        $('#your_product_price').text(price);
                        $('#your_product_description').text(removeHtmlTags(description));

                        // Show the results table
                        $resultsTable.closest('.panel').show();
                    }
                }

                // Add AJAX fetch for competitor URL
                $('#fetch_competitor_ajax').on('click', function(e) {
                    e.preventDefault();

                    const url = $('#competitor_url').val();
                    if (!url) {
                        alert('{l s='Please enter a competitor URL'}');
                        return;
                    }

                    // Show loading indicator
                    $('#competitor_product_name').html('<div class="alert alert-info">{l s='Loading competitor data...'}</div>');

                    $.ajax({
                        url: currentIndex + '&ajax=1&action=fetchCompetitorInfo&token=' + token,
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            url: url
                        },
                        success: function(response) {
                            if (response.success) {
                                // Update competitor data in the table
                                $('#competitor_product_name').text(response.data.name);
                                $('#competitor_product_price').text(response.data.price);
                                $('#competitor_product_description').text(response.data.description);
                                calculateSimilarity();
                                // Show success message
                                showSuccessMessage('{l s='Competitor product data fetched successfully'}');
                            } else {
                                // Show error
                                $('#competitor_product_name').html('<div class="alert alert-danger">' + response.error + '</div>');
                                showErrorMessage(response.error);
                            }
                        },
                        error: function() {
                            $('#competitor_product_name').html('<div class="alert alert-danger">{l s='Error fetching competitor data'}</div>');
                            showErrorMessage('{l s='Error fetching competitor data'}');
                        }
                    });
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
    {elseif $input.type == 'Url'}
        <div class="form-group">
            <label class="control-label col-lg-3">{l s='Competitor Product Url'}</label>
            <div class="col-lg-8">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="{l s='http://example.com/product'}"
                           name="competitor_product_url" id="competitor_url"
                           value="{if isset($fields_value.competitor_product_url)}{$fields_value.competitor_product_url|escape:'html':'UTF-8'}{/if}">
                    <span class="input-group-btn">
                        <button type="button" class="btn btn-default" id="fetch_competitor_ajax">{l s='Fetch Data'}</button>
                    </span>
                </div>
                <p class="help-block">{l s='Enter competitor product URL and click "Fetch Data" to retrieve product details without page refresh'}</p>
            </div>
        </div>
    {elseif $input.type == 'results_table'}
        <div class="form-group">
            <label class="control-label col-lg-3">{l s='Comparison Results'}</label>
            <div class="col-lg-9">
                <div class="panel" {if !isset($fields_value.search) || empty($fields_value.search)}style="display: none;"{/if}>
                    <div class="panel-heading">
                        <i class="icon-table"></i> {l s='Product Comparison Results'}
                        <div class="panel-heading-action">
                            <a href="{$current|escape:'html':'UTF-8'}&amp;token={$token|escape:'html':'UTF-8'}&amp;resetComparison=1" class="btn btn-default btn-xs">
                                <i class="icon-refresh"></i> {l s='Reset Comparison'}
                            </a>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table" id="comparison_results_table">
                            <thead>
                            <tr>
                                <th></th>
                                <th>{l s='Your Product'}</th>
                                <th>{l s='Competitor Product'}</th>
                            </tr>
                            </thead>
                            <tbody>

                            <tr>
                                <td><strong>{l s='Name'}</strong></td>
                                <td id="your_product_name">
                                    {if isset($fields_value.search)}{$fields_value.search|escape:'html':'UTF-8'}{/if}
                                </td>
                                <td id="competitor_product_name">
                                    {if isset($fields_value.competitor_product_name)}
                                        {$fields_value.competitor_product_name|escape:'html':'UTF-8'}
                                    {elseif isset($fields_value.has_competitor_data)}
                                        <div class="alert alert-warning">{l s='No name available'}</div>
                                    {else}
                                        <div class="alert alert-info">{l s='Enter competitor URL and click "Fetch Data" or submit form'}</div>
                                    {/if}
                                </td>
                            </tr>
                            <tr>
                                <td><strong>{l s='Price'}</strong></td>
                                <td id="your_product_price">
                                    {if isset($fields_value.product_price)}{$fields_value.product_price|escape:'html':'UTF-8'}{/if}
                                </td>
                                <td id="competitor_product_price">
                                    {if isset($fields_value.competitor_product_price)}
                                        {$fields_value.competitor_product_price|escape:'html':'UTF-8'}
                                    {elseif isset($fields_value.has_competitor_data)}
                                        <div class="alert alert-warning">{l s='No price available'}</div>
                                    {else}
                                        <div class="alert alert-info">{l s='Price will appear here'}</div>
                                    {/if}
                                </td>
                            </tr>
                            <tr>
                                <td><strong>{l s='Description'}</strong></td>
                                <td id="your_product_description">
                                    {if isset($fields_value.product_description)}{$fields_value.product_description|truncate:100:'...'|escape:'html':'UTF-8'}{/if}
                                </td>
                                <td id="competitor_product_description">
                                    {if isset($fields_value.competitor_product_description)}
                                        {$fields_value.competitor_product_description|truncate:100:'...'|escape:'html':'UTF-8'}
                                    {elseif isset($fields_value.has_competitor_data)}
                                        <div class="alert alert-warning">{l s='No description available'}</div>
                                    {else}
                                        <div class="alert alert-info">{l s='Description will appear here'}</div>
                                    {/if}
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}