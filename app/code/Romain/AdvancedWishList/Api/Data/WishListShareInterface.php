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
 * Interface WishListShareInterface
 */
interface WishListShareInterface
{
    public const SHARE_ID          = 'share_id';
    public const WISHLIST_ID       = 'wishlist_id';
    public const SHARED_WITH_EMAIL = 'shared_with_email';
    public const SHARE_MESSAGE     = 'share_message';
    public const SHARED_AT         = 'shared_at';
    public const VIEW_COUNT        = 'view_count';

    /**
     * @return int|null
     */
    public function getShareId(): ?int;

    /**
     * @param int $shareId
     *
     * @return WishListShareInterface
     */
    public function setShareId(int $shareId): WishListShareInterface;

    /**
     * @return int
     */
    public function getWishlistId(): int;

    /**
     * @param int $wishlistId
     *
     * @return WishListShareInterface
     */
    public function setWishlistId(int $wishlistId): WishListShareInterface;

    /**
     * @return string|null
     */
    public function getSharedWithEmail(): ?string;

    /**
     * @param string|null $email
     *
     * @return WishListShareInterface
     */
    public function setSharedWithEmail(?string $email): WishListShareInterface;

    /**
     * @return string|null
     */
    public function getShareMessage(): ?string;

    /**
     * @param string|null $message
     *
     * @return WishListShareInterface
     */
    public function setShareMessage(?string $message): WishListShareInterface;

    /**
     * @return string
     */
    public function getSharedAt(): string;

    /**
     * @param string $sharedAt
     *
     * @return WishListShareInterface
     */
    public function setSharedAt(string $sharedAt): WishListShareInterface;

    /**
     * @return int
     */
    public function getViewCount(): int;

    /**
     * @param int $viewCount
     *
     * @return WishListShareInterface
     */
    public function setViewCount(int $viewCount): WishListShareInterface;

    /**
     * @return WishListShareInterface
     */
    public function incrementViewCount(): WishListShareInterface;
}
