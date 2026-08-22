define([
    'jquery',
    'Magento_Ui/js/modal/modal'
], function ($) {
    'use strict';

    /**
     * Opens the "Add Products" chooser (Controller\Adminhtml\Post\Product\Index,
     * layout="popup") in a modal iframe. Reloads the parent page on close so
     * the Related Products grid reflects whatever was added — the chooser
     * itself does a full round trip per click (see
     * Controller\Adminhtml\Post\Product\Add), so there is nothing to merge
     * client-side.
     */
    return function (config, element) {
        var $iframe = $('<iframe>', {
            src: config.url,
            style: 'width: 100%; height: 520px; border: 0;'
        });
        var $modal = $('<div>').append($iframe);

        $(element).on('click', function (e) {
            e.preventDefault();

            $modal.modal({
                title: config.title || '',
                type: 'slide',
                modalClass: 'magenx-blog-related-products-modal',
                buttons: [],
                closed: function () {
                    window.location.reload();
                }
            });
            $modal.modal('openModal');
        });
    };
});
