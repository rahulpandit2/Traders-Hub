// Cookie management for admin panel settings
const CookieSettings = {
    // Set cookie with expiration
    setCookie: function(name, value, days = 30) {
        const d = new Date();
        d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
        const expires = "expires=" + d.toUTCString();
        document.cookie = name + "=" + value + ";" + expires + ";path=/";
    },

    // Get cookie value by name
    getCookie: function(name) {
        const cookieName = name + "=";
        const cookies = document.cookie.split(';');
        for(let i = 0; i < cookies.length; i++) {
            let cookie = cookies[i].trim();
            if (cookie.indexOf(cookieName) === 0) {
                return cookie.substring(cookieName.length, cookie.length);
            }
        }
        return "";
    },

    // Save pagination settings
    savePaginationSettings: function(page, perPage) {
        this.setCookie('admin_page', page);
        this.setCookie('admin_per_page', perPage);
    },

    // Save contact filter settings
    saveContactSettings: function(status, date) {
        this.setCookie('contact_status', status);
        this.setCookie('contact_date', date);
    },

    // Save logs pagination settings
    saveLogSettings: function(page, perPage) {
        this.setCookie('logs_page', page);
        this.setCookie('logs_per_page', perPage);
    },

    // Clear all admin settings cookies
    clearAllSettings: function() {
        const cookies = ['admin_page', 'admin_per_page', 'contact_status', 'contact_date', 'logs_page', 'logs_per_page'];
        cookies.forEach(cookie => {
            document.cookie = cookie + '=;expires=Thu, 01 Jan 1970 00:00:01 GMT;path=/';
        });
    }
};