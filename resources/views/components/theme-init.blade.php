@props(['key'])
<script>
    (function () {
        var storageKey = '{{ $key }}';

        function applyStoredTheme() {
            try {
                var stored = localStorage.getItem(storageKey);
                var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                var isDark = stored ? stored === 'dark' : prefersDark;
                document.documentElement.classList.toggle('dark', isDark);
            } catch (e) {}
        }

        applyStoredTheme();

        // Keep other tabs/windows (e.g. landing page open alongside the user
        // dashboard) in sync the moment the theme changes elsewhere, instead
        // of only picking it up on next load — the native `storage` event
        // only fires in OTHER tabs, never the one that made the change.
        window.addEventListener('storage', function (e) {
            if (e.key === storageKey) {
                document.documentElement.classList.toggle('dark', e.newValue === 'dark');
            }
        });

        // Pages restored from the browser's back/forward cache (bfcache)
        // don't re-run this script, so re-check localStorage on restore too.
        window.addEventListener('pageshow', function (e) {
            if (e.persisted) {
                applyStoredTheme();
            }
        });
    })();
</script>
