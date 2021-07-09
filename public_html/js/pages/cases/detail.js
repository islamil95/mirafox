function CasesModule() {
	this._cases = [];
	this._activeCase = {};
	this._loading = false;
	this._detail = 0;
	this._type = "mobile";
}

CasesModule.prototype._loadNewCase = function () {
	if (this._loading) {
		return;
	}

	var params = {
		detail: this._detail,
		offset: this._cases.length - 1
	};

	this._loading = true;
	var that = this;
	$.post("/cases/next", params, function (responseData) {
		if (responseData.success !== true) {
			return;
		}
		var response = responseData.data;
		if (response.done) {
			return;
		}

		var caseItem = that._addNewCase(response.id, $(response.html));
		$(".cases-list").append(caseItem.jBlock);
		that._calcHeight(caseItem);
		that._loading = false;

		if (that._type === "desktop") {
			that._setListHeight();
		}
	}, "json");
};


CasesModule.prototype._setListHeight = function () {
	var lastCase = this._cases[this._cases.length - 1];
	var footerHeight = 0;
	if (this._type === "desktop") {
		footerHeight = 250;
	}

	$(".cases").height(lastCase.height + lastCase.offset + footerHeight);
};

CasesModule.prototype._calcHeight = function (caseItem) {
	var that = this;
	var $images = caseItem.jBlock.find(".cases-desc").find("img");

	caseItem.height = caseItem.jBlock.height();

	$images.each(function () {
		caseItem.unloadedImages++;
		$("<img>")
		.attr("src", $(this).attr("src"))
		.on("load", function () {
			caseItem.unloadedImages--;
			if (!caseItem.unloadedImages) {
				that._setHeight(caseItem);
				if (caseItem.position < that._cases.length - 1) {
					for (var i = caseItem.position + 1; i < that._cases.length; i++) {
						that._cases[i].offset = that._calcCaseOffset(i);
					}
				}
				if (that._type === "desktop") {
					that._setListHeight();
				}
			}
		});
	});
};

CasesModule.prototype._setHeight = function (caseItem) {
	var extHeight = 0;
	if (typeof this._options !== "undefined") {
		extHeight = this._options.OFFSET_FROM_TOP ? this._options.OFFSET_FROM_TOP : 0;
	}

	caseItem.height = caseItem.jBlock.height() + extHeight;
};

CasesModule.prototype._addNewCase = function (id, $case) {
	var caseItem = {
		jBlock: $case,
		id: id,
		height: 0,
		unloadedImages: 0,
		offset: this._calcCaseOffset(this._cases.length),
		position: this._cases.length
	};

	caseItem.jBlock.data("obj", caseItem);
	caseItem.jBlock.css({"z-index": "-" + this._cases.length - 1});
	this._cases.push(caseItem);

	return caseItem;
};

CasesModule.prototype._calcCaseOffset = function (casePosition) {
	var offset = 0;
	var headerHeightOffset = 0;
	if (this._type === "desktop") {
		headerHeightOffset = this._options.OFFSET_FROM_TOP;
	}

	for (var i = 0; i < casePosition; i++) {
		offset += this._cases[i].height + headerHeightOffset;
	}

	return offset;
};

CasesModule.prototype.addFirstCase = function (id, $case) {
	this._detail = id;

	var caseItem = this._addNewCase(id, $case);
	this._calcHeight(caseItem);
	this._setActive(caseItem);
	this._loadNewCase();
};

CasesModule.prototype._setActive = function (caseItem) {
	if (typeof caseItem === "object") {
		this._activeCase = caseItem;
	}
};


function CasesMobileModule() {
	CasesModule.apply(this, arguments);

	var that = this;
	$(document).on("scroll", function () {
		that._onScroll();
	});
}

CasesMobileModule.prototype = Object.create(CasesModule.prototype);
CasesMobileModule.prototype.constructor = CasesMobileModule;

CasesMobileModule.prototype._onScroll = function () {
	var currentScroll = $(document).scrollTop();

	var caseNumber = this._activeCase.position;
	if (currentScroll < this._activeCase.offset && caseNumber) {
		this._setActive(this._cases[caseNumber - 1]);
	} else if (currentScroll > this._activeCase.offset + this._activeCase.height) {
		this._setActive(this._cases[caseNumber + 1]);
	}
	if (this._activeCase.position + 1 === this._cases.length) {
		this._loadNewCase();
	}
};

var $firstCase = $(".cases__item_active");
var mobileModule = new CasesMobileModule();
mobileModule.addFirstCase($firstCase.data("id"), $firstCase);