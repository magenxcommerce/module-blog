<?php

declare(strict_types=1);

namespace Magenx\Blog\Model\ResourceModel\Tag;

use Magenx\Blog\Model\ResourceModel\Tag as TagResource;
use Magenx\Blog\Model\Tag;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct(): void
    {
        $this->_init(Tag::class, TagResource::class);
    }
}
