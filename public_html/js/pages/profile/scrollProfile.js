let ScrollProfile = function () {
	let profileMenu = $('.profile-tab_block');
	let profileMenuTab = profileMenu.find('a');
	let profileTabAnchor = $('.profile-tab-link');

	this.lastActiveTab = "";

	this.init = () => {
		this.scroll();
		this.clickTab();
	};

	this.clickTab = () => {
		profileMenuTab.on('click', (e) => {
			e.preventDefault();

			scrollToAnchor($(e.target).attr('href'));
		});
	};

	this.scroll = () => {
		let self = this;

		$(window).scroll((e) => {
			let fromTop = $(e.target).scrollTop() + profileMenu.outerHeight() + $('.header_top').outerHeight();

			let scrollTabs = profileMenuTab.map((key, element) => {
				let scrollItem = profileTabAnchor.filter("[name='"+ $(element).attr("href") +"']");
				if (scrollItem.offset().top < fromTop)
					return scrollItem;
			});

			let nameTab = scrollTabs.last()[0].attr('name');

			if (self.lastActiveTab !== nameTab) {
				self.lastActiveTab = nameTab;
				self.activeTab(profileMenuTab.filter("[href='" + nameTab + "']"));
			}
		});
	};

	this.activeTab = ($tab) => {
		profileMenuTab.removeClass('active');
		$tab.addClass('active');
	}
};

let scrollProfile = new ScrollProfile();
scrollProfile.init();
