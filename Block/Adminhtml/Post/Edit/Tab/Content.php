<?php

declare(strict_types=1);

namespace Magenx\Blog\Block\Adminhtml\Post\Edit\Tab;

use Magenx\Blog\Model\MediaUrl;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Cms\Helper\Wysiwyg\Images as ImagesHelper;
use Magento\Cms\Model\Wysiwyg\Config as WysiwygConfig;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;

/**
 * Headline image and post body.
 *
 * The body is edited through the standard admin WYSIWYG (TinyMCE + the media
 * browser), the same editor CMS pages and blocks use. When WYSIWYG is turned
 * off in Content > Design > Configuration, Editor degrades to a plain
 * textarea on its own — nothing here has to handle that case.
 */
class Content extends Generic
{
    /**
     * Media folder the headline-image gallery opens in, relative to the
     * gallery's storage root. Magento creates it: passing it as
     * current_tree_path makes Cms\Helper\Wysiwyg\Images::getCurrentPath() run
     * createSubDirIfNotExist() on the first open.
     */
    private const IMAGE_FOLDER = 'blog';

    private WysiwygConfig $wysiwygConfig;
    private ImagesHelper $imagesHelper;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        WysiwygConfig $wysiwygConfig,
        ImagesHelper $imagesHelper,
        array $data = []
    ) {
        $this->wysiwygConfig = $wysiwygConfig;
        $this->imagesHelper = $imagesHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _construct(): void
    {
        parent::_construct();
        $this->setId('post_edit_content_form');
    }

    protected function _prepareForm()
    {
        $post = $this->_coreRegistry->registry('magenx_blog_post');

        $form = $this->_formFactory->create(['data' => ['id' => 'post_edit_content_form']]);
        // No form container: the tabs widget moves this content into the page's
        // single <form id="edit_form">, so a nested form here would both split
        // the POST across tabs and break the move (HierarchyRequestError).
        $form->setUseContainer(false);
        $this->setForm($form);

        $fieldset = $form->addFieldset('content_fieldset', ['legend' => __('Content')]);

        // The headline image comes first: it sits above the body everywhere it
        // is rendered — the storefront post page hero and the card image in
        // every post listing.
        $image = $fieldset->addField('image', 'text', [
            'name' => 'image',
            'label' => __('Headline Image'),
            'title' => __('Headline Image'),
            'note' => __('Shown above the post body on the storefront, and as the card image in post listings.'),
        ]);
        $image->setAfterElementHtml($this->getGalleryButtonHtml($image));

        $fieldset->addField('content', 'editor', [
            'name' => 'content',
            'label' => __('Post Content'),
            'title' => __('Post Content'),
            'style' => 'height: 400px;',
            // No force_load: Editor then binds its setup to window load, which
            // runs after the tabs widget has moved this textarea into
            // #edit_form. Initialising earlier would leave TinyMCE bound to a
            // node that is about to be re-parented.
            'config' => $this->wysiwygConfig->getConfig([
                'add_variables' => false,
                'add_widgets' => false,
                'add_images' => true,
            ]),
            'note' => __('Stored exactly as entered — Magento does not sanitize it. The Next.js storefront sanitizes it on read (sanitizeCmsHtml, packages/engine), so script and event-handler attributes never reach a visitor.'),
        ]);

        if ($post) {
            $values = $post->getData();
            // The image element renders its preview straight from the value, so
            // it needs a URL — the column stores a media-relative path. Only
            // the form's copy is rewritten; the post itself keeps the path.
            $values['image'] = $this->mediaUrl->toUrl($post->getImage());
            $form->setValues($values);
        }

        return parent::_prepareForm();
    }

    /**
     * "Select from Gallery" button, wired to the native media gallery — the
     * very dialog the WYSIWYG's Insert Image button opens, so whichever
     * gallery the install has enabled (legacy browser or the enhanced Media
     * Gallery) is what the admin sees, with its own upload button.
     *
     * Gated on the same ACL resource Cms\Model\Wysiwyg\Config::getConfig()
     * checks before it exposes the files browser at all.
     */
    private function getGalleryButtonHtml(AbstractElement $element): string
    {
        if (!$this->_authorization->isAllowed('Magento_Cms::media_gallery')) {
            return '';
        }

        $buttonId = $element->getHtmlId() . '_gallery';
        $config = json_encode([
            '#' . $buttonId => [
                'Magenx_Blog/js/media-gallery-field' => [
                    'targetId' => $element->getHtmlId(),
                    'url' => $this->getGalleryUrl($element->getHtmlId()),
                    'title' => __('Insert File...')->render(),
                ],
            ],
        ], JSON_UNESCAPED_SLASHES);

        return ' <button type="button" id="' . $buttonId . '" class="action-default">'
            . '<span>' . __('Select from Gallery') . '</span></button>'
            . '<script type="text/x-magento-init">' . $config . '</script>';
    }

    /**
     * Both URL parameters are load-bearing, and both need their trailing
     * slash: MediabrowserUtility::openDialog() parses target_element_id out of
     * the URL with /target_element_id/(.*?)/ and current_tree_path with a
     * regex whose match it indexes without a null check — a URL missing
     * current_tree_path throws before the dialog ever opens.
     *
     * node is the one the legacy browser reads to decide which directory to
     * list. It is resolved before current_tree_path creates the folder, so on
     * a legacy install the very first open lands at the storage root and every
     * open after that lands in the blog folder.
     */
    private function getGalleryUrl(string $targetElementId): string
    {
        $folderId = $this->imagesHelper->idEncode(self::IMAGE_FOLDER);

        return $this->getUrl('cms/wysiwyg_images/index', [
            'target_element_id' => $targetElementId,
            'current_tree_path' => $folderId,
            'node' => $folderId,
        ]);
    }
}
