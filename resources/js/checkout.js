/**
 * Checkout Page JavaScript
 * 
 * Features:
 * - Payment method selection with visual feedback
 * - Section toggling (COD delivery, KBZ Pay instructions)
 * - Lazy loading of saved cards via AJAX
 */

class CheckoutManager {
    constructor() {
        this.form = document.getElementById('checkout-form');
        this.paymentError = document.getElementById('payment-error');
        this.errorMessage = document.getElementById('payment-error-message');
        this.payButton = document.getElementById('pay-button');
        this.payButtonText = document.getElementById('pay-button-text');
        this.payButtonLoading = document.getElementById('pay-button-loading');
        
        // Section elements
        this.codSection = document.getElementById('cod-section');
        this.kbzSection = document.getElementById('kbz-section');
        this.saveCardSection = document.getElementById('save-card-section');
        
        // Saved cards state
        this.savedCardsLoaded = false;
        this.savedCardsData = [];
        
        this.init();
    }
    
    init() {
        if (!this.form) {
            console.warn('Checkout form not found');
            return;
        }
        
        this.setupEventListeners();
        this.updatePaymentSections();
    }
    
    setupEventListeners() {
        // Payment method radio buttons
        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', () => this.handlePaymentMethodChange());
        });
        
        // Form submission
        this.form.addEventListener('submit', (e) => this.handleFormSubmit(e));
    }
    
    handlePaymentMethodChange() {
        this.updatePaymentSections();
        this.hideError();
        
        // Lazy load saved cards when Stripe is selected
        const selected = document.querySelector('input[name="payment_method"]:checked');
        if (selected && selected.value === 'stripe' && !this.savedCardsLoaded) {
            this.loadSavedCards();
        }
    }
    
    async loadSavedCards() {
        if (this.savedCardsLoaded) return;
        
        try {
            const response = await fetch('/checkout/saved-cards');
            const data = await response.json();
            
            this.savedCardsLoaded = true;
            this.savedCardsData = data.cards || [];
            
            // Update the UI if saved cards section exists
            if (this.saveCardSection && this.savedCardsData.length > 0) {
                this.updateSavedCardsUI();
            }
        } catch (error) {
            console.warn('Failed to load saved cards:', error);
            this.savedCardsLoaded = true; // Don't retry
        }
    }
    
    updateSavedCardsUI() {
        const container = document.getElementById('saved-cards-container');
        const list = document.getElementById('saved-cards-list');
        
        if (!container || !list || this.savedCardsData.length === 0) {
            if (container) container.classList.add('hidden');
            return;
        }
        
        // Show container
        container.classList.remove('hidden');
        
        // Build cards HTML
        list.innerHTML = this.savedCardsData.map((card, index) => `
            <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition-all saved-card-option
                ${index === 0 ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-emerald-300 dark:hover:border-emerald-600'}">
                <input type="radio" name="saved_card" value="saved_${card.id}" class="w-4 h-4 text-emerald-600" ${index === 0 ? 'checked' : ''}>
                <div class="flex items-center gap-3 flex-1">
                    <div class="w-10 h-6 bg-gradient-to-r from-gray-700 to-gray-900 rounded flex items-center justify-center">
                        <span class="text-white text-xs font-bold">${(card.brand || 'card').toUpperCase().substring(0, 4)}</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">**** ${card.last4 || '****'}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Expires ${card.exp_month || '**'}/${card.exp_year || '****'}</p>
                    </div>
                </div>
                ${index === 0 ? '<span class="ml-auto text-emerald-600 dark:text-emerald-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></span>' : ''}
            </label>
        `).join('');
        
        // Add click handlers
        list.querySelectorAll('.saved-card-option').forEach(option => {
            option.addEventListener('click', () => {
                list.querySelectorAll('.saved-card-option').forEach(opt => {
                    opt.classList.remove('border-emerald-500', 'bg-emerald-50', 'dark:bg-emerald-900/20');
                    opt.classList.add('border-gray-200', 'dark:border-gray-700');
                    const checkmark = opt.querySelector('.ml-auto');
                    if (checkmark) checkmark.remove();
                });
                
                option.classList.remove('border-gray-200', 'dark:border-gray-700');
                option.classList.add('border-emerald-500', 'bg-emerald-50', 'dark:bg-emerald-900/20');
                
                const checkmark = document.createElement('span');
                checkmark.className = 'ml-auto text-emerald-600 dark:text-emerald-400';
                checkmark.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></span>';
                option.appendChild(checkmark);
            });
        });
    }
    
    updatePaymentSections() {
        const selected = document.querySelector('input[name="payment_method"]:checked');
        if (!selected) return;
        
        const paymentValue = selected.value;
        
        // Update visual state
        this.updatePaymentOptionStyles();
        
        // Show/hide sections
        this.hideAllSections();
        
        switch (paymentValue) {
            case 'cod':
                if (this.codSection) this.codSection.classList.remove('hidden');
                break;
            case 'kbz_pay':
                if (this.kbzSection) this.kbzSection.classList.remove('hidden');
                break;
            case 'stripe':
                if (this.saveCardSection) this.saveCardSection.classList.remove('hidden');
                // Trigger saved cards loading if not already loaded
                if (!this.savedCardsLoaded) {
                    this.loadSavedCards();
                }
                break;
        }
    }
    
    updatePaymentOptionStyles() {
        document.querySelectorAll('.payment-option-label').forEach(label => {
            label.classList.remove('border-emerald-500', 'bg-emerald-50', 'dark:border-emerald-400', 'dark:bg-emerald-900/20', 'ring-1', 'ring-emerald-500', 'dark:ring-emerald-400');
            label.classList.add('border-gray-200', 'dark:border-gray-700', 'bg-white', 'dark:bg-gray-800');
            
            const textSpan = label.querySelector('span.text-sm');
            if (textSpan) {
                textSpan.classList.remove('text-emerald-700', 'dark:text-emerald-300', 'font-semibold');
                textSpan.classList.add('text-gray-700', 'dark:text-gray-200');
            }
            
            const iconDiv = label.querySelector('.transition-colors');
            if (iconDiv) {
                iconDiv.classList.remove('text-emerald-600', 'dark:text-emerald-400');
                iconDiv.classList.add('text-gray-400', 'dark:text-gray-500');
            }
            
            const checkmark = label.querySelector('.ml-auto');
            if (checkmark) {
                checkmark.remove();
            }
        });
        
        const selected = document.querySelector('input[name="payment_method"]:checked');
        if (selected && selected.closest('.payment-option-label')) {
            const label = selected.closest('.payment-option-label');
            label.classList.add('border-emerald-500', 'dark:border-emerald-400', 'bg-emerald-50', 'dark:bg-emerald-900/20', 'ring-1', 'ring-emerald-500', 'dark:ring-emerald-400');
            label.classList.remove('border-gray-200', 'dark:border-gray-700', 'bg-white', 'dark:bg-gray-800');
            
            const textSpan = label.querySelector('span.text-sm');
            if (textSpan) {
                textSpan.classList.add('text-emerald-700', 'dark:text-emerald-300', 'font-semibold');
                textSpan.classList.remove('text-gray-700', 'dark:text-gray-200');
            }
            
            const iconDiv = label.querySelector('.transition-colors');
            if (iconDiv) {
                iconDiv.classList.add('text-emerald-600', 'dark:text-emerald-400');
                iconDiv.classList.remove('text-gray-400', 'dark:text-gray-500');
            }
            
            if (!label.querySelector('.ml-auto')) {
                const checkmark = document.createElement('span');
                checkmark.className = 'ml-auto text-emerald-600 dark:text-emerald-400';
                checkmark.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
                label.querySelector('.flex.items-center').appendChild(checkmark);
            }
        }
    }
    
    hideAllSections() {
        if (this.codSection) this.codSection.classList.add('hidden');
        if (this.kbzSection) this.kbzSection.classList.add('hidden');
        if (this.saveCardSection) this.saveCardSection.classList.add('hidden');
        // Don't hide saved cards container - it's shown separately in the payment options section
    }
    
    async handleFormSubmit(e) {
        e.preventDefault();
        
        const selected = document.querySelector('input[name="payment_method"]:checked');
        if (!selected) {
            this.showError('Please select a payment method');
            return;
        }
        
        // Show loading state
        this.setLoading(true);
        this.hideError();
        
        // For COD and KBZ Pay - submit form normally
        this.form.submit();
    }
    
    showError(message) {
        if (this.paymentError) {
            this.paymentError.classList.remove('hidden');
            if (this.errorMessage) {
                this.errorMessage.textContent = message;
            }
        }
    }
    
    hideError() {
        if (this.paymentError) {
            this.paymentError.classList.add('hidden');
        }
    }
    
    setLoading(isLoading) {
        if (this.payButton) {
            this.payButton.disabled = isLoading;
        }
        if (this.payButtonText) {
            this.payButtonText.classList.toggle('hidden', isLoading);
        }
        if (this.payButtonLoading) {
            this.payButtonLoading.classList.toggle('hidden', !isLoading);
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.checkoutManager = new CheckoutManager();
});
