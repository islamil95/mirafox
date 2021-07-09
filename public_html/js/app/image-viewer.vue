<template>
	<div v-if="viewedImage >= 0" class="t-image-viewer">
		<transition name="fade" @after-leave="viewedImage = -1">
			<div v-if="imageShowed" class="iv-bg" @click.self="closeViewer">
				<div class="iv-close" @click="closeViewer"></div>
				<div v-if="images.length > 1" class="iv-control left" @click="prevImage"></div>
				<div v-if="images.length > 1" class="iv-control right" @click="nextImage"></div>
				<i-spinner :class="{'hidden': (viewedImage < 0 || loadedImages.indexOf(images[viewedImage]) > -1)}" />
				<div class="iv-window">
					<img v-for="(v, k) in images" :key="k" :style="{'max-width': maxImageWidth + 'px', 'max-height': maxImageHeight + 'px', 'display': (k == viewedImage ? 'block' : 'none'), 'opacity': (loadedImages.indexOf(v) > -1 ? 1 : 0)}" :src="(k == viewedImage || loadedImages.indexOf(v) > -1 ? v : 'data:')" @click="nextImage" @load="imageLoad(v)" />
				</div>
			</div>
		</transition>
	</div>
</template>

<script>
window.updateAttachedImages = function() {
	$('.attached-images-area .js-message-body').each((k, v) => {
		let el = $(v);
		if (el.data('imagesHandled')) {
			return true;
		}
		let html = el.html();

		let messageBlock = el.closest('.ms-message');
		let filesBlock = messageBlock.find('.ms-files');
		let files = filesBlock.data('jsonFiles') || [];
		let newHtml = window.handleAttachedImages(html, false, files);
		if (html != newHtml) {
			if (!el.data('rawHtml')) {
				el.data('rawHtml', html);
			}
			el.html(newHtml);
		}
		el.data('imagesHandled', true);
	});
}

window.handleAttachedImages = function(text, forEdit = false, filesData) {
	text = text.replace(/\[attached-img([^]+?)\]/gi, (p0, p1, p2) => {
		let re = new RegExp('([^\\s]+?)=(?:&quot;|")([^]+?)(?:&quot;|")', 'gi');
		let props = {};
		let match;
		while (match = re.exec(p1)) {
			props[match[1]] = match [2];
		}
		if (!('id' in props)) {
			return '';
		}
		let file = null;
		let file_id = null;
		_.forEach(filesData, (v, k) => {
			file_id = v.FID || v.file_id || v.id;
			if (file_id == props.id) {
				file = v;
				return false;
			}
		});
		if (!file || file.status == 'deleted') {
			return '';
		}
		let fileThumb = filesData;
		if (forEdit) {
			return '<img data-id="' + props.id + '" class="attached-img" src="' + file.miniature_url + '" />';
		}
		let displayWidth = 430;
		let style = '';
		let width = file.original_width;
		let height = file.original_height;
		if ('size' in props) {
			let size = props.size.split('x');
			let height = 0;
			if (size.length >= 2) {
				width = parseInt(size[0]);
				height = parseInt(size[1]);
			}
		}
		if (width < 1) {
			width = 200;
		}
		if (height < 1) {
			height = 200;
		}
		if (width < 430) {
			displayWidth = width;
		}
		let ratio = width / displayWidth;
		let displayHeight = height / ratio;
		if (displayHeight > 430) {
			displayHeight = 430;
			ratio = height / displayHeight;
			displayWidth = width / ratio;
		}
		style = ' style= "padding-top: calc(' + height + ' / ' + width + ' * 100%);"';

		let filePath = file.file_path || '';
		if (!filePath) {
			filePath = window.uploadedFilesUrl + '/' + file.path + '/' + file.name;
		}

		return '<div class="attached-image-img" data-src="' + filePath + '" data-width="' + width + '" style="width: ' + displayWidth + 'px;"><div' + style + '><img src="' + file.miniature_url + '" /></div></div>';
	});
	if (forEdit) {
		text = text.replace(/\r?\n/g, '<br />');
	}
	return text;
}

import ISpinner from 'appJs/ISpinner.vue';

export default {
	components: {
		'i-spinner': ISpinner,
	},

	data () {
		return {
			images: () => [],
			viewedImage: -1,
			imageShowed: false,
			windowWidth: 0,
			windowHeight: 0,
			scrollbarWidth: 0,
			maxImageWidth: 0,
			maxImageHeight: 0,
			loadedImages: [],
		}
	},

	watch: {
		viewedImage: function() {
			this.updateOverflow();
		},
	},

	mounted() {
		$('.attached-images-area').on('click', '.attached-image-img', (e) => {
			if (!this.checkImageSizeable(e.target)) {
				return;
			}

			let el = $(e.target);
			if (!el.hasClass('attached-image-img')) {
				el = el.closest('.attached-image-img');
			}
			let currentSrcNum = 0;
			let currentSrc = el.data('src');
			if (!currentSrc) {
				return;
			}
			
			let sources = [];

			if(el.data('singleView')) {
				sources.push(el.data('src'));
			} else {
				let container = el.closest('.vi-container');
				if (container.length < 1) {
					container = $(document);
				}
				container.find('.attached-image-img').each((k, v) => {
					let src = $(v).data('src');
					if (src) {
						if (currentSrc == src) {
							currentSrcNum = sources.length;
						}
						sources.push(src);
					}
				});
			}

			window.app.$refs.imageViewer.show(sources, currentSrcNum);
		});

		$('.attached-images-area').on('mouseover', '.attached-image-img', (e) => {
			this.checkImageSizeable(e.target);
		});

		$(document).on('keydown', (e) => {
			if (e.key == 'Escape') {
				this.closeViewer();
			}
		});

		$(window).on('resize', () => {
			this.updateWindowSize();
		});

		this.updateWindowSize();
	},

	methods: {
		imageLoad(v) {
			this.loadedImages.push(v);
		},
		
		nextImage() {
			if (this.images.length <= 1) {
				this.closeViewer();
				return;
			}
			this.viewedImage++;
			if (this.viewedImage >= this.images.length) {
				this.viewedImage = 0;
			}
		},

		prevImage() {
			this.viewedImage--;
			if (this.viewedImage < 0) {
				this.viewedImage = this.images.length - 1;
			}
		},

		closeViewer() {
			this.imageShowed = false;
		},

		updateWindowSize() {
			this.updateScrollbarWidth();
			let windowWidth = $(window).width();
			this.maxImageWidth = windowWidth - (windowWidth >= 1100 ? 160 : 60) - this.scrollbarWidth;
			this.maxImageHeight = $(window).height();
		},

		checkImageSizeable(target) {
			let el = $(target);
			if (!el.hasClass('attached-image-img')) {
				el = el.closest('.attached-image-img');
			}
			let img = el.find('img');
			let zoomAlways = el.data('zoomAlways');
			if (zoomAlways) {
				return true;
			}
			let width = el.data('width');
			if (img.width() < width - 2) {
				el.addClass('sizeable');
				return true;
			}
			el.removeClass('sizeable');
			return false;
		},

		show: function(sources, num) {
			this.images = sources;
			this.loadedImages = [];
			this.viewedImage = num;
			this.$nextTick(() => {
				this.imageShowed = true;
			});
		},

		updateOverflow: function() {
			if (this.viewedImage >= 0) {
				this.updateScrollbarWidth();
				$('body').css({'overflow-y': 'hidden'});
				$('body').css({'padding-right': this.scrollbarWidth + 'px'});
			} else {
				$('body').css({'overflow-y': ''});
				$('body').css({'padding-right': ''});
			}
		},

		updateScrollbarWidth: function() {
			let div = $('<div style="width:50px; height:50px; overflow:hidden; position:absolute; top:-200px; left:-200px;"><div style="height:100px;"></div>');
			$('body').append(div);
			let w1 = $('div', div).innerWidth();
			div.css('overflow-y', 'scroll');
			let w2 = $('div', div).innerWidth();
			$(div).remove();
			this.scrollbarWidth = (w1 - w2);
		},
	}
}
</script>
