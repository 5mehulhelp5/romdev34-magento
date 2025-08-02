define([
    'jquery',
    'mage/url',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/confirm',
    'mage/translate'
], function ($, urlBuilder, alert, confirm, $t) {
    'use strict';

    return function (config, element) {
        var $element = $(element);


        // Remove from wishlist functionality
        $element.on('click', '.remove-item', function (e) {
            e.preventDefault();
            var $button = $(this);
            var itemId = $button.data('item-id');
            var productId = $button.data('product-id');
            var wishlistId = $button.data('wishlist-id');
            var removeUrl = $button.data('remove-url');

            confirm({
                title: $t('Remove Item'),
                content: $t('Are you sure you want to remove this item from your wishlist?'),
                actions: {
                    confirm: function () {
                        $button.prop('disabled', true).text($t('Removing...'));

                        $.ajax({
                            url: removeUrl,
                            type: 'POST',
                            data: {
                                wishlist_id: wishlistId,
                                product_id: productId,
                                item_id : itemId
                            },
                            success: function (response) {
                                if (response.success) {
                                    $button.closest('.wishlist-item').fadeOut(300, function () {
                                        $(this).remove();
                                        
                                        // Check if wishlist is empty
                                        if ($element.find('.wishlist-item').length === 0) {
                                            location.reload();
                                        }
                                    });
                                } else {
                                    alert({
                                        title: $t('Error'),
                                        content: response.message || $t('Could not remove item from wishlist.')
                                    });
                                    $button.prop('disabled', false).text($t('Remove'));
                                }
                            },
                            error: function () {
                                alert({
                                    title: $t('Error'),
                                    content: $t('An error occurred while removing the item.')
                                });
                                $button.prop('disabled', false).text($t('Remove'));
                            }
                        });
                    }
                }
            });
        });

        // Share wishlist functionality
        $element.on('submit', '.share-wishlist-form', function (e) {
            e.preventDefault();
            var $form = $(this);
            var shareUrl = $form.data('share-url');
            var $submitButton = $form.find('button[type="submit"]');

            var formData = {
                wishlist_id: $form.find('input[name="wishlist_id"]').val(),
                email: $form.find('input[name="email"]').val(),
                message: $form.find('textarea[name="message"]').val()
            };

            // Basic validation
            if (!formData.email) {
                alert({
                    title: $t('Error'),
                    content: $t('Please enter an email address.')
                });
                return;
            }

            // Email format validation
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(formData.email)) {
                alert({
                    title: $t('Error'),
                    content: $t('Please enter a valid email address.')
                });
                return;
            }

            $submitButton.prop('disabled', true).text($t('Sharing...'));

            $.ajax({
                url: shareUrl,
                type: 'POST',
                data: formData,
                success: function (response) {
                    if (response.success) {
                        alert({
                            title: $t('Success'),
                            content: response.message || $t('Wishlist has been shared successfully.')
                        });
                        $form[0].reset();
                    } else {
                        alert({
                            title: $t('Error'),
                            content: response.message || $t('Could not share wishlist.')
                        });
                    }
                },
                error: function () {
                    alert({
                        title: $t('Error'),
                        content: $t('An error occurred while sharing the wishlist.')
                    });
                },
                complete: function () {
                    $submitButton.prop('disabled', false).text($t('Share Wishlist'));
                }
            });
        });

        // Add price alert functionality (if needed)
        $element.on('click', '.setup-price-alert', function (e) {
            e.preventDefault();
            // Implementation for price alert setup
            // This would open a modal or form to set target price
        });
    };
});