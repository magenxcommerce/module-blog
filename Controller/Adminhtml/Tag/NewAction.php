<?php

declare(strict_types=1);

namespace Magenx\Blog\Controller\Adminhtml\Tag;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;

class NewAction extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Magenx_Blog::tag';

    public function execute()
    {
        return $this->_forward('edit');
    }
}
