<?php

declare(strict_types=1);

namespace Magenx\Blog\Controller\Adminhtml\Post;

use Magenx\Blog\Model\PostRepository;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\Component\MassAction\Filter;

class MassDelete extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Magenx_Blog::post';

    private Filter $filter;
    private PostRepository $postRepository;
    private \Magenx\Blog\Model\ResourceModel\Post\CollectionFactory $collectionFactory;

    public function __construct(
        Action\Context $context,
        Filter $filter,
        PostRepository $postRepository,
        \Magenx\Blog\Model\ResourceModel\Post\CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->postRepository = $postRepository;
        $this->collectionFactory = $collectionFactory;
    }

    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $deleted = 0;
        foreach ($collection->getItems() as $post) {
            try {
                $this->postRepository->delete($this->postRepository->getById((int) $post->getId()));
                $deleted++;
            } catch (NoSuchEntityException $e) {
                continue;
            }
        }

        if ($deleted) {
            $this->messageManager->addSuccessMessage(__('A total of %1 blog post(s) have been deleted.', $deleted));
        }

        return $resultRedirect->setPath('*/*/');
    }
}
