<?php

declare(strict_types=1);

namespace Magenx\Blog\Block\Adminhtml\Post\Product;

use Magenx\Blog\Model\PostRepository;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

/**
 * The "Add Products" popup grid (loaded as a layout fragment into the modal
 * Edit\Tab\RelatedProducts opens). Searchable/filterable like any admin grid;
 * already-linked products are excluded. Each row's "Add" action is a plain
 * link to Controller\Adminhtml\Post\Product\Add, which appends the relation
 * and redirects back here — so the admin can keep adding without the modal
 * closing.
 */
class Chooser extends Extended
{
    private ProductCollectionFactory $productCollectionFactory;
    private PostRepository $postRepository;

    public function __construct(
        Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        ProductCollectionFactory $productCollectionFactory,
        PostRepository $postRepository,
        array $data = []
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->postRepository = $postRepository;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct(): void
    {
        parent::_construct();
        $this->setId('post_related_products_chooser_grid');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
    }

    private function getPostId(): int
    {
        return (int) $this->getRequest()->getParam('post_id');
    }

    protected function _prepareCollection()
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect(['name']);

        $linkedIds = array_keys($this->postRepository->getProductPositions($this->getPostId()));
        if ($linkedIds) {
            $collection->addFieldToFilter('entity_id', ['nin' => $linkedIds]);
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', ['header' => __('ID'), 'index' => 'entity_id', 'type' => 'number']);
        $this->addColumn('name', ['header' => __('Name'), 'index' => 'name']);
        $this->addColumn('sku', ['header' => __('SKU'), 'index' => 'sku']);

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
                    'caption' => __('Add'),
                    // post_id rides in the route path, not in
                    // 'url' => ['params' => ...]: Widget\Grid\Column\Renderer\
                    // Action::_transformActionData *appends* that array to the
                    // param list ($params[] = ...) instead of merging it, and
                    // Framework\Url drops non-scalar route params — so post_id
                    // never reached the controller and every Add was a no-op.
                    'url' => ['base' => '*/post_product/add/post_id/' . $postId],
                    'field' => 'product_id',
                ],
            ],
        ]);

        return parent::_prepareColumns();
    }

    public function getGridUrl(): string
    {
        return $this->getUrl('*/post_product/grid', ['post_id' => $this->getPostId(), '_current' => true]);
    }
}
