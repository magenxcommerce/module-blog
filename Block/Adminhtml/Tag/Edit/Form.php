<?php

declare(strict_types=1);

namespace Magenx\Blog\Block\Adminhtml\Tag\Edit;

use Magento\Backend\Block\Widget\Form\Generic;

class Form extends Generic
{
    protected function _construct(): void
    {
        parent::_construct();
        $this->setId('tag_edit_form');
    }

    protected function _prepareForm()
    {
        $tag = $this->_coreRegistry->registry('magenx_blog_tag');

        $form = $this->_formFactory->create(['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]);
        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset('tag_fieldset', ['legend' => __('Tag Information')]);

        $fieldset->addField('tag_id', 'hidden', ['name' => 'tag_id']);

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

        if ($tag) {
            $form->setValues($tag->getData());
        }

        return parent::_prepareForm();
    }
}
