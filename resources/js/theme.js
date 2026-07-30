window.toggleTheme = function (storageKey) {
    var isDark = document.documentElement.classList.toggle('dark');

    try {
        localStorage.setItem(storageKey, isDark ? 'dark' : 'light');
    } catch (e) {}
};
