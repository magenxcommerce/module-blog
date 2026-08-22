<?php

declare(strict_types=1);

namespace Magenx\Blog\Block\Adminhtml\Category;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Registry;

class Edit extends Container
{
    private Registry $coreRegistry;

    public function __construct(Context $context, Registry $registry, array $data = [])
    {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    protected function _construct(): void
    {
        $this->_objectId = 'category_id';
        $this->_blockGroup = 'Magenx_Blog';
        $this->_controller = 'adminhtml_category';

        parent::_construct();
    }

    public function getHeaderText(): \Magento\Framework\Phrase
    {
        $category = $this->coreRegistry->registry('magenx_blog_category');
        if ($category && $category->getId()) {
            return __('Edit Category "%1"', $category->getName());
        }

        return __('New Category');
    }
}
