define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'underscore'
], function (Component, customerData, _) {
    'use strict';

    var wishlistReloaded = false;

    return Component.extend({
        /** @inheritdoc */
        initialize: function () {
            this._super();

            this.wishlist = customerData.get('advanced-wishlist');
            this.company = customerData.get('company');

            // Mettre à jour l'affichage manuellement puisque le binding KO ne marche pas
            this.updateDisplay();

            // Surveiller les changements et mettre à jour
            var self = this;
            this.wishlist.subscribe(function() {
                self.updateDisplay();
            });

            // Logique de rechargement
            if (!wishlistReloaded &&
                !_.isEmpty(this.wishlist()) &&
                _.indexOf(customerData.getExpiredSectionNames(), 'advanced-wishlist') === -1 &&
                window.checkout &&
                window.checkout.storeId &&
                (window.checkout.storeId !== this.wishlist().storeId || this.company().is_enabled)
            ) {
                console.log('Reloading wishlist...');
                customerData.invalidate(['advanced-wishlist']);
                customerData.reload(['advanced-wishlist'], false).done(function() {
                    wishlistReloaded = true;
                    self.updateDisplay();
                });
            }
        },

        /**
         * Mettre à jour l'affichage manuellement
         */
        updateDisplay: function() {
            var count = this.getWishlistCount();
            var counterElement = document.querySelector('#advanced-wishlist-menu-item .counter.qty');

            if (counterElement) {
                counterElement.textContent = count;
                counterElement.style.display = count > 0 ? 'inline' : 'none';
            }
        },

        /**
         * Obtenir le compteur
         */
        getWishlistCount: function() {
            var data = this.wishlist();
            return data ? parseInt(data.wishlist_count || data.counter || 0, 10) : 0;
        }
    });
});
