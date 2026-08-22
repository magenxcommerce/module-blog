<?php

declare(strict_types=1);

namespace Magenx\Blog\Controller\Adminhtml\Tag;

use Magenx\Blog\Model\TagRepository;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class Delete extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Magenx_Blog::tag';

    private TagRepository $tagRepository;

    public function __construct(Action\Context $context, TagRepository $tagRepository)
    {
        parent::__construct($context);
        $this->tagRepository = $tagRepository;
    }

    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $tagId = (int) $this->getRequest()->getParam('tag_id');

        try {
            $this->tagRepository->delete($this->tagRepository->getById($tagId));
            $this->messageManager->addSuccessMessage(__('The blog tag has been deleted.'));
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('This blog tag no longer exists.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong while deleting the blog tag.'));
        }

        return $resultRedirect->setPath('*/*/');
    }
}
