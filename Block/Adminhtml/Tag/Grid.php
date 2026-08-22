<?php

declare(strict_types=1);

namespace Magenx\Blog\Block\Adminhtml\Tag;

use Magenx\Blog\Model\ResourceModel\Tag\CollectionFactory;
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
        $this->setId('magenxBlogTagGrid');
        $this->setDefaultSort('tag_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $this->setCollection($this->collectionFactory->create());

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('tag_id', ['header' => __('ID'), 'type' => 'number', 'index' => 'tag_id']);
        $this->addColumn('name', ['header' => __('Name'), 'index' => 'name']);
        $this->addColumn('url_key', ['header' => __('URL Key'), 'index' => 'url_key']);
        $this->addColumn('action', [
            'header' => __('Action'),
            'type' => 'action',
            'getter' => 'getTagId',
            'actions' => [['caption' => __('Edit'), 'url' => ['base' => '*/*/edit'], 'field' => 'tag_id']],
            'filter' => false,
            'sortable' => false,
            'is_system' => true,
        ]);

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('tag_id');
        $this->getMassactionBlock()->setFormFieldName('tag_id');
        $this->getMassactionBlock()->addItem('delete', [
            'label' => __('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => __('Are you sure you want to delete the selected tags?'),
        ]);

        return $this;
    }

    public function getRowUrl($row): string
    {
        return $this->getUrl('*/*/edit', ['tag_id' => $row->getId()]);
    }
}
