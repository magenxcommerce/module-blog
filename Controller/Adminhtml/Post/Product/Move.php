<?php

declare(strict_types=1);

namespace Magenx\Blog\Controller\Adminhtml\Post\Product;

use Magenx\Blog\Model\PostRepository;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;

class Move extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Magenx_Blog::post';

    private PostRepository $postRepository;

    public function __construct(Action\Context $context, PostRepository $postRepository)
    {
        parent::__construct($context);
        $this->postRepository = $postRepository;
    }

    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $postId = (int) $this->getRequest()->getParam('post_id');
        $productId = (int) $this->getRequest()->getParam('product_id');
        $direction = (string) $this->getRequest()->getParam('direction') === 'up' ? 'up' : 'down';

        if ($postId && $productId) {
            $this->postRepository->moveProduct($postId, $productId, $direction);
        }

        return $resultRedirect->setPath('*/post/edit', ['post_id' => $postId]);
    }
}
