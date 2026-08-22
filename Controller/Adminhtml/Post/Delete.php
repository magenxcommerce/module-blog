<?php

declare(strict_types=1);

namespace Magenx\Blog\Controller\Adminhtml\Post;

use Magenx\Blog\Model\PostRepository;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class Delete extends Action implements HttpPostActionInterface
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

        try {
            $this->postRepository->delete($this->postRepository->getById($postId));
            $this->messageManager->addSuccessMessage(__('The blog post has been deleted.'));
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('This blog post no longer exists.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong while deleting the blog post.'));
        }

        return $resultRedirect->setPath('*/*/');
    }
}
