<?php

declare(strict_types=1);

namespace Magenx\Blog\Block\Adminhtml\Tag;

use Magento\Backend\Block\Widget\Grid\Container;

class Index extends Container
{
    protected function _construct(): void
    {
        $this->_controller = 'adminhtml_tag';
        $this->_blockGroup = 'Magenx_Blog';
        $this->_headerText = __('Blog Tags');
        $this->_addButtonLabel = __('Add New Tag');
        parent::_construct();
    }
}
