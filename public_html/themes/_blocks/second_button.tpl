{strip}
    <style>
        .deny-email-button{
            padding: 0 25px;
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
            color:rgb(55,55,55) !important;
            background: #f8f8f8;
            background: -moz-linear-gradient(top, #f8f8f8 57%, #e9e9e9 93%);
            background: -webkit-linear-gradient(top, #f8f8f8 57%,#e9e9e9 93%);
            background: -o-linear-gradient(top, #f8f8f8 57%,#e9e9e9 93%);
            background: -ms-linear-gradient(top, #f8f8f8 57%,#e9e9e9 93%);
            background: linear-gradient(to bottom, #f8f8f8 57%,#e9e9e9 93%);
            filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#f8f8f8', endColorstr='#e9e9e9',GradientType=0 );
        }
    </style>
    <div style="display: inline-block; margin-left: 20px">
        <a href="{$buttonUrl}" class="deny-email-button" style="{$width}">
            {$buttonName}
        </a>
    </div>
{/strip}