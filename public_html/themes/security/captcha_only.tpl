<!DOCTYPE html>
<html style="height: 100%;">
	<head>
		<meta name="robots" content="noindex, follow"/>
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<script src="https://www.google.com/recaptcha/api.js?hl={Translations::getLang()}"></script>
		<script>
			function redirectOnRecaptchaSuccess(response) {
				window.location.hash = '#response=' + response;
			}
		</script>
	</head>
	<body style="height: 100%;">
		<div style="display: flex; justify-content: center; align-items: center; height: 100%">
			<div class="g-recaptcha" data-sitekey="{reCAPTCHA::getPublicKey()}" data-callback="redirectOnRecaptchaSuccess" data-fixed="1"></div>
		</div>
	</body>
</html>