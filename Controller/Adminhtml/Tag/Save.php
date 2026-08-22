<?php

declare(strict_types=1);

namespace Magenx\Blog\Controller\Adminhtml\Tag;

use Magenx\Blog\Model\TagFactory;
use Magenx\Blog\Model\TagRepository;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Save extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Magenx_Blog::tag';

    private TagRepository $tagRepository;
    private TagFactory $tagFactory;

    public function __construct(Action\Context $context, TagRepository $tagRepository, TagFactory $tagFactory)
    {
        parent::__construct($context);
        $this->tagRepository = $tagRepository;
        $this->tagFactory = $tagFactory;
    }

    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            return $resultRedirect->setPath('*/*/');
        }

        $tagId = (int) ($data['tag_id'] ?? 0);

        try {
            $tag = $tagId ? $this->tagRepository->getById($tagId) : $this->tagFactory->create();
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('This blog tag no longer exists.'));

            return $resultRedirect->setPath('*/*/');
        }

        $tag->addData([
            'name' => trim((string) ($data['name'] ?? '')),
            'url_key' => trim((string) ($data['url_key'] ?? '')),
            'description' => $data['description'] ?? null,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
        ]);

        try {
            if ($tag->getName() === '') {
                throw new LocalizedException(__('Name is required.'));
            }
            if ($tag->getUrlKey() === '') {
                throw new LocalizedException(__('URL Key is required.'));
            }

            $this->tagRepository->save($tag);
            $this->messageManager->addSuccessMessage(__('The blog tag has been saved.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            return $resultRedirect->setPath('*/*/edit', ['tag_id' => $tagId ?: null]);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong while saving the blog tag.'));

            return $resultRedirect->setPath('*/*/edit', ['tag_id' => $tagId ?: null]);
        }

        if ($this->getRequest()->getParam('back')) {
            return $resultRedirect->setPath('*/*/edit', ['tag_id' => $tag->getId()]);
        }

        return $resultRedirect->setPath('*/*/');
    }
}
