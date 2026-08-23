<?php

declare(strict_types=1);

namespace Magenx\Blog\Block\Adminhtml\Post\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Registry;

/**
 * The module's one explicit backend-config requirement: a proper,
 * relation-table-backed related-products picker (not a free-text SKU
 * field). This tab lists the products currently linked to the post
 * (magenx_blog_post_product, ordered by position); products are added via
 * the "Add Products" modal (Product\Chooser, opened in an iframe from the
 * accompanying phtml) and removed / reordered via row actions — the same
 * search-then-attach shape core Magento uses to assign products to a
 * category.
 */
class RelatedProducts extends Extended
{
    private Registry $coreRegistry;
    private ProductCollectionFactory $productCollectionFactory;

    public function __construct(
        Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        ProductCollectionFactory $productCollectionFactory,
        Registry $registry,
        array $data = []
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->coreRegistry = $registry;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct(): void
    {
        parent::_construct();
        $this->setId('post_related_products_grid');
        $this->setUseAjax(false);
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
    }

    private function getPostId(): int
    {
        $post = $this->coreRegistry->registry('magenx_blog_post');

        return $post ? (int) $post->getId() : 0;
    }

    protected function _prepareCollection()
    {
        $postId = $this->getPostId();

        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect(['name']);
        $collection->getSelect()->join(
            ['post_product' => $collection->getResource()->getTable('magenx_blog_post_product')],
            'post_product.product_id = e.entity_id AND post_product.post_id = ' . (int) $postId,
            ['position' => 'position']
        );
        $collection->getSelect()->order('post_product.position ASC');

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('name', ['header' => __('Name'), 'index' => 'name', 'filter' => false, 'sortable' => false]);
        $this->addColumn('sku', ['header' => __('SKU'), 'index' => 'sku', 'filter' => false, 'sortable' => false]);

        $postId = $this->getPostId();
        $this->addColumn('action', [
            'header' => __('Action'),
            'type' => 'action',
            'getter' => 'getId',
            'filter' => false,
            'sortable' => false,
            'is_system' => true,
            'actions' => [
                [
                    'caption' => __('Move Up'),
                    'url' => ['base' => '*/post_product/move', 'params' => ['post_id' => $postId, 'direction' => 'up']],
                    'field' => 'product_id',
                ],
                [
                    'caption' => __('Move Down'),
                    'url' => ['base' => '*/post_product/move', 'params' => ['post_id' => $postId, 'direction' => 'down']],
                    'field' => 'product_id',
                ],
                [
                    'caption' => __('Remove'),
                    'url' => ['base' => '*/post_product/remove', 'params' => ['post_id' => $postId]],
                    'field' => 'product_id',
                ],
            ],
        ]);

        return parent::_prepareColumns();
    }

    public function getGridUrl(): string
    {
        return $this->getUrl('*/post/edit', ['post_id' => $this->getPostId(), '_current' => true]);
    }

    /**
     * The chooser grid as a layout fragment, loaded straight into the modal.
     * Not a standalone page: an admin page rendered in an iframe re-bootstraps
     * RequireJS, and the legacy grid stack (prototype, jquery-ui-modules/*)
     * only resolves through the requirejs-config map this document already has.
     */
    public function getAddProductsUrl(): string
    {
        return $this->getUrl('*/post_product/grid', ['post_id' => $this->getPostId()]);
    }

    protected function _toHtml()
    {
        $config = json_encode([
            '#post-related-products-add' => [
                'Magenx_Blog/js/related-products-modal' => [
                    'url' => $this->getAddProductsUrl(),
                    'title' => __('Add Products')->render(),
                    'errorMessage' => __('Could not load the product list.')->render(),
                ],
            ],
        ], JSON_UNESCAPED_SLASHES);

        $addButton = '<div class="admin__field" style="margin-bottom: 10px;">'
            . '<a href="#" id="post-related-products-add" class="action-default">' . __('Add Products') . '</a>'
            . '</div>'
            . '<script type="text/x-magento-init">' . $config . '</script>';

        return $addButton . parent::_toHtml();
    }
}
