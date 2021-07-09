/**
 * Этот класс позволяет добавить функционал скрытия и открытия контента по нажатию
 * вот пример шаблона
 * 	<div class="toggler">
 *   <div class="toggler--link">Открыть</div>
 *   <div class="toggler--content">Скрытый контент</div>
 *  </div>
 *  Стили для этого компонета в public_html/css/components/scss/toggler.scss
 *  Механизм работы простой, если есть у родителя (.toggler) класс .open
 *  То дочерний элемент с .toggle--content становится видимым, по умолчаию он скрыт.
 *
 *  Инициализируется только если нету класса .toggler-inited у .toggler
 */

export default class Toggler{
	constructor(instance){
		this.instance = instance;
		this.openner = this.instance.querySelector('.toggler--link')
	}

	init(){
		if(this.instance.classList.contains('toggler-inited')){
			return false;
		}
		if(!this.openner){
			return false; 
		}
		this.openner.addEventListener('click',evt => this.toggleContent());
		this.instance.classList.add('toggler-inited')
	}

	toggleContent(){
		this.instance.classList.toggle('open')
	}
}