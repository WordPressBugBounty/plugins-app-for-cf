
/**
 * Create the CloudflareAppAdmin namespace
 * @package CloudflareApp
 */

let CloudflareAppAdmin = {};

{
	let __ = wp.i18n.__,
//		_x = wp.i18n._x,
//		_n = wp.i18n._n,
//		_nx = wp.i18n._nx,
		sprintf = wp.i18n.sprintf;

	CloudflareAppAdmin.Admin = function() { this.__construct(); };
	CloudflareAppAdmin.Admin.prototype =
		{
			charts: [],

			__construct: function()
			{
				document.addEventListener('DOMContentLoaded', function () {
					if (document.querySelector('#dp_tabs.nav-tab-wrapper')) {
						this.init_tabs();
					}

					document.querySelectorAll('[data-click="overlay"]').forEach(el => {
						el.addEventListener('click', this.overlay.bind(this));
					});

					document.addEventListener('click', this.closeOverlay);

					document.querySelectorAll('.column-primary .row-actions .delete a').forEach(el => {
						el.addEventListener('click', this.displayConfirm.bind(this));
					});

					document.querySelectorAll('[form="cfSettingsForm"]').forEach(el => {
						el.addEventListener('change', this.settings.bind(this));
					});

					this.__register();
				}.bind(this));
			},

			__register: function ()
			{
				document.querySelectorAll('[data-init="dependent"] .primary:not(.is-reg)').forEach(el => {
					el.addEventListener('change', this.dependent.bind(this));
					el.classList.add('is-reg');

					if (el.type === 'radio') {
						const event = new Event('change', { bubbles: true });
						el.dispatchEvent(event);
					}
				});

				document.querySelectorAll('.dp-ui-toggle[data-overlay-href]:not(.is-reg)').forEach(el => {
					el.addEventListener('click', this.overlay.bind(this));
					el.classList.add('is-reg');
				});
			},


			init_tabs: function() {

				let tab = window.location.hash.slice(5);

				if (!tab)
				{
					const firstTab = document.querySelector('.nav-tab');
					if (firstTab)
					{
						tab = firstTab.id.slice(0, -4);
					}
				}

				CloudflareAppAdmin._Admin.select_tab(tab);

				document.querySelectorAll('#dp_tabs a').forEach(anchor => {
					anchor.addEventListener('click', CloudflareAppAdmin._Admin.select_tab);
				});
			},

			select_tab: function(tab) {
				function fadeIn(element, duration = 400) {
					element.style.transition = 'none !important';
					element.style.opacity = 0;
					element.style.display = 'revert';
					element.style.transition = '';

					setTimeout(() => {
						element.style.opacity = 1
					}, 0);

					setTimeout(() => {
						element.style.opacity = '';
					}, duration);
				}

				function fadeOut(element, duration = 400) {
					element.style.transition = 'none !important';
					element.style.opacity = 1;
					element.style.transition = '';

					setTimeout(() => {
						element.style.opacity = 0
					}, 0);

					setTimeout(() => {
						element.style.display = 'none';
					}, duration);
				}

				let tabEl;

				if (typeof tab === "object") {
					const hash = tab.currentTarget.hash.slice(4) + '-tab';
					tabEl = document.querySelector(hash);
				} else {
					tabEl = document.getElementById(tab + '-tab');
				}

				try {
					const tabId = tabEl.id.slice(0, -4);
					document.getElementById('dp_current_tab').value = tabId;
				} catch (err) {
					document.getElementById('dp_current_tab').value = 'setup';
				}

				document.querySelectorAll('.nav-tab').forEach(el => {
					el.classList.remove('nav-tab-active');
				});

				tabEl.classList.add('nav-tab-active');

				document.querySelectorAll('.tab_content').forEach(el => {
					el.style.display = 'none';
				});

				const currentTab = document.getElementById('dp_current_tab').value;
				document.querySelectorAll(`.group_${currentTab}:not(.api_hideable)`).forEach(el => {
					fadeIn(el);
				});

				const sidebarPro = document.querySelector('#app-for-cf_sidebar .pro');
				const hasPro = document.querySelector(`.group_${currentTab} .pro`) !== null;

				if (sidebarPro) {
					if (hasPro) {
						fadeIn(sidebarPro);
					} else {
						fadeOut(sidebarPro);
					}
				}
			},


			overlay: function(e)
			{
				let url = e.currentTarget.getAttribute('href');
				if (!url) {
					url = e.currentTarget.dataset.overlayHref;
				}

				e.preventDefault();

				let href;
				try {
					href = new URL(url, window.location.origin);
				} catch (err) {
					this.displayError(__('Invalid URL.', 'app-for-cf'));
					return;
				}

				if (!href.searchParams.get('_wpnonce')) {
					this.displayError(__('Missing CSRF token.', 'app-for-cf'));
					return;
				}

				href.searchParams.set('ajax', '1');

				fetch(href.href, { method: 'GET' })
					.then(response => response.text())
					.then(responseText => {
						// Try parsing the response as HTML
						const parser = new DOMParser();
						const doc = parser.parseFromString(responseText, 'text/html');
						const wpbodyContent = doc.querySelector('#wpbody-content');

						if (!wpbodyContent) {
							this.displayError(__('Unexpected response structure.', 'app-for-cf'));
							return;
						}

						const screenMeta = wpbodyContent.querySelector('#screen-meta');
						if (screenMeta) screenMeta.remove();

						const submitSection = wpbodyContent.querySelector('.submit');
						if (submitSection) {
							const dismissBtn = document.createElement('button');
							dismissBtn.className = 'dismiss button';
							dismissBtn.textContent = __('Cancel', 'app-for-cf');
							submitSection.appendChild(dismissBtn);
						} else {
							const div = document.createElement('div');
							const dismissBtn = document.createElement('button');
							dismissBtn.className = 'dismiss button';
							dismissBtn.textContent = __('Close', 'app-for-cf');
							div.appendChild(dismissBtn);
							wpbodyContent.appendChild(div);
						}

						const overlay = document.createElement('div');
						overlay.className = 'dp_overlay';
						overlay.innerHTML = wpbodyContent.innerHTML;

						const dismiss = overlay.querySelector('.dismiss');
						if (dismiss) {
							dismiss.addEventListener('click', function (e) {
								e.preventDefault();
								overlay.remove();
								return false;
							});
						}

						document.body.appendChild(overlay);

						CloudflareAppAdmin._Admin.__register();
					})
					.catch(error => {
						this.displayError(error.message || __('Request failed', 'app-for-cf'));
					});

				return false;
			},

			closeOverlay: function(e)
			{
				if (!e.target.closest('.dp_overlay')) {
					document.querySelectorAll('.dp_overlay').forEach(el => el.remove());
				}
			},




			flashMessage: function(message, timeout, onClose)
			{
				if (typeof timeout === 'undefined') {
					timeout = 5000;
				}

				let wrapper = document.getElementById('flashWrapper');
				if (!wrapper) {
					wrapper = document.createElement('div');
					wrapper.id = 'flashWrapper';
					document.body.appendChild(wrapper);
				}

				const messageEl = document.createElement('div');
				messageEl.className = 'flashMessage';

				const timerEl = document.createElement('div');
				timerEl.className = 'flashMessage-timer';
				timerEl.style.transitionDuration = (Math.max(500, timeout) / 1000) + 's';

				const contentEl = document.createElement('div');
				contentEl.className = 'flashMessage-content';
				contentEl.innerHTML = message;

				messageEl.appendChild(timerEl);
				messageEl.appendChild(contentEl);

				wrapper.appendChild(messageEl);

				requestAnimationFrame(() => {
					messageEl.classList.add('is-active');
				});

				setTimeout(() => {
					messageEl.classList.remove('is-active');
				}, Math.max(500, timeout));

				setTimeout(() => {
					messageEl.remove();

					if (!wrapper.querySelector('.flashMessage')) {
						wrapper.remove();
					}

					if (typeof onClose === 'function') {
						onClose();
					}
				}, Math.max(500, timeout) + 500);
			},



			displayMessage: function(message, target = null, isError = false)
			{
				const currentTarget = target.currentTarget;

				const errorDiv = document.createElement('div');
				errorDiv.className = 'dp_error';

				errorDiv.innerHTML = message;

				const buttonContainer = document.createElement('div');

				if (isError) {
					const okButton = document.createElement('input');
					okButton.type = 'submit';
					okButton.className = 'dismiss button button-primary';
					okButton.value = __('Okay', 'app-for-cf');
					buttonContainer.appendChild(okButton);
				} else {
					const yesButton = document.createElement('input');
					yesButton.type = 'submit';
					yesButton.className = 'dismiss button button-primary';
					yesButton.setAttribute('data-true', '1');
					yesButton.value = __('Yes', 'app-for-cf');

					const noButton = document.createElement('input');
					noButton.type = 'submit';
					noButton.className = 'dismiss button';
					noButton.value = __('No', 'app-for-cf');

					buttonContainer.appendChild(yesButton);
					buttonContainer.appendChild(noButton);
				}

				errorDiv.appendChild(buttonContainer);

				errorDiv.querySelectorAll('.dismiss').forEach(btn => {
					btn.addEventListener('click', function (e) {
						if (this.getAttribute('data-true')) {
							window.location.replace(currentTarget.href);
						}

						const dpError = this.closest('.dp_error');
						if (dpError) {
							dpError.remove();
						}
					});
				});

				document.body.appendChild(errorDiv);
			},

			displayError: function(message)
			{
				this.displayMessage(message, null, true);
			},

			displayConfirm: function(e)
			{
				e.preventDefault();

				const rowTitle = e.target.closest('tr')?.querySelector('.row-title strong');
				const itemName = rowTitle ? rowTitle.textContent.trim() : '';

				this.displayMessage(sprintf(
					// translators: %s: Item name.
					__('Are you sure you want to delete %s?', 'app-for-cf'),
					itemName
				), e, false);

				return false;
			},


			dependent: function(e)
			{
				if (e.target.type === 'radio') {
					let parent = e.target.closest('form');

					// If the element is not inside a form, fallback to table
					if (!parent) {
						parent = e.target.closest('table');
					}

					if (parent) {
						const name = e.target.name;
						const radios = parent.querySelectorAll(`[name="${name}"]`);

						radios.forEach(radio => {
							const dependentWrapper = radio.closest('[data-init="dependent"]');
							if (dependentWrapper) {
								const inputs = dependentWrapper.querySelectorAll('.dependent input, .dependent select');
								const primary = dependentWrapper.querySelector('.primary');
								const isChecked = primary?.checked;

								inputs.forEach(input => {
									input.disabled = !isChecked;
								});
							}
						});
					}
				} else {
					const dependentWrapper = e.target.closest('[data-init="dependent"]');
					if (dependentWrapper) {
						const inputs = dependentWrapper.querySelectorAll('.dependent input, .dependent select');
						const primary = dependentWrapper.querySelector('.primary');
						const isChecked = primary?.checked;

						inputs.forEach(input => {
							input.disabled = !isChecked;
						});
					}
				}
			},

			settings: function(e)
			{
				e.preventDefault();

				let data = {
					action: 'app-for-cf_settings'
				};

				document.querySelectorAll('[form="cfSettingsForm"]').forEach(el => {
					if (el.type !== 'checkbox' && el.type !== 'radio' || el.checked) {
						data[el.name] = el.value;
					}
				});

				fetch(ajaxurl, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded'
					},
					body: new URLSearchParams(data)
				})
					.then(response => response.json())
					.then(ajaxData => {
						if (ajaxData.error !== undefined) {
							this.displayError(ajaxData.error);
						} else {
							this.flashMessage(ajaxData.message);
						}
					});

				return false;
			},
		};

	CloudflareAppAdmin._Admin = new CloudflareAppAdmin.Admin();
}