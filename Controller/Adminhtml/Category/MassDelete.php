<?php

declare(strict_types=1);

namespace Magenx\Blog\Controller\Adminhtml\Category;

use Magenx\Blog\Model\CategoryRepository;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * See Post\MassDelete: the legacy grid massaction posts a comma-separated id
 * list, not a UI-component selection.
 */
class MassDelete extends Action implements HttpPostActionInterface
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

        $deleted = 0;
        foreach ($this->getSelectedIds() as $categoryId) {
            try {
                $this->categoryRepository->delete($this->categoryRepository->getById($categoryId));
                $deleted++;
            } catch (NoSuchEntityException $e) {
                continue;
            }
        }

        if ($deleted) {
            $this->messageManager->addSuccessMessage(__('A total of %1 blog categories have been deleted.', $deleted));
        } else {
            $this->messageManager->addErrorMessage(__('Please select at least one blog category to delete.'));
        }

        return $resultRedirect->setPath('*/*/');
    }

    /** @return int[] */
    private function getSelectedIds(): array
    {
        $selected = $this->getRequest()->getParam('category_id');
        if (!is_array($selected)) {
            $selected = explode(',', (string) $selected);
        }

        return array_values(array_unique(array_filter(array_map('intval', $selected))));
    }
}
