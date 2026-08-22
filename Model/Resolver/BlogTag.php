<?php

declare(strict_types=1);

namespace Magenx\Blog\Model\Resolver;

use Magenx\Blog\Model\TagRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class BlogTag implements ResolverInterface
{
    private TagRepository $tagRepository;
    private DataMapper $dataMapper;

    public function __construct(TagRepository $tagRepository, DataMapper $dataMapper)
    {
        $this->tagRepository = $tagRepository;
        $this->dataMapper = $dataMapper;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        $urlKey = trim((string) ($args['urlKey'] ?? ''));
        if ($urlKey === '') {
            return null;
        }

        try {
            $tag = $this->tagRepository->getByUrlKey($urlKey);
        } catch (NoSuchEntityException $e) {
            return null;
        }

        return $this->dataMapper->mapTag($tag);
    }
}
