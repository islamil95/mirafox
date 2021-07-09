export default {
	created() {
		let ruDomain = window.ruDomain || '';
		let enDomain = window.enDomain || '';
		
		let paddedCharacters = /(?:[.,\'!$%^*;:{}=_`~()&"-])/;
		let tsUrlPattern = /\[url=([^"]+?)\]([^]+?)\[\/url\]/;
		let autoUrlPattern = /(^|[^="']\s*)((?:https?|ftp):\/\/[^\s<\[\]]+)/;

		this.tsUrlRegexp = new RegExp(tsUrlPattern.source, 'gi');
		this.userUrlRegexp = new RegExp('\\[url=(https?:\\/\\/(?:(?:www|dev|d[0-9]+)\\.)?(?:' + ruDomain.replace('.', '\\.') + '|' + enDomain.replace('.', '\\.') + ')\\/[^"]+?)\\]([^]+?)\\[\\/url\\]', 'gi');
		this.autoUrlRegexp = new RegExp(autoUrlPattern.source, 'gi');
		this.autoUrlCropRegexp = new RegExp(`${paddedCharacters.source}$`, 'i');
	},

	methods: {
		formatText(rawMessage, args = {}) {
			let parseBBcode = args.bbcode || false;
			let checkHrefSrcRegExp = new RegExp('(href|src)=(\'|\\"|&quot;|&amp;quot;)', 'gi');
			let text = rawMessage || '';
			let isHtml = (checkHrefSrcRegExp.test(text) === true);

			if (parseBBcode) {
				// Обработка тега [url] для техподдержки
				text = text.replace(this.tsUrlRegexp, (match, p1, p2) => {
					return `<noindex><a href="${p1}" target="_blank" rel="nofollow">${p2}</a></noindex>`;
				});
				text = text.replace(/\[b\]([^]+?)\[\/b\]/gi, '<b>$1</b>');
				text = text.replace(/\[size=([0-9]+)\]([^"]+?)\[\/size\]/gi, '<span style="font-size: $1px">$2</span>');
				text = text.replace(/\[color=([a-z]+)\]([^"]+?)\[\/color\]/gi, '<span style="color: $1">$2</span>');
				text = text.replace(/\[img\]([^"]+?)\[\/img\]/gi, '<img src="$1" alt="">');
			} else {
				// Обработка тега [url] для пользователя (ограниченная)
				text = text.replace(this.userUrlRegexp, (match, p1, p2) => {
					return `<a href="${p1}" target="_blank">${p2}</a>`;
				});
			}
			text = text.replace(/\r\n/g, '\n');
			if (window.isChat && text.length) {
				text = '<p>' + text.replace(/[\n]{2,}/g, '</p><p>') + '</p>';
			}
			text = text.replace(/\n/g, '<br />');
			// Находим и создаём автоссылки (без тегов)
			if (!isHtml) {
				text = text.replace(this.autoUrlRegexp, (match, p1, p2) => {
					let paddedPart = '';
					let decodedUrl = he.decode(p2);
					decodedUrl = decodedUrl.replace(this.autoUrlCropRegexp, (match) => {
						if (match) {
							paddedPart = he.encode(match);
						}
						return '';
					});
					let rawUrl = decodedUrl;
					try {
						rawUrl = decodeURI(rawUrl);
					} catch(e) {}
					let encodedUrl = he.encode(rawUrl);
					let textUrl = he.encode(rawUrl);

					return `${p1}<a rel="nofollow" target="_blank" class="shortened-url" href="${encodedUrl}">${textUrl}</a>${(paddedPart ? paddedPart : '')}`;
				});
			}
			return text;
		},
	}
}