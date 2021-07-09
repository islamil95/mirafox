function createWidget(config) {
	var Util = {
		extendObject: function(a, b) {
			for(prop in b){
				a[prop] = b[prop];
			}
			return a;
		},
		proto: '//' //'https:' == document.location.protocol ? 'https://' : 'http://'
	};
        
        var _selectors = {
            widgetIframe: 'widget_iframe',
            widgetContainer: 'widget_iframe_container',
            widgetId: 'kwork-widget'
        };
        
	var options = Util.extendObject({
		id: 0,
		height: 100,
                width: 100,
                headColor: "FFFFFF",
                headHeight: "30",
                catType:"all",
                count: 0,
                bookmarksType:"",
                myKworksType:"",
                kworkListType:"",
                popularType:"",
                ref:"",
				user_style: ""
                 
	}, config);
		var domain = config.domain || "kwork.ru";
		options.domain = domain + "/api/widget/get?json=no";
		options.widget_url = [Util.proto, options.domain,
            "&cat_type=", options.catType,
            "&count=", options.count,
            "&bookmarks_type=", options.bookmarksType,
            "&my_kworks_type=", options.myKworksType,
            "&kwork_list_type=", options.kworkListType,
            "&popular_type=", options.popularType,
            "&width=", options.width,
            "&ref=", options.ref,
			"&user_style=", options.user_style
        ].join("");

        var receiveMessage = function(e){
            if(typeof(e.data.width) == "undefined" || typeof(e.data.height) == "undefined"){
                return true;
            }
            document.getElementById(_selectors.widgetIframe).style.display = 'none';
            document.getElementById(_selectors.widgetIframe).width = e.data.width; 
            document.getElementById(_selectors.widgetIframe).height = e.data.height; 
            document.getElementById(_selectors.widgetIframe).style.display = 'block';
        };
        window.addEventListener("message", receiveMessage, false);
        
	var Widget = {
		created: false,
		widgetElement: null,
		show: function() {
                    if (this.created)
                            return;
					if (!document.getElementById(_selectors.widgetId))
						return;
                    this.widgetElement = document.createElement('div');
                    this.widgetElement.setAttribute('id', _selectors.widgetContainer);
                    this.widgetElement.innerHTML = ' \
                        <iframe id="'+ _selectors.widgetIframe +'" src="' + options.widget_url + '" width="0" height="0" scrolling="no" frameborder="0"></iframe>';
                    document.getElementById(_selectors.widgetId).innerHTML = this.widgetElement.innerHTML;
                    this.widgetElement.style.display = 'block';
                    this.created = true;
		}
	};

	Widget.show();
}

createWidget(widgetOptions);