<script>
    (function () {
        var storageKey = 'platform-theme';
        var root = document.documentElement;
        var storedTheme = null;

        try {
            storedTheme = window.localStorage.getItem(storageKey);
        } catch (error) {
            storedTheme = null;
        }

        var hasStoredTheme = storedTheme === 'dark' || storedTheme === 'light';
        var systemPrefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        var theme = hasStoredTheme ? storedTheme : (systemPrefersDark ? 'dark' : 'light');

        root.dataset.theme = theme;
        root.style.colorScheme = theme;
        window.__platformTheme = {
            key: storageKey,
            theme: theme,
            followsSystem: ! hasStoredTheme,
        };
    })();
</script>
