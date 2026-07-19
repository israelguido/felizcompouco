(function () {
	var toastTimer = null;

	function ready(fn) {
		if (document.readyState !== 'loading') fn();
		else document.addEventListener('DOMContentLoaded', fn);
	}

	function ensureHost() {
		var host = document.getElementById('fcp-nl-toast-host');
		if (host) return host;

		host = document.createElement('div');
		host.id = 'fcp-nl-toast-host';
		host.className = 'fcp-nl-toast-host';
		host.setAttribute('aria-live', 'polite');
		host.setAttribute('aria-relevant', 'additions');
		document.body.appendChild(host);
		return host;
	}

	function showToast(text, isErr) {
		var host = ensureHost();
		// Um toast por vez — evita pilha de erros
		host.innerHTML = '';

		var toast = document.createElement('div');
		toast.className = 'fcp-nl-toast' + (isErr ? ' is-err' : ' is-ok');
		toast.setAttribute('role', 'status');

		var label = document.createElement('span');
		label.className = 'fcp-nl-toast__text';
		label.textContent = text;

		var close = document.createElement('button');
		close.type = 'button';
		close.className = 'fcp-nl-toast__close';
		close.setAttribute('aria-label', 'Fechar');
		close.innerHTML = '&times;';
		close.addEventListener('click', function () {
			dismiss(toast);
		});

		toast.appendChild(label);
		toast.appendChild(close);
		host.appendChild(toast);

		void toast.offsetWidth;
		toast.classList.add('is-in');

		if (toastTimer) clearTimeout(toastTimer);
		toastTimer = setTimeout(function () {
			dismiss(toast);
		}, isErr ? 6000 : 4500);
	}

	function dismiss(toast) {
		if (!toast || !toast.parentNode) return;
		toast.classList.remove('is-in');
		toast.classList.add('is-out');
		setTimeout(function () {
			if (toast.parentNode) toast.parentNode.removeChild(toast);
		}, 280);
	}

	ready(function () {
		document.querySelectorAll('[data-fcp-newsletter]').forEach(function (wrap) {
			var form = wrap.querySelector('form');
			var btn = wrap.querySelector('button[type="submit"]');
			var url = wrap.getAttribute('data-ajax-url');

			if (!form || !url) return;

			form.addEventListener('submit', function (e) {
				e.preventDefault();

				var email = form.querySelector('input[name="email"]');
				if (!email || !email.value.trim()) {
					showToast('Informe um e-mail válido.', true);
					return;
				}

				if (btn) btn.disabled = true;

				var body = new FormData(form);
				var widget = form.querySelector('altcha-widget');
				if (widget) {
					var state = widget.value || widget.getAttribute('value') || '';
					if (!state && widget._state && widget._state.payload) {
						state = widget._state.payload;
					}
					if (state && !body.get('captcha')) {
						body.set('captcha', state);
					}
				}

				fetch(url, {
					method: 'POST',
					body: body,
					credentials: 'same-origin',
					headers: { 'X-Requested-With': 'XMLHttpRequest' }
				})
					.then(function (r) { return r.json(); })
					.then(function (data) {
						var err = !(data && data.success);
						var text = '';
						if (data && data.message) {
							text = data.message;
						} else if (data && typeof data.data === 'string') {
							text = data.data;
						} else {
							text = err ? 'Não foi possível assinar.' : 'Inscrição realizada!';
						}
						showToast(text, err);
						if (!err) {
							form.reset();
							if (widget && typeof widget.reset === 'function') {
								widget.reset();
							}
						}
					})
					.catch(function () {
						showToast('Erro de conexão. Tente novamente.', true);
					})
					.finally(function () {
						if (btn) btn.disabled = false;
					});
			});
		});
	});
})();
