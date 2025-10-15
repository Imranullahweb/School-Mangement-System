// theme-toggle.js - Theme Toggle and RTL Support

function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
}
function setRTL(rtl) {
    document.documentElement.setAttribute('dir', rtl ? 'rtl' : 'ltr');
    localStorage.setItem('rtl', rtl ? '1' : '0');
}
function toggleTheme() {
    const current = localStorage.getItem('theme') || 'light';
    setTheme(current === 'light' ? 'dark' : 'light');
}
function toggleRTL() {
    const current = localStorage.getItem('rtl') === '1';
    setRTL(!current);
}
function initThemeAndRTL() {
    const theme = localStorage.getItem('theme') || 'light';
    setTheme(theme);
    const rtl = localStorage.getItem('rtl') === '1';
    setRTL(rtl);
}
document.addEventListener('DOMContentLoaded', initThemeAndRTL); 