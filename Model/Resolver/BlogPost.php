<?php

declare(strict_types=1);

namespace Magenx\Blog\Model\Resolver;

use Magenx\Blog\Model\ResourceModel\Post\CollectionFactory;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Root Query.blogPost(urlKey). Fails soft: returns null (not an error) when
 * the post does not exist, is disabled, is not assigned to this store, or is
 * future-dated — the direct server-side lookup the previous integration's
 * PostsFilterInput could not reliably do.
 */
class BlogPost implements ResolverInterface
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
        $urlKey = trim((string) ($args['urlKey'] ?? ''));
        if ($urlKey === '') {
            return null;
        }

        /** @var ContextInterface $context */
        $storeId = (int) $context->getExtensionAttributes()->getStore()->getId();

        $collection = $this->collectionFactory->create();
        $collection->addPublishedFilter();
        $collection->addStoreFilter($storeId);
        $collection->addUrlKeyFilter($urlKey);
        $collection->setPageSize(1);

        $post = $collection->getFirstItem();
        if (!$post->getId()) {
            return null;
        }

        return $this->dataMapper->mapPost($post);
    }
}
