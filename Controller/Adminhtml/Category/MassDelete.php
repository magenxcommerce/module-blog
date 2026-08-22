<?php

declare(strict_types=1);

namespace Magenx\Blog\Controller\Adminhtml\Category;

use Magenx\Blog\Model\CategoryRepository;
use Magenx\Blog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\Component\MassAction\Filter;

class MassDelete extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Magenx_Blog::category';

    private Filter $filter;
    private CategoryRepository $categoryRepository;
    private CollectionFactory $collectionFactory;

    public function __construct(
        Action\Context $context,
        Filter $filter,
        CategoryRepository $categoryRepository,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->categoryRepository = $categoryRepository;
        $this->collectionFactory = $collectionFactory;
    }

    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $deleted = 0;
        foreach ($collection->getItems() as $category) {
            try {
                $this->categoryRepository->delete($this->categoryRepository->getById((int) $category->getId()));
                $deleted++;
            } catch (NoSuchEntityException $e) {
                continue;
            }
        }

        if ($deleted) {
            $this->messageManager->addSuccessMessage(__('A total of %1 blog categories have been deleted.', $deleted));
        }

        return $resultRedirect->setPath('*/*/');
    }
}
