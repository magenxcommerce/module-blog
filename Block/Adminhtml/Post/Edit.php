<?php

declare(strict_types=1);

namespace Magenx\Blog\Block\Adminhtml\Post;

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
        $this->_objectId = 'post_id';
        $this->_blockGroup = 'Magenx_Blog';
        $this->_controller = 'adminhtml_post';

        parent::_construct();

        // The base Form\Container already wires Save / Delete / Back / Reset
        // correctly for an object with an id param (_objectId) — no custom
        // button JS needed here.
    }

    public function getHeaderText(): \Magento\Framework\Phrase
    {
        $post = $this->coreRegistry->registry('magenx_blog_post');
        if ($post && $post->getId()) {
            return __('Edit Post "%1"', $post->getTitle());
        }

        return __('New Post');
    }
}
