<?php

declare(strict_types=1);

namespace Magenx\Blog\Block\Adminhtml\Post;

use Magenx\Blog\Model\ResourceModel\Post\CollectionFactory;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
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
        $this->setId('magenxBlogPostGrid');
        $this->setDefaultSort('post_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $this->setCollection($this->collectionFactory->create());

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('post_id', [
            'header' => __('ID'),
            'type' => 'number',
            'index' => 'post_id',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id',
        ]);

        $this->addColumn('title', [
            'header' => __('Title'),
            'index' => 'title',
        ]);

        $this->addColumn('url_key', [
            'header' => __('URL Key'),
            'index' => 'url_key',
        ]);

        $this->addColumn('author_name', [
            'header' => __('Author'),
            'index' => 'author_name',
        ]);

        $this->addColumn('publish_date', [
            'header' => __('Publish Date'),
            'type' => 'date',
            'index' => 'publish_date',
        ]);

        $this->addColumn('is_active', [
            'header' => __('Status'),
            'index' => 'is_active',
            'type' => 'options',
            'options' => ['1' => __('Enabled'), '0' => __('Disabled')],
        ]);

        $this->addColumn('action', [
            'header' => __('Action'),
            'type' => 'action',
            'getter' => 'getPostId',
            'actions' => [
                [
                    'caption' => __('Edit'),
                    'url' => ['base' => '*/*/edit'],
                    'field' => 'post_id',
                ],
            ],
            'filter' => false,
            'sortable' => false,
            'is_system' => true,
        ]);

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('post_id');
        $this->getMassactionBlock()->setFormFieldName('post_id');
        $this->getMassactionBlock()->addItem('delete', [
            'label' => __('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => __('Are you sure you want to delete the selected posts?'),
        ]);

        return $this;
    }

    public function getRowUrl($row): string
    {
        return $this->getUrl('*/*/edit', ['post_id' => $row->getId()]);
    }
}
