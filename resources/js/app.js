import './bootstrap';

// Import modules...
import { LoadingPlugin } from 'vue-loading-overlay';
import { appName } from '@/Libs/defaults';
import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { loadingOptions } from './Libs/defaults';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import VueSweetalert2 from 'vue-sweetalert2';
import VueSocialSharing from 'vue-social-sharing';
import 'sweetalert2/dist/sweetalert2.min.css';

window.inertiaEventsCount = {
    navigateCount: 0,
    successCount: 0,
    errorCount: 0,
    isDebug: false,
};

createInertiaApp({
    title: title => `${title} | ${appName}`,
    //resolve: name => require(`./Pages/${name}`),
    progress: {
        color: '#29d',
    },
    resolve: (name) => {
        let module = name.split('::');

        if (module[1]) {
            if (module[0] == 'Space') {
                return resolvePageComponent(
                    `../../modules/Space/Resources/assets/js/Pages/${module[1]}.vue`,
                    import.meta.glob(`../../modules/Space/Resources/assets/js/Pages/**/*.vue`)
                );
            }

            if (module[0] == 'Booking') {
                return resolvePageComponent(
                    `../../modules/Booking/Resources/assets/js/Pages/${module[1]}.vue`,
                    import.meta.glob(`../../modules/Booking/Resources/assets/js/Pages/**/*.vue`)
                );
            }

            if (module[0] == 'FormBuilder') {
                return resolvePageComponent(
                    `../../modules/FormBuilder/Resources/assets/js/Pages/${module[1]}.vue`,
                    import.meta.glob(`../../modules/FormBuilder/Resources/assets/js/Pages/**/*.vue`)
                );
            }
        }

        return resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue')
        );
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .mixin({ methods: { route } })
            .use(plugin)
            .use(VueSweetalert2)
            .use(LoadingPlugin, loadingOptions)
            .use(VueSocialSharing)
            .mount(el)
    },
})

// This handles offline session validation using a local authentication token
// Configuration
const AUTH_TOKEN_KEY = 'buskincity_auth_token';
const AUTH_TOKEN_EXPIRY_KEY = 'buskincity_auth_expiry';

// Helper function to generate a secure random token
function generateAuthToken() {
    const array = new Uint8Array(32);
    crypto.getRandomValues(array);
    return Array.from(array, byte => byte.toString(16).padStart(2, '0')).join('');
}

// Helper function to set auth token with expiry
function setAuthToken(expiryMinutes = 120) { // 2 hours default
    const token = generateAuthToken();
    const expiry = Date.now() + (expiryMinutes * 60 * 1000);
    
    localStorage.setItem(AUTH_TOKEN_KEY, token);
    localStorage.setItem(AUTH_TOKEN_EXPIRY_KEY, expiry.toString());
    
    // Also set as a non-HttpOnly cookie for additional validation
    const expiryDate = new Date(expiry);
    document.cookie = `buskincity_auth_client=${token}; expires=${expiryDate.toUTCString()}; path=/; SameSite=Lax`;
    
    return token;
}

// Helper function to get auth token if valid
function getValidAuthToken() {
    const token = localStorage.getItem(AUTH_TOKEN_KEY);
    const expiry = localStorage.getItem(AUTH_TOKEN_EXPIRY_KEY);
    
    if (!token || !expiry) {
        return null;
    }
    
    // Check if token has expired
    if (Date.now() > parseInt(expiry)) {
        clearAuthToken();
        return null;
    }
    
    return token;
}

// Helper function to clear auth token
function clearAuthToken() {
    localStorage.removeItem(AUTH_TOKEN_KEY);
    localStorage.removeItem(AUTH_TOKEN_EXPIRY_KEY);
    
    // Clear the client cookie
    document.cookie = 'buskincity_auth_client=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/';
}

// Helper function to check if user is authenticated locally
function isAuthenticatedLocally() {
    const token = getValidAuthToken();
    
    if (!token) {
        return false;
    }
    
    // Additional validation: check if client cookie matches localStorage
    const clientCookie = getCookie('buskincity_auth_client');
    if (clientCookie !== token) {
        console.warn('Auth token mismatch between localStorage and cookie');
        clearAuthToken();
        return false;
    }
    
    return true;
}

// Helper function to get cookie value
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
    return null;
}

// Function to check if current route is an admin route (protected)
function isAdminRoute(url) {
    const path = new URL(url, window.location.origin).pathname;
    return path.startsWith('/admin');
}

// Function to check if current route is admin login (should be accessible without session)
function isAdminLoginRoute(url) {
    const path = new URL(url, window.location.origin).pathname;
    return path === '/admin/login' || 
        path === '/admin/register' || 
        path === '/admin/forgot-password' || 
        path.startsWith('/admin/reset-password') ||
        path.startsWith('/admin/email/verify') ||
        path === '/admin/two-factor-challenge';
}

// Function to handle authentication failure
function handleAuthFailure(event = null) {
    console.warn('Authentication validation failed, redirecting to admin login');
    
    // Prevent navigation if event is provided
    if (event && event.preventDefault) {
        event.preventDefault();
    }
    
    // Clear all authentication data
    clearAuthToken();
    
    if (typeof Storage !== 'undefined') {
        localStorage.clear();
        sessionStorage.clear();
    }
    
    // Clear all cookies related to authentication
    const authCookies = ['buskincity_auth_client', 'XSRF-TOKEN', 'laravel_session'];
    authCookies.forEach(cookieName => {
        document.cookie = `${cookieName}=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/`;
        document.cookie = `${cookieName}=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/; domain=${window.location.hostname}`;
    });
    
    // Force redirect to admin login
    window.location.href = '/admin/login';
    
    return false;
}

// Check authentication on page load/reload for admin routes
document.addEventListener('DOMContentLoaded', function() {
    const currentPath = window.location.pathname;
    
    // Only check if we're on an admin route but not on admin login pages
    if (isAdminRoute(window.location.href) && !isAdminLoginRoute(window.location.href)) {
        setTimeout(() => {
            // Initialize token if user data is available
            initializeAuthToken();
            
            // Check local authentication
            if (!isAuthenticatedLocally()) {
                handleAuthFailure();
            }
        }, 100); // Wait for Inertia to load
    }

    // Handle session validation before Inertia requests start
    router.on('start', (event) => {
        const targetUrl = event.detail.visit.url;
        const urlString = targetUrl.href || targetUrl;
        
        if (isAdminRoute(urlString) && !isAdminLoginRoute(urlString)) {
            if (!isAuthenticatedLocally()) {
                console.warn('Authentication expired during admin navigation, canceling request');
                
                // Cancel the current request
                event.detail.visit.cancel();
                
                // Handle authentication failure
                handleAuthFailure();
            }
        }
    });

    // Periodic authentication check (every 30 seconds)
    setInterval(() => {
        const currentPath = window.location.pathname;
        
        // Only check if we're on an admin route but not on login pages
        if (isAdminRoute(window.location.href) && !isAdminLoginRoute(window.location.href)) {
            if (!isAuthenticatedLocally()) {
                console.warn('Authentication expired during periodic check');
                handleAuthFailure();
            }
        }
    }, 30000); // Check every 30 seconds

    // Handle browser back/forward navigation for admin routes
    window.addEventListener('popstate', function(event) {
        setTimeout(() => {
            const currentPath = window.location.pathname;
            
            if (isAdminRoute(window.location.href) && !isAdminLoginRoute(window.location.href)) {
                if (!isAuthenticatedLocally()) {
                    console.warn('Authentication expired on browser navigation');
                    handleAuthFailure();
                }
            }
        }, 50);
    });

    // Handle page visibility change (when user comes back to tab)
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            const currentPath = window.location.pathname;
            
            if (isAdminRoute(window.location.href) && !isAdminLoginRoute(window.location.href)) {
                if (!isAuthenticatedLocally()) {
                    console.warn('Authentication expired while tab was hidden');
                    handleAuthFailure();
                }
            }
        }
    });

    // Listen for storage events to sync across tabs
    window.addEventListener('storage', function(event) {
        if (event.key === AUTH_TOKEN_KEY || event.key === AUTH_TOKEN_EXPIRY_KEY) {
            const currentPath = window.location.pathname;
            
            if (isAdminRoute(window.location.href) && !isAdminLoginRoute(window.location.href)) {
                // Token was cleared in another tab
                if (!event.newValue || !getValidAuthToken()) {
                    console.warn('Authentication cleared in another tab');
                    handleAuthFailure();
                }
            }
        }
    });

    // Export helper functions for debugging and manual control
    if (typeof window !== 'undefined') {
        window.authHelpers = {
            isAuthenticated: isAuthenticatedLocally,
            setAuthToken: setAuthToken,
            clearAuthToken: clearAuthToken,
            getValidAuthToken: getValidAuthToken,
            isAdminRoute: isAdminRoute,
            isAdminLoginRoute: isAdminLoginRoute,
            
            // Manual token refresh (call this after successful login)
            refreshAuthToken: () => {
                clearAuthToken();
                initializeAuthToken();
            }
        };
    }
});

// Add this to your resources/js/app.js BEFORE the router event listeners
// This initializes the auth token from Laravel shared data

// Initialize auth token from server-provided data
function initializeAuthTokenFromServer() {
    const page = window.page || {};
    const props = page.props || {};
    
    // Check if Laravel provided auth token data
    if (props.authToken && props.authTokenExpiry) {
        console.log('Initializing auth token from server data');
        
        const token = props.authToken;
        const expiry = props.authTokenExpiry * 1000; // Convert to milliseconds
        
        // Store in localStorage
        localStorage.setItem('buskincity_auth_token', token);
        localStorage.setItem('buskincity_auth_expiry', expiry.toString());
        
        return true;
    }
    
    // Fallback: try to get from cookies set by Laravel
    const clientToken = getCookie('buskincity_auth_client');
    const clientExpiry = getCookie('buskincity_auth_client_expiry');
    
    if (clientToken && clientExpiry) {
        console.log('Initializing auth token from cookies');
        
        const expiry = parseInt(clientExpiry) * 1000; // Convert to milliseconds
        
        // Store in localStorage
        localStorage.setItem('buskincity_auth_token', clientToken);
        localStorage.setItem('buskincity_auth_expiry', expiry.toString());
        
        return true;
    }
    
    return false;
}

// Enhanced initialization function
function initializeAuthToken() {
    // First, try to initialize from server data
    if (initializeAuthTokenFromServer()) {
        return;
    }
    
    // If no server data, check if we have user data and should generate token
    const page = window.page || {};
    const props = page.props || {};
    const user = props.user;
    
    if (user && user.id && !getValidAuthToken()) {
        console.log('User authenticated but no token found, this should not happen');
        console.log('User may need to refresh the page or re-login');
        
        // In this case, we cannot generate a token client-side for security
        // The user should be redirected to login to get a proper token
        return false;
    }
    
    return true;
}

// Call this when the page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeAuthToken();
});

// Update the router navigation handler to use the enhanced initialization
router.on('navigate', (event) => {
    const currentUrl = event.detail.page.url;
    const isAdmin = isAdminRoute(currentUrl);
    const isAdminLogin = isAdminLoginRoute(currentUrl);
    
    // Update global page object
    window.page = event.detail.page;
    
    // Initialize token from server data if available
    initializeAuthToken();
    
    // Only check authentication for admin routes that are not login-related
    if (isAdmin && !isAdminLogin) {
        if (!isAuthenticatedLocally()) {
            return handleAuthFailure(event);
        }
    }
    
    return true;
});


router.on('success', (event) => {
    window.inertiaEventsCount.successCount++;
    if (window.inertiaEventsCount.isDebug) {
        console.log(`Successfully made a visit to ${event.detail.page.url}`)
    }
});
router.on('error', (errors) => {
    window.inertiaEventsCount.errorCount++;
    if (window.inertiaEventsCount.isDebug) {
        console.log(errors)
    }
});
// router.on('navigate', (event) => {
//     console.log(`Navigating to ${event.detail.page.url}`, document.cookie);
//     window.inertiaEventsCount.navigateCount++;
//     if (window.inertiaEventsCount.isDebug) {
//         console.log(`Navigated to ${event.detail.page.url}`);
//     }
// });
