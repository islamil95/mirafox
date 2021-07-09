
var KworkWidgetModule = (function (){
    
    var _kworksCount = 0;
    var _curKworkIndex = 0;
    var SWITCH_PERIOD = 5000;    
    var _width = 0;
    var _height = 0;
    
    var kworkNumSelector = 'kwork_num_';
    
    var _setSwitchKworks = function(){
        setInterval(_switchKwork, SWITCH_PERIOD);
    };
    
    var _switchKwork = function(){
        var index = (_curKworkIndex + 1) % _kworksCount;        
        document.getElementById(kworkNumSelector + _curKworkIndex).style.display = 'none';
        document.getElementById(kworkNumSelector + index).style.display = 'block';
        _curKworkIndex = index;
    };
    
    var _resize = function(){
        parent.window.postMessage({width:_width, height:_height}, '*');        
    };
    
    return {
        init: function(options){
            _kworksCount = options.kworkCount;
            _width = options.width;
            _height = document.getElementById(kworkNumSelector + '0').offsetHeight;
            
            if(_kworksCount > 1){
                _setSwitchKworks();
            }
            _resize();            
        },
        imagePreload: function(url){            
            if (typeof document.body == "undefined") return;
            try {
                var div = document.createElement("div");
                var s = div.style;
                s.position = "absolute";
                s.top = s.left = 0;
                s.visibility = "hidden";
                document.body.appendChild(div);
                div.innerHTML = "<img src=\"" + url + "\" />";
            }
            catch(e) {
           // Error. Do nothing.
           }
        }
    };
})();