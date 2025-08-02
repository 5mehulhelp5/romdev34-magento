define([
    'uiComponent',
    'ko',
    'jquery',
    'Magento_Customer/js/customer-data',
    'mage/url',
    'domReady!'
], function (Component, ko, $, customerData, urlBuilder) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Romain_AdvancedWishList/product/wishlist-container'
        },

        initialize: function () {
            this._super();
            var self = this;
            var productId = $('[data-product-id]').data('product-id');
            console.log(urlBuilder.build('advancedwishlist/block/render'));
            $.ajax({
                url: urlBuilder.build('advancedwishlist/block/render'), // Crée ce contrôleur
                type: 'POST',
                data: { product_id: productId },
                success: function (html) {
                    $('#product-advanced-wishlist-container').html(html);
                }
            });
        }
    });
});
