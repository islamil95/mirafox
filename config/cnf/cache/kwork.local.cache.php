<?php
return array (
  'rudomain' => 'kwork.local',
  'mirrordomain' => 'kworks.local',
  'endomain' => 'thekwork.local',
  'enmirror' => 'thekworks.local',
  'connect_subdomain_ru' => 'connect.kwork.ru',
  'connect_subdomain_en' => 'connect.kwork.com',
  'app.mode' => 'local',
  'app.base_name' => 'Kwork',
  'app.mail_sender_name' => 'kwork.ru',
  'app.must_user' => 'www-data',
  'app.local_cron_enable' => 'false',
  'mail.enable' => 'true',
  'mail.engine' => 'php',
  'mail.host_service' => '********',
  'mail.email_service' => 'info2@kwork.ru',
  'mail.password_service' => '********',
  'mail.email_service_en' => 'info@kwork.com',
  'mail.password_service_en' => '********',
  'mail.host_news' => '********',
  'mail.email_news' => 'news2@kwork.ru',
  'mail.password_news' => '********',
  'mail.email_news_en' => 'news@kwork.com',
  'mail.password_news_en' => '********',
  'mail.max_smtp_error' => '3',
  'mail.admin_email' => 'admin@kwork.ru',
  'mail.pay_email' => '********',
  'mail.postal.servers' => 
  array (
    0 => 'service',
    1 => 'news',
  ),
  'mail.postal.service.url' => 'https://postal.kwork.ru/api/v1/send/message',
  'mail.postal.service.secret' => '********',
  'mail.postal.service.indexpage' => 'https://postal.kwork.ru',
  'mail.postal.service.email' => '********',
  'mail.postal.service.password' => '********',
  'mail.postal.service.queue' => '5000',
  'mail.postal.news.url' => 'https://postal.miracloud.ru/api/v1/send/message',
  'mail.postal.news.secret' => '********',
  'mail.postal.news.indexpage' => 'https://postal.miracloud.ru',
  'mail.postal.news.email' => '********',
  'mail.postal.news.password' => '********',
  'mail.postal.news.queue' => '2000',
  'maillog.enable' => 'false',
  'maillog.host_service' => '********',
  'maillog.host_news' => '********',
  'mailfbl.host' => 'mail.mirafox.ru',
  'mailfbl.login' => 'fbl@kwork.ru',
  'mailfbl.pass' => '********',
  'kwork.max_count' => '10',
  'kwork.user_id' => '41',
  'kwork.moder_id' => '15553',
  'kwork.support_id' => '71232',
  'kwork.pause.on' => '12',
  'kwork.pause.off' => '7',
  'kwork.pause.low_portfolio_cnt' => '8',
  'kwork.per_page' => '24',
  'kwork.per_page_payer' => '20',
  'kwork.count_on_search_page' => '36',
  'kwork.count_on_user_search_page' => '35',
  'kwork.category_auth_per_page' => '24',
  'kwork.category_unauth_per_page' => '40',
  'kwork.land_per_page' => '40',
  'kwork.autocancel_hours' => '24',
  'kwork.autocancel_hours_in_stages' => '72',
  'kwork.autoaccept_days' => '3',
  'kwork.autoaccept_holidays_threshold_hours' => '24',
  'kwork.autoaccept_holidays_extend_hours' => '24',
  'kwork.need_worker_inprogress_check_hours' => '24',
  'kwork.need_worker_new_inwork_hours' => '12',
  'kwork.custom_min_price' => '500',
  'kwork.custom_max_price' => '50000',
  'kwork.custom_min_price_en' => '10',
  'kwork.custom_max_price_en' => '1000',
  'kwork.pay_id' => '350320',
  'kwork.wd_pay_id' => '892864',
  'kwork.max_offline_month_for_blocking' => '18',
  'kwork.only_one_translate' => 'false',
  'invite.secret' => '********',
  'tarif_normal.persent' => '20',
  'tarif_promo.persent' => '20',
  'db.master.host' => 'localhost',
  'db.master.user' => 'root',
  'db.master.password' => '123',
  'db.master.name' => 'dev_kwork',
  'db.slave.host' => 'localhost',
  'db.slave.user' => 'root',
  'db.slave.password' => '123',
  'db.slave.name' => 'dev_kwork',
  'db.work.host' => 'localhost',
  'db.work.user' => 'root',
  'db.work.password' => '123',
  'db.work.name' => 'dev_kwork',
  'db.sphinx.host' => 'localhost:9306',
  'db.sphinx.user' => 'fake_user',
  'db.sphinx.password' => 'fake_password',
  'db.sphinx.name' => 'fake_database',
  'db.slave.developers_user' => '********',
  'db.slave.developers_password' => '********',
  'crypto.user_pass_salt_before' => '****************************************',
  'crypto.user_pass_salt_after' => '****************************************',
  'crypto.xor_string_salt' => '****************************************',
  'crypto.sign_token' => '****************************************',
  'crypto.remember_me_salt' => '****************************************',
  'unitpay.out.project_id' => '25102',
  'unitpay.out.login' => '********',
  'unitpay.out.public_key' => '********',
  'unitpay.out.secret_key' => '********',
  'unitpay.out.api.secret_key' => '********',
  'unitpay.out.currency' => 'RUB',
  'unitpay.project_id' => '2085',
  'unitpay.login' => '********',
  'unitpay.public_key' => '********',
  'unitpay.secret_key' => '********',
  'unitpay.api.secret_key' => '********',
  'unitpay.currency' => 'RUB',
  'unitpay.en.project_id' => '2085',
  'unitpay.en.login' => '********',
  'unitpay.en.public_key' => '********',
  'unitpay.en.secret_key' => '********',
  'unitpay.en.api.secret_key' => '********',
  'unitpay.en.currency' => 'USD',
  'unitpay.mirafox.login' => 'unitpay@miralinks.ru',
  'unitpay.solar.login' => 'office@solar-staff.com',
  'paypal.account' => 'admin-facilitator@kwork.com',
  'paypal.sandbox' => 'true',
  'paypal.ru.enable' => 'true',
  'paypal.en.enable' => 'true',
  'paypal.usd.zero_commission' => 'true',
  'paypal.usd.commission_percent' => '2.9',
  'paypal.usd.commission_fixed' => '0.3',
  'paypal.rub.zero_commission' => 'true',
  'paypal.rub.commission_percent' => '2.9',
  'paypal.rub.commission_fixed' => '22',
  'paypal.identity_token' => 'GCTMvUpDIQYFsA2f-w6Fefu4H5XIHXMeiX11hXE45qFIgz77bWdk0OQDDDu',
  'paypal.testers.enable' => 'false',
  'paypal.testers' => 
  array (
    0 => '1',
    1 => '4',
    2 => '6',
    3 => '43',
    4 => '2927',
    5 => '22638',
    6 => '365447',
    7 => '570662',
    8 => '625792',
    9 => '712591',
    10 => '989203',
    11 => '997226',
    12 => '1013526',
  ),
  'amazon.enable' => 'false',
  'amazon.access_key_id' => 'AKIAIQ43VPIRF3K2D5UQ',
  'amazon.access_key_secret' => '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',
  'amazon.region' => 'eu-central-1',
  'amazon.conversation.bucket' => 'kwork-dev-conversation',
  'amazon.conversation.response_mode' => 'private',
  'amazon.conversation.storage_class' => 'STANDARD',
  'amazon.track.bucket' => 'kwork-dev-track',
  'amazon.track.response_mode' => 'private',
  'amazon.track.storage_class' => 'STANDARD',
  'amazon.cache.ttl' => '86400',
  'vk.app_id' => '5056433',
  'vk.secret' => '********',
  'vkm.app_id' => '********',
  'vkm.secret' => '********',
  'fb.ru.app_id' => '1474368409349515',
  'fb.ru.secret' => '9ca1846ae6ea3149d4d396f20a30d60f',
  'fb.en.app_id' => '****************',
  'fb.en.secret' => '********************************',
  'lmi.payee_purse' => '********',
  'lmi.secret_key' => '********',
  'lmi.hash_method' => 'sha256',
  'webmoney.system' => 'unitpay',
  'webmoney.wmid' => '************',
  'webmoney.key_file_password' => '********',
  'webmoney.limit.enable' => 'true',
  'webmoney.limit.amount' => '10000',
  'files.max_count' => '10',
  'files.max_size' => '4194304',
  'files.demo_file_max_size' => '10485760',
  'sphinx.enable' => 'true',
  'sphinx.host' => 'localhost',
  'sphinx.port' => '9312',
  'purse.yandex.enable' => 'false',
  'purse.yandex.comission' => '1',
  'purse.yandex.comission.internal' => '1',
  'purse.yandex.duration' => '1-3',
  'purse.qiwi.enable' => 'false',
  'purse.qiwi.comission' => '1.5',
  'purse.qiwi.comission.internal' => '0',
  'purse.qiwi.duration' => '1-3',
  'qiwi.limit.amount' => '14000',
  'purse.card.enable' => 'false',
  'purse.card.comission' => '0',
  'purse.card.comission.internal' => '0',
  'purse.card.duration' => '1-3',
  'purse.webmoney.enable' => 'false',
  'purse.webmoney.comission' => '0.8',
  'purse.webmoney.comission.internal' => '0.8',
  'purse.webmoney.duration' => '1-3',
  'purse.card2.enable' => 'false',
  'purse.card2.comission' => '4.5',
  'purse.card2.comission.internal' => '2.5',
  'purse.card2.duration' => '1-3',
  'purse.card2.limit.month' => '600000',
  'purse.card2.limit.single' => '60000',
  'purse.webmoney2.enable' => 'false',
  'purse.webmoney2.comission' => '4',
  'purse.webmoney2.comission.internal' => '1.03093',
  'purse.webmoney2.duration' => '1-3',
  'purse.webmoney2.month_limit' => '100000',
  'purse.webmoney2.single_limit' => '14000',
  'purse.webmoney2.limit.day' => '140000',
  'purse.webmoney2.limit.single' => '14000',
  'purse.qiwi3.enable' => 'true',
  'purse.qiwi3.available' => 'true',
  'purse.qiwi3.max_per_day' => '600000',
  'purse.card3.enable' => 'true',
  'purse.card3.available' => 'true',
  'purse.card3.min_amount_eur' => '7300',
  'purse.card3.max_per_day' => '600000',
  'purse.webmoney3.enable' => 'true',
  'purse.webmoney3.available' => 'true',
  'referal.payer.persent' => '3.5',
  'referal.worker.persent' => '2.5',
  'kwork.popular.day' => '3',
  'moder.kwork.price' => '3.20',
  'moder.kwork.price_en' => '0.05',
  'moder.request.price' => '2.10',
  'moder.request.price_en' => '0.03',
  'basket.enable' => 'true',
  'basket.notification_enable' => 'true',
  'redis.enable' => 'true',
  'redis.servers' => 
  array (
    0 => 'redis1',
  ),
  'redis.redis1.host' => 'localhost',
  'redis.redis1.port' => '6379',
  'redis.redis1.auth' => '',
  'redis.redis1.db' => '2',
  'redis.db_ban_ip.server' => 'redis1',
  'redis.redis1.db_ban_ip' => '0',
  'redis.logs.key' => 'redis_monolog',
  'arbitrage.enable' => 'true',
  'kwork.popular.type' => 'new',
  'ban_ip.enable' => 'true',
  'ban_ip.iptable.enable' => 'false',
  'ban_ip.use_bonus_period' => '1296000',
  'ban_ip.bonus_power' => '3',
  'ban_ip.max_ban_time' => '86400',
  'ban_ip.expire_request_log' => '2592000',
  'ban_ip.iptables_count_treshold' => '30',
  'ban_ip.iptables_time_treshold' => '432000',
  'metric.enable' => 'false',
  'metric.yaMetrika.id' => '32983614',
  'metric.yaMetrika.oAuthToken' => '********',
  'metric.adWords.clientId' => '***************************',
  'metric.adWords.AuthorizationCode' => '***************************',
  'metric.adWords.clientCustomerId' => '***-***-****',
  'metric.adWords.developerToken' => '***************************',
  'metric.adWords.refreshToken' => '***************************',
  'metric.adWords.clientSecret' => '***************************',
  'rotation.email_count' => '6',
  'sqllog.enable' => 'true',
  'offer.edit_time' => '10',
  'order.payer_extras.enable' => 'true',
  'order.worker_extras.enable' => 'true',
  'order.expired_order_cancel' => 'true',
  'order.propose_inbox_order' => 'true',
  'order.request_inbox_order' => 'true',
  'order.volume_max_total_ru' => '300000',
  'order.volume_max_total_en' => '5000',
  'category.color_view' => 'false',
  'category.hide_smm' => 'true',
  'category.smm_redirect_cat_name' => 'promotion',
  'module.poll_notify.enable' => 'true',
  'module.refill_bill.enable' => 'true',
  'module.chat.enable' => 'true',
  'module.chat.log' => 'true',
  'module.chat.inbox' => 'true',
  'module.letter_worker.enable' => 'true',
  'module.quick.enable' => 'false',
  'extention_time_event.enable' => 'true',
  'balance_refill_wait.enable' => 'false',
  'balance_withdraw_wait.enable' => 'false',
  'module.support_enable' => 'true',
  'module.user_kwork_filter_enable' => 'true',
  'module.user_kwork_marks.enable' => 'false',
  'module.card_number_refill_limit.enable' => 'true',
  'module.inbox_to_track.enable' => 'true',
  'module.timezone.enable' => 'true',
  'module.inbox_abuse.enable' => 'true',
  'module.lang.enable' => 'true',
  'module.lang.en_site_enable' => 'false',
  'module.lang.testers.enable' => 'true',
  'module.lang.testers' => 
  array (
    0 => '6',
    1 => '2927',
    2 => '84013',
    3 => '22638',
  ),
  'module.newanalytics.enable' => 'false',
  'module.en_bugreport.enable' => 'true',
  'module.affiliate.enable' => 'true',
  'package_lesson.enable' => 'true',
  'package_lesson.video_id' => 'dc271PluIc4',
  'botScout.enable' => 'false',
  'botScout.key' => '********',
  'blog_news.send_to_all' => 'false',
  'blog_news.send_to_userid' => '43',
  'solar_staff_ru.client_id' => '373',
  'solar_staff_ru.salt' => '1a567232b017f1540e7c35',
  'solar_staff_en.client_id' => '373',
  'solar_staff_en.salt' => '1a567232b017f1540e7c35',
  'solar_staff.fake_phone' => '+79397261745',
  'solar_staff.hold_day_change' => '14',
  'solar_staff.block_ua' => 'true',
  'solar_staff.block_blr_qiwi' => 'true',
  'bytehand.client_id' => '28816',
  'bytehand.key' => '********',
  'technical_works.event' => 'false',
  'technical_works.redirect' => 'false',
  'sms.max_attempt' => '5',
  'admitad.postback_key' => '625996afdbd5a06C3e6B0794bcfB430D',
  'admitad.campaign_code' => 'd7a732141a',
  'admitad.user_id' => '268021',
  'server.timezone' => '3',
  'server.timezone_id' => '1',
  'log.userlog.on' => 'false',
  'optimize_images.small_jpeg_quality' => '90',
  'optimize_images.big_jpeg_quality' => '95',
  'optimize_images.retina_jpeg_quality' => '70',
  'test_mobile_api.payer_username' => 'lalala',
  'test_mobile_api.payer_password' => 'qwerty',
  'test_mobile_api.payer_id' => '2',
  'test_mobile_api.worker_username' => 'tanderdams',
  'test_mobile_api.worker_password' => 'qwerty',
  'test_mobile_api.worker_id' => '98520',
  'test_mobile_api.kwork_id' => '67389',
  'test_mobile_api.extra_id' => '136069',
  'test_mobile_api.package_kwork_id' => '67422',
  'test_mobile_api.inbox_user_id' => '99398',
  'test_mobile_api.no_done' => 'false',
  'test_mobile_api.base_username' => 'kwork',
  'test_mobile_api.base_password' => 'dev',
  'virustotal.enable' => 'true',
  'virustotal.get_report_delay' => '15',
  'admin_refill.max_count' => '100',
  'admin_refill.max_amount' => '1000000',
  'qiberty.enable' => 'true',
  'fcm.server_key' => '********',
  'fcm.logging_enabled' => 'true',
  'speller.enable' => 'true',
  'speller.check_kwork.enable' => 'false',
  'checktrust.api_key' => '****************',
  'sourcebuster.enable' => 'false',
  'faq.individual_offer_ru' => '185',
  'faq.individual_offer_en' => '384',
  'promo.4plus1.show_top_banner' => 'false',
  'promo.4plus1.show_page' => 'false',
  'promo.blackfriday.show_top_banner' => 'false',
  'promo.blackfriday.show_page' => 'false',
  'language_tool.server_ru' => 'http://langtool-dev.kwork.ru',
  'language_tool.server_en' => 'http://langtool-dev.kwork.com',
  'bug_report.emails' => 
  array (
    0 => 'user17314@kwork.local',
  ),
  'attributes_auto_visible' => '1',
  'use_new_category_search' => 'true',
  'use_new_category_search.subquery' => 'false',
  'per_page_items' => '12',
  'epayments.ru.url' => 'https://ms.epayments.com',
  'epayments.ru.shop_id' => '',
  'epayments.ru.secret_key' => '',
  'epayments.ru.user_name' => '',
  'epayments.ru.password' => '',
  'epayments.en.url' => 'https://ms.epayments.com',
  'epayments.en.shop_id' => '',
  'epayments.en.secret_key' => '',
  'epayments.en.user_name' => '',
  'epayments.en.password' => '',
  'paymore.endpoint' => 'https://api.paymore.org/v1/',
  'paymore.payment_create_endpoint' => '',
  'paymore.email' => 'admin@kwork.ru',
  'paymore.api_key' => '7872D7B4-DF72-4423-961F-92EDED3382A9',
  'paymore.project_id' => '2528',
  'paymore.notification_api_key' => '48E08D1A-E8B0-4C9F-8C04-F6B4823CCC04',
  'paymore.main_wallet_id' => '3953',
  'paymore.private_wallet_id' => '3953',
  'commission_percent' => '20',
  'commission_level_2_amount' => '30000',
  'commission_level_2_amount_en' => '500',
  'commission_level_2_percent' => '12',
  'commission_level_3_amount' => '300000',
  'commission_level_3_amount_en' => '5000',
  'commission_level_3_percent' => '5',
  'mongodb.enable' => 'true',
  'mongodb.host' => 'localhost',
  'mongodb.port' => '27017',
  'mongodb.database' => 'kwork',
  'mongodb.logs.collection' => 'logs',
  'mongodb.mail.collection' => 'letters',
  'mongodb.mail.testers' => 
  array (
    0 => 'andrey.fedyukov@gmail.com',
    1 => 'skamz@yandex.ru',
    2 => 'strex@bk.ru',
  ),
  'admin_login.sms_expire' => '5',
  'admin_login.sms_timeout' => '1',
  'admin_login.sms_attempts' => '3',
  'admin_login.sms_block' => '10',
  'session.lifetime' => '72',
  'encoder.catch.object.on' => 'false',
  'encoder.catch.fail_decode.on' => 'false',
  'order_stages.unpaid_cancel_days' => '5',
  'order_stages_testers' => 
  array (
    0 => '1',
    1 => '970402',
  ),
  'new_want_block_testers' => 
  array (
    0 => '763992',
  ),
  'monolog.engine' => 'stream',
  'monolog.compulsory_closing' => 'true',
  'front_page_gallery_testers' => 
  array (
    0 => 'MiraJyl',
    1 => 'Zakaz',
    2 => 'Alex',
    3 => 'agentmad',
    4 => 'bellflower',
  ),
  'gallery.testers' => 
  array (
    0 => '1',
    1 => '4',
    2 => '6',
    3 => '43',
    4 => '2927',
    5 => '22638',
  ),
  'is_new_kwork_rating_group_algoritm' => 'false',
  'enable_kwork_rt_index_update' => 'false',
  'enable_kwork_rt_distributed_index_query' => 'false',
  'track.files.max_count' => '25',
  'track.editable_period' => '2',
  'track.draft.ttl' => '10',
  'track.testers' => 
  array (
    0 => '1',
    1 => '4',
    2 => '6',
    3 => '43',
    4 => '2927',
    5 => '22638',
    6 => '660148',
  ),
  'track.admin_removes_per_month_limit' => '20',
  'monitoring.enable' => 'false',
  'monitoring.admin_send_errors_id' => 
  array (
    0 => '1',
  ),
  'mail.smtp_port' => '26',
  'mail.smtp_auth' => 'false',
  'mail.webeffector_email' => '********',
  'kwork.pause.low_portfolio_on' => '3',
  'kwork.pause.low_portfolio_off' => '1',
  'kwork.pause.wo_package_on' => '1',
  'kwork.pause.wo_package_off' => '0',
  'db.host' => 'localhost',
  'db.user' => 'root',
  'db.password' => '123',
  'db.name' => 'dev_kwork',
  'memcache.enable' => 'true',
  'memcache.engine' => 'memcached',
  'memcache.host' => 'localhost',
  'memcache.port' => '11211',
  'memcache.secret' => '********',
  'fb.app_id' => '153222465017970',
  'fb.secret' => '********',
  'purse.qiwi3.comission' => '2.75',
  'purse.qiwi3.min_amount' => '50',
  'purse.qiwi3.limit.single' => '14000',
  'purse.card3.comission' => '4.5',
  'purse.card3.min_amount_rub_ru' => '1050',
  'purse.card3.min_amount_rub_other' => '3150',
  'purse.card3.limit.single' => '60000',
  'purse.webmoney3.comission' => '3',
  'purse.webmoney3.min_amount' => '50',
  'purse.webmoney3.limit.single' => '14000',
  'redis.host' => 'localhost',
  'redis.port' => '6379',
  'redis.auth' => '',
  'redis.db' => '2',
  'redis.db_ban_ip' => '0',
  'module.could_be_package_limit' => 'true',
  'module.low_portfolio_limit' => 'true',
  'module.new_statuses.enable' => 'false',
  'solar_staff.client_id' => '373',
  'solar_staff.salt' => '1a567232b017f1540e7c35',
  'solar_staff_ru.fake_phone' => '+79397261745',
  'solar_staff_en.fake_phone' => '+79397261745',
  'netpeak.protocol_version' => '1',
  'netpeak.tid' => 'UA-68703836-1',
  'virustotal.api_key' => '381dedab6f41fcb0a3de77db6c08a9315bb07fb0fd4b827c5ba2ac0d9e0b1294',
  'new_land.enabled' => 'true',
  'telegram.admin_chat_id' => '',
  'telegram.api_token' => '',
  'server_admins' => 
  array (
    0 => '1',
  ),
  'groups_sort.tester_id' => 
  array (
    0 => '1',
  ),
  'elastic.enable' => 'true',
  'originurl' => 'http://kwork.local',
  'mirrorurl' => 'http://kworks.local',
  'baseurl' => 'http://kwork.local',
  'connect_baseurl' => 'http://connect.kwork.su',
  'pushserver' => 'kwork.local',
  'purl' => 'http://kwork.local/pics',
  'purl_mirror' => 'https://kwork.ru/pics',
  'imageurl' => 'http://kwork.local/images',
  'membersprofilepicurl' => 'http://kwork.local/files/avatar',
  'adminurl' => 'http://kwork.local/administrator',
  'uploadedurl' => 'http://kwork.local/files/uploaded',
  'portfoliourl' => 'http://kwork.local/files/portfolio',
  'demofileurl' => 'http://kwork.su/files/demofile',
  'coverurl' => 'http://kwork.local/files/cover',
  'widgeturl' => 'http://kwork.local/js/kwork_widget.js',
  'mobile_api_url' => 'http://api.kwork.local',
  'qualityurl' => 'http://kwork.su/files/quality',
  'froalaurl' => 'http://kwork.su/files/froala',
  'temp_image_url' => 'http://kwork.local/files/temp_image',
  'basedir' => '/var/www/kwork.local/public_html',
  'imagedir' => '/var/www/kwork.local/public_html/images',
  'pdir' => '/var/www/kwork.local/public_html/pics',
  'membersprofilepicdir' => '/var/www/kwork.local/public_html/files/avatar',
  'logDir' => '/var/www/kwork.local/logs',
  'mobileLogDir' => '/var/www/kwork.local/logs/mobile',
  'configdir' => '/var/www/kwork.local/config',
  'accesslog' => '/var/www/kwork.local/logs/kwork.su.access.log',
  'partnerfile' => '/var/www/kwork.local/public_html/files/xml/partner.xml',
  'partnerfile_mirror' => '/var/www/kwork.local/public_html/files/xml/partner_mirror.xml',
  'webmoney.key_file_path' => '/var/www/kwork.local/config/webmoney/************.kwm',
  'adwords_shopping_xml' => '/var/www/kwork.local/public_html/adwords_shopping.xml',
  'uploadeddir' => '/var/www/kwork.local/public_html/files/uploaded/',
  'portfoliodir' => '/var/www/kwork.local/public_html/files/portfolio',
  'demofiledir' => '/var/www/kwork.local/public_html/files/demofile',
  'coverdir' => '/var/www/kwork.local/public_html/files/cover',
  'admitadfile' => '/var/www/kwork.local/public_html/files/xml/admitad.xml',
  'admitadfile_mirror' => '/var/www/kwork.local/public_html/files/xml/admitad_mirror.xml',
  'bill.xls_path' => '/var/www/kwork.local/files/bill/xls',
  'bill.pdf_path' => '/var/www/kwork.local/files/bill/pdf',
  'tmpDir' => '/var/www/kwork.local/files/tmp',
  'libDir' => '/var/www/kwork.local/lib',
  'dailyLogDir' => 'Z:/home/kwork.su/www/logs/daily',
  'pspell_personal_en' => '/var/www/kwork.local/public_html/include/dictionaries/pspell_personal_en.pws',
  'pspell_personal_ru' => '/var/www/kwork.local/public_html/include/dictionaries/pspell_personal_ru.pws',
  'qualitydir' => 'Z:/home/kwork.su/www/public_html/files/quality',
  'froaladir' => 'Z:/home/kwork.su/www/public_html/files/froala',
  'imageanywhere' => '/var/www/kwork.local/public_html/files/anywhere/',
  'mystem_executable_path' => '/var/www/lib/mystem',
  'temp_image_dir' => '/var/www/kwork.local/public_html/files/temp_image',
  'scripts_path' => '/var/www/kwork.local/cron/scripts',
  'kwork_book_dir' => 'Z:/home/kwork.su/www/public_html/files/books',
  'blog.feedurl' => 'http://blog.kwork.local/feed',
  'blog.thumbnaildir' => '/var/www/kwork.local/public_html/images/news',
  'blog.thumbnail' => '/var/www/kwork.local/images/news',
  'mail.parse_exim.path' => '/var/www/kwork.local/logs/exim/php/mainlog.1',
  'mail.dkim_privatekey_path' => 'false',
  'languagesdir' => '/var/www/kwork.local/languages',
  'sphinx.synonims' => '/var/www/kwork.local/config/sphinx/wordforms.txt',
  'sphinx.indexer_full_log' => 'Z:/home/kwork.su/www/config/sphinx/indexer_full.log',
  'sphinx.indexer_delta_log' => 'Z:/home/kwork.su/www/config/sphinx/indexer_delta.log',
  'sphinx.inc' => 'Z:/home/kwork.su/www/config/sphinx/indexes/dynamic_indexes/inc/',
  'mysql.deny_access_tables' => '/var/www/kwork.local/config/mysql/deny_access.list',
  'maillogo.default' => '/images/mailing/logo.png',
  'ru.maillogo.store' => '/images/mailing/logo.png',
  'en.maillogo.store' => '/images/mailing/logo.png',
  'ru.maillogo.exchange' => '/images/mailing/logo-exchange.png',
  'en.maillogo.exchange' => '/images/mailing/logo-exchange.png',
  'files_miniature_url' => 'http://kwork.local/files/miniature',
  'netpeakfile' => '/var/www/kwork.local/public_html/files/xml/admitad.yml',
  'netpeakfile_mirror' => '/var/www/kwork.local/public_html/files/xml/admitad_mirror.yml',
  'netpeakfile_mirror_csv' => '/var/www/kwork.local/public_html/files/xml/netpeak_mirror.csv',
  'files_miniature_dir' => '/var/www/kwork.local/public_html/files/miniature',
  'productsfile' => '/var/www/kwork.local/public_html/files/xml/products.csv',
  'bill.month_report.path' => '/var/www/kwork.local/files/bill/month_report',
  'errorDailyLogDir' => '/var/www/kwork.local/logs/daily',
);