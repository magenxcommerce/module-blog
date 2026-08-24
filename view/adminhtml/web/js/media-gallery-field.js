define([
    'jquery',
    'mage/adminhtml/browser'
], function ($) {
    'use strict';

    /**
     * Turns a plain text field into a media-gallery-backed image picker by
     * opening the native gallery dialog — the same cms/wysiwyg_images/index
     * the WYSIWYG's Insert Image button opens, so the install's own gallery
     * (legacy media browser or the enhanced Media Gallery) is what renders,
     * upload button and all.
     *
     * The gallery writes the chosen file straight into the target input
     * (targetElement.val(...) in both implementations), so there is no
     * callback to register here — only the flag below and the click handler.
     */
    return function (config, element) {
        var $target = $('#' + config.targetId);

        // Both implementations read this off the target with jQuery .data() to
        // decide what the insert returns: set, a media path ("/media/blog/x.jpg");
        // unset, a {{media url="…"}} directive or a whole <img> tag, neither of
        // which is usable over GraphQL. It cannot be rendered as an attribute
        // from the form element — AbstractElement::getHtmlAttributes() is a
        // fixed allowlist that drops unknown data-* keys — so it is set on
        // jQuery's own data store instead, which is what .data() reads.
        $target.data('force_static_path', 1);

        $(element).on('click', function (e) {
            e.preventDefault();
            window.MediabrowserUtility.openDialog(config.url, null, null, config.title);
        });
    };
});
