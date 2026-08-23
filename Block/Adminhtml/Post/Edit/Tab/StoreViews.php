<?php

declare(strict_types=1);

namespace Magenx\Blog\Block\Adminhtml\Post\Edit\Tab;

use Magenx\Blog\Model\PostRepository;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store as SystemStore;

class StoreViews extends Generic
{
    private SystemStore $systemStore;
    private PostRepository $postRepository;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        SystemStore $systemStore,
        PostRepository $postRepository,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        $this->postRepository = $postRepository;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _construct(): void
    {
        parent::_construct();
        $this->setId('post_edit_store_views_form');
    }

    protected function _prepareForm()
    {
        $post = $this->_coreRegistry->registry('magenx_blog_post');
        $postId = $post ? (int) $post->getId() : 0;

        $form = $this->_formFactory->create(['data' => ['id' => 'post_edit_store_views_form']]);
        // No form container: the tabs widget moves this content into the page's
        // single <form id="edit_form">, so a nested form here would both split
        // the POST across tabs and break the move (HierarchyRequestError).
        $form->setUseContainer(false);
        $this->setForm($form);

        $fieldset = $form->addFieldset('store_views_fieldset', ['legend' => __('Store Views')]);

        $field = $fieldset->addField('store_ids', 'multiselect', [
            'name' => 'store_ids[]',
            'label' => __('Store Views'),
            'title' => __('Store Views'),
            'required' => true,
            'values' => $this->systemStore->getStoreValuesForForm(false, true),
            'note' => __('Leave "All Store Views" selected unless this post must differ per store.'),
        ]);
        $renderer = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element::class
        );
        $field->setRenderer($renderer);

        if ($post) {
            $values = $post->getData();
            // A saved post with no rows in magenx_blog_post_store predates the
            // store-id fix in the Save controller; show it as "All Store Views"
            // so re-saving repairs it in one click instead of tripping the
            // required-field validator on an empty selection.
            $storeIds = $postId ? $this->postRepository->getStoreIds($postId) : [];
            $values['store_ids'] = $storeIds ?: [0];
            $form->setValues($values);
        }

        return parent::_prepareForm();
    }
}
