<?php

declare(strict_types=1);

namespace Magenx\Blog\Model;

use Magento\Framework\Filter\FilterManager;

/**
 * Normalizes admin-entered URL keys into something that survives a URL.
 *
 * Admin forms let an author paste a title straight into the URL Key field
 * ("With great power comes great capability"), which produces a storefront
 * link full of encoded spaces. Magento does the same normalization for
 * product and CMS url keys; this is the blog's copy of it.
 */
class UrlKey
{
    private FilterManager $filterManager;

    public function __construct(FilterManager $filterManager)
    {
        $this->filterManager = $filterManager;
    }

    /**
     * @param string $urlKey The value the admin typed.
     * @param string $fallback Used when $urlKey normalizes to nothing (the title).
     */
    public function normalize(string $urlKey, string $fallback = ''): string
    {
        $slug = $this->slug($urlKey);

        return $slug !== '' ? $slug : $this->slug($fallback);
    }

    private function slug(string $value): string
    {
        // translitUrl() lowercases, transliterates and hyphenates; the regex
        // then drops anything it left behind (quotes, stray punctuation) and
        // collapses repeated or edge hyphens.
        $slug = $this->filterManager->translitUrl(trim($value));
        $slug = preg_replace('/[^a-z0-9-]+/', '-', strtolower((string) $slug)) ?? '';

        return trim(preg_replace('/-+/', '-', $slug) ?? '', '-');
    }
}
