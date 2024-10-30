(function () {
    var Components = {};

    Components.ratesTable = {
        trigger: '#laposteproexp-rates-table',

        init: function() {
            const triggerEl = document.querySelector(this.trigger);
            const self = this;

            if (null !== triggerEl) {
				self.on("body", "click", ".laposteproexp-add-rate-line", function(e) {
                	e.preventDefault();
					const httpRequest = new XMLHttpRequest();
					httpRequest.onreadystatechange = function(data) {
						if (httpRequest.readyState === 4) {
							if (httpRequest.status === 200) {
								const data = typeof httpRequest.response === 'object' && httpRequest.response !== null ? httpRequest.response.data : JSON.parse(httpRequest.response).data;
								const tableBody = self.getTableBody();
								tableBody.insertAdjacentHTML('beforeend', data);
								self.initSelect('#laposteproexp-rates-table tbody tr:last-child select.laposteproexp-tom-select');
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
					const action = e.target.getAttribute('data-action');
					let params = "action=" + action + "&security=" + encodeURIComponent(laposteproexpData.shippingMethodAjaxNonce);
					const tableRows = self.getTableRows();
					if (null !== tableRows) {
						const lastLine = tableRows[tableRows.length - 1];
						const values = [];
						self.parseNodeChildrenSpecificTypeAction(lastLine, ['SELECT', 'INPUT'], function(item) {
							if ('SELECT' === item.tagName) {
								const name = item.getAttribute('name');
								const options = self.getSelectedOptions(item);
								if (options.length > 0) {
									options.map(option => {
										values.push(name + "=" + encodeURIComponent(option.value));
									});
								} else {
									values.push(name + "=");
								}
							} else {
								values.push(item.getAttribute('name') + "=" + encodeURIComponent(item.value));
							}
						});
						params += '&' + values.join('&');
					}
					httpRequest.send(params);
                });

				self.on("body", "click", ".laposteproexp-remove-rate-line", function(e) {
					e.preventDefault();
					const selectedLine = document.querySelector('.pricing-item.last_selected');
					if (null !== selectedLine) {
						selectedLine.remove();
					}
				});

				self.on("body", "click", ".laposteproexp-remove-line", function(e) {
					e.preventDefault();
					const selectedLine = self.getParentRow(e.target);
					if (null !== selectedLine) {
						selectedLine.remove();
					}
				});

				self.on("body", "change", ".laposteproexp-change-state", function(e, v) {
					const disabled = e.target !== null && e.target.checked === false;
					const selectedLine = self.getParentRow(e.target);
					
					if (selectedLine !== null) {
						self.setRowState(selectedLine, disabled);
					}
				});

				self.initSelect("select.laposteproexp-tom-select");

				self.submitForm = self.submitForm.bind(self);

				document.getElementById("mainform").addEventListener("submit", self.submitForm);
            }
		},
		
		getParentRow: function (element) {
			while (element !== null && element !== undefined && element.tagName.toLowerCase() !== 'tr') {
				element = element.parentNode;
			}
			return element === undefined ? null : element;
		},

		setRowState: function(row, disabled) {
        	const self = this;
			const inputs = row.querySelectorAll('input[type="text"], input[type="number"], select');

			for (const input of inputs) {
				input.disabled = disabled;
			}

			self.initSelect('#laposteproexp-rates-table select.laposteproexp-tom-select');

			if (disabled) {
				row.classList.add('disabled');
			} else {
				row.classList.remove('disabled');
			}
		},

		getSelectedOptions: function(sel, fn) {
			var opts = [], opt;

			// loop through options in select list
			for (var i=0, len=sel.options.length; i<len; i++) {
				opt = sel.options[i];

				// check if selected
				if ( opt.selected ) {
					// add to array of option elements to return from this function
					opts.push(opt);

					// invoke optional callback function if provided
					if (fn) {
						fn(opt);
					}
				}
			}

			// return array containing references to selected option elements
			return opts;
		},

		submitForm: function(e) {
        	const self = this;
			e.preventDefault();
			const pricingItems = [];
			const pricingRows = document.querySelectorAll('.pricing-item');
			if (pricingRows.length > 0) {
				for (let i = 0; i < pricingRows.length; i++) {
					const element = pricingRows[i];

					const stateInput = element.querySelector("td .state");
					const pricing = stateInput.checked ? stateInput.getAttribute('data-checked') : stateInput.getAttribute('data-unchecked');
					const shippingClasses = element.querySelector("td .shipping-class");

					const pricingItem = {
						'price-from': element.querySelector("td .price-from").value,
						'price-to': element.querySelector("td .price-to").value,
						'weight-from': element.querySelector("td .weight-from").value,
						'weight-to': element.querySelector("td .weight-to").value,
						'shipping-class': shippingClasses === null ? [] : Array.apply(null, shippingClasses.options).filter(option => option.selected).map(option => option.value),
						'parcel-point-network': Array.apply(null, element.querySelector("td .parcel-point-network").options).filter(option => option.selected).map(option => option.value),
						'pricing': pricing,
						'flat-rate': element.querySelector("td .flat-rate").value,
						'state': element.querySelector("td .state").checked
					};
					pricingItems.push(pricingItem);
				}
			}

			const el = document.createElement("input");
			el.setAttribute("type", "hidden");
			el.setAttribute("name", "pricing-items");
			el.value = JSON.stringify(pricingItems);

			const saveInput = document.createElement("input");
			saveInput.setAttribute("type", "hidden");
			saveInput.setAttribute("name", "save");
			saveInput.value = 1;

			document.querySelector(self.trigger).appendChild(el);
			document.querySelector(self.trigger).appendChild(saveInput); // necessary for WC submission to work.

			document.getElementById("mainform").removeEventListener("submit", self.submitForm);
			document.getElementById("mainform").submit();

			return true;
		},

		initSelect: function(selector) {
			const selects = document.querySelectorAll(selector);

			for (let i = 0; i < selects.length; i++) {
				const select = selects[i];
				if (select.tomselect) {
					if (select.disabled) {
						select.tomselect.disable();
					} else {
						select.tomselect.enable();
					}
				} else {
					new TomSelect(selects[i], {
						plugins: [ 'remove_button' ],
					});
				}
			}
		},

		getTableBody: function() {
			const table = document.querySelector(this.trigger);
			const tableBody = table.querySelector('tbody');
			if (null === tableBody) {
				return null;
			}
			return tableBody;
		},

		getTableRows: function() {
			const tableBody = this.getTableBody();
			if (null === tableBody) {
				return null;
			}
			if (0 === tableBody.childElementCount) {
				return null;
			}
			return tableBody.children;
		},

		parseNodeChildrenSpecificTypeAction: function(element, types, action) {
			if (element.hasChildNodes()) {
				const children = element.childNodes;
				for (let i = 0; i < children.length; i++) {
					const child = children[i];
					if (-1 !== types.indexOf(child.tagName)) {
						action(child);
					}
					this.parseNodeChildrenSpecificTypeAction(child, types, action);
				}
			}
			return element;
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
            Components.ratesTable.init();
        }
    );

})();
