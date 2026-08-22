<?php

declare(strict_types=1);

namespace Magenx\Blog\Controller\Adminhtml\Category;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;

class Index extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Magenx_Blog::category';

    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Magenx_Blog::category');
        $resultPage->getConfig()->getTitle()->prepend(__('Blog Categories'));

        return $resultPage;
    }
}
