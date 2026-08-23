<?php

declare(strict_types=1);

namespace Magenx\Blog\Block\Adminhtml\Post\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;

class Meta extends Generic
{
    protected function _construct(): void
    {
        parent::_construct();
        $this->setId('post_edit_meta_form');
    }

    protected function _prepareForm()
    {
        $post = $this->_coreRegistry->registry('magenx_blog_post');

        $form = $this->_formFactory->create(['data' => ['id' => 'post_edit_meta_form']]);
        // No form container: the tabs widget moves this content into the page's
        // single <form id="edit_form">, so a nested form here would both split
        // the POST across tabs and break the move (HierarchyRequestError).
        $form->setUseContainer(false);
        $this->setForm($form);

        $fieldset = $form->addFieldset('meta_fieldset', ['legend' => __('Meta / SEO')]);

        $fieldset->addField('meta_title', 'text', [
            'name' => 'meta_title',
            'label' => __('Meta Title'),
            'title' => __('Meta Title'),
        ]);

        $fieldset->addField('meta_description', 'textarea', [
            'name' => 'meta_description',
            'label' => __('Meta Description'),
            'title' => __('Meta Description'),
            'style' => 'height: 80px;',
        ]);

        $fieldset->addField('meta_keywords', 'textarea', [
            'name' => 'meta_keywords',
            'label' => __('Meta Keywords'),
            'title' => __('Meta Keywords'),
            'style' => 'height: 80px;',
        ]);

        if ($post) {
            $form->setValues($post->getData());
        }

        return parent::_prepareForm();
    }
}
