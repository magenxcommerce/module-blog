<?php

declare(strict_types=1);

namespace Magenx\Blog\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Assigns every post that has no store rows to "All Store Views" (store_id 0).
 *
 * Posts saved before the store-id fix ended up with an empty
 * magenx_blog_post_store relation: both the Save controller and the resource
 * model dropped 0 as falsy, so the default selection wrote nothing. The
 * storefront collection inner-joins that table, so those posts were invisible
 * on the storefront while still listed in the admin grid — and no amount of
 * re-saving repaired them.
 *
 * Only posts with no rows at all are touched; a post deliberately assigned to
 * specific store views keeps exactly what it has.
 */
class BackfillPostStoreRelations implements DataPatchInterface
{
    private ModuleDataSetupInterface $moduleDataSetup;

    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public function apply(): self
    {
        $connection = $this->moduleDataSetup->getConnection();
        $postTable = $this->moduleDataSetup->getTable('magenx_blog_post');
        $storeTable = $this->moduleDataSetup->getTable('magenx_blog_post_store');

        $select = $connection->select()
            ->from(['p' => $postTable], ['post_id'])
            ->joinLeft(
                ['s' => $storeTable],
                's.post_id = p.post_id',
                []
            )
            ->where('s.post_id IS NULL');

        $rows = [];
        foreach ($connection->fetchCol($select) as $postId) {
            $rows[] = ['post_id' => (int) $postId, 'store_id' => 0];
        }

        if ($rows) {
            $connection->insertMultiple($storeTable, $rows);
        }

        return $this;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
