<?php

declare(strict_types=1);

namespace Magenx\Blog\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Blog post.
 *
 * @method int getPostId()
 * @method $this setPostId(int $postId)
 * @method string getTitle()
 * @method $this setTitle(string $title)
 * @method string getShortDescription()
 * @method $this setShortDescription(?string $shortDescription)
 * @method string getContent()
 * @method $this setContent(?string $content)
 * @method string getImage()
 * @method $this setImage(?string $image)
 * @method string getUrlKey()
 * @method $this setUrlKey(string $urlKey)
 * @method string getPublishDate()
 * @method $this setPublishDate(?string $publishDate)
 * @method int getIsActive()
 * @method $this setIsActive(int $isActive)
 * @method string getAuthorName()
 * @method $this setAuthorName(?string $authorName)
 * @method string getMetaTitle()
 * @method $this setMetaTitle(?string $metaTitle)
 * @method string getMetaDescription()
 * @method $this setMetaDescription(?string $metaDescription)
 * @method string getMetaKeywords()
 * @method $this setMetaKeywords(?string $metaKeywords)
 */
class Post extends AbstractModel implements IdentityInterface
{
    public const CACHE_TAG = 'magenx_blog_post';

    /**
     * Saving or deleting a post cleans these tags, which is what makes
     * Magento_CacheInvalidate send its X-Magento-Tags-Pattern PURGE. The
     * storefront turns that into a Next.js `blog` tag revalidation, so an edit
     * shows up immediately instead of waiting out the 30-minute blog TTL.
     *
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    protected function _construct(): void
    {
        $this->_init(ResourceModel\Post::class);
    }

    /** @return string[] */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG, self::CACHE_TAG . '_' . $this->getId()];
    }
}
