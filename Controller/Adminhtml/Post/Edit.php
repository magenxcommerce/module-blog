<?php

declare(strict_types=1);

namespace Magenx\Blog\Controller\Adminhtml\Post;

use Magenx\Blog\Model\Post;
use Magenx\Blog\Model\PostFactory;
use Magenx\Blog\Model\PostRepository;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;

class Edit extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Magenx_Blog::post';

    private Registry $coreRegistry;
    private PostRepository $postRepository;
    private PostFactory $postFactory;

    public function __construct(
        Action\Context $context,
        Registry $coreRegistry,
        PostRepository $postRepository,
        PostFactory $postFactory
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->postRepository = $postRepository;
        $this->postFactory = $postFactory;
    }

    public function execute()
    {
        $postId = (int) $this->getRequest()->getParam('post_id');

        if ($postId) {
            try {
                $post = $this->postRepository->getById($postId);
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('This blog post no longer exists.'));

                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

                return $resultRedirect->setPath('*/*/');
            }
        } else {
            $post = $this->postFactory->create();
        }

        $this->coreRegistry->register('magenx_blog_post', $post);

        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Magenx_Blog::post');
        $resultPage->getConfig()->getTitle()->prepend(
            $postId ? $post->getTitle() : __('New Blog Post')
        );

        return $resultPage;
    }
}
