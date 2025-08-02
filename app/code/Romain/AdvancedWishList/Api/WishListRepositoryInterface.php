<?php
/**
 * Copyright © 2025 Romain ITOFO. Tous droits réservés.
 *
 * @author  Romain ITOFO
 * @license Propriétaire
 */

declare(strict_types=1);

namespace Romain\AdvancedWishList\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Romain\AdvancedWishList\Api\Data\WishListInterface;

/**
 * Interface WishListRepositoryInterface
 */
interface WishListRepositoryInterface
{
    /**
     * Save wishlist.
     *
     * @param WishListInterface $wishlist
     *
     * @return WishListInterface
     */
    public function save(WishListInterface $wishlist): WishListInterface;

    /**
     * Get wishlist by ID.
     *
     * @param int $id
     *
     * @return WishListInterface
     */
    public function getById(int $id): WishListInterface;

    /**
     * Delete wishlist.
     *
     * @param WishListInterface $wishlist
     *
     * @return bool
     */
    public function delete(WishListInterface $wishlist): bool;

    /**
     * Delete wishlist by ID.
     *
     * @param int $id
     *
     * @return bool
     */
    public function deleteById(int $id): bool;

    /**
     * Get wishlist list.
     *
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface;

    /**
     * Get wishlist by customer ID.
     *
     * @param int $customerId
     *
     * @return WishListInterface[]
     */
    public function getByCustomerId(int $customerId): array;

    /**
     * Get wishlist by customer ID.
     *
     * @return WishListInterface[]
     */
    public function getAll(): array;

    /**
     * @param int $customerId
     * @param int $wishlistId
     *
     * @return void
     */
    public function setDefaultWishList(int $customerId, int $wishlistId): void;

    /**
     * @param string $shareCode
     *
     * @return WishListInterface|null
     */
    public function getByShareCode(string $shareCode): ?WishListInterface;

    /**
     * @return array
     */
    public function getAllWishlistIds(): array;
}
