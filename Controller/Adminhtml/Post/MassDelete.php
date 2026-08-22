<?php

declare(strict_types=1);

namespace Magenx\Blog\Controller\Adminhtml\Post;

use Magenx\Blog\Model\PostRepository;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * The listing grid is a legacy Widget\Grid\Extended, whose massaction posts the
 * selected ids as a comma-separated string under its form field name — NOT a
 * UI-component selection, so Magento\Ui\Component\MassAction\Filter cannot
 * resolve it.
 */
class MassDelete extends Action implements HttpPostActionInterface
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

        $deleted = 0;
        foreach ($this->getSelectedIds() as $postId) {
            try {
                $this->postRepository->delete($this->postRepository->getById($postId));
                $deleted++;
            } catch (NoSuchEntityException $e) {
                continue;
            }
        }

        if ($deleted) {
            $this->messageManager->addSuccessMessage(__('A total of %1 blog post(s) have been deleted.', $deleted));
        } else {
            $this->messageManager->addErrorMessage(__('Please select at least one blog post to delete.'));
        }

        return $resultRedirect->setPath('*/*/');
    }

    /** @return int[] */
    private function getSelectedIds(): array
    {
        $selected = $this->getRequest()->getParam('post_id');
        if (!is_array($selected)) {
            $selected = explode(',', (string) $selected);
        }

        return array_values(array_unique(array_filter(array_map('intval', $selected))));
    }
}
