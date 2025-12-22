import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Redirecionamento unificado para sessão expirada ou falha de rede
const LOGIN_PATH = '/login';

function shouldRedirectToLogin() {
    return typeof window !== 'undefined' && window.location && window.location.pathname !== LOGIN_PATH;
}

function redirectToLogin() {
    if (shouldRedirectToLogin()) {
        window.location.href = LOGIN_PATH;
    }
}

function isAuthExpiredStatus(status) {
    return status === 401 || status === 419;
}

// Interceptor global do axios (SPA + Blade)
axios.interceptors.response.use(
    (response) => {
        // Se o backend redirecionou para /login, forçar navegação completa
        if (response?.request?.responseURL && response.request.responseURL.includes(LOGIN_PATH)) {
            redirectToLogin();
        }
        return response;
    },
    (error) => {
        const status = error?.response?.status;
        const isNetworkError = !error?.response;

        if (isNetworkError || isAuthExpiredStatus(status)) {
            redirectToLogin();
        }

        return Promise.reject(error);
    }
);

// Wrapper global para fetch, garantindo tratamento uniforme em chamadas AJAX
if (typeof window !== 'undefined' && !window.__fetchAuthWrapped) {
    const originalFetch = window.fetch.bind(window);

    window.fetch = async (...args) => {
        try {
            const response = await originalFetch(...args);

            const redirectedToLogin = response.redirected && response.url.includes(LOGIN_PATH);
            if (redirectedToLogin || isAuthExpiredStatus(response.status)) {
                redirectToLogin();
            }

            return response;
        } catch (err) {
            // Erro de rede ou offline
            redirectToLogin();
            throw err;
        }
    };

    // Flag para evitar múltiplos wrappers (hot reload / reexecução)
    window.__fetchAuthWrapped = true;
}
