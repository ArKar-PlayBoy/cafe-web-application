/**
 * Checkout Page JavaScript
 *
 * Features:
 * - Payment method selection with visual feedback
 * - Section toggling (COD delivery, KBZ Pay instructions)
 * - Lazy loading of saved cards via AJAX
 * - Save-card toggle for new Stripe cards only
 */

class CheckoutManager {
    constructor() {
        this.form = document.getElementById('checkout-form');
        this.paymentError = document.getElementById('payment-error');
        this.errorMessage = document.getElementById('payment-error-message');
        this.payButton = document.getElementById('pay-button');
        this.payButtonText = document.getElementById('pay-button-text');
        this.payButtonLoading = document.getElementById('pay-button-loading');

        this.paymentMethodInput = document.getElementById('payment_method_input');
        this.saveCardSection = document.getElementById('save-card-section');
        this.saveCardCheckbox = document.getElementById('save_card');
        this.savedCardsContainer = document.getElementById('saved-cards-container');
        this.savedCardsList = document.getElementById('saved-cards-list');

        this.codSection = document.getElementById('cod-section');
        this.kbzSection = document.getElementById('kbz-section');

        this.savedCardsLoaded = false;
        this.savedCardsData = [];

        this.init();
    }

    init() {
        if (!this.form || !this.paymentMethodInput) {
            return;
        }

        this.ensurePrimaryPaymentSelected();
        this.syncInitialPaymentMethod();
        this.setupEventListeners();
        this.updatePaymentSections();
    }

    setupEventListeners() {
        document.querySelectorAll('input[name="payment_method_radio"]').forEach((radio) => {
            radio.addEventListener('change', () => this.handlePaymentMethodChange());
        });

        if (this.saveCardCheckbox) {
            this.saveCardCheckbox.addEventListener('change', () => this.hideError());
        }

        this.form.addEventListener('submit', (e) => this.handleFormSubmit(e));
    }

    ensurePrimaryPaymentSelected() {
        let selected = this.getSelectedPrimaryMethod();

        if (selected) {
            return;
        }

        const hiddenMethod = this.paymentMethodInput.value || 'stripe';
        let fallbackPrimary = hiddenMethod;

        if (hiddenMethod.startsWith('saved_')) {
            fallbackPrimary = 'stripe';
        }

        const radio = document.querySelector(`input[name="payment_method_radio"][value="${fallbackPrimary}"]`)
            || document.querySelector('input[name="payment_method_radio"][value="stripe"]');

        if (radio) {
            radio.checked = true;
        }
    }

    syncInitialPaymentMethod() {
        const hiddenMethod = this.paymentMethodInput.value;
        const selectedPrimary = this.getSelectedPrimaryMethod() || 'stripe';

        if (!hiddenMethod) {
            this.paymentMethodInput.value = selectedPrimary;
            return;
        }

        if (!hiddenMethod.startsWith('saved_')
            && hiddenMethod !== 'stripe'
            && hiddenMethod !== 'cod'
            && hiddenMethod !== 'kbz_pay') {
            this.paymentMethodInput.value = selectedPrimary;
        }
    }

    getSelectedPrimaryMethod() {
        const selected = document.querySelector('input[name="payment_method_radio"]:checked');
        return selected ? selected.value : null;
    }

    handlePaymentMethodChange() {
        const primary = this.getSelectedPrimaryMethod();

        if (!primary) {
            return;
        }

        if (primary !== 'stripe') {
            this.paymentMethodInput.value = primary;
        } else if (!this.paymentMethodInput.value.startsWith('saved_')) {
            this.paymentMethodInput.value = 'stripe';
        }

        this.updatePaymentSections();
        this.hideError();
    }

    async loadSavedCards() {
        if (this.savedCardsLoaded) {
            return;
        }

        try {
            const response = await fetch('/checkout/saved-cards', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error('Failed to load saved cards');
            }

            const data = await response.json();
            this.savedCardsData = Array.isArray(data.cards) ? data.cards : [];
        } catch (error) {
            this.savedCardsData = [];
        } finally {
            this.savedCardsLoaded = true;
            this.updateSavedCardsUI();
        }
    }

    updateSavedCardsUI() {
        if (!this.savedCardsContainer || !this.savedCardsList) {
            return;
        }

        if (this.savedCardsData.length === 0) {
            this.savedCardsContainer.classList.add('hidden');
            if (this.paymentMethodInput.value.startsWith('saved_')) {
                this.paymentMethodInput.value = 'stripe';
            }
            this.updateSaveCardAvailability(this.paymentMethodInput.value);
            return;
        }

        this.savedCardsContainer.classList.remove('hidden');

        let selectedMethod = this.paymentMethodInput.value;
        const validSaved = this.savedCardsData.some((card) => `saved_${card.id}` === selectedMethod);

        if (!(selectedMethod === 'stripe' || validSaved)) {
            selectedMethod = 'stripe';
            this.paymentMethodInput.value = 'stripe';
        }

        const newCardOption = `
            <label class="saved-card-option flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition-all" data-value="stripe">
                <input type="radio" name="saved_or_new_card" value="stripe" class="w-4 h-4 text-emerald-600" ${selectedMethod === 'stripe' ? 'checked' : ''}>
                <div class="flex items-center gap-3 flex-1">
                    <div class="w-10 h-6 bg-gradient-to-r from-emerald-700 to-emerald-900 rounded flex items-center justify-center">
                        <span class="text-white text-xs font-bold">NEW</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Use a new card</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Enter card details at Stripe checkout</p>
                    </div>
                </div>
            </label>
        `;

        const savedCardOptions = this.savedCardsData.map((card) => {
            const value = `saved_${card.id}`;
            const checked = value === selectedMethod ? 'checked' : '';
            const brand = (card.brand || 'card').toUpperCase().substring(0, 4);
            const last4 = card.last4 || '****';
            const expMonth = card.exp_month || '**';
            const expYear = card.exp_year || '****';

            const label = document.createElement('label');
            label.className = 'saved-card-option flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition-all';
            label.setAttribute('data-value', value);

            const radio = document.createElement('input');
            radio.type = 'radio';
            radio.name = 'saved_or_new_card';
            radio.value = value;
            radio.className = 'w-4 h-4 text-emerald-600';
            if (checked) radio.checked = true;

            const innerDiv = document.createElement('div');
            innerDiv.className = 'flex items-center gap-3 flex-1';

            const brandDiv = document.createElement('div');
            brandDiv.className = 'w-10 h-6 bg-gradient-to-r from-gray-700 to-gray-900 rounded flex items-center justify-center';
            const brandSpan = document.createElement('span');
            brandSpan.className = 'text-white text-xs font-bold';
            brandSpan.textContent = brand;
            brandDiv.appendChild(brandSpan);

            const infoDiv = document.createElement('div');
            const pLast4 = document.createElement('p');
            pLast4.className = 'text-sm font-medium text-gray-900 dark:text-white';
            pLast4.textContent = '**** ' + last4;
            const pExp = document.createElement('p');
            pExp.className = 'text-xs text-gray-500 dark:text-gray-400';
            pExp.textContent = 'Expires ' + expMonth + '/' + expYear;
            infoDiv.appendChild(pLast4);
            infoDiv.appendChild(pExp);

            innerDiv.appendChild(brandDiv);
            innerDiv.appendChild(infoDiv);
            label.appendChild(radio);
            label.appendChild(innerDiv);

            return label;
        });

        this.savedCardsList.innerHTML = '';
        this.savedCardsList.insertAdjacentHTML('beforeend', newCardOption);
        savedCardOptions.forEach((node) => {
            this.savedCardsList.appendChild(node);
        });

        this.savedCardsList.querySelectorAll('input[name="saved_or_new_card"]').forEach((input) => {
            input.addEventListener('change', () => {
                this.selectStripeSubMethod(input.value);
            });
        });

        this.applySavedCardOptionStyles(selectedMethod);
        this.updateSaveCardAvailability(selectedMethod);
    }

    selectStripeSubMethod(value) {
        const primary = this.getSelectedPrimaryMethod();
        if (primary !== 'stripe') {
            const stripeRadio = document.querySelector('input[name="payment_method_radio"][value="stripe"]');
            if (stripeRadio) {
                stripeRadio.checked = true;
            }
        }

        this.paymentMethodInput.value = value;
        this.applySavedCardOptionStyles(value);
        this.updateSaveCardAvailability(value);
        this.hideError();
    }

    applySavedCardOptionStyles(selectedValue) {
        if (!this.savedCardsList) {
            return;
        }

        this.savedCardsList.querySelectorAll('.saved-card-option').forEach((option) => {
            const value = option.getAttribute('data-value');

            option.classList.remove('border-emerald-500', 'bg-emerald-50', 'dark:bg-emerald-900/20');
            option.classList.add('border-gray-200', 'dark:border-gray-700', 'hover:border-emerald-300', 'dark:hover:border-emerald-600');

            const existingCheck = option.querySelector('.saved-check');
            if (existingCheck) {
                existingCheck.remove();
            }

            if (value === selectedValue) {
                option.classList.remove('border-gray-200', 'dark:border-gray-700');
                option.classList.add('border-emerald-500', 'bg-emerald-50', 'dark:bg-emerald-900/20');

                const checkmark = document.createElement('span');
                checkmark.className = 'saved-check ml-auto text-emerald-600 dark:text-emerald-400';
                checkmark.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
                option.appendChild(checkmark);
            }
        });
    }

    updateSaveCardAvailability(paymentMethod) {
        if (!this.saveCardCheckbox || !this.saveCardSection) {
            return;
        }

        const primary = this.getSelectedPrimaryMethod();
        const isStripeFlow = primary === 'stripe';
        const isNewCard = paymentMethod === 'stripe';

        if (!isStripeFlow) {
            this.saveCardCheckbox.checked = false;
            this.saveCardCheckbox.disabled = true;
            this.saveCardSection.classList.add('opacity-60');
            return;
        }

        if (!isNewCard) {
            this.saveCardCheckbox.checked = false;
            this.saveCardCheckbox.disabled = true;
            this.saveCardSection.classList.add('opacity-60');
            return;
        }

        this.saveCardCheckbox.disabled = false;
        this.saveCardSection.classList.remove('opacity-60');
    }

    updatePaymentSections() {
        const primary = this.getSelectedPrimaryMethod();
        if (!primary) {
            return;
        }

        this.updatePaymentOptionStyles();
        this.hideAllSections();

        if (primary === 'cod') {
            if (this.codSection) {
                this.codSection.classList.remove('hidden');
            }
            this.paymentMethodInput.value = 'cod';
            this.updateSaveCardAvailability('cod');
            return;
        }

        if (primary === 'kbz_pay') {
            if (this.kbzSection) {
                this.kbzSection.classList.remove('hidden');
            }
            this.paymentMethodInput.value = 'kbz_pay';
            this.updateSaveCardAvailability('kbz_pay');
            return;
        }

        if (this.saveCardSection) {
            this.saveCardSection.classList.remove('hidden');
        }

        if (!this.savedCardsLoaded) {
            if (!this.paymentMethodInput.value.startsWith('saved_')) {
                this.paymentMethodInput.value = 'stripe';
            }
            this.updateSaveCardAvailability(this.paymentMethodInput.value);
            this.loadSavedCards();
            return;
        }

        if (this.savedCardsData.length === 0) {
            this.paymentMethodInput.value = 'stripe';
            this.updateSaveCardAvailability('stripe');
            if (this.savedCardsContainer) {
                this.savedCardsContainer.classList.add('hidden');
            }
            return;
        }

        this.updateSavedCardsUI();
    }

    updatePaymentOptionStyles() {
        document.querySelectorAll('.payment-option-label').forEach((label) => {
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

        const selected = document.querySelector('input[name="payment_method_radio"]:checked');
        if (!selected || !selected.closest('.payment-option-label')) {
            return;
        }

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

    hideAllSections() {
        if (this.codSection) {
            this.codSection.classList.add('hidden');
        }
        if (this.kbzSection) {
            this.kbzSection.classList.add('hidden');
        }
        if (this.saveCardSection) {
            this.saveCardSection.classList.add('hidden');
        }
        if (this.savedCardsContainer) {
            this.savedCardsContainer.classList.add('hidden');
        }
    }

    handleFormSubmit(e) {
        e.preventDefault();

        const paymentMethod = this.paymentMethodInput ? this.paymentMethodInput.value : null;
        if (!paymentMethod) {
            this.showError('Please select a payment method');
            return;
        }

        const primary = this.getSelectedPrimaryMethod();
        if (primary !== 'stripe' && paymentMethod.startsWith('saved_')) {
            this.paymentMethodInput.value = primary || 'stripe';
        }

        if (this.saveCardCheckbox && this.paymentMethodInput.value !== 'stripe') {
            this.saveCardCheckbox.checked = false;
        }

        this.setLoading(true);
        this.hideError();
        this.form.submit();
    }

    showError(message) {
        if (!this.paymentError || !this.errorMessage) {
            return;
        }

        this.paymentError.classList.remove('hidden');
        this.errorMessage.textContent = message;
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

document.addEventListener('DOMContentLoaded', () => {
    window.checkoutManager = new CheckoutManager();
});
