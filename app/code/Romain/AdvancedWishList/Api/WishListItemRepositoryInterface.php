<?php
/**
 * Copyright © 2025 Romain ITOFO. Tous droits réservés.
 *
 * @author  Romain ITOFO
 * @license Propriétaire
 */

declare(strict_types=1);

namespace Romain\AdvancedWishList\Api;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Romain\AdvancedWishList\Api\Data\WishListItemInterface;

/**
 * Interface WishListItemRepositoryInterface
 */
interface WishListItemRepositoryInterface
{
    /**
     * Save wishlist item
     *
     * @param WishListItemInterface $wishListItem
     *
     * @return WishListItemInterface
     * @throws CouldNotSaveException
     */
    public function save(WishListItemInterface $wishListItem): WishListItemInterface;

    /**
     * Get wishlist item by ID
     *
     * @param int $itemId
     *
     * @return WishListItemInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $itemId): WishListItemInterface;

    /**
     * Delete wishlist item
     *
     * @param WishListItemInterface $wishListItem
     *
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(WishListItemInterface $wishListItem): bool;

    /**
     * Delete wishlist item by ID
     *
     * @param int $itemId
     *
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function deleteById(int $itemId): bool;

    /**
     * Get items by wishlist ID
     *
     * @param int $wishlistId
     *
     * @return WishListItemInterface[]
     */
    public function getByWishlistId(int $wishlistId): array;

    /**
     * Add product to wishlist
     *
     * @param int $wishlistId
     * @param int $productId
     * @param int $storeId
     * @param array $options
     *
     * @return WishListItemInterface
     * @throws CouldNotSaveException
     */
    public function addProductToWishlist(int   $wishlistId,
                                         int   $productId,
                                         int   $storeId,
                                         array $options = []
    ): WishListItemInterface;

    /**
     * Remove product from wishlist
     *
     * @param int $wishlistId
     * @param int $itemId
     *
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function removeItemFromWishlist(int $wishlistId, int $itemId): bool;

    /**
     * Check if product is in wishlist
     *
     * @param int $wishlistId
     * @param int $productId
     *
     * @return bool
     */
    public function isProductInWishlist(int $wishlistId, int $productId): bool;

    /**
     * Get items with price alerts
     *
     * @return WishListItemInterface[]
     */
    public function getItemsWithPriceAlerts(): array;
}
