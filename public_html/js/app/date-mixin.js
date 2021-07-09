import { DateTime } from 'luxon';

export default {
	data () {
		return {
			// Локализация компонента
			i18n: {
				en: {
					'января': 'January',
					'февраля': 'February',
					'марта': 'March',
					'апреля': 'April',
					'мая': 'May',
					'июня': 'June',
					'июля': 'July',
					'августа': 'August',
					'сентября': 'September',
					'октября': 'October',
					'ноября': 'November',
					'декабря': 'December',
				}
			},

			months: [
				'января',
				'февраля',
				'марта',
				'апреля',
				'мая',
				'июня',
				'июля',
				'августа',
				'сентября',
				'октября',
				'ноября',
				'декабря',
			],

			timezone: window.actorTimezone || 'UTC+3',
		}
	},

	methods: {
		getDate: function(time, withYear = true) {
			let date = new DateTime.fromSeconds(parseInt(time)).setZone(this.timezone);
			return date.day + ' ' + this.t(this.months[date.month - 1]) + (withYear ? ' ' + date.year : '');
		},

		getTime: function(time, withDay = false) {
			let date = new DateTime.fromSeconds(parseInt(time)).setZone(this.timezone);
			return date.toFormat('HH:mm');
		},

		/**
		 * Выводит дату без года, если год текущий, и с годом, если нет
		 * @param time
		 * @return {string}
		 */
		getDateWithoutYear: function(time) {
			let dateReturn,
				date = new DateTime.fromSeconds(parseInt(time)).setZone(this.timezone),
				dateMonth = date.month - 1,
				dateDay = date.day,
				now = new DateTime.fromSeconds(new Date().getTime() / 1000).setZone(this.timezone);


			if (now.year === date.year) {
				dateReturn = date.day + ' ' + this.t(_.upperFirst(this.months[date.month - 1]));
			} else {
				if (dateMonth < 10) {
					dateMonth = '0' + dateMonth;
				}
				if (dateDay < 10) {
					dateDay = '0' + dateDay;
				}
				dateReturn = dateDay + '.' + dateMonth + '.' + date.toFormat('yyyy');
			}
			return dateReturn;
		},

		/**
		 * Выводит дату + время
		 *
		 * @param time
		 * @returns {string}
		 */
		getDateWithTime(time) {
			if (!time) {
				return '';
			}

			let dateReturn,
				date = new DateTime.fromSeconds(parseInt(time)).setZone(this.timezone),
				dateMonth = date.month - 1,
				dateDay = date.day,
				now = new DateTime.fromSeconds(new Date().getTime() / 1000).setZone(this.timezone),
				diff = now.ts - date.ts;

			if (dateMonth < 10) {
				dateMonth = '0' + dateMonth;
			}
			if (dateDay < 10) {
				dateDay = '0' + dateDay;
			}

			if (diff < 4*60*60*1000) {
				dateReturn = date.toFormat('HH:mm');
			}
			else if (diff < 24*60*60*1000) {
				dateReturn = this.t('Сегодня');
			}
			else if (diff < 48*60*60*1000) {
				dateReturn = this.t('Вчера');
			}
			else if (now.year === date.year) {
				dateReturn = dateDay + '.' + dateMonth;
			} else {
				dateReturn = dateDay + '.' + dateMonth + '.' + date.toFormat('yy');
			}

			return dateReturn;
		},
	},
};
