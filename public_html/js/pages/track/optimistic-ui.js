/**
 * Оптимистичный интерфейс делаем 4 попытки отправить сообщение, если не удачно, то удаляем сообщение
 */
export class OptimisticUi {
	constructor(xhr, action, sendData, files, submitButton) {
		/**
		 * Счетчик для изменения переуда между попыткаим отправить для массива timerPeriods
		 * @type {number}
		 */
		this.index;
		
		/**
		 * Локальный идентификатор сообщения (чтобы отличать их до получения ид с сервера)
		 * @type {number}
		 */
		this.localId = 0;

		/**
		 * Кнопка отправки сообщения
		 * @type {Object}
		 */	
		this.submitButton = submitButton;

		/**
		 * Сколько отсчитывать времени после неудачной отправки сообщения 1,2,3,4 попыток
		 * @type {Array}
		 */
		this.timerPeriods = [5, 15, 30, 60];
		
		/**
		 * Таймер до первого отображения крутилки (при быстрой отправке не появляется)
		 * @type {number}
		 */		
		this.preloaderTimeout;

		/**
		 * Таймер отсчета  времени до повторной отправки
		 * @type {number}
		 */		
		this.timerTimeout;		
		
		/**
		 * Таймер который срабатывает если сообщение не отправилось через 15 секунд
		 * @type {number}
		 */
		this.errorTimeout;
					
		/**
		 * Ссылка на объект XMLHttpRequest для отправки запроса
		 * @type {XMLHttpRequest}
		 */
		this.xhr = xhr; 
					
		/**
		 * Адрес по которому отправляем запрос
		 * @type {string}
		 */		
		this.action = action;
					
		/**
		 * Отправляемы параметры
		 * @type {SendData}
		 */	
		this.sendData = sendData;
					
		/**
		 * Индификатор сообщения для замены сообщения на то, что пришло с сервера
		 * @type {string}
		 */			
		this.messageKey = this.generateMessageKey();
		
		// Добавляем параметр с сгенерированным ключем		
		this.sendData.append('message_key', this.messageKey);						
					
		/**
		 * Список файлов
		 * @type {Array}
		 */
		this.files = files;
	}
					
	/**
	 * Отображаем сообщение и запускаем процесс повторой отправки
	 */		
	start() {
		if (!window.config.track.isFocusGroupMember) {
			return;
		}
		// Обнуляем таймер
		window.appTracks.$refs.trackList.setTimer(-1);
		// Скрываем временную форму
		MessageFormModule.closeMessageForm();
		// Показываем сообщение
		this.showMessage();
		// Добавляем в очередь на отправку
		this.addToQueue();
		// Отслеживаем событие
		this.setEvents();
		// Запускаем таймер появления крутилки
		this.runPreloader();
		// Зпускаем таймер
		this.planError(0);
	}

	addToQueue() {
		// Добавляем данные в глобальный массив для фоновой отправки, на случай если юзер покинет страницу
		window.bus.$emit('addSendingCount');
		csSendQueue.push({
			time: Math.floor(Date.now() / 1000),
			action: this.action,
			data: this.sendData.data,
		});
		csUpdateSendQueue();
	}

	runPreloader() {
		this.preloaderTimeout = setTimeout(() => {
			this.showPreloader();
		}, 800);
	}
			
	/**
	 * Запускаем таймер по истечению которого считаем что сообщение не смогло отправится
	 * и показываем таймер отсчета.
	 * рекурсивная функция 2 условия выхода из рекурсии:
	 * 1) все попыки отправки иссякли, вызывается функция fail()
	 * 2) вне функции вызвали функцию end()
	 * @param index индекс попытки отправки, индекс массива this.timerPeriods
	 */	
	planError(index) {
		this.index = index;
		// Если таймер через 15 секунд срабатывает значит сообщение не отправлено
		this.errorTimeout = setTimeout(() => {
			this.xhr.abort();
			let count = this.timerPeriods[this.index] || 0;
			if (!count) {
				this.xhr.onload();
				this.fail();
				return;
			}
			this.showTimer(count);
			// Запускаем отсчет до следующей отправки сообщения
			this.timer(count);
		}, 15000);
	}
	
	/**
	 * Отсчет времени до повторной отправки сообщения
	 * Рекурсия, каждый раз уменьшает значение count
	 * @param count число с которово начиается отсчет времени
	 */
	timer(count) {
		if (count < 1) {
			// Повторно отправляем сообщение
			this.send();
			return;
		}
		window.appTracks.$refs.trackList.setTimer(count);
		count--;
		this.timerTimeout = setTimeout(
			() => { 
				this.timer(count)
			}, 
			1000
		);
	}	
	
	/**
	 * Пытаемся отправить сообщения, и запускаем таймер по истечению которого считаем, что сообщение не смогло отправится
	 */
	send() {
		if (this.timerTimeout) {
			clearTimeout(this.timerTimeout);
		}
		this.xhr.open('post', this.action, true);
		let formData = this.sendData.getFormData();
		this.xhr.send(formData);
		this.showPreloader();
		this.planError(this.index + 1);
	}	
	
	restoreMessageToForm() {
		if($('#message_body1').hasClass('trumbowyg-textarea')) {
			$('#message_body1').trumbowyg('html', window.emojiReplacements.shortcodeToImg(this.sendData.get('message')));
		} else {
			$('#message_body1').val(this.sendData.get('message'));
		}
		$('#message_body1').trigger('input');
		window.appFiles.$refs.fileUploader.applyFileList(this.files, true);
	}

	/**
	 * Оптимистичный интерфейс не сработал, сообщение не получилось отправить убираем сообщение
	 */
	fail() {
		this.clearAll();
		MessageFormModule.openMessageForm();
		window.appTracks.$refs.trackList.removeMessageByLocalId(this.localId);
		this.restoreMessageToForm();
	}
	
	/**
	 * Оптимистичный интерфейс сработал, останавливаем таймер о повтороной отправки
	 */
	end() {
		this.clearAll();
		MessageFormModule.sendingComplete();
	}

	clearAll() {
		// Очищаем очередь отправки
		csSendQueue = [];
		csUpdateSendQueue();
		window.bus.$emit('removeSendingCount');
		if (this.preloaderTimeout) {
			clearTimeout(this.preloaderTimeout);
		}
		if (this.errorTimeout) {
			clearTimeout(this.errorTimeout);
		}
		this.submitButton.removeClass('btn_disabled').prop('disabled', false);
	}
	
	/**
	 * Показывае прелоудер, скрываем отсчет времени
	 */
	showPreloader() {
		window.appTracks.$refs.trackList.setTimer(0);
	}	
	
	/**
	 * Показывает отсчет времени скрываем прелоудер
	 */
	showTimer(count) {
		window.appTracks.$refs.trackList.setTimer(count);
	}
	
	/**
	 * Показываем пользователю его сообщение в треке
	 */
	showMessage() {
		let message = _.escape(this.sendData.get("message"));
		// Шорткод в emoji изображение
		message = window.emojiReplacements.shortcodeToSpan(message);
		
		let messageData = {
			key: this.messageKey,
			author: {
				USERID: window.actorId,
				username: window.actorLogin,
				profilepicture: window.actorAvatar,
			},
			message: message,
			time: _.now() / 1000,
			filesArray: this.files,
		};
		let quoteId = this.sendData.get('quoteId');
		if (quoteId) {
			messageData.quote = window.appTracks.$refs.trackList.getQuoteData(quoteId);
		}
		this.localId = window.appTracks.$refs.trackList.applyContent(messageData);
	}
		
	/**
	 * Событие для кнопки отправить, если пользователь не хочет дожидатся отсчета времени до следующей отправки
	 */
	setEvents() {
		window.bus.$on('forceMessageSend', () => {
			this.send();
		});
	}

	/**
	 * Генерируем индификатор, по которому можно заменить сообщение в треке после удачной отправки
	 */
	generateMessageKey() {
		let text = '';
		let chars = 'abcdefghijklmnopqrstuvwxyz0123456789';

		for (let i = 0; i < 8; i++) {
			text += chars.charAt(Math.floor(Math.random() * chars.length));
		}
		return text;
	}
}

window.OptimisticUi = OptimisticUi;
