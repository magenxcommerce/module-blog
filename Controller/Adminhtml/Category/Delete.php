<?php

declare(strict_types=1);

namespace Magenx\Blog\Controller\Adminhtml\Category;

use Magenx\Blog\Model\CategoryRepository;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class Delete extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Magenx_Blog::category';

    private CategoryRepository $categoryRepository;

    public function __construct(Action\Context $context, CategoryRepository $categoryRepository)
    {
        parent::__construct($context);
        $this->categoryRepository = $categoryRepository;
    }

    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $categoryId = (int) $this->getRequest()->getParam('category_id');

        try {
            $this->categoryRepository->delete($this->categoryRepository->getById($categoryId));
            $this->messageManager->addSuccessMessage(__('The blog category has been deleted.'));
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('This blog category no longer exists.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong while deleting the blog category.'));
        }

        return $resultRedirect->setPath('*/*/');
    }
}
