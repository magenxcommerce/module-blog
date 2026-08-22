<?php

declare(strict_types=1);

namespace Magenx\Blog\Controller\Adminhtml\Tag;

use Magenx\Blog\Model\ResourceModel\Tag\CollectionFactory;
use Magenx\Blog\Model\TagRepository;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\Component\MassAction\Filter;

class MassDelete extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Magenx_Blog::tag';

    private Filter $filter;
    private TagRepository $tagRepository;
    private CollectionFactory $collectionFactory;

    public function __construct(
        Action\Context $context,
        Filter $filter,
        TagRepository $tagRepository,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->tagRepository = $tagRepository;
        $this->collectionFactory = $collectionFactory;
    }

    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $deleted = 0;
        foreach ($collection->getItems() as $tag) {
            try {
                $this->tagRepository->delete($this->tagRepository->getById((int) $tag->getId()));
                $deleted++;
            } catch (NoSuchEntityException $e) {
                continue;
            }
        }

        if ($deleted) {
            $this->messageManager->addSuccessMessage(__('A total of %1 blog tags have been deleted.', $deleted));
        }

        return $resultRedirect->setPath('*/*/');
    }
}
