define(["uiComponent", "ko"], function (Component, ko) {
    "use strict";

    return Component.extend({
        initialize: function () {
            this._super();

            // Observables pour le formulaire
            this.wishlistName = ko.observable("");
            this.wishlistDescription = ko.observable("");
            this.isPrivate = ko.observable(false);
            this.shareCode = ko.observable("");
            this.isDefault = ko.observable(false);

            console.log("WishList Form component initialized");

            return this;
        },

        resetForm: function () {
            this.wishlistName("");
            this.wishlistDescription("");
            this.isPrivate(false);
            this.isDefault("");
        },

        getFormData: function () {
            return {
                name: this.wishlistName(),
                description: this.wishlistDescription(),
                is_public: this.isPrivateValue(),
                share_code: this.shareCode(),
                is_default: this.isDefaultValue(),
            };
        },

        validateForm: function () {
            if (!this.wishlistName().trim()) {
                return {
                    valid: false,
                    message: "Le nom est obligatoire",
                };
            }

            return {
                valid: true,
                message: "",
            };
        },

        onSubmit: function () {
            const validation = this.validateForm();

            if (!validation.valid) {
                alert(validation.message);
                return false;
            }

            return false;
        },
    });
});
