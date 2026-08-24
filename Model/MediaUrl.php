<?php

declare(strict_types=1);

namespace Magenx\Blog\Model;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;

/**
 * Converts between the media-relative path stored in magenx_blog_post.image
 * and the absolute URL every consumer needs.
 *
 * The column holds a path ("magenx/blog/hero.jpg") so a base-URL or domain
 * change never invalidates it, but neither consumer can use a bare path: the
 * admin image element renders its preview from the value verbatim, and
 * next/image rejects a src that is neither absolute nor leading-slash. Both
 * therefore get the value through toUrl(); Save runs the posted value back
 * through toPath().
 */
class MediaUrl
{
    private const SUBDIR = 'magenx/blog';

    private StoreManagerInterface $storeManager;

    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /** The media subdirectory headline images are uploaded to, relative to pub/media. */
    public function getSubdirectory(): string
    {
        return self::SUBDIR;
    }

    /** Absolute URL for a stored path; a value that is already absolute is returned untouched. */
    public function toUrl(?string $path): ?string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return null;
        }

        if ($this->isAbsolute($path)) {
            return $path;
        }

        return $this->getMediaBaseUrl() . ltrim($path, '/');
    }

    /**
     * Media-relative path for a value coming back off the form, which the
     * block fed as an absolute URL. A value that is not under this store's
     * media base URL is kept as posted rather than mangled — an image set to
     * an external URL by an older save stays reachable.
     */
    public function toPath(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $base = $this->getMediaBaseUrl();
        if ($base !== '' && str_starts_with($value, $base)) {
            return substr($value, strlen($base)) ?: null;
        }

        return $value;
    }

    private function isAbsolute(string $value): bool
    {
        return (bool) preg_match('#^(https?:)?//#i', $value);
    }

    private function getMediaBaseUrl(): string
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
    }
}
