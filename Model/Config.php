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

    public function isEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE, $storeId);
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
