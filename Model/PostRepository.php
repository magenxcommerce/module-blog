<?php

declare(strict_types=1);

namespace Magenx\Blog\Model;

use Magenx\Blog\Model\ResourceModel\Post as PostResource;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Thin repository over the Post model + its resource-model relation
 * lookups, used by the admin controllers and the GraphQL resolvers.
 */
class PostRepository
{
    private PostFactory $postFactory;
    private PostResource $postResource;

    public function __construct(PostFactory $postFactory, PostResource $postResource)
    {
        $this->postFactory = $postFactory;
        $this->postResource = $postResource;
    }

    public function getById(int $postId): Post
    {
        $post = $this->postFactory->create();
        $this->postResource->load($post, $postId);
        if (!$post->getId()) {
            throw new NoSuchEntityException(__('The blog post with ID "%1" does not exist.', $postId));
        }

        return $post;
    }

    public function getByUrlKey(string $urlKey): Post
    {
        $post = $this->postFactory->create();
        $this->postResource->load($post, $urlKey, 'url_key');
        if (!$post->getId()) {
            throw new NoSuchEntityException(__('The blog post with URL key "%1" does not exist.', $urlKey));
        }

        return $post;
    }

    public function save(Post $post): Post
    {
        $this->postResource->save($post);

        return $post;
    }

    public function delete(Post $post): void
    {
        $this->postResource->delete($post);
    }

    /** @return array<int, int> Category IDs assigned to the post. */
    public function getCategoryIds(int $postId): array
    {
        return $this->postResource->getCategoryIds($postId);
    }

    /** @return array<int, int> Tag IDs assigned to the post. */
    public function getTagIds(int $postId): array
    {
        return $this->postResource->getTagIds($postId);
    }

    /** @return array<int, int> Store IDs the post is assigned to (0 = all store views). */
    public function getStoreIds(int $postId): array
    {
        return $this->postResource->getStoreIds($postId);
    }

    /** @return array<int, int> Related product ID => sort position, ordered by position. */
    public function getProductPositions(int $postId): array
    {
        return $this->postResource->getProductPositions($postId);
    }

    /** @param array<int, int> $positions Product ID => sort position. */
    public function saveProductPositions(int $postId, array $positions): void
    {
        $this->postResource->saveProductPositions($postId, $positions);
    }

    public function removeProduct(int $postId, int $productId): void
    {
        $positions = $this->postResource->getProductPositions($postId);
        unset($positions[$productId]);
        $this->postResource->saveProductPositions($postId, $positions);
    }

    /** @param int[] $postIds @return array<int, int[]> Post ID => category IDs. */
    public function getCategoryIdsForPosts(array $postIds): array
    {
        return $this->postResource->getCategoryIdsForPosts($postIds);
    }

    /** @param int[] $postIds @return array<int, int[]> Post ID => tag IDs. */
    public function getTagIdsForPosts(array $postIds): array
    {
        return $this->postResource->getTagIdsForPosts($postIds);
    }

    /** @param int[] $postIds @return array<int, array<int, int>> Post ID => [productId => position]. */
    public function getProductPositionsForPosts(array $postIds): array
    {
        return $this->postResource->getProductPositionsForPosts($postIds);
    }

    /** @param int[] $postIds @return array<int, array<int, int>> Post ID => [relatedPostId => position]. */
    public function getRelatedPostPositionsForPosts(array $postIds): array
    {
        return $this->postResource->getRelatedPostPositionsForPosts($postIds);
    }

    public function moveProduct(int $postId, int $productId, string $direction): void
    {
        $positions = $this->postResource->getProductPositions($postId);
        $ids = array_keys($positions);
        $index = array_search($productId, $ids, true);
        if ($index === false) {
            return;
        }
        $swapWith = $direction === 'up' ? $index - 1 : $index + 1;
        if (!isset($ids[$swapWith])) {
            return;
        }
        [$ids[$index], $ids[$swapWith]] = [$ids[$swapWith], $ids[$index]];
        $this->postResource->saveProductPositions($postId, array_flip($ids));
    }

    /** @return array<int, int> Related post ID => sort position, ordered by position. */
    public function getRelatedPostPositions(int $postId): array
    {
        return $this->postResource->getRelatedPostPositions($postId);
    }
}
