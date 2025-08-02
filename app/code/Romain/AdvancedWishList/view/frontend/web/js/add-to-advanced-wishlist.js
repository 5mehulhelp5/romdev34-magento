define([
    'jquery',
    'mage/url',
    'Magento_Ui/js/modal/alert',
    'mage/translate'
], function ($, urlBuilder, alert, $t) {
    'use strict';

    return function (config, element) {
        var $element = $(element);
        var $wishlistSelect = $element.find('#wishlist-select');
        var $addButton = $element.find('.add-to-advanced-wishlist');
        var $options = $element.find('.wishlist-options');
        var $priceAlert = $element.find('#wishlist-price-alert');
        var $targetPrice = $element.find('#wishlist-target-price');

        // Enable/disable add button based on wishlist selection
        $wishlistSelect.on('change', function () {
            var selectedWishlist = $(this).val();
            $addButton.prop('disabled', !selectedWishlist);

            if (selectedWishlist) {
                $options.show();
            } else {
                $options.hide();
            }
        });

        // Enable/disable target price input based on price alert checkbox
        $priceAlert.on('change', function () {
            $targetPrice.prop('disabled', !$(this).is(':checked'));
            if (!$(this).is(':checked')) {
                $targetPrice.val('');
            }
        });

        // Add to wishlist functionality
        $addButton.on('click', function (e) {
            e.preventDefault();
            var $button = $(this);
            var productId = $button.data('product-id');
            var addUrl = $button.data('add-url');
            var wishlistId = $wishlistSelect.val();

            if (!wishlistId) {
                alert({
                    title: $t('Error'),
                    content: $t('Please select a wishlist.')
                });
                return;
            }

            // Collect form data
            var formData = {
                wishlist_id: wishlistId,
                product_id: productId,
                qty: $element.find('#wishlist-qty').val() || 1,
                description: $element.find('#wishlist-description').val() || '',
                price_alert: $priceAlert.is(':checked') ? 1 : 0,
                target_price: $priceAlert.is(':checked') ? $targetPrice.val() : ''
            };

            // Validate target price if price alert is enabled
            if (formData.price_alert && (!formData.target_price || formData.target_price <= 0)) {
                alert({
                    title: $t('Error'),
                    content: $t('Please enter a valid target price for the price alert.')
                });
                return;
            }

            $button.prop('disabled', true).text($t('Adding...'));

            $.ajax({
                url: addUrl,
                type: 'POST',
                data: formData,
                success: function (response) {
                    if (response.success) {
                        alert({
                            title: $t('Success'),
                            content: response.message || $t('Product has been added to your wishlist.')
                        });

                        // Disable the option for this wishlist since product is now added
                        $wishlistSelect.find('option[value="' + wishlistId + '"]')
                                      .prop('disabled', true)
                                      .text($wishlistSelect.find('option[value="' + wishlistId + '"]').text().replace(')', ' - Already added)'));

                        // Reset form
                        $wishlistSelect.val('');
                        $element.find('#wishlist-qty').val(1);
                        $element.find('#wishlist-description').val('');
                        $priceAlert.prop('checked', false);
                        $targetPrice.val('').prop('disabled', true);
                        $options.hide();
                        location.reload();

                    } else {
                        alert({
                            title: $t('Error'),
                            content: response.message || $t('Could not add product to wishlist.')
                        });
                    }
                },
                error: function () {
                    alert({
                        title: $t('Error'),
                        content: $t('An error occurred while adding the product to your wishlist.')
                    });
                },
                complete: function () {
                    $button.prop('disabled', false).text($t('Add to Wishlist'));
                }
            });
        });

        // Toggle advanced options
        $element.on('click', '.toggle-options', function (e) {
            e.preventDefault();
            $options.toggle();
        });
    };
});
