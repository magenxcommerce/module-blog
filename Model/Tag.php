<?php

declare(strict_types=1);

namespace Magenx\Blog\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Blog tag.
 *
 * @method int getTagId()
 * @method $this setTagId(int $tagId)
 * @method string getName()
 * @method $this setName(string $name)
 * @method string getUrlKey()
 * @method $this setUrlKey(string $urlKey)
 * @method string getDescription()
 * @method $this setDescription(?string $description)
 * @method string getMetaTitle()
 * @method $this setMetaTitle(?string $metaTitle)
 * @method string getMetaDescription()
 * @method $this setMetaDescription(?string $metaDescription)
 */
class Tag extends AbstractModel
{
    protected function _construct(): void
    {
        $this->_init(ResourceModel\Tag::class);
    }
}
