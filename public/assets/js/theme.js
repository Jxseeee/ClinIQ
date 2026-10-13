(function () {
    'use strict';

    var storageKey = 'cliniq-theme';
    var root = document.documentElement;

    function readPreference() {
        try {
            var savedTheme = window.localStorage.getItem(storageKey);
            return savedTheme === 'dark' ? 'dark' : 'light';
        } catch (error) {
            return 'light';
        }
    }

    function savePreference(theme) {
        try {
            window.localStorage.setItem(storageKey, theme);
        } catch (error) {
            // The current page still changes theme when storage is unavailable.
        }
    }

    function updateControls(theme) {
        document.querySelectorAll('[data-theme-toggle]').forEach(function (button) {
            var isDark = theme === 'dark';
            var label = isDark ? 'Switch to light mode' : 'Switch to dark mode';
            var moon = button.querySelector('[data-theme-icon="moon"]');
            var sun = button.querySelector('[data-theme-icon="sun"]');

            button.setAttribute('aria-label', label);
            button.setAttribute('title', label);
            if (moon) moon.hidden = isDark;
            if (sun) sun.hidden = !isDark;
        });
    }

    function applyTheme(theme, persist) {
        root.dataset.theme = theme;
        root.style.colorScheme = theme;
        updateControls(theme);
        if (persist) savePreference(theme);
    }

    function initThemeToggle() {
        applyTheme(root.dataset.theme === 'dark' ? 'dark' : 'light', false);

        document.querySelectorAll('[data-theme-toggle]').forEach(function (button) {
            button.addEventListener('click', function () {
                var nextTheme = root.dataset.theme === 'dark' ? 'light' : 'dark';
                applyTheme(nextTheme, true);

                var visibleIcon = button.querySelector('[data-theme-icon]:not([hidden])');
                if (visibleIcon && window.gsap && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    window.gsap.fromTo(visibleIcon,
                        { rotation: -35, scale: 0.65, opacity: 0 },
                        { rotation: 0, scale: 1, opacity: 1, duration: 0.32, ease: 'back.out(1.8)', clearProps: 'transform,opacity' }
                    );
                }
            });
        });

        window.addEventListener('storage', function (event) {
            if (event.key === storageKey) applyTheme(event.newValue === 'dark' ? 'dark' : 'light', false);
        });
    }

    applyTheme(readPreference(), false);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initThemeToggle);
    } else {
        initThemeToggle();
    }
}());