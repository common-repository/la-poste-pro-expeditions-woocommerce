(function () {
	var Components = {};

	Components.modals = {
		trigger: '.laposteproexp-modal-trigger',

		init: function () {
			var triggers = document.querySelectorAll(this.trigger);
			if (triggers.length) {

				for (var i = 0, len = triggers.length; i < len; i++) {
					var targetSelector = triggers[i].getAttribute("laposteproexp-modal-target");
					var target = document.querySelector("#" + targetSelector);
					document.body.appendChild(target);

					triggers[i].addEventListener(
						"click", function() {
							var targetContent = document.querySelector("#" + targetSelector + " ." + "laposteproexp-modal-content");
							targetContent.classList.add("laposteproexp-modal-show");
							document.getElementById("laposteproexp-modal-backdrop").classList.add("laposteproexp-modal-show");
						}
					);
				}

				// add backdrop
				var backdrop = document.createElement("div");
				backdrop.setAttribute("id", "laposteproexp-modal-backdrop");

				backdrop.addEventListener(
					"click", function() {
						var targets = document.querySelectorAll("." + "laposteproexp-modal-content");
						for (var j = 0, len2 = targets.length; j < len2; j++) {
							targets[j].classList.remove("laposteproexp-modal-show");
						}
						this.classList.remove("laposteproexp-modal-show");
					}
				);
				document.body.appendChild(backdrop);
			}
		}
	};

	document.addEventListener(
		"DOMContentLoaded", function() {
			Components.modals.init();
		}
	);

})();
