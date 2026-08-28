/* DonasiYuk admin: same as frontend lucide-init.js but bound to admin jQuery. */
(function () {
	function render() {
		if (window.lucide && typeof window.lucide.createIcons === 'function') {
			window.lucide.createIcons();
		}
	}

	function initSidebarState() {
		try {
			if (localStorage.getItem('dyk_sidebar_collapsed') === 'true') {
				document.body.classList.add('dyk-sidebar-collapsed');
				document.documentElement.classList.add('dyk-sidebar-collapsed');
			}
		} catch(e) {}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function() {
			initSidebarState();
			render();
		});
	} else {
		initSidebarState();
		render();
	}

	if (window.jQuery) {
		window.jQuery(document).ajaxComplete(render);
	}
})();
