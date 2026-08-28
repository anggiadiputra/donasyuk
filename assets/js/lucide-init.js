/* DonasiYuk: replace every <i data-lucide="name"> with inline SVG after load + AJAX. */
(function () {
	function render() {
		if (window.lucide && typeof window.lucide.createIcons === 'function') {
			window.lucide.createIcons();
		}
	}
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', render);
	} else {
		render();
	}
	if (window.jQuery) {
		window.jQuery(document).ajaxComplete(render);
	}
})();
