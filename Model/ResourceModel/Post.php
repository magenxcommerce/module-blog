<?php

declare(strict_types=1);

namespace Magenx\Blog\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Blog post resource model.
 *
 * Owns the post row plus its five relation tables (store, category, tag,
 * product, related post). Relations are only touched on save when the
 * corresponding *Ids (or, for products/related posts, positions) key was
 * explicitly set on the model — so a plain re-save of the post row (e.g.
 * from a resolver hydrating extra fields) never wipes relations it didn't
 * load.
 */
class Post extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init('magenx_blog_post', 'post_id');
    }

    protected function _afterSave(AbstractModel $object): AbstractDb
    {
        if ($object->hasData('store_ids')) {
            $this->saveStoreIds((int) $object->getId(), (array) $object->getData('store_ids'));
        }
        if ($object->hasData('category_ids')) {
            $this->saveCategoryIds((int) $object->getId(), (array) $object->getData('category_ids'));
        }
        if ($object->hasData('tag_ids')) {
            $this->saveTagIds((int) $object->getId(), (array) $object->getData('tag_ids'));
        }
        if ($object->hasData('product_positions')) {
            $this->saveProductPositions((int) $object->getId(), (array) $object->getData('product_positions'));
        }
        if ($object->hasData('related_post_positions')) {
            $this->saveRelatedPostPositions((int) $object->getId(), (array) $object->getData('related_post_positions'));
        }

        return parent::_afterSave($object);
    }

    public function saveStoreIds(int $postId, array $storeIds): void
    {
        // $allowZero: store_id 0 is "All Store Views", a real assignment — the
        // other relations hold entity ids, where 0 only ever means "nothing".
        $this->replaceRelation(
            'magenx_blog_post_store',
            'post_id',
            $postId,
            'store_id',
            array_map('intval', $storeIds),
            true
        );
    }

    public function getStoreIds(int $postId): array
    {
        return $this->getRelatedIds('magenx_blog_post_store', 'post_id', $postId, 'store_id');
    }

    public function saveCategoryIds(int $postId, array $categoryIds): void
    {
        $this->replaceRelation('magenx_blog_post_category', 'post_id', $postId, 'category_id', array_map('intval', $categoryIds));
    }

    public function getCategoryIds(int $postId): array
    {
        return $this->getRelatedIds('magenx_blog_post_category', 'post_id', $postId, 'category_id');
    }

    public function saveTagIds(int $postId, array $tagIds): void
    {
        $this->replaceRelation('magenx_blog_post_tag', 'post_id', $postId, 'tag_id', array_map('intval', $tagIds));
    }

    public function getTagIds(int $postId): array
    {
        return $this->getRelatedIds('magenx_blog_post_tag', 'post_id', $postId, 'tag_id');
    }

    /**
     * @param int $postId
     * @param array<int, int> $productPositions Product ID => sort position.
     */
    public function saveProductPositions(int $postId, array $productPositions): void
    {
        $this->replacePositionedRelation('magenx_blog_post_product', 'post_id', $postId, 'product_id', $productPositions);
    }

    /**
     * @return array<int, int> Product ID => sort position, ordered by position.
     */
    public function getProductPositions(int $postId): array
    {
        return $this->getPositionedRelation('magenx_blog_post_product', 'post_id', $postId, 'product_id');
    }

    /**
     * @param int $postId
     * @param array<int, int> $relatedPostPositions Related post ID => sort position.
     */
    public function saveRelatedPostPositions(int $postId, array $relatedPostPositions): void
    {
        unset($relatedPostPositions[$postId]);
        $this->replacePositionedRelation('magenx_blog_post_related', 'post_id', $postId, 'related_post_id', $relatedPostPositions);
    }

    /**
     * @return array<int, int> Related post ID => sort position, ordered by position.
     */
    public function getRelatedPostPositions(int $postId): array
    {
        return $this->getPositionedRelation('magenx_blog_post_related', 'post_id', $postId, 'related_post_id');
    }

    /**
     * @param int[] $postIds
     * @return array<int, int[]> Post ID => related IDs. Used by the batch
     *     GraphQL resolvers so a multi-post list query costs one relation
     *     lookup total, not one per post.
     */
    public function getCategoryIdsForPosts(array $postIds): array
    {
        return $this->getRelatedIdsForOwners('magenx_blog_post_category', 'post_id', $postIds, 'category_id');
    }

    /** @param int[] $postIds @return array<int, int[]> */
    public function getTagIdsForPosts(array $postIds): array
    {
        return $this->getRelatedIdsForOwners('magenx_blog_post_tag', 'post_id', $postIds, 'tag_id');
    }

    /** @param int[] $postIds @return array<int, array<int, int>> Post ID => [productId => position]. */
    public function getProductPositionsForPosts(array $postIds): array
    {
        return $this->getPositionedRelationForOwners('magenx_blog_post_product', 'post_id', $postIds, 'product_id');
    }

    /** @param int[] $postIds @return array<int, array<int, int>> Post ID => [relatedPostId => position]. */
    public function getRelatedPostPositionsForPosts(array $postIds): array
    {
        return $this->getPositionedRelationForOwners('magenx_blog_post_related', 'post_id', $postIds, 'related_post_id');
    }

    /** @param int[] $ownerIds @return array<int, int[]> */
    private function getRelatedIdsForOwners(string $table, string $ownerColumn, array $ownerIds, string $relatedColumn): array
    {
        $result = array_fill_keys($ownerIds, []);
        if (!$ownerIds) {
            return $result;
        }

        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getTable($table), [$ownerColumn, $relatedColumn])
            ->where($ownerColumn . ' IN (?)', $ownerIds);

        foreach ($connection->fetchAll($select) as $row) {
            $result[(int) $row[$ownerColumn]][] = (int) $row[$relatedColumn];
        }

        return $result;
    }

    /** @param int[] $ownerIds @return array<int, array<int, int>> */
    private function getPositionedRelationForOwners(string $table, string $ownerColumn, array $ownerIds, string $relatedColumn): array
    {
        $result = array_fill_keys($ownerIds, []);
        if (!$ownerIds) {
            return $result;
        }

        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getTable($table), [$ownerColumn, $relatedColumn, 'position'])
            ->where($ownerColumn . ' IN (?)', $ownerIds)
            ->order('position ASC');

        foreach ($connection->fetchAll($select) as $row) {
            $result[(int) $row[$ownerColumn]][(int) $row[$relatedColumn]] = (int) $row['position'];
        }

        return $result;
    }

    /**
     * @param bool $allowZero Keep a related id of 0. Only magenx_blog_post_store
     *     wants this — its 0 is the "All Store Views" assignment, and dropping
     *     it left posts with no store rows at all, invisible to the storefront
     *     collection's inner join while still listed in the admin grid.
     */
    private function replaceRelation(
        string $table,
        string $ownerColumn,
        int $ownerId,
        string $relatedColumn,
        array $relatedIds,
        bool $allowZero = false
    ): void {
        $connection = $this->getConnection();
        $connection->delete($this->getTable($table), [$ownerColumn . ' = ?' => $ownerId]);

        $minimum = $allowZero ? 0 : 1;
        $relatedIds = array_values(
            array_unique(array_filter($relatedIds, static fn ($id) => $id >= $minimum))
        );
        if (!$relatedIds) {
            return;
        }

        $rows = array_map(
            static fn (int $relatedId) => [$ownerColumn => $ownerId, $relatedColumn => $relatedId],
            $relatedIds
        );
        $connection->insertMultiple($this->getTable($table), $rows);
    }

    private function getRelatedIds(string $table, string $ownerColumn, int $ownerId, string $relatedColumn): array
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getTable($table), [$relatedColumn])
            ->where($ownerColumn . ' = ?', $ownerId);

        return array_map('intval', $connection->fetchCol($select));
    }

    /**
     * @param array<int, int> $positions Related ID => sort position.
     */
    private function replacePositionedRelation(string $table, string $ownerColumn, int $ownerId, string $relatedColumn, array $positions): void
    {
        $connection = $this->getConnection();
        $connection->delete($this->getTable($table), [$ownerColumn . ' = ?' => $ownerId]);

        if (!$positions) {
            return;
        }

        $rows = [];
        foreach ($positions as $relatedId => $position) {
            $relatedId = (int) $relatedId;
            if ($relatedId <= 0) {
                continue;
            }
            $rows[] = [$ownerColumn => $ownerId, $relatedColumn => $relatedId, 'position' => (int) $position];
        }
        if ($rows) {
            $connection->insertMultiple($this->getTable($table), $rows);
        }
    }

    /**
     * @return array<int, int> Related ID => sort position, ordered by position.
     */
    private function getPositionedRelation(string $table, string $ownerColumn, int $ownerId, string $relatedColumn): array
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getTable($table), [$relatedColumn, 'position'])
            ->where($ownerColumn . ' = ?', $ownerId)
            ->order('position ASC');

        return array_map('intval', $connection->fetchPairs($select));
    }
}
