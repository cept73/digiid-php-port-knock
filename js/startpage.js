(function ($) {
	"use strict";

	// QR not scanned yet

	// Poll server, is user scan QR throw his application?
	var timerId = setInterval(function() {
		// Detect URL base dir
    		var path = window.location.href; path = path.substring (0, path.lastIndexOf('/'));

		var r = new XMLHttpRequest();
		r.open("POST", path + "/ajax.php", true);
		r.onreadystatechange = function () {
			if (r.readyState != 4 || r.status != 200) return;
			if (r.responseText !='') {
				var result = JSON.parse(r.responseText);

				// Login failed?	
				if (result.address == false) return;

				// If already registered, show dashboard
				if (result.info.fio) document.location = path;
				// If not registered yet, need to fill the form
				else {
					// Stop polling
					if (timerId) clearInterval (timerId);

					// and show form
					/*$("#step1").addClass('hidden');
					$("#step2").removeClass('hidden');*/
					// Refresh for next step
					document.location = document.location
				}
			}
		};
		r.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		r.send("nonce="+nonce);
	}, 5* 1000); // 5 sec

	// If user is not active..
	var timerTimeout = setTimeout(function() {
		// Change a view
		$("#qr").after('<div class="hint">' + PRESS_TO_UPDATE + '</div>')
			.parent()./* div-> */addClass('timeout')
			.parent()./* a-> */attr({'href': document.location, 'title': 'Обновить'});

		// Stop polling
		clearInterval (timerId);
	}, 1* 60* 1000); // 1 min

})(jQuery);