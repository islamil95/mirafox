<template>
	<div class="container-fluid p-5" v-if="loaded">
		<div class="row">
			<div class="col-12 col-md-5">
				<div class="mb-2">
					<span class="font-weight-bold">Файл:</span><br>
					<input type="file" @change="processFile($event)"/><br>
				</div>
				<div class="mb-2">
					<span class="font-weight-bold">Валидатор:</span><br>
					<v-select v-model="validator" :options="validators"></v-select>
				</div>
				<div class="mb-2">
					<span class="font-weight-bold">Загруженное изображения:</span><br>
					<img :src="src" v-if="src != ''" class="mw-100">
					<span v-else>Нет</span>
					<br>
				</div>
				<div class="mb-3">
					<span class="font-weight-bold">Ответ сервера:</span><br>
					<span v-if="response">{{ response }}</span>
					<span v-else>Нет</span>
				</div>
				<div>
					<button class="button button-success" @click="upload" :disabled="loading || formData.file == null">
						Загрузить
					</button>
				</div>
			</div>
		</div>
	</div>
</template>

<script>

/**
 * Компонент для тестирования загрузки черновиков изображений
 */

// Выбор
Vue.component("v-select", require("appJs/v-select.vue").default);

export default {
	data () {
		return {
			// Данные для отправки на сервер
			formData: {
				// Файл
				file: null,
				// Валидатор
				validator: "",
			},
			// Ссылка на загруженный черновик изображения
			src: "",
			// Ответ сервера
			response: null,
			// Валидаторы
			validators: [],
			// Выбранный валидатор
			validator: "",
			// Идет отправка данных на сервер
			loading: false,
			// Компонент готов к загрузе дочерних компонент
			loaded: false,
		};
	},

	props: [
		// Доступные валидаторы (JSON)
		"validatorsJson",
	],

	watch: {

		/**
		 * Изменился выбранный валидатор
		 */
		validator: function () {
			this.formData.validator = this.validator.id;
		},

	},

	created: function () {
		// Распарсить валидаторы из JSON
		this.validators = JSON.parse(this.validatorsJson);
		// Выбрать первый доступный валидатор
		this.validator = this.validators[0];
		// Компонент готов к загрузе дочерних компонент
		this.loaded = true;
	},

	methods: {

		/**
		 * Загрузить черновик изображения
		 */
		upload: function () {
			this.response = null;
			this.src = "";
			this.loading = true;
			// Преобразовать this.formData в объект FormData
			var formData = new FormData();
			_.forOwn(this.formData, function(value, key) {
				formData.append(key, value);
			});			
	    	// Отправить запрос на сервер
			axios({
			    method: "post",
			    url: "/temp-image-upload",
			    data: formData,
			    config: {
			    	headers: {
			    		"Content-Type": "multipart/form-data",
			    	}
			    }
			})
	      		.then( (response) => {
	      			this.response = response.data;
	      			if (this.response.success) {
	      				this.src = this.response.data.src;
	      			}
	      			this.loading = false;
				});
		},

		/**
		 * Обработать выбранный файл
		 * @param {object} event
		 */
		processFile: function (event) {
			this.formData.file = event.target.files[0];
		},

	},

};
</script>