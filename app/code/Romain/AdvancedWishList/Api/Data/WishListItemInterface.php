<?php
/**
 * Copyright © 2025 Romain ITOFO. Tous droits réservés.
 *
 * @author  Romain ITOFO
 * @license Propriétaire
 */

declare(strict_types=1);

namespace Romain\AdvancedWishList\Api\Data;

/**
 * Interface WishListItemInterface
 */
interface WishListItemInterface
{
    public const ITEM_ID      = 'item_id';
    public const WISHLIST_ID  = 'wishlist_id';
    public const PRODUCT_ID   = 'product_id';
    public const STORE_ID     = 'store_id';
    public const ADDED_AT     = 'added_at';
    public const DESCRIPTION  = 'description';
    public const QTY          = 'qty';
    public const PRICE_ALERT  = 'price_alert';
    public const TARGET_PRICE = 'target_price';

    /**
     * @return int|null
     */
    public function getItemId(): ?int;

    /**
     * @param int $itemId
     *
     * @return WishListItemInterface
     */
    public function setItemId(int $itemId): WishListItemInterface;

    /**
     * @return int
     */
    public function getWishlistId(): int;

    /**
     * @param int $wishlistId
     *
     * @return WishListItemInterface
     */
    public function setWishlistId(int $wishlistId): WishListItemInterface;

    /**
     * @return int
     */
    public function getProductId(): int;

    /**
     * @param int $productId
     *
     * @return WishListItemInterface
     */
    public function setProductId(int $productId): WishListItemInterface;

    /**
     * @return int
     */
    public function getStoreId(): int;

    /**
     * @param int $storeId
     *
     * @return WishListItemInterface
     */
    public function setStoreId(int $storeId): WishListItemInterface;

    /**
     * @return string
     */
    public function getAddedAt(): string;

    /**
     * @param string $addedAt
     *
     * @return WishListItemInterface
     */
    public function setAddedAt(string $addedAt): WishListItemInterface;

    /**
     * @return string|null
     */
    public function getDescription(): ?string;

    /**
     * @param string|null $description
     *
     * @return WishListItemInterface
     */
    public function setDescription(?string $description): WishListItemInterface;

    /**
     * @return float
     */
    public function getQty(): float;

    /**
     * @param float $qty
     *
     * @return WishListItemInterface
     */
    public function setQty(float $qty): WishListItemInterface;

    /**
     * @return bool
     */
    public function isPriceAlert(): bool;

    /**
     * @param bool $priceAlert
     *
     * @return WishListItemInterface
     */
    public function setPriceAlert(bool $priceAlert): WishListItemInterface;

    /**
     * @return float|null
     */
    public function getTargetPrice(): ?float;

    /**
     * @param float|null $targetPrice
     *
     * @return WishListItemInterface
     */
    public function setTargetPrice(?float $targetPrice): WishListItemInterface;
}
