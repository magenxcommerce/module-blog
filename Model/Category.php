<?php

declare(strict_types=1);

namespace Magenx\Blog\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Blog category. Flat list, no hierarchy.
 *
 * @method int getCategoryId()
 * @method $this setCategoryId(int $categoryId)
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
 * @method int getIsActive()
 * @method $this setIsActive(int $isActive)
 * @method int getPosition()
 * @method $this setPosition(int $position)
 */
class Category extends AbstractModel
{
    protected function _construct(): void
    {
        $this->_init(ResourceModel\Category::class);
    }
}
