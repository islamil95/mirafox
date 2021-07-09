import { PortfolioModal } from './portfolio-modal.js';
import { Portfolio, Image } from './sortable-card.js';

export class SortableCardList {
	constructor(el) {
		this.draggedBlock = null;

		this.stageOffset = null;
		this.stageWidth = 0;
		this.stageHeight = 0;
		this.containerWidth = 0;
		this.containerHeight = 0;
		this.containerRowSize = 3;

		this.blockDragLeft = 0;
		this.blockDragTop = 0;
		
		this.stageBlock = $(el);

		this.draggableBlocks = this.stageBlock.find('.draggable-blocks');
		this.placeholders = this.stageBlock.find('.placeholders');
		this.errorBlock = this.stageBlock.find('.portfolio-error');
		
		this.type = this.stageBlock.data('type');
		this.maxCount = this.stageBlock.data('maxCount') || 9;
		this.sortable = (this.stageBlock.data('sortable') == "unsortable" ? false : true);
		
		this.items = [];

		this.additionalData = {};
		let additional = this.stageBlock.find('.data-additional');
		if (additional.length) {
			this.additionalData = JSON.parse(additional.val());
		}

		if(this.type == 'portfolios') {
			this.modal = new PortfolioModal();
			this.modal.onSave = (item) => {
				this.itemSave(this.modal.portfolioItem);

				this.modal.portfolioItem.hasError = false;
				this.modal.portfolioItem.updateErrorVisuals();

				$.each(this.items, (k, vItem) => {
					if (vItem.hasError) {
						return false;
					}

					this.errorBlock.text('');
				});
			};
			this.modal.getImagesHashes = (hash) => {
				return this.getImagesHashes(hash);
			};
			this.modal.getCoverHashes = (hash) => {
				return this.getCoverHashes(hash);
			};
			this.modal.checkCoverHash = (hash) => {
				return this.checkCoverHash(hash);
			};
			this.modal.checkImageHash = (hash) => {
				return this.checkImageHash(hash);
			};
			this.modal.checkVideoUrl = (url, pos) => {
				return this.checkVideoUrl(url, pos);
			};
			this.loadItems(window.portfolios);
		}
		
		this.stageBlock.find('.create-block').on('click', () => {
			this.createItemDialogue();
		});

		$(document).mousemove((e) => {
			return this.dragMove(e);
		});

		$(document).mouseup((e) => {
			this.dragStop(e);
		});

		$(window).on('resize', () => {
			this.updateOffsets();
			this.updateAllBlockCoordinates();
		});
	}

	updateAddBlock() {
		if(this.items.length >= this.maxCount) {
			this.stageBlock.find('.create-block').hide();
			return 0;
		} else {
			this.stageBlock.find('.create-block').show();
			return 1;
		}
	}

	updatePlaceholders() {
		let addBlockCount = this.updateAddBlock();
		let placeholders = '';
		for (let i = 0, l = this.items.length + addBlockCount; i < l; i++) {
			placeholders += '<div class="placeholder"><div class="image"></div></div>';
		}
		this.placeholders.html(placeholders);
	}

	createItemDialogue() {
		let item = this.createItem();
		if(this.type == 'portfolios') {
			this.modal.edit(item, this.additionalData);
		} else {
			item.onLoad = () => {
				this.itemSave(item);
			}
			item.fileUploader.upload();
		}
	}

	editItem(item) {
		if(this.type == 'portfolios') {
			this.modal.edit(item, this.additionalData);
		} else {
			this.uploaderBlock.trigger('click');
		}
	}

	loadItems(items) {
		this.items = [];
		this.draggableBlocks.find('.item').remove();
		$.each(items, (k, v) => {
			let item = this.createItem(v);
			this.items.push(item);
			this.draggableBlocks.append(item.html);
		});
		this.updatePlaceholders();
		this.updateOffsets();
		this.updateAllBlockCoordinates();
	}

	itemSave(item) {
		item.updateImage();
		if(this.items.indexOf(item) != -1) {
			return;
		}
		this.items.push(item);
		this.draggableBlocks.append(item.html);
		this.updatePlaceholders();
		this.updateOffsets();
		this.updateAllBlockCoordinates();
	}

	createItem(data) {
		let item = null;
		if(this.type == 'portfolios') {
			item = new Portfolio(data);
		} else if(this.type == 'images') {
			item = new Image(data);
		}
		
		item.onLoadStart = () => {
			this.stageBlock.find('.portfolio-error').html('');
		}

		item.onError = (text) => {
			this.stageBlock.find('.portfolio-error').html(text);
		}

		item.onDragStart = (e) => {
			this.dragStart(e, item);
		}

		item.onEdit = () => {
			this.editItem(item);
		}

		item.onDelete = () => {
			this.deleteItem(item);
			if(this.type == 'images') {
				this.onDelete(item);
			}
		}

		item.onChangeState = () => {
			this.onChangeState();
		}

		item.onSuccess = () => {
			this.onSuccess(item);
		}

		return item;
	}

	deleteItem(item) {
		item.html.remove();
		this.items.splice(this.items.indexOf(item), 1);
		this.stageBlock.find('.placeholder:last-child').remove();
		this.updatePlaceholders();
		this.updateOffsets();
		this.updateAllBlockCoordinates();
	}

	updateOffsets() {
		this.stageOffset = this.stageBlock.offset();
		this.stageWidth = this.stageBlock.outerWidth();
		this.stageHeight = this.stageBlock.outerHeight();
		let container = this.stageBlock.find('.placeholder');
		this.containerWidth = container.outerWidth();
		this.containerHeight = container.outerHeight();
		let colsCount = -1;
		if(this.sortable == false) {
			let colsCount = 1;
		} else {
			for (let i = this.stageWidth + 30; i > 0; i -= (190 + 15)) {
				colsCount++;
			}
		}
		this.stageBlock.attr('data-cols', colsCount);
		this.containerRowSize = colsCount;
	}

	getPositionByCoords(x, y) {
		let row = Math.round(y / this.containerHeight);
		let col = Math.round(x / (this.stageWidth / this.containerRowSize));
		let pos = row * this.containerRowSize + col;
		if(pos >= this.items.length) {
			pos = this.items.length - 1;
		}
		return pos;
	}

	updateAllBlockCoordinates() {
		$.each(this.items, (k, v) => {
			this.updateBlockCoordinates(v.html, k);
		});
		this.updateBlockCoordinates(this.stageBlock.find('.create-block'), this.items.length);
	}

	updateBlockCoordinates(el, newPosition) {
		let container = this.stageBlock.find('.placeholder:nth-child(' + (newPosition + 1) + ')');
		if(container.length < 1) {
			return;
		}
		let offset = container.offset();

		let offsetTop = offset.top - this.stageOffset.top;
		let offsetLeft = offset.left - this.stageOffset.left;

		el.css({'top': offsetTop, 'left': offsetLeft});
	}

	dragStart(e, item) {
		if(!this.sortable || e.which != 1) {
			return;
		}
		this.updateOffsets();
		item.html.addClass('moved');
		let offset = item.html.offset();
		this.blockDragLeft = e.pageX - offset.left;
		this.blockDragTop = e.pageY - offset.top;
		this.draggedBlock = item;
		return false;
	}

	dragStop(e) {
		if(!this.draggedBlock) {
			return;
		}
		this.draggedBlock.html.removeClass('moved');
		this.updateAllBlockCoordinates();
		this.draggedBlock = null;
	}

	dragMove(e) {
		if(!this.draggedBlock) {
			return;
		}
		let top = e.pageY - this.stageOffset.top - this.blockDragTop;
		let left = e.pageX - this.stageOffset.left - this.blockDragLeft;
		let maxWidth = this.stageWidth - this.containerWidth;
		let maxHeight = this.stageHeight - this.containerHeight;
		if(left < 0) {
			left = 0;
		} else if (left > maxWidth) {
			left = maxWidth;
		}
		if(top < 0) {
			top = 0;
		} else if (top > maxHeight) {
			top = maxHeight;
		}
		let oldPos = this.items.indexOf(this.draggedBlock);
		let newPos = this.getPositionByCoords(left, top);
		if(newPos != oldPos) {
			this.moveElement(this.items, oldPos, newPos);
		};
		this.updateAllBlockCoordinates();
		this.draggedBlock.html.css({'left': left + 'px', 'top': top + 'px'});
		return false;
	}

	moveElement(arr, old_index, new_index) {
		if (new_index >= arr.length) {
			var k = new_index - arr.length + 1;
			while (k--) {
				arr.push(undefined);
			}
		}
		arr.splice(new_index, 0, arr.splice(old_index, 1)[0]);
	};

	getData() {
		let data = [];
		$.each(this.items, (k, v) => {
			data.push(v.getData());
		});
		return data;
	}

	isReady() {
		let ready = true;
		$.each(this.items, (k, v) => {
			if(v.uploadableBase64) {
				ready = false;
				return false;
			}
		});
		return ready;
	}

	applyErrors(errors) {
		let itemListErrors = {};

		$.each(errors, (k, v) => {
			itemListErrors[parseInt(v.position)] = v.errors;
		});
		$.each(this.items, (k, v) => {
			let itemErrors = [];
			if(k in itemListErrors) {
				itemErrors = itemListErrors[k];
			}
			v.applyErrors(itemErrors);
		});
	}

	checkCoverHash(hash) {
		let hashes = this.getCoverHashes();
		return ($.inArray(hash, hashes) == -1);
	}

	checkImageHash(hash) {
		let hashes = this.getImagesHashes();
		return ($.inArray(hash, hashes) == -1);
	}

	checkVideoUrl(url, pos) {
		let urls = this.getVideoUrls(pos);
		return ($.inArray(url, urls) == -1);
	}

	getCoverHashes() {
		let hashes = [];
		let activeWork = this.modal.portfolioItem;

		$.each(this.items, (k, v) => {
			if(v == activeWork) {
				return true;
			}
			if (v.data.cover.hash) {
				hashes.push(v.data.cover.hash);
			}
		});
		return hashes;
	}

	getImagesHashes() {
		let hashes = [];
		let activeWork = this.modal.portfolioItem;
		let modalImages = this.modal.portfolio ? this.modal.portfolio.images : [];
		
		$.each(this.items, (k, v) => {
			if(v == activeWork) {
				return true;
			}
			$.each(v.data.images, (k2, v2) => {
				hashes.push(v2.hash);
			});
		});
		$.each(modalImages, (k2, v2) => {
			hashes.push(v2.hash);
		});
		return hashes;
	}

	getVideoUrls(pos) {
		let urls = [];
		let activeWork = this.modal.portfolioItem;
		let modalVideos = this.modal.portfolio.videos;

		$.each(this.items, (k, v) => {
			if(v == activeWork) {
				return true;
			}
			$.each(v.data.videos, (k2, v2) => {
				urls.push(v2);
			});
		});
		
		$.each(modalVideos, (k, v) => {
			if(k == pos) {
				return true;
			}
			urls.push(v);
		});

		if(window.ordersPortfolioVideos) {
			$.each(window.ordersPortfolioVideos, (k, v) => {
				urls.push(v);
			});
		}
		return urls;
	}

}
