import Fingerprint2 from 'fingerprintjs2';

function saveFingerprint() {
	Fingerprint2.get(function (components) {
		var hash = Fingerprint2.x64hash128(components.map(function (pair) { return pair.value }).join(), 31);
		axios.post("/user_fingerprint", {hash: hash});
	});
}

if (window.requestIdleCallback) {
	requestIdleCallback(function () {
		saveFingerprint();
	})
} else {
	setTimeout(function () {
		saveFingerprint();
	}, 500)
}