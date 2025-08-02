define([
    'jquery',
    'Magento_Ui/js/modal/modal'
], function ($, modal) {
    'use strict';

    $(document).ready(function () {
        var options = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            title: 'Formulaire',
            buttons: []
        };

        var popup = modal(options, $('#custom-modal'));

        $('#open-modal').on('click', function () {
            $('#custom-modal').modal('openModal');
        });
    });
});
