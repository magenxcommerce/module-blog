<?php

declare(strict_types=1);

namespace Magenx\Blog\Controller\Adminhtml\Category;

use Magenx\Blog\Model\CategoryFactory;
use Magenx\Blog\Model\CategoryRepository;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;

class Edit extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Magenx_Blog::category';

    private Registry $coreRegistry;
    private CategoryRepository $categoryRepository;
    private CategoryFactory $categoryFactory;

    public function __construct(
        Action\Context $context,
        Registry $coreRegistry,
        CategoryRepository $categoryRepository,
        CategoryFactory $categoryFactory
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->categoryRepository = $categoryRepository;
        $this->categoryFactory = $categoryFactory;
    }

    public function execute()
    {
        $categoryId = (int) $this->getRequest()->getParam('category_id');

        if ($categoryId) {
            try {
                $category = $this->categoryRepository->getById($categoryId);
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('This blog category no longer exists.'));
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

                return $resultRedirect->setPath('*/*/');
            }
        } else {
            $category = $this->categoryFactory->create();
        }

        $this->coreRegistry->register('magenx_blog_category', $category);

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Magenx_Blog::category');
        $resultPage->getConfig()->getTitle()->prepend(
            $categoryId ? $category->getName() : __('New Blog Category')
        );

        return $resultPage;
    }
}
