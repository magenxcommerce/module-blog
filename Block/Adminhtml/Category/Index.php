<?php

declare(strict_types=1);

namespace Magenx\Blog\Block\Adminhtml\Category;

use Magento\Backend\Block\Widget\Grid\Container;

class Index extends Container
{
    protected function _construct(): void
    {
        $this->_controller = 'adminhtml_category';
        $this->_blockGroup = 'Magenx_Blog';
        $this->_headerText = __('Blog Categories');
        $this->_addButtonLabel = __('Add New Category');
        parent::_construct();
    }
}
