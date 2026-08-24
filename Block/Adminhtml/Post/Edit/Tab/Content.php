<?php

declare(strict_types=1);

namespace Magenx\Blog\Block\Adminhtml\Post\Edit\Tab;

use Magenx\Blog\Model\MediaUrl;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Cms\Model\Wysiwyg\Config as WysiwygConfig;
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
    private WysiwygConfig $wysiwygConfig;
    private MediaUrl $mediaUrl;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        WysiwygConfig $wysiwygConfig,
        MediaUrl $mediaUrl,
        array $data = []
    ) {
        $this->wysiwygConfig = $wysiwygConfig;
        $this->mediaUrl = $mediaUrl;
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
        $fieldset->addField('image', 'image', [
            'name' => 'image',
            'label' => __('Headline Image'),
            'title' => __('Headline Image'),
            'note' => __('Shown above the post body on the storefront, and as the card image in post listings. Uploaded to pub/media/magenx/blog.'),
        ]);

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
}
