import {DateTime} from 'luxon';

/**
 * Компонент который обновляет и добавляет время для $.onlineWidget()
 * TODO: перенести весь функционал сюда
 */
export default class UserStatus {
	constructor(instance) {
		this.instance = instance;
		this.lastOnline = this.getNowTime();
		this.user_id = parseInt(instance.getAttribute('data-user-id'));
		this.timer = null;
		this.timerInterval = 1;
		this.isOnline = true;
		this.withText = !!this.instance.getAttribute('data-with-text');
		this.lastOnlineStatus = true;
	}

	/**
	 * Возвращает элемент с текстов внутри
	 * @param time
	 * @returns {Element}
	 */
	getOfflineTime(time) {
		return parseHTML(`<span class="f13 user-offline-time">&nbsp;(${time})</span>`)[0];
	}

	/**
	 * Слушает event-ы отправляемые с разных мест
	 * в данном случае с fox.js:5829
	 */
	listenEvent() {
		document.addEventListener('user-status', (evt) => {
			if (evt.detail.user_id == this.user_id) {
				this.changeStatus(evt.detail.status);
			}
		});
	}

	/**
	 * Возвращает текущее время в миллисекундах
	 * @returns {number}
	 */
	getNowTime() {
		return Utils.getServerTime() * 1000;
	}

	/**
	 * Возвращает разницу во времени с последнего онлайна
	 * @returns {string}
	 */
	getDiffTime() {
		let now = DateTime.fromMillis(this.getNowTime()).setLocale('ru');
		let lastOnline = DateTime.fromMillis(this.lastOnline).setLocale('ru');
		let diffsObject = now.diff(lastOnline, ['years', 'months', 'days', 'hours', 'minutes', 'seconds']).toObject();
		let biggestTimeType = this.getBiggestTimeType(diffsObject);
		return this.getAgoTime(biggestTimeType, diffsObject);
	}

	/**
	 * Вовзращает в человеко-понятном виде
	 * типа "5 секунд".
	 * или "21 секунда"
	 * @param typeAndTime
	 * @param diffsObject
	 * @returns {string}
	 */
	getAgoTime(typeAndTime, diffsObject) {
		try {
			let type = typeAndTime.type;
			let time = typeAndTime.value;
			let plurals = {
				'years': ['год', 'года', 'лет'],
				'months': ['месяц', 'месяца', 'месяцев'],
				'days': ['день', 'дня', 'дней'],
				'hours': ['час', 'часа', 'часов'],
				'minutes': ['минуту', 'минуты', 'минут'],
				'seconds': ['секунду', 'секунды', 'секунд'],
			};
			if (type == 'minutes') {
				this.timerInterval = 60;
				this.setupTimer();
			}
			let plural = Utils.declOfNum(time, plurals[type]);
			return `${time} ${plural}`;
		} catch (err) {

		}

	}

	/**
	 * Возвращает самый большой тип из данных в объекте
	 * например в объекте могут быть
	 * {
	 *     days:0,
	 *     hours:1,
	 *     minutes:12,
	 *     seconds:0,
	 * }
	 * вернет hours и его значения, нужен для getAgoTime()
	 * @param diffsObject
	 * @returns {{type: string, value: number}|{type: *, value: *}}
	 */
	getBiggestTimeType(diffsObject) {
		for (let type in diffsObject) {
			let value = diffsObject[type];
			if (value > 0) {
				return {type: type, value: value};
			}
		}
		return {type: 'seconds', value: 1};
	}

	/**
	 * Обновляет в модуле текст
	 */
	updateTime() {
		if (this.isOnline || !this.withText) {
			return;
		}
		let diffForHuman = this.getDiffTime();
		let timeTemplate = this.getOfflineTime(diffForHuman);
		if (this.instance.querySelector('.user-offline-time')) {
			$(this.instance).find('.user-offline-time').replaceWith(timeTemplate);
		} else {
			this.instance.append(timeTemplate);
		}
	}


	changeToOffline() {
		if (!this.lastOnlineStatus) {
			return;
		}
		this.lastOnline = this.getNowTime();
		this.isOnline = false;
		this.lastOnlineStatus = false;
		this.timerInterval = 1;
		this.setupTimer();
	}

	/**
	 * Запускает таймер который будет
	 * раз в секунду или минуту обновлять значение в виджете
	 */
	setupTimer() {
		clearInterval(this.timer);
		this.timer = setInterval(() => {
			this.updateTime()
		}, this.timerInterval * 1000);
	}

	changeToOnline() {
		if (this.lastOnlineStatus) {
			return;
		}
		this.isOnline = true;
		this.lastOnlineStatus = true;
	}

	changeStatus(status) {
		status === 'online' ? this.changeToOnline() : this.changeToOffline();
	}

	init() {
		this.listenEvent();
	}
}