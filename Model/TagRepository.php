<?php

declare(strict_types=1);

namespace Magenx\Blog\Model;

use Magenx\Blog\Model\ResourceModel\Tag as TagResource;
use Magenx\Blog\Model\ResourceModel\Tag\CollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class TagRepository
{
    private TagFactory $tagFactory;
    private TagResource $tagResource;
    private CollectionFactory $collectionFactory;

    public function __construct(
        TagFactory $tagFactory,
        TagResource $tagResource,
        CollectionFactory $collectionFactory
    ) {
        $this->tagFactory = $tagFactory;
        $this->tagResource = $tagResource;
        $this->collectionFactory = $collectionFactory;
    }

    public function getById(int $tagId): Tag
    {
        $tag = $this->tagFactory->create();
        $this->tagResource->load($tag, $tagId);
        if (!$tag->getId()) {
            throw new NoSuchEntityException(__('The blog tag with ID "%1" does not exist.', $tagId));
        }

        return $tag;
    }

    public function getByUrlKey(string $urlKey): Tag
    {
        $tag = $this->tagFactory->create();
        $this->tagResource->load($tag, $urlKey, 'url_key');
        if (!$tag->getId()) {
            throw new NoSuchEntityException(__('The blog tag with URL key "%1" does not exist.', $urlKey));
        }

        return $tag;
    }

    public function save(Tag $tag): Tag
    {
        $this->tagResource->save($tag);

        return $tag;
    }

    public function delete(Tag $tag): void
    {
        $this->tagResource->delete($tag);
    }

    /** @param int[] $tagIds @return array<int, Tag> */
    public function getByIds(array $tagIds): array
    {
        if (!$tagIds) {
            return [];
        }
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('tag_id', ['in' => $tagIds]);

        return $collection->getItems();
    }

    /** @return array<int, Tag> Every tag, ordered by name. */
    public function getAll(): array
    {
        $collection = $this->collectionFactory->create();
        $collection->setOrder('name', 'ASC');

        return $collection->getItems();
    }
}
