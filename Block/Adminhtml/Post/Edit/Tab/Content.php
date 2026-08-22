<?php

declare(strict_types=1);

namespace Magenx\Blog\Block\Adminhtml\Post\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;

/**
 * Plain textarea, not a full WYSIWYG — keeping Magento_Cms out of the
 * dependency graph for a module that has no other reason to need it.
 */
class Content extends Generic
{
    protected function _construct(): void
    {
        parent::_construct();
        $this->setId('post_edit_content_form');
    }

    protected function _prepareForm()
    {
        $post = $this->_coreRegistry->registry('magenx_blog_post');

        $form = $this->_formFactory->create(['data' => ['id' => 'edit_form', 'method' => 'post']]);
        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset('content_fieldset', ['legend' => __('Content')]);

        $fieldset->addField('content', 'textarea', [
            'name' => 'content',
            'label' => __('Post Content'),
            'title' => __('Post Content'),
            'style' => 'height: 400px;',
            'note' => __('HTML is stored as-is and returned to the storefront unsanitized on read — sanitize/render with care on the client, the same as any other rich-text field.'),
        ]);

        if ($post) {
            $form->setValues($post->getData());
        }

        return parent::_prepareForm();
    }
}
