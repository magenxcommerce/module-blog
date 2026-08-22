<?php

declare(strict_types=1);

namespace Magenx\Blog\Block\Adminhtml\Post\Edit\Tab;

use Magenx\Blog\Model\PostRepository;
use Magenx\Blog\Model\ResourceModel\Post\CollectionFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;

class RelatedPosts extends Generic
{
    private CollectionFactory $collectionFactory;
    private PostRepository $postRepository;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        CollectionFactory $collectionFactory,
        PostRepository $postRepository,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->postRepository = $postRepository;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _construct(): void
    {
        parent::_construct();
        $this->setId('post_edit_related_posts_form');
    }

    protected function _prepareForm()
    {
        $post = $this->_coreRegistry->registry('magenx_blog_post');
        $postId = $post ? (int) $post->getId() : 0;

        $form = $this->_formFactory->create(['data' => ['id' => 'edit_form', 'method' => 'post']]);
        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset('related_posts_fieldset', ['legend' => __('Related Posts')]);

        $options = [];
        $collection = $this->collectionFactory->create();
        if ($postId) {
            $collection->addFieldToFilter('post_id', ['neq' => $postId]);
        }
        foreach ($collection as $candidate) {
            $options[] = ['label' => $candidate->getTitle(), 'value' => $candidate->getId()];
        }

        $fieldset->addField('related_post_ids', 'multiselect', [
            'name' => 'related_post_ids[]',
            'label' => __('Related Posts'),
            'title' => __('Related Posts'),
            'values' => $options,
        ]);

        if ($post) {
            $values = $post->getData();
            if ($postId) {
                $values['related_post_ids'] = array_keys($this->postRepository->getRelatedPostPositions($postId));
            }
            $form->setValues($values);
        }

        return parent::_prepareForm();
    }
}
