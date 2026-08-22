<?php

declare(strict_types=1);

namespace Magenx\Blog\Block\Adminhtml\Post\Edit;

use Magento\Backend\Block\Widget\Tabs as WidgetTabs;
use Magento\Framework\Registry;

class Tabs extends WidgetTabs
{
    private Registry $coreRegistry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    protected function _construct(): void
    {
        parent::_construct();
        $this->setId('post_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Post Information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('general', [
            'label' => __('General'),
            'title' => __('General'),
            'content' => $this->getLayout()->createBlock(\Magenx\Blog\Block\Adminhtml\Post\Edit\Tab\General::class)->toHtml(),
            'active' => true,
        ]);

        $this->addTab('content', [
            'label' => __('Content'),
            'title' => __('Content'),
            'content' => $this->getLayout()->createBlock(\Magenx\Blog\Block\Adminhtml\Post\Edit\Tab\Content::class)->toHtml(),
        ]);

        $this->addTab('taxonomy', [
            'label' => __('Categories & Tags'),
            'title' => __('Categories & Tags'),
            'content' => $this->getLayout()->createBlock(\Magenx\Blog\Block\Adminhtml\Post\Edit\Tab\Taxonomy::class)->toHtml(),
        ]);

        $post = $this->coreRegistry->registry('magenx_blog_post');
        if ($post && $post->getId()) {
            $this->addTab('related_products', [
                'label' => __('Related Products'),
                'title' => __('Related Products'),
                'content' => $this->getLayout()
                    ->createBlock(\Magenx\Blog\Block\Adminhtml\Post\Edit\Tab\RelatedProducts::class)
                    ->toHtml(),
            ]);
        } else {
            $this->addTab('related_products', [
                'label' => __('Related Products'),
                'title' => __('Related Products'),
                'content' => '<div class="message message-notice">'
                    . __('Save the post before assigning related products.')->render()
                    . '</div>',
            ]);
        }

        $this->addTab('related_posts', [
            'label' => __('Related Posts'),
            'title' => __('Related Posts'),
            'content' => $this->getLayout()->createBlock(\Magenx\Blog\Block\Adminhtml\Post\Edit\Tab\RelatedPosts::class)->toHtml(),
        ]);

        $this->addTab('meta', [
            'label' => __('Meta / SEO'),
            'title' => __('Meta / SEO'),
            'content' => $this->getLayout()->createBlock(\Magenx\Blog\Block\Adminhtml\Post\Edit\Tab\Meta::class)->toHtml(),
        ]);

        $this->addTab('store_views', [
            'label' => __('Store Views'),
            'title' => __('Store Views'),
            'content' => $this->getLayout()->createBlock(\Magenx\Blog\Block\Adminhtml\Post\Edit\Tab\StoreViews::class)->toHtml(),
        ]);

        return parent::_beforeToHtml();
    }
}
