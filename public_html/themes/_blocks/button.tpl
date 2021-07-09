{strip}
    <div style="display: inline-block; {if $leftMargin}{$leftMargin}{/if} {if $textAlign}{$textAlign}{/if}">
        <a href="{$buttonUrl}" style="padding: 0 25px;
            display: inline-block;
            text-decoration: none;
            font-family:sans-serif;
            font-size:20px;
            font-weight:600;
            line-height:50px;
            text-align:center;
            outline: none;
            border: none;
            border-radius: 4px;
            {if $buttonColor == \Letter\UIComponent\Button\Style\ButtonColors::GREEN}
                color:#ffffff !important;
                background: #87b948;
                background: -moz-linear-gradient(top, #87b948 0%, #5ca042 100%, #5ca042 100%);
                background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#87b948), color-stop(100%,#5ca042), color-stop(100%,#5ca042));
                background: -o-linear-gradient(top, #87b948 0%,#5ca042 100%,#5ca042 100%);
                background: -ms-linear-gradient(top, #87b948 0%,#5ca042 100%,#5ca042 100%);
                background: linear-gradient(to bottom, #87b948 0%,#5ca042 100%,#5ca042 100%);
                filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#87b948', endColorstr='#5ca042',GradientType=0 );
            {elseif $buttonColor == \Letter\UIComponent\Button\Style\ButtonColors::WHITE}
                color:rgb(55,55,55) !important;
                background: #f8f8f8;
                background: -moz-linear-gradient(top, #f8f8f8 57%, #e9e9e9 93%);
                background: -webkit-linear-gradient(top, #f8f8f8 57%,#e9e9e9 93%);
                background: -o-linear-gradient(top, #f8f8f8 57%,#e9e9e9 93%);
                background: -ms-linear-gradient(top, #f8f8f8 57%,#e9e9e9 93%);
                background: linear-gradient(to bottom, #f8f8f8 57%,#e9e9e9 93%);
                filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#f8f8f8', endColorstr='#e9e9e9',GradientType=0 );
            {/if}
            {$width}">
            {$buttonName}
        </a>
    </div>
{/strip}