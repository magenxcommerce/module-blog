<?php

declare(strict_types=1);

namespace Magenx\Blog\Controller\Adminhtml\Post\Product;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * The chooser grid as a layout fragment: the modal's initial load, and its
 * filter / sort / paging reloads.
 *
 * Both HTTP methods are allowed on purpose. The modal fetches the first
 * render with jQuery $.get, but every later reload goes through the legacy
 * grid stack — varienGrid::reload() uses Prototype's Ajax.Request, which
 * defaults to POST (and posts form_key with it). Declaring GET alone made
 * Magento's request validator reject those reloads with NotFoundException, so
 * filtering or paging inside the modal rendered a 404.
 */
class Grid extends Action implements HttpGetActionInterface, HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Magenx_Blog::post';

    public function execute()
    {
        return $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
    }
}
