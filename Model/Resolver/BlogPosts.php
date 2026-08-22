<?php

declare(strict_types=1);

namespace Magenx\Blog\Model\Resolver;

use Magenx\Blog\Model\ResourceModel\Post\CollectionFactory;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Root Query.blogPosts. A plain resolver: it is the query root, not a field
 * that can ride a product/cart listing, so the batch-resolver rule does not
 * apply here.
 */
class BlogPosts implements ResolverInterface
{
    private CollectionFactory $collectionFactory;
    private DataMapper $dataMapper;

    public function __construct(CollectionFactory $collectionFactory, DataMapper $dataMapper)
    {
        $this->collectionFactory = $collectionFactory;
        $this->dataMapper = $dataMapper;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        /** @var ContextInterface $context */
        $storeId = (int) $context->getExtensionAttributes()->getStore()->getId();
        $pageSize = isset($args['pageSize']) ? max(1, (int) $args['pageSize']) : 10;
        $currentPage = isset($args['currentPage']) ? max(1, (int) $args['currentPage']) : 1;
        $filter = (array) ($args['filter'] ?? []);

        $collection = $this->collectionFactory->create();
        $collection->addPublishedFilter();
        $collection->addStoreFilter($storeId);

        if (!empty($filter['url_key'])) {
            $collection->addUrlKeyFilter((string) $filter['url_key']);
        }
        if (!empty($filter['category_id'])) {
            $collection->addCategoryFilter((int) $filter['category_id']);
        }
        if (!empty($filter['tag_id'])) {
            $collection->addTagFilter((int) $filter['tag_id']);
        }
        if (!empty($filter['sku'])) {
            $collection->addSkuFilter((string) $filter['sku']);
        }

        $collection->setOrder('publish_date', 'DESC');
        $totalCount = (int) $collection->getSize();
        $collection->setPageSize($pageSize)->setCurPage($currentPage);

        $items = [];
        foreach ($collection as $post) {
            $items[] = $this->dataMapper->mapPost($post);
        }

        return [
            'total_count' => $totalCount,
            'items' => $items,
        ];
    }
}
