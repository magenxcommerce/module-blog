<?php

declare(strict_types=1);

namespace Magenx\Blog\Block\Adminhtml\Post;

use Magento\Backend\Block\Widget\Grid\Container;

class Index extends Container
{
    protected function _construct(): void
    {
        $this->_controller = 'adminhtml_post';
        $this->_blockGroup = 'Magenx_Blog';
        $this->_headerText = __('Blog Posts');
        $this->_addButtonLabel = __('Add New Post');
        parent::_construct();
    }
}
