<?php

declare(strict_types=1);

namespace Magenx\Blog\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Typed reader for the magenx_blog/* settings. Resolvers consume this, never
 * ScopeConfigInterface inline.
 */
class Config
{
    private const XML_PATH_ENABLED = 'magenx_blog/general/enabled';
    private const XML_PATH_POSTS_PER_PAGE = 'magenx_blog/general/posts_per_page';
    private const XML_PATH_MAX_PAGE_SIZE = 'magenx_blog/general/max_page_size';

    private ScopeConfigInterface $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Deliberately not isSetFlag(): that reports "off" both when a merchant
     * turned the blog off and when the path is missing entirely, which is what
     * a stale `config` cache after a module update looks like. A missing path
     * therefore falls back to the config.xml default (on) — the storefront blog
     * cannot go dark because a cache is behind. An explicit 0 still disables it.
     */
    public function isEnabled(?int $storeId = null): bool
    {
        $value = $this->scopeConfig->getValue(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE, $storeId);

        if ($value === null || $value === '') {
            return true;
        }

        return (bool) (int) $value;
    }

    public function getPostsPerPage(?int $storeId = null): int
    {
        $value = (int) $this->scopeConfig->getValue(self::XML_PATH_POSTS_PER_PAGE, ScopeInterface::SCOPE_STORE, $storeId);

        return $value > 0 ? $value : 10;
    }

    public function getMaxPageSize(?int $storeId = null): int
    {
        $value = (int) $this->scopeConfig->getValue(self::XML_PATH_MAX_PAGE_SIZE, ScopeInterface::SCOPE_STORE, $storeId);

        return $value > 0 ? $value : 50;
    }
}
