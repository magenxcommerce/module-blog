<?php

declare(strict_types=1);

namespace Magenx\Blog\Block\Adminhtml\Post\Edit;

use Magento\Backend\Block\Widget\Form\Generic;

/**
 * The single <form id="edit_form"> wrapper for the tabbed post edit page.
 *
 * It carries no fields of its own: the tabs widget moves every tab's content
 * into this element (Tabs::setDestElementId('edit_form')), so all tabs post
 * together. The tab blocks therefore render their fieldsets *without* a form
 * container of their own — a nested <form id="edit_form"> inside a tab makes
 * the widget append an element to its own descendant, which the browser
 * rejects with "HierarchyRequestError: The new child element contains the
 * parent" and leaves the page collapsed with no tab content.
 */
class Form extends Generic
{
    protected function _construct(): void
    {
        parent::_construct();
        $this->setId('post_edit_form');
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create([
            'data' => [
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/save'),
                'method' => 'post',
                'enctype' => 'multipart/form-data',
            ],
        ]);
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
