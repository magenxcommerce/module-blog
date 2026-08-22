<?php

declare(strict_types=1);

namespace Magenx\Blog\Model\ResourceModel\Category;

use Magenx\Blog\Model\Category;
use Magenx\Blog\Model\ResourceModel\Category as CategoryResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct(): void
    {
        $this->_init(Category::class, CategoryResource::class);
    }
}
