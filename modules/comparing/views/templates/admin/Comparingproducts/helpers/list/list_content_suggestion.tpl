{extends file="helpers/list/list_content.tpl"}

{block name="td_content"}
    {if $key == 'image'}
        {assign var=product value=SuggestionProducts::getImage($tr.id_product)}
        {if $product}
            <div>
                <img src="{$product}" alt="{$tr.id_product}" class="img-responsive" style="max-width: 50px;" />
            </div>
        {/if}
    {elseif $key == 'name'}
        {assign var=product value=SuggestionProducts::getName($tr.id_product)}
        {if $product}
            <div>
                {$product|escape:'html':'UTF-8'}
            </div>
        {/if}
    {elseif $key == 'price'}
        {assign var=currency value=Currency::getDefaultCurrency()}
        {assign var=price value=SuggestionProducts::getPrice($tr.id_product)}
        <div>
            {number_format($price, 3, ',', ' ')|escape:'html':'UTF-8'} {$currency->iso_code}
        </div>
    {elseif $key == 'competitor_product'}
        <div class="competitors-inline-list">
            {* Similar to main template but using SuggestionProducts methods *}
            {assign var=competitors value=SuggestionProducts::getCompetitorProduct($tr.id_product)}
            {* Rest of the competitor display code... *}
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}