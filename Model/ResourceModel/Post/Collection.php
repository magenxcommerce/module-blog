<?php

declare(strict_types=1);

namespace Magenx\Blog\Model\ResourceModel\Post;

use Magenx\Blog\Model\Post;
use Magenx\Blog\Model\ResourceModel\Post as PostResource;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;

/**
 * Blog post collection with the server-side filters the storefront needs
 * (url_key, category_id, tag_id, sku, store scope, published-only) — the
 * exact filters the previous integration lacked, which is why this schema
 * exists.
 */
class Collection extends AbstractCollection
{
    private DateTime $dateTime;

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        DateTime $dateTime,
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->dateTime = $dateTime;
    }

    protected function _construct(): void
    {
        $this->_init(Post::class, PostResource::class);
    }

    public function addActiveFilter(): self
    {
        $this->addFieldToFilter('is_active', 1);

        return $this;
    }

    /**
     * is_active AND publish_date is null-or-past — the "visible on the
     * storefront right now" filter.
     */
    public function addPublishedFilter(): self
    {
        $this->addActiveFilter();
        $this->getSelect()->where(
            'main_table.publish_date IS NULL OR main_table.publish_date <= ?',
            $this->dateTime->gmtDate()
        );

        return $this;
    }

    public function addUrlKeyFilter(string $urlKey): self
    {
        $this->addFieldToFilter('url_key', $urlKey);

        return $this;
    }

    /**
     * Restrict to posts assigned to the given store view, or scoped to
     * "all store views" (store_id = 0).
     */
    public function addStoreFilter(int $storeId): self
    {
        $this->getSelect()->join(
            ['post_store' => $this->getTable('magenx_blog_post_store')],
            'post_store.post_id = main_table.post_id AND post_store.store_id IN (0, ' . (int) $storeId . ')',
            []
        )->distinct(true);

        return $this;
    }

    public function addCategoryFilter(int $categoryId): self
    {
        $this->getSelect()->join(
            ['post_category' => $this->getTable('magenx_blog_post_category')],
            'post_category.post_id = main_table.post_id AND post_category.category_id = ' . (int) $categoryId,
            []
        )->distinct(true);

        return $this;
    }

    public function addTagFilter(int $tagId): self
    {
        $this->getSelect()->join(
            ['post_tag' => $this->getTable('magenx_blog_post_tag')],
            'post_tag.post_id = main_table.post_id AND post_tag.tag_id = ' . (int) $tagId,
            []
        )->distinct(true);

        return $this;
    }

    public function addSkuFilter(string $sku): self
    {
        $this->getSelect()
            ->join(
                ['post_product' => $this->getTable('magenx_blog_post_product')],
                'post_product.post_id = main_table.post_id',
                []
            )
            ->join(
                ['related_product' => $this->getTable('catalog_product_entity')],
                'related_product.entity_id = post_product.product_id AND related_product.sku = '
                    . $this->getConnection()->quote($sku),
                []
            )
            ->distinct(true);

        return $this;
    }
}
