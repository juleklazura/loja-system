'use strict';

window.EcommerceApp = window.EcommerceApp || {};

EcommerceApp.Utils = (() => {
    const debounce = (fn, delay) => {
        let timeoutId;
        return (...args) => {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => fn.apply(this, args), delay);
        };
    };

    const throttle = (fn, limit) => {
        let inThrottle;
        return (...args) => {
            if (!inThrottle) {
                fn.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    };

    const validateEmail = email => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

    const formatCurrency = (value, currency = 'BRL') => 
        new Intl.NumberFormat('pt-BR', { style: 'currency', currency }).format(value);

    const log = (level, message, data = {}) => {
        if (console?.log) {
            const timestamp = new Date().toISOString();
            const methods = { error: 'error', warn: 'warn', info: 'log' };
            console[methods[level] || 'log'](`[${timestamp}] ${level.toUpperCase()}: ${message}`, data);
        }
    };

    return { debounce, throttle, validateEmail, formatCurrency, log };
})();

EcommerceApp.Counters = (() => {
    const SELECTORS = {
        CART: '[data-cart-count]',
        WISHLIST: '[data-wishlist-count]'
    };

    const updateCounter = (selector, quantity, type) => {
        const elements = document.querySelectorAll(selector);
        elements.forEach(el => {
            if (el) {
                el.textContent = quantity || 0;
                el.style.display = quantity > 0 ? 'flex' : 'none';
                el.classList.add('counter-updated');
                setTimeout(() => el.classList.remove('counter-updated'), 300);
            }
        });
        EcommerceApp.Utils.log('info', `${type} counter updated: ${quantity}`);
    };

    const updateCart = quantity => updateCounter(SELECTORS.CART, quantity, 'cart');
    const updateWishlist = quantity => updateCounter(SELECTORS.WISHLIST, quantity, 'wishlist');

    const init = () => {
        [SELECTORS.CART, SELECTORS.WISHLIST].forEach(selector => {
            const el = document.querySelector(selector);
            if (el && parseInt(el.textContent) === 0) {
                el.style.display = 'none';
            }
        });
    };

    return { updateCart, updateWishlist, init };
})();

EcommerceApp.Notifications = (() => {
    const CONFIG = { AUTO_DISMISS: 4000, MAX_NOTIFICATIONS: 3 };
    const TYPES = { SUCCESS: 'success', ERROR: 'danger', WARNING: 'warning', INFO: 'info' };
    const ICONS = {
        [TYPES.SUCCESS]: 'check-circle',
        [TYPES.ERROR]: 'exclamation-circle',
        [TYPES.WARNING]: 'exclamation-triangle',
        [TYPES.INFO]: 'info-circle'
    };

    const create = (message, type) => {
        const div = document.createElement('div');
        const icon = ICONS[type] || ICONS[TYPES.INFO];
        
        div.className = `alert alert-${type} alert-dismissible fade show notification-floating`;
        div.setAttribute('role', 'alert');
        div.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-${icon} me-2"></i>
                <span class="flex-grow-1">${message}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        return div;
    };

    const removeOld = () => {
        const existing = document.querySelectorAll('.notification-floating');
        const excess = existing.length - CONFIG.MAX_NOTIFICATIONS + 1;
        for (let i = 0; i < excess; i++) {
            existing[i]?.remove();
        }
    };

    const show = (message, type = TYPES.SUCCESS) => {
        if (!message) return;
        
        removeOld();
        const notification = create(message, type);
        document.body.appendChild(notification);
        
        const timer = setTimeout(() => {
            notification.classList.add('fade-out');
            setTimeout(() => notification.remove(), 300);
        }, CONFIG.AUTO_DISMISS);
        
        notification.querySelector('.btn-close')?.addEventListener('click', () => clearTimeout(timer));
    };

    return { show, types: TYPES };
})();

EcommerceApp.Forms = (() => {
    const handleNewsletter = form => {
        const emailInput = form.querySelector('input[type="email"]');
        if (!emailInput) return;

        const email = emailInput.value.trim();
        
        if (!email) {
            EcommerceApp.Notifications.show('Por favor, digite seu e-mail.', EcommerceApp.Notifications.types.WARNING);
            return;
        }

        if (!EcommerceApp.Utils.validateEmail(email)) {
            EcommerceApp.Notifications.show('Por favor, digite um e-mail válido.', EcommerceApp.Notifications.types.ERROR);
            return;
        }

        setTimeout(() => {
            EcommerceApp.Notifications.show('Obrigado por se inscrever!', EcommerceApp.Notifications.types.SUCCESS);
            emailInput.value = '';
        }, 500);
    };

    const handleSearch = form => {
        const input = form.querySelector('input[name="search"]');
        if (!input) return;

        const debouncedValidation = EcommerceApp.Utils.debounce(e => {
            const value = e.target.value.trim();
            if (value.length > 0 && value.length < 2) {
                EcommerceApp.Notifications.show('Digite pelo menos 2 caracteres.', EcommerceApp.Notifications.types.INFO);
            }
        }, 500);

        input.addEventListener('input', debouncedValidation);
        
        form.addEventListener('submit', e => {
            if (!input.value.trim()) {
                e.preventDefault();
                EcommerceApp.Notifications.show('Digite algo para buscar.', EcommerceApp.Notifications.types.WARNING);
            }
        });
    };

    const init = () => {
        const newsletter = document.getElementById('newsletterSubscriptionForm');
        if (newsletter) {
            newsletter.addEventListener('submit', e => {
                e.preventDefault();
                handleNewsletter(newsletter);
            });
        }

        document.querySelectorAll('form[role="search"]').forEach(handleSearch);
    };

    return { init };
})();

EcommerceApp.Accessibility = (() => {
    const enhanceKeyboard = () => {
        document.addEventListener('keydown', e => {
            if (e.altKey && e.key === 's') {
                e.preventDefault();
                document.querySelector('main')?.focus();
            }
            
            if (e.ctrlKey && e.key === 'k') {
                e.preventDefault();
                document.querySelector('input[name="search"]')?.focus();
            }
        });
    };

    const announce = message => {
        const div = document.createElement('div');
        div.setAttribute('aria-live', 'polite');
        div.className = 'sr-only';
        div.textContent = message;
        
        document.body.appendChild(div);
        setTimeout(() => div.remove(), 1000);
    };

    const init = () => {
        enhanceKeyboard();
        
        if (!document.querySelector('.skip-link')) {
            const skipLink = document.createElement('a');
            skipLink.href = '#main';
            skipLink.className = 'skip-link position-absolute top-0 start-0 bg-primary text-white p-2 text-decoration-none';
            skipLink.style.transform = 'translateY(-100%)';
            skipLink.textContent = 'Pular para conteúdo';
            
            skipLink.addEventListener('focus', () => {
                skipLink.style.transform = 'translateY(0)';
            });
            
            skipLink.addEventListener('blur', () => {
                skipLink.style.transform = 'translateY(-100%)';
            });
            
            document.body.prepend(skipLink);
        }
    };

    return { init, announce };
})();

EcommerceApp.App = (() => {
    let initialized = false;

    const init = () => {
        if (initialized) return;
        
        try {
            EcommerceApp.Counters.init();
            EcommerceApp.Forms.init();
            EcommerceApp.Accessibility.init();
            
            initialized = true;
            EcommerceApp.Utils.log('info', 'App initialized successfully');
            EcommerceApp.Accessibility.announce('Aplicação carregada');
        } catch (error) {
            EcommerceApp.Utils.log('error', 'App initialization failed', error);
        }
    };

    const status = () => ({ initialized, timestamp: Date.now() });

    return { init, status };
})();

// Legacy compatibility
Object.assign(window, {
    updateCartCounter: EcommerceApp.Counters.updateCart,
    updateWishlistCounter: EcommerceApp.Counters.updateWishlist,
    showMessage: EcommerceApp.Notifications.show,
    showCartMessage: (message, success = true) => {
        EcommerceApp.Notifications.show(
            message,
            success ? EcommerceApp.Notifications.types.SUCCESS : EcommerceApp.Notifications.types.ERROR
        );
    }
});

// Initialize
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', EcommerceApp.App.init);
} else {
    EcommerceApp.App.init();
}

// Add styles
const style = document.createElement('style');
style.textContent = `
.counter-updated { animation: counterBounce 0.3s ease-out; }
@keyframes counterBounce { 0% { transform: scale(1); } 50% { transform: scale(1.2); } 100% { transform: scale(1); } }
.fade-out { opacity: 0 !important; transform: translateX(100%) !important; }
.sr-only { position: absolute !important; width: 1px !important; height: 1px !important; padding: 0 !important; margin: -1px !important; overflow: hidden !important; clip: rect(0,0,0,0) !important; white-space: nowrap !important; border: 0 !important; }
.skip-link { transition: transform 0.3s; z-index: 9999; }
`;
document.head?.appendChild(style);

if (typeof module !== 'undefined' && module.exports) {
    module.exports = EcommerceApp;
}
