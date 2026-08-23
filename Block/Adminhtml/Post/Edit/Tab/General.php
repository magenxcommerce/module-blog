<?php

declare(strict_types=1);

namespace Magenx\Blog\Block\Adminhtml\Post\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;

class General extends Generic
{
    protected function _construct(): void
    {
        parent::_construct();
        $this->setId('post_edit_general_form');
    }

    protected function _prepareForm()
    {
        $post = $this->_coreRegistry->registry('magenx_blog_post');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(['data' => ['id' => 'post_edit_general_form']]);
        // No form container: the tabs widget moves this content into the page's
        // single <form id="edit_form">, so a nested form here would both split
        // the POST across tabs and break the move (HierarchyRequestError).
        $form->setUseContainer(false);
        $this->setForm($form);

        $fieldset = $form->addFieldset('general_fieldset', ['legend' => __('General')]);

        $fieldset->addField('post_id', 'hidden', ['name' => 'post_id']);

        $fieldset->addField('title', 'text', [
            'name' => 'title',
            'label' => __('Title'),
            'title' => __('Title'),
            'required' => true,
        ]);

        $fieldset->addField('url_key', 'text', [
            'name' => 'url_key',
            'label' => __('URL Key'),
            'title' => __('URL Key'),
            'required' => true,
            'note' => __('Used to build the post\'s storefront URL. Letters, numbers and hyphens only.'),
        ]);

        $fieldset->addField('is_active', 'select', [
            'name' => 'is_active',
            'label' => __('Status'),
            'title' => __('Status'),
            'values' => ['1' => __('Enabled'), '0' => __('Disabled')],
        ]);

        $fieldset->addField('author_name', 'text', [
            'name' => 'author_name',
            'label' => __('Author Name'),
            'title' => __('Author Name'),
        ]);

        $fieldset->addField('publish_date', 'date', [
            'name' => 'publish_date',
            'label' => __('Publish Date'),
            'title' => __('Publish Date'),
            'date_format' => 'yyyy-MM-dd',
            'note' => __('Posts with a future publish date are not returned by the storefront-facing GraphQL query.'),
        ]);

        $fieldset->addField('short_description', 'textarea', [
            'name' => 'short_description',
            'label' => __('Short Description'),
            'title' => __('Short Description'),
            'style' => 'height: 100px;',
        ]);

        if ($post) {
            $form->setValues($post->getData());
        }

        return parent::_prepareForm();
    }
}
