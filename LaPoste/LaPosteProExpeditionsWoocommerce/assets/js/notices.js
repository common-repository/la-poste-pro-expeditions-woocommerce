(function () {
    var Components = {};

    Components.notices = {
        trigger: '.laposteproexp-notice',

        init: function() {
            const triggers = document.querySelectorAll(this.trigger);
            const self = this;

            if (triggers.length) {
                self.on("body", "click", ".laposteproexp-hide-notice", function() {
                    const httpRequest = new XMLHttpRequest();
                    const notice = this;
                    httpRequest.onreadystatechange = function(data) {
                        if (httpRequest.readyState === 4) {
                            if (httpRequest.status === 200) {
                                notice.closest(".laposteproexp-notice").style.display = 'none';
                            } else {
                                console.log("Error: " + httpRequest.status);
                            }
                        }
                    };
                    httpRequest.open("POST", laposteproexpData.ajaxurl);
                    httpRequest.setRequestHeader(
                        "Content-Type",
                        "application/x-www-form-urlencoded"
                    );
                    httpRequest.responseType = "json";
                    const noticeId = notice.getAttribute("rel");
                    const action = notice.getAttribute("data-action");
                    httpRequest.send("action=" + action + "&notice_id=" + encodeURIComponent(noticeId) + "&security=" + encodeURIComponent(laposteproexpData.noticeAjaxNonce));
                });

                self.on("body", "click", ".laposteproexp-pairing-update-validate", function() {
                    const httpRequest = new XMLHttpRequest();
                    const notice = this;
                    httpRequest.onreadystatechange = function() {
                        if (httpRequest.readyState === 4) {
                            if (httpRequest.status === 200) {
                                notice.closest(".laposteproexp-notice").style.display = 'none';
                            } else {
                                console.log("Error: " + httpRequest.status);
                            }
                        }
                    };
                    httpRequest.open("POST", laposteproexpData.ajaxurl);
                    httpRequest.setRequestHeader(
                        "Content-Type",
                        "application/x-www-form-urlencoded"
                    );
                    httpRequest.responseType = "json";
                    const approve = notice.getAttribute("laposteproexp-pairing-update-validate");
                    httpRequest.send("action=laposteproexp_pairing_update_validate&approve=" + encodeURIComponent(approve) + "&security=" + encodeURIComponent(laposteproexpData.noticeAjaxNonce));
                });
            }
        },

        on: function(elSelector, eventName, selector, fn) {
            const element = document.querySelector(elSelector);

            element.addEventListener(eventName, function(event) {
                const possibleTargets = element.querySelectorAll(selector);
                const target = event.target;

                for (let i = 0, l = possibleTargets.length; i < l; i++) {
                    let el = target;
                    const p = possibleTargets[i];

                    while(el && el !== element) {
                        if (el === p) {
                            return fn.call(p, event);
                        }

                        el = el.parentNode;
                    }
                }
            });
        }
    };

    document.addEventListener(
        "DOMContentLoaded", function() {
            Components.notices.init();
        }
    );

})();
