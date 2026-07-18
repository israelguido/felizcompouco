/**
 * Pin It (Pinterest) — botão no canto superior esquerdo das imagens.
 * Abre o formulário oficial de salvar pin.
 */
(function () {
	'use strict';

	var MIN_SIZE = 80;
	var ROOT_SELECTORS = [
		'#yt_component',
		'#content_main',
		'#content #yt_component',
		'.item-page',
		'.blog',
		'.fcp-ig',
		'#position3',
		'#position4',
		'#yt_spotlight6'
	].join(',');

	var SKIP_CLOSEST = [
		'.fcp-pin-btn',
		'#yt_logo',
		'.logo',
		'.social-top',
		'.social-footer',
		'#yt_menu',
		'.yt-menu',
		'.bannergroup',
		'[data-pin-nopin]',
		'.pagination',
		'.pagination-warpper'
	].join(',');

	function absUrl(src) {
		if (!src) {
			return '';
		}
		try {
			return new URL(src, window.location.href).href;
		} catch (e) {
			return src;
		}
	}

	function isTooSmall(img) {
		var w = img.naturalWidth || img.width || parseInt(img.getAttribute('width'), 10) || 0;
		var h = img.naturalHeight || img.height || parseInt(img.getAttribute('height'), 10) || 0;
		return (w > 0 && w < MIN_SIZE) || (h > 0 && h < MIN_SIZE);
	}

	function isSkippable(img) {
		if (!img || img.nodeName !== 'IMG') {
			return true;
		}
		if (img.closest('.fcp-pin-wrap')) {
			return true;
		}
		if (img.closest(SKIP_CLOSEST)) {
			return true;
		}
		if (img.hasAttribute('data-pin-nopin') || img.hasAttribute('data-pin-no-hover')) {
			return true;
		}
		var src = img.currentSrc || img.getAttribute('src') || '';
		if (!src || src.indexOf('data:') === 0) {
			return true;
		}
		if (/\b(1x1|pixel|spacer|blank)\b/i.test(src)) {
			return true;
		}
		if (isTooSmall(img)) {
			return true;
		}
		return false;
	}

	function buildPinUrl(img) {
		var media = absUrl(img.currentSrc || img.src);
		var page = window.location.href.split('#')[0];
		var desc = (img.getAttribute('alt') || document.title || '').trim();
		return 'https://www.pinterest.com/pin/create/button/' +
			'?url=' + encodeURIComponent(page) +
			'&media=' + encodeURIComponent(media) +
			'&description=' + encodeURIComponent(desc);
	}

	function enhance(img) {
		if (isSkippable(img) || img.closest('.fcp-pin-wrap')) {
			return;
		}

		var wrap = document.createElement('span');
		wrap.className = 'fcp-pin-wrap';

		var cs = window.getComputedStyle(img);
		if (cs.display === 'block' || img.classList.contains('img-fulltext') || img.closest('.img-fulltext, .item-image, figure')) {
			wrap.classList.add('fcp-pin-wrap--block');
		}

		var parent = img.parentNode;
		if (!parent) {
			return;
		}
		parent.insertBefore(wrap, img);
		wrap.appendChild(img);

		var btn = document.createElement('button');
		btn.type = 'button';
		btn.className = 'fcp-pin-btn';
		btn.setAttribute('aria-label', 'Salvar no Pinterest');
		btn.setAttribute('title', 'Salvar no Pinterest');
		btn.innerHTML = '<i class="fa fa-pinterest" aria-hidden="true"></i><span>Pin</span>';

		btn.addEventListener('click', function (e) {
			e.preventDefault();
			e.stopPropagation();
			var url = buildPinUrl(img);
			window.open(url, 'pintab_' + Date.now(), 'width=750,height=650,scrollbars=yes,resizable=yes');
		});

		wrap.appendChild(btn);
	}

	function scan(root) {
		var scope = root && root.querySelectorAll ? root : document;
		var nodes = scope.querySelectorAll(ROOT_SELECTORS);
		if (!nodes.length && scope === document) {
			nodes = [document.body];
		}
		Array.prototype.forEach.call(nodes, function (container) {
			if (!container || !container.querySelectorAll) {
				return;
			}
			Array.prototype.forEach.call(container.querySelectorAll('img'), enhance);
		});
	}

	function onReady(fn) {
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', fn);
		} else {
			fn();
		}
	}

	onReady(function () {
		scan(document);

		// Imagens lazy / carregadas depois — remove Pin se a foto for pequena demais
		Array.prototype.forEach.call(document.querySelectorAll('.fcp-pin-wrap img'), function (img) {
			img.addEventListener('load', function () {
				if (!isTooSmall(img)) {
					return;
				}
				var wrap = img.closest('.fcp-pin-wrap');
				if (!wrap || !wrap.parentNode) {
					return;
				}
				wrap.parentNode.insertBefore(img, wrap);
				wrap.parentNode.removeChild(wrap);
			});
		});

		if ('MutationObserver' in window) {
			var obs = new MutationObserver(function (mutations) {
				mutations.forEach(function (m) {
					Array.prototype.forEach.call(m.addedNodes || [], function (node) {
						if (node.nodeType !== 1) {
							return;
						}
						if (node.matches && node.matches('img')) {
							enhance(node);
						} else if (node.querySelectorAll) {
							scan(node);
						}
					});
				});
			});
			obs.observe(document.body, { childList: true, subtree: true });
		}
	});
})();
