import TrackManager from "../TrackManager";

export default class NewTracksManager {
	constructor(trackManager) {
		this.trackManager = trackManager;
		this.tracks = this.trackManager.tracks;
		this.trackIdWrapper = '#track-id-'
	}

	/**
	 * Метод для вызова методов этого класса
	 * @param details
	 */
	handleTracks(details) {
		try {
			this[details.action](details.data)
		} catch (err) {
			console.warn(err);
		}

	}

	/**
	 * Получает с бэкенда trackToAdd через метод getnewtrack в track.js
	 * Превращает их в DomElement
	 * и отправляет на обработку
	 * @param tracksToAdd
	 */
	insertNewTracks(tracksToAdd) {
		let trackInHTMLs = [];
		for (let id in tracksToAdd) {
			let track = tracksToAdd[id];
			track = TrackManager.parseHTML(track)[0];
			trackInHTMLs.push(track);
		}
		this.processHTMLTracks(trackInHTMLs);
	}

	/**
	 *
 	 * @param trackHTML
	 */
	insertNewMessage(trackHTML) {
		let key = trackHTML.data('trackId');
		let tracksToAdd = {};
		tracksToAdd[key] = trackHTML[0].outerHTML;
		this.insertNewTracks(tracksToAdd);
	}

	/**
	 * В зависимости от наличии в списке треков, либо добавляет новый, либо заменяет.
	 * @param object
	 * @param trackNode
	 */
	insertIntoHTML(object, trackNode) {
		let exists = this.tracks.filter(item => object.MID == item.MID);
		if (exists.length) {
			this.replaceHTML(object, trackNode);
		} else {
			this.insertHTML(object, trackNode);
			this.trackManager.tracks.push(object);
		}

	}

	/**
	 * Функция замены DomElement по id
	 * @param object
	 * @param node
	 */
	replaceHTML(object, node) {
		$(`${this.trackIdWrapper + object.MID}`).replaceWith(node);
	}

	/**
	 * В завивисмости от наличии контейнера для сообщений
	 * либо добавляет новый контейнер с сообщением внутри
	 * либо просто добавляет в последний контейнре для сообщений этого пользователя
	 * @param object
	 * @param node
	 */
	insertHTML(object, node) {
		let nodes = this.trackManager.wrapper.querySelectorAll(`.track--item__user[data-user_id="${object.user_id}"]`);
		let last = nodes[nodes.length - 1];
		let unreadLine = this.trackManager.getUnreadLine();
		let lastTrack = this.trackManager.getLastTrack();
		if (!this.trackManager.wrapper.querySelector(`${this.trackIdWrapper + object.MID}`)) {
			if (lastTrack.user_id == object.user_id && object.type == 'text' && lastTrack.type == 'text') {
				last.append(node);
				if(!this.trackManager.hasUnreadLine && USER_ID !== object.user_id){
					last.insertBefore(unreadLine,node);
					this.trackManager.hasUnreadLine = true;
				}
			} else {
				let userTrackWrapper = this.trackManager.getUserWrapperTemplate(object.user_id);
				userTrackWrapper = TrackManager.parseHTML(userTrackWrapper)[0];
				if(!this.trackManager.hasUnreadLine && USER_ID !== object.user_id){
					userTrackWrapper.append(unreadLine);
					this.trackManager.hasUnreadLine = true;
				}
				userTrackWrapper.append(node);
				this.trackManager.wrapper.append(userTrackWrapper);
			}
		}
	}

	/**
	 * Собирает в объект из HTML данные для последующей обработки
	 * @param tracks
	 */
	processHTMLTracks(tracks) {
		tracks.forEach(track => {
			let object = {
				MID: parseInt(track.getAttribute('data-track-id')),
				user_id: track.getAttribute('data-user-id'),
				type: track.getAttribute('data-type')
			};

			this.insertIntoHTML(object, track);
		});
	}
}