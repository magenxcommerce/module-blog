<?php

declare(strict_types=1);

namespace Magenx\Blog\Controller\Adminhtml\Post;

use Magenx\Blog\Model\MediaUrl;
use Magenx\Blog\Model\PostFactory;
use Magenx\Blog\Model\PostRepository;
use Magenx\Blog\Model\UrlKey;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;

class Save extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Magenx_Blog::post';

    private PostRepository $postRepository;
    private PostFactory $postFactory;
    private UrlKey $urlKey;
    private UploaderFactory $uploaderFactory;
    private AdapterFactory $imageAdapterFactory;
    private Filesystem $filesystem;
    private MediaUrl $mediaUrl;

    public function __construct(
        Action\Context $context,
        PostRepository $postRepository,
        PostFactory $postFactory,
        UrlKey $urlKey,
        UploaderFactory $uploaderFactory,
        AdapterFactory $imageAdapterFactory,
        Filesystem $filesystem,
        MediaUrl $mediaUrl
    ) {
        parent::__construct($context);
        $this->postRepository = $postRepository;
        $this->postFactory = $postFactory;
        $this->urlKey = $urlKey;
        $this->uploaderFactory = $uploaderFactory;
        $this->imageAdapterFactory = $imageAdapterFactory;
        $this->filesystem = $filesystem;
        $this->mediaUrl = $mediaUrl;
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
            // A media path picked in the gallery ("/media/blog/hero.jpg"),
            // stored exactly as inserted.
            'image' => trim((string) ($data['image'] ?? '')) ?: null,
            'url_key' => $this->urlKey->normalize(
                (string) ($data['url_key'] ?? ''),
                (string) ($data['title'] ?? '')
            ),
            'publish_date' => $data['publish_date'] ?? null,
            'is_active' => (int) ($data['is_active'] ?? 0),
            'author_name' => $data['author_name'] ?? null,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
        ]);

        $post->setData('category_ids', $this->toIntArray($data['category_ids'] ?? []));
        $post->setData('tag_ids', $this->toIntArray($data['tag_ids'] ?? []));
        $post->setData('store_ids', $this->toStoreIds($data['store_ids'] ?? null));

        // Flipping the posted list gives [postId => position] directly, and is
        // empty-safe — array_combine() with range(0, -1) throws when nothing is
        // selected, which is the common case.
        $post->setData(
            'related_post_positions',
            array_flip($this->toIntArray($data['related_post_ids'] ?? []))
        );

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

            return $resultRedirect->setPath('*/*/edit', ['post_id' => $postId ?: null]);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong while saving the blog post.'));

            return $resultRedirect->setPath('*/*/edit', ['post_id' => $postId ?: null]);
        }

        if ($this->getRequest()->getParam('back')) {
            return $resultRedirect->setPath('*/*/edit', ['post_id' => $post->getId()]);
        }

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Store ids need their own normalization: 0 means "All Store Views" and is
     * a real, selectable value, but toIntArray()'s array_filter() treats it as
     * empty and drops it. That left every post saved with the default
     * selection with no rows in magenx_blog_post_store — and since the
     * storefront collection inner-joins that table, such a post is invisible
     * on the storefront while still listed in the admin grid. Nothing posted
     * also means all store views.
     *
     * @return int[]
     */
    private function toStoreIds($value): array
    {
        if (!is_array($value)) {
            return [0];
        }

        $ids = array_values(array_unique(array_map('intval', $value)));

        return $ids ?: [0];
    }

    /**
     * Works out what magenx_blog_post.image should hold after this save.
     *
     * The admin image element posts three things, any of which may be absent:
     * an uploaded file in $_FILES, a hidden image[value] carrying what was
     * already set (as the absolute URL the form rendered), and image[delete]
     * when the "Delete Image" box is ticked. A new upload wins; then a delete;
     * otherwise the existing path is kept.
     *
     * An upload that fails must not take the rest of the post down with it —
     * the admin gets an error message and the previous image stays.
     */
    private function resolveImage(array $data, ?string $currentImage): ?string
    {
        $posted = is_array($data['image'] ?? null) ? $data['image'] : [];

        if ($this->hasUploadedFile()) {
            try {
                return $this->uploadImage();
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('The headline image could not be uploaded: %1', $e->getMessage())
                );

                return $currentImage;
            }
        }

        if (!empty($posted['delete'])) {
            return null;
        }

        if (isset($posted['value'])) {
            return $this->mediaUrl->toPath((string) $posted['value']);
        }

        return $currentImage;
    }

    private function hasUploadedFile(): bool
    {
        $file = $this->getRequest()->getFiles('image');

        return is_array($file)
            && !empty($file['name'])
            && (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK;
    }

    /** @return string media-relative path of the stored file */
    private function uploadImage(): string
    {
        $uploader = $this->uploaderFactory->create(['fileId' => 'image']);
        $uploader->setAllowedExtensions(['jpg', 'jpeg', 'png', 'gif', 'webp']);
        $uploader->setAllowRenameFiles(true);
        // Flat directory, not Magento's a/b/abc.jpg dispersion: the path is
        // read back by a headless storefront, where a predictable URL is worth
        // more than spreading files across subdirectories.
        $uploader->setFilesDispersion(false);
        $uploader->setAllowCreateFolders(true);
        // Extension alone is not proof of an image; the adapter has to be able
        // to actually open the file. SVG is deliberately not in the list above:
        // it is markup, it would be served from the media domain verbatim, and
        // no image adapter can vet it.
        $uploader->addValidateCallback('image', $this, 'validateUploadedImage');

        $subdirectory = $this->mediaUrl->getSubdirectory();
        $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $result = $uploader->save($mediaDirectory->getAbsolutePath($subdirectory));

        return $subdirectory . '/' . ltrim((string) $result['file'], '/');
    }

    /**
     * Uploader validate callback — public because the uploader calls it back
     * by name on this object.
     */
    public function validateUploadedImage(string $filePath): void
    {
        $this->imageAdapterFactory->create()->validateUploadFile($filePath);
    }

    /** @return int[] */
    private function toIntArray($value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return array_values(array_filter(array_map('intval', $value)));
    }
}
