<?php

declare(strict_types=1);

namespace Magenx\Blog\Controller\Adminhtml\Category;

use Magenx\Blog\Model\CategoryFactory;
use Magenx\Blog\Model\CategoryRepository;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Save extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Magenx_Blog::category';

    private CategoryRepository $categoryRepository;
    private CategoryFactory $categoryFactory;

    public function __construct(
        Action\Context $context,
        CategoryRepository $categoryRepository,
        CategoryFactory $categoryFactory
    ) {
        parent::__construct($context);
        $this->categoryRepository = $categoryRepository;
        $this->categoryFactory = $categoryFactory;
    }

    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            return $resultRedirect->setPath('*/*/');
        }

        $categoryId = (int) ($data['category_id'] ?? 0);

        try {
            $category = $categoryId ? $this->categoryRepository->getById($categoryId) : $this->categoryFactory->create();
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('This blog category no longer exists.'));

            return $resultRedirect->setPath('*/*/');
        }

        $category->addData([
            'name' => trim((string) ($data['name'] ?? '')),
            'url_key' => trim((string) ($data['url_key'] ?? '')),
            'description' => $data['description'] ?? null,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'is_active' => (int) ($data['is_active'] ?? 0),
            'position' => (int) ($data['position'] ?? 0),
        ]);

        try {
            if ($category->getName() === '') {
                throw new LocalizedException(__('Name is required.'));
            }
            if ($category->getUrlKey() === '') {
                throw new LocalizedException(__('URL Key is required.'));
            }

            $this->categoryRepository->save($category);
            $this->messageManager->addSuccessMessage(__('The blog category has been saved.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            return $resultRedirect->setPath('*/*/edit', ['category_id' => $categoryId ?: null]);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong while saving the blog category.'));

            return $resultRedirect->setPath('*/*/edit', ['category_id' => $categoryId ?: null]);
        }

        if ($this->getRequest()->getParam('back')) {
            return $resultRedirect->setPath('*/*/edit', ['category_id' => $category->getId()]);
        }

        return $resultRedirect->setPath('*/*/');
    }
}
