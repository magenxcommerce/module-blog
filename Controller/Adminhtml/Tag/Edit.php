<?php

declare(strict_types=1);

namespace Magenx\Blog\Controller\Adminhtml\Tag;

use Magenx\Blog\Model\TagFactory;
use Magenx\Blog\Model\TagRepository;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;

class Edit extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Magenx_Blog::tag';

    private Registry $coreRegistry;
    private TagRepository $tagRepository;
    private TagFactory $tagFactory;

    public function __construct(
        Action\Context $context,
        Registry $coreRegistry,
        TagRepository $tagRepository,
        TagFactory $tagFactory
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->tagRepository = $tagRepository;
        $this->tagFactory = $tagFactory;
    }

    public function execute()
    {
        $tagId = (int) $this->getRequest()->getParam('tag_id');

        if ($tagId) {
            try {
                $tag = $this->tagRepository->getById($tagId);
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('This blog tag no longer exists.'));
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

                return $resultRedirect->setPath('*/*/');
            }
        } else {
            $tag = $this->tagFactory->create();
        }

        $this->coreRegistry->register('magenx_blog_tag', $tag);

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Magenx_Blog::tag');
        $resultPage->getConfig()->getTitle()->prepend($tagId ? $tag->getName() : __('New Blog Tag'));

        return $resultPage;
    }
}
