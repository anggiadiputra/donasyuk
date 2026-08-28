/**
 * DonasiYuk Full-Page Admin Mode
 * ───────────────────────────────
 * Injects a custom sidebar and hides WordPress admin chrome
 * when on any DonasiYuk admin page.
 */
(function ($) {
    'use strict';

    // Only run on DonasiYuk pages
    if (typeof dykFullpageData === 'undefined') return;

    var data = dykFullpageData;

    // ── 1. Add body class ──────────────────────────────────
    document.body.classList.add('dyk-fullpage');

    // Restore collapsed state immediately
    try {
        if (localStorage.getItem('dyk_sidebar_collapsed') === 'true') {
            document.body.classList.add('dyk-sidebar-collapsed');
            document.documentElement.classList.add('dyk-sidebar-collapsed');
        }
    } catch(e) {}

    // Also fix <html> padding-top set by WP admin bar
    document.documentElement.style.paddingTop = '0';
    document.documentElement.style.marginTop = '0';

    // ── 2. Build sidebar HTML ──────────────────────────────
    var currentPage = data.currentPage || '';

    function isActive(slug) {
        return currentPage === slug ? ' dyk-nav-active' : '';
    }

    function svgIcon(paths) {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' + paths + '</svg>';
    }

    // Icon definitions (Lucide-style)
    var icons = {
        heart: '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>',
        dashboard: '<rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/>',
        campaign: '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/><path d="M12 5 9.04 7.96a2.17 2.17 0 0 0 0 3.08c.82.82 2.13.85 3 .07l2.07-1.9a2.82 2.82 0 0 1 3.79 0l2.96 2.66"/><path d="m18 15-2-2"/><path d="m15 18-2-2"/>',
        analytics: '<line x1="18" x2="18" y1="20" y2="10"/><line x1="12" x2="12" y1="20" y2="4"/><line x1="6" x2="6" y1="20" y2="14"/>',
        fundraising: '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" x2="15.42" y1="13.51" y2="17.49"/><line x1="15.41" x2="8.59" y1="6.51" y2="10.49"/>',
        shortcode: '<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>',
        members: '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        wallet: '<path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/>',
        profile: '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        settings: '<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>',
        arrowLeft: '<path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>',
        wordpress: '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="m9 8 6 4-6 4Z"/>',
        menu: '<line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/>',
        collapse: '<path d="M18 3a3 3 0 0 1 3 3v12a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3z"/><path d="M9 3v18"/><path d="m14 9-3 3 3 3"/>',
        expand: '<path d="M18 3a3 3 0 0 1 3 3v12a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3z"/><path d="M9 3v18"/><path d="m11 9 3 3-3 3"/>',
        logout: '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/>'
    };

    // Build menu items from server-side data
    var navItems = '';
    if (data.menuItems && data.menuItems.length) {
        for (var i = 0; i < data.menuItems.length; i++) {
            var item = data.menuItems[i];
            var activeClass = isActive(item.slug);
            var iconSvg = icons[item.icon] ? svgIcon(icons[item.icon]) : svgIcon(icons.dashboard);
            var badge = item.badge ? '<span class="dyk-nav-badge">' + item.badge + '</span>' : '';
            navItems += '<li class="' + activeClass + '">' +
                '<a href="' + item.url + '" data-dyk-tooltip="' + (item.label || '') + '">' +
                iconSvg +
                '<span>' + item.label + '</span>' +
                badge +
                '</a></li>';

            // Insert divider before "Donasi Saya"
            if (item.slug === 'donasiyuk_data_members' || 
                (item.dividerAfter)) {
                navItems += '<li><div class="dyk-sidebar-divider"></div></li>';
            }
        }
    }

    var sidebarHTML =
        // Mobile toggle button
        '<button class="dyk-sidebar-toggle" id="dyk-sidebar-toggle" aria-label="Toggle sidebar">' +
            svgIcon(icons.menu) +
        '</button>' +
        // Mobile overlay
        '<div class="dyk-sidebar-overlay" id="dyk-sidebar-overlay"></div>' +
        // Sidebar
        '<nav id="dyk-sidebar" role="navigation" aria-label="DonasiYuk Navigation">' +
            // Brand
            '<div class="dyk-sidebar-brand">' +
                '<div class="dyk-sidebar-brand-icon">' +
                    svgIcon(icons.heart) +
                '</div>' +
                '<div class="dyk-sidebar-brand-text">' +
                    '<div class="dyk-sidebar-brand-name">' + (data.brandName || 'DonasiYuk') + '</div>' +
                    '<div class="dyk-sidebar-brand-version">v' + (data.version || '2.2.5') + '</div>' +
                '</div>' +
            '</div>' +
            // Back to WP button
            '<a href="' + (data.wpAdminUrl || '/wp-admin/') + '" class="dyk-back-wp" id="dyk-back-wp" data-dyk-tooltip="Kembali ke WordPress">' +
                svgIcon(icons.arrowLeft) +
                '<span>Kembali ke WordPress</span>' +
            '</a>' +
            // Section label
            '<div class="dyk-sidebar-label">Menu</div>' +
            // Navigation
            '<ul class="dyk-sidebar-nav">' +
                navItems +
            '</ul>' +
            // Footer with user info & collapse button on top right of profile
            '<div class="dyk-sidebar-footer">' +
                '<div class="dyk-sidebar-footer-header">' +
                    '<button type="button" class="dyk-sidebar-collapse-btn" id="dyk-sidebar-collapse-btn" onclick="if(window.dykToggleSidebar){window.dykToggleSidebar(event);}" title="Kecilkan / Lebarkan Sidebar" aria-label="Toggle Sidebar" data-dyk-tooltip="Kecilkan Sidebar">' +
                        '<span class="dyk-icon-collapse">' + svgIcon(icons.collapse) + '</span>' +
                        '<span class="dyk-icon-expand" style="display:none;">' + svgIcon(icons.expand) + '</span>' +
                    '</button>' +
                '</div>' +
                '<div class="dyk-sidebar-footer-user" data-dyk-tooltip="' + (data.userName || 'User') + ' (' + (data.userRole || 'user') + ')">' +
                    '<img class="dyk-sidebar-footer-avatar" src="' + (data.userAvatar || '') + '" alt="User" />' +
                    '<div class="dyk-sidebar-footer-info">' +
                        '<div class="dyk-sidebar-footer-name">' + (data.userName || 'User') + '</div>' +
                        '<div class="dyk-sidebar-footer-role">' + (data.userRole || 'user') + '</div>' +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</nav>';

    // ── 3. Inject sidebar into DOM (Fallback if not server-rendered) ──
    if ($('#dyk-sidebar').length === 0) {
        $(document.body).prepend(sidebarHTML);
    }

    // ── 4. Desktop Sidebar Collapse / Expand (Single Idempotent Handler) ────
    window.dykToggleSidebar = function(e) {
        if (e && e.preventDefault) e.preventDefault();
        if (e && e.stopPropagation) e.stopPropagation();
        if (e && e.stopImmediatePropagation) e.stopImmediatePropagation();

        var body = document.body;
        var html = document.documentElement;
        var isCurrentlyCollapsed = body.classList.contains('dyk-sidebar-collapsed');
        var willCollapse = !isCurrentlyCollapsed;

        if (willCollapse) {
            body.classList.add('dyk-sidebar-collapsed');
            html.classList.add('dyk-sidebar-collapsed');
        } else {
            body.classList.remove('dyk-sidebar-collapsed');
            html.classList.remove('dyk-sidebar-collapsed');
        }

        try {
            localStorage.setItem('dyk_sidebar_collapsed', willCollapse ? 'true' : 'false');
        } catch(err) {}

        var btn = document.getElementById('dyk-sidebar-collapse-btn');
        if (btn) {
            btn.setAttribute('title', willCollapse ? 'Lebarkan Sidebar' : 'Kecilkan Sidebar');
            btn.setAttribute('data-dyk-tooltip', willCollapse ? 'Lebarkan Sidebar' : 'Kecilkan Sidebar');
        }

        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }
    };

    $(document).off('click.dykCollapse').on('click.dykCollapse', '#dyk-sidebar-collapse-btn, .dyk-sidebar-collapse-btn, #dyk_toggle_sidebar', function (e) {
        window.dykToggleSidebar(e);
    });

    // ── 5. Mobile sidebar toggle ───────────────────────────
    var $sidebar = $('#dyk-sidebar');
    var $overlay = $('#dyk-sidebar-overlay');
    var $toggle = $('#dyk-sidebar-toggle');

    $toggle.on('click', function () {
        $sidebar.toggleClass('dyk-sidebar-open');
        $overlay.toggleClass('dyk-visible');
    });

    $overlay.on('click', function () {
        $sidebar.removeClass('dyk-sidebar-open');
        $overlay.removeClass('dyk-visible');
    });

    // Close sidebar on ESC key
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape' && $sidebar.hasClass('dyk-sidebar-open')) {
            $sidebar.removeClass('dyk-sidebar-open');
            $overlay.removeClass('dyk-visible');
        }
    });

    // ── 6. Global Single-Line Toast Helper & Interceptor ────────────────
    window.dykToast = function (type, title, timer) {
        var iconType = (type === 'error' || type === 'failed') ? 'error' : ((type === 'warning' || type === 'note') ? 'warning' : 'success');
        var textTitle = title || 'Berhasil!';
        if (typeof Swal !== 'undefined' && Swal.fire) {
            return Swal.fire({
                toast: true,
                position: 'top-end',
                icon: iconType,
                title: textTitle,
                showConfirmButton: false,
                timer: timer || 2800,
                timerProgressBar: true,
                customClass: {
                    popup: 'dyk-swal-toast'
                }
            });
        } else if (typeof swal !== 'undefined' && swal.fire) {
            return swal.fire({
                toast: true,
                position: 'top-end',
                icon: iconType,
                title: textTitle,
                showConfirmButton: false,
                timer: timer || 2800,
                timerProgressBar: true,
                customClass: {
                    popup: 'dyk-swal-toast'
                }
            });
        }
    };

    function attachSwalInterceptor() {
        if (typeof Swal !== 'undefined' && Swal.fire && !Swal._dykToastIntercepted) {
            var origSwalFire = Swal.fire;
            Swal.fire = function () {
                if (arguments.length >= 2 && typeof arguments[0] === 'string' && typeof arguments[1] === 'string') {
                    var title = arguments[0];
                    var message = arguments[1];
                    var icon = arguments[2] || 'success';
                    var msg = message && message.trim().length > 0 ? message : title;
                    return origSwalFire.call(Swal, {
                        toast: true,
                        position: 'top-end',
                        icon: icon,
                        title: msg,
                        showConfirmButton: false,
                        timer: 2800,
                        timerProgressBar: true,
                        customClass: {
                            popup: 'dyk-swal-toast'
                        }
                    });
                }
                return origSwalFire.apply(Swal, arguments);
            };
            Swal._dykToastIntercepted = true;
        }

        if (typeof swal !== 'undefined' && swal.fire && !swal._dykToastIntercepted) {
            var origSwal2Fire = swal.fire;
            swal.fire = function () {
                if (arguments.length >= 2 && typeof arguments[0] === 'string' && typeof arguments[1] === 'string') {
                    var title = arguments[0];
                    var message = arguments[1];
                    var icon = arguments[2] || 'success';
                    var msg = message && message.trim().length > 0 ? message : title;
                    return origSwal2Fire.call(swal, {
                        toast: true,
                        position: 'top-end',
                        icon: icon,
                        title: msg,
                        showConfirmButton: false,
                        timer: 2800,
                        timerProgressBar: true,
                        customClass: {
                            popup: 'dyk-swal-toast'
                        }
                    });
                }
                return origSwal2Fire.apply(swal, arguments);
            };
            swal._dykToastIntercepted = true;
        }
    }

    $(document).ready(function() {
        attachSwalInterceptor();
    });
    setTimeout(attachSwalInterceptor, 500);
    setTimeout(attachSwalInterceptor, 1500);

})(jQuery);

