{extends file="helpers/list/list_content.tpl"}

    {block name="td_content"}
        {if $key == 'image'}
            {assign var=product value=ComparingProducts::getImage($tr.id_product)}
            {if $product}
                <div>
                    <img src="{$product}" alt="{$tr.id_product}" class="img-responsive" style="max-width: 50px;" />
                </div>
            {/if}
        {elseif $key == 'name'}
            {assign var=product value=ComparingProducts::getName($tr.id_product)}
            {if $product}
                <div>
                    {$product|escape:'html':'UTF-8'}
                </div>
            {/if}
        {elseif $key == 'price'}
            {assign var=currency value=Currency::getDefaultCurrency()}
            {assign var=price value=ComparingProducts::getPrice($tr.id_product)}
            <div>
                {number_format($price, 3, ',', ' ')|escape:'html':'UTF-8'} {$currency->iso_code}
            </div>
        {elseif $key == 'competitor_product'}
            <div class="competitors-inline-list">
                <table class="table competitors-table-{$tr.id_product}">
                    <thead>
                        <tr>
                            <th>Logo</th>
                            <th>Competitor</th>
                            <th class="fixed-width-md">Price</th>
                            <th>Similarity</th>
                        </tr>
                    </thead>
                    <tbody>
                        {assign var=competitors value=ComparingProducts::getCompetitorProduct($tr.id_product)}
                        {foreach from=$competitors item=competitor}
                            <tr>
                                <td>
                                    {if $competitor.logo}
                                        <img src="{$competitor.logo}" alt="{$competitor.name}" class="img-responsive" style="max-width: 50px;" />
                                    {/if}
                                </td>
                                <td>
                                    <a href="{$competitor.url|escape:'html':'UTF-8'}" target="_blank">{$competitor.name|escape:'html':'UTF-8'}</a>
                                </td>
                                <td>
                                    <div>{$competitor.price|escape:'html':'UTF-8'}</div>
                                </td>
                                <td>
                                    {if $competitor.similarity}
                                        {number_format($competitor.similarity*100, 2)}%
                                    {/if}
                                </td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
        {else}
            {$smarty.block.parent}
        {/if}
    {/block}