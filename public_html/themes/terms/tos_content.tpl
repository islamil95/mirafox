{if $isShowRu}
	<h1 class="f32 ta-center">{'Условия предоставления услуг'|t}</h1>
{else}
	<h1 class="f32 ta-center">{'Условия предоставления услуг'|t|upper}</h1>
{/if}
<table class="oferta-table info-table--{Translations::getLang()}">
	<colgroup>
		<col style="width:50%;">
	</colgroup>
	<tbody>
	<tr>
		{if $isShowRu}
			<td>
				<p class="centered">
					<strong>{'УСЛОВИЯ ПРЕДОСТАВЛЕНИЯ'|t}</strong><br>
					<strong>{'УСЛУГ СЕРВИСА KWORK'|t}</strong><br>
					<strong>Дата последней редакции – 13 марта 2017 года</strong>
				</p>
			</td>
			{if !$isMobileApi}
				<td>
					<p class="centered">
						<strong>KWORK SERVICE</strong><br>
						<strong>TERMS OF SERVICE</strong><br>
						<strong>Effective date – March 13, 2017</strong>
					</p>
				</td>
			{/if}
		{else}
			<td>
				<p class="centered">
					<strong>KWORK SERVICE TERMS OF SERVICE</strong><br>
					<strong>Effective date – March 13, 2017</strong>
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p class="centered">
					<strong>{'ОПРЕДЕЛЕНИЯ'|t}</strong>
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p class="centered">
					<strong>DEFINITIONS</strong>
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Для целей настоящих Условий Предоставления Услуг приведённые ниже определения будут иметь следующие значения:'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					For the purposes of these Terms of Service the following definitions shall have the following
					meanings:
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					<strong>"Вы"</strong> или <strong>"Пользователь"</strong> означает физическое лицо, индивидуального
					предпринимателя либо юридическое лицо, зарегистрированное в Сервисе KWORK в качестве Пользователя и
					принявшее настоящие Условия Предоставления Услуг KWORK и Договор-Оферту.
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					<strong>"You"</strong> or <strong>"User"</strong> means a natural person, individual entrepreneur or
					a legal entity that registered with the KWORK Service as the User and accepted these KWORK Terms of
					Service and the Offer Agreement.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					<strong>"Услуги"</strong> совместно означают платные услуги, которые Оператор предоставляет и делает
					доступными для Пользователя посредством либо с использованием Сервиса KWORK. Под Услугами
					понимаются, в частности, предоставление Пользователю возможности использования определённой
					функциональности Сервиса KWORK по модели Software-as-a-Service ("Программа для ЭВМ как услуга").
					Услуги включают в себя любые дополнительные либо опциональные услуги, предлагаемые Оператором
					посредством Сервиса KWORK в связи с любыми из Услуг. Полное описание всех Услуг, доступных
					Пользователю, доступно в Личном Кабинете Пользователя в Сервисе KWORK.
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					<strong>"Services"</strong> mean, collectively, paid services which the Operator provides and makes
					available to the User through or with the use of the KWORK Service. The Services include, inter
					alia, enabling the User to use specific functionality of the KWORK Services under the SaaS
					("Software-as-a-Service") model, and the Services include any and all additional or optional
					services offered and/or made available by the Operator via the KWORK Service in connection with any
					of the Services. Full details of all Services available to the User are available in the User’s
					Personal Account in the KWORK Service.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					<strong>"Сервис KWORK"</strong> означает принадлежащий Оператору интерактивный программный комплекс
					и сервис, включая веб-сайт, предназначенные для предоставления Пользователю Услуг, доступный в сети
					Интернет по уникальному адресу <a href="{$baseurl}">{$baseurl}</a>, используемый Оператором для
					предоставления и оказания Пользователю Услуг в соответствии с настоящими Условиями.
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					<strong>"KWORK Service"</strong> means the Operator’s proprietary online software suite and service
					inclusive of the website, designated for provision of Services to the User, available on the
					Internet via URL <a href="{$baseurl}">{$baseurl}</a>, that is used by the Operator to provide the
					Services to the User hereunder.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					<strong>"Личный Кабинет"</strong> означает персонализированный раздел Сервиса KWORK, закрытый для
					публичного доступа. Доступ к Личному Кабинету осуществляется посредством ввода на странице входа в
					Сервис KWORK аутентификационных данных: логина (имени пользователя) и пароля (кода доступа).
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					<strong>"Personal Account"</strong> means the personalized section of the KWORK Service closed for
					public access. Personal Account is accessed by entering authentication details on the KWORK Service
					login page: login (username) and password (access code).
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					<strong>"Оператор"</strong> означает компанию <strong>Мирафокс Лимитед</strong>, зарегистрированную
					под номером 2386193, находящуюся по адресу: Гонконг, Ван Чаи, 338 Дорога Хеннесси, башня СНТ, 11/Ф,
					Блок Ф, в лице Директора Элна Полетт Лафортун, действующей на основании Устава, а также её дочерние
					и аффилированные компании, которая является оператором Сервиса KWORK как он определён в настоящих
					Условиях и предоставляет Услуги посредством и с помощью Сервиса KWORK.
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					<strong>"Operator"</strong> means <strong>Mirafox Limited</strong>, a company registered under No.
					2386193, with its address at: Unit F, 11/F, CNT Tower, 338 Hennessy Road, Wan Chai, Hong Kong,
					represented by its Director, Elna Paulette Lafortune, acting on the basis of the Articles of
					Association, and its subsidiaries and affiliates, that is the operator of the KWORK Service as it is
					defined herein and that provides the Services as they are defined herein by means and with help of
					the KWORK Service.
				</p>
			</td>
		{/if}
	</tr>
	{if !Translations::isDefaultLang()}
		<tr>
			<td>
				<p>
					<strong>"New Operator"</strong> means <strong>Kwork Technologies OU</strong>, a company registered
					under No.14531126, with its address at: Harju maakond, Таllinn, Kesklinna linnaosa, Nаrvа mnt 7,634,
					Estonia, represented by its Director Ekaterina Kutiukova. The company operates KWORK Service under
					the Software Licensing Agreement with Mirafox Limited, its subsidiaries and affiliates, which
					operate KWORK Service as it is defined herein and provide the Services as they are defined herein by
					means and with help of KWORK Service.
				</p>
			</td>
		</tr>
	{/if}

	<tr>
		{if $isShowRu}
			<td>
				<p>
					<strong>"Кворки"</strong> совместно означают пакеты платных работ и услуг с фиксированной
					стоимостью, предложения о выполнении и оказании которых Пользователь может размещать в Сервисе
					KWORK, а также пакеты платных работ и услуг с фиксированной стоимостью других Пользователей,
					выполнение и оказание которых Пользователь может заказывать посредством и с помощью функциональности
					Сервиса KWORK.
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					<strong>"Kworks"</strong> mean, collectively, fixed-price packages of works and services the offers
					for performance and rendering of which the User may place in the KWORK Service, as well as
					fixed-price packages of works and services of other Users performance and rendering of which may be
					ordered by the User by means and with help of the KWORK Service
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p class="centered">
					<strong>{'ОБЩИЕ ПОЛОЖЕНИЯ'|t}</strong>
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p class="centered">
					<strong>GENERAL</strong>
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					Настоящие Условия Предоставления Услуг определяют принципы использования Вами как Пользователем
					интерактивного сервиса <strong>"KWORK"</strong>, находящегося в сети Интернет на веб-сайте <a
							href="{$baseurl}">{$baseurl}</a> (далее <strong>"Сервис KWORK"</strong>), который
					предоставляется и управляется компанией <strong>Мирафокс Лимитед</strong>, зарегистрированной под
					номером 2386193, находящейся по адресу: Гонконг, Ван Чаи, 338 Дорога Хеннесси, башня СНТ, 11/Ф, Блок
					Ф, в лице Директора Элна Полетт Лафортун, действующей на основании Устава, и/или её дочерними или
					аффилированными компаниями (далее <strong>"Оператор"</strong>), а также условия предоставления
					Оператором Вам как Пользователю Услуг как они определены в настоящих Условиях Предоставления Услуг.
					Настоящие Условия Предоставления Услуг являются неотъемлемой частью Договора-Оферты с Пользователем
					Сервиса KWORK и представляют собой юридически связывающий договор между Пользователем как он
					определён в настоящих Условиях, осуществляющим доступ к Сервису KWORK и его использование,
					получающим Услуги и принимающим настоящие Условия Предоставления Услуг и все связанные с ними
					документы, включая, но не ограничиваясь этим, Договор-Оферту, Политику Конфиденциальности и Политику
					Разрешения Споров, включённые в настоящие Условия Предоставления Услуг посредством ссылки (далее
					<strong>"Вы"</strong> или <strong>"Пользователь"</strong>) и Оператором, и его положения могут
					приводиться в исполнение против Вас в принудительном порядке в соответствии с применимым правом.
					Пожалуйста, прочтите настоящие Условия Предоставления Услуг и ознакомьтесь с ними перед тем, как Вы
					начнёте использование Сервиса KWORK и Услуг. Осуществляя доступ к Сервису KWORK и его использование
					Вы подтверждаете, что Вы прочитали и понимаете настоящие Условия Предоставления Услуг, и
					соглашаетесь быть связанными всеми положениями настоящих Условий Предоставления Услуг без каких-либо
					ограничений. В настоящие Условия Предоставления Услуг могут вноситься изменения; пожалуйста,
					ознакомьтесь с настоящими Условиями Предоставления Услуг каждый раз, когда Вы осуществляете доступ к
					Сервису KWORK и/или его использование. Вы подтверждаете и соглашаетесь с тем, что если Вы
					осуществляете доступ к Сервису KWORK и Услугам и/или их использование, будет считаться, что Вы
					приняли самую актуальную версию настоящих Условий Предоставления Услуг. Если Вы не соглашаетесь
					исполнять условия настоящих Условий Предоставления Услуг и/или быть связанным(ой) настоящими
					Условиями Предоставления Услуг, Вы не вправе осуществлять доступ к Сервису KWORK или использование
					Сервиса KWORK любым образом, а также получать Услуги.
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					These Terms of Service establish the principles and conditions of your use as a User of the<strong>"KWORK"</strong>
					interactive service located on the Internet on the website <a href="{$baseurl}">{$baseurl}</a> (the
					<strong>"KWORK Service"</strong>), which is provided and operated by <strong>Mirafox
						Limited</strong>, a company registered under No. 2386193, with its address at: Unit F, 11/F, CNT
					Tower, 338 Hennessy Road, Wan Chai, Hong Kong, represented by its Director, Elna Paulette Lafortune,
					acting on the basis of the Articles of Association, and/or its subsidiaries or affiliates
					(the<strong>"Operator"</strong>), as well as the terms of provision by the Operator of Services as
					they are defined in these Terms of Service to you as a User. These Terms of Service constitute an
					integral part of the KWORK Service User Offer Agreement and a legally binding agreement between the
					User as defined herein that accesses and uses the KWORK Service, receives Services, and accepts
					these Terms of Service and all associated documents, including but not limited to the Offer
					Agreement, Privacy Policy and the Dispute Resolution Policy included by reference herein (<strong>"You"</strong>
					or the <strong>"User"</strong>) and the Operator, and its provisions may be enforced against you
					under applicable law. Please read and review these Terms of Service before you commence using the
					KWORK Service. By accessing or using the KWORK Service you acknowledge that you have read,
					understand and agree to be legally bound by all of the provisions of these Terms of Service without
					any limitations whatsoever. These Terms of Service are subject to change; please review these Terms
					of Service each time you access and/or use the KWORK Service and. You acknowledge and agree that by
					accessing or using the KWORK Service and the Services, you shall be deemed as having accepted the
					most recent version of these Terms of Service. If you do not agree to follow the terms of and/or be
					bound by these Terms of Service, you may not access or use the KWORK Service or receive the
					Services.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Для целей настоящих Условий Предоставления Услуг термин "Сервис KWORK" включает в себя, но не ограничивается этим, непосредственно сам Сервис KWORK, лежащие в основе Сервиса KWORK программы для ЭВМ, программные алгоритмы, базы данных и технические средства ЭВМ, всё информационное наполнение (контент), доступное в Сервисе или посредством Сервиса KWORK, все элементы дизайна Сервиса KWORK и все интерактивные службы и функциональность, предоставляемые посредством Сервиса KWORK (включая но не ограничиваясь этим, Услуги), а также любые и все иные элементы и составляющие Сервиса KWORK без каких-либо ограничений. Будет считаться, что Вы используете Сервис KWORK каждый раз, когда Вы осуществляете доступ к Сервису KWORK (посредством персонального компьютера, мобильного устройства или иной технологии) либо иным способом осуществляете взаимодействие или сообщение с Сервисом KWORK либо подключение к Сервису KWORK, либо любыми его частями или разделами, либо осуществляете взаимодействие или сообщение с другими пользователями Сервиса KWORK посредством и с помощью Сервиса KWORK, а также когда Вы получаете Услуги посредством и с помощью Сервиса KWORK.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					For the purposes of these Terms of Service the term "KWORK Service" includes but is not limited to
					the KWORK Service itself, underlying software programs, software algorithms, databases and hardware,
					all content available on or by means of the KWORK Service, all KWORK Service design elements and all
					of the interactive services and functionality provided on or through the KWORK Service (including
					but not limited to the Services), and any and all other elements and parts of the KWORK Service
					without any limitation. You will be deemed using the KWORK Service anytime you access (via computer,
					mobile device or other technology) or otherwise interact or communicate with or connect to, the
					KWORK Service or any parts or sections thereof or interact or communicate with other users of the
					KWORK Service by means and with help of the KWORK Service, as well as when you receive the Services
					by means and with help of the KWORK Service.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Настоящие Условия Предоставления Услуг применяются только и исключительно к Сервису KWORK и Услугам, применяются только к Вашим правам и обязанностям в отношении использования Вами Сервиса KWORK и Услуг, и не применяются к использованию Вами, или к правам и обязанностям в отношении, любых иных веб-сайтов, включая но не ограничиваясь этим любых иных веб-сайтов, которые могут управляться либо предлагаться Оператором или иными третьими лицами.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					These Terms of Service apply to only and exclusively to the KWORK Service and the Services, apply
					only to your rights and responsibilities in connection with your use of the KWORK Service and the
					Services and do not apply to your use of, or rights and responsibilities regarding, any other
					websites including but not limited to any other websites that may be operated or offered by the
					Operator or other third parties.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Осуществление Вами доступа к Сервису KWORK и/или использование Сервиса KWORK и/или любой его части, особенности или функциональности (в том числе Услуг) посредством и с помощью Сервиса KWORK в любой момент времени означает Ваше согласие быть связанным/ой самой актуальной редакцией настоящих Условий Предоставления Услуг.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					Your access to and/or use of the KWORK Service and/or any part, feature or functionality thereof
					(including the Services) by means and with help of the KWORK Service at any moment signifies your
					agreement to be bound by the most recent version of these Terms of Service.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
		<td>
			<p>
				{'Принимая настоящие Условия Предоставления Услуг и соглашаясь быть связанным/ей/и их условиями, Вы соглашаетесь с тем, что осуществление Вами доступа к Сервису KWORK и использование Сервиса KWORK должны во все времена осуществляться Вами в строгом соответствии со следующими общими правилами:'|t}
			</p>
		</td>
		{if $isShowEn}
			<td>
				<p>
					By accepting these Terms of Service and agreeing to be bound by them, you agree that your access and
					use of the KWORK Service must be at all times performed by you in strict accordance with the
					following general rules:
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Принимая настоящие Условия Предоставления Услуг и соглашаясь быть связанным/ей/и их условиями, Вы соглашаетесь с тем, что осуществление Вами доступа к Сервису KWORK и использование Сервиса KWORK должны во все времена осуществляться Вами в строгом соответствии со следующими общими правилами:'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					By accepting these Terms of Service and agreeing to be bound by them, you agree that your access and
					use of the KWORK Service must be at all times performed by you in strict accordance with the
					following general rules:
				</p>
			</td>
		{/if}
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'Вы должны осуществлять использование Сервиса KWORK исключительно в соответствии с его целевым предназначением;'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						you must use the KWORK Service in accordance with its designated purpose only;
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'осуществление Вами доступа к Сервису KWORK и его использование должны осуществляться Вами в полном соответствии с настоящими Условиями Предоставления Услуг, Договором-Офертой, иными применимыми документами, относящимися к Сервису KWORK, а также всеми применимыми законодательными актами;'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						your accessing and use of the KWORK Service must be performed by you in full compliance with
						these Terms of Service, the Offer Agreement, other applicable documents related to the KWORK
						Service and all applicable laws;
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'Вы обязаны всегда предоставлять достоверную и полную информацию о Вас и лицах, которых Вы представляете, в зависимости от того, что применимо;'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						you must always provide true and complete information about yourself and parties you represent,
						whichever is applicable;
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'Вы не должны осуществлять никаких действий, прямо запрещённых настоящими Условиями Предоставления Услуг.'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						you must not perform any actions expressly prohibited by these Terms of Service.
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p class="centered">
					<strong>{'ТРЕБОВАНИЯ К ПОЛЬЗОВАТЕЛЯМ'|t}</strong>
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p class="centered">
					<strong>USER ELIGIBILITY</strong>
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Информационное наполнение (контент), функциональность и результаты взаимодействия, доступные в Сервисе или посредством Сервиса KWORK, предназначены только для взрослых.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					Content, functionality and results of interaction available on or by means of the KWORK Service are
					intended for adults only.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Если Вы намереваетесь использовать Сервис KWORK в качестве Пользователя, то Вы:'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					If you intend to use the KWORK Service as a User, you must:
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'если Вы являетесь физическим лицом, то одновременно (1) Вам должно быть 18 (восемнадцать) полных лет, и (2) Вы должны являться совершеннолетним по законодательству той юрисдикции, из которой Вы осуществляете доступ к Сервису KWORK и его использование. Если Вам исполнилось 18 лет, но Вы являетесь несовершеннолетним по законодательству страны Вашего пребывания, Вы обязаны ознакомиться с настоящими Условиями Предоставления Услуг, и их условия от Вашего лица должен принять либо Ваш родитель, либо законный представитель, для того, чтобы Вы могли использовать Сервис KWORK. Лицо, принимающее Условия Предоставления Услуг от Вашего лица, должно быть юридически грамотным. Если Вам не исполнилось 18 лет, Вы не вправе осуществлять доступ к Сервису KWORK и его использование. Оператор оставляет за собой право уведомлять соответствующие государственные органы о попытках использования Сервиса KWORK несовершеннолетними; ИЛИ'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						if you are a natural person, be both (1) at least 18 (eighteen) full years of age, and (2) of
						legal age in the jurisdiction from which you access and use the KWORK Service. If you are at
						least 18 years of age but are a minor under the laws of your country of residence, you must
						review these Terms of Service and have your parent or legal guardian accept the Terms of Service
						on your behalf in order for you to use the KWORK Service. The person accepting the Terms of
						Service on your behalf must be legally competent. If you are not 18 years of age, you may not
						access and use the KWORK Service. The Operator reserves the right to report attempts of use of
						the KWORK Service by minors to the appropriate officials; OR
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'если Вы являетесь представителем юридического лица, Вы обязаны быть должным образом уполномоченным тем юридическим лицом, которое Вы представляете, на осуществление регистрации в Сервисе и использование его функциональности. Оператор оставляет за собой право расследовать попытки использования Сервиса KWORK лицами, официально не уполномоченными теми юридическими лицами, которые, по утверждению таких лиц, они представляют, и уведомлять соответствующие государственные органы о таких попытках.'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						if you are a representative of a legal entity, you must be duly authorized by the legal entity
						you represent to register with the KWORK Service and use its functionality. The Operator
						reserves the right to investigate attempts of use of the KWORK Service by parties not officially
						authorized to represent legal entities these parties claim to represent, and report such
						attempts to the appropriate officials.
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p class="centered">
					<strong>{'РЕГИСТРАЦИЯ ЛИЧНОГО КАБИНЕТА'|t}</strong>
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p class="centered">
					<strong>PERSONAL ACCOUNT REGISTRATION</strong>
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Если Вы намереваетесь использовать Сервис KWORK и Услуги, Вам будет предложено зарегистрировать Личный Кабинет и указать определённые регистрационные данные для осуществления доступа к Сервису KWORK и его использования. Вы явным образом соглашаетесь с тем, что вся информация, которую Вы предоставляете в момент регистрации Личного Кабинета в Сервисе KWORK, является точной, достоверной, актуальной и полной. Предоставление вводящей в заблуждение или заведомо ложной информации о Вашей личности запрещено и представляет собой существенное нарушение настоящих Условий Предоставления Услуг. Если Оператор полагает, что предоставляемая Вами информация не является точной, достоверной, актуальной и полной, либо является вводящей в заблуждение либо заведомо ложной, Оператор оставляет за собой право отказать Вам в доступе к Сервису KWORK либо любым его ресурсам, приостановить такой доступ либо полностью прекратить его предоставление, а также заблокировать Ваш Личный Кабинет в любой момент времени.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					If you intend to use the KWORK Service and the Services you will be asked to register a Personal
					Account and provide certain registration details in order to access and use the KWORK Service. You
					expressly agree that all of the information you provide upon registration of your Personal Account
					with the KWORK Service must be correct, truthful, current, and complete. Providing misleading or
					false information about your identity is forbidden and constitutes a material breach of these Terms
					of Service. If the Operator believes that the information you provide is not correct, truthful,
					current, or complete, or is false or misleading, the Operator has the right to refuse, suspend or
					terminate your access to the KWORK Service or any of its resources and to suspend or block your
					Personal Account at any time.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Когда Вы регистрируетесь в качестве Пользователя Сервиса KWORK, создаёте Ваш Личный Кабинет и принимаете Договор-Оферту и настоящие Условия Предоставления Услуг, Вам необходимо создать имя пользователя и пароль для входа в свой Личный Кабинет в Сервисе KWORK. Вы несёте персональную и единоличную ответственность за любое использование Сервиса KWORK под Вашими именем пользователя и паролем. Вы соглашаетесь предпринимать разумные действия для сохранения Ваших имени пользователя и пароля от противоправного использования третьими лицами, и незамедлительно уведомлять Оператора о любом таком противоправном использовании посредством следующего адреса электронной почты:'|t}
					<a href="mailto:info@kwork.ru" target="_blank">info@kwork.ru</a>.
				</p>
			</td>
			{if !$isMobileApi}
				<td>
					<p>
						When you register as a User of the KWORK Service, create your Personal Account and accept the
						Offer Agreement and these Terms of Service, you are required to create a username and a password
						for logging into your Personal Account on the KWORK Service. You are personally and solely
						responsible for any use of the KWORK Service with your username and password. You agree to take
						due care in protecting your username and password against misuse by third parties and promptly
						notify the Operator about any misuse via the following e-mail: <a href="mailto:info@kwork.ru"
																						  target="_blank">info@kwork.ru</a>.
					</p>
				</td>
			{/if}
		{else}
			<td>
				<p>
					When you register as a User of the KWORK Service, create your Personal Account and accept the Offer
					Agreement and these Terms of Service, you are required to create a username and a password for
					logging into your Personal Account on the KWORK Service. You are personally and solely responsible
					for any use of the KWORK Service with your username and password. You agree to take due care in
					protecting your username and password against misuse by third parties and promptly notify the
					Operator about any misuse via the following e-mail: <a href="mailto:info@kwork.com" target="_blank">info@kwork.com</a>.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p class="centered">
					<strong>{'УСЛУГИ'|t}</strong>
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p class="centered">
					<strong>SERVICES</strong>
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Для целей настоящих Условий Предоставления Услуг и Договора-Оферты Услуги включают как определённую функциональность Сервиса KWORK, так и дополнительные Услуги, предлагаемые Вам Оператором. Услуги включают в себя, в частности:'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					For the purposes of these Terms of Service and the Offer Agreement, the Services include both the
					functionality of the KWORK Service and additional Services offered to you by the Operator. Services
					include, inter alia:
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'предоставление Пользователю возможности создания и размещения в Сервисе KWORK Кворков для заказа другими Пользователями Сервиса KWORK работ и услуг, включаемых Пользователем в Кворки Пользователя;'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						enabling the User to create and place in the KWORK Service of Kworks for the purposes of
						ordering of works and services included by the User in the User’s Kworks by other Users of the
						KWORK Service;
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'предоставление Пользователю возможности заказывать выполнение работ и оказание услуг, включённых в Кворки других Пользователей Сервиса KWORK и предлагаемых ими для выполнения и оказания посредством и с помощью Сервиса KWORK;'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						enabling the User to order performance of works and rendering of services included in Kworks of
						other Users of the KWORK Service and offered by them for performance and rendering by means and
						with help of the KWORK Service;
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'предоставление Пользователю возможности проводить денежные расчёты с другими Пользователями Сервиса KWORK, как за Кворки Пользователя, в рамках которых Пользователь выполняет работы и оказывает услуги для других Пользователей, так и за Кворки других Пользователей, выполнение работ и/или оказание услуг в рамках которых заказывается Пользователем посредством и с помощью Сервиса KWORK;'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						enabling the User to perform monetary transactions with other Users of the KWORK Service, both
						for User’s Kworks within the scope of which the User performs works and renders services for
						other Users, and the Kworks of other Users performance of works and/or rendering of services
						within the scope of which is ordered by the User by means and with help of the KWORK Service;
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'предоставление Пользователю иной функциональности и оказание дополнительных Услуг, доступных для выбора Пользователем посредством и с помощью Личного Кабинета в Сервисе KWORK.'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						providing to the User other functionality and rendering of additional Services, available for
						selection by the User by means and with help of the Personal Account in the KWORK Service.
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Услуги предоставляются и оказываются посредством и с помощью Сервиса KWORK.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					The Services are provided and rendered by means and with help of the KWORK Service.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Оператор оставляет за собой право отказать в предоставлении Услуг по собственному усмотрению, в том числе, если предоставление Услуг подразумевает нарушение Пользователем настоящих Условий Предоставления Услуг.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					The Operator reserves the right to refuse to provide the Services at its discretion, inter alia, if
					provision of the Services implies violation of these Terms of Service by the User.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Оператор будет предпринимать все разумные усилия к тому, чтобы Услуги были доступны на уровне 24 часа в день, 7 дней в неделю, 365 дней в году (24x7x365) и в полном соответствии с настоящими Условиями Предоставления Услуг.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					The Operator shall use all reasonable endeavors for the Services to be available on a level of 24
					hours a day, 7 days a week, 365 days a year (24x7x365) and in full accordance with these Terms of
					Service.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Оператор оставляет за собой право время от времени осуществлять техническое обслуживание Сервиса KWORK для целей улучшения либо поддержки надлежащего уровня предоставляемых Услуг. Оператор будет предпринимать все разумные усилия к уведомлению Пользователя о таких работах по обслуживанию заранее посредством электронной почты либо посредством Личного Кабинета Пользователя. Пользователь соглашается, что Услуги могут быть недоступны в период технического обслуживания Сервиса KWORK.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					The Operator reserves the right to carry out from time to time KWORK Service technical maintenance
					to improve or support the appropriate level of provided Services. The Operator shall use all
					reasonable endeavors to notify the User about such maintenance works in advance via e-mail or via
					User’s Personal Account. The User agrees that the Services may not be available during technical
					maintenance the KWORK Service.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p class="centered">
					<strong>{'ПРАВИЛА СОЗДАНИЯ, РАЗМЕЩЕНИЯ И ВЫПОЛНЕНИЯ КВОРКОВ'|t}</strong>
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p class="centered">
					<strong>RULES OF KWORKS CREATION, PLACEMENT AND PERFORMANCE</strong>
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					Если Пользователь принимает решение о создании и размещении в Сервисе KWORK Кворка Пользователя, то
					такой Кворк должен быть создан путём заполнения формы <strong>«Создать Кворк»</strong>, доступной
					посредством Личного Кабинета Пользователя в Сервисе KWORK.
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					If the User decides to create and place in the KWORK Service a User’s Kwork, that Kwork must be
					created by filling the <strong>"Create New Kwork"</strong> form available in the User’s Personal
					Account in the KWORK Service.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Размещение Кворка в Сервисе KWORK является бесплатным. Продавец не вправе отказаться от продажи активного Кворка, если все требования Кворка покупателем выполнены. В случае отказа рейтинг продавца снижается.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					Placement of a Kwork in the KWORK Service is free of charge. The user may not refuse the sale of an
					active Kwork, if all requirements are met by the customer. If the User refuses the order, the user's
					rating will be lowered.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Стоимость каждого Кворка устанавливается размещающим его в Сервисе KWORK Пользователем в зависимости от объёма работ/услуг, включённых в Кворк и начинается с 500 рублей. Также Пользователь может добавить дополнительные опции к каждому Кворку с обязательным указанием дополнительной стоимости таких опций.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					The price of each Kwork is set by the User who placed it in the KWORK Service depending the volume
					of works/services included in the Kwork and starts from $10 (ten dollars). Also the User may add
					additional options to each Kwork with mandatory designation of the additional prices of such
					options.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Работы и услуги в рамках каждого Кворка выполняются и оказываются только и непосредственно тем Пользователем, который разместил соответствующий Кворк в Сервисе KWORK. Оператор не несёт ответственности за качество, своевременность и иные параметры выполнения работ и оказания услуг в рамках Кворков, размещаемых Пользователями в Сервисе KWORK.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					Works and services within the scope of each Kwork are performed and rendered only and directly by
					the User who placed the relevant Kwork in the KWORK Service. The Operator bears no responsibility
					with respect to the quality, timeliness and other parameters of performance of works and rendering
					of services within the scope of Kworks placed by Users in the KWORK Service.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Перед фактическим размещением нового Кворка в Сервисе KWORK каждый Кворк проходит модерацию на предмет соответствия настоящим Условиям Предоставления Услуг, и размещается в Сервисе KWORK только после успешного прохождения модерации. Оператор и модераторы Оператора оставляют за собой право редактировать информацию о Кворке либо удалить Кворк в процессе модерации в случае его несоответствия настоящим Условиям Предоставления Услуг либо любым документам, регулирующим использование Сервиса Kwork.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					Prior to actual placement of each new Kwork in the KWORK Service each Kwork undergoes moderation
					with respect to its conformance with these Terms of Service, and is placed in the KWORK Service only
					upon successful moderation. The Operator and Operator’s moderators reserve the right to edit the
					information about Kwork or delete Kwork in the process of moderation if the Kwork does not conform
					to these Terms of Service or any documents governing use of the KWORK Service.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Пользователь вправе приостановить показ размещённого Кворка в Сервисе KWORK в случае, если включённые в такой Кворк работы или услуги не могут быть выполнены или оказаны Пользователем либо стали неактуальны.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					The User may suspend the display of the Kwork placed in the KWORK Service if the works or services
					included in that Kwork may not be performed or rendered by the User, or are no longer relevant.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'При необходимости предоставления какому-либо Пользователю определённых материалов для целей выполнения работ и/или оказания услуг в рамках соответствующего Кворка, Пользователь, заказывающий работы или услуги в рамках такого Кворка, обязуется предоставить такие материалы самостоятельно и за свой счёт.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					In instances where provision of certain materials to a User for the purposes of performance of works
					/ rendering of services within the scope of the relevant Kwork is required, the User ordering the
					works or services within the scope of said Kwork must provide the discussed materials by him/herself
					and at his/her own expense.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Вознаграждение Пользователя за выполненные Кворки выплачивается Пользователю только после приёмки результатов работ или услуг в рамках Кворка Пользователем, заказавшим соответствующий Кворк. При этом, приёмка работ/услуг, выполненных / оказанных в рамках соответствующего Кворка должна быть произведена Пользователем, заказавшим их выполнение / оказание, в течение 3 (Трёх) рабочих дней с момента предоставления результата выполненных работ Пользователю, либо с момента завершения оказания соответствующих услуг, в зависимости от того, что применимо. Если работа в рамках Кворка не выполнена, либо услуги в рамках Кворка не оказаны, либо выполненные работы / оказанные услуги не соответствуют описанию работ или услуг, данном в описании Кворка, Пользователь, заказавший Кворк, вправе запросить у Пользователя, выполнявшего работы или оказывавшего услуги в рамках такого Кворка либо доработать результат работ (если выполнялись работы) или повторно оказать услуги (если оказывались услуги).'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					User’s Remuneration for completed Kworks is paid out to the User only upon acceptance of works or
					services in the scope of the Kwork by the User who ordered the relevant Kwork. At that, acceptance
					of works/services performed/rendered within the scope of the relevant Kwork shall be performed by
					the User who ordered their performance / rendering within 3 (Three) business days as of the moment
					the result of performed works was provided to the User, or as of the moment rendering of the
					relevant services was completed, whichever is applicable. If the work within the scope of the Kwork
					was not performed, or the services within the scope of Kwork were not rendered, or if the performed
					works / rendered services do not conform to the description of works or services given in the
					Kwork’s description, the User who ordered the Kwork may request from the User that performed works
					or rendered services within the scope of such Kwork either to re-work the result of works (if works
					were performed) or re-render the services (if services were rendered).
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Пользователь, заказывающий выполнение работ или оказание услуг в рамках соответствующего Кворка вправе отменить заказ Кворка, указав причину отмены заказа. При подтверждении отмены Пользователем, выполняющим работы или оказывающим услуги в рамках такого Кворка, стоимость Кворка возвращается Пользователю, заказавшему Кворк. В случае отмены заказа по причине просрочки выполнения работ или оказания услуг в рамках Кворка, стоимость Кворка возвращается Пользователю-заказчику моментально, без запроса подтверждения отмены заказа Кворка от Пользователя-исполнителя Кворка.'|t}
				</p>
				<p>Политика возвратов сервиса KWORK - <a href="{$baseurl}/refund">{$baseurl}/refund</a></p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					The User ordering performance of works or rendering of services within the scope of the relevant
					Kwork may cancel the Kwork order with indicating reason for cancellation. Upon confirmation of
					cancellation by the User performing works or rendering services within the scope of such Kwork the
					price of the Kwork is returned to the User who ordered the Kwork. Upon cancellation of the order due
					to expiration of the time limit for performance of works or rendering of services within the scope
					of the Kwork the price of the Kwork is returned to the customer User immediately, without receiving
					the Kwork order cancellation confirmation from the User performing the Kwork.
				</p>
				<p>KWORK'S Refund Policy - <a href="{$baseurl}/refund">{$baseurl}/refund</a></p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Продавцу запрещается запрашивать у других Пользователей оплату работ, выполненных / услуг, оказанных в рамках Кворков такого Пользователя вне Сервиса KWORK. Оператор оставляет за собой право деактивировать Личный Кабинет любого Пользователя, нарушившего настоящее положение.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					The User is prohibited to request other Users to be paid for the works performed / services rendered
					within the scope of that User’s Kworks outside of the KWORK Service. The Operator reserves the right
					to deactivate the Personal Account of any User who violates this provision.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Пользователь понимает и соглашается, что в тех случаях, когда выполнение работ в рамках соответствующего Кворка подразумевает создание какой-либо интеллектуальной собственности, то в момент приёмки соответствующего результата работ Пользователем, заказавшим создание такой интеллектуальной собственности в рамках соответствующего Кворка, Пользователь, в безотзывном и безусловном порядке, на весь срок охраны и без каких-либо ограничений любого рода (включая какие-либо территориальные ограничения) полностью передает, отчуждает и уступает (с полной гарантией прав и без каких-либо обременений) такому другому Пользователю исключительные интеллектуальные права на соответствующую интеллектуальную собственность, после приёмки результата работ не сохраняет за собой никаких прав любого рода в отношении такой интеллектуальной собственности, и лишается любых и всех прав на интеллектуальную собственность, включая права пользоваться, распоряжаться, уступать, отчуждать, передавать или предоставлять лицензии или любые другие права любого рода на такую интеллектуальную собственность любым третьим лицам.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					The User understands and agrees that in instances where performance of works within the scope of the
					relevant Kwork entails creation of certain intellectual property, then upon acceptance of the
					relevant result of performed works by the User who ordered creation of such intellectual property
					within the scope of the relevant Kwork the User irrevocably, unconditionally and for the entire term
					of protection and without any limitations whatsoever (including any territorial restrictions) fully
					transfers, conveys and assigns to that other User (with full title guarantee and free from any liens
					or encumbrances) the exclusive intellectual property rights in full in and to the relevant
					intellectual property, after acceptance of the result of works retains no rights whatsoever with
					regard to the intellectual property, and is deprived of any and all rights in and to the
					intellectual property, including the rights to use, dispose, assign, transfer or convey or license
					or grant any other right of any kind in or to the intellectual property to any third parties.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p class="centered">
					<strong>{'УРОВНИ И РЕЙТИНГ'|t}</strong>
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p class="centered">
					<strong>LEVELS AND RATING</strong>
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Каждому Пользователю Сервиса KWORK, выполняющему работы и оказывающему услуги в рамках Кворков, присваивается рейтинг. Рейтинг зависит от:'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					Each KWORK Service’s User who performs works and renders services within the scope of Kworks is
					given a rating. The rating depends on:
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'количества успешно выполненных Пользователем Кворков;'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						the quantity of Kworks successfully completed by the User;
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'соотношения позитивных и негативных Отзывов других Пользователей о качестве работ/услуг, выполняемых/оказываемых Пользователем в рамках Кворков такого Пользователя.'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						the correlation between the positive and negative Reviews of other Users with respect to the
						works/services performed/rendered by the User within the scope of that User’s Kworks.
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Оператор оставляет за собой право понизить Рейтинг Пользователя и приостановить показ Кворков Пользователя в Сервисе KWORK в следующих случаях:'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					The Operator reserves the right to lower the User’s Rating and suspend display of User’s Kworks in
					the KWORK Service in the following instances:
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'Пользователь отказался от выполнения работ / оказания услуг в рамках Кворка по неуважительной причине;'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						the User cancels performance of works / rendering of services within the scope of a Kwork for an
						invalid reason;
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'Пользователь неоднократно отказывается от выполнения работ / оказания услуг в рамках какого-либо Кворка;'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						the User cancels performance of works / rendering of services within the scope of a Kwork for an
						invalid reason;

					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'Пользователь игнорирует заказы других Пользователей на выполнение работ / оказание услуг, предлагаемых в рамках Кворков Пользователя.'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						the User ignores orders of other Users to perform works / render services offered within the
						scope of User’s Kworks.
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Количество и качество выполненных Кворков влияет на уровень Пользователя. Начинающим Пользователям, размещающим Кворки, присваивается уровень «Новичок». После успешного выполнения %s Кворков (при наличии положительного Рейтинга не менее %s%%) Пользователю присваивается уровень «Продвинутый». После %s успешно выполненных Кворков, при наличии положительного Рейтинга, Пользователю присваивается уровень «Профессионал».'|t:{App::config('level2num')}:{App::config('level2rate')}:{App::config('level3num')} }
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					The quantity and quality of completed Kworks influences the User’s level. Beginner-level Users
					placing Kworks are given the "Novice" level. Upon successful completion
					of {App::config('level2num')} Kworks (on the condition that the User has at least
					a {App::config('level2rate')}% positive Rating) the User is awarded the "Advanced" level. Upon
					successful completion of {App::config('level3num')} Kworks, on the condition that the User has a
					positive Rating, the User is awarded the "Professional" level.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Отметка «Высший Рейтинг» может быть присвоена отдельному Кворку в случае, если работы/услуги в рамках этого Кворка были успешно выполнены/оказаны не менее 20 (Двадцати) раз с положительными Отзывами Пользователей-заказчиков, и при этом Рейтинг Пользователя является максимальным (нет отрицательных Отзывов; мало или нет Кворков без Отзывов; нет Кворков, выполненных с просрочками; отсутствуют отмены Кворков по неуважительной причине).'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					The "Top Rating" mark may be awarded to a particular Kwork if the works/services within such Kwork’s
					scope were performed/rendered no less than 20 (Twenty) times with positive Reviews from
					Users-customers, and the User’s Rating is at the same time at the maximum level (no negative
					Reviews; little or no Kworks without Reviews; there are no Kworks completed with delays; no Kwork
					cancellations for invalid reasons).
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p class="centered">
					<strong>{'ОТЗЫВЫ'|t}</strong>
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p class="centered">
					<strong>REVIEWS</strong>
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					За совершение определённых действий с помощью Сервиса KWORK, в том числе, за выполнение работ и
					оказание услуг в рамках Кворков, Пользователи могут оставлять отзывы (<strong>"Отзывы"</strong>).
					Пользователи могут оставлять как позитивные, так и негативные Отзывы.
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					For performing certain actions within the KWORK Service, including for performance of works and
					rendering of services within the scope of Kworks, the Users may leave reviews
					(<strong>"Reviews"</strong>). Users are allowed to leave both positive and negative Reviews.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Все Отзывы являются публичными и доступны для ознакомления как Пользователям Сервиса KWORK, так и остальным пользователям сети Интернет.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					All Reviews are public and accessible by both Users of the KWORK Service and other Internet users.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Любой Отзыв может быть удалён Оператором как по единоличному усмотрению Оператора, так и по заявке Пользователя в Обратную Связь. Причинами удаления Отзыва могут являться следующие причины (но не ограничиваются ими):'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					Any Review may be removed by the Operator both at the Operator’s sole discretion or under a request
					of a User sent to Feedback. The reasons for removal of a Review may be the following (but are not
					limited to them):
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'информация в Отзыве недостоверна/неточна;'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						information in the Review is false/inaccurate;
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'Отзыв не обоснован (не подкреплен примерами, доказательствами, объяснением/обоснованием высказанной точки зрения) или содержит недоказуемые суждения, домыслы, прогнозы, субъективные оценочные суждения;'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						the Review is not substantiated (not substantiated by examples, proof, explanation of the given
						point of view) or contains improvable assertions, speculations, forecasts, subjective evaluative
						judgements;
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'Отзыв содержит оскорбления, ненормативную лексику, некорректные высказывания и т. п.'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						the Review contains insults, expletives, inappropriate claims;
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'Раздел "Отзывы" используется вместо приватных сообщений (текст представляет собой прямую переписку между Пользователями или обращение одного Пользователя к другому);'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						the "Reviews" Section is used instead of private messaging (i.e. the text is a direct
						communication between Users or is an address of one User to another);
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'Отзыв содержит фрагменты личной переписки (в том числе, скопированной из приватных сообщений в Сервисе KWORK), на опубликование которых получатель Отзыва не давал согласия;'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						the Review contains fragments of private messages (including copied from private messages in the
						KWORK Service) publication of which was not authorized by the recipient of the Review;
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'Отзыв не содержит детальной информации о конфликте сторон, что может привести к неверному пониманию Оператором и другими Пользователями описываемой в Отзыве ситуации;'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						the Review does not contain detailed information about the parties’ conflict, which may entail
						wrong understanding of the situation described in the Review by the Operator or other Users;
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'информация в Отзыве не имеет отношения к взаимоотношениям Пользователей (содержит рекламу услуг автора Отзыва и т.д.) или вводит Пользователей в заблуждение;'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						information in the Review does not relate to User relations (contains advertising of Review
						author’s services etc.) or may mislead Users;
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'несоответствие содержащихся в Отзыве претензий реальной ситуации;'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						Inconsistence of the claims in the Review with the real situation;
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'описанная в Отзыве ситуация не имеет отношения к Сервису KWORK (например, Отзыв касается некорректного общения Пользователей вне Сервиса KWORK т.п.).'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						the situation described in the Review does not relate to the KWORK Service (for example, the
						Review concerns inappropriate communication between Users outside the KWORK Service etc.).
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p class="centered">
					<strong>{'АРБИТРАЖ'|t}</strong>
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p class="centered">
					<strong>ARBITRATION</strong>
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'В случае возникновения между Пользователями Сервиса KWORK споров в отношении выполнения работ и оказания услуг в рамках какого-либо Кворка, Пользователи могут передать спор на рассмотрение в Арбитраж, проводимый сотрудниками Оператора.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					In case of disputes between the Users of the KWORK Service regarding performance of works and
					rendering of services within the scope of a particular Kwork the Users may transfer the dispute for
					resolution by Arbitration performed by Operator’s employees.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					Для передачи спора в Арбитраж Пользователю необходимо воспользоваться соответствующей
					функциональностью Личного Кабинета, либо направить заявку в произвольной форме через форму Обратной
					Связи, онлайн-чат или на почту <a href="mailto:info@kwork.ru" target="_blank">info@kwork.ru</a> с
					пометкой "В Арбитраж". Заявка должна содержать логин Пользователя-заявителя, логин
					Пользователя-ответчика, номер спорного Кворка и аргументы, подтверждающие позицию
					Пользователя-заявителя.
				</p>
			</td>
			{if !$isMobileApi}
				<td>
					<p>
						For transferring the dispute to Arbitration the User must utilize the relevant functionality of
						the Personal Account, or send an application in free form through Feedback, online chat, or via
						<a href="mailto:info@kwork.ru" target="_blank">info@kwork.ru</a>e-mail with the header "To
						Arbitration". The application must contain the login of the User-claimant, the login of the
						User-defendant, number of the disputed Kwork, and arguments confirming the position of
						User-claimant.
					</p>
				</td>
			{/if}
		{else}
			<td>
				<p>
					For transferring the dispute to Arbitration the User must utilize the relevant functionality of the
					Personal Account, or send an application in free form through Feedback, online chat, or via <a
							href="mailto:info@kwork.com" target="_blank">info@kwork.com</a>e-mail with the header "To
					Arbitration". The application must contain the login of the User-claimant, the login of the
					User-defendant, number of the disputed Kwork, and arguments confirming the position of
					User-claimant.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Срок рассмотрения направленных в Арбитраж заявок и вынесения решений составляет до 3 (Трёх) рабочих дней с момента получения заявки арбитрами Арбитража.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					The term of review of applications submitted to Arbitration and delivering a decision is up to 3
					(Three) business days as of receipt of the application by the Arbitration arbiters.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'При рассмотрении споров арбитры не оценивают творческую составляющую выполнения работ/оказания услуг в рамках спорного Кворка. Оценивается только соответствие выполненных работ/оказанных услуг условиям Кворка, то есть, описанию работ/услуг и согласованным в переписке между Пользователями требованиям, результат работы или оказанные услуги с объективной точки зрения, а также соответствие настоящим Условиям Предоставления Услуг, Договору-Оферте, и всем Связанным Документам.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					When reviewing disputes arbiters do not review the creative input used in performance of
					works/rendering of services within the scope of the disputed Kwork. Only conformance of the
					performed works/rendered services with the terms of the Kwork is reviewed, that is, conformance with
					the description of the works/services and conditions agreed by Users in message exchange through
					Personal Accounts, the result of works or the rendered services from the objective standpoint, as
					well as conformance with these Terms of Service, Offer Agreement, and all Related Documents.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'При наличии противоречивой информации в описании заказанного Кворка, при условии наличия полной и детализированной информации в разных блоках описания Кворка, преимущественно верной считается информация, указанная в блоке "Объём Кворка", далее информация, указанная в блоке "Описание Кворка", и на третьем месте информация, указанная в названии Кворка.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					If there is conflicting information in the description of an ordered Kwork, on the condition that
					full and detailed information is provided in the various Kwork description blocks, the information
					having priority shall be information given in the "Kwork Volume" block, then information given in
					the "Kwork Description" block, and then information given in the Kwork title.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Если работы/услуги в рамках определённого Кворка выполнены/оказаны, но Пользователь, заказавший работы/услуги в рамках этого Кворка не может проверить результат работ либо оказанные услуги, то заказ в отношении такого Кворка считается не выполненным.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					If works/services within the scope of a particular Kwork were performed/rendered, but the User who
					ordered the works/services within the scope of that Kwork is unable to review the result of
					performed works or the rendered services, then the order for such Kwork is deemed not completed.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Оставить Отзыв по спорному Кворку, спор в отношении которого решался через Арбитраж, нельзя.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					Leaving a Review for a disputed Kwork the dispute with respect to which was resolved via Arbitration
					is not possible.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Настоящий Раздел не применяется к спорам между Пользователями и третьими лицами, к которым применяется Политика Разрешения Споров Сервиса KWORK, доступная по следующей ссылке:'|t}
					<a href="{$baseurl}/resolution">{$baseurl}/resolution</a>.
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					This Section does not apply to disputes between Users and third parties, which are resolved under
					the KWORK Service Dispute Resolution Policy, available here: <a
							href="{$baseurl}/resolution">{$baseurl}/resolution</a>.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p class="centered">
					<strong>{'СРОК ДЕЙСТВИЯ И ПРЕКРАЩЕНИЕ ДЕЙСТВИЯ'|t}</strong>
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p class="centered">
					<strong>TERM AND TERMINATION</strong>
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Настоящие Условия Предоставления Услуг вступают в силу в момент принятия их условий Пользователем при регистрации Пользователем Личного Кабинета и принятия условий Договора-Оферты, неотъемлемую часть которого составляют настоящие Условия Предоставления Услуг, и действуют до момента либо отказа Пользователя от получения Услуг, либо прекращения предоставления Услуг Оператором в соответствии с положениями настоящих Условий Предоставления Услуг. При расторжении настоящих Условий Предоставления Услуг любой Стороной, предоставление и оказание Услуг незамедлительно прекращается без учёта причин такого расторжения.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					These Terms of Service come into effect upon their acceptance by the User when the User registers
					the Personal Account and accepts the terms of the Offer Agreement of which these Terms of Use form
					an integral part, and are valid until either cancellation of Services by the User or termination of
					Services by the Operator in accordance with the provisions of these Terms of Service. Upon any
					termination of these Terms of Service by any Party, provision and rendering of Services will be
					immediately terminated notwithstanding the reasons for such termination.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'При отказе Пользователя от получения Услуг, либо прекращения предоставления Услуг Оператором Пользователь не получает никаких возмещений предоплаченных сумм Цены Услуг за Услуги, не полученные Пользователем на момент прекращения использования Сервиса KWORK и получения Услуг. Тем не менее, Пользователь вправе вывести суммы Вознаграждения Пользователя, полученного за выполнение Пользователем работ и оказание Пользователем услуг в рамках Кворков Пользователя, для чего Пользователь обязан обратиться к Оператору посредством раздела "Обратная Связь" Сервиса KWORK.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					Upon cancellation of Services by the User or termination of Services by the Operator the User will
					not receive any refunds of pre-paid amounts of Services Fees for the Services not received by the
					User at the moment of cancellation of use of the KWORK Service and the Services. Notwithstanding
					this, the User may withdraw the amounts of User’s Remuneration for performance by User of works and
					rendering by User of services within the scope of User’s Kworks, for which purpose the User must
					contact the Operator via the "Feedback" section of the KWORK Service.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Пользователь соглашается, что Оператор вправе в одностороннем порядке прекратить оказание Услуг и расторгнуть настоящие Условия Предоставления Услуг в любой момент времени в случае несоблюдения и/или нарушения любых положений настоящих Условий Предоставления Услуг, Политики Конфиденциальности либо Политики Разрешения Споров'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					The User agrees that the Operator may unilaterally terminate rendering of the Services and these
					Terms of Service at any time in the event of User’s non-compliance and/or breach of any terms of
					these Terms of Service, Privacy Policy, or Dispute Resolution Policy.

				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'При расторжении настоящих Условий Предоставления Услуг, их положения, регулирующие гарантии и обязанности Пользователя, применимое право и юрисдикции, обязывающий арбитраж, возмещение ущерба, и любые и все положения, относящиеся к отказу Оператора от ответственности, ограничению ответственности Оператора будут продолжать действовать в полной юридической силе бессрочно после прекращения действия настоящих Условий Предоставления Услуг.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					Upon termination of these Terms of Service, its provisions governing User’s warranties and
					liabilities, governing law and jurisdictions, binding arbitration, indemnity, and any and all
					provisions relating to disclaimer of Operator’s warranties, limitation of Operator’s liabilities
					shall continue in full legal force and effect in perpetuity after termination of these Terms of
					Service.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p class="centered">
					<strong>{'СОБЛЮДЕНИЕ ПРАВОВЫХ НОРМ; РАЗРЕШЕНИЕ СПОРОВ'|t}</strong>
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p class="centered">
					<strong>COMPLIANCE AND DISPUTE RESOLUTION</strong>
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Используя Сервис KWORK и заказывая и получая Услуги Пользователь обязан соблюдать все применимые законы и обычаи делового оборота, действующие в соответствующий период времени.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					When using the KWORK Service and ordering and receiving the Services the User must comply with all
					the applicable laws and trade policies effective at the relevant time.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Процедура разрешения споров, вытекающих из жалоб третьих лиц, регулируется Политикой Разрешения Споров Сервиса KWORK, являющейся неотъемлемой частью настоящих Условий и доступной по следующей ссылке:'|t}
					<a href="{$baseurl}/resolution">{$baseurl}/resolution</a>.
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					The procedure for resolution of disputes arising out of claims of third parties is regulated by the
					KWORK Service Dispute Resolution Policy that forms an integral part hereof and is available via the
					following link: <a href="{$baseurl}/resolution">{$baseurl}/resolution</a>.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p class="centered">
					<strong>{'КОНФИДЕНЦИАЛЬНОСТЬ,  ПЕРСОНАЛЬНЫЕ ДАННЫЕ И ПОЛИТИКА В ОТНОШЕНИИ COOKIE-ФАЙЛОВ'|t}</strong>
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p class="centered">
					<strong>PRIVACY, PERSONAL DATA AND COOKIE POLICY</strong>
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Информация, которую Вам может быть необходимо указать в связи с использованием Вами Сервиса KWORK, может включать определённые персональные данные. Сбор, использование и обработка Оператором Ваших персональных данных, а также применяемая политика в отношении cookie-файлов, регулируются Политикой Конфиденциальности Сервиса KWORK, доступной по следующей ссылке'|t}
					: <a href="{$baseurl}/privacy">{$baseurl}/privacy</a>.
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					The information required to be provided in connection with your use of the KWORK Service may include
					certain personal data. Collection, use and processing of your personal data by the Operator, as well
					as the applicable cookie file policy, are governed by the KWORK Service’s the Privacy Policy,
					available here: <a href="{$baseurl}/privacy">{$baseurl}/privacy</a>.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p class="centered">
					<strong>{'СОГЛАСИЕ НА ПРОВЕРКУ'|t}</strong>
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p class="centered">
					<strong>CONSENT TO MONITOR</strong>
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Оператор оставляет за собой право, но не несёт обязанность, осуществлять проверки Вашего Личного Кабинета и действий, осуществляемых Вами в Сервисе KWORK посредством Личного Кабинета, для целей подтверждения того, что Вы используете Сервис KWORK и Услуги в полном соответствии с настоящими Условиями Предоставления Услуг и применимым правом. Настоящим Вы предоставляете Оператору Ваши явно выраженные разрешение и согласие на осуществление проверки Вашего Личного Кабинета и всех действий, осуществляемых посредством Вашего Личного Кабинета, для целей подтверждения законного использования Сервиса KWORK и выявления случаев запрещённого использования и потенциальных нарушений настоящих Условий Предоставления Услуг и применимого права.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					The Operator reserves the right, but is under no obligation, to monitor your Personal Account and
					your activities in the KWORK Service related to your Personal Account for the purposes of confirming
					that you use the KWORK Service and the Services in full accordance with these Terms of Service and
					applicable law. You hereby grant the Operator your express permission and consent to monitor your
					Personal Account and all activities performed under your Personal Account for purposes of confirming
					legal use of the KWORK Service and identifying instances of restricted use and potential violations
					of these Terms of Service and applicable law.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p class="centered">
					<strong>{'ОГРАНИЧЕНИЯ'|t}</strong>
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p class="centered">
					<strong>RESTRICTIONS</strong>
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Принимая настоящие Условия Предоставления Услуг Вы явным образом соглашаетесь с тем, что Вам явным образом запрещено, а также явным образом запрещено позволять любому третьему лицу, осуществлять любые из перечисленных ниже действий, а также любых действий, сходных с перечисленными по своей природе или намерению, и что осуществление любого из таких действий будет являться существенным нарушением настоящих Условий Предоставления Услуг:'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					By accepting these Terms of Service you expressly agree that you are expressly prohibited to, and
					are prohibited to allow any third party to, perform any of the following actions, and any actions
					similar in nature or intent thereto, and that performance of any such actions shall constitute a
					material breach of these Terms of Service:
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'осуществлять показ, копировать, хранить, модифицировать, адаптировать, осуществлять обратное проектирование, продавать, опубликовывать, доводить до всеобщего сведения либо осуществлять повторное распространение Сервиса KWORK либо любых служб или функциональности Сервиса KWORK, доступных Вам посредством Сервиса KWORK (включая Услуги), в том числе, но не ограничиваясь этим, любых результатов интеллектуальной деятельности и объектов интеллектуальной собственности, являющихся составной частью Сервиса KWORK. Во избежание недоразумений, указанное ограничение не распространяется на распространение Вами информации о Сервисе KWORK и Услугах третьим лицам, в том числе, потенциальным Пользователям Сервиса KWORK;'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						display, copy, store, modify, adapt, reverse engineer, sell, publish, make available to the
						public or redistribute the KWORK Service or any services or functionality made available to you
						via the KWORK Service (including the Services), including but not limited to any results of
						intellectual activity and intellectual property items comprising integral parts of the KWORK
						Service. For the avoidance of doubt, this limitation does not extend to dissemination by you of
						information about the KWORK Service and the Services to third parties, including potential Users
						of the KWORK Service;
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'позволять любому третьему лицу осуществлять доступ к Сервису KWORK и использование Сервиса KWORK с использованием Ваших логина и пароля;'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						allow any third party to access and use the KWORK Service using your login and password;
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'использовать Сервис KWORK для любых незаконных целей;'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						use the KWORK Service for any illegal purposes;
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'выдавать себя за другое физическое или юридическое лицо, либо представлять ложную информацию об аффилированности с таким физическим или юридическим лицом;'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						impersonate or falsely claim affiliation with any person or entity;
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'сообщать ложную информацию, порочащие сведения о третьих лицах, либо осуществлять мошеннические действия в отношении таких лиц;'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						misrepresent, defraud or defame others;
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'представлять Сервис KWORK и/или Оператора и/или аффилированных с ним лиц в негативном свете'|t}
						;
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						negatively present the KWORK Service and/or the Operator and/or its affiliates;
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'передавать вредоносный программный код посредством Сервиса KWORK или с его помощью;'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						transmit malicious software code via or with help of the KWORK Service;
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'собирать информацию о других Пользователях Сервиса посредством Сервиса KWORK;'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						collect information of other Service’s Users through the KWORK Service;
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'прерывать нормальную работу или вмешиваться в работу Сервиса KWORK или любой функциональности, содержащейся в Сервиса KWORK либо предоставляемой посредством него (включая Услуги), либо любых серверов, используемых для предоставления Сервиса KWORK, либо необоснованно мешать любым образом использованию Сервиса KWORK другими лицами;'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						interrupt the normal operation of or tamper with the KWORK Service or any functionality
						contained in or provided through the KWORK Service (including the Services), or any servers used
						in providing the KWORK Service, or to unreasonably affect others’ use of the KWORK Service in
						any way;
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'заниматься вторжением в частную жизнь других пользователей Сервиса KWORK или любых лиц посредством фишинга, хищения персональных данных и иными сходными способами;'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						invade privacy of other KWORK Service users or any persons by means of phishing, identity theft
						and other means;
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'использовать программы-боты, программы-пауки, оффлайн-программы для считывания и иные автоматизированные программные системы для осуществления доступа к Сервису KWORK и использования Сервиса KWORK;'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						use bots, spiders, offline readers or other automated software systems to access or use the
						KWORK Service;
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'иным образом использовать Сервис KWORK для занятия деятельностью, являющейся незаконной по праву любой юрисдикции либо деятельностью, поощряющей уголовно наказуемое поведение;'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						otherwise use the KWORK Service for engaging in any activities that are illegal under laws of
						any jurisdiction or that encourage criminal conduct;
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'использовать Сервис KWORK для любых целей, отличных от тех, которые явным образом разрешены настоящими Условиями Предоставления Услуг.'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						use the KWORK Service for any purposes other than those expressly permitted under these Terms of
						Service.
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					Нарушение настоящего Раздела <strong>"Ограничения"</strong> настоящих Условий Предоставления Услуг
					будет являться существенным нарушением Вами настоящих Условий Предоставления Услуг, и прямым
					нарушением применимого законодательства. Неразрешённые доступ и использование Сервиса KWORK, включая
					любое использование в обход настоящих Условий Предоставления Услуг и раздела
					<strong>"Ограничения"</strong> настоящих Условий Предоставления Услуг запрещено и может повлечь за
					собой уголовную, и/или гражданско-правовую и/или административную и/или дисциплинарную
					ответственность, включая судебное разбирательство против Вас, инициированное Оператором либо
					соответствующими правоохранительными органами.
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					Breach of this <strong>"Restrictions"</strong> Section of these Terms of Service shall constitute a
					material breach of these Terms of Service by you and a direct violation of applicable laws.
					Unauthorized access and use of the KWORK Service, including any use in contravention of these Terms
					of Service and the <strong>"Restrictions"</strong> section of these Terms of Service, is prohibited
					and may result in criminal prosecution and/or civil and/or disciplinary or administrative liability,
					including court action against you initiated by the Operator or relevant law enforcement
					authorities.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p class="centered">
					<strong>{'СООБЩЕНИЯ О НАРУШЕНИИ УСЛОВИЙ ПРЕДОСТАВЛЕНИЯ УСЛУГ'|t}</strong>
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p class="centered">
					<strong>REPORTING VIOLATIONS OF TERMS OF SERVICE</strong>
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Вы можете сообщить Оператору о нарушениях настоящих Условий Предоставления Услуг другими пользователями Сервиса KWORK путём направления сообщения электронной почты по следующему адресу:'|t}
					<a href="mailto:info@kwork.ru" target="_blank">info@kwork.ru</a>.
				</p>
			</td>
			{if !$isMobileApi}
				<td>
					<p>
						You may inform the Operator about violations of these Terms of Service by other users of the
						KWORK Service by sending an e-mail to the following address: <a href="mailto:info@kwork.ru"
																						target="_blank">info@kwork.ru</a>.
					</p>
				</td>
			{/if}
		{else}
			<td>
				<p>
					You may inform the Operator about violations of these Terms of Service by other users of the KWORK
					Service by sending an e-mail to the following address: <a href="mailto:info@kwork.com"
																			  target="_blank">info@kwork.com</a>.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p class="centered">
					<strong>ПРИОСТАНОВЛЕНИЕ РАБОТЫ/БЛОКИРОВКА ЛИЧНОГО КАБИНЕТА</strong>
				</p>
			</td>
		{/if}
		{if !$isMobileApi}
			<td>
				<p class="centered">
					<strong>SUSPENSION/BLOCKING OF PERSONAL ACCOUNT</strong>
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Вы можете прекратить осуществление доступа к Сервису KWORK и его использование, а также прекратить получение Услуг в случае, если Вы более не желаете использовать Сервис KWORK в любой момент времени.'|t}
				</p>
			</td>
			{if !$isMobileApi}
				<td>
					<p>
						You may inform the Operator about violations of these Terms of Service by other users of the
						KWORK Service by sending an e-mail to the following address: <a href="mailto:info@kwork.ru"
																						target="_blank">info@kwork.ru</a>.
					</p>
				</td>
			{/if}
		{else}
			<td>
				<p>
					You may inform the Operator about violations of these Terms of Service by other users of the KWORK
					Service by sending an e-mail to the following address: <a href="mailto:info@kwork.com"
																			  target="_blank">info@kwork.com</a>.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Оператор оставляет за собой право расследовать любые и все возможные нарушения настоящих Условий Предоставления Услуг и всех документов, относящихся к использованию Вами Сервиса KWORK и Услугам, включая, но не ограничиваясь этим, Договора-Оферты, и предпринимать любые и все необходимые либо надлежащие меры для разрешения таких нарушений, по разумному усмотрению Оператора. Оператор вправе приостановить работу, заблокировать, изменить либо ограничить Ваш доступ или полностью прекратить предоставление Вам доступа к Сервису KWORK в любой момент времени по своему единоличному усмотрению, с уведомлением Вас либо без уведомления, если существуют признаки нарушения Вами настоящих Условий Предоставления Услуг.'|t}
				</p>
			</td>
		{/if}
		{if !$isMobileApi}
			<td>
				<p>
					The Operator reserves the right to investigate any and all suspected violations of these Terms of
					Service and all documents relating to your use of the KWORK Service and the Services, including but
					not limited to the Offer Agreement, and to take any and all necessary or appropriate actions to
					remedy such violations, as the Operator may determine appropriate. The Operator may suspend, block,
					modify, or restrict your access to the KWORK Service at any time at its sole discretion, with or
					without notice to you, if there is an indication that you have breached these Terms of Service.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					Если выяснится, что Вы допустили существенное нарушение настоящих Условий Предоставления Услуг,
					такое как нарушение раздела <strong>"Ограничения"</strong> и иных аналогичных разделов настоящих
					Условий Предоставления Услуг путём использования Сервиса KWORK в незаконных целях, либо нарушаете
					интеллектуальные права Оператора или его лицензиаров или третьих лиц, указанные в разделе <strong>"Интеллектуальные
						Права"</strong> настоящих Условий Предоставления Услуг, Оператор оставляет за собой право
					одновременно приостановить работу Вашего Личного Кабинета на неопределённый срок и уведомить
					соответствующие государственные органы в соответствующей юрисдикции если Ваши действия могут
					расцениваться как противоправные и наказуемые в соответствии с применимым правом, а также, в случае
					получения Жалобы от соответствующего Заявителя в соответствии с Политикой Разрешения Споров KWORK,
					инициировать разбирательство по такой Жалобе.
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					If you are found to be in a material breach of these Terms of Service, such as being found violating
					the <strong>"Restrictions"</strong> and other similar sections of these Terms of Service by using
					the KWORK Service for illegal purposes or infringing Operator’s or its licensors’ or third parties’
					intellectual property rights discussed in the <strong>"Intellectual Property Rights"</strong>
					section of these Terms of Service, the Operator reserves the right to both suspend your Personal
					Account for an indeterminable amount of time and notify the relevant government officials in the
					relevant jurisdiction if your actions may be classified as illegal and punishable under applicable
					laws, as well as initiate proceedings under a Complaint if such Complaint is filed in accordance
					with the KWORK Dispute Resolution Policy.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p class="centered">
					<strong>{'ДОСТУП, РАБОТОСПОСОБНОСТЬ И ДОСТУПНОСТЬ'|t}</strong>
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p class="centered">
					<strong>ACCESS, UPTIME AND AVAILABILITY</strong>
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Сервис KWORK и Услуги могут быть недоступны в некоторых странах и могут предоставляться только на определённых языках. Сервис KWORK может зависеть от телекоммуникационных сетей. Оператор оставляет за собой право, по собственному усмотрению, изменять, улучшать и исправлять Сервис KWORK. Сервис KWORK и Услуги могут быть недоступны в период проведения технического обслуживания и в другое время. Оператор может принять решение о прекращении работы Сервиса KWORK или любой его части по собственному усмотрению и в любое время. Оператор не даёт гарантий и не делает заявлений о том, что Сервис KWORK или какая-либо его часть или функциональность является пригодной или доступной для использования в какой-либо конкретной юрисдикции, а также не даёт гарантий и не делает заявлений о том, что Ваш доступ к Сервису KWORK не будет содержать ошибок, вирусов, будет бесперебойным, а также что серверы, на которых развёрнут Сервис KWORK, будут постоянно находиться в рабочем состоянии.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					The KWORK Service and the Services may not be available in some countries and may be provided only
					in selected languages. The KWORK Service may be network dependent. The Operator reserves the right,
					in its sole discretion, to change, improve and correct the KWORK Service. The KWORK Service and the
					Services may not be available during maintenance breaks and other times. The Operator may decide to
					discontinue the KWORK Service or any part thereof in its sole discretion and at any given moment.
					The Operator does not represent or warrant that the KWORK Service, or any part or functionality
					thereof, is appropriate or available for use in any particular jurisdiction, and does not represent
					or warrant that your access to the KWORK Service will be error-free, virus-free, uninterrupted, and
					that the servers on which the KWORK Service is deployed will be up permanently.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p class="centered">
					<strong>{'ИНТЕЛЛЕКТУАЛЬНЫЕ ПРАВА'|t}</strong>
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p class="centered">
					<strong>INTELLECTUAL PROPERTY RIGHTS</strong>
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Сервис KWORK и Услуги предоставляются Вам по модели "Программа для ЭВМ как Услуга" ("Software-as-a-Service"). Вам не предоставляются какие-либо имущественные интеллектуальные права в отношении Сервиса KWORK, по лицензии либо иным образом.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					The KWORK Service and the Services are provided to you under the "SaaS" ("Software-as-a-Service")
					model. No intellectual property rights with respect to the KWORK Service are granted to you under a
					license or otherwise.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Все права, правовой титул и правомочия, включая, но не ограничиваясь этим, исключительные авторские права и иные интеллектуальные права в отношении Сервиса KWORK и все его части, элементы и разделы, включая без ограничений любой и весь компьютерный код, технологии, программное ядро веб-сайта, графические темы, объекты, логотипы, художественные элементы, элементы меню, пользовательский интерфейс, встроенные службы, функциональность, дизайн Сервиса KWORK, система обмена сообщениями, текст, иллюстрации, фотографии, графические произведения, аудиофайлы, видеофайлы, аудиовизуальные файлы, и иные материалы и информационное наполнение (контент), доступные в Сервисе KWORK или посредством Сервиса KWORK, принадлежат Оператору и/или его лицензиарам и/или третьим лицам, являются интеллектуальной собственностью указанных лиц, и Оператор, и его лицензиары и третьи лица сохраняют за собой все права, правовой титул и правомочия на указанные объекты интеллектуальной собственности. Всё информационное наполнение (контент) Сервиса KWORK, включая подборку, способ размещения, а также внешний вид, охраняются различными национальными законами об авторском праве, о товарных знаках, о секретах производства, а также международными соглашениями и конвенциями. Никакие права, правовые титулы, лицензии или иные правомочия на любое информационное наполнение (контент), как и какие-либо патентные права, права на товарные знаки, авторские права или иные любые интеллектуальные права не передаются, не отчуждаются, не предоставляются по лицензии либо иным образом передаются Вам при осуществлении Вами доступа к Сервису KWORK и его использования, и Оператор, либо лицо, предоставившее соответствующую интеллектуальную собственность, во все времена сохраняет за собой все права, правовой титул и правомочия на любую такую интеллектуальную собственность, доступ к которой или использование которой Вы можете осуществлять посредством Сервиса KWORK.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					All rights, title and interests, including but not limited to the exclusive copyrights and other
					intellectual property rights in and to the KWORK Service and all parts, elements and sections
					thereof including without limitation any and all computer code, technology, website engine, themes,
					objects, logos, artwork, menu items, user interface, embedded services, functionality, design of the
					KWORK Service, message exchange system, text, illustrations, photographs, graphics, audio files,
					video files, audio-visual files, and other materials and content available on or through the KWORK
					Service are owned by the Operator and/or its licensors and/or third parties, constitute intellectual
					property of said parties, and the Operator and its licensors and such third parties retain all
					right, title, and interest in and to these intellectual property items. All contents of the KWORK
					Service, including the selection, arrangement, and look and feel, are protected by various national
					copyright, trademark and trade secret laws and by international treaties and conventions. No right,
					title, license or other interest in any of the contents or any patent, trademark, copyright or other
					intellectual property rights are transferred, assigned, licensed or otherwise conveyed to you by
					your access to and use of this KWORK Service, and the Operator, or the party that provided the
					relevant intellectual property, at all times retains all right, title, and interest in any such
					intellectual property that you may be accessing or using on the KWORK Service.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Принимая настоящие Условия Предоставления Услуг Вы явным образом соглашаетесь с тем, что Вам явным образом запрещено, а также явным образом запрещено позволять любому третьему лицу, осуществлять любые из перечисленных ниже действий, а также любых действий, сходных с перечисленными по своей природе или намерению, и что осуществление любого из таких действий будет являться существенным нарушением настоящих Условий Предоставления Услуг:'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					By accepting these Terms of Service you expressly agree that you are expressly prohibited to, and
					are prohibited to allow any third party to, perform any of the following actions, and any actions
					similar in nature or intent thereto, and that performance of any such actions shall constitute a
					material breach of these Terms of Service:
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'использовать Сервис KWORK для любых целей, отличных от тех, которые явным образом разрешены настоящими Условиями Предоставления Услуг.'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						copy, replicate, distribute, modify, remake, republish, download, post, display, perform, add
						to, abridge, compile, adapt, translate, derive source code from, disassemble, decompile,
						reverse-engineer, or create derivative works based on the KWORK Service or any part or portion
						thereof, update, broadcast, make available to the general public, or otherwise transmit,
						disseminate or use in any similar way or manner whatsoever the KWORK Service, any of its
						functionality, or any of the KWORK Service’s contents;
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'продавать, предоставлять по лицензии, предоставлять на безвозмездной основе либо передавать Ваш доступ к Сервису KWORK либо Вашего Личного Кабинета в Сервисе KWORK;'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						sell, license, grant on a royalty-free basis or transfer access to the KWORK Service or your
						Personal Account within the KWORK Service;
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'разрабатывать, распространять либо развёртывать любой сервис или веб-сайт, основанный на Сервисе KWORK либо сходный с Сервисом KWORK до степени смешения;'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						develop, distribute or host any service or website that is based on, or is confusingly similar
						to, the KWORK Service;
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'удалять, скрывать либо изменять любые уведомления об авторских правах, правах на товарные знаки либо любые иные уведомления о правообладании, размещённые в любой части информационного наполнения (контента) Сервиса KWORK;'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						remove, obscure or alter any copyright, trademark, or other proprietary notice appearing in any
						part of the KWORK Service’s contents;
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'осуществлять любые иные действия, которые могут нарушать либо поставить под угрозу нарушения интеллектуальные права Оператора, его лицензиаров и третьих лиц в отношении Сервиса KWORK.'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						perform any other actions that may infringe or put under threat of infringement intellectual
						property rights of the Operator, its licensors or third parties with respect to the KWORK
						Service.
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Все наименования товаров либо услуг либо интерактивных служб (в том числе, Услуг), фирменные наименования, слоганы, логотипы, а также иные товарные знаки, знаки обслуживания и обозначения, отображаемые в Сервисе KWORK, являются собственностью Оператора, его лицензиаров и третьих лиц. Оператор, его лицензиары и третьи лица оставляют за собой любые и все права на такие обозначения. Использование либо противоправное использование Вами указанных обозначений или любых иных сходных материалов запрещено и может являться нарушением норм применимого права.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					All of the product or service or interactive service names (including, inter alia, the Services),
					trade names, slogans, logos, and other trademarks and service marks and designations appearing on
					the KWORK Service are the property of the Operator, its licensors, affiliates, or third parties. The
					Operator, its licensors and third parties retain any and all rights in these designations. The use
					or misuse by you of these designations or any other similar materials is prohibited and may be in
					violation of applicable law.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Вы понимаете и соглашаетесь с тем, что Вы не обладаете никакими законными интересами, денежными либо иными, в отношении любых особенностей, функциональности либо информационного наполнения (контента), содержащихся в Сервисе KWORK, в том числе Услуг.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					You understand and agree that you have no legal interest, monetary or otherwise, in any feature,
					functionality or content contained on the KWORK Service, including the Services.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Настоящим Вы явным образом соглашаетесь, что Оператор, лицензиары Оператора и соответствующие третьи лица вправе защищать путём приведения в исполнение в принудительном порядке их интеллектуальные права в отношении Сервиса KWORK в максимально полном объёме, доступном по любому применимому праву, в случае нарушения Вами их интеллектуальных прав.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					You hereby expressly agree that the Operator, its licensors and relevant third parties are entitled
					to enforce their intellectual property rights with respect of the KWORK Service to the fullest
					extent of any applicable law in case of any violation of their intellectual property rights by you.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p class="centered">
					<strong>{'ОТКАЗ ОТ ГАРАНТИЙ'|t}</strong>
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p class="centered">
					<strong>DISCLAIMER OF WARRANTIES</strong>
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'ВЫ ЯВНЫМ ОБРАЗОМ ПРИЗНАЁТЕ И СОГЛАШАЕТЕСЬ, ЧТО ВЫ ОСУЩЕСТВЛЯЕТЕ ДОСТУП К СЕРВИСУ KWORK И ИСПОЛЬЗУЕТЕ СЕРВИС KWORK, А ТАКЖЕ УСЛУГИ ПО СВОЕМУ СОБСТВЕННОМУ УСМОТРЕНИЮ И ПОД СВОЮ ЕДИНОЛИЧНУЮ ОТВЕТСТВЕННОСТЬ. СЕРВИС KWORK И ЛЮБАЯ ЕГО ЧАСТЬ ИЛИ РАЗДЕЛ, В ТОМ ЧИСЛЕ, НО НЕ ОГРАНИЧИВАЯСЬ ЭТИМ, ЛЮБЫЕ ИНТЕРАКТИВНЫЕ СЛУЖБЫ И ФУНКЦИОНАЛЬНОСТЬ, ДОСТУПНЫЕ ВАМ ПОСРЕДСТВОМ СЕРВИСА KWORK, А ТАКЖЕ УСЛУГИ ПРЕДОСТАВЛЯЮТСЯ И ОКАЗЫВАЮТСЯ НА УСЛОВИЯХ "КАК ЕСТЬ", "СО ВСЕМИ НЕДОСТАТКАМИ" И "ПО МЕРЕ НАЛИЧИЯ". В МАКСИМАЛЬНО РАЗРЕШЁННОЙ ЛЮБЫМ ПРИМЕНИМЫМ ПРАВОМ СТЕПЕНИ ОПЕРАТОР И ЕГО АФФИЛИРОВАННЫЕ ЛИЦА ЯВНЫМ ОБРАЗОМ ОТКАЗЫВАЮТСЯ ОТ ПРЕДОСТАВЛЕНИЯ КАКИХ-ЛИБО ГАРАНТИЙ ЛЮБОГО РОДА В ОТНОШЕНИИ СЕРВИСА KWORK, КАК ЯВНО ВЫРАЖЕННЫХ, ТАК И ПОДРАЗУМЕВАЕМЫХ, В ТОМ ЧИСЛЕ, НО НЕ ОГРАНИЧИВАЯСЬ ЭТИМ, ПОДРАЗУМЕВАЕМЫХ ГАРАНТИЙ КОММЕРЧЕСКОЙ ПРИГОДНОСТИ, ПРИГОДНОСТИ ДЛЯ ОПРЕДЕЛЁННОГО ИСПОЛЬЗОВАНИЯ ИЛИ ОПРЕДЕЛЁННОЙ ЦЕЛИ, А ТАКЖЕ ГАРАНТИЙ ОТСУТСТВИЯ НАРУШЕНИЙ ПРАВ. В МАКСИМАЛЬНО РАЗРЕШЁННОЙ ЛЮБЫМ ПРИМЕНИМЫМ ПРАВОМ СТЕПЕНИ, НИ ОПЕРАТОР, НИ КАКИЕ-ЛИБО АФФИЛИРОВАННЫЕ С НИМ ЛИЦА НЕ ДАЮТ НИКАКИХ ГАРАНТИЙ И НЕ ДЕЛАЮТ ЗАЯВЛЕНИЙ В ОТНОШЕНИИ ТОЧНОСТИ ИЛИ ПОЛНОТЫ ИНФОРМАЦИОННОГО НАПОЛНЕНИЯ (КОНТЕНТА), ДОСТУПНОГО В СЕРВИСЕ KWORK ИЛИ ПОСРЕДСТВОМ СЕРВИСА KWORK, ИЛИ ИНФОРМАЦИОННОГО НАПОЛНЕНИЯ (КОНТЕНТА) ЛЮБЫХ ДРУГИХ ИНТЕРНЕТ-РЕСУРСОВ ИЛИ МОБИЛЬНЫХ РЕСУРСОВ, СВЯЗАННЫХ С СЕРВИСОМ KWORK, ЛИБО ССЫЛКИ НА КОТОРЫЕ РАЗМЕЩЕНЫ В СЕРВИСЕ KWORK. ОПЕРАТОР СОХРАНЯЕТ ЗА СОБОЙ ПРАВО ПО СВОЕМУ ЕДИНОЛИЧНОМУ И ИСКЛЮЧИТЕЛЬНОМУ УСМОТРЕНИЮ ИЗМЕНЯТЬ, МОДИФИЦИРОВАТЬ, ДОБАВЛЯТЬ, УДАЛЯТЬ ЛЮБУЮ ЧАСТЬ СЕРВИСА KWORK ИЛИ  ПРЕКРАЩАТЬ ДОСТУП К ЛЮБОЙ ЧАСТИ СЕРВИСА KWORK, А ТАКЖЕ ПРЕКРАЩАТЬ ПРЕДОСТАВЛЕНИЕ УСЛУГ ПОЛНОСТЬЮ ИЛИ В ЧАСТИ, В ЛЮБОЙ МОМЕНТ ВРЕМЕНИ.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					YOU EXPRESSLY ACKNOWLEDGE AND AGREE THAT YOU ACCESS AND USE THE KWORK SERVICE, AS WELL AS THE
					SERVICES AT YOUR SOLE DISCRETION AND YOUR SOLE RISK. THE KWORK SERVICE, AND ANY PORTION OR SECTION
					OF THE KWORK SERVICE, INCLUDING BUT NOT LIMITED TO ANY INTERACTIVE SERVICES AND FUNCTIONALITY
					AVAILABLE TO YOU THROUGH THE KWORK SERVICE, AS WELL AS THE SERVICES ARE PROVIDED AND RENDERED "AS
					IS", "WITH ALL FAULTS" AND "AS AVAILABLE". TO THE FULLEST EXTENT PERMITTED BY ANY APPLICABLE LAW,
					THE OPERATOR AND ITS AFFILIATES EXPRESSLY DISCLAIM ANY AND ALL WARRANTIES OF ANY KIND WITH RESPECT
					TO THE KWORK SERVICE, WHETHER EXPRESS OR IMPLIED, INCLUDING, WITHOUT LIMITATION, THE IMPLIED
					WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR USE OR PURPOSE AND NON-INFRINGEMENT. TO THE
					MAXIMUM EXTENT ALLOWED UNDER ANY APPLICABLE LAWS, NEITHER THE OPERATOR NOR ANY OF ITS AFFILIATES
					MAKE ANY WARRANTIES OR REPRESENTATIONS ABOUT THE ACCURACY OR COMPLETENESS OF CONTENT AVAILABLE ON OR
					THROUGH THE KWORK SERVICE OR THE CONTENT OF ANY OTHER INTERNET OR MOBILE RESOURCES RELATING TO THE
					KWORK SERVICE OR LINKS TO WHICH ARE CONTAINED IN THE SERVICE. THE OPERATOR RESERVES THE RIGHT, IN
					ITS SOLE AND EXCLUSIVE DISCRETION, TO CHANGE, MODIFY, ADD, REMOVE OR DISABLE ACCESS TO ANY PORTION
					OF THE KWORK SERVICE, AS WELL AS TERMINATE THE SERVICES FULLY OR PARTIALLY, AT ANY MOMENT OF TIME.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p class="centered">
					<strong>{'ОГРАНИЧЕНИЕ ОТВЕТСТВЕННОСТИ'|t}</strong>
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p class="centered">
					<strong>LIMITATION OF LIABILITY</strong>
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'В МАКСИМАЛЬНО РАЗРЕШЁННОЙ ЛЮБЫМ ПРИМЕНИМЫМ ПРАВОМ СТЕПЕНИ, ОПЕРАТОР, ЕГО АКЦИОНЕРЫ, ДОЧЕРНИЕ КОМПАНИИ, АФФИЛИРОВАННЫЕ ЛИЦА, ЛИЦЕНЗИАРЫ, ПОСТАВЩИКИ ИНТЕРНЕТ-УСЛУГ И ВЕБСАЙТОВ, ПОСТАВЩИКИ ИНФОРМАЦИОННОГО НАПОЛНЕНИЯ (КОНТЕНТА), РАБОТНИКИ, ДОЛЖНОСТНЫЕ ЛИЦА, РУКОВОДИТЕЛИ И АГЕНТЫ НИ ПРИ КАКИХ ОБСТОЯТЕЛЬСТВАХ НЕ БУДУТ НЕСТИ ПЕРЕД ВАМИ ИЛИ ЛЮБЫМ ИНЫМ ЛИЦОМ ОТВЕТСТВЕННОСТЬ ПО ЛЮБОЙ ТЕОРИИ ОТВЕТСТВЕННОСТИ (ВЫТЕКАЮЩЕЙ ИЗ ДОГОВОРА, ГРАЖДАНСКОГО ПРАВОНАРУШЕНИЯ, ЗАКОНА ИЛИ ИНЫМ ОБРАЗОМ) ЗА ЛЮБЫЕ СЛУЧАЙНЫЕ, ПРЯМЫЕ, КОСВЕННЫЕ, ШТРАФНЫЕ, ФАКТИЧЕСКИЕ,  ПОСЛЕДУЮЩИЕ, ОСОБЫЕ, ПРИСУЖДАЕМЫЕ В ПОРЯДКЕ НАКАЗАНИЯ ИЛИ ИНЫЕ УБЫТКИ, В ТОМ ЧИСЛЕ ЗА ПОТЕРЮ ДОХОДА ИЛИ УПУЩЕННУЮ ВЫГОДУ, МОРАЛЬНЫЙ УЩЕРБ, МОРАЛЬНЫЙ ВРЕД, УБЫТКИ, СВЯЗАННЫЕ С УТРАТОЙ ДЕЛОВОЙ РЕПУТАЦИИ, ДАННЫХ ИЛИ ПОТЕРЕЙ ЛЮБЫХ ДРУГИХ НЕМАТЕРИАЛЬНЫХ ЦЕННОСТЕЙ, ПОНЕСЁННЫХ В РЕЗУЛЬТАТЕ ОСУЩЕСТВЛЕНИЯ ВАМИ ДОСТУПА К СЕРВИСУ KWORK И/ИЛИ ИСПОЛЬЗОВАНИЯ ВАМИ СЕРВИСА KWORK ИЛИ ВАШЕЙ НЕСПОСОБНОСТИ ПОЛУЧИТЬ ДОСТУП К СЕРВИСУ KWORK ИЛИ ИСПОЛЬЗОВАТЬ СЕРВИС KWORK ИЛИ КАКУЮ-ЛИБО ЕГО ЧАСТЬ ИЛИ КОМПОНЕНТ, ДАЖЕ ЕСЛИ ОПЕРАТОР БЫЛ УВЕДОМЛЕН О ВОЗМОЖНОСТИ ВОЗНИКНОВЕНИЯ ТАКИХ УБЫТКОВ. ВЫ ПРИНИМАЕТЕ НА СЕБЯ ПОЛНУЮ ОТВЕТСТВЕННОСТЬ ЗА ЛЮБЫЕ УБЫТКИ, ИЗДЕРЖКИ ИЛИ УЩЕРБ ЛЮБОГО РОДА, ВОЗНИКАЮЩИЕ В РЕЗУЛЬТАТЕ ОСУЩЕСТВЛЕНИЯ ВАМИ ДОСТУПА К СЕРВИСУ KWORK И/ИЛИ ИСПОЛЬЗОВАНИЯ ВАМИ СЕРВИСА KWORK ИЛИ ВАШЕЙ НЕСПОСОБНОСТИ ПОЛУЧИТЬ ДОСТУП К СЕРВИСУ KWORK ИЛИ ИСПОЛЬЗОВАТЬ СЕРВИС KWORK. НАСТОЯЩЕЕ ПОЛОЖЕНИЕ ТАКЖЕ ОТНОСИТСЯ К УСЛУГАМ, ПРЕДОСТАВЛЯЕМЫМ ВАМ ОПЕРАТОРОМ ПОСРЕДСТВОМ И С ПОМОЩЬЮ СЕРВИСА KWORK.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					TO THE FULLEST EXTENT PERMITTED UNDER ANY AND ALL APPLICABLE LAWS, IN NO EVENT WILL THE OPERATOR,
					ITS SHAREHOLDERS, SUBSIDIARIES, AFFILIATES, LICENSORS, WEBSITE PROVIDERS, CONTENT PROVIDERS,
					EMPLOYEES, OFFICERS, DIRECTORS, AND AGENTS BE LIABLE TO YOU OR ANYONE ELSE UNDER ANY THEORY OF
					LIABILITY (WHETHER IN CONTRACT, TORT, STATUTORY, OR OTHERWISE) FOR ANY INCIDENTAL, DIRECT, INDIRECT,
					PUNITIVE, ACTUAL, CONSEQUENTIAL, SPECIAL, EXEMPLARY, OR ANY OTHER DAMAGES, INCLUDING LOSS OF REVENUE
					OR INCOME, PAIN AND SUFFERING, EMOTIONAL DISTRESS, DAMAGES FOR LOSS OF GOODWILL, DATA OR ANY OTHER
					INTANGIBLE LOSSES, RESULTING FROM YOUR ACCESS AND/OR USE OF, OR INABILITY TO ACCESS AND/OR USE THE
					KWORK SERVICE OR ANY PART OR PORTION THEREOF, EVEN IF THE OPERATOR HAS BEEN ADVISED OF THE
					POSSIBILITY OF SUCH DAMAGES. YOU ASSUME FULL RESPONSIBILITY FOR ANY DAMAGES, LOSSES, COSTS, OR HARM
					ARISING FROM YOUR ACCESS AND/OR USE OF, OR INABILITY TO ACCESS AND/OR USE, THE KWORK SERVICE. THIS
					CONDITION ALSO APPLIES TO THE SERVICES PROVIDED TO YOU BY THE OPERATOR BY MEANS AND WITH HELP OF THE
					KWORK SERVICE.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p class="centered">
					<strong>{'ВОЗМЕЩЕНИЕ УБЫТКОВ'|t}</strong>
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p class="centered">
					<strong>INDEMNITY</strong>
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Вы соглашаетесь защищать, гарантировать возмещение ущерба и освободить Оператора, его дочерние компании, аффилированные лица, лицензиаров, поставщиков информационного наполнения (контента), поставщиков услуг, работников, должностных лиц, руководителей, агентов, представителей, лицензиатов, уполномоченных лиц, правопреемников, цессионариев и контрагентов от, а также полностью возместить Оператору или любому такому лицу, любые расходы или издержки, возникающие в результате любых претензий любых третьих лиц, а также любой ответственности, платёжных требований, исковых требований, оснований для исковых требований (независимо от их формы), ущерба, убытков, судебных решений, судебных постановлений, штрафов, издержек, затрат и расходов на услуги юридических представителей, связанных с или возникающих из:'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					You agree to defend, indemnify and hold the Operator, its subsidiaries, affiliates, licensors,
					content providers, service providers, employees, officers, directors, agents, representatives,
					licensees, authorized designees, successors, assigns and contractors harmless from and against, and
					reimburse to the Operator or any such party in full any costs or expenses arising or resulting from,
					any and all third party claims and all liabilities, assessments, actions, causes of action
					(regardless of the form), losses, damages, awards, judgments, fines, costs, expenses, and attorneys’
					fees resulting from or arising out of:
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'любого нарушения Вами любых положений настоящих Условий Предоставления Услуг, либо Договора-Оферты;'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						any breach by you of these Terms of Service or Offer Agreement;
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'несоблюдения или нарушения Вами любых прав любого третьего лица, в том числе, без ограничения, других Пользователей Сервиса KWORK; и'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						your infringement or violation of any intellectual property, other rights or privacy of a third
						party, including but not limited to other Users of the KWORK Service; and
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						{'несоблюдения или нарушения Вами любых прав любого третьего лица, в том числе, без ограничения, других Пользователей Сервиса KWORK; и'|t}
					</p>
				</div>
			</td>
		{/if}
		{if $isShowEn}
			<td>
                            <span>
                                <strong>&nbsp;•</strong>
                            </span>
				<div>
					<p class="offset_1">
						misuse of the KWORK Service by a third party where such misuse was made possible due to your
						failure to take reasonable measures to protect your username and password against misuse.
					</p>
				</div>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Оператор оставляет за собой право принять на себя исключительную защиту и контроль в отношении любых дел, которые иначе бы подпадали под положения о возмещении Вами убытков Оператору, и в таком случае Вы обязаны сотрудничать с Оператором при использовании любых доступных средств юридической защиты. Данное положение сохраняет полную юридическую силу вне зависимости от прекращения Вами использования Сервиса KWORK по любой причине.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					The Operator reserves the right to assume the exclusive defense and control of any matter otherwise
					subject to indemnification by you, in which event you will cooperate with the Operator in asserting
					any available defenses. This provision shall remain in full force and effect notwithstanding any
					termination of your use of the KWORK Service.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p class="centered">
					<strong>{'ОТКАЗ ОТ ПРАВ'|t}</strong>
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p class="centered">
					<strong>WAIVER</strong>
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'ОСУЩЕСТВЛЯЯ ИСПОЛЬЗОВАНИЕ СЕРВИСА KWORK И ПРИНИМАЯ ПОЛОЖЕНИЯ И УСЛОВИЯ НАСТОЯЩИХ УСЛОВИЙ ПРЕДОСТАВЛЕНИЯ УСЛУГ, НАСТОЯЩИМ ВЫ, В МАКСИМАЛЬНО РАЗРЕШЁННОЙ ЛЮБЫМ ПРИМЕНИМЫМ ПРАВОМ СТЕПЕНИ, ОТКАЗЫВАЕТЕСЬ ОТ ОТВЕТСТВЕННОСТИ И СОГЛАШАЕТЕСЬ ОСВОБОДИТЬ И ОГРАДИТЬ ОТ ОТВЕТСТВЕННОСТИ ОПЕРАТОРА, ЕГО ДОЧЕРНИЕ КОМПАНИИ, АФФИЛИРОВАННЫЕ ЛИЦА, ЛИЦЕНЗИАРОВ, ПОСТАВЩИКОВ ВЕБ-САЙТОВ, ПОСТАВЩИКОВ ИНФОРМАЦИОННОГО НАПОЛНЕНИЯ (КОНТЕНТА), РАБОТНИКОВ, ДОЛЖНОСТНЫХ ЛИЦ, РУКОВОДИТЕЛЕЙ И АГЕНТОВ, А ТАКЖЕ ЛЮБОЕ ИНОЕ СООТВЕТСТВУЮЩЕЕ ЛИЦО В ОТНОШЕНИИ ЛЮБЫХ И ВСЕХ ПРЕТЕНЗИЙ, ВОЗНИКАЮЩИХ ИЗ ЛЮБЫХ ДЕЙСТВИЙ, ПРЕДПРИНЯТЫХ ОПЕРАТОРОМ, ЕГО ДОЧЕРНИМИ КОМПАНИЯМИ, АФФИЛИРОВАННЫМИ ЛИЦАМИ, ЛИЦЕНЗИАРАМИ, ПОСТАВЩИКАМИ ВЕБСАЙТОВ, ПОСТАВЩИКАМИ ИНФОРМАЦИОННОГО НАПОЛНЕНИЯ (КОНТЕНТА), РАБОТНИКАМИ, ДОЛЖНОСТНЫМИ ЛИЦАМИ, РУКОВОДИТЕЛЯМИ И АГЕНТАМИ, А ТАКЖЕ ЛЮБЫМ ИНЫМ СООТВЕТСТВУЮЩИМ ЛИЦОМ ВО ВРЕМЯ ИЛИ В РЕЗУЛЬТАТЕ РАССЛЕДОВАНИЙ В ОТНОШЕНИИ ОСУЩЕСТВЛЕНИЯ ВАМИ ДОСТУПА К СЕРВИСУ KWORK И ИСПОЛЬЗОВАНИЯ СЕРВИСА KWORK ИЛИ ЛЮБОЙ ЕГО ЧАСТИ ИЛИ РАЗДЕЛА, А ТАКЖЕ В ОТНОШЕНИИ ЛЮБЫХ ДЕЙСТВИЙ, ПРЕДПРИНЯТЫХ В РЕЗУЛЬТАТЕ ТАКИХ РАССЛЕДОВАНИЙ, ПРОВОДИМЫХ ОПЕРАТОРОМ, ЕГО ДОЧЕРНИМИ КОМПАНИЯМИ, АФФИЛИРОВАННЫМИ ЛИЦАМИ, ЛИЦЕНЗИАРАМИ, ПОСТАВЩИКАМИ ВЕБСАЙТОВ, ПОСТАВЩИКАМИ ИНФОРМАЦИОННОГО НАПОЛНЕНИЯ (КОНТЕНТА), РАБОТНИКАМИ, ДОЛЖНОСТНЫМИ ЛИЦАМИ, РУКОВОДИТЕЛЯМИ И АГЕНТАМИ, А ТАКЖЕ ЛЮБЫМ ИНЫМ СООТВЕТСТВУЮЩИМ ЛИЦОМ, ВКЛЮЧАЯ, НО НЕ ОГРАНИЧИВАЯСЬ ЭТИМ, ЛЮБЫМИ ПРАВООХРАНИТЕЛЬНЫМИ ОРГАНАМИ.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					BY USING THE KWORK SERVICE AND ACCEPTING THE TERMS AND CONDITIONS OF THESE TERMS OF SERVICE, TO THE
					MAXIMUM EXTENT AVAILABLE UNDER ANY APPLICABLE LAW YOU HEREBY WAIVE AND AGREE TO RELEASE AND HOLD
					HARMLESS THE OPERATOR, ITS SUBSIDIARIES, AFFILIATES, LICENSORS, WEBSITE PROVIDERS, CONTENT
					PROVIDERS, EMPLOYEES, OFFICERS, DIRECTORS, AND AGENTS AND ANY OTHER APPROPRIATE PARTY FROM ANY AND
					ALL CLAIMS RESULTING FROM ANY ACTION TAKEN BY THE OPERATOR, ITS SUBSIDIARIES, AFFILIATES, LICENSORS,
					WEBSITE PROVIDERS, CONTENT PROVIDERS, EMPLOYEES, OFFICERS, DIRECTORS, OR AGENTS AND ANY OTHER
					APPROPRIATE PARTY DURING OR AS A RESULT OF INVESTIGATIONS WITH RESPECT TO YOUR ACCESS AND USE OF THE
					KWORK SERVICE OR ANY PART OR PORTION THEREOF, AND FROM ANY AND ALL ACTIONS TAKEN AS A RESULT OF SUCH
					INVESTIGATIONS BY THE OPERATOR, ITS SUBSIDIARIES, AFFILIATES, LICENSORS, WEBSITE PROVIDERS, CONTENT
					PROVIDERS, EMPLOYEES, OFFICERS, DIRECTORS, OR AGENTS, AND ANY OTHER APPROPRIATE PARTY, INCLUDING BUT
					NOT LIMITED TO ANY LAW ENFORCEMENT AUTHORITIES.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p class="centered">
					<strong>{'ЮРИСДИКЦИИ'|t}</strong>
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p class="centered">
					<strong>JURISDICTIONS</strong>
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Сервис KWORK, функциональность Сервиса KWORK и его информационное наполнение (контент) по намерению соблюдают требования применимых законов и нормативных актов Гонконга. Другие страны могут иметь законы, нормативные требования и деловые практики, которые отличаются от существующих в Гонконге. Сервис KWORK может быть связан посредством ссылок с другими вебсайтами, созданными и/или управляемыми операционными подразделениями и дочерними компаниями Оператора, некоторые из которых расположены, размещены посредством хостинга, или предназначены для осуществления доступа к и использования за пределами Гонконга. Такие вебсайты могут содержать информацию, которая предназначена только для такой конкретной страны происхождения. Оператор сохраняет за собой право ограничить доступ к Сервису KWORK и возможность использования Сервиса KWORK для любого лица, географического региона или юрисдикции. Любое предложение любого товара или услуги, сделанное посредством Сервиса KWORK, является недействительным там, где оно запрещено.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					The KWORK Service, its functionality and its contents are intended to comply with applicable laws
					and regulations of Hong Kong. Other countries may have laws, regulatory requirements and business
					practices that differ from those established in Hong Kong. The KWORK Service may link to other
					websites produced and/or operated by the Operator’s operating divisions and subsidiaries, some of
					which are located or hosted or intended to be accessed and used outside of Hong Kong. Such websites
					may have information that is appropriate only to that particular originating country. The Operator
					reserves the right to limit access to and the ability to use the KWORK Service to any person,
					geographic region or jurisdiction. Any offer for any product or service made via the KWORK Service
					is void where prohibited.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Не взирая на любые иные положения настоящих Условий Предоставления Услуг, и не взирая на тот факт, что Оператор является компанией, зарегистрированной и осуществляющей деятельность по законодательству и на территории Гонконга, Сервис KWORK доступен Пользователям по всему миру за прямым исключением территории Гонконга.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					Notwithstanding any other provisions of these Terms of Service, and notwithstanding the fact that
					the Operator is a company registered and operating under the laws of Hong Kong, the KWORK Service is
					available to the Users worldwide with the express exception of the territory of Hong Kong.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p class="centered">
					<strong>{'ПРИМЕНИМОЕ ПРАВО'|t}</strong>
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p class="centered">
					<strong>APPLICABLE LAW</strong>
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Настоящие Условия Предоставления Услуг регулируются и подлежат толкованию в соответствии с законодательством Гонконга без учёта его норм коллизионного права.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					These Terms of Service are governed by and shall be construed in accordance with the laws of Hong
					Kong without regard to its conflict of law provisions.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p class="centered">
					<strong>{'ОБЯЗЫВАЮЩИЙ АРБИТРАЖ. РАЗРЕШЕНИЕ СПОРОВ'|t}</strong>
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p class="centered">
					<strong>BINDING ARBITRATION. DISPUTE RESOLUTION</strong>
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Вы и Оператор обязуетесь принимать разумные меры к тому, чтобы любые спорные вопросы, разногласия, либо претензии, возникающие в процессе исполнения Ваших обязательств и обязательств Оператора по настоящим Условиям Предоставления Услуг, были урегулированы путём переговоров.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					You and the Operator shall undertake reasonable measures to rectify any controversial issues,
					disputes or claims arising in the process of performance of yours and Operator’s obligations under
					these Terms of Service by negotiations.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Если Вам и Оператору не удастся прийти к согласию в ходе переговоров, все неразрешённые споры, разногласия или претензии, возникающие из или в связи с настоящими Условиями Предоставления Услуг либо их нарушением, расторжением или недействительностью, подлежат окончательному разрешению путём арбитражного разбирательства в соответствии с Правилами Ускоренной Арбитражной Процедуры Арбитражного Института Торговой Палаты г. Стокгольм. Местом проведения арбитражного разбирательства будет являться г. Стокгольм, Швеция. Языком арбитражного разбирательства будет английский язык.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					If you and the Operator fail to reach an agreement in the course of negotiations, all unsettled
					disputes, controversies or claims arising out of or in connection with these Terms of Service, or
					the breach, termination or invalidity thereof, shall be finally settled by arbitration in accordance
					with the Rules for Expedited Arbitration of the Arbitration Institute of the Stockholm Chamber of
					Commerce. The seat of arbitration shall be Stockholm, Sweden. The language to be used in the
					arbitral proceedings shall be English.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Неспособность Оператора осуществить или обеспечить принудительное исполнение любого права или положения настоящих Условий Предоставления Услуг не будет являться отказом от такого права или положения. В максимальной разрешённой любым применимым правом степени Вы соглашаетесь с тем что, независимо от существования любого нормативно-правового акта или закона об обратном, любое исковое заявление или основание для иска, возникающее из или в связи с использованием Вами Сервиса KWORK или настоящими Условиями Предоставления Услуг, должны быть поданы в течение 1 (Одного) года после того, как любое такое исковое заявление или основание для иска возникли, либо срок их исковой давности истечёт навсегда. Настоящее положение сохраняет свою полную юридическую силу вне зависимости от прекращения использования Вами Сервиса KWORK по любой причине.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					The failure of the Operator to exercise or enforce any right or provision of these Terms of Service
					shall not constitute a waiver of such right or provision. To the maximum extent allowed under any
					applicable laws you agree that regardless of any statute or law to the contrary, any claim or cause
					of action arising out of or related to your use of the KWORK Service or these Terms of Service must
					be filed within 1 (One) year after any such claim or cause of action arose or be forever barred.
					This provision shall remain in full force and effect notwithstanding any termination of your use of
					the KWORK Service.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Никакой отказ от разбирательства в отношении любого неисполнения, условия или нарушения настоящих Условий Предоставления Услуг не будет представлять собой отказ от разбирательства в отношении любого другого неисполнения, условия или нарушения настоящих Условий Предоставления Услуг, как сходного, так и иного по своей природе.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					No waiver of any default, condition or breach of these Terms of Service shall constitute a waiver of
					any other default, condition or breach of these Terms of Service, whether of a similar nature or
					otherwise.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p class="centered">
					<strong>{'ОБЕСПЕЧИТЕЛЬНЫЕ МЕРЫ'|t}</strong>
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p class="centered">
					<strong>INJUNCTIVE RELIEF</strong>
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Вы явным образом признаёте и соглашаетесь с тем, что Оператор может понести непоправимый ущерб, если Вы нарушите любое из положений настоящих Условий Предоставления Услуг. По этой причине, несмотря на положения Раздела "Обязывающий Арбитраж. Разрешение Споров" настоящих Условий Предоставления Услуг, в любом случае нарушения Вами условий настоящих Условий Предоставления Услуг Оператор будет вправе истребовать обеспечительные меры и/или судебное решение об исполнении договорных обязательств, а также аналогичные и дополнительные средства судебной защиты, которые могут быть доступны в любой юрисдикции.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					You expressly acknowledge and agree that the Operator may suffer irreparable damage if you breach
					any of the provisions of these Terms of Service. Therefore, notwithstanding provisions of the
					"Binding Arbitration. Dispute Resolution" section of these Terms of Service, in any case of your
					violation of these Terms of Service the Operator shall be entitled to apply for injunctive relief
					and/or a decree for specific performance and such other and further relief as may be appropriate in
					any jurisdiction
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p class="centered">
					<strong>{'АВТОНОМНОСТЬ ПОЛОЖЕНИЙ'|t}</strong>
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p class="centered">
					<strong>SEVERABILITY</strong>
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Если суд компетентной юрисдикции сочтет какую-либо часть данных Условий Предоставления Услуг противоречащей закону, недействительной или по какой-либо причине не могущей быть приведённой в исполнение в принудительном порядке, такое положение будет считаться отделимым от настоящих Условий Предоставления Услуг и не повлияет на действительность и возможность приведения в исполнение в принудительном порядке любых остальных положений настоящих Условий Предоставления Услуг.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					If any part of these Terms of Service is determined by a court of competent jurisdiction to be
					unlawful, void, or for any reason unenforceable, then that provision shall be deemed severable from
					these Terms of Service and shall not affect the validity and enforceability of any remaining
					provisions of these Terms of Service.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p class="centered">
					<strong>{'ИЗМЕНЕНИЯ В УСЛОВИЯХ ПРЕДОСТАВЛЕНИЯ УСЛУГ'|t}</strong>
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p class="centered">
					<strong>CHANGES TO TERMS OF SERVICE</strong>
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'В максимально разрешённой применимым правом степени Оператор оставляет за собой право модифицировать, обновлять, дополнять, пересматривать или иным образом изменять настоящие Условия Предоставления Услуг для того, чтобы они соответствовали новому применимому законодательству и/или нормативно-правовым актам, и/или улучшать Сервис KWORK, а также время от времени устанавливать новые или дополнительные правила, политики или условия в отношении Сервиса KWORK, уведомляя или не уведомляя Вас об этом. Оператор может уведомить Вас о поправках, внесенных в настоящие Условия Предоставления Услуг, выслав электронное письмо на адрес электронной почты, указанный в Вашем Личном Кабинете, или разместив сообщение с таким уведомлением в Сервисе KWORK, или разместив сообщение с таким уведомлением в Вашем Личном Кабинете. Все поправки к настоящим Условиям Предоставления Услуг будут незамедлительно вступать в силу и включаться в Условия Предоставления Услуг по факту направления или размещения такого уведомления. Вы несёте единоличную ответственность за регулярное ознакомление с настоящими Условиями Предоставления Услуг. Использование Вами Сервиса KWORK или любой его части или функциональности, а также заказ и получение Услуг после того, как любые изменения в настоящих Условиях Предоставления Услуг были размещены в сообщении в Сервисе KWORK или иным образом стали доступными для ознакомления, будет считаться согласием с такими изменениями, и будет отражать Вашу готовность принять на себя обязательства по ним. Если Вы не согласны с какими-либо из таких изменений, единственной доступной формой отказа для Вас является отказ от получения Услуг и прекращение использования Сервиса KWORK.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					To the fullest extent permitted under applicable law, the Operator reserves the right to modify,
					update, supplement, revise or otherwise change these Terms of Service in order to comply with
					applicable new legislation and/or regulations and/or to improve the KWORK Service, and to impose new
					or additional rules, policies, terms or conditions in relation to the KWORK Service, from time to
					time with or without notice to you. The Operator may notify you of the amendments to these Terms of
					Service by sending an email message to the email address listed in your Personal Account or by
					posting the notice on the KWORK Service or posting the notice in your Personal Account with the
					KWORK Service. All amendments to these Terms of Service will be effective immediately and
					incorporated into the Terms of Service upon sending or posting of such notice. You are solely
					responsible for regularly reviewing these Terms of Service. Your use of the KWORK Service or any
					part or functionality thereof, as well as ordering and receiving Services, after any changes to
					these Terms of Service are posted on the KWORK Service or otherwise made available for review will
					be considered acceptance of those changes and will constitute your agreement to be bound thereby. If
					you object to any such changes, your sole recourse will be to cancel the Services and stop using the
					KWORK Service.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p class="centered">
					<strong>{'ОТСУТСТВИЕ ПРАВА НА ДОСТУП В БУДУЩЕМ'|t}</strong>
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p class="centered">
					<strong>NO RIGHT OF FUTURE ACCESS</strong>
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'НАСТОЯЩИЕ УСЛОВИЯ ПРЕДОСТАВЛЕНИЯ УСЛУГ НЕ НАДЕЛЯЮТ ВАС КАКИМИ-ЛИБО ПРАВАМИ В ОТНОШЕНИИ ОСУЩЕСТВЛЕНИЯ ВАМИ ДОСТУПА К СЕРВИСУ KWORK В БУДУЩЕМ. ПО ЭТОЙ ПРИЧИНЕ, ОПЕРАТОР ПО СВОЕМУ УСМОТРЕНИЮ И В ЛЮБОЕ ВРЕМЯ МОЖЕТ ПРЕКРАТИТЬ РАБОТУ СЕРВИСА ИЛИ ЛЮБЫХ ЕГО ЧАСТЕЙ, ЧАСТИЧНО ИЛИ ПОЛНОСТЬЮ ПРЕКРАТИТЬ ПРЕДОСТАВЛЕНИЕ УСЛУГ, ЛИБО ОГРАНИЧИТЬ ИЛИ ЗАБЛОКИРОВАТЬ ДОСТУП ЛЮБОГО ПОЛЬЗОВАТЕЛЯ, В ТОМ ЧИСЛЕ, ВАС, К СЕРВИСУ ПО ЛЮБОЙ ПРИЧИНЕ, УВЕДОМЛЯЯ ИЛИ НЕ УВЕДОМЛЯЯ ОБ ЭТОМ. ВЫ ПОНИМАЕТЕ И СОГЛАШАЕТЕСЬ, ЧТО ОПЕРАТОР МОЖЕТ СОВЕРШИТЬ ОДНО ИЛИ НЕСКОЛЬКО ИЗ ЭТИХ ДЕЙСТВИЙ, НЕ УВЕДОМЛЯЯ ВАС ОБ ЭТОМ ЗАРАНЕЕ ИЛИ ИНЫМ ОБРАЗОМ, И ВЫ ПОНИМАЕТЕ И СОГЛАШАЕТЕСЬ, ЧТО НИ ОПЕРАТОР, НИ КАКИЕ-ЛИБО ИЗ АФФИЛИРОВАННЫХ С НИМ ЛИЦ НЕ БУДУТ НЕСТИ ОТВЕТСТВЕННОСТЬ ПЕРЕД ВАМИ ИЛИ ЛЮБЫМ ДРУГИМ ЛИЦОМ ЗА ЛЮБОЕ ПРЕКРАЩЕНИЕ ВАШЕГО ДОСТУПА ИЛИ ДОСТУПА ЛЮБОГО ДРУГОГО ЛИЦА К СЕРВИСУ ИЛИ ЕГО ЧАСТЯМ, И/ИЛИ УДАЛЕНИЕ ВАШЕГО ЛИЧНОГО КАБИНЕТА, И/ИЛИ УДАЛЕНИЕ ИЛИ БЕЗВОЗВРАТНОЕ УДАЛЕНИЕ ИЛИ ДЕАКТИВАЦИЮ ЛЮБОЙ ИНОЙ ИНФОРМАЦИИ ИЛИ ДАННЫХ, КОТОРЫЕ ВЫ ИЛИ ЛЮБОЕ ДРУГОЕ ЛИЦО МОГЛИ ПРЕДОСТАВИТЬ В СЕРВИСЕ ЛИБО ПОСРЕДСТВОМ СЕРВИСА KWORK.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					THESE TERMS OF SERVICE DO NOT GRANT YOU ANY RIGHTS WITH RESPECT TO YOUR FUTURE ACCESS TO THE KWORK
					SERVICE. THUS, THE OPERATOR MAY, IN ITS SOLE DISCRETION AND AT ANY TIME, DISCONTINUE THE KWORK
					SERVICE OR ANY PARTS THEREOF, PARTIALLY OR COMPLETELY TERMINATE PROVISION OF SERVICES, OR LIMIT OR
					RESTRICT ANY USER, INCLUDING YOU, ACCESS THERETO, FOR ANY REASON, WITH OR WITHOUT NOTICE. YOU
					UNDERSTAND AND AGREE THAT THE OPERATOR MAY TAKE ANY ONE OR MORE OF THESE ACTIONS WITHOUT ANY NOTICE
					TO YOU, PRIOR OR OTHERWISE, AND YOU UNDERSTAND AND AGREE THAT NEITHER THE OPERATOR NOR ANY OF ITS
					AFFILIATES SHALL HAVE ANY LIABILITY TO YOU OR TO ANY OTHER PERSON FOR ANY TERMINATION OF YOUR OR
					ANYONE ELSE'S ACCESS TO THE SERVICE OR PARTS THEREOF AND/OR TERMINATION OF YOUR PERSONAL ACCOUNT
					AND/OR REMOVAL OR PURGING OR DEACTIVATION OF ANY OTHER INFORMATION OR DATA THAT YOU OR ANYONE ELSE
					MAY HAVE PROVIDED ON OR BY MEANS OF THE KWORK SERVICE.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p class="centered">
					<strong>{'СМЕНА ВЛАДЕНИЯ'|t}</strong>
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p class="centered">
					<strong>CHANGE OF OWNERSHIP</strong>
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Будет считаться, что Вы предоставили своё согласие на раскрытие последующему владельцу или оператору Сервиса KWORK, а также использование таким лицом любой всей информации о Вас, в том числе ваших регистрационных данных, данных об использовании Вами Сервиса KWORK и персональных данных (если применимо), содержащихся в соответствующей базе данных, которая используется Сервисом KWORK, в таком объеме, в каком Оператор уступает свои права и обязанности в отношении такой информации в связи со слиянием, приобретением или продажей всех или некоторых активов Оператора, или в связи со слиянием, приобретением или продажей всех или некоторых активов, относящихся к Сервису KWORK, последующему владельцу или оператору. В случае такого слияния, приобретения или продажи факт продолжения использования Вами Сервиса KWORK свидетельствует о Вашем согласии с Условиями Предоставления Услуг и политики конфиденциальности последующего владельца или оператора Сервиса KWORK. Настоящее положение сохраняет свою полную юридическую силу вне зависимости от прекращения использования Вами Сервиса KWORK по любой причине.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					You will be deemed to have consented to the disclosure to, and use by, a subsequent owner or
					operator of the KWORK Service, of any and all information about you, including your registration
					details, history of your use of the KWORK Service and personal data (if applicable), contained in
					the applicable database used by the KWORK Service, to the extent the Operator assigns its rights and
					obligations regarding such information in connection with a merger, acquisition, or sale of all or
					some of the Operator's assets, or in connection with a merger, acquisition or sale of all or some
					assets related to the KWORK Service, to a subsequent owner or operator. In the event of such a
					merger, acquisition, or sale, your continued use of the KWORK Service signifies your agreement to be
					bound by the Terms of Service and privacy statement of the KWORK Service's subsequent owner or
					operator. This provision shall remain in full force and effect notwithstanding any termination of
					your use of the KWORK Service.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p class="centered">
					<strong>{'ПРОЧИЕ ПОЛОЖЕНИЯ'|t}</strong>
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p class="centered">
					<strong>MISCELLANEOUS</strong>
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Положения и условия, изложенные в настоящих Условиях Предоставления Услуг, являются основополагающими условиями договора между Оператором и Вами в отношении использования Вами Сервиса KWORK и Услуг, и Вы явным образом признаёте и соглашаетесь с тем, что Оператор не сможет предоставить Вам Сервис KWORK и его функциональность (в том числе Услуги), без ограничений и запретов, установленных настоящими Условиями Предоставления Услуг.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					The terms and conditions set forth in these Terms of Service are fundamental elements of the basis
					of the agreement between the Operator and you with respect to your use of the KWORK Service and the
					Services, and you expressly acknowledge and agree that the Operator would not be able to provide the
					KWORK Service and its functionality (including the Services) to you without the limitations and
					restrictions set forth herein.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Оператор не будет нести ответственность за любое невыполнение своих обязательств по настоящим Условиям Предоставления Услуг, если такое невыполнение произошло по причине или из-за обстоятельств, объективно не поддающихся контролю Оператора, которые могут включать в себя, но ни при каких обстоятельствах не ограничиваются, форс-мажорными обстоятельствами.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					The Operator will not be liable or responsible for any failure to fulfill any of its obligations
					under these Terms of Service which failure is due to any cause or condition beyond the reasonable
					control of the Operator, which cause or condition may include, but in no event shall be limited to,
					force majeure circumstances.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Положения настоящих Условий Предоставления Услуг, которые должны по своему назначению или по своей сути остаться в силе после прекращения использования Вами Сервиса KWORK, будут являться действительными и продолжать юридически действовать после такого прекращения по любой причине.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					The provisions of these Terms of Service that are intended to or by their nature should survive
					termination of your use of the KWORK Service shall remain valid and shall be legally effective after
					any such termination.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Вы явным образом соглашаетесь с тем, что для целей настоящих Условий Предоставления Услуг Вы не считаетесь агентом, работником или участником совместного предприятия Оператора, и что Вы не вправе представляться таковым, а также что использование Вами Сервиса KWORK не наделяет ни Оператора, ни Вас правами и обязанностями перечисленных лиц.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					You expressly agree that for the purposes of these Terms of Service you are not considered, and
					shall not represent yourself as, an agent, employee, joint venturer, or partner of the Operator, and
					that your use of the KWORK Service does not vest in the Operator or you the rights or obligations of
					the discussed parties.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Вы не вправе переуступать свои права и обязанности по настоящим Условиям Предоставления Услуг полностью или частично любому третьему лицу, и любая предпринятая попытка переуступки в нарушение данного положения будет считаться не имеющей юридической силы. Оператор вправе переуступить настоящие Условия Предоставления Услуг или любые свои права и обязанности по ним без Вашего согласия в любой момент времени.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					You may not assign these Terms of Service in whole or in part to any third party, and any attempted
					assignment in violation of this provision shall be null and void. The Operator may assign these
					Terms of Service or any of its rights and obligations under these Terms of Service without your
					consent at any time.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Настоящие Условия представляют собой полное соглашение между Вами и Оператором в отношении предмета настоящих Условий. Независимо от вышеизложенного, любые дополнительные условия, которые в явной форме содержатся в Сервисе KWORK или становятся доступными посредством Сервиса KWORK, в любой момент времени будут регулировать объекты, функции, службы, Услуги или Ваши взаимоотношения с Оператором, к которым они относятся.'|t}
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p>
					These Terms of Service constitute the entire agreement between you and the Operator relating to the
					subject matter hereof. Notwithstanding the foregoing, any additional terms and conditions expressly
					contained on or made available via the KWORK Service at any moment will govern the items,
					functionality, services, the Services or your relationship with the Operator to which they pertain.
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p class="centered">
					<strong>{'КОНТАКТЫ'|t}</strong>
				</p>
			</td>
		{/if}
		{if $isShowEn}
			<td>
				<p class="centered">
					<strong>CONTACTS</strong>
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					{'Если у Вас возникают какие-либо комментарии или вопросы в отношении осуществления Вами доступа к Сервису KWORK и/или использования Сервиса KWORK, Услуг либо в отношении настоящих Условий Предоставления Услуг, пожалуйста, свяжитесь с Оператором используя следующие контактные данные:'|t}
				</p>
			</td>
			{if !$isMobileApi}
				<td>
					<p>
						If you have any comments or questions concerning your access and/or use of the KWORK Service,
						the Services or in relation to these Terms of Service, please contact the Operator using the
						following contact details:
					</p>
				</td>
			{/if}
		{else}
			<td>
				<p>
					If you have any comments or questions concerning your access and/or use of the KWORK Service, the
					Services or in relation to these Terms of Service, please contact the Operator using the following
					contact details:
				</p>
			</td>
		{/if}
	</tr>
	<tr>
		{if $isShowRu}
			<td>
				<p>
					<strong>Адрес Оператора</strong>: Гонконг, Ван Чаи, 338 Дорога Хеннесси, башня СНТ, 11/Ф, Блок Ф
					<br>
					<strong>Для общих вопросов</strong>: <a href="mailto:info@kwork.ru"
															target="_blank">info@kwork.ru</a><br>
					<strong>Юридический отдел</strong>: <a href="mailto:info@kwork.ru" target="_blank">info@kwork.ru</a><br>
					<strong>Техническая Поддержка</strong>: <a href="mailto:support@kwork.ru" target="_blank">support@kwork.ru</a><br>
				</p>
			</td>
		{/if}

		{if $isShowRu}
			{if !$isMobileApi}
				<td>
					<p>
						<strong>Operator address</strong>: Unit F, 11/F, CNT Tower, 338 Hennessy Road, Wan Chai, Hong
						Kong<br>
						<strong>For general inquiries</strong>: <a href="mailto:info@kwork.ru" target="_blank">info@kwork.ru</a><br>
						<strong>Legal department</strong>: <a href="mailto:info@kwork.ru"
															  target="_blank">info@kwork.ru</a><br>
						<strong>Tech support</strong>: <a href="mailto:support@kwork.ru" target="_blank">support@kwork.ru</a><br>
					</p>
				</td>
			{/if}
		{else}
			<td>
				<p>
					<strong>Operator address</strong>: Unit F, 11/F, CNT Tower, 338 Hennessy Road, Wan Chai, Hong
					Kong<br>
					<strong>For general inquiries</strong>: <a href="mailto:info@kwork.com" target="_blank">info@kwork.com</a><br>
					<strong>Legal department</strong>: <a href="mailto:info@kwork.com"
														  target="_blank">info@kwork.com</a><br>
					<strong>Tech support</strong>: <a href="mailto:info@kwork.com"
													  target="_blank">info@kwork.com</a><br>
				</p>
			</td>
		{/if}
	</tr>
	{if !Translations::isDefaultLang()}
		<tr>
			<td>
				KWORK TECHNOLOGIES OÜ<br>
				Registry code: 14531126<br>
				IBAN: GB89 EPMT 0099 7226 8060 77<br>
				BIC (SWIFT): EPMTGB2L<br>
				+79275595558
			</td>
		</tr>
	{/if}
	</tbody>
</table>