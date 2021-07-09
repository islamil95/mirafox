{strip}
    <div class="bodybg pt20 foxbookmarks">
        <div class="lg-centerwrap centerwrap m-margin-reset">
            <div class="cusongs">
                <h1 class="f32">{'Популярные кворки'|t}</h1>
                {if $posts}
                    <div class="cusongslist cusongslist_4_column c4c">
                        {include file="fox_bit.tpl"}
                        <div class="clear"></div>
                    </div>
                    <div class="t-align-c">
                        {insert name=paging_block assign=pages value=a data=$pagingdata}
                    </div>
                {/if}
                <div class="clear"></div>
                <div class="clear" style="padding-bottom:20px;"></div>
            </div>
        </div>
    </div>
{/strip}