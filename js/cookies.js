// Cookie management functions
const Cookies = {
    // Set cookie with expiration
    set: function(name, value, days = 30) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        const expires = "; expires=" + date.toUTCString();
        document.cookie = name + "=" + encodeURIComponent(value) + expires + "; path=/";
    },

    // Get cookie value by name
    get: function(name) {
        const nameEQ = name + "=";
        const ca = document.cookie.split(';');
        for(let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return decodeURIComponent(c.substring(nameEQ.length, c.length));
        }
        return null;
    },

    // Delete cookie
    delete: function(name) {
        document.cookie = name + '=; Max-Age=-99999999; path=/';
    }
};

// Save user preferences
function saveUserPreferences() {
    const sortBy = new URLSearchParams(window.location.search).get('sort') || 'upload_time';
    const order = new URLSearchParams(window.location.search).get('order') || 'DESC';
    const perPage = document.querySelector('select[name="per_page"]')?.value || '10';
    const fileType = document.querySelector('select[name="file_type"]')?.value || '';

    Cookies.set('user_sort', sortBy);
    Cookies.set('user_order', order);
    Cookies.set('user_per_page', perPage);
    Cookies.set('user_file_type', fileType);
}

// Apply user preferences
function applyUserPreferences() {
    const urlParams = new URLSearchParams(window.location.search);
    
    // Only apply cookie preferences if no sorting/search parameters are present
    if (!urlParams.has('sort') && !urlParams.has('order') && 
        !urlParams.has('start_date') && !urlParams.has('file_name') && 
        !urlParams.has('file_type')) {
        
        const sortBy = Cookies.get('user_sort') || 'upload_time';
        const order = Cookies.get('user_order') || 'DESC';
        
        // Update the URL without reloading
        urlParams.set('sort', sortBy);
        urlParams.set('order', order);
        window.history.replaceState({}, '', `?${urlParams.toString()}`);
    }

    // Apply preferences to form elements
    const perPage = Cookies.get('user_per_page') || '10';
    const fileType = Cookies.get('user_file_type') || '';

    if (document.querySelector('select[name="per_page"]')) {
        document.querySelector('select[name="per_page"]').value = perPage;
    }
    if (document.querySelector('select[name="file_type"]')) {
        document.querySelector('select[name="file_type"]').value = fileType;
    }
}

// Show cookie consent banner for first-time visitors
function showCookieConsent() {
    if (!Cookies.get('cookie_consent')) {
        const banner = document.createElement('div');
        banner.className = 'cookie-banner';
        banner.innerHTML = `
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <strong>Cookie Notice:</strong> We use cookies to enhance your experience. By continuing to use this site, you agree to our use of cookies.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        document.body.insertBefore(banner, document.body.firstChild);

        // Add event listener to close button
        banner.querySelector('.btn-close').addEventListener('click', function() {
            Cookies.set('cookie_consent', 'true');
        });
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    showCookieConsent();
    applyUserPreferences();

    // Save preferences when form is submitted
    const searchForm = document.querySelector('form');
    if (searchForm) {
        searchForm.addEventListener('submit', saveUserPreferences);
    }

    // Add click handlers to sorting links
    document.querySelectorAll('th a').forEach(link => {
        link.addEventListener('click', function(e) {
            const url = new URL(this.href);
            Cookies.set('user_sort', url.searchParams.get('sort'));
            Cookies.set('user_order', url.searchParams.get('order'));
        });
    });
});