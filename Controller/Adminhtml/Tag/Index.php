<?php

declare(strict_types=1);

namespace Magenx\Blog\Controller\Adminhtml\Tag;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;

class Index extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Magenx_Blog::tag';

    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Magenx_Blog::tag');
        $resultPage->getConfig()->getTitle()->prepend(__('Blog Tags'));

        return $resultPage;
    }
}
