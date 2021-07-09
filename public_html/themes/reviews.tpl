{strip}
{if $revs|count gt 0}
    <div class="reviews_order">
        <div class="reviews_order_type ">
            <div id="pos" data-count="{$grat}" data-type="positive" class="reviews-tab__item{if $grat gt 0} active{/if}" {if $grat eq 0}style="cursor: default; color: #000; border-bottom: 1px solid #eaeaea;"{/if}><span>{'Положительные'|t}</span> {$grat}</div>
            <div id="neg" data-count="{$brat}" data-type="negative" class="reviews-tab__item{if $grat eq 0} active{/if}" {if $brat eq 0}style="cursor: default; color: #000; border-bottom: 1px solid #eaeaea;"{/if}><span {if $brat eq 0}style="background-position: -1px -2px;"{/if}>{'Отрицательные'|t}</span> {$brat}<div class="reviews_order_preloader-js preloader__ico preloader__ico--analytics hidden" ></div></div>
        </div>
        <div class="reviews_order_block" style="display:block;">
            <ul class="gig-reviews-list mb20{if $revs|count > 3} gig-reviews-list-full{/if}">
                {control name="reviews_list" revs=$revs count=$count type=$type grat=$grat brat=$brat offset=$offset isTinyView=0}
            </ul>
        </div>
    </div>
    <div class="m-text-center m-hidden">
        <div class="more-btn-blue more-btn-reviews mb10 pr0" data-offset="{$offset}" style="display:none;">
            <span id="more-text" class="more-btn__text" style="cursor: pointer;">{'Показать ещё'|t}</span>
            <span id="arrow-down"></span>
            <div class="reviews_order_preloader-js preloader__ico preloader__ico--analytics hidden" ></div>
        </div>
    </div>
	{if $revs|count > 3}
		<div class="m-text-center mb10 m-visible">
			<a href="javascript:;" class="more-btn__text js-show-more-reviews">
				{'Показать ещё'|t} <i class="fa fa-lg fa-angle-down"></i>
			</a>
		</div>
	{/if}
{else}
    <br>
    <div class="pb20">{'Отзывов по кворку нет'|t}</div>
{/if}
{/strip}