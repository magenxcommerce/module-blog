define([
    'jquery',
    'Magento_Ui/js/modal/modal'
], function ($) {
    'use strict';

    /**
     * Opens the "Add Products" chooser in a modal on the post edit page.
     *
     * The grid is fetched as a layout fragment (Controller\Adminhtml\Post\
     * Product\Grid) and injected into the modal — the same shape core uses for
     * its own modal choosers (see Magento_Cms's media browser, a generic
     * layout loaded into a modal). It deliberately does NOT render a
     * standalone admin page in an iframe: that page bootstraps its own
     * RequireJS environment, and without the config the legacy grid stack
     * (prototype, jquery-ui-modules/*, text) resolves to unmapped module names
     * and 404s. Inside the parent document those modules are already loaded —
     * the Related Products grid on this very page uses them.
     *
     * Each row's "Add" link is intercepted and replayed over AJAX, then the
     * grid is reloaded, so adding several products never leaves the modal.
     * Closing it reloads the page so the Related Products tab picks up
     * whatever was added -- through config.reloadUrl, which carries
     * active_tab: a plain location.reload() drops the param and reopens the
     * form on General.
     */
    return function (config, element) {
        var $content = $('<div class="magenx-blog-product-chooser"></div>'),
            added = false;

        $content.modal({
            title: config.title || '',
            type: 'slide',
            modalClass: 'magenx-blog-related-products-modal',
            buttons: [],
            closed: function () {
                if (added) {
                    window.location.href = config.reloadUrl || window.location.href;
                }
            }
        });

        function loadGrid() {
            $content.html('<div class="admin__data-grid-loading-mask"><div class="spinner"></div></div>');

            $.get(config.url).done(function (html) {
                $content.html(html);
            }).fail(function () {
                $content.html('<div class="message message-error">' + config.errorMessage + '</div>');
            });
        }

        $(element).on('click', function (e) {
            e.preventDefault();
            loadGrid();
            $content.modal('openModal');
        });

        // Row action links are plain hrefs to the Add controller; keep the
        // navigation inside the modal.
        $content.on('click', 'a[href*="post_product/add"]', function (e) {
            e.preventDefault();

            $.get($(this).attr('href')).done(function () {
                added = true;
            }).always(loadGrid);
        });
    };
});
