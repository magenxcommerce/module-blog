<?php

declare(strict_types=1);

namespace Magenx\Blog\Block\Adminhtml\Post\Edit;

use Magento\Backend\Block\Widget\Tabs as WidgetTabs;

/**
 * Deliberately declares NO constructor: Widget\Tabs takes injected
 * dependencies whose order has changed between Magento versions, and any
 * override here has to forward them exactly or di:compile rejects the class.
 * The only thing this block needed the registry for was "is there a saved
 * post?", which the request answers directly — so there is nothing to inject
 * and nothing to keep in sync with the parent.
 */
class Tabs extends WidgetTabs
{
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

        // Related products are stored against a post id, so the tab can only
        // render once the post exists. The Edit controller redirects away when
        // the id does not resolve, so a post_id present here means saved.
        $postId = (int) $this->getRequest()->getParam('post_id');

        $this->addTab('related_products', [
            'label' => __('Related Products'),
            'title' => __('Related Products'),
            'content' => $postId
                ? $this->getLayout()
                    ->createBlock(\Magenx\Blog\Block\Adminhtml\Post\Edit\Tab\RelatedProducts::class)
                    ->toHtml()
                : '<div class="message message-notice">'
                    . __('Save the post before assigning related products.')->render()
                    . '</div>',
        ]);

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
