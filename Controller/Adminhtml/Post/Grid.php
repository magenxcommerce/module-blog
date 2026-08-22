<?php

declare(strict_types=1);

namespace Magenx\Blog\Controller\Adminhtml\Post;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;

/** AJAX reload endpoint for the post listing grid (filter/sort/paging). */
class Grid extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Magenx_Blog::post';

    public function execute()
    {
        return $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
    }
}
