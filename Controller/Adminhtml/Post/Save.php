<?php

declare(strict_types=1);

namespace Magenx\Blog\Controller\Adminhtml\Post;

use Magenx\Blog\Model\PostFactory;
use Magenx\Blog\Model\PostRepository;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Save extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Magenx_Blog::post';

    private PostRepository $postRepository;
    private PostFactory $postFactory;

    public function __construct(
        Action\Context $context,
        PostRepository $postRepository,
        PostFactory $postFactory
    ) {
        parent::__construct($context);
        $this->postRepository = $postRepository;
        $this->postFactory = $postFactory;
    }

    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            return $resultRedirect->setPath('*/*/');
        }

        $postId = (int) ($data['post_id'] ?? 0);

        try {
            $post = $postId ? $this->postRepository->getById($postId) : $this->postFactory->create();
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('This blog post no longer exists.'));

            return $resultRedirect->setPath('*/*/');
        }

        $post->addData([
            'title' => trim((string) ($data['title'] ?? '')),
            'short_description' => $data['short_description'] ?? null,
            'content' => $data['content'] ?? null,
            'image' => $data['image'] ?? null,
            'url_key' => trim((string) ($data['url_key'] ?? '')),
            'publish_date' => $data['publish_date'] ?? null,
            'is_active' => (int) ($data['is_active'] ?? 0),
            'author_name' => $data['author_name'] ?? null,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
        ]);

        $post->setData('category_ids', $this->toIntArray($data['category_ids'] ?? []));
        $post->setData('tag_ids', $this->toIntArray($data['tag_ids'] ?? []));
        $post->setData('store_ids', $this->toIntArray($data['store_ids'] ?? [0]));

        $relatedIds = $this->toIntArray($data['related_post_ids'] ?? []);
        $post->setData('related_post_positions', array_combine($relatedIds, range(0, count($relatedIds) - 1)) ?: []);

        try {
            if ($post->getTitle() === '') {
                throw new LocalizedException(__('Title is required.'));
            }
            if ($post->getUrlKey() === '') {
                throw new LocalizedException(__('URL Key is required.'));
            }

            $this->postRepository->save($post);
            $this->messageManager->addSuccessMessage(__('The blog post has been saved.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->getDataPersistor()->set('magenx_blog_post', $data);

            return $resultRedirect->setPath('*/*/edit', ['post_id' => $postId ?: null]);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong while saving the blog post.'));
            $this->getDataPersistor()->set('magenx_blog_post', $data);

            return $resultRedirect->setPath('*/*/edit', ['post_id' => $postId ?: null]);
        }

        if ($this->getRequest()->getParam('back')) {
            return $resultRedirect->setPath('*/*/edit', ['post_id' => $post->getId()]);
        }

        return $resultRedirect->setPath('*/*/');
    }

    /** @return int[] */
    private function toIntArray($value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return array_values(array_filter(array_map('intval', $value)));
    }

    private function getDataPersistor(): \Magento\Framework\App\Request\DataPersistorInterface
    {
        return $this->_objectManager->get(\Magento\Framework\App\Request\DataPersistorInterface::class);
    }
}
