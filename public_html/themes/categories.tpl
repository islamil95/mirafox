{strip}
{include file="fox_error7.tpl"}
{assign var=lang value=Translations::getLang()}
<div class="static-page__block">

        <div class="noborder font-OpenSans f15 white-bg-block centerwrap">
            <div class="pt10 m-visible"></div>



			<div class="clearfix block-response m-p0">
                <h1 class="f32  m-hidden">{'Все категории'|t}</h1>


                <div class="category-tree category-tree_index m-visible mt0">
                    <div class="category">
                
                        <a href="{$baseurl}/{$catalog}/design" class="color-text db">

                            <i class="icon ico-design v-align-m"></i>
                            <span class="dib w75p ml12 v-align-m">
                                <span class="bold f18 dib">{'Дизайн'|t}</span><br>
                                <span class="f14 mt3 dib lh16 color-gray">{'Логотипы, веб-дизайн, визитки'|t}</span>
                            </span>
                        </a>
                    </div>
                    <div class="category">
                        <a href="{$baseurl}/{$catalog}/programming" class="color-text db">
                            <i class="icon ico-code v-align-m"></i>
                            <span class="dib w75p ml12 v-align-m">
                                <span class="bold f18 dib">{'Разработка и IT'|t}</span><br>
                                <span class="f14 mt3 dib lh16 color-gray">{'Доработка, сайт под ключ'|t}</span>
                            </span>
                        </a>
                    </div>
                    {if !App::isMirror()}
                        <div class="category">
                            <a href="{$baseurl}/{$catalog}/seo" class="color-text db">
                                <i class="icon ico-seo v-align-m"></i>
                                <span class="dib w75p ml12 v-align-m">
                                    <span class="bold f18 dib">{'SEO и трафик'|t}</span><br>
                                    <span class="f14 mt3 dib lh16 color-gray">{'Аудиты, ссылки, трафик'|t}</span>
                                </span>
                            </a>
                        </div>
                    {/if}
                    <div class="category">
                        <a href="{$baseurl}/{$catalog}/writing-translations" class="color-text db">
                            <i class="icon ico-writing v-align-m"></i>

                            <span class="dib w75p ml12 v-align-m">
                                <span class="bold f18 dib">{'Тексты и переводы'|t}</span><br>
                                <span class="f14 mt3 dib lh16 color-gray">{'Статьи, корректура, переводы'|t}</span>
                            </span>
                        </a>
                    </div>
                    <div class="category">
                        <a href="{$baseurl}/{$catalog}/{if $lang == Translations::EN_LANG}marketing{else}promotion{/if}" class="color-text db">
                            <i class="icon ico-marketing v-align-m"></i>
                            <span class="dib w75p ml12 v-align-m">
                                <span class="bold f18 dib">{'Маркетинг и реклама'|t}</span><br>
                                <span class="f14 mt3 dib lh16 color-gray">{'Реклама, продвижение, PR'|t}</span>
                            </span>
                        </a>
                    </div>
                    <div class="category">
                        <a href="{$baseurl}/{$catalog}/business" class="color-text db">
                            <i class="icon ico-business v-align-m"></i>
                            <span class="dib w75p ml12 v-align-m">
                                <span class="bold f18 dib">{'Бизнес'|t}</span><br>
                                <span class="f14 mt3 dib lh16 color-gray">{'Наполнение сайта, консультации'|t}</span>
                            </span>
                        </a>
                    </div>
                    <div class="category">
                        <a href="{$baseurl}/{$catalog}/audio-video" class="color-text db">
                            <i class="icon ico-media v-align-m"></i>
                            <span class="dib w75p ml12 v-align-m">
                                <span class="bold f18 dib">{'Аудио и видео'|t}</span><br>
                                <span class="f14 mt3 dib lh16 color-gray">{'Музыка, обработка, видеоролики'|t}</span>
                            </span>
                        </a>
                    </div>
                    <div class="category">
                        <a href="{$baseurl}/{$catalog}/{if $lang == Translations::EN_LANG}lifestyle{else}hobbies{/if}" class="color-text db">
                            <i class="icon ico-life v-align-m"></i>
                            <span class="dib w75p ml12 v-align-m">
                                <span class="bold f18 dib">{'Стиль жизни'|t}</span><br>
                                <span class="f14 mt3 dib lh16 color-gray">{'Репетиторы, фитнес, интерьер'|t}</span>
                            </span>
                        </a>
                    </div>
                </div>



	            <div class="font-OpenSans all_cats m-hidden">
	                <div class="category-tree">
	                    {foreach item=table from=$arrayCats}
                                <table style="display: inline-block;">
                                    {foreach item=category from=$table}
                                    <tr style="padding-top: 10px;">
                                        <td class="category">
                                            <h2 class="h2-OpenSans" style="line-height:22px; margin-bottom:10px;">
                                                    <a href="{$baseurl}/{$catalog}/{$category->seo|lower|stripslashes}">
                                                            {$category->name|t|stripslashes}
                                                    </a>
                                            </h2>
                                            <ul>
                                            {foreach item=cat from=$category->cats}
                                                <li>                                                    
                                                    {if $cat->landList|count > 0}
                                                        <div id="subLandCategoriesButton_{$cat->id}" class="showmorebtn sub_land_button_hide" onclick="hideSubLandCategories({$cat->id});"></div>
                                                        <a class="f14" href="{$baseurl}/{$catalog}/{$cat->seo|lower|stripslashes}" style="line-height: 22px;">
                                                                {$cat->name|t|stripslashes}
                                                        </a>
                                                        <ul id="landCatList_{$cat->id}" class="sub_land_categories_list" style="margin-left: 10px; margin-top: 5px;">
                                                        {foreach from=$cat->landList item=landItem}
                                                                <li>
                                                                    <a class="f14" href="{$baseurl}/land/{$landItem.name|lower|stripslashes}">
                                                                        {$landItem.seo|t|stripslashes}
                                                                    </a>
                                                                </li>
                                                        {/foreach}
                                                        </ul>
                                                    {else}
                                                        <a href="{$baseurl}/{$catalog}/{$cat->seo|lower|stripslashes}">{$cat->name|t|stripslashes}</a>
                                                    {/if}
                                                </li>
                                            {/foreach}
                                            </ul>
                                        </td>
                                    </tr>
                                    {/foreach}
                                </table>
                            {/foreach}
	                </div>
	            </div>
				<div class="clear"></div>
			</div>

	</div>
</div>
<div id="foxnobottom">
    <div class="centerwrap footertop">
        <div class="footerbg gray foxfooter842"></div>
    </div>
</div>
<script>
    function showSubLandCategories(catId){
        $("#landCatList_" + catId).show();
        $("#subLandCategoriesButton_" + catId).attr('onclick', 'hideSubLandCategories(' + catId + ')');
        $("#subLandCategoriesButton_" + catId).addClass('sub_land_button_hide');
        $("#subLandCategoriesButton_" + catId).removeClass('sub_land_button_show');
    }
    function hideSubLandCategories(catId){
        $("#landCatList_" + catId).hide();
        $("#subLandCategoriesButton_" + catId).attr('onclick', 'showSubLandCategories(' + catId + ')');
        $("#subLandCategoriesButton_" + catId).addClass('sub_land_button_show');
        $("#subLandCategoriesButton_" + catId).removeClass('sub_land_button_hide');
    }
    $(window).load(function(){
        $(".sub_land_button_hide").click();
        $(".sub_land_categories_list").removeClass('db');
    });
</script>
{/strip}