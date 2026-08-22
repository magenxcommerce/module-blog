<?php

declare(strict_types=1);

namespace Magenx\Blog\Block\Adminhtml\Tag;

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
        $this->_objectId = 'tag_id';
        $this->_blockGroup = 'Magenx_Blog';
        $this->_controller = 'adminhtml_tag';

        parent::_construct();
    }

    public function getHeaderText(): \Magento\Framework\Phrase
    {
        $tag = $this->coreRegistry->registry('magenx_blog_tag');
        if ($tag && $tag->getId()) {
            return __('Edit Tag "%1"', $tag->getName());
        }

        return __('New Tag');
    }
}
