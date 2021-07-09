{strip}
    {include file="header.tpl"}

    {Helper::printCssFile("/css/dist/temp-image-test.css"|cdnBaseUrl, "all")}
    {Helper::printCssFile("/css/chosen.css"|cdnBaseUrl, "all")}

    <div id="app">
        <temp-image-test validators-json='{$validators}'>
        </temp-image-test>
    </div>

    {Helper::registerFooterJsFile("/js/dist/temp-image-test.js"|cdnBaseUrl)}
    {Helper::printJsFiles()}

    {include file="footer.tpl"}
{/strip}