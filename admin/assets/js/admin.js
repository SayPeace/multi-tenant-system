/**
 * Admin Dashboard JavaScript
 * Multi-Tenant Journal Management System
 */

(function() {
    'use strict';

    // Auto-hide flash messages after 5 seconds
    document.querySelectorAll('.alert').forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s ease';
            setTimeout(function() {
                alert.remove();
            }, 500);
        }, 5000);
    });

    // Sidebar toggle for mobile
    var sidebarToggle = document.querySelector('.sidebar-toggle');
    var sidebar = document.querySelector('.sidebar');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
        });

        // Close sidebar when clicking outside
        document.addEventListener('click', function(e) {
            if (sidebar.classList.contains('open') &&
                !sidebar.contains(e.target) &&
                !sidebarToggle.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        });
    }

    // Confirm delete actions
    document.querySelectorAll('[data-confirm]').forEach(function(element) {
        element.addEventListener('click', function(e) {
            var message = this.getAttribute('data-confirm') || 'Are you sure?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // Form validation feedback
    document.querySelectorAll('form').forEach(function(form) {
        form.addEventListener('submit', function() {
            var submitBtn = form.querySelector('[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Please wait...';
            }
        });
    });

    // CSRF token for AJAX requests
    var csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
        // Set default header for fetch requests
        var originalFetch = window.fetch;
        window.fetch = function(url, options) {
            options = options || {};
            options.headers = options.headers || {};
            if (typeof options.headers.set === 'function') {
                options.headers.set('X-CSRF-Token', csrfToken.content);
            } else {
                options.headers['X-CSRF-Token'] = csrfToken.content;
            }
            return originalFetch(url, options);
        };
    }

    // Tab functionality (if needed)
    document.querySelectorAll('[data-tab]').forEach(function(tabBtn) {
        tabBtn.addEventListener('click', function(e) {
            e.preventDefault();
            var tabId = this.getAttribute('data-tab');
            var tabContent = document.getElementById(tabId);

            if (tabContent) {
                // Hide all tabs
                document.querySelectorAll('.tab-content').forEach(function(content) {
                    content.style.display = 'none';
                });

                // Remove active from all buttons
                document.querySelectorAll('[data-tab]').forEach(function(btn) {
                    btn.classList.remove('active');
                });

                // Show selected tab
                tabContent.style.display = 'block';
                this.classList.add('active');
            }
        });
    });

    // Dropdown menus (if needed)
    document.querySelectorAll('.dropdown-toggle').forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var dropdown = this.nextElementSibling;
            if (dropdown && dropdown.classList.contains('dropdown-menu')) {
                dropdown.classList.toggle('show');
            }
        });
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
            menu.classList.remove('show');
        });
    });

    console.log('Admin Dashboard initialized');
})();
