define(["uiComponent", "ko", "jquery", "Magento_Ui/js/modal/modal"], function (
    Component,
    ko,
    $,
    modal
) {
    "use strict";

    return Component.extend({
        initialize: function () {
            this._super();
            this.buttonText = ko.observable("Créer une wishlist");
            this.bindAjaxWishlistLinks(); 
            return this;
        },
        defaults: {
            template: "Romain_AdvancedWishList/component/wishlist-modal",
        },
        bindAjaxWishlistLinks: function () {
        const self = this;

        $(document).on("click", ".advanced-wishlist-link", function (e) {
            e.preventDefault();
            const url = $(this).data("url");
            if (!url) return;

            window.location.href = url;
            });
        },
        openModal: function () {
            const modalElement = $("#create-wishlist-modal");

            if (
                modalElement.length &&
                !modalElement.hasClass("modal-initialized")
            ) {
                modal(
                    {
                        type: "popup",
                        responsive: true,
                        innerScroll: true,
                        title: "Créer une nouvelle wishlist",
                        buttons: [],
                    },
                    modalElement
                );
                modalElement.addClass("modal-initialized");
            }

            modalElement.modal("openModal");
        },
        closeModal: function () {
            $("#create-wishlist-modal").modal("closeModal");
        },
    });
});
