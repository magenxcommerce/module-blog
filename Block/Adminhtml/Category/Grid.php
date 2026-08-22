<?php

declare(strict_types=1);

namespace Magenx\Blog\Block\Adminhtml\Category;

use Magenx\Blog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data as BackendHelper;

class Grid extends Extended
{
    private CollectionFactory $collectionFactory;

    public function __construct(
        Context $context,
        BackendHelper $backendHelper,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct(): void
    {
        parent::_construct();
        $this->setId('magenxBlogCategoryGrid');
        $this->setDefaultSort('position');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $this->setCollection($this->collectionFactory->create());

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('category_id', ['header' => __('ID'), 'type' => 'number', 'index' => 'category_id']);
        $this->addColumn('name', ['header' => __('Name'), 'index' => 'name']);
        $this->addColumn('url_key', ['header' => __('URL Key'), 'index' => 'url_key']);
        $this->addColumn('position', ['header' => __('Position'), 'type' => 'number', 'index' => 'position']);
        $this->addColumn('is_active', [
            'header' => __('Status'),
            'index' => 'is_active',
            'type' => 'options',
            'options' => ['1' => __('Enabled'), '0' => __('Disabled')],
        ]);
        $this->addColumn('action', [
            'header' => __('Action'),
            'type' => 'action',
            'getter' => 'getCategoryId',
            'actions' => [['caption' => __('Edit'), 'url' => ['base' => '*/*/edit'], 'field' => 'category_id']],
            'filter' => false,
            'sortable' => false,
            'is_system' => true,
        ]);

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('category_id');
        $this->getMassactionBlock()->setFormFieldName('category_id');
        $this->getMassactionBlock()->addItem('delete', [
            'label' => __('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => __('Are you sure you want to delete the selected categories?'),
        ]);

        return $this;
    }

    public function getRowUrl($row): string
    {
        return $this->getUrl('*/*/edit', ['category_id' => $row->getId()]);
    }
}
