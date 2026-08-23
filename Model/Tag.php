<?php

declare(strict_types=1);

namespace Magenx\Blog\Model;

use Magento\Framework\DataObject\IdentityInterface;
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
class Tag extends AbstractModel implements IdentityInterface
{
    public const CACHE_TAG = 'magenx_blog_tag';

    /**
     * Saving or deleting a tag cleans these tags, which is what makes
     * Magento_CacheInvalidate send its X-Magento-Tags-Pattern PURGE. The
     * storefront turns that into a Next.js `blog` tag revalidation, so an edit
     * shows up immediately instead of waiting out the 30-minute blog TTL.
     *
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    protected function _construct(): void
    {
        $this->_init(ResourceModel\Tag::class);
    }

    /** @return string[] */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG, self::CACHE_TAG . '_' . $this->getId()];
    }
}
