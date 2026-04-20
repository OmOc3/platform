import './bootstrap';

const themeStorageKey = window.__platformTheme?.key ?? 'platform-theme';
const themeMediaQuery = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;

const getStoredTheme = () => {
    try {
        const value = window.localStorage.getItem(themeStorageKey);

        return value === 'dark' || value === 'light' ? value : null;
    } catch (error) {
        return null;
    }
};

const getSystemTheme = () => themeMediaQuery?.matches ? 'dark' : 'light';

const getPreferredTheme = () => getStoredTheme() ?? getSystemTheme();

const syncThemeToggles = (theme) => {
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        const isDark = theme === 'dark';
        const label = button.querySelector('[data-theme-label]');
        const status = button.querySelector('[data-theme-status]');

        button.dataset.activeTheme = theme;
        button.setAttribute('aria-pressed', isDark ? 'true' : 'false');
        button.setAttribute('aria-label', isDark ? 'تفعيل الوضع الفاتح' : 'تفعيل الوضع الداكن');
        button.setAttribute('title', isDark ? 'تفعيل الوضع الفاتح' : 'تفعيل الوضع الداكن');

        if (label) {
            label.textContent = isDark ? 'داكن' : 'فاتح';
        }

        if (status) {
            status.textContent = isDark ? 'الوضع الحالي: داكن' : 'الوضع الحالي: فاتح';
        }
    });
};

const applyTheme = (theme, { persist = false } = {}) => {
    const nextTheme = theme === 'dark' ? 'dark' : 'light';
    const root = document.documentElement;

    root.dataset.theme = nextTheme;
    root.style.colorScheme = nextTheme;

    if (persist) {
        try {
            window.localStorage.setItem(themeStorageKey, nextTheme);
        } catch (error) {
            // Ignore unavailable storage and still apply the theme in-memory.
        }
    }

    window.__platformTheme = {
        key: themeStorageKey,
        theme: nextTheme,
        followsSystem: ! getStoredTheme(),
    };

    syncThemeToggles(nextTheme);
};

const bindThemeToggles = () => {
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        if (button.dataset.themeBound === 'true') {
            return;
        }

        button.dataset.themeBound = 'true';
        button.addEventListener('click', () => {
            const nextTheme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';

            applyTheme(nextTheme, { persist: true });
        });
    });

    syncThemeToggles(document.documentElement.dataset.theme || getPreferredTheme());
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bindThemeToggles, { once: true });
} else {
    bindThemeToggles();
}

if (themeMediaQuery) {
    const handleSystemThemeChange = (event) => {
        if (getStoredTheme()) {
            return;
        }

        applyTheme(event.matches ? 'dark' : 'light');
    };

    if (typeof themeMediaQuery.addEventListener === 'function') {
        themeMediaQuery.addEventListener('change', handleSystemThemeChange);
    } else if (typeof themeMediaQuery.addListener === 'function') {
        themeMediaQuery.addListener(handleSystemThemeChange);
    }
}

document.addEventListener('livewire:navigated', bindThemeToggles);
