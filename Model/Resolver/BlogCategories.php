<?php

declare(strict_types=1);

namespace Magenx\Blog\Model\Resolver;

use Magenx\Blog\Model\CategoryRepository;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class BlogCategories implements ResolverInterface
{
    private CategoryRepository $categoryRepository;
    private DataMapper $dataMapper;

    public function __construct(CategoryRepository $categoryRepository, DataMapper $dataMapper)
    {
        $this->categoryRepository = $categoryRepository;
        $this->dataMapper = $dataMapper;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        $items = [];
        foreach ($this->categoryRepository->getActiveList() as $category) {
            $items[] = $this->dataMapper->mapCategory($category);
        }

        return $items;
    }
}
