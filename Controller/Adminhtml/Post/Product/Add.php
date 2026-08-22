<?php

declare(strict_types=1);

namespace Magenx\Blog\Controller\Adminhtml\Post\Product;

use Magenx\Blog\Model\PostRepository;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Appends one product to a post's related-products list (position = current
 * max + 1) and returns to the chooser so the admin can keep adding without
 * closing the modal.
 */
class Add extends Action implements HttpGetActionInterface
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

        if ($postId && $productId) {
            try {
                $this->postRepository->getById($postId);
                $positions = $this->postRepository->getProductPositions($postId);
                if (!array_key_exists($productId, $positions)) {
                    $positions[$productId] = $positions ? max($positions) + 1 : 0;
                    $this->postRepository->saveProductPositions($postId, $positions);
                }
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('This blog post no longer exists.'));
            }
        }

        return $resultRedirect->setPath('*/*/index', ['post_id' => $postId]);
    }
}
