<?php

declare(strict_types=1);

namespace Magenx\Blog\Model;

use Magenx\Blog\Model\ResourceModel\Category as CategoryResource;
use Magenx\Blog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class CategoryRepository
{
    private CategoryFactory $categoryFactory;
    private CategoryResource $categoryResource;
    private CollectionFactory $collectionFactory;

    public function __construct(
        CategoryFactory $categoryFactory,
        CategoryResource $categoryResource,
        CollectionFactory $collectionFactory
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->categoryResource = $categoryResource;
        $this->collectionFactory = $collectionFactory;
    }

    public function getById(int $categoryId): Category
    {
        $category = $this->categoryFactory->create();
        $this->categoryResource->load($category, $categoryId);
        if (!$category->getId()) {
            throw new NoSuchEntityException(__('The blog category with ID "%1" does not exist.', $categoryId));
        }

        return $category;
    }

    public function getByUrlKey(string $urlKey): Category
    {
        $category = $this->categoryFactory->create();
        $this->categoryResource->load($category, $urlKey, 'url_key');
        if (!$category->getId()) {
            throw new NoSuchEntityException(__('The blog category with URL key "%1" does not exist.', $urlKey));
        }

        return $category;
    }

    public function save(Category $category): Category
    {
        $this->categoryResource->save($category);

        return $category;
    }

    public function delete(Category $category): void
    {
        $this->categoryResource->delete($category);
    }

    /** @return array<int, Category> */
    public function getActiveList(): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('is_active', 1)->setOrder('position', 'ASC');

        return $collection->getItems();
    }

    /** @param int[] $categoryIds @return array<int, Category> */
    public function getByIds(array $categoryIds): array
    {
        if (!$categoryIds) {
            return [];
        }
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('category_id', ['in' => $categoryIds]);

        return $collection->getItems();
    }
}
