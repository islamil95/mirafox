-- --------------------------------------------------------
-- Хост:                         93.171.201.111
-- Версия сервера:               8.0.17-8 - Percona Server (GPL), Release '8', Revision '868a4ef'
-- Операционная система:         debian-linux-gnu
-- HeidiSQL Версия:              10.1.0.5464
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Дамп структуры для таблица test_stand.categories
CREATE TABLE IF NOT EXISTS `categories` (
  `CATID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL DEFAULT '',
  `seo` varchar(200) NOT NULL,
  `parent` int(11) NOT NULL DEFAULT '0',
  `lang` varchar(8) NOT NULL DEFAULT 'ru' COMMENT 'Язык категории',
  `custom_offer` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`CATID`),
  UNIQUE KEY `name` (`name`),
  KEY `seo_idx` (`seo`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы test_stand.categories: ~1 rows (приблизительно)
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` (`CATID`, `name`, `seo`, `parent`, `lang`, `custom_offer`) VALUES
	(1, 'Услуги', 'services', 0, 'ru', 0);
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;

-- Дамп структуры для таблица test_stand.dblock
CREATE TABLE IF NOT EXISTS `dblock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `created` datetime NOT NULL,
  `lifetime` int(11) NOT NULL DEFAULT '10',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=399 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы test_stand.dblock: ~0 rows (приблизительно)
/*!40000 ALTER TABLE `dblock` DISABLE KEYS */;
/*!40000 ALTER TABLE `dblock` ENABLE KEYS */;

-- Дамп структуры для таблица test_stand.kwork_log
CREATE TABLE IF NOT EXISTS `kwork_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `kwork_id` int(11) NOT NULL,
  `status` int(11) DEFAULT NULL,
  `feat` int(11) DEFAULT NULL,
  `date_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_type` enum('abuse','year','portfolio','package','queue','moder','postmoder','stay_on_moder','stay_on_postmoder','admin_block','autoactive','package_price_ratio','create','auto_stop','auto_reject','auto_delete','restored_after_delete','inactive_remove_from_moder','active_add_to_moder','edit','moder_reopen','admin_change_classification','admin_change_category','take_away_block') DEFAULT NULL,
  `kwork_moder_id` int(11) DEFAULT NULL COMMENT 'Идентификатор модерации',
  `data` longtext,
  `admin_id` int(11) DEFAULT NULL,
  `is_virtual` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Признак виртуального логина',
  PRIMARY KEY (`id`),
  KEY `kwork_id_idx` (`kwork_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дамп данных таблицы test_stand.kwork_log: ~0 rows (приблизительно)
/*!40000 ALTER TABLE `kwork_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `kwork_log` ENABLE KEYS */;

-- Дамп структуры для таблица test_stand.members
CREATE TABLE IF NOT EXISTS `members` (
  `USERID` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `role` enum('user','moder','admin') CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'user',
  `type` enum('payer','worker') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'payer',
  `status` enum('new','active','delete','blocked','self_deleted') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'active',
  `fullname` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `lastlogin` int(11) NOT NULL,
  `profilepicture` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `ip` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `lip` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `live_date` int(11) DEFAULT NULL,
  `cover` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'default.jpg',
  `lang` varchar(8) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'ru',
  `phone` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `phone_verified` int(1) NOT NULL DEFAULT '0',
  `funds` decimal(9,2) NOT NULL DEFAULT '0.00',
  `email` varchar(80) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `verified` char(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '1',
  `password` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `addtime` int(11) NOT NULL,
  `is_orders` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`USERID`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username_idx` (`username`),
  UNIQUE KEY `phone` (`phone`),
  KEY `status_idx` (`status`),
  KEY `USERID_lang` (`USERID`),
  KEY `members_role_index` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Дамп данных таблицы test_stand.members: ~6 rows (приблизительно)
/*!40000 ALTER TABLE `members` DISABLE KEYS */;
INSERT INTO `members` (`USERID`, `username`, `role`, `type`, `status`, `fullname`, `lastlogin`, `profilepicture`, `ip`, `lip`, `live_date`, `cover`, `lang`, `phone`, `phone_verified`, `funds`, `email`, `verified`, `password`, `addtime`, `is_orders`) VALUES
	(4, 'test', 'user', 'worker', 'active', '', 1574352864, 'noprofilepicture.gif', '127.0.0.1', '127.0.0.1', 1574352864, 'header1.jpg', 'ru', NULL, 0, 81060.00, 'Xk9ZXmpHS0NGBFhf', '1', '897c8fde25c5cc5270cda61425eed3c8', 1574352864, 1),
	(5, 'test1', 'user', 'worker', 'active', '', 1574354098, 'noprofilepicture.gif', '127.0.0.1', '127.0.0.1', 1574354098, 'header9.jpg', 'ru', NULL, 0, 100400.00, 'Xk9ZXhhqQV1FWEEEWF8=', '1', '897c8fde25c5cc5270cda61425eed3c8', 1574354098, 1),
	(6, 'test2', 'user', 'worker', 'active', '', 1574354463, 'noprofilepicture.gif', '127.0.0.1', '127.0.0.1', 1574354463, 'header10.jpg', 'ru', NULL, 0, 99799.20, 'Xk9ZXhlqQV1FWEEEWF8=', '1', '897c8fde25c5cc5270cda61425eed3c8', 1574354463, 1),
	(7, 'test3', 'user', 'worker', 'active', '', 1574354909, 'noprofilepicture.gif', '127.0.0.1', '127.0.0.1', 1574354909, 'header6.jpg', 'ru', NULL, 0, 88701.00, 'Xk9ZXh5qQV1FWEEEWF8=', '1', '897c8fde25c5cc5270cda61425eed3c8', 1574354909, 1),
	(8, 'test4', 'user', 'payer', 'active', '', 1574356761, 'noprofilepicture.gif', '127.0.0.1', '127.0.0.1', 1574356761, 'header6.jpg', 'ru', NULL, 0, 91600.00, 'Xk9ZXh9qQV1FWEEEWF8=', '1', '897c8fde25c5cc5270cda61425eed3c8', 1574356761, 1),
	(9, 'test5', 'user', 'worker', 'active', '', 1574674241, 'noprofilepicture.gif', '127.0.0.1', '127.0.0.1', 1574674241, 'header2.jpg', 'ru', NULL, 0, 65200.00, 'Xk9ZXhxqQV1FWARYXw==', '1', '897c8fde25c5cc5270cda61425eed3c8', 1574674241, 1);
/*!40000 ALTER TABLE `members` ENABLE KEYS */;

-- Дамп структуры для таблица test_stand.offer
CREATE TABLE IF NOT EXISTS `offer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `want_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `kwork_id` int(11) DEFAULT NULL,
  `comment` varchar(2000) DEFAULT NULL,
  `status` enum('active','delete','cancel','done','reject') NOT NULL,
  `date_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `highlighted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Выделенный',
  `comment_doubles_description` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Комментарий к предложению дублирует опписание кворка - для предложений сделанных через новую форму индивидуального предложения',
  `is_read` tinyint(4) DEFAULT '0',
  `app_id` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'ID проекта',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_want_idx` (`user_id`,`want_id`),
  KEY `want_id_idx` (`want_id`),
  KEY `order_id_idx` (`order_id`),
  KEY `status_idx` (`status`),
  KEY `kwork_id_idx` (`kwork_id`),
  KEY `app_id` (`app_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы test_stand.offer: ~11 rows (приблизительно)
/*!40000 ALTER TABLE `offer` DISABLE KEYS */;
INSERT INTO `offer` (`id`, `user_id`, `want_id`, `order_id`, `kwork_id`, `comment`, `status`, `date_create`, `highlighted`, `comment_doubles_description`, `is_read`, `app_id`) VALUES
	(1, 9, 1, 3, 11, 'Предложение на первый проект Предложение на первый проект Предложение на первый проект Предложение на первый проект Предложение на первый проект Предложение на первый проект', 'active', '2019-11-26 11:15:33', 0, 0, 1, 1),
	(2, 9, 3, 4, 12, 'Предложение на третий проект Предложение на третий проект Предложение на третий проект Предложение на третий проект Предложение на третий проект Предложение на третий проект', 'active', '2019-11-26 11:15:51', 0, 0, 0, 1),
	(3, 9, 6, 5, 13, 'Предложение на шестой проект Предложение на шестой проект Предложение на шестой проект Предложение на шестой проект Предложение на шестой проект Предложение на шестой проект Предложение на шестой проект', 'active', '2019-11-26 11:16:11', 0, 0, 0, 1),
	(4, 9, 8, 6, 14, 'Предложение на восьмой проект Предложение на восьмой проект Предложение на восьмой проект Предложение на восьмой проект Предложение на восьмой проект Предложение на восьмой проект', 'active', '2019-11-26 11:17:18', 0, 0, 0, 1),
	(5, 8, 2, 7, 15, 'Предложение на второй проект Предложение на второй проект Предложение на второй проект Предложение на второй проект Предложение на второй проект Предложение на второй проект', 'active', '2019-11-26 11:17:34', 0, 0, 0, 1),
	(6, 8, 10, 8, 16, 'Предложение на десятый проект Предложение на десятый проект Предложение на десятый проект Предложение на десятый проект Предложение на десятый проект Предложение на десятый проект', 'active', '2019-11-26 11:17:58', 0, 0, 1, 1),
	(7, 8, 5, 9, 17, 'Предложение на пятый проект Предложение на пятый проект Предложение на пятый проект Предложение на пятый проект Предложение на пятый проект Предложение на пятый проект', 'active', '2019-11-26 11:18:32', 0, 0, 0, 1),
	(8, 6, 1, 10, 18, 'Предложение на второй проект Предложение на второй проект Предложение на второй проект Предложение на второй проект Предложение на второй проект Предложение на второй проект', 'active', '2019-11-26 11:19:36', 0, 0, 1, 1),
	(9, 6, 2, 11, 19, 'Предложение на второй проект Предложение на второй проект Предложение на второй проект Предложение на второй проект Предложение на второй проект Предложение на второй проект', 'active', '2019-11-26 11:20:39', 0, 0, 0, 1),
	(10, 6, 5, 12, 20, 'Предложение на пятый проект Предложение на пятый проект Предложение на пятый проект Предложение на пятый проект Предложение на пятый проект Предложение на пятый проект', 'active', '2019-11-26 11:20:59', 0, 0, 0, 1),
	(11, 6, 9, 13, 21, 'Предложение на девятый проект Предложение на девятый проект Предложение на девятый проект Предложение на девятый проект Предложение на девятый проект Предложение на девятый проект', 'active', '2019-11-26 11:21:19', 0, 0, 0, 1);
/*!40000 ALTER TABLE `offer` ENABLE KEYS */;

-- Дамп структуры для таблица test_stand.offer_edit
CREATE TABLE IF NOT EXISTS `offer_edit` (
  `offer_id` int(11) NOT NULL COMMENT 'ID предложения',
  `last_edit_time` datetime NOT NULL COMMENT 'Время последнего изменения предложения',
  PRIMARY KEY (`offer_id`),
  KEY `last_edit_time_idx` (`last_edit_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дамп данных таблицы test_stand.offer_edit: ~0 rows (приблизительно)
/*!40000 ALTER TABLE `offer_edit` DISABLE KEYS */;
/*!40000 ALTER TABLE `offer_edit` ENABLE KEYS */;

-- Дамп структуры для таблица test_stand.operation
CREATE TABLE IF NOT EXISTS `operation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` enum('withdraw','adm_refund','refund','refill','order_out','order_in','post_out','order_out_bonus','refund_bonus','refill_bonus','refill_referal','cancel_bonus','refill_moder_kwork','order_out_yandex','order_in_yandex','order_out_bill','refund_bill','refill_bill','refill_moder_request','moneyback') NOT NULL,
  `amount` decimal(9,2) NOT NULL,
  `description` varchar(256) DEFAULT NULL,
  `status` enum('inprogress','done','cancel','new') NOT NULL,
  `payment` varchar(128) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `kwork_id` int(11) DEFAULT NULL,
  `date_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_done` timestamp NULL DEFAULT NULL,
  `request_id` int(20) DEFAULT NULL,
  `currency_id` int(3) NOT NULL DEFAULT '643',
  `currency_rate` decimal(9,2) NOT NULL DEFAULT '1.00',
  `is_tips` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Является операцией по чаевым (бонус продавцу)',
  `sub_type` varchar(10) DEFAULT NULL COMMENT 'Поле подтипа операции, для refill это unitpay, bill, admin',
  `lang` enum('ru','en') NOT NULL COMMENT 'Язык операции для фильтрации в админке (может не совпадать с валютой операции)',
  `lang_amount` decimal(9,2) NOT NULL COMMENT 'Сумма в валюте языка операции',
  PRIMARY KEY (`id`),
  KEY `user_id_idx` (`user_id`),
  KEY `type_idx` (`type`),
  KEY `order_id_idx` (`order_id`),
  KEY `date_done` (`date_done`),
  KEY `date_create` (`date_create`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы test_stand.operation: ~36 rows (приблизительно)
/*!40000 ALTER TABLE `operation` DISABLE KEYS */;
INSERT INTO `operation` (`id`, `user_id`, `type`, `amount`, `description`, `status`, `payment`, `order_id`, `kwork_id`, `date_create`, `date_done`, `request_id`, `currency_id`, `currency_rate`, `is_tips`, `sub_type`, `lang`, `lang_amount`) VALUES
	(1, 9, 'refill', 10000.00, NULL, 'done', 'admin', NULL, NULL, '2019-11-26 11:00:06', '2019-11-26 11:00:06', NULL, 643, 1.00, 0, 'admin', 'ru', 10000.00),
	(2, 9, 'order_out', 2000.00, NULL, 'done', NULL, 1, NULL, '2019-11-26 11:03:27', '2019-11-26 11:03:27', NULL, 643, 1.00, 0, NULL, 'ru', 2000.00),
	(3, 4, 'refill', 90000.00, NULL, 'done', 'admin', NULL, NULL, '2019-11-26 11:07:12', '2019-11-26 11:07:12', NULL, 643, 1.00, 0, 'admin', 'ru', 90000.00),
	(4, 7, 'refill', 72000.00, NULL, 'done', 'admin', NULL, NULL, '2019-11-26 11:07:26', '2019-11-26 11:07:26', NULL, 643, 1.00, 0, 'admin', 'ru', 72000.00),
	(5, 8, 'refill', 99999.00, NULL, 'done', 'admin', NULL, NULL, '2019-11-26 11:07:44', '2019-11-26 11:07:44', NULL, 643, 1.00, 0, 'admin', 'ru', 99999.00),
	(6, 9, 'refill', 9000.00, NULL, 'done', 'admin', NULL, NULL, '2019-11-26 11:11:25', '2019-11-26 11:11:25', NULL, 643, 1.00, 0, 'admin', 'ru', 9000.00),
	(7, 9, 'refill', 60000.00, NULL, 'done', 'admin', NULL, NULL, '2019-11-26 11:12:36', '2019-11-26 11:12:36', NULL, 643, 1.00, 0, 'admin', 'ru', 60000.00),
	(8, 9, 'order_out', 3500.00, NULL, 'done', NULL, 2, NULL, '2019-11-26 11:14:56', '2019-11-26 11:14:56', NULL, 643, 1.00, 0, NULL, 'ru', 3500.00),
	(9, 6, 'order_out', 1500.00, NULL, 'done', NULL, 14, NULL, '2019-11-26 11:21:31', '2019-11-26 11:21:31', NULL, 643, 1.00, 0, NULL, 'ru', 1500.00),
	(10, 6, 'order_out', 1000.00, NULL, 'done', NULL, 15, NULL, '2019-11-26 11:21:34', '2019-11-26 11:21:34', NULL, 643, 1.00, 0, NULL, 'ru', 1000.00),
	(11, 6, 'order_out', 1500.00, NULL, 'done', NULL, 16, NULL, '2019-11-26 11:21:35', '2019-11-26 11:21:35', NULL, 643, 1.00, 0, NULL, 'ru', 1500.00),
	(12, 7, 'order_out', 1000.00, NULL, 'done', NULL, 17, NULL, '2019-11-26 11:21:48', '2019-11-26 11:21:48', NULL, 643, 1.00, 0, NULL, 'ru', 1000.00),
	(13, 7, 'order_out', 1999.00, NULL, 'done', NULL, 18, NULL, '2019-11-26 11:21:49', '2019-11-26 11:21:49', NULL, 643, 1.00, 0, NULL, 'ru', 1999.00),
	(14, 7, 'order_out', 1500.00, NULL, 'done', NULL, 19, NULL, '2019-11-26 11:21:51', '2019-11-26 11:21:51', NULL, 643, 1.00, 0, NULL, 'ru', 1500.00),
	(15, 7, 'order_out', 500.00, NULL, 'done', NULL, 20, NULL, '2019-11-26 11:21:52', '2019-11-26 11:21:52', NULL, 643, 1.00, 0, NULL, 'ru', 500.00),
	(16, 8, 'order_out', 3000.00, NULL, 'done', NULL, 21, NULL, '2019-11-26 11:22:04', '2019-11-26 11:22:04', NULL, 643, 1.00, 0, NULL, 'ru', 3000.00),
	(17, 8, 'order_out', 2000.00, NULL, 'done', NULL, 22, NULL, '2019-11-26 11:22:05', '2019-11-26 11:22:05', NULL, 643, 1.00, 0, NULL, 'ru', 2000.00),
	(18, 8, 'order_out', 1000.00, NULL, 'done', NULL, 23, NULL, '2019-11-26 11:22:07', '2019-11-26 11:22:07', NULL, 643, 1.00, 0, NULL, 'ru', 1000.00),
	(19, 9, 'order_out', 3500.00, NULL, 'done', NULL, 24, NULL, '2019-11-26 11:22:54', '2019-11-26 11:22:54', NULL, 643, 1.00, 0, NULL, 'ru', 3500.00),
	(20, 9, 'order_out', 1999.00, NULL, 'done', NULL, 25, NULL, '2019-11-26 11:22:55', '2019-11-26 11:22:55', NULL, 643, 1.00, 0, NULL, 'ru', 1999.00),
	(21, 9, 'order_out', 500.00, NULL, 'done', NULL, 26, NULL, '2019-11-26 11:22:58', '2019-11-26 11:22:58', NULL, 643, 1.00, 0, NULL, 'ru', 500.00),
	(22, 9, 'order_out', 1500.00, NULL, 'done', NULL, 27, NULL, '2019-11-26 11:22:59', '2019-11-26 11:22:59', NULL, 643, 1.00, 0, NULL, 'ru', 1500.00),
	(23, 4, 'order_out', 3500.00, NULL, 'done', NULL, 28, NULL, '2019-11-26 11:23:22', '2019-11-26 11:23:22', NULL, 643, 1.00, 0, NULL, 'ru', 3500.00),
	(24, 4, 'order_out', 2000.00, NULL, 'done', NULL, 29, NULL, '2019-11-26 11:23:23', '2019-11-26 11:23:23', NULL, 643, 1.00, 0, NULL, 'ru', 2000.00),
	(25, 4, 'order_out', 1000.00, NULL, 'done', NULL, 30, NULL, '2019-11-26 11:23:24', '2019-11-26 11:23:24', NULL, 643, 1.00, 0, NULL, 'ru', 1000.00),
	(26, 4, 'order_out', 1500.00, NULL, 'done', NULL, 31, NULL, '2019-11-26 11:25:23', '2019-11-26 11:25:23', NULL, 643, 1.00, 0, NULL, 'ru', 1500.00),
	(27, 9, 'order_out', 3500.00, NULL, 'done', NULL, 32, NULL, '2019-11-26 12:58:37', '2019-11-26 12:58:37', NULL, 643, 1.00, 0, NULL, 'ru', 3500.00),
	(28, 9, 'order_out', 1000.00, NULL, 'done', NULL, 33, NULL, '2019-11-26 12:58:52', '2019-11-26 12:58:52', NULL, 643, 1.00, 0, NULL, 'ru', 1000.00),
	(29, 8, 'order_out', 500.00, NULL, 'done', NULL, 34, NULL, '2019-11-26 12:59:40', '2019-11-26 12:59:40', NULL, 643, 1.00, 0, NULL, 'ru', 500.00),
	(30, 8, 'order_out', 1000.00, NULL, 'done', NULL, 35, NULL, '2019-11-26 12:59:41', '2019-11-26 12:59:41', NULL, 643, 1.00, 0, NULL, 'ru', 1000.00),
	(31, 8, 'order_out', 1000.00, NULL, 'done', NULL, 36, NULL, '2019-11-26 12:59:58', '2019-11-26 12:59:58', NULL, 643, 1.00, 0, NULL, 'ru', 1000.00),
	(32, 8, 'order_out', 1000.00, NULL, 'done', NULL, 37, NULL, '2019-11-26 13:08:09', '2019-11-26 13:08:09', NULL, 643, 1.00, 0, NULL, 'ru', 1000.00),
	(33, 8, 'order_out', 1999.00, NULL, 'done', NULL, 38, NULL, '2019-11-26 13:08:59', '2019-11-26 13:08:59', NULL, 643, 1.00, 0, NULL, 'ru', 1999.00),
	(34, 8, 'order_out', 1500.00, NULL, 'done', NULL, 39, NULL, '2019-11-26 13:09:00', '2019-11-26 13:09:00', NULL, 643, 1.00, 0, NULL, 'ru', 1500.00),
	(35, 8, 'order_out', 1000.00, NULL, 'done', NULL, 40, NULL, '2019-11-26 13:09:02', '2019-11-26 13:09:02', NULL, 643, 1.00, 0, NULL, 'ru', 1000.00),
	(36, 9, 'order_out', 1000.00, NULL, 'done', NULL, 41, NULL, '2019-11-26 13:11:55', '2019-11-26 13:11:55', NULL, 643, 1.00, 0, NULL, 'ru', 1000.00),
	(37, 4, 'order_out', 1000.00, NULL, 'done', NULL, 42, NULL, '2019-11-26 14:13:44', '2019-11-26 14:13:44', NULL, 643, 1.00, 0, NULL, 'ru', 1000.00),
	(38, 4, 'order_out', 1000.00, NULL, 'done', NULL, 43, NULL, '2019-11-26 14:14:45', '2019-11-26 14:14:45', NULL, 643, 1.00, 0, NULL, 'ru', 1000.00),
	(39, 4, 'order_out', 1000.00, NULL, 'done', NULL, 44, NULL, '2019-11-26 14:19:10', '2019-11-26 14:19:10', NULL, 643, 1.00, 0, NULL, 'ru', 1000.00),
	(40, 4, 'order_out', 1000.00, NULL, 'done', NULL, 45, NULL, '2019-11-26 14:26:18', '2019-11-26 14:26:18', NULL, 643, 1.00, 0, NULL, 'ru', 1000.00),
	(41, 4, 'order_out', 1000.00, NULL, 'done', NULL, 46, NULL, '2019-11-26 14:27:10', '2019-11-26 14:27:10', NULL, 643, 1.00, 0, NULL, 'ru', 1000.00),
	(42, 4, 'order_out', 1000.00, NULL, 'done', NULL, 47, NULL, '2019-11-26 14:30:27', '2019-11-26 14:30:27', NULL, 643, 1.00, 0, NULL, 'ru', 1000.00),
	(43, 4, 'order_out', 1000.00, NULL, 'done', NULL, 48, NULL, '2019-11-26 14:33:12', '2019-11-26 14:33:12', NULL, 643, 1.00, 0, NULL, 'ru', 1000.00);
/*!40000 ALTER TABLE `operation` ENABLE KEYS */;

-- Дамп структуры для таблица test_stand.orders
CREATE TABLE IF NOT EXISTS `orders` (
  `OID` int(11) NOT NULL AUTO_INCREMENT,
  `USERID` int(11) NOT NULL DEFAULT '0',
  `PID` int(11) NOT NULL DEFAULT '0',
  `worker_id` int(11) DEFAULT NULL,
  `time_added` int(11) DEFAULT NULL,
  `status` int(1) NOT NULL DEFAULT '0',
  `stime` int(11) DEFAULT NULL,
  `price` decimal(9,2) NOT NULL DEFAULT '0.00',
  `crt` decimal(9,2) NOT NULL DEFAULT '0.00',
  `cltime` int(11) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `extended_time` int(11) NOT NULL DEFAULT '0' COMMENT 'Количество секунд продления заказа администратором',
  `deadline` int(11) DEFAULT NULL,
  `count` int(11) NOT NULL DEFAULT '1',
  `kwork_title` varchar(256) NOT NULL,
  `kwork_days` int(11) DEFAULT '0',
  `workTime` int(11) NOT NULL DEFAULT '0',
  `date_inprogress` timestamp NULL DEFAULT NULL,
  `date_arbitrage` timestamp NULL DEFAULT NULL,
  `date_cancel` timestamp NULL DEFAULT NULL,
  `date_check` timestamp NULL DEFAULT NULL,
  `date_done` timestamp NULL DEFAULT NULL,
  `in_work` tinyint(4) NOT NULL DEFAULT '0',
  `data_provided` tinyint(4) NOT NULL DEFAULT '0',
  `last_track_id` int(11) DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `portfolio_type` enum('new','allow','deny') DEFAULT 'new',
  `data_provided_hash` varchar(32) DEFAULT NULL,
  `is_quick` int(1) DEFAULT NULL,
  `rating_type` enum('new','good','bad','first','second','tips') DEFAULT 'new' COMMENT 'тип отзыва у сделанного заказа: new - не определен, good - оставлен положительный отзыв, bad - оставлен отрицательный отзыв, first - без отзыва первичный, second - без отзыва повторный, tips - без отзыва с чаевыми',
  `currency_id` int(3) NOT NULL DEFAULT '643',
  `currency_rate` decimal(9,2) NOT NULL DEFAULT '1.00',
  `source_type` enum('kwork','want','inbox','cart','want_private','inbox_private','anywhere','anywhere_private','kwork_promo','cart_promo') DEFAULT 'kwork',
  `rating_ignore` tinyint(1) DEFAULT '0',
  `expires` int(11) DEFAULT NULL,
  `payer_unread_tracks` int(11) NOT NULL DEFAULT '0' COMMENT 'Количество непрочтенных сообщений для покупателя от продавца',
  `worker_unread_tracks` int(11) NOT NULL DEFAULT '0' COMMENT 'Количество непрочтенных сообщений для продавца от покупателя',
  `bonus_text` varchar(25) DEFAULT NULL,
  `worker_amount` decimal(9,2) NOT NULL DEFAULT '0.00' COMMENT 'Сумма, которую получит продавец в своей валюте',
  `payer_amount` decimal(9,2) NOT NULL DEFAULT '0.00' COMMENT 'Сумма, которую заплатил покупатель в своей валюте',
  `stages_price` decimal(9,2) NOT NULL DEFAULT '0.00' COMMENT 'Актуальная цена для покупателя в зависимости от состояния этапного заказа',
  `stages_crt` decimal(9,2) NOT NULL DEFAULT '0.00' COMMENT 'Актуальная цена для продавца в зависимости от состояния этапного заказа',
  `has_stages` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'У заказа есть этапы, денормализация для ускорения запросов',
  `has_payer_stages` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'В заказе есть безакцептные этапы от покупателя (это добавляет причину отмены заказа)',
  `show_as_inprogress_for_worker` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Отображать статус заказа как В работе в то время как он на проверке для продавца',
  `initial_offer_price` decimal(9,2) NOT NULL DEFAULT '0.00' COMMENT 'Стоимость изначального предложения продавца',
  `restarted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Заказ был перезапущен из состояния выполнен',
  `initial_duration` int(11) NOT NULL DEFAULT '0' COMMENT 'Длительность заказа по первоначальному предложению продавца',
  `app_id` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Идентификатор проекта - источника заказа',
  `discount_amount` decimal(9,2) DEFAULT '0.00' COMMENT 'Размер скидки',
  PRIMARY KEY (`OID`),
  KEY `USERID_idx` (`USERID`),
  KEY `PID_idx` (`PID`),
  KEY `status_idx` (`status`),
  KEY `last_track_id_idx` (`last_track_id`),
  KEY `time_added` (`time_added`),
  KEY `worker_id` (`worker_id`),
  KEY `date_done` (`date_done`),
  KEY `date_cancel` (`date_cancel`),
  KEY `project_id` (`project_id`),
  KEY `has_stages_idx` (`has_stages`),
  KEY `app_id` (`app_id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы test_stand.orders: ~41 rows (приблизительно)
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` (`OID`, `USERID`, `PID`, `worker_id`, `time_added`, `status`, `stime`, `price`, `crt`, `cltime`, `duration`, `extended_time`, `deadline`, `count`, `kwork_title`, `kwork_days`, `workTime`, `date_inprogress`, `date_arbitrage`, `date_cancel`, `date_check`, `date_done`, `in_work`, `data_provided`, `last_track_id`, `project_id`, `portfolio_type`, `data_provided_hash`, `is_quick`, `rating_type`, `currency_id`, `currency_rate`, `source_type`, `rating_ignore`, `expires`, `payer_unread_tracks`, `worker_unread_tracks`, `bonus_text`, `worker_amount`, `payer_amount`, `stages_price`, `stages_crt`, `has_stages`, `has_payer_stages`, `show_as_inprogress_for_worker`, `initial_offer_price`, `restarted`, `initial_duration`, `app_id`, `discount_amount`) VALUES
	(1, 9, 1, 4, 1574755407, 5, 1574755407, 2000.00, 1600.00, NULL, 0, 0, 1574841817, 2, 'Первый тестовый кворк', 0, 6706, '2019-11-26 11:03:27', NULL, NULL, NULL, '2019-11-26 12:55:23', 0, 1, 31, NULL, 'new', 'bc7620c63879839b9d576e419cb1b9d3', NULL, 'new', 643, 1.00, 'kwork', 0, NULL, 0, 0, NULL, 1600.00, 2000.00, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, 1, 0.00),
	(2, 9, 3, 6, 1574756096, 1, 1574756096, 3500.00, 2800.00, NULL, 0, 0, 1574848526, 1, 'Третий тестовый кворк', 0, 0, '2019-11-26 11:14:56', NULL, NULL, NULL, NULL, 0, 1, 33, NULL, 'new', '4297f44b13955235245b2497399d7a93', NULL, 'new', 643, 1.00, 'kwork', 0, NULL, 0, 0, NULL, 2800.00, 3500.00, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, 1, 0.00),
	(3, 4, 11, 9, 1574756133, 0, 1574756133, 500.00, 400.00, NULL, 0, 0, NULL, 1, 'Предложение на первый проект', 0, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 'new', NULL, NULL, 'new', 643, 1.00, 'want_private', 0, NULL, 0, 0, '', 0.00, 0.00, 0.00, 0.00, 0, 0, 0, 500.00, 0, 0, 1, 0.00),
	(4, 6, 12, 9, 1574756151, 0, 1574756151, 1500.00, 1200.00, NULL, 0, 0, NULL, 1, 'Предложение на третий проект', 0, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 'new', NULL, NULL, 'new', 643, 1.00, 'want_private', 0, NULL, 0, 0, '', 0.00, 0.00, 0.00, 0.00, 0, 0, 0, 1500.00, 0, 0, 1, 0.00),
	(5, 7, 13, 9, 1574756171, 0, 1574756171, 1500.00, 1200.00, NULL, 0, 0, NULL, 1, 'Предложение на шестой проект', 0, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 'new', NULL, NULL, 'new', 643, 1.00, 'want_private', 0, NULL, 0, 0, '', 0.00, 0.00, 0.00, 0.00, 0, 0, 0, 1500.00, 0, 0, 1, 0.00),
	(6, 8, 14, 9, 1574756238, 0, 1574756238, 1500.00, 1200.00, NULL, 0, 0, NULL, 1, 'Предложение на восьмой проект', 0, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 'new', NULL, NULL, 'new', 643, 1.00, 'want_private', 0, NULL, 0, 0, '', 0.00, 0.00, 0.00, 0.00, 0, 0, 0, 1500.00, 0, 0, 1, 0.00),
	(7, 4, 15, 8, 1574756254, 0, 1574756254, 1000.00, 800.00, NULL, 0, 0, NULL, 1, 'Предложение на второй проект', 0, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 'new', NULL, NULL, 'new', 643, 1.00, 'want_private', 0, NULL, 0, 0, '', 0.00, 0.00, 0.00, 0.00, 0, 0, 0, 1000.00, 0, 0, 1, 0.00),
	(8, 9, 16, 8, 1574756277, 0, 1574756277, 500.00, 400.00, NULL, 0, 0, NULL, 1, 'Предложение на десятый проект', 0, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 'new', NULL, NULL, 'new', 643, 1.00, 'want_private', 0, NULL, 0, 0, '', 0.00, 0.00, 0.00, 0.00, 0, 0, 0, 500.00, 0, 0, 1, 0.00),
	(9, 7, 17, 8, 1574756312, 0, 1574756312, 2000.00, 1600.00, NULL, 0, 0, NULL, 1, 'Предложение на пятый проект', 0, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 'new', NULL, NULL, 'new', 643, 1.00, 'want_private', 0, NULL, 0, 0, '', 0.00, 0.00, 0.00, 0.00, 0, 0, 0, 2000.00, 0, 0, 1, 0.00),
	(10, 4, 18, 6, 1574756376, 0, 1574756376, 600.00, 480.00, NULL, 0, 0, NULL, 1, 'Предложение на второй проект', 0, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 'new', NULL, NULL, 'new', 643, 1.00, 'want_private', 0, NULL, 0, 0, '', 0.00, 0.00, 0.00, 0.00, 0, 0, 0, 600.00, 0, 0, 1, 0.00),
	(11, 4, 19, 6, 1574756439, 0, 1574756439, 1000.00, 800.00, NULL, 0, 0, NULL, 1, 'Предложение на второй проект', 0, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 'new', NULL, NULL, 'new', 643, 1.00, 'want_private', 0, NULL, 0, 0, '', 0.00, 0.00, 0.00, 0.00, 0, 0, 0, 1000.00, 0, 0, 1, 0.00),
	(12, 7, 20, 6, 1574756459, 0, 1574756459, 3000.00, 2400.00, NULL, 0, 0, NULL, 1, 'Предложение на пятый проект', 0, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 'new', NULL, NULL, 'new', 643, 1.00, 'want_private', 0, NULL, 0, 0, '', 0.00, 0.00, 0.00, 0.00, 0, 0, 0, 3000.00, 0, 0, 1, 0.00),
	(13, 9, 21, 6, 1574756479, 0, 1574756479, 500.00, 400.00, NULL, 0, 0, NULL, 1, 'Предложение на девятый проект', 0, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 'new', NULL, NULL, 'new', 643, 1.00, 'want_private', 0, NULL, 0, 0, '', 0.00, 0.00, 0.00, 0.00, 0, 0, 0, 500.00, 0, 0, 1, 0.00),
	(14, 6, 2, 4, 1574756491, 1, 1574756491, 1500.00, 1200.00, NULL, 0, 0, 1574848893, 1, 'Второй тестовый кворк срочность', 0, 0, '2019-11-26 11:21:31', NULL, NULL, NULL, NULL, 1, 0, 62, NULL, 'new', NULL, NULL, 'new', 643, 1.00, 'kwork', 0, NULL, 0, 0, NULL, 1200.00, 1500.00, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, 1, 0.00),
	(15, 6, 8, 8, 1574756494, 1, 1574756494, 1000.00, 800.00, NULL, 0, 0, 1574848646, 1, 'Восьмой тестовый кворк', 0, 0, '2019-11-26 11:21:34', NULL, NULL, NULL, NULL, 1, 0, 41, NULL, 'new', NULL, NULL, 'new', 643, 1.00, 'kwork', 0, NULL, 0, 0, NULL, 800.00, 1000.00, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, 1, 0.00),
	(16, 6, 6, 7, 1574756495, 1, 1574756495, 1500.00, 1200.00, NULL, 0, 0, NULL, 1, 'Шестой тестовый кворк', 0, 0, '2019-11-26 11:21:35', NULL, NULL, NULL, NULL, 0, 0, 28, NULL, 'new', NULL, NULL, 'new', 643, 1.00, 'kwork', 0, NULL, 0, 0, NULL, 1200.00, 1500.00, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, 1, 0.00),
	(17, 7, 1, 4, 1574756508, 1, 1574756508, 1000.00, 800.00, NULL, 0, 0, NULL, 1, 'Первый тестовый кворк', 0, 0, '2019-11-26 11:21:48', NULL, NULL, NULL, NULL, 0, 0, 61, NULL, 'new', NULL, NULL, 'new', 643, 1.00, 'kwork', 0, NULL, 0, 0, NULL, 800.00, 1000.00, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, 1, 0.00),
	(18, 7, 4, 6, 1574756509, 1, 1574756509, 1999.00, 1599.20, NULL, 0, 0, 1574848613, 1, 'Четвертый тестовый кворк', 0, 0, '2019-11-26 11:21:49', NULL, NULL, NULL, NULL, 1, 0, 38, NULL, 'new', NULL, NULL, 'new', 643, 1.00, 'kwork', 0, NULL, 0, 0, NULL, 1599.20, 1999.00, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, 1, 0.00),
	(19, 7, 2, 4, 1574756510, 4, 1574756511, 1500.00, 1200.00, NULL, 0, 0, 1574848864, 1, 'Второй тестовый кворк срочность', 0, 0, '2019-11-26 11:21:51', NULL, NULL, '2019-11-26 13:01:07', NULL, 1, 0, 58, NULL, 'new', NULL, NULL, 'new', 643, 1.00, 'kwork', 0, NULL, 0, 0, NULL, 1200.00, 1500.00, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, 1, 0.00),
	(20, 7, 9, 9, 1574756512, 1, 1574756512, 500.00, 400.00, NULL, 0, 0, NULL, 1, 'Девятый тестовый кворк', 0, 0, '2019-11-26 11:21:52', NULL, NULL, NULL, NULL, 0, 0, 10, NULL, 'new', NULL, NULL, 'new', 643, 1.00, 'kwork', 0, NULL, 0, 0, NULL, 400.00, 500.00, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, 1, 0.00),
	(21, 8, 2, 4, 1574756524, 3, 1574756524, 3000.00, 2400.00, NULL, 86400, 0, 1574848871, 1, 'Второй тестовый кворк срочность', 0, 0, '2019-11-26 11:22:04', NULL, '2019-11-26 13:01:19', NULL, NULL, 1, 0, 60, NULL, 'new', NULL, 1, 'new', 643, 1.00, 'kwork', 0, NULL, 0, 0, NULL, 2400.00, 3000.00, 0.00, 0.00, 0, 0, 0, 0.00, 0, 86400, 1, 0.00),
	(22, 8, 5, 7, 1574756525, 1, 1574756525, 2000.00, 1600.00, NULL, 0, 0, 1574848422, 1, 'Пятый тестовый кворк срочность', 0, 0, '2019-11-26 11:22:05', NULL, NULL, NULL, NULL, 1, 0, 25, NULL, 'new', NULL, NULL, 'new', 643, 1.00, 'kwork', 0, NULL, 0, 0, NULL, 1600.00, 2000.00, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, 1, 0.00),
	(23, 8, 1, 4, 1574756527, 1, 1574756527, 1000.00, 800.00, NULL, 0, 0, 1574854230, 1, 'Первый тестовый кворк', 0, 0, '2019-11-26 11:22:07', NULL, NULL, NULL, NULL, 0, 0, 117, NULL, 'new', NULL, NULL, 'new', 643, 1.00, 'kwork', 0, NULL, 0, 0, NULL, 800.00, 1000.00, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, 1, 0.00),
	(24, 9, 3, 6, 1574756573, 4, 1574756574, 3500.00, 2800.00, NULL, 0, 0, 1574848589, 1, 'Третий тестовый кворк', 0, 0, '2019-11-26 11:22:54', NULL, NULL, '2019-11-26 12:56:34', NULL, 1, 1, 36, NULL, 'new', '4297f44b13955235245b2497399d7a93', NULL, 'new', 643, 1.00, 'kwork', 0, NULL, 0, 0, NULL, 2800.00, 3500.00, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, 1, 0.00),
	(25, 9, 4, 6, 1574756575, 3, 1574756575, 1999.00, 1599.20, NULL, 0, 0, NULL, 1, 'Четвертый тестовый кворк', 0, 0, '2019-11-26 11:22:55', NULL, '2019-11-26 12:56:45', NULL, NULL, 0, 0, 37, NULL, 'new', NULL, NULL, 'new', 643, 1.00, 'kwork', 0, NULL, 0, 0, NULL, 1599.20, 1999.00, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, 1, 0.00),
	(26, 9, 7, 8, 1574756578, 4, 1574756578, 500.00, 400.00, NULL, 0, 0, 1574848638, 1, 'Седьмой тестовый кворк', 0, 0, '2019-11-26 11:22:58', NULL, NULL, '2019-11-26 12:57:21', NULL, 1, 0, 40, NULL, 'new', NULL, NULL, 'new', 643, 1.00, 'kwork', 0, NULL, 0, 0, NULL, 400.00, 500.00, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, 1, 0.00),
	(27, 9, 6, 7, 1574756579, 3, 1574756579, 1500.00, 1200.00, NULL, 0, 0, NULL, 1, 'Шестой тестовый кворк', 0, 0, '2019-11-26 11:22:59', NULL, '2019-11-26 12:55:01', NULL, NULL, 0, 0, 30, NULL, 'new', NULL, NULL, 'new', 643, 1.00, 'kwork', 0, NULL, 0, 0, NULL, 1200.00, 1500.00, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, 1, 0.00),
	(28, 4, 3, 6, 1574756602, 1, 1574756602, 3500.00, 2800.00, NULL, 0, 0, NULL, 1, 'Третий тестовый кворк', 0, 0, '2019-11-26 11:23:22', NULL, NULL, NULL, NULL, 0, 0, 18, NULL, 'new', NULL, NULL, 'new', 643, 1.00, 'kwork', 0, NULL, 0, 0, NULL, 2800.00, 3500.00, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, 1, 0.00),
	(29, 4, 5, 7, 1574756603, 5, 1574756603, 2000.00, 1600.00, NULL, 0, 0, 1574848420, 1, 'Пятый тестовый кворк срочность', 0, 543, '2019-11-26 11:23:23', NULL, NULL, NULL, '2019-11-26 13:02:43', 1, 0, 63, NULL, 'new', NULL, NULL, 'new', 643, 1.00, 'kwork', 0, NULL, 0, 0, NULL, 1600.00, 2000.00, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, 1, 0.00),
	(30, 4, 8, 8, 1574756604, 1, 1574756604, 1000.00, 800.00, NULL, 0, 0, 1574843131, 1, 'Восьмой тестовый кворк', 0, 0, '2019-11-26 11:23:24', NULL, NULL, NULL, NULL, 0, 1, 20, NULL, 'new', '527a540e3009fcddf5a9707781ad3fc9', NULL, 'new', 643, 1.00, 'kwork', 0, NULL, 0, 0, NULL, 800.00, 1000.00, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, 1, 0.00),
	(31, 4, 6, 7, 1574756723, 4, 1574756723, 1500.00, 1200.00, NULL, 0, 0, 1574848431, 1, 'Шестой тестовый кворк', 0, 0, '2019-11-26 11:25:23', NULL, NULL, '2019-11-26 12:53:55', NULL, 1, 0, 27, NULL, 'new', NULL, NULL, 'new', 643, 1.00, 'kwork', 0, NULL, 0, 0, NULL, 1200.00, 1500.00, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, 1, 0.00),
	(32, 9, 3, 6, 1574762316, 5, 1574762317, 3500.00, 2800.00, NULL, 0, 0, 1574848721, 1, 'Третий тестовый кворк', 0, 3, '2019-11-26 12:58:37', NULL, NULL, NULL, '2019-11-26 12:58:44', 0, 1, 44, NULL, 'new', 'c6dd60a67f164c8a38cf909467b7415a', NULL, 'new', 643, 1.00, 'kwork', 0, NULL, 0, 0, NULL, 2800.00, 3500.00, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, 1, 0.00),
	(33, 9, 8, 8, 1574762332, 5, 1574762332, 1000.00, 800.00, NULL, 0, 0, 1574848737, 1, 'Восьмой тестовый кворк', 0, 7, '2019-11-26 12:58:52', NULL, NULL, NULL, '2019-11-26 12:59:04', 0, 1, 47, NULL, 'new', '2e0aca891f2a8aedf265edf533a6d9a8', NULL, 'new', 643, 1.00, 'kwork', 0, NULL, 0, 0, NULL, 800.00, 1000.00, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, 1, 0.00),
	(34, 8, 9, 9, 1574762380, 5, 1574762380, 500.00, 400.00, NULL, 0, 0, 1574848785, 1, 'Девятый тестовый кворк', 0, 5, '2019-11-26 12:59:40', NULL, NULL, NULL, '2019-11-26 12:59:50', 0, 1, 51, NULL, 'new', 'd1c07866d71dc3a09b3b692d0a2086b4', NULL, 'new', 643, 1.00, 'kwork', 0, NULL, 0, 0, NULL, 400.00, 500.00, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, 1, 0.00),
	(35, 8, 10, 9, 1574762381, 3, 1574762381, 1000.00, 800.00, NULL, 0, 0, 1574848793, 1, 'Десятый тестовый кворк', 0, 0, '2019-11-26 12:59:41', NULL, '2019-11-26 12:59:55', NULL, NULL, 0, 1, 53, NULL, 'new', '860b432652504fa60f8da945398e20de', NULL, 'new', 643, 1.00, 'kwork', 0, NULL, 0, 0, NULL, 800.00, 1000.00, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, 1, 0.00),
	(36, 8, 10, 9, 1574762398, 4, 1574762398, 1000.00, 800.00, NULL, 0, 0, 1574848804, 1, 'Десятый тестовый кворк', 0, 0, '2019-11-26 12:59:58', NULL, NULL, '2019-11-26 13:00:07', NULL, 1, 0, 56, NULL, 'new', NULL, NULL, 'new', 643, 1.00, 'kwork', 0, NULL, 0, 0, NULL, 800.00, 1000.00, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, 1, 0.00),
	(37, 8, 1, 4, 1574762889, 5, 1574762889, 1000.00, 800.00, NULL, 0, 0, 1574849301, 1, 'Первый тестовый кворк', 0, 11, '2019-11-26 13:08:09', NULL, NULL, '2019-11-26 13:08:24', '2019-11-26 13:08:34', 1, 1, 68, NULL, 'new', 'fd45ebc1e1d76bc1fe0ba933e60e9957', NULL, 'new', 643, 1.00, 'kwork', 0, NULL, 0, 0, NULL, 800.00, 1000.00, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, 1, 0.00),
	(38, 8, 4, 6, 1574762939, 5, 1574762939, 1999.00, 1599.20, NULL, 0, 0, 1574849394, 1, 'Четвертый тестовый кворк', 0, 39, '2019-11-26 13:09:48', NULL, NULL, '2019-11-26 13:09:57', '2019-11-26 13:10:01', 1, 1, 79, NULL, 'new', '4297f44b13955235245b2497399d7a93', NULL, 'new', 643, 1.00, 'kwork', 0, NULL, 0, 0, NULL, 1599.20, 1999.00, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, 1, 0.00),
	(39, 8, 6, 7, 1574762940, 5, 1574762940, 1500.00, 1200.00, NULL, 0, 0, 1574849423, 1, 'Шестой тестовый кворк', 0, 80, '2019-11-26 13:09:00', NULL, NULL, '2019-11-26 13:10:29', '2019-11-26 13:10:38', 1, 1, 82, NULL, 'new', '4297f44b13955235245b2497399d7a93', NULL, 'new', 643, 1.00, 'kwork', 0, NULL, 0, 0, NULL, 1200.00, 1500.00, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, 1, 0.00),
	(40, 8, 10, 9, 1574762942, 5, 1574762942, 1000.00, 800.00, NULL, 0, 0, 1574849463, 1, 'Десятый тестовый кворк', 0, 120, '2019-11-26 13:09:02', NULL, NULL, '2019-11-26 13:11:06', '2019-11-26 13:12:02', 1, 1, 86, NULL, 'new', '4671aeaf49c792689533b00664a5c3ef', NULL, 'new', 643, 1.00, 'kwork', 0, NULL, 0, 0, NULL, 800.00, 1000.00, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, 1, 0.00),
	(41, 9, 8, 8, 1574763115, 5, 1574763115, 1000.00, 800.00, NULL, 0, 0, 1574849532, 1, 'Восьмой тестовый кворк', 0, 13, '2019-11-26 13:11:55', NULL, NULL, '2019-11-26 13:12:19', '2019-11-26 13:12:29', 1, 1, 90, NULL, 'new', '7f725650f4fdec0cc8d4099bb7c8b9d4', NULL, 'new', 643, 1.00, 'kwork', 0, NULL, 0, 0, NULL, 800.00, 1000.00, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, 1, 0.00),
	(42, 4, 22, 5, 1574766824, 3, 1574766824, 1000.00, 800.00, NULL, 0, 0, NULL, 1, 'Одиннадцатый тестовый кворк', 0, 0, '2019-11-26 14:13:44', NULL, '2019-11-26 14:14:34', NULL, NULL, 0, 0, 92, NULL, 'new', NULL, NULL, 'new', 643, 1.00, 'kwork', 0, NULL, 0, 0, NULL, 800.00, 1000.00, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, 1, 0.00),
	(43, 4, 22, 5, 1574766885, 5, 1574766885, 1000.00, 800.00, NULL, 0, 0, 1574853301, 1, 'Одиннадцатый тестовый кворк', 0, 19, '2019-11-26 14:14:45', NULL, NULL, '2019-11-26 14:15:12', '2019-11-26 14:16:37', 1, 1, 97, NULL, 'new', 'a7fd9bee17172ede11112e463b50d260', NULL, 'new', 643, 1.00, 'kwork', 0, NULL, 0, 0, NULL, 800.00, 1000.00, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, 1, 0.00),
	(44, 4, 22, 5, 1574767150, 5, 1574767150, 1000.00, 800.00, NULL, 0, 0, 1574853911, 1, 'Одиннадцатый тестовый кворк', 0, 241, '2019-11-26 14:24:51', NULL, NULL, '2019-11-26 14:25:12', '2019-11-26 14:26:05', 1, 1, 108, NULL, 'new', 'cfefa9801326140c0efa99a767b82e76', NULL, 'new', 643, 1.00, 'kwork', 0, NULL, 0, 0, NULL, 800.00, 1000.00, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, 1, 0.00),
	(45, 4, 22, 5, 1574767578, 1, 1574767578, 1000.00, 800.00, NULL, 0, 0, 1574853991, 1, 'Одиннадцатый тестовый кворк', 0, 0, '2019-11-26 14:26:18', NULL, NULL, NULL, NULL, 1, 1, 111, NULL, 'new', 'a472063042d48e31d045907d8e8599cc', NULL, 'new', 643, 1.00, 'kwork', 0, NULL, 0, 0, NULL, 800.00, 1000.00, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, 1, 0.00),
	(46, 4, 22, 5, 1574767630, 4, 1574767630, 1000.00, 800.00, NULL, 0, 0, 1574854120, 1, 'Одиннадцатый тестовый кворк', 0, 0, '2019-11-26 14:27:10', NULL, NULL, '2019-11-26 14:28:43', NULL, 1, 1, 115, NULL, 'new', '4297f44b13955235245b2497399d7a93', NULL, 'new', 643, 1.00, 'kwork', 0, NULL, 0, 0, NULL, 800.00, 1000.00, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, 1, 0.00),
	(47, 4, 22, 5, 1574767827, 1, 1574767827, 1000.00, 800.00, NULL, 0, 0, 1574854247, 1, 'Одиннадцатый тестовый кворк', 0, 0, '2019-11-26 14:30:42', NULL, NULL, '2019-11-26 14:30:37', NULL, 1, 1, 122, NULL, 'new', 'cd6a1a15421189de23d7309feebff8d7', NULL, 'new', 643, 1.00, 'kwork', 0, NULL, 0, 0, NULL, 800.00, 1000.00, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, 1, 0.00),
	(48, 4, 22, 5, 1574767992, 1, 1574767992, 1000.00, 800.00, NULL, 0, 0, NULL, 1, 'Одиннадцатый тестовый кворк', 0, 0, '2019-11-26 14:33:12', NULL, NULL, NULL, NULL, 0, 0, 123, NULL, 'new', NULL, NULL, 'new', 643, 1.00, 'kwork', 0, NULL, 0, 0, NULL, 800.00, 1000.00, 0.00, 0.00, 0, 0, 0, 0.00, 0, 0, 1, 0.00);
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;

-- Дамп структуры для таблица test_stand.orders_data
CREATE TABLE IF NOT EXISTS `orders_data` (
  `order_id` int(11) NOT NULL,
  `kwork_desc` varchar(8192) NOT NULL,
  `kwork_category` int(11) DEFAULT NULL,
  `kwork_price` decimal(9,2) DEFAULT NULL,
  `kwork_ctp` decimal(9,2) DEFAULT NULL,
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дамп данных таблицы test_stand.orders_data: ~41 rows (приблизительно)
/*!40000 ALTER TABLE `orders_data` DISABLE KEYS */;
INSERT INTO `orders_data` (`order_id`, `kwork_desc`, `kwork_category`, `kwork_price`, `kwork_ctp`) VALUES
	(1, '&lt;p&gt;Первый тестовый кворк Первый тестовый кворк Первый тестовый кворк Первый тестовый кворк Первый тестовый кворк Первый тестовый кворк&lt;/p&gt;', 1, 1000.00, 200.00),
	(2, '&lt;p&gt;Третий тестовый кворк Третий тестовый кворк Третий тестовый кворк Третий тестовый кворк Третий тестовый кворк&lt;/p&gt;', 1, 3500.00, 700.00),
	(3, '&lt;p&gt;Предложение на первый проект Предложение на первый проект Предложение на первый проект Предложение на первый проект Предложение на первый проект Предложение на первый проект&lt;/p&gt;', 1, 500.00, 100.00),
	(4, '&lt;p&gt;Предложение на третий проект Предложение на третий проект Предложение на третий проект Предложение на третий проект Предложение на третий проект Предложение на третий проект&lt;/p&gt;', 1, 1500.00, 300.00),
	(5, '&lt;p&gt;Предложение на шестой проект Предложение на шестой проект Предложение на шестой проект Предложение на шестой проект Предложение на шестой проект Предложение на шестой проект Предложение на шестой проект&lt;/p&gt;', 1, 1500.00, 300.00),
	(6, '&lt;p&gt;Предложение на восьмой проект Предложение на восьмой проект Предложение на восьмой проект Предложение на восьмой проект Предложение на восьмой проект Предложение на восьмой проект&lt;/p&gt;', 1, 1500.00, 300.00),
	(7, '&lt;p&gt;Предложение на второй проект Предложение на второй проект Предложение на второй проект Предложение на второй проект Предложение на второй проект Предложение на второй проект&lt;/p&gt;', 1, 1000.00, 200.00),
	(8, '&lt;p&gt;Предложение на десятый проект Предложение на десятый проект Предложение на десятый проект Предложение на десятый проект Предложение на десятый проект Предложение на десятый проект&lt;/p&gt;', 1, 500.00, 100.00),
	(9, '&lt;p&gt;Предложение на пятый проект Предложение на пятый проект Предложение на пятый проект Предложение на пятый проект Предложение на пятый проект Предложение на пятый проект&lt;/p&gt;', 1, 2000.00, 400.00),
	(10, '&lt;p&gt;Предложение на второй проект Предложение на второй проект Предложение на второй проект Предложение на второй проект Предложение на второй проект Предложение на второй проект&lt;/p&gt;', 1, 600.00, 120.00),
	(11, '&lt;p&gt;Предложение на второй проект Предложение на второй проект Предложение на второй проект Предложение на второй проект Предложение на второй проект Предложение на второй проект&lt;/p&gt;', 1, 1000.00, 200.00),
	(12, '&lt;p&gt;Предложение на пятый проект Предложение на пятый проект Предложение на пятый проект Предложение на пятый проект Предложение на пятый проект Предложение на пятый проект&lt;/p&gt;', 1, 3000.00, 600.00),
	(13, '&lt;p&gt;Предложение на девятый проект Предложение на девятый проект Предложение на девятый проект Предложение на девятый проект Предложение на девятый проект Предложение на девятый проект&lt;/p&gt;', 1, 500.00, 100.00),
	(14, '&lt;p&gt;Второй тестовый кворк Второй тестовый кворк Второй тестовый кворк Второй тестовый кворк Второй тестовый кворк&lt;/p&gt;', 1, 1500.00, 300.00),
	(15, '&lt;p&gt;Восьмой тестовый кворк Восьмой тестовый кворк Восьмой тестовый кворк Восьмой тестовый кворк Восьмой тестовый кворк&lt;/p&gt;', 1, 1000.00, 200.00),
	(16, '&lt;p&gt;Шестой тестовый кворк Шестой тестовый кворк Шестой тестовый кворк Шестой тестовый кворк Шестой тестовый кворк&lt;/p&gt;', 1, 1500.00, 300.00),
	(17, '&lt;p&gt;Первый тестовый кворк Первый тестовый кворк Первый тестовый кворк Первый тестовый кворк Первый тестовый кворк Первый тестовый кворк&lt;/p&gt;', 1, 1000.00, 200.00),
	(18, '&lt;p&gt;Четвертый тестовый кворк Четвертый тестовый кворк Четвертый тестовый кворк Четвертый тестовый кворк Четвертый тестовый кворк&lt;/p&gt;', 1, 1999.00, 399.80),
	(19, '&lt;p&gt;Второй тестовый кворк Второй тестовый кворк Второй тестовый кворк Второй тестовый кворк Второй тестовый кворк&lt;/p&gt;', 1, 1500.00, 300.00),
	(20, '&lt;p&gt;Девятый тестовый кворк Девятый тестовый кворк Девятый тестовый кворк Девятый тестовый кворк Девятый тестовый кворк Девятый тестовый кворк&lt;/p&gt;', 1, 500.00, 100.00),
	(21, '&lt;p&gt;Второй тестовый кворк Второй тестовый кворк Второй тестовый кворк Второй тестовый кворк Второй тестовый кворк&lt;/p&gt;', 1, 1500.00, 300.00),
	(22, '&lt;p&gt;Пятый тестовый кворк срочность Пятый тестовый кворк срочность Пятый тестовый кворк срочность Пятый тестовый кворк срочность Пятый тестовый кворк срочность&lt;/p&gt;', 1, 2000.00, 400.00),
	(23, '&lt;p&gt;Первый тестовый кворк Первый тестовый кворк Первый тестовый кворк Первый тестовый кворк Первый тестовый кворк Первый тестовый кворк&lt;/p&gt;', 1, 1000.00, 200.00),
	(24, '&lt;p&gt;Третий тестовый кворк Третий тестовый кворк Третий тестовый кворк Третий тестовый кворк Третий тестовый кворк&lt;/p&gt;', 1, 3500.00, 700.00),
	(25, '&lt;p&gt;Четвертый тестовый кворк Четвертый тестовый кворк Четвертый тестовый кворк Четвертый тестовый кворк Четвертый тестовый кворк&lt;/p&gt;', 1, 1999.00, 399.80),
	(26, '&lt;p&gt;Седьмой тестовый кворк Седьмой тестовый кворк Седьмой тестовый кворк Седьмой тестовый кворк Седьмой тестовый кворк&lt;/p&gt;', 1, 500.00, 100.00),
	(27, '&lt;p&gt;Шестой тестовый кворк Шестой тестовый кворк Шестой тестовый кворк Шестой тестовый кворк Шестой тестовый кворк&lt;/p&gt;', 1, 1500.00, 300.00),
	(28, '&lt;p&gt;Третий тестовый кворк Третий тестовый кворк Третий тестовый кворк Третий тестовый кворк Третий тестовый кворк&lt;/p&gt;', 1, 3500.00, 700.00),
	(29, '&lt;p&gt;Пятый тестовый кворк срочность Пятый тестовый кворк срочность Пятый тестовый кворк срочность Пятый тестовый кворк срочность Пятый тестовый кворк срочность&lt;/p&gt;', 1, 2000.00, 400.00),
	(30, '&lt;p&gt;Восьмой тестовый кворк Восьмой тестовый кворк Восьмой тестовый кворк Восьмой тестовый кворк Восьмой тестовый кворк&lt;/p&gt;', 1, 1000.00, 200.00),
	(31, '&lt;p&gt;Шестой тестовый кворк Шестой тестовый кворк Шестой тестовый кворк Шестой тестовый кворк Шестой тестовый кворк&lt;/p&gt;', 1, 1500.00, 300.00),
	(32, '&lt;p&gt;Третий тестовый кворк Третий тестовый кворк Третий тестовый кворк Третий тестовый кворк Третий тестовый кворк&lt;/p&gt;', 1, 3500.00, 700.00),
	(33, '&lt;p&gt;Восьмой тестовый кворк Восьмой тестовый кворк Восьмой тестовый кворк Восьмой тестовый кворк Восьмой тестовый кворк&lt;/p&gt;', 1, 1000.00, 200.00),
	(34, '&lt;p&gt;Девятый тестовый кворк Девятый тестовый кворк Девятый тестовый кворк Девятый тестовый кворк Девятый тестовый кворк Девятый тестовый кворк&lt;/p&gt;', 1, 500.00, 100.00),
	(35, '&lt;p&gt;Десятый тестовый кворк Десятый тестовый кворк Десятый тестовый кворк Десятый тестовый кворк Десятый тестовый кворк&lt;/p&gt;', 1, 1000.00, 200.00),
	(36, '&lt;p&gt;Десятый тестовый кворк Десятый тестовый кворк Десятый тестовый кворк Десятый тестовый кворк Десятый тестовый кворк&lt;/p&gt;', 1, 1000.00, 200.00),
	(37, '&lt;p&gt;Первый тестовый кворк Первый тестовый кворк Первый тестовый кворк Первый тестовый кворк Первый тестовый кворк Первый тестовый кворк&lt;/p&gt;', 1, 1000.00, 200.00),
	(38, '&lt;p&gt;Четвертый тестовый кворк Четвертый тестовый кворк Четвертый тестовый кворк Четвертый тестовый кворк Четвертый тестовый кворк&lt;/p&gt;', 1, 1999.00, 399.80),
	(39, '&lt;p&gt;Шестой тестовый кворк Шестой тестовый кворк Шестой тестовый кворк Шестой тестовый кворк Шестой тестовый кворк&lt;/p&gt;', 1, 1500.00, 300.00),
	(40, '&lt;p&gt;Десятый тестовый кворк Десятый тестовый кворк Десятый тестовый кворк Десятый тестовый кворк Десятый тестовый кворк&lt;/p&gt;', 1, 1000.00, 200.00),
	(41, '&lt;p&gt;Восьмой тестовый кворк Восьмой тестовый кворк Восьмой тестовый кворк Восьмой тестовый кворк Восьмой тестовый кворк&lt;/p&gt;', 1, 1000.00, 200.00),
	(42, '&lt;p&gt;Одиннадцатый тестовый кворк Одиннадцатый тестовый кворк Одиннадцатый тестовый кворк Одиннадцатый тестовый кворк Одиннадцатый тестовый кворк&lt;/p&gt;', 1, 1000.00, 200.00),
	(43, '&lt;p&gt;Одиннадцатый тестовый кворк Одиннадцатый тестовый кворк Одиннадцатый тестовый кворк Одиннадцатый тестовый кворк Одиннадцатый тестовый кворк&lt;/p&gt;', 1, 1000.00, 200.00),
	(44, '&lt;p&gt;Одиннадцатый тестовый кворк Одиннадцатый тестовый кворк Одиннадцатый тестовый кворк Одиннадцатый тестовый кворк Одиннадцатый тестовый кворк&lt;/p&gt;', 1, 1000.00, 200.00),
	(45, '&lt;p&gt;Одиннадцатый тестовый кворк Одиннадцатый тестовый кворк Одиннадцатый тестовый кворк Одиннадцатый тестовый кворк Одиннадцатый тестовый кворк&lt;/p&gt;', 1, 1000.00, 200.00),
	(46, '&lt;p&gt;Одиннадцатый тестовый кворк Одиннадцатый тестовый кворк Одиннадцатый тестовый кворк Одиннадцатый тестовый кворк Одиннадцатый тестовый кворк&lt;/p&gt;', 1, 1000.00, 200.00),
	(47, '&lt;p&gt;Одиннадцатый тестовый кворк Одиннадцатый тестовый кворк Одиннадцатый тестовый кворк Одиннадцатый тестовый кворк Одиннадцатый тестовый кворк&lt;/p&gt;', 1, 1000.00, 200.00),
	(48, '&lt;p&gt;Одиннадцатый тестовый кворк Одиннадцатый тестовый кворк Одиннадцатый тестовый кворк Одиннадцатый тестовый кворк Одиннадцатый тестовый кворк&lt;/p&gt;', 1, 1000.00, 200.00);
/*!40000 ALTER TABLE `orders_data` ENABLE KEYS */;

-- Дамп структуры для таблица test_stand.order_log
CREATE TABLE IF NOT EXISTS `order_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `status` int(11) NOT NULL,
  `date_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id_idx` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы test_stand.order_log: ~86 rows (приблизительно)
/*!40000 ALTER TABLE `order_log` DISABLE KEYS */;
INSERT INTO `order_log` (`id`, `order_id`, `user_id`, `status`, `date_create`) VALUES
	(1, 1, 9, 0, '2019-11-26 11:03:27'),
	(2, 1, 9, 1, '2019-11-26 11:03:27'),
	(3, 2, 9, 0, '2019-11-26 11:14:56'),
	(4, 2, 9, 1, '2019-11-26 11:14:56'),
	(5, 14, 6, 0, '2019-11-26 11:21:31'),
	(6, 14, 6, 1, '2019-11-26 11:21:31'),
	(7, 15, 6, 0, '2019-11-26 11:21:34'),
	(8, 15, 6, 1, '2019-11-26 11:21:34'),
	(9, 16, 6, 0, '2019-11-26 11:21:35'),
	(10, 16, 6, 1, '2019-11-26 11:21:35'),
	(11, 17, 7, 0, '2019-11-26 11:21:48'),
	(12, 17, 7, 1, '2019-11-26 11:21:48'),
	(13, 18, 7, 0, '2019-11-26 11:21:49'),
	(14, 18, 7, 1, '2019-11-26 11:21:49'),
	(15, 19, 7, 0, '2019-11-26 11:21:50'),
	(16, 19, 7, 1, '2019-11-26 11:21:51'),
	(17, 20, 7, 0, '2019-11-26 11:21:52'),
	(18, 20, 7, 1, '2019-11-26 11:21:52'),
	(19, 21, 8, 0, '2019-11-26 11:22:04'),
	(20, 21, 8, 1, '2019-11-26 11:22:04'),
	(21, 22, 8, 0, '2019-11-26 11:22:05'),
	(22, 22, 8, 1, '2019-11-26 11:22:05'),
	(23, 23, 8, 0, '2019-11-26 11:22:07'),
	(24, 23, 8, 1, '2019-11-26 11:22:07'),
	(25, 24, 9, 0, '2019-11-26 11:22:53'),
	(26, 24, 9, 1, '2019-11-26 11:22:54'),
	(27, 25, 9, 0, '2019-11-26 11:22:55'),
	(28, 25, 9, 1, '2019-11-26 11:22:55'),
	(29, 26, 9, 0, '2019-11-26 11:22:58'),
	(30, 26, 9, 1, '2019-11-26 11:22:58'),
	(31, 27, 9, 0, '2019-11-26 11:22:59'),
	(32, 27, 9, 1, '2019-11-26 11:22:59'),
	(33, 28, 4, 0, '2019-11-26 11:23:22'),
	(34, 28, 4, 1, '2019-11-26 11:23:22'),
	(35, 29, 4, 0, '2019-11-26 11:23:23'),
	(36, 29, 4, 1, '2019-11-26 11:23:23'),
	(37, 30, 4, 0, '2019-11-26 11:23:24'),
	(38, 30, 4, 1, '2019-11-26 11:23:24'),
	(39, 31, 4, 0, '2019-11-26 11:25:23'),
	(40, 31, 4, 1, '2019-11-26 11:25:23'),
	(41, 31, 7, 4, '2019-11-26 12:53:55'),
	(42, 27, 9, 3, '2019-11-26 12:55:01'),
	(43, 1, 9, 5, '2019-11-26 12:55:23'),
	(44, 24, 6, 4, '2019-11-26 12:56:34'),
	(45, 25, 6, 3, '2019-11-26 12:56:45'),
	(46, 26, 8, 4, '2019-11-26 12:57:21'),
	(47, 32, 9, 0, '2019-11-26 12:58:36'),
	(48, 32, 9, 1, '2019-11-26 12:58:37'),
	(49, 32, 9, 5, '2019-11-26 12:58:44'),
	(50, 33, 9, 0, '2019-11-26 12:58:52'),
	(51, 33, 9, 1, '2019-11-26 12:58:52'),
	(52, 33, 9, 5, '2019-11-26 12:59:04'),
	(53, 34, 8, 0, '2019-11-26 12:59:40'),
	(54, 34, 8, 1, '2019-11-26 12:59:40'),
	(55, 35, 8, 0, '2019-11-26 12:59:41'),
	(56, 35, 8, 1, '2019-11-26 12:59:41'),
	(57, 34, 8, 5, '2019-11-26 12:59:50'),
	(58, 35, 8, 3, '2019-11-26 12:59:55'),
	(59, 36, 8, 0, '2019-11-26 12:59:58'),
	(60, 36, 8, 1, '2019-11-26 12:59:58'),
	(61, 36, 9, 4, '2019-11-26 13:00:07'),
	(62, 19, 4, 4, '2019-11-26 13:01:07'),
	(63, 21, 4, 3, '2019-11-26 13:01:19'),
	(64, 29, 4, 5, '2019-11-26 13:02:43'),
	(65, 37, 8, 0, '2019-11-26 13:08:09'),
	(66, 37, 8, 1, '2019-11-26 13:08:09'),
	(67, 37, 4, 4, '2019-11-26 13:08:24'),
	(68, 37, 8, 5, '2019-11-26 13:08:34'),
	(69, 38, 8, 0, '2019-11-26 13:08:59'),
	(70, 38, 8, 1, '2019-11-26 13:08:59'),
	(71, 39, 8, 0, '2019-11-26 13:09:00'),
	(72, 39, 8, 1, '2019-11-26 13:09:00'),
	(73, 40, 8, 0, '2019-11-26 13:09:02'),
	(74, 40, 8, 1, '2019-11-26 13:09:02'),
	(75, 38, 6, 4, '2019-11-26 13:09:42'),
	(76, 38, 8, 1, '2019-11-26 13:09:48'),
	(77, 38, 6, 4, '2019-11-26 13:09:57'),
	(78, 38, 8, 5, '2019-11-26 13:10:01'),
	(79, 39, 7, 4, '2019-11-26 13:10:29'),
	(80, 39, 8, 5, '2019-11-26 13:10:38'),
	(81, 40, 9, 4, '2019-11-26 13:11:06'),
	(82, 41, 9, 0, '2019-11-26 13:11:55'),
	(83, 41, 9, 1, '2019-11-26 13:11:55'),
	(84, 40, 8, 5, '2019-11-26 13:12:03'),
	(85, 41, 8, 4, '2019-11-26 13:12:19'),
	(86, 41, 9, 5, '2019-11-26 13:12:29'),
	(87, 42, 4, 0, '2019-11-26 14:13:44'),
	(88, 42, 4, 1, '2019-11-26 14:13:44'),
	(89, 42, 4, 3, '2019-11-26 14:14:34'),
	(90, 43, 4, 0, '2019-11-26 14:14:45'),
	(91, 43, 4, 1, '2019-11-26 14:14:45'),
	(92, 43, 5, 4, '2019-11-26 14:15:12'),
	(93, 43, 4, 5, '2019-11-26 14:16:37'),
	(94, 44, 4, 0, '2019-11-26 14:19:10'),
	(95, 44, 4, 1, '2019-11-26 14:19:10'),
	(96, 44, 5, 4, '2019-11-26 14:24:31'),
	(97, 44, 4, 1, '2019-11-26 14:24:51'),
	(98, 44, 5, 4, '2019-11-26 14:25:12'),
	(99, 44, 4, 5, '2019-11-26 14:26:05'),
	(100, 45, 4, 0, '2019-11-26 14:26:18'),
	(101, 45, 4, 1, '2019-11-26 14:26:18'),
	(102, 46, 4, 0, '2019-11-26 14:27:10'),
	(103, 46, 4, 1, '2019-11-26 14:27:10'),
	(104, 46, 5, 4, '2019-11-26 14:28:43'),
	(105, 47, 4, 0, '2019-11-26 14:30:27'),
	(106, 47, 4, 1, '2019-11-26 14:30:27'),
	(107, 47, 5, 4, '2019-11-26 14:30:37'),
	(108, 47, 4, 1, '2019-11-26 14:30:42'),
	(109, 48, 4, 0, '2019-11-26 14:33:12'),
	(110, 48, 4, 1, '2019-11-26 14:33:12');
/*!40000 ALTER TABLE `order_log` ENABLE KEYS */;

-- Дамп структуры для таблица test_stand.order_names
CREATE TABLE IF NOT EXISTS `order_names` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Таблица содержащая индивидуальные названия заказов';

-- Дамп данных таблицы test_stand.order_names: ~0 rows (приблизительно)
/*!40000 ALTER TABLE `order_names` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_names` ENABLE KEYS */;

-- Дамп структуры для таблица test_stand.posts
CREATE TABLE IF NOT EXISTS `posts` (
  `PID` int(11) NOT NULL AUTO_INCREMENT,
  `USERID` int(11) NOT NULL DEFAULT '0',
  `gtitle` varchar(4096) NOT NULL,
  `gdesc` varchar(8192) NOT NULL,
  `active` int(1) NOT NULL DEFAULT '0',
  `price` decimal(9,2) NOT NULL DEFAULT '0.00',
  `ctp` decimal(9,2) NOT NULL DEFAULT '0.00',
  `rating` int(11) NOT NULL DEFAULT '0',
  `date_active` timestamp NULL DEFAULT NULL,
  `feat` int(1) NOT NULL DEFAULT '0',
  `date_feat` timestamp NULL DEFAULT NULL,
  `date_modify` timestamp NULL DEFAULT NULL,
  `time_added` int(11) DEFAULT NULL,
  `url` varchar(1024) DEFAULT NULL,
  `category` int(11) DEFAULT NULL,
  `is_quick` int(1) NOT NULL DEFAULT '0',
  `viewcount` int(11) NOT NULL DEFAULT '0',
  `lang` varchar(8) NOT NULL DEFAULT 'ru',
  PRIMARY KEY (`PID`),
  KEY `USERID_idx` (`USERID`),
  KEY `active_idx` (`active`),
  KEY `feat_idx` (`feat`),
  KEY `active_feat_idx` (`feat`,`active`),
  KEY `time_added` (`time_added`),
  KEY `PID_active_feat` (`PID`,`active`,`feat`),
  KEY `active_feat_similalweight` (`active`,`feat`),
  KEY `active_feat_category` (`active`,`feat`),
  KEY `active_feat_timeadded` (`active`,`feat`,`time_added`),
  KEY `active_feat_rotationweight` (`active`,`feat`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы test_stand.posts: ~21 rows (приблизительно)
/*!40000 ALTER TABLE `posts` DISABLE KEYS */;
INSERT INTO `posts` (`PID`, `USERID`, `gtitle`, `gdesc`, `active`, `price`, `ctp`, `rating`, `date_active`, `feat`, `date_feat`, `date_modify`, `time_added`, `url`, `category`, `is_quick`, `viewcount`, `lang`) VALUES
	(1, 4, 'Первый тестовый кворк', '&lt;p&gt;Первый тестовый кворк Первый тестовый кворк Первый тестовый кворк Первый тестовый кворк Первый тестовый кворк Первый тестовый кворк&lt;/p&gt;', 1, 1000.00, 200.00, 0, '2019-11-26 11:01:49', 1, NULL, NULL, 1574755309, '/name/1/perviy-testoviy-kvork', 1, 0, 0, 'ru'),
	(2, 4, 'Второй тестовый кворк срочность', '&lt;p&gt;Второй тестовый кворк Второй тестовый кворк Второй тестовый кворк Второй тестовый кворк Второй тестовый кворк&lt;/p&gt;', 1, 1500.00, 300.00, 0, '2019-11-26 11:02:15', 1, NULL, NULL, 1574755335, '/name/2/vtoroy-testoviy-kvork-srochnost', 1, 1, 0, 'ru'),
	(3, 6, 'Третий тестовый кворк', '&lt;p&gt;Третий тестовый кворк Третий тестовый кворк Третий тестовый кворк Третий тестовый кворк Третий тестовый кворк&lt;/p&gt;', 1, 3500.00, 700.00, 0, '2019-11-26 11:03:26', 1, NULL, NULL, 1574755406, '/name/3/tretiy-testoviy-kvork', 1, 0, 0, 'ru'),
	(4, 6, 'Четвертый тестовый кворк', '&lt;p&gt;Четвертый тестовый кворк Четвертый тестовый кворк Четвертый тестовый кворк Четвертый тестовый кворк Четвертый тестовый кворк&lt;/p&gt;', 1, 1999.00, 399.80, 0, '2019-11-26 11:03:45', 1, NULL, NULL, 1574755425, '/name/4/chetvertiy-testoviy-kvork', 1, 0, 0, 'ru'),
	(5, 7, 'Пятый тестовый кворк срочность', '&lt;p&gt;Пятый тестовый кворк срочность Пятый тестовый кворк срочность Пятый тестовый кворк срочность Пятый тестовый кворк срочность Пятый тестовый кворк срочность&lt;/p&gt;', 1, 2000.00, 400.00, 0, '2019-11-26 11:08:48', 1, NULL, NULL, 1574755728, '/name/5/pyatiy-testoviy-kvork-srochnost', 1, 1, 0, 'ru'),
	(6, 7, 'Шестой тестовый кворк', '&lt;p&gt;Шестой тестовый кворк Шестой тестовый кворк Шестой тестовый кворк Шестой тестовый кворк Шестой тестовый кворк&lt;/p&gt;', 1, 1500.00, 300.00, 0, '2019-11-26 11:10:01', 1, NULL, NULL, 1574755801, '/name/6/shestoy-testoviy-kvork', 1, 0, 0, 'ru'),
	(7, 8, 'Седьмой тестовый кворк', '&lt;p&gt;Седьмой тестовый кворк Седьмой тестовый кворк Седьмой тестовый кворк Седьмой тестовый кворк Седьмой тестовый кворк&lt;/p&gt;', 1, 500.00, 100.00, 0, '2019-11-26 11:10:22', 1, NULL, NULL, 1574755822, '/name/7/sedmoy-testoviy-kvork', 1, 0, 0, 'ru'),
	(8, 8, 'Восьмой тестовый кворк', '&lt;p&gt;Восьмой тестовый кворк Восьмой тестовый кворк Восьмой тестовый кворк Восьмой тестовый кворк Восьмой тестовый кворк&lt;/p&gt;', 1, 1000.00, 200.00, 0, '2019-11-26 11:10:38', 1, NULL, NULL, 1574755838, '/name/8/vosmoy-testoviy-kvork', 1, 0, 0, 'ru'),
	(9, 9, 'Девятый тестовый кворк', '&lt;p&gt;Девятый тестовый кворк Девятый тестовый кворк Девятый тестовый кворк Девятый тестовый кворк Девятый тестовый кворк Девятый тестовый кворк&lt;/p&gt;', 1, 500.00, 100.00, 0, '2019-11-26 11:13:30', 1, NULL, NULL, 1574756010, '/name/9/devyatiy-testoviy-kvork', 1, 0, 0, 'ru'),
	(10, 9, 'Десятый тестовый кворк', '&lt;p&gt;Десятый тестовый кворк Десятый тестовый кворк Десятый тестовый кворк Десятый тестовый кворк Десятый тестовый кворк&lt;/p&gt;', 1, 1000.00, 200.00, 0, '2019-11-26 11:14:31', 1, NULL, NULL, 1574756071, '/name/10/desyatiy-testoviy-kvork', 1, 0, 0, 'ru'),
	(11, 9, 'Предложение на первый проект', '&lt;p&gt;Предложение на первый проект Предложение на первый проект Предложение на первый проект Предложение на первый проект Предложение на первый проект Предложение на первый проект&lt;/p&gt;', 6, 500.00, 100.00, 0, '2019-11-26 11:15:33', 1, NULL, NULL, 1574756133, '/name/11/predlozhenie-na-perviy-proekt', 1, 0, 0, 'ru'),
	(12, 9, 'Предложение на третий проект', '&lt;p&gt;Предложение на третий проект Предложение на третий проект Предложение на третий проект Предложение на третий проект Предложение на третий проект Предложение на третий проект&lt;/p&gt;', 6, 1500.00, 300.00, 0, '2019-11-26 11:15:51', 1, NULL, NULL, 1574756151, '/name/12/predlozhenie-na-tretiy-proekt', 1, 0, 0, 'ru'),
	(13, 9, 'Предложение на шестой проект', '&lt;p&gt;Предложение на шестой проект Предложение на шестой проект Предложение на шестой проект Предложение на шестой проект Предложение на шестой проект Предложение на шестой проект Предложение на шестой проект&lt;/p&gt;', 6, 1500.00, 300.00, 0, '2019-11-26 11:16:11', 1, NULL, NULL, 1574756171, '/name/13/predlozhenie-na-shestoy-proekt', 1, 0, 0, 'ru'),
	(14, 9, 'Предложение на восьмой проект', '&lt;p&gt;Предложение на восьмой проект Предложение на восьмой проект Предложение на восьмой проект Предложение на восьмой проект Предложение на восьмой проект Предложение на восьмой проект&lt;/p&gt;', 6, 1500.00, 300.00, 0, '2019-11-26 11:17:18', 1, NULL, NULL, 1574756238, '/name/14/predlozhenie-na-vosmoy-proekt', 1, 0, 0, 'ru'),
	(15, 8, 'Предложение на второй проект', '&lt;p&gt;Предложение на второй проект Предложение на второй проект Предложение на второй проект Предложение на второй проект Предложение на второй проект Предложение на второй проект&lt;/p&gt;', 6, 1000.00, 200.00, 0, '2019-11-26 11:17:34', 1, NULL, NULL, 1574756254, '/name/15/predlozhenie-na-vtoroy-proekt', 1, 0, 0, 'ru'),
	(16, 8, 'Предложение на десятый проект', '&lt;p&gt;Предложение на десятый проект Предложение на десятый проект Предложение на десятый проект Предложение на десятый проект Предложение на десятый проект Предложение на десятый проект&lt;/p&gt;', 6, 500.00, 100.00, 0, '2019-11-26 11:17:57', 1, NULL, NULL, 1574756277, '/name/16/predlozhenie-na-desyatiy-proekt', 1, 0, 0, 'ru'),
	(17, 8, 'Предложение на пятый проект', '&lt;p&gt;Предложение на пятый проект Предложение на пятый проект Предложение на пятый проект Предложение на пятый проект Предложение на пятый проект Предложение на пятый проект&lt;/p&gt;', 6, 2000.00, 400.00, 0, '2019-11-26 11:18:32', 1, NULL, NULL, 1574756312, '/name/17/predlozhenie-na-pyatiy-proekt', 1, 0, 0, 'ru'),
	(18, 6, 'Предложение на второй проект', '&lt;p&gt;Предложение на второй проект Предложение на второй проект Предложение на второй проект Предложение на второй проект Предложение на второй проект Предложение на второй проект&lt;/p&gt;', 6, 600.00, 120.00, 0, '2019-11-26 11:19:36', 1, NULL, NULL, 1574756376, '/name/18/predlozhenie-na-vtoroy-proekt', 1, 0, 0, 'ru'),
	(19, 6, 'Предложение на второй проект', '&lt;p&gt;Предложение на второй проект Предложение на второй проект Предложение на второй проект Предложение на второй проект Предложение на второй проект Предложение на второй проект&lt;/p&gt;', 6, 1000.00, 200.00, 0, '2019-11-26 11:20:39', 1, NULL, NULL, 1574756439, '/name/19/predlozhenie-na-vtoroy-proekt', 1, 0, 0, 'ru'),
	(20, 6, 'Предложение на пятый проект', '&lt;p&gt;Предложение на пятый проект Предложение на пятый проект Предложение на пятый проект Предложение на пятый проект Предложение на пятый проект Предложение на пятый проект&lt;/p&gt;', 6, 3000.00, 600.00, 0, '2019-11-26 11:20:59', 1, NULL, NULL, 1574756459, '/name/20/predlozhenie-na-pyatiy-proekt', 1, 0, 0, 'ru'),
	(21, 6, 'Предложение на девятый проект', '&lt;p&gt;Предложение на девятый проект Предложение на девятый проект Предложение на девятый проект Предложение на девятый проект Предложение на девятый проект Предложение на девятый проект&lt;/p&gt;', 6, 500.00, 100.00, 0, '2019-11-26 11:21:19', 1, NULL, NULL, 1574756479, '/name/21/predlozhenie-na-devyatiy-proekt', 1, 0, 0, 'ru'),
	(22, 5, 'Одиннадцатый тестовый кворк', '&lt;p&gt;Одиннадцатый тестовый кворк Одиннадцатый тестовый кворк Одиннадцатый тестовый кворк Одиннадцатый тестовый кворк Одиннадцатый тестовый кворк&lt;/p&gt;', 1, 1000.00, 200.00, 0, '2019-11-26 14:13:31', 1, NULL, NULL, 1574766811, '/name/22/odinnadtsatiy-testoviy-kvork', 1, 0, 0, 'ru');
/*!40000 ALTER TABLE `posts` ENABLE KEYS */;

-- Дамп структуры для таблица test_stand.ratings
CREATE TABLE IF NOT EXISTS `ratings` (
  `RID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id записи',
  `USERID` int(11) NOT NULL DEFAULT '0' COMMENT 'id продавца',
  `PID` int(11) NOT NULL DEFAULT '0' COMMENT 'id кворка',
  `OID` int(11) NOT NULL DEFAULT '0' COMMENT 'id заказа',
  `RATER` int(11) NOT NULL DEFAULT '0' COMMENT 'id покупателя',
  `time_added` int(11) DEFAULT NULL COMMENT 'дата создания',
  `good` int(1) NOT NULL DEFAULT '0' COMMENT 'положительный ли',
  `bad` int(1) NOT NULL DEFAULT '0' COMMENT 'отрицательный ли',
  `comment` varchar(1000) NOT NULL COMMENT 'текст отзыва',
  `auto_mode` enum('inwork_time_over','time_over','incorrect_execute','arbitrage_payer') DEFAULT NULL COMMENT 'тип отзыва: inwork_time_over - кроном при просрочке заказа, time_over - покупателем при просрочке заказа, incorrect_execute - кроном или продавцом по запросу покупателя на отмену по причине что заказ выполнен некорректно, arbitrage_payer - автоматический негативный отзыв после арбитража в пользу покупателя',
  `time_vote_changed` int(11) DEFAULT NULL,
  `unread` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Прочитал ли отзыв продавец',
  PRIMARY KEY (`RID`),
  KEY `USERID_idx` (`USERID`),
  KEY `PID_idx` (`PID`),
  KEY `OID_idx` (`OID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дамп данных таблицы test_stand.ratings: ~0 rows (приблизительно)
/*!40000 ALTER TABLE `ratings` DISABLE KEYS */;
/*!40000 ALTER TABLE `ratings` ENABLE KEYS */;

-- Дамп структуры для таблица test_stand.ratings_for_display
CREATE TABLE IF NOT EXISTS `ratings_for_display` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Id записи',
  `user_id` int(11) NOT NULL COMMENT 'Id пользователя',
  `rating_id` int(11) DEFAULT NULL COMMENT 'Id отзыва',
  `portfolio_id` int(11) DEFAULT NULL COMMENT 'Id портфолио',
  `is_good` tinyint(1) DEFAULT NULL COMMENT 'Является ли отзыв положительным',
  `currency_id` int(11) NOT NULL COMMENT 'Код валюты',
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата создания',
  PRIMARY KEY (`id`),
  KEY `fk_user_id_idx` (`user_id`),
  CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `members` (`USERID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Отзывы для отображения на странице профиля пользователя';

-- Дамп данных таблицы test_stand.ratings_for_display: ~0 rows (приблизительно)
/*!40000 ALTER TABLE `ratings_for_display` DISABLE KEYS */;
/*!40000 ALTER TABLE `ratings_for_display` ENABLE KEYS */;

-- Дамп структуры для таблица test_stand.rating_comment
CREATE TABLE IF NOT EXISTS `rating_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `message` varchar(1024) NOT NULL,
  `review_id` int(11) NOT NULL,
  `status` enum('new','active','reject') DEFAULT 'new',
  `time_added` int(11) NOT NULL,
  `unread` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Прочитал ли ответ на отзыв покупатель',
  PRIMARY KEY (`id`),
  KEY `review_id_idx` (`review_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дамп данных таблицы test_stand.rating_comment: ~0 rows (приблизительно)
/*!40000 ALTER TABLE `rating_comment` DISABLE KEYS */;
/*!40000 ALTER TABLE `rating_comment` ENABLE KEYS */;

-- Дамп структуры для таблица test_stand.review
CREATE TABLE IF NOT EXISTS `review` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `text` varchar(1024) NOT NULL,
  `date_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type` enum('payer','worker') DEFAULT NULL,
  `status` enum('new','active') DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дамп данных таблицы test_stand.review: ~0 rows (приблизительно)
/*!40000 ALTER TABLE `review` DISABLE KEYS */;
/*!40000 ALTER TABLE `review` ENABLE KEYS */;

-- Дамп структуры для таблица test_stand.tips
CREATE TABLE IF NOT EXISTS `tips` (
  `track_id` int(11) NOT NULL COMMENT 'ID track-записи о переводе бонуса',
  `order_id` int(11) NOT NULL COMMENT 'ID заказа',
  `amount` decimal(9,2) NOT NULL COMMENT 'Сумма бонуса',
  `crt` decimal(9,2) NOT NULL COMMENT 'Сумма бонуса с учётом комиссии',
  `comment` varchar(512) NOT NULL COMMENT 'Комментарий для исполнителя',
  `currency_rate` decimal(9,2) NOT NULL COMMENT 'Курс на момент оплаты',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Время отправки бонуса',
  PRIMARY KEY (`track_id`),
  KEY `fk_tips_2` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дамп данных таблицы test_stand.tips: ~0 rows (приблизительно)
/*!40000 ALTER TABLE `tips` DISABLE KEYS */;
/*!40000 ALTER TABLE `tips` ENABLE KEYS */;

-- Дамп структуры для таблица test_stand.track
CREATE TABLE IF NOT EXISTS `track` (
  `MID` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `OID` int(11) NOT NULL DEFAULT '0',
  `message` varchar(4096) DEFAULT NULL,
  `type` enum('admin_arbitrage_cancel','admin_arbitrage_done','admin_arbitrage_inprogress','admin_cancel_arbitrage','admin_check_cancel','admin_done_arbitrage','admin_inprogress_cancel','create','cron_inprogress_cancel','cron_payer_inprogress_cancel','cron_worker_check_done','cron_worker_inprogress_cancel','extra','instruction','payer_check_arbitrage','payer_check_done','payer_check_inprogress','payer_inprogress_cancel','payer_inprogress_cancel_confirm','payer_inprogress_cancel_delete','payer_inprogress_cancel_reject','payer_inprogress_cancel_request','payer_new_inprogress','text','text_first','worker_check_arbitrage','worker_inprogress_cancel','worker_inprogress_cancel_confirm','worker_inprogress_cancel_delete','worker_inprogress_cancel_reject','worker_inprogress_cancel_request','worker_inprogress_check','worker_inwork','cron_inprogress_inwork_cancel','payer_inprogress_done','payer_extend','payer_inprogress_arbitrage','worker_inprogress_arbitrage','admin_arbitrage_done_half','worker_portfolio','payer_advice','delete_extra','from_dialog','admin_cancel_inprogress','admin_done_inprogress','payer_upgrade_package','worker_report_deadline_cancel','worker_report_new','payer_send_tips','payer_inprogress_add_option','payer_reject_stages','payer_reject_stages_inprogress','payer_approve_stages','payer_approve_stages_inprogress','cron_check_approve_stage','cron_check_approve_stage_inprogress','stage_unpaid','cron_unpaid_cancel','admin_arbitrage_stage_continue','admin_arbitrage_stage_cancel','admin_arbitrage_stage_done','payer_done_inprogress','payer_cancel_inprogress','cron_restarted_inprogress_cancel','payer_unpaid_inprogress','admin_arbitrage_check','payer_done_inprogress_unpaid','payer_stage_paid') NOT NULL,
  `status` enum('new','close','done','cancel') NOT NULL DEFAULT 'new',
  `date_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_update` timestamp NULL DEFAULT NULL,
  `reason_type` enum('worker_bad_payer_requirements','worker_payer_ordered_by_mistake','worker_no_communication_with_payer','worker_payer_is_dissatisfied','worker_force_cancel','payer_do_not_like_this_worker','payer_ordered_by_mistake','payer_time_over','payer_no_communication_with_worker','payer_other','worker_no_time','worker_no_payer_requirements','payer_worker_is_busy','payer_worker_cannot_execute_correct','payer_other_no_guilt','worker_bad_payer_requirements-too_much_work','worker_bad_payer_requirements-work_doesnt_match_kwork','payer_inflated_price','worker_disagree_stages') DEFAULT NULL,
  `prev_reason_type` enum('worker_bad_payer_requirements','worker_payer_ordered_by_mistake','worker_no_communication_with_payer','worker_payer_is_dissatisfied','worker_force_cancel','payer_do_not_like_this_worker','payer_ordered_by_mistake','payer_time_over','payer_no_communication_with_worker','payer_other','worker_no_time','worker_no_payer_requirements','payer_worker_is_busy','payer_worker_cannot_execute_correct','payer_other_no_guilt','worker_bad_payer_requirements-too_much_work','worker_bad_payer_requirements-work_doesnt_match_kwork','payer_inflated_price','worker_disagree_stages') DEFAULT NULL,
  `reply_type` enum('agree','disagree') DEFAULT NULL,
  `unread` tinyint(1) NOT NULL DEFAULT '1',
  `cron_worker_unread` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Прочитал ли трек продавец (для треков, генерируемых кронами)',
  PRIMARY KEY (`MID`),
  KEY `OID_idx` (`OID`),
  KEY `type_idx` (`type`),
  KEY `user_id_idx` (`user_id`),
  KEY `type_status` (`type`,`status`),
  KEY `OID_type` (`OID`,`type`),
  KEY `reason_type` (`reason_type`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы test_stand.track: ~90 rows (приблизительно)
/*!40000 ALTER TABLE `track` DISABLE KEYS */;
INSERT INTO `track` (`MID`, `user_id`, `OID`, `message`, `type`, `status`, `date_create`, `date_update`, `reason_type`, `prev_reason_type`, `reply_type`, `unread`, `cron_worker_unread`) VALUES
	(1, 9, 1, NULL, 'payer_new_inprogress', 'new', '2019-11-26 11:03:27', NULL, NULL, NULL, NULL, 0, 0),
	(2, 9, 1, 'Информация моя', 'text_first', 'new', '2019-11-26 11:03:37', NULL, NULL, NULL, NULL, 0, 0),
	(3, 9, 2, NULL, 'payer_new_inprogress', 'new', '2019-11-26 11:14:56', NULL, NULL, NULL, NULL, 0, 0),
	(4, 6, 14, NULL, 'payer_new_inprogress', 'new', '2019-11-26 11:21:31', NULL, NULL, NULL, NULL, 0, 0),
	(5, 6, 15, NULL, 'payer_new_inprogress', 'new', '2019-11-26 11:21:34', NULL, NULL, NULL, NULL, 0, 0),
	(6, 6, 16, NULL, 'payer_new_inprogress', 'new', '2019-11-26 11:21:35', NULL, NULL, NULL, NULL, 0, 0),
	(7, 7, 17, NULL, 'payer_new_inprogress', 'new', '2019-11-26 11:21:48', NULL, NULL, NULL, NULL, 0, 0),
	(8, 7, 18, NULL, 'payer_new_inprogress', 'new', '2019-11-26 11:21:49', NULL, NULL, NULL, NULL, 0, 0),
	(9, 7, 19, NULL, 'payer_new_inprogress', 'new', '2019-11-26 11:21:51', NULL, NULL, NULL, NULL, 0, 0),
	(10, 7, 20, NULL, 'payer_new_inprogress', 'new', '2019-11-26 11:21:52', NULL, NULL, NULL, NULL, 0, 0),
	(11, 8, 21, NULL, 'payer_new_inprogress', 'new', '2019-11-26 11:22:04', NULL, NULL, NULL, NULL, 0, 0),
	(12, 8, 22, NULL, 'payer_new_inprogress', 'new', '2019-11-26 11:22:05', NULL, NULL, NULL, NULL, 0, 0),
	(13, 8, 23, NULL, 'payer_new_inprogress', 'new', '2019-11-26 11:22:07', NULL, NULL, NULL, NULL, 0, 0),
	(14, 9, 24, NULL, 'payer_new_inprogress', 'new', '2019-11-26 11:22:54', NULL, NULL, NULL, NULL, 0, 0),
	(15, 9, 25, NULL, 'payer_new_inprogress', 'new', '2019-11-26 11:22:55', NULL, NULL, NULL, NULL, 0, 0),
	(16, 9, 26, NULL, 'payer_new_inprogress', 'new', '2019-11-26 11:22:58', NULL, NULL, NULL, NULL, 0, 0),
	(17, 9, 27, NULL, 'payer_new_inprogress', 'new', '2019-11-26 11:22:59', NULL, NULL, NULL, NULL, 0, 0),
	(18, 4, 28, NULL, 'payer_new_inprogress', 'new', '2019-11-26 11:23:22', NULL, NULL, NULL, NULL, 0, 0),
	(19, 4, 29, NULL, 'payer_new_inprogress', 'new', '2019-11-26 11:23:23', NULL, NULL, NULL, NULL, 0, 0),
	(20, 4, 30, NULL, 'payer_new_inprogress', 'new', '2019-11-26 11:23:24', NULL, NULL, NULL, NULL, 0, 0),
	(21, 4, 31, NULL, 'payer_new_inprogress', 'new', '2019-11-26 11:25:23', NULL, NULL, NULL, NULL, 0, 0),
	(22, 4, 30, 'Тестовый данные', 'text_first', 'new', '2019-11-26 11:25:31', NULL, NULL, NULL, NULL, 1, 0),
	(23, 9, 20, 'Тестовое сообщение', 'text', 'new', '2019-11-26 11:31:41', NULL, NULL, NULL, NULL, 0, 0),
	(24, 7, 29, NULL, 'worker_inwork', 'new', '2019-11-26 12:53:40', NULL, NULL, NULL, NULL, 0, 0),
	(25, 7, 22, NULL, 'worker_inwork', 'new', '2019-11-26 12:53:42', NULL, NULL, NULL, NULL, 1, 0),
	(26, 7, 31, NULL, 'worker_inwork', 'new', '2019-11-26 12:53:51', NULL, NULL, NULL, NULL, 0, 0),
	(27, 7, 31, NULL, 'worker_inprogress_check', 'new', '2019-11-26 12:53:55', NULL, NULL, NULL, NULL, 1, 0),
	(28, 7, 16, '123123', 'worker_inprogress_cancel_request', 'new', '2019-11-26 12:54:09', NULL, 'worker_no_payer_requirements', NULL, NULL, 1, 0),
	(29, 7, 27, '123123', 'worker_inprogress_cancel_request', 'close', '2019-11-26 12:54:25', NULL, 'worker_no_payer_requirements', NULL, NULL, 0, 0),
	(30, 9, 27, NULL, 'payer_inprogress_cancel_confirm', 'new', '2019-11-26 12:55:01', NULL, 'worker_no_payer_requirements', NULL, 'agree', 1, 0),
	(31, 9, 1, NULL, 'payer_inprogress_done', 'new', '2019-11-26 12:55:23', NULL, NULL, NULL, NULL, 0, 0),
	(32, 9, 2, '123123', 'text_first', 'new', '2019-11-26 12:55:26', NULL, NULL, NULL, NULL, 1, 0),
	(33, 9, 2, '123123', 'payer_inprogress_cancel_request', 'new', '2019-11-26 12:55:31', NULL, 'payer_other_no_guilt', NULL, NULL, 1, 0),
	(34, 9, 24, '123123', 'text_first', 'new', '2019-11-26 12:55:34', NULL, NULL, NULL, NULL, 0, 0),
	(35, 6, 24, NULL, 'worker_inwork', 'new', '2019-11-26 12:56:29', NULL, NULL, NULL, NULL, 1, 0),
	(36, 6, 24, NULL, 'worker_inprogress_check', 'new', '2019-11-26 12:56:34', NULL, NULL, NULL, NULL, 1, 0),
	(37, 6, 25, '123123', 'worker_inprogress_cancel', 'new', '2019-11-26 12:56:45', NULL, 'worker_force_cancel', NULL, NULL, 1, 0),
	(38, 6, 18, NULL, 'worker_inwork', 'new', '2019-11-26 12:56:53', NULL, NULL, NULL, NULL, 1, 0),
	(39, 8, 26, NULL, 'worker_inwork', 'new', '2019-11-26 12:57:18', NULL, NULL, NULL, NULL, 1, 0),
	(40, 8, 26, NULL, 'worker_inprogress_check', 'new', '2019-11-26 12:57:21', NULL, NULL, NULL, NULL, 1, 0),
	(41, 8, 15, NULL, 'worker_inwork', 'new', '2019-11-26 12:57:26', NULL, NULL, NULL, NULL, 1, 0),
	(42, 9, 32, NULL, 'payer_new_inprogress', 'new', '2019-11-26 12:58:37', NULL, NULL, NULL, NULL, 0, 0),
	(43, 9, 32, '21313', 'text_first', 'new', '2019-11-26 12:58:41', NULL, NULL, NULL, NULL, 1, 0),
	(44, 9, 32, NULL, 'payer_inprogress_done', 'new', '2019-11-26 12:58:44', NULL, NULL, NULL, NULL, 1, 0),
	(45, 9, 33, NULL, 'payer_new_inprogress', 'new', '2019-11-26 12:58:52', NULL, NULL, NULL, NULL, 0, 0),
	(46, 9, 33, '3123', 'text_first', 'new', '2019-11-26 12:58:57', NULL, NULL, NULL, NULL, 1, 0),
	(47, 9, 33, NULL, 'payer_inprogress_done', 'new', '2019-11-26 12:59:04', NULL, NULL, NULL, NULL, 1, 0),
	(48, 8, 34, NULL, 'payer_new_inprogress', 'new', '2019-11-26 12:59:40', NULL, NULL, NULL, NULL, 0, 0),
	(49, 8, 35, NULL, 'payer_new_inprogress', 'new', '2019-11-26 12:59:41', NULL, NULL, NULL, NULL, 0, 0),
	(50, 8, 34, '213123', 'text_first', 'new', '2019-11-26 12:59:45', NULL, NULL, NULL, NULL, 1, 0),
	(51, 8, 34, NULL, 'payer_inprogress_done', 'new', '2019-11-26 12:59:50', NULL, NULL, NULL, NULL, 1, 0),
	(52, 8, 35, '23123', 'text_first', 'new', '2019-11-26 12:59:53', NULL, NULL, NULL, NULL, 0, 0),
	(53, 8, 35, NULL, 'payer_inprogress_cancel', 'new', '2019-11-26 12:59:55', NULL, 'payer_ordered_by_mistake', NULL, NULL, 1, 0),
	(54, 8, 36, NULL, 'payer_new_inprogress', 'new', '2019-11-26 12:59:58', NULL, NULL, NULL, NULL, 0, 0),
	(55, 9, 36, NULL, 'worker_inwork', 'new', '2019-11-26 13:00:04', NULL, NULL, NULL, NULL, 1, 0),
	(56, 9, 36, NULL, 'worker_inprogress_check', 'new', '2019-11-26 13:00:07', NULL, NULL, NULL, NULL, 1, 0),
	(57, 4, 19, NULL, 'worker_inwork', 'new', '2019-11-26 13:01:04', NULL, NULL, NULL, NULL, 1, 0),
	(58, 4, 19, NULL, 'worker_inprogress_check', 'new', '2019-11-26 13:01:07', NULL, NULL, NULL, NULL, 1, 0),
	(59, 4, 21, NULL, 'worker_inwork', 'new', '2019-11-26 13:01:12', NULL, NULL, NULL, NULL, 1, 0),
	(60, 4, 21, '123123', 'worker_inprogress_cancel', 'new', '2019-11-26 13:01:19', NULL, 'worker_no_time', NULL, NULL, 1, 0),
	(61, 4, 17, '312312', 'worker_inprogress_cancel_request', 'new', '2019-11-26 13:01:28', NULL, 'worker_no_payer_requirements', NULL, NULL, 1, 0),
	(62, 4, 14, NULL, 'worker_inwork', 'new', '2019-11-26 13:01:33', NULL, NULL, NULL, NULL, 1, 0),
	(63, 4, 29, NULL, 'payer_inprogress_done', 'new', '2019-11-26 13:02:43', NULL, NULL, NULL, NULL, 1, 0),
	(64, 8, 37, NULL, 'payer_new_inprogress', 'new', '2019-11-26 13:08:09', NULL, NULL, NULL, NULL, 0, 0),
	(65, 8, 37, '4234', 'text_first', 'new', '2019-11-26 13:08:13', NULL, NULL, NULL, NULL, 0, 0),
	(66, 4, 37, NULL, 'worker_inwork', 'new', '2019-11-26 13:08:21', NULL, NULL, NULL, NULL, 0, 0),
	(67, 4, 37, NULL, 'worker_inprogress_check', 'new', '2019-11-26 13:08:24', NULL, NULL, NULL, NULL, 0, 0),
	(68, 8, 37, NULL, 'payer_check_done', 'new', '2019-11-26 13:08:34', NULL, NULL, NULL, NULL, 1, 0),
	(69, 8, 38, NULL, 'payer_new_inprogress', 'new', '2019-11-26 13:08:59', NULL, NULL, NULL, NULL, 0, 0),
	(70, 8, 39, NULL, 'payer_new_inprogress', 'new', '2019-11-26 13:09:00', NULL, NULL, NULL, NULL, 0, 0),
	(71, 8, 40, NULL, 'payer_new_inprogress', 'new', '2019-11-26 13:09:02', NULL, NULL, NULL, NULL, 0, 0),
	(72, 8, 40, '1323', 'text_first', 'new', '2019-11-26 13:09:06', NULL, NULL, NULL, NULL, 0, 0),
	(73, 8, 39, '123123', 'text_first', 'new', '2019-11-26 13:09:09', NULL, NULL, NULL, NULL, 0, 0),
	(74, 8, 38, '123123', 'text_first', 'new', '2019-11-26 13:09:12', NULL, NULL, NULL, NULL, 0, 0),
	(75, 6, 38, NULL, 'worker_inwork', 'new', '2019-11-26 13:09:38', NULL, NULL, NULL, NULL, 0, 0),
	(76, 6, 38, '3123', 'worker_inprogress_check', 'close', '2019-11-26 13:09:42', NULL, NULL, NULL, NULL, 0, 0),
	(77, 8, 38, NULL, 'payer_check_inprogress', 'new', '2019-11-26 13:09:48', NULL, NULL, NULL, NULL, 0, 0),
	(78, 6, 38, NULL, 'worker_inprogress_check', 'new', '2019-11-26 13:09:57', NULL, NULL, NULL, NULL, 0, 0),
	(79, 8, 38, NULL, 'payer_check_done', 'new', '2019-11-26 13:10:01', NULL, NULL, NULL, NULL, 1, 0),
	(80, 7, 39, NULL, 'worker_inwork', 'new', '2019-11-26 13:10:23', NULL, NULL, NULL, NULL, 0, 0),
	(81, 7, 39, NULL, 'worker_inprogress_check', 'new', '2019-11-26 13:10:29', NULL, NULL, NULL, NULL, 0, 0),
	(82, 8, 39, NULL, 'payer_check_done', 'new', '2019-11-26 13:10:38', NULL, NULL, NULL, NULL, 1, 0),
	(83, 9, 40, NULL, 'worker_inwork', 'new', '2019-11-26 13:11:03', NULL, NULL, NULL, NULL, 0, 0),
	(84, 9, 40, NULL, 'worker_inprogress_check', 'new', '2019-11-26 13:11:06', NULL, NULL, NULL, NULL, 0, 0),
	(85, 9, 41, NULL, 'payer_new_inprogress', 'new', '2019-11-26 13:11:55', NULL, NULL, NULL, NULL, 0, 0),
	(86, 8, 40, NULL, 'payer_check_done', 'new', '2019-11-26 13:12:02', NULL, NULL, NULL, NULL, 0, 0),
	(87, 9, 41, '42342', 'text_first', 'new', '2019-11-26 13:12:06', NULL, NULL, NULL, NULL, 0, 0),
	(88, 8, 41, NULL, 'worker_inwork', 'new', '2019-11-26 13:12:12', NULL, NULL, NULL, NULL, 0, 0),
	(89, 8, 41, 'Тест', 'worker_inprogress_check', 'new', '2019-11-26 13:12:19', NULL, NULL, NULL, NULL, 0, 0),
	(90, 9, 41, NULL, 'payer_check_done', 'new', '2019-11-26 13:12:29', NULL, NULL, NULL, NULL, 1, 0),
	(91, 4, 42, NULL, 'payer_new_inprogress', 'new', '2019-11-26 14:13:44', NULL, NULL, NULL, NULL, 0, 0),
	(92, 4, 42, NULL, 'payer_inprogress_cancel', 'new', '2019-11-26 14:14:34', NULL, 'payer_ordered_by_mistake', NULL, NULL, 1, 0),
	(93, 4, 43, NULL, 'payer_new_inprogress', 'new', '2019-11-26 14:14:45', NULL, NULL, NULL, NULL, 0, 0),
	(94, 4, 43, 'Информация', 'text_first', 'new', '2019-11-26 14:14:53', NULL, NULL, NULL, NULL, 0, 0),
	(95, 5, 43, NULL, 'worker_inwork', 'new', '2019-11-26 14:15:01', NULL, NULL, NULL, NULL, 0, 0),
	(96, 5, 43, NULL, 'worker_inprogress_check', 'new', '2019-11-26 14:15:12', NULL, NULL, NULL, NULL, 0, 0),
	(97, 4, 43, NULL, 'payer_check_done', 'new', '2019-11-26 14:16:37', NULL, NULL, NULL, NULL, 0, 0),
	(98, 4, 44, NULL, 'payer_new_inprogress', 'new', '2019-11-26 14:19:10', NULL, NULL, NULL, NULL, 0, 0),
	(99, 4, 44, 'Тестовая информация', 'text_first', 'new', '2019-11-26 14:19:18', NULL, NULL, NULL, NULL, 0, 0),
	(100, 5, 44, '123123', 'worker_inprogress_cancel_request', 'close', '2019-11-26 14:19:50', NULL, 'worker_no_payer_requirements', NULL, NULL, 0, 0),
	(101, 5, 44, NULL, 'worker_inprogress_cancel_delete', 'new', '2019-11-26 14:20:28', NULL, NULL, NULL, NULL, 0, 0),
	(102, 5, 44, '123123', 'worker_inprogress_cancel_request', 'close', '2019-11-26 14:23:19', NULL, 'worker_no_payer_requirements', NULL, NULL, 0, 0),
	(103, 5, 44, NULL, 'worker_inprogress_cancel_delete', 'new', '2019-11-26 14:24:11', NULL, NULL, NULL, NULL, 0, 0),
	(104, 5, 44, NULL, 'worker_inwork', 'new', '2019-11-26 14:24:14', NULL, NULL, NULL, NULL, 0, 0),
	(105, 5, 44, NULL, 'worker_inprogress_check', 'close', '2019-11-26 14:24:31', NULL, NULL, NULL, NULL, 0, 0),
	(106, 4, 44, NULL, 'payer_check_inprogress', 'new', '2019-11-26 14:24:51', NULL, NULL, NULL, NULL, 0, 0),
	(107, 5, 44, NULL, 'worker_inprogress_check', 'new', '2019-11-26 14:25:12', NULL, NULL, NULL, NULL, 0, 0),
	(108, 4, 44, NULL, 'payer_check_done', 'new', '2019-11-26 14:26:05', NULL, NULL, NULL, NULL, 1, 0),
	(109, 4, 45, NULL, 'payer_new_inprogress', 'new', '2019-11-26 14:26:18', NULL, NULL, NULL, NULL, 0, 0),
	(110, 4, 45, '1241234', 'text_first', 'new', '2019-11-26 14:26:22', NULL, NULL, NULL, NULL, 0, 0),
	(111, 5, 45, NULL, 'worker_inwork', 'new', '2019-11-26 14:26:31', NULL, NULL, NULL, NULL, 1, 0),
	(112, 4, 46, NULL, 'payer_new_inprogress', 'new', '2019-11-26 14:27:10', NULL, NULL, NULL, NULL, 0, 0),
	(113, 4, 46, '123123', 'text_first', 'new', '2019-11-26 14:27:14', NULL, NULL, NULL, NULL, 0, 0),
	(114, 5, 46, NULL, 'worker_inwork', 'new', '2019-11-26 14:28:40', NULL, NULL, NULL, NULL, 1, 0),
	(115, 5, 46, NULL, 'worker_inprogress_check', 'new', '2019-11-26 14:28:43', NULL, NULL, NULL, NULL, 1, 0),
	(116, 4, 23, '3423', 'worker_inprogress_cancel_request', 'close', '2019-11-26 14:29:54', NULL, 'worker_no_payer_requirements', NULL, NULL, 1, 0),
	(117, 4, 23, NULL, 'worker_inprogress_cancel_delete', 'new', '2019-11-26 14:30:12', NULL, NULL, NULL, NULL, 1, 0),
	(118, 4, 47, NULL, 'payer_new_inprogress', 'new', '2019-11-26 14:30:27', NULL, NULL, NULL, NULL, 0, 0),
	(119, 4, 47, '324234', 'text_first', 'new', '2019-11-26 14:30:33', NULL, NULL, NULL, NULL, 0, 0),
	(120, 5, 47, NULL, 'worker_inwork', 'new', '2019-11-26 14:30:34', NULL, NULL, NULL, NULL, 0, 0),
	(121, 5, 47, NULL, 'worker_inprogress_check', 'close', '2019-11-26 14:30:37', NULL, NULL, NULL, NULL, 0, 0),
	(122, 4, 47, NULL, 'payer_check_inprogress', 'new', '2019-11-26 14:30:42', NULL, NULL, NULL, NULL, 1, 0),
	(123, 4, 48, NULL, 'payer_new_inprogress', 'new', '2019-11-26 14:33:12', NULL, NULL, NULL, NULL, 0, 0);
/*!40000 ALTER TABLE `track` ENABLE KEYS */;

-- Дамп структуры для таблица test_stand.want
CREATE TABLE IF NOT EXISTS `want` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `desc` varchar(8192) NOT NULL,
  `category_id` int(11) NOT NULL,
  `status` enum('new','active','cancel','stop','delete','archived','user_stop') DEFAULT NULL,
  `date_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_confirm` timestamp NULL DEFAULT NULL,
  `date_active` timestamp NULL DEFAULT NULL,
  `price_limit` decimal(9,2) NOT NULL DEFAULT '0.00' COMMENT 'Лимит цены предложений',
  `views` int(11) NOT NULL DEFAULT '0',
  `views_dirty` int(11) NOT NULL DEFAULT '0',
  `lang` varchar(8) NOT NULL DEFAULT 'ru',
  PRIMARY KEY (`id`),
  KEY `user_id_idx` (`user_id`),
  KEY `category_id_idx` (`category_id`),
  KEY `status_idx` (`status`),
  KEY `date_confirm_idx` (`date_confirm`),
  KEY `date_create_idx` (`date_create`),
  KEY `price_limit_idx` (`price_limit`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы test_stand.want: ~10 rows (приблизительно)
/*!40000 ALTER TABLE `want` DISABLE KEYS */;
INSERT INTO `want` (`id`, `user_id`, `name`, `desc`, `category_id`, `status`, `date_create`, `date_confirm`, `date_active`, `price_limit`, `views`, `views_dirty`, `lang`) VALUES
	(1, 4, 'Первый тестовый проект', 'Первый тестовый проект', 1, 'active', '2019-11-26 11:02:33', '2019-11-26 11:02:33', NULL, 1000.00, 0, 2, 'ru'),
	(2, 4, 'Второй тестовый проект', 'Второй тестовый проект', 1, 'active', '2019-11-26 11:02:48', '2019-11-26 11:02:48', NULL, 2000.00, 0, 3, 'ru'),
	(3, 6, 'Третий тестовый проект', 'Третий тестовый проект', 1, 'active', '2019-11-26 11:04:00', '2019-11-26 11:04:00', NULL, 6000.00, 0, 1, 'ru'),
	(4, 6, 'Четвертый тестовый проект', 'Четвертый тестовый проект', 1, 'active', '2019-11-26 11:04:13', '2019-11-26 11:04:13', NULL, 9000.00, 0, 2, 'ru'),
	(5, 7, 'Пятый тестовый проект', 'Пятый тестовый проект', 1, 'active', '2019-11-26 11:04:30', '2019-11-26 11:04:30', NULL, 10000.00, 0, 3, 'ru'),
	(6, 7, 'Шестой тестовый проект', 'Шестой тестовый проект', 1, 'active', '2019-11-26 11:04:44', '2019-11-26 11:04:44', NULL, 3000.00, 0, 1, 'ru'),
	(7, 8, 'Седьмой тестовый проект', 'Седьмой тестовый проект', 1, 'active', '2019-11-26 11:08:06', '2019-11-26 11:08:06', NULL, 4000.00, 0, 1, 'ru'),
	(8, 8, 'Восьмой тестовый проект', 'Восьмой тестовый проект', 1, 'active', '2019-11-26 11:08:19', '2019-11-26 11:08:19', NULL, 5000.00, 0, 1, 'ru'),
	(9, 9, 'Девятый тестовый проект', 'Девятый тестовый проект', 1, 'active', '2019-11-26 11:12:51', '2019-11-26 11:12:51', NULL, 500.00, 0, 3, 'ru'),
	(10, 9, 'Десятый тестовый проект', 'Десятый тестовый проект', 1, 'active', '2019-11-26 11:13:05', '2019-11-26 11:13:05', NULL, 950.00, 0, 3, 'ru');
/*!40000 ALTER TABLE `want` ENABLE KEYS */;

-- Дамп структуры для таблица test_stand.want_log
CREATE TABLE IF NOT EXISTS `want_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID Записи',
  `user_id` int(11) DEFAULT NULL COMMENT 'ID пользователя',
  `want_id` int(11) NOT NULL COMMENT 'ID запроса',
  `want_moder_id` int(11) DEFAULT NULL COMMENT 'ID записи в таблице модерации',
  `status` varchar(55) DEFAULT NULL COMMENT 'Статус запроса',
  `moder_type` varchar(55) DEFAULT NULL COMMENT 'Тип модерации запроса',
  `date_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата создания записи',
  `info` varchar(255) DEFAULT NULL,
  `action_type_id` tinyint(4) NOT NULL DEFAULT '0',
  `action_name_id` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Идентификатор наименования действия',
  `admin_id` int(11) DEFAULT NULL COMMENT 'Идентификатор модератора',
  PRIMARY KEY (`id`),
  KEY `want_id_idx` (`want_id`),
  KEY `want_log_want_log_action_type_id_fk` (`action_type_id`),
  KEY `want_log_want_log_action_name_id_fk` (`action_name_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COMMENT='Лог статистики по запросам';

-- Дамп данных таблицы test_stand.want_log: ~10 rows (приблизительно)
/*!40000 ALTER TABLE `want_log` DISABLE KEYS */;
INSERT INTO `want_log` (`id`, `user_id`, `want_id`, `want_moder_id`, `status`, `moder_type`, `date_create`, `info`, `action_type_id`, `action_name_id`, `admin_id`) VALUES
	(1, 4, 1, NULL, 'active', NULL, '2019-11-26 11:02:33', NULL, 0, 0, NULL),
	(2, 4, 2, NULL, 'active', NULL, '2019-11-26 11:02:48', NULL, 0, 0, NULL),
	(3, 6, 3, NULL, 'active', NULL, '2019-11-26 11:04:00', NULL, 0, 0, NULL),
	(4, 6, 4, NULL, 'active', NULL, '2019-11-26 11:04:13', NULL, 0, 0, NULL),
	(5, 7, 5, NULL, 'active', NULL, '2019-11-26 11:04:30', NULL, 0, 0, NULL),
	(6, 7, 6, NULL, 'active', NULL, '2019-11-26 11:04:44', NULL, 0, 0, NULL),
	(7, 8, 7, NULL, 'active', NULL, '2019-11-26 11:08:06', NULL, 0, 0, NULL),
	(8, 8, 8, NULL, 'active', NULL, '2019-11-26 11:08:19', NULL, 0, 0, NULL),
	(9, 9, 9, NULL, 'active', NULL, '2019-11-26 11:12:51', NULL, 0, 0, NULL),
	(10, 9, 10, NULL, 'active', NULL, '2019-11-26 11:13:05', NULL, 0, 0, NULL);
/*!40000 ALTER TABLE `want_log` ENABLE KEYS */;

-- Дамп структуры для таблица test_stand.want_view
CREATE TABLE IF NOT EXISTS `want_view` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `want_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `want_id_idx` (`want_id`),
  KEY `user_id_idx` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы test_stand.want_view: ~21 rows (приблизительно)
/*!40000 ALTER TABLE `want_view` DISABLE KEYS */;
INSERT INTO `want_view` (`id`, `want_id`, `user_id`, `date_create`) VALUES
	(1, 3, 9, '2019-11-26 11:14:42'),
	(2, 1, 9, '2019-11-26 11:15:06'),
	(3, 2, 9, '2019-11-26 11:15:07'),
	(4, 6, 9, '2019-11-26 11:15:55'),
	(5, 10, 9, '2019-11-26 11:16:27'),
	(6, 9, 9, '2019-11-26 11:16:27'),
	(7, 8, 9, '2019-11-26 11:16:59'),
	(8, 2, 8, '2019-11-26 11:17:25'),
	(9, 10, 8, '2019-11-26 11:17:38'),
	(10, 0, 8, '2019-11-26 11:18:06'),
	(11, 7, 8, '2019-11-26 11:18:13'),
	(12, 5, 8, '2019-11-26 11:18:15'),
	(13, 9, 8, '2019-11-26 11:18:35'),
	(14, 5, 9, '2019-11-26 11:19:06'),
	(15, 4, 9, '2019-11-26 11:19:06'),
	(16, 1, 6, '2019-11-26 11:19:18'),
	(17, 2, 6, '2019-11-26 11:20:16'),
	(18, 4, 6, '2019-11-26 11:20:43'),
	(19, 5, 6, '2019-11-26 11:20:48'),
	(20, 10, 6, '2019-11-26 11:21:03'),
	(21, 9, 6, '2019-11-26 11:21:03');
/*!40000 ALTER TABLE `want_view` ENABLE KEYS */;


CREATE TABLE `bans_words` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `word` varchar(255) NOT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `word` (`word`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
