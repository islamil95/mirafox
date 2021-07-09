import NewTracksManager from "./modules/NewTracksManager";

export default class TrackManager {
	constructor() {
		this.tracks = [];
		this.newTracksManager = new NewTracksManager(this);
		this.wrapper = document.querySelector("#tracks-wrapper");
		this.trackIdPrefix = "#track-id-";
		this.hasUnreadLine = this.wrapper.querySelectorAll('.track--item__unread').length ? true : false;
	}

	/**
	 * Инициализация класса
	 */
	init() {
		this.listenForEvents();
	}

	/**
	 * возвращает Элемент полоски "новые сообщения"
	 * @returns {Element}
	 */
	getUnreadLine() {
		return TrackManager.parseHTML(`<span class="track--item__unread"><span>Новые сообщения</span></span>`)[0];
	}

	/**
	 * Парсит строку в Dom Element
	 * @param str
	 * @returns {HTMLCollection}
	 */
	static parseHTML(str) {
		let tmp = document.implementation.createHTMLDocument();
		tmp.body.innerHTML = str;
		return tmp.body.children;
	}

	/**
	 *  функция для вызова методов текущего класса
	 * @param details
	 */
	handleActions(details) {
		try {
			this[details.action](details.data)
		} catch (err) {
			console.warn(err);
		}

	}

	/**
	 * Тут регистрируются Event Listener-ы
	 */
	listenForEvents() {
		document.addEventListener('get-new-tracks', evt => {
			this.newTracksManager.handleTracks(evt.detail)
		});
		document.addEventListener('track-manager-handle', evt => {
			this.handleActions(evt.detail)
		})
	}

	/**
	 * Удаляет сообщение или контейнер с сообщением
	 * @param data
	 */
	removeTrack(data) {
		let trackId = parseInt(data.trackId);
		let item = this.wrapper.querySelector(this.trackIdPrefix + trackId);
		let parent = item.closest('.track--item__user');
		if (parent.childElementCount == 1) {
			parent.remove();
		} else {
			item.remove();
		}
		this.tracks = this.tracks.filter(item => item.MID !== trackId);
	}

	/**
	 * Удаляет полоску новые сообщение после прочтения
	 */
	removeUnreadLines() {
		this.wrapper.querySelectorAll('.track--item__unread').forEach(item => item.remove());
		this.hasUnreadLine = false;
	}

	/**
	 * Возвращает шаблон контейнера для сообщений пользователя
	 * @param userId
	 * @returns {string}
	 */
	getUserWrapperTemplate(userId) {
		return `<div class="track--item__user" data-user_id="${userId}"></div>`
	}

	/**
	 * Возвращает последнее соббщение среди треков this.tracks
	 * @returns {null}
	 */
	getLastTrack() {
		return this.tracks.length ? this.tracks[this.tracks.length - 1] : null;
	}
}