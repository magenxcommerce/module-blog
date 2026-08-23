<?php

declare(strict_types=1);

namespace Magenx\Blog\Block\Adminhtml\Post\Edit\Tab;

use Magenx\Blog\Model\CategoryRepository;
use Magenx\Blog\Model\PostRepository;
use Magenx\Blog\Model\TagRepository;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;

class Taxonomy extends Generic
{
    private CategoryRepository $categoryRepository;
    private TagRepository $tagRepository;
    private PostRepository $postRepository;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        CategoryRepository $categoryRepository,
        TagRepository $tagRepository,
        PostRepository $postRepository,
        array $data = []
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->tagRepository = $tagRepository;
        $this->postRepository = $postRepository;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _construct(): void
    {
        parent::_construct();
        $this->setId('post_edit_taxonomy_form');
    }

    protected function _prepareForm()
    {
        $post = $this->_coreRegistry->registry('magenx_blog_post');

        $form = $this->_formFactory->create(['data' => ['id' => 'post_edit_taxonomy_form']]);
        // No form container: the tabs widget moves this content into the page's
        // single <form id="edit_form">, so a nested form here would both split
        // the POST across tabs and break the move (HierarchyRequestError).
        $form->setUseContainer(false);
        $this->setForm($form);

        $fieldset = $form->addFieldset('taxonomy_fieldset', ['legend' => __('Categories & Tags')]);

        $categoryOptions = [];
        foreach ($this->categoryRepository->getActiveList() as $category) {
            $categoryOptions[] = ['label' => $category->getName(), 'value' => $category->getId()];
        }
        $fieldset->addField('category_ids', 'multiselect', [
            'name' => 'category_ids[]',
            'label' => __('Categories'),
            'title' => __('Categories'),
            'values' => $categoryOptions,
        ]);

        $tagOptions = [];
        foreach ($this->tagRepository->getAll() as $tag) {
            $tagOptions[] = ['label' => $tag->getName(), 'value' => $tag->getId()];
        }
        $fieldset->addField('tag_ids', 'multiselect', [
            'name' => 'tag_ids[]',
            'label' => __('Tags'),
            'title' => __('Tags'),
            'values' => $tagOptions,
            'note' => __('Tags are created from the Blog > Tags grid.'),
        ]);

        if ($post) {
            $values = $post->getData();
            if ($post->getId()) {
                $values['category_ids'] = $this->postRepository->getCategoryIds((int) $post->getId());
                $values['tag_ids'] = $this->postRepository->getTagIds((int) $post->getId());
            }
            $form->setValues($values);
        }

        return parent::_prepareForm();
    }
}
