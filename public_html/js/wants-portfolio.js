$(function () {
	$('.offer-item__wants-portfolio__filter').chosen({
		disable_search: true,
		width: '100%'
	});
	
	$(".offer-item__wants-portfolio__portfolio-list-collage").each(function (i, elem) {
		var userId = $(elem).data("user");
		var wrapperId = "#portfolio-" + userId;
		var collapseBtn = wrapperId + " .btn_show-more";
		// показать кнопку, если есть класс show
		if($(collapseBtn).hasClass("show")){
			$(collapseBtn).show();
		}
	});

	$(".offer-item__wants-portfolio__button-panel .btn_collapse").on("click", function () {
		WantsPortfolio.collapse(this);
	});

	$(".offer-item__wants-portfolio__button-panel .btn_show-more").on("click", function () {
		WantsPortfolio.more(this);
	});

	// при клике на блоке инициализируем pop-up
	$(".offer-item__wants-portfolio__portfolio-list-collage").on("click", function () {
		var userId = $(this).data("user");
		WantsPortfolio.initCard(userId);
	});
});

var WantsPortfolio = {
	getCatSelect: function(userId){
		var val = $("select[name='portfolio-filter[" + userId + "]']").val();
		if(val === undefined) {
			val = 0;
		}
		return val;
	},	
	
	isLockBtn: function(lock, that) {
		if (lock === true) {
			$(that).addClass('onload').prop('disabled', true);
		} else {
			$(that).removeClass('onload').prop('disabled', false);
		}
	},

	changeCat: function(userId){
		var curCat = this.getCatSelect(userId);
		this.load(userId, curCat, 1, this.getWantLang());
	},

	getNextPage: function(userId){
		var page = $("input[name='curPage[" + userId + "]']").val();
		page++;
		return page;
	},

	savePage: function(userId, page){
		$("input[name='curPage[" + userId + "]']").val(page);
	},

	load: function(userId, category, page, lang, type)
	{
		var wrapperId = "#portfolio-" + userId;
		var portfolioId = wrapperId + " .offer-item__wants-portfolio__portfolio-list-collage";
		var nextBtn = wrapperId + " .btn_show-more";
		var collapseBtn = wrapperId + " .btn_collapse";
		$.ajax( {
			url: "/wants/portfolio",
			type: "POST",
			data: {
				userId: userId,
				category: category,
				page: page,
				lang: lang
			},
			success: function(ret) {
				if(ret.success) {					
					WantsPortfolio.isLockBtn(false, nextBtn);
					
					if(page == 1){
						$(portfolioId).html("");
					}
					$(portfolioId).append(ret.data.portfolioItems);

					if(ret.data.haveNext) {
						$(nextBtn).show();
					}else{
						$(nextBtn).hide();
					}

					if(page > 1){
						$(collapseBtn).show();
					}else{
						$(collapseBtn).hide();
					}
					
					if(page === 1 && !ret.data.haveNext) {
						$('.offer-item__wants-portfolio__button-panel').hide();
					} else {
						$('.offer-item__wants-portfolio__button-panel').show();
					}
					

					WantsPortfolio.savePage(userId, page);
					
					if(type == 'collapse') {
						var destination = $(portfolioId).parents('.offer-item__detail-block').offset().top - $('.header_top').height() - 25;
						$("html:not(:animated),body:not(:animated)").animate({scrollTop: destination}, 300, 'linear');
					}
				}
			}
		} );
	},

	collapse: function (that)
	{
		var userId = $(that).parents('.portfolio-list-collage-wrapper').find(".portfolio-list-collage").data("user");
		this.load(userId, this.getCatSelect(userId), 1, this.getWantLang(), 'collapse');
	},

	more: function (that)
	{		
		var userId = $(that).parents('.portfolio-list-collage-wrapper').find(".portfolio-list-collage").data("user");
		var nextPage = this.getNextPage(userId);
		WantsPortfolio.isLockBtn(true, that);
		this.load(userId, this.getCatSelect(userId), nextPage, this.getWantLang(), 'show');
	},

	initCard: function(userId)
	{
		var cardIds=[];
		$("#portfolio-" + userId + " .portfolio-card-collage").each(function (i, elem) {
			cardIds.push($(elem).data("id"));
		});
		// если 1 элемент прокрутку не делаем
		portfolioCard.setMode('portfolio');
		if(cardIds.length <= 1){
			portfolioCard.setAllIds([]);
		}else{
			portfolioCard.setAllIds(cardIds.join(","));
		}

	},

	getWantLang: function()
	{
		var wantLang = $("input[name='wantLang']").val();
		return wantLang;
	}

};