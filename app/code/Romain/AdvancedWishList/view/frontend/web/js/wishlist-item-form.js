/**
 * JavaScript pour gérer les formulaires des items de wishlist
 * app/code/Romain/AdvancedWishList/view/frontend/web/js/wishlist-item-form.js
 */

define([
    'jquery',
    'mage/translate',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/alert',
    'jquery/ui'
], function ($, $t, confirmation, alert) {
    'use strict';

    return function (config, element) {
        var $element = $(element);

        // Gestion des formulaires de suppression d'items
        $element.on('submit', '.remove-item-form', function (e) {
            e.preventDefault();

            var $form = $(this);
            var $button = $form.find('.remove-item');

            // Confirmation avant suppression
            confirmation({
                title: $t('Remove Item'),
                content: $t('Are you sure you want to remove this item from your wishlist?'),
                actions: {
                    confirm: function () {
                        // Désactiver le bouton et ajouter un état de chargement
                        $button.prop('disabled', true).addClass('loading');
                        $button.text($t('Removing...'));

                        // Soumettre le formulaire
                        $.ajax({
                            url: $form.attr('action'),
                            type: 'POST',
                            data: $form.serialize(),
                            dataType: 'json',
                            success: function (response) {
                                if (response.success) {
                                    // Supprimer l'item du DOM avec animation
                                    $form.closest('.wishlist-item').fadeOut(300, function() {
                                        $(this).remove();

                                        // Vérifier s'il reste des items
                                        if ($element.find('.wishlist-item').length === 0) {
                                            // Recharger la page ou afficher un message "liste vide"
                                            location.reload();
                                        }
                                    });

                                    // Afficher un message de succès
                                    if (response.message) {
                                        alert({
                                            title: $t('Success'),
                                            content: response.message
                                        });
                                    }
                                } else {
                                    // Afficher l'erreur
                                    alert({
                                        title: $t('Error'),
                                        content: response.message || $t('An error occurred while removing the item.')
                                    });

                                    // Réactiver le bouton
                                    $button.prop('disabled', false).removeClass('loading');
                                    $button.text($t('Remove'));
                                }
                            },
                            error: function (xhr, status, error) {
                                console.error('Remove item error:', error);

                                alert({
                                    title: $t('Error'),
                                    content: $t('An error occurred while removing the item. Please try again.')
                                });

                                // Réactiver le bouton
                                $button.prop('disabled', false).removeClass('loading');
                                $button.text($t('Remove'));
                            }
                        });
                    },
                    cancel: function () {
                        // L'utilisateur a annulé, ne rien faire
                    }
                }
            });
        });

        // Gestion du formulaire de partage (si présent)
        $element.on('submit', '.share-wishlist-form', function (e) {
            e.preventDefault();

            var $form = $(this);
            var $button = $form.find('button[type="submit"]');
            var originalText = $button.text();

            // Validation simple
            var email = $form.find('input[name="email"]').val();
            console.log(email);
            if (!email || !email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                alert({
                    title: $t('Error'),
                    content: $t('Please enter a valid email address.')
                });
                return;
            }

            // État de chargement
            $button.prop('disabled', true).text($t('Sharing...'));

            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: $form.serialize(),
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        alert({
                            title: $t('Success'),
                            content: response.message || $t('Wishlist shared successfully!')
                        });

                        // Réinitialiser le formulaire
                        $form[0].reset();
                    } else {
                        alert({
                            title: $t('Error'),
                            content: response.message || $t('An error occurred while sharing the wishlist.')
                        });
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Share wishlist error:', error);

                    alert({
                        title: $t('Error'),
                        content: $t('An error occurred while sharing the wishlist. Please try again.')
                    });
                },
                complete: function () {
                    // Restaurer le bouton
                    $button.prop('disabled', false).text(originalText);
                }
            });
        });

        // Gestion des clicks sur les boutons remove (compatibilité avec votre ancien code)
        $element.on('click', '.remove-item', function (e) {
            // Si c'est dans un formulaire, laisser le gestionnaire de formulaire gérer
            if ($(this).closest('form').hasClass('remove-item-form')) {
                return; // Le gestionnaire de formulaire se chargera de tout
            }

            // Sinon, gérer comme avant (pour compatibilité)
            e.preventDefault();

            var $button = $(this);
            var itemData = {
                itemId: $button.data('item-id'),
                productId: $button.data('product-id'),
                wishlistId: $button.data('wishlist-id'),
                removeUrl: $button.data('remove-url')
            };

            console.log('Legacy remove button clicked:', itemData);
            // Vous pouvez ajouter ici votre logique legacy si nécessaire
        });
    };
});
