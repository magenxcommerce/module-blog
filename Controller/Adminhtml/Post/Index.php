<?php

declare(strict_types=1);

namespace Magenx\Blog\Controller\Adminhtml\Post;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;

class Index extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Magenx_Blog::post';

    public function execute()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Magenx_Blog::post');
        $resultPage->getConfig()->getTitle()->prepend(__('Blog Posts'));

        return $resultPage;
    }
}
