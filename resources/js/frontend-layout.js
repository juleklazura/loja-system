'use strict';

window.EcommerceApp = window.EcommerceApp || {};

EcommerceApp.UtilitiesService = (function() {
    const debounceFunction = (fn, delay) => {
        let timeoutId;
        return (...args) => {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => fn.apply(this, args), delay);
        };
    };

    const throttleFunction = (fn, limit) => {
        let inThrottle;
        return (...args) => {
            if (!inThrottle) {
                fn.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    };

    const validateEmailFormat = email => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

    const formatCurrencyDisplay = (value, currency = 'BRL') => 
        new Intl.NumberFormat('pt-BR', { style: 'currency', currency }).format(value);

    const logApplicationEvent = (level, message, data = {}) => {
        if (console?.log) {
            const timestamp = new Date().toISOString();
            const methods = { error: 'error', warn: 'warn', info: 'log' };
            console[methods[level] || 'log'](`[${timestamp}] ${level.toUpperCase()}: ${message}`, data);
        }
    };

    return {
        debounce: debounceFunction,
        throttle: throttleFunction,
        validateEmail: validateEmailFormat,
        formatCurrency: formatCurrencyDisplay,
        log: logApplicationEvent
    };
})();
                    break;
                default:
                    console.log(`[${timestamp}] INFO: ${eventMessage}`, eventData);
            }
        }
    }

    // Public API
    return {
        debounce: debounceFunction,
        throttle: throttleFunction,
        validateEmail: validateEmailFormat,
        formatCurrency: formatCurrencyDisplay,
        log: logApplicationEvent
    };
})();

// ========================================
// MODULE: COUNTER MANAGEMENT SERVICE
// ========================================
EcommerceApp.CounterManagementService = (function() {
    const COUNTER_SELECTORS = {
        CART: '[data-cart-count]',
        WISHLIST: '[data-wishlist-count]'
    };

    /**
     * Updates counter elements by selector
     * @param {string} selectorQuery - CSS selector for counter elements
     * @param {number} itemQuantity - Quantity to display
     * @param {string} counterType - Type of counter for logging
     */
    function updateCounterElementsBySelector(selectorQuery, itemQuantity, counterType) {
        try {
            const counterElements = document.querySelectorAll(selectorQuery);
            
            if (counterElements.length === 0) {
                EcommerceApp.UtilitiesService.log('warn', `No ${counterType} counter elements found`);
                return;
            }

            counterElements.forEach(counterElement => {
                if (counterElement) {
                    counterElement.textContent = itemQuantity || 0;
                    counterElement.style.display = itemQuantity > 0 ? 'flex' : 'none';
                    
                    // Add animation class for visual feedback
                    counterElement.classList.add('counter-updated');
                    setTimeout(() => {
                        counterElement.classList.remove('counter-updated');
                    }, 300);
                }
            });
            
            EcommerceApp.UtilitiesService.log('info', `${counterType} counter updated`, {
                quantity: itemQuantity,
                elementsUpdated: counterElements.length
            });
        } catch (updateError) {
            EcommerceApp.UtilitiesService.log('error', `Failed to update ${counterType} counter`, updateError);
        }
    }

    /**
     * Updates cart counter display
     * @param {number} itemQuantity - Cart items quantity
     */
    function updateCartCounterDisplay(itemQuantity) {
        updateCounterElementsBySelector(COUNTER_SELECTORS.CART, itemQuantity, 'cart');
    }

    /**
     * Updates wishlist counter display
     * @param {number} itemQuantity - Wishlist items quantity
     */
    function updateWishlistCounterDisplay(itemQuantity) {
        updateCounterElementsBySelector(COUNTER_SELECTORS.WISHLIST, itemQuantity, 'wishlist');
    }

    /**
     * Initializes all counters with proper visibility
     */
    function initializeAllCounters() {
        try {
            const cartCounterElement = document.querySelector(COUNTER_SELECTORS.CART);
            const wishlistCounterElement = document.querySelector(COUNTER_SELECTORS.WISHLIST);
            
            if (cartCounterElement) {
                const cartCount = parseInt(cartCounterElement.textContent) || 0;
                if (cartCount === 0) {
                    cartCounterElement.style.display = 'none';
                }
            }
            
            if (wishlistCounterElement) {
                const wishlistCount = parseInt(wishlistCounterElement.textContent) || 0;
                if (wishlistCount === 0) {
                    wishlistCounterElement.style.display = 'none';
                }
            }
            
            EcommerceApp.UtilitiesService.log('info', 'Counters initialized successfully');
        } catch (initializationError) {
            EcommerceApp.UtilitiesService.log('error', 'Failed to initialize counters', initializationError);
        }
    }

    // Public API
    return {
        updateCartCounter: updateCartCounterDisplay,
        updateWishlistCounter: updateWishlistCounterDisplay,
        initializeCounters: initializeAllCounters
    };
})();

// ========================================
// MODULE: NOTIFICATION SYSTEM SERVICE
// ========================================
EcommerceApp.NotificationSystemService = (function() {
    const NOTIFICATION_CONFIG = {
        AUTO_DISMISS_DELAY: 4000,
        MAX_NOTIFICATIONS: 3,
        ANIMATION_DURATION: 300
    };

    const NOTIFICATION_TYPES = {
        SUCCESS: 'success',
        ERROR: 'danger',
        WARNING: 'warning',
        INFO: 'info'
    };

    const ICON_MAPPING = {
        [NOTIFICATION_TYPES.SUCCESS]: 'check-circle',
        [NOTIFICATION_TYPES.ERROR]: 'exclamation-circle',
        [NOTIFICATION_TYPES.WARNING]: 'exclamation-triangle',
        [NOTIFICATION_TYPES.INFO]: 'info-circle'
    };

    /**
     * Creates a notification element
     * @param {string} messageText - Message to display
     * @param {string} messageType - Type of notification
     * @returns {HTMLElement} Notification element
     */
    function createNotificationElement(messageText, messageType) {
        const notificationElement = document.createElement('div');
        const iconName = ICON_MAPPING[messageType] || ICON_MAPPING[NOTIFICATION_TYPES.INFO];
        
        notificationElement.className = `alert alert-${messageType} alert-dismissible fade show notification-floating`;
        notificationElement.setAttribute('role', 'alert');
        notificationElement.setAttribute('aria-live', 'polite');
        
        notificationElement.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-${iconName} me-2" aria-hidden="true"></i>
                <span class="flex-grow-1">${messageText}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar notificação"></button>
            </div>
        `;
        
        return notificationElement;
    }

    /**
     * Removes old notifications if limit exceeded
     */
    function removeExcessNotifications() {
        const existingNotifications = document.querySelectorAll('.notification-floating');
        const excessCount = existingNotifications.length - NOTIFICATION_CONFIG.MAX_NOTIFICATIONS + 1;
        
        if (excessCount > 0) {
            for (let i = 0; i < excessCount; i++) {
                if (existingNotifications[i]) {
                    existingNotifications[i].remove();
                }
            }
        }
    }

    /**
     * Displays a notification to the user
     * @param {string} messageText - Message to display
     * @param {string} messageType - Type of notification
     * @param {number} customDismissDelay - Custom dismiss delay (optional)
     */
    function displayUserNotification(messageText, messageType = NOTIFICATION_TYPES.SUCCESS, customDismissDelay = null) {
        try {
            if (!messageText || typeof messageText !== 'string') {
                throw new Error('Invalid message text provided');
            }

            // Validate message type
            if (!Object.values(NOTIFICATION_TYPES).includes(messageType)) {
                EcommerceApp.UtilitiesService.log('warn', `Invalid notification type: ${messageType}`);
                messageType = NOTIFICATION_TYPES.INFO;
            }

            // Remove excess notifications
            removeExcessNotifications();
            
            // Create and display notification
            const notificationElement = createNotificationElement(messageText, messageType);
            document.body.appendChild(notificationElement);
            
            // Auto-dismiss functionality
            const dismissDelay = customDismissDelay || NOTIFICATION_CONFIG.AUTO_DISMISS_DELAY;
            const dismissTimer = setTimeout(() => {
                if (notificationElement && notificationElement.parentNode) {
                    notificationElement.classList.add('fade-out');
                    setTimeout(() => {
                        if (notificationElement.parentNode) {
                            notificationElement.remove();
                        }
                    }, NOTIFICATION_CONFIG.ANIMATION_DURATION);
                }
            }, dismissDelay);
            
            // Clear timer if manually closed
            const closeButton = notificationElement.querySelector('.btn-close');
            if (closeButton) {
                closeButton.addEventListener('click', () => {
                    clearTimeout(dismissTimer);
                });
            }
            
            EcommerceApp.UtilitiesService.log('info', 'Notification displayed', {
                type: messageType,
                message: messageText
            });
            
        } catch (notificationError) {
            EcommerceApp.UtilitiesService.log('error', 'Failed to display notification', notificationError);
        }
    }

    // Public API
    return {
        show: displayUserNotification,
        types: NOTIFICATION_TYPES
    };
})();

// ========================================
// MODULE: FORM MANAGEMENT SERVICE
// ========================================
EcommerceApp.FormManagementService = (function() {
    /**
     * Handles newsletter subscription
     * @param {HTMLFormElement} formElement - Newsletter form element
     */
    function handleNewsletterSubmission(formElement) {
        if (!formElement) return;

        const emailInput = formElement.querySelector('input[type="email"]');
        if (!emailInput) return;

        const userEmail = emailInput.value.trim();
        
        if (!userEmail) {
            EcommerceApp.NotificationSystemService.show(
                'Por favor, digite seu e-mail.',
                EcommerceApp.NotificationSystemService.types.WARNING
            );
            return;
        }

        if (!EcommerceApp.UtilitiesService.validateEmail(userEmail)) {
            EcommerceApp.NotificationSystemService.show(
                'Por favor, digite um e-mail válido.',
                EcommerceApp.NotificationSystemService.types.ERROR
            );
            return;
        }

        // Simulate subscription (replace with actual API call)
        setTimeout(() => {
            EcommerceApp.NotificationSystemService.show(
                'Obrigado por se inscrever! Em breve você receberá nossas novidades.',
                EcommerceApp.NotificationSystemService.types.SUCCESS
            );
            emailInput.value = '';
            
            EcommerceApp.UtilitiesService.log('info', 'Newsletter subscription', {
                email: userEmail
            });
        }, 500);
    }

    /**
     * Handles search form validation
     * @param {HTMLFormElement} searchForm - Search form element
     */
    function handleSearchFormValidation(searchForm) {
        if (!searchForm) return;

        const searchInput = searchForm.querySelector('input[name="search"]');
        if (!searchInput) return;

        const debouncedValidation = EcommerceApp.UtilitiesService.debounce((event) => {
            const searchValue = event.target.value.trim();
            
            if (searchValue.length > 0 && searchValue.length < 2) {
                EcommerceApp.NotificationSystemService.show(
                    'Digite pelo menos 2 caracteres para buscar.',
                    EcommerceApp.NotificationSystemService.types.INFO
                );
            }
        }, 500);

        searchInput.addEventListener('input', debouncedValidation);
        
        searchForm.addEventListener('submit', (event) => {
            const searchValue = searchInput.value.trim();
            
            if (!searchValue) {
                event.preventDefault();
                EcommerceApp.NotificationSystemService.show(
                    'Digite algo para buscar produtos.',
                    EcommerceApp.NotificationSystemService.types.WARNING
                );
            }
        });
    }

    /**
     * Initializes all form handlers
     */
    function initializeAllFormHandlers() {
        try {
            // Newsletter form
            const newsletterForm = document.getElementById('newsletterSubscriptionForm');
            if (newsletterForm) {
                newsletterForm.addEventListener('submit', (event) => {
                    event.preventDefault();
                    handleNewsletterSubmission(newsletterForm);
                });
            }

            // Search form
            const searchForms = document.querySelectorAll('form[role="search"]');
            searchForms.forEach(searchForm => {
                handleSearchFormValidation(searchForm);
            });

            EcommerceApp.UtilitiesService.log('info', 'Form handlers initialized');
        } catch (initializationError) {
            EcommerceApp.UtilitiesService.log('error', 'Failed to initialize form handlers', initializationError);
        }
    }

    // Public API
    return {
        initializeForms: initializeAllFormHandlers,
        handleNewsletter: handleNewsletterSubmission,
        handleSearch: handleSearchFormValidation
    };
})();

// ========================================
// MODULE: ACCESSIBILITY SERVICE
// ========================================
EcommerceApp.AccessibilityService = (function() {
    /**
     * Enhances keyboard navigation
     */
    function enhanceKeyboardNavigation() {
        document.addEventListener('keydown', (event) => {
            // Skip to main content with Alt + S
            if (event.altKey && event.key === 's') {
                event.preventDefault();
                const mainContent = document.querySelector('main');
                if (mainContent) {
                    mainContent.focus();
                    mainContent.scrollIntoView({ behavior: 'smooth' });
                }
            }
            
            // Focus search with Ctrl + K
            if (event.ctrlKey && event.key === 'k') {
                event.preventDefault();
                const searchInput = document.querySelector('input[name="search"]');
                if (searchInput) {
                    searchInput.focus();
                }
            }
        });
    }

    /**
     * Announces dynamic content changes to screen readers
     * @param {string} announcementMessage - Message to announce
     */
    function announceToScreenReader(announcementMessage) {
        const announcement = document.createElement('div');
        announcement.setAttribute('aria-live', 'polite');
        announcement.setAttribute('aria-atomic', 'true');
        announcement.className = 'sr-only';
        announcement.textContent = announcementMessage;
        
        document.body.appendChild(announcement);
        
        setTimeout(() => {
            document.body.removeChild(announcement);
        }, 1000);
    }

    /**
     * Initializes accessibility enhancements
     */
    function initializeAccessibilityFeatures() {
        try {
            enhanceKeyboardNavigation();
            
            // Add skip link if not present
            if (!document.querySelector('.skip-link')) {
                const skipLink = document.createElement('a');
                skipLink.href = '#main';
                skipLink.className = 'skip-link position-absolute top-0 start-0 bg-primary text-white p-2 text-decoration-none';
                skipLink.style.transform = 'translateY(-100%)';
                skipLink.style.transition = 'transform 0.3s';
                skipLink.textContent = 'Pular para o conteúdo principal';
                
                skipLink.addEventListener('focus', () => {
                    skipLink.style.transform = 'translateY(0)';
                });
                
                skipLink.addEventListener('blur', () => {
                    skipLink.style.transform = 'translateY(-100%)';
                });
                
                document.body.insertBefore(skipLink, document.body.firstChild);
            }
            
            EcommerceApp.UtilitiesService.log('info', 'Accessibility features initialized');
        } catch (accessibilityError) {
            EcommerceApp.UtilitiesService.log('error', 'Failed to initialize accessibility features', accessibilityError);
        }
    }

    // Public API
    return {
        initialize: initializeAccessibilityFeatures,
        announce: announceToScreenReader
    };
})();

// ========================================
// MODULE: APPLICATION CONTROLLER
// ========================================
EcommerceApp.ApplicationController = (function() {
    let isInitialized = false;

    /**
     * Initializes the entire application
     */
    function initializeApplication() {
        if (isInitialized) {
            EcommerceApp.UtilitiesService.log('warn', 'Application already initialized');
            return;
        }

        try {
            EcommerceApp.UtilitiesService.log('info', 'Initializing e-commerce application...');
            
            // Initialize core services
            EcommerceApp.CounterManagementService.initializeCounters();
            EcommerceApp.FormManagementService.initializeForms();
            EcommerceApp.AccessibilityService.initialize();
            
            isInitialized = true;
            EcommerceApp.UtilitiesService.log('info', 'E-commerce application initialized successfully');
            
            // Announce to screen readers
            EcommerceApp.AccessibilityService.announce('Aplicação carregada com sucesso');
            
        } catch (initializationError) {
            EcommerceApp.UtilitiesService.log('error', 'Failed to initialize application', initializationError);
            
            EcommerceApp.NotificationSystemService.show(
                'Ocorreu um erro ao carregar a aplicação. Recarregue a página.',
                EcommerceApp.NotificationSystemService.types.ERROR
            );
        }
    }

    /**
     * Gets application status
     * @returns {Object} Application status information
     */
    function getApplicationStatus() {
        return {
            initialized: isInitialized,
            timestamp: new Date().toISOString(),
            services: {
                utilities: !!EcommerceApp.UtilitiesService,
                counter: !!EcommerceApp.CounterManagementService,
                notification: !!EcommerceApp.NotificationSystemService,
                form: !!EcommerceApp.FormManagementService,
                accessibility: !!EcommerceApp.AccessibilityService
            }
        };
    }

    // Public API
    return {
        init: initializeApplication,
        status: getApplicationStatus
    };
})();

// ========================================
// BACKWARD COMPATIBILITY LAYER
// ========================================
(function() {
    // Legacy global functions for backward compatibility
    window.updateCartCounter = function(count) {
        EcommerceApp.CounterManagementService.updateCartCounter(count);
    };
    
    window.updateWishlistCounter = function(count) {
        EcommerceApp.CounterManagementService.updateWishlistCounter(count);
    };
    
    window.showMessage = function(message, type = 'success') {
        EcommerceApp.NotificationSystemService.show(message, type);
    };
    
    window.showCartMessage = function(message, success = true) {
        const messageType = success ? 
            EcommerceApp.NotificationSystemService.types.SUCCESS : 
            EcommerceApp.NotificationSystemService.types.ERROR;
        EcommerceApp.NotificationSystemService.show(message, messageType);
    };
})();

// ========================================
// APPLICATION BOOTSTRAP
// ========================================
(function() {
    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', EcommerceApp.ApplicationController.init);
    } else {
        // DOM is already ready
        EcommerceApp.ApplicationController.init();
    }
})();

// Add CSS for counter animation
if (document.head) {
    const style = document.createElement('style');
    style.textContent = `
        .counter-updated {
            animation: counterBounce 0.3s ease-out;
        }
        
        @keyframes counterBounce {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        
        .fade-out {
            opacity: 0 !important;
            transform: translateX(100%) !important;
        }
        
        .sr-only {
            position: absolute !important;
            width: 1px !important;
            height: 1px !important;
            padding: 0 !important;
            margin: -1px !important;
            overflow: hidden !important;
            clip: rect(0, 0, 0, 0) !important;
            white-space: nowrap !important;
            border: 0 !important;
        }
    `;
    document.head.appendChild(style);
}

// Export for potential module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = EcommerceApp;
}
