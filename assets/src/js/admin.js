/**
 * Atlas Returns Admin JavaScript
 *
 * @package AtlasReturns
 */

(function($) {
    'use strict';

    // DOM Elements
    const elements = {
        form: $('#atlr-return-form'),
        orderIdInput: $('#atlr_order_id'),
        reasonSelect: $('#atlr_reason'),
        productsToReplaceInput: $('#atlr_products_to_replace'),
        newProductsInput: $('#atlr_new_products'),
        submitButton: $('#atlr_submit'),
        previewSection: $('#atlr-preview-section'),
        previewDetails: $('#atlr-preview-details'),
        calculationDetails: $('#atlr-calculation-details'),
        loading: $('#atlr-loading'),
        spinner: $('.atlr-spinner'),
        reasonList: $('.atlr-reason-list li'),
    };

    // State
    let isCalculating = false;
    let calculateTimeout = null;

    /**
     * Initialize the admin module.
     */
    function init() {
        bindEvents();
        showReasonInfo();
    }

    /**
     * Bind event handlers.
     */
    function bindEvents() {
        elements.orderIdInput.on('blur', handleOrderIdBlur);
        elements.orderIdInput.on('input', clearForm);
        elements.reasonSelect.on('change', handleReasonChange);
        elements.productsToReplaceInput.on('input', debouncedCalculate);
        elements.newProductsInput.on('input', debouncedCalculate);
        elements.form.on('submit', handleFormSubmit);
    }

    /**
     * Handle order ID blur event.
     */
    function handleOrderIdBlur() {
        const orderId = elements.orderIdInput.val().trim();

        if (!orderId) {
            return;
        }

        previewOrder(orderId);
    }

    /**
     * Preview order via AJAX.
     *
     * @param {string} orderId Order ID or phone number.
     */
    function previewOrder(orderId) {
        showLoading(true);

        $.ajax({
            url: atlrAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'atlr_preview_order',
                nonce: atlrAdmin.nonce,
                order_id: orderId,
            },
            success: function(response) {
                if (response.success) {
                    elements.previewDetails.html(response.data);
                    elements.previewSection.show();
                } else {
                    showError(response.data);
                }
            },
            error: function(xhr, status, error) {
                showError(atlrAdmin.i18n.error + ' ' + error);
            },
            complete: function() {
                showLoading(false);
            },
        });
    }

    /**
     * Handle reason change event.
     */
    function handleReasonChange() {
        showReasonInfo();
        triggerCalculate();
    }

    /**
     * Show reason info based on selected reason.
     */
    function showReasonInfo() {
        const reason = elements.reasonSelect.val();

        elements.reasonList.hide();

        if (reason) {
            elements.reasonList.filter('[data-reason="' + reason + '"]').show();
        }
    }

    /**
     * Clear form fields.
     */
    function clearForm() {
        elements.productsToReplaceInput.val('');
        elements.newProductsInput.val('');
        elements.previewDetails.html('');
        elements.calculationDetails.html('');
        elements.previewSection.hide();
    }

    /**
     * Debounced calculate function.
     */
    function debouncedCalculate() {
        if (calculateTimeout) {
            clearTimeout(calculateTimeout);
        }

        calculateTimeout = setTimeout(function() {
            triggerCalculate();
        }, 500);
    }

    /**
     * Trigger calculation.
     */
    function triggerCalculate() {
        const reason = elements.reasonSelect.val();

        if (!reason) {
            return;
        }

        calculateReturn(false);
    }

    /**
     * Calculate return via AJAX.
     *
     * @param {boolean} validate Whether to validate.
     */
    function calculateReturn(validate) {
        if (isCalculating) {
            return;
        }

        const orderId = elements.orderIdInput.val().trim();
        const reason = elements.reasonSelect.val();
        const productsToReplace = elements.productsToReplaceInput.val().trim();
        const newProducts = elements.newProductsInput.val().trim();

        if (!orderId || !reason) {
            return;
        }

        isCalculating = true;
        elements.spinner.addClass('active');

        $.ajax({
            url: atlrAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'atlr_calculate_return',
                nonce: atlrAdmin.nonce,
                order_id: orderId,
                reason: reason,
                products_to_replace: productsToReplace,
                new_products: newProducts,
                validate: validate,
            },
            success: function(response) {
                if (response.success) {
                    elements.calculationDetails.html(response.data);
                } else if (validate) {
                    showError(response.data);
                }
            },
            error: function(xhr, status, error) {
                if (validate) {
                    showError(atlrAdmin.i18n.error + ' ' + error);
                }
            },
            complete: function() {
                isCalculating = false;
                elements.spinner.removeClass('active');
            },
        });
    }

    /**
     * Handle form submission.
     *
     * @param {Event} e Submit event.
     */
    function handleFormSubmit(e) {
        e.preventDefault();

        const orderId = elements.orderIdInput.val().trim();
        const reason = elements.reasonSelect.val();
        const productsToReplace = elements.productsToReplaceInput.val().trim();
        const newProducts = elements.newProductsInput.val().trim();

        // Validation
        if (!orderId) {
            showError(atlrAdmin.i18n.error);
            elements.orderIdInput.focus();
            return;
        }

        if (!reason) {
            showError(atlrAdmin.i18n.selectReason);
            elements.reasonSelect.focus();
            return;
        }

        if (!productsToReplace) {
            showError(atlrAdmin.i18n.enterProducts);
            elements.productsToReplaceInput.focus();
            return;
        }

        if (!newProducts) {
            showError(atlrAdmin.i18n.enterNewProducts);
            elements.newProductsInput.focus();
            return;
        }

        // Confirm submission
        if (!confirm(atlrAdmin.i18n.confirmSubmit)) {
            return;
        }

        createReturn();
    }

    /**
     * Create return order via AJAX.
     */
    function createReturn() {
        const orderId = elements.orderIdInput.val().trim();
        const reason = elements.reasonSelect.val();
        const productsToReplace = elements.productsToReplaceInput.val().trim();
        const newProducts = elements.newProductsInput.val().trim();
        const createCoupon = $('#atlr_create_coupon').is(':checked');

        showLoading(true);
        disableForm(true);

        $.ajax({
            url: atlrAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'atlr_create_return',
                nonce: atlrAdmin.nonce,
                order_id: orderId,
                reason: reason,
                products_to_replace: productsToReplace,
                new_products: newProducts,
                create_coupon: createCoupon,
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data);
                    resetForm();
                } else {
                    showError(response.data);
                }
            },
            error: function(xhr, status, error) {
                showError(atlrAdmin.i18n.error + ' ' + error);
            },
            complete: function() {
                showLoading(false);
                disableForm(false);
            },
        });
    }

    /**
     * Show loading overlay.
     *
     * @param {boolean} show Whether to show loading.
     */
    function showLoading(show) {
        if (show) {
            elements.loading.show();
        } else {
            elements.loading.hide();
        }
    }

    /**
     * Disable/enable form.
     *
     * @param {boolean} disabled Whether to disable.
     */
    function disableForm(disabled) {
        elements.submitButton.prop('disabled', disabled);
        elements.orderIdInput.prop('disabled', disabled);
        elements.reasonSelect.prop('disabled', disabled);
        elements.productsToReplaceInput.prop('disabled', disabled);
        elements.newProductsInput.prop('disabled', disabled);
    }

    /**
     * Reset form to initial state.
     */
    function resetForm() {
        elements.form[0].reset();
        elements.previewDetails.html('');
        elements.calculationDetails.html('');
        elements.previewSection.hide();
        showReasonInfo();
    }

    /**
     * Show error message.
     *
     * @param {string} message Error message.
     */
    function showError(message) {
        alert(message);
    }

    // Initialize on document ready
    $(document).ready(init);

})(jQuery);
