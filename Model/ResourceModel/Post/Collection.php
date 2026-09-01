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
 *
 * The relation filters are semi-joins (`main_table.post_id IN (SELECT …)`)
 * rather than JOINs. Every relation table carries its own `post_id`, so a
 * joined-in table put two `post_id` columns in scope and any later
 * addFieldToFilter('post_id') died with "Column 'post_id' in WHERE is
 * ambiguous". A subselect keeps those columns out of the outer query
 * entirely. It also means no DISTINCT: joining post_store on
 * `store_id IN (0, $storeId)` matched a post assigned to both scopes twice,
 * and getSelectCountSql() counts joined rows, so getSize() — the storefront's
 * total_count — came back inflated even with the rows de-duplicated.
 */
class Collection extends AbstractCollection
{
    /**
     * Qualify every own column. addFieldToFilter() and setOrder() route the
     * field through _getMappedField(), which without this map emits the bare
     * column name — ambiguous the moment anything else in scope shares it.
     *
     * @var array
     */
    protected $_map = [
        'fields' => [
            'post_id' => 'main_table.post_id',
            'title' => 'main_table.title',
            'short_description' => 'main_table.short_description',
            'content' => 'main_table.content',
            'image' => 'main_table.image',
            'url_key' => 'main_table.url_key',
            'publish_date' => 'main_table.publish_date',
            'is_active' => 'main_table.is_active',
            'author_name' => 'main_table.author_name',
            'meta_title' => 'main_table.meta_title',
            'meta_description' => 'main_table.meta_description',
            'meta_keywords' => 'main_table.meta_keywords',
            'created_at' => 'main_table.created_at',
            'updated_at' => 'main_table.updated_at',
        ],
    ];

    private DateTime $dateTime;

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        DateTime $dateTime,
        $connection = null,
        ?\Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
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
        $select = $this->getConnection()->select()
            ->from(['post_store' => $this->getTable('magenx_blog_post_store')], ['post_id'])
            ->where('post_store.store_id IN (?)', [0, $storeId]);

        return $this->addPostIdSubselectFilter($select);
    }

    public function addCategoryFilter(int $categoryId): self
    {
        $select = $this->getConnection()->select()
            ->from(['post_category' => $this->getTable('magenx_blog_post_category')], ['post_id'])
            ->where('post_category.category_id = ?', $categoryId);

        return $this->addPostIdSubselectFilter($select);
    }

    public function addTagFilter(int $tagId): self
    {
        $select = $this->getConnection()->select()
            ->from(['post_tag' => $this->getTable('magenx_blog_post_tag')], ['post_id'])
            ->where('post_tag.tag_id = ?', $tagId);

        return $this->addPostIdSubselectFilter($select);
    }

    /**
     * "Posts about this product". The catalog join stays inside the
     * subselect, so catalog_product_entity's own created_at/updated_at never
     * reach the outer query.
     */
    public function addSkuFilter(string $sku): self
    {
        $select = $this->getConnection()->select()
            ->from(['post_product' => $this->getTable('magenx_blog_post_product')], ['post_id'])
            ->join(
                ['related_product' => $this->getTable('catalog_product_entity')],
                'related_product.entity_id = post_product.product_id',
                []
            )
            ->where('related_product.sku = ?', $sku);

        return $this->addPostIdSubselectFilter($select);
    }

    /**
     * Applies a post_id subselect as a semi-join on the outer query. Values
     * inside $select are already bound by the subselect's own builder, so
     * assembling it here introduces nothing unquoted.
     */
    private function addPostIdSubselectFilter(\Magento\Framework\DB\Select $select): self
    {
        $this->getSelect()->where('main_table.post_id IN (' . $select->assemble() . ')');

        return $this;
    }
}
