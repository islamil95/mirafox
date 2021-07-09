window.popupAllowConversations = function (idAllowConversations, idNotAllowConversations) {
	if (idAllowConversations === undefined) {
		idAllowConversations = 'allowConversations';
	}
	if (idNotAllowConversations === undefined) {
		idNotAllowConversations = 'notAllowConversations';
	}
	show_popup('' +
		'<h1 class="popup__title">' + t('Разрешить переписку') + '</h1>' +
		'<hr class="gray mt20 balance-popup__line">' +
		t('Упс, с этим продавцом были проблемы при сдаче вашего заказа, поэтому он больше не сможет написать вам. Хотите дать возможность продавцу снова отправлять вам сообщения?') +
		'<div class="popup__buttons-flexible">' +
		'<button class="popup__button white-btn popup-close-js" id="' + idNotAllowConversations + '">' + t('Нет, не давать') + '</button>' +
		'<button class="popup__button green-btn popup-close-js" id="' + idAllowConversations + '">' + t('Да, пусть отправляет') + '</button>' +
		'</div>', 'popup-unblock', '', (window.isChat ? 'popup_position_centered' : ''));
};
