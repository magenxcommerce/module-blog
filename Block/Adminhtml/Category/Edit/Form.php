<?php

declare(strict_types=1);

namespace Magenx\Blog\Block\Adminhtml\Category\Edit;

use Magento\Backend\Block\Widget\Form\Generic;

class Form extends Generic
{
    protected function _construct(): void
    {
        parent::_construct();
        $this->setId('category_edit_form');
    }

    protected function _prepareForm()
    {
        $category = $this->_coreRegistry->registry('magenx_blog_category');

        $form = $this->_formFactory->create(['data' => ['id' => 'edit_form', 'action' => $this->getUrl('*/*/save'), 'method' => 'post']]);
        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset('category_fieldset', ['legend' => __('Category Information')]);

        $fieldset->addField('category_id', 'hidden', ['name' => 'category_id']);

        $fieldset->addField('name', 'text', [
            'name' => 'name',
            'label' => __('Name'),
            'title' => __('Name'),
            'required' => true,
        ]);

        $fieldset->addField('url_key', 'text', [
            'name' => 'url_key',
            'label' => __('URL Key'),
            'title' => __('URL Key'),
            'required' => true,
        ]);

        $fieldset->addField('is_active', 'select', [
            'name' => 'is_active',
            'label' => __('Status'),
            'title' => __('Status'),
            'values' => ['1' => __('Enabled'), '0' => __('Disabled')],
        ]);

        $fieldset->addField('position', 'text', [
            'name' => 'position',
            'label' => __('Position'),
            'title' => __('Position'),
            'class' => 'validate-digits',
        ]);

        $fieldset->addField('description', 'textarea', [
            'name' => 'description',
            'label' => __('Description'),
            'title' => __('Description'),
            'style' => 'height: 100px;',
        ]);

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

        if ($category) {
            $form->setValues($category->getData());
        }

        return parent::_prepareForm();
    }
}
