export default class TrackInitActions {
	static doInitAction(item) {
		if (!item.initAction) {
			return;
		}
		switch (item.initAction) {
			case 'initPortfolio':
				this.initPortfolio(item);
				break;
		}
	}

	static initPortfolio(item) {
		window.portfolios = [];
		window.portfolioType = item.kworkPortfolioType;
		window.ordersPortfolioCoversHashes = item.portfolioCoversHashes;
		window.ordersPortfolioImagesHashes = item.portfolioImagesHashes;
		window.ordersPortfolioVideos = item.portfolioVideos;
		if (item.portfolioJson) {
			window.portfolios = [item.portfolioJson];
		}
		window.initPortfolioList();
	}
}