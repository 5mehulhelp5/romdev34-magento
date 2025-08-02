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
 * Interface WishListInterface
 */
interface WishListInterface
{
    public const WISHLIST_ID = 'wishlist_id';
    public const CUSTOMER_ID = 'customer_id';
    public const NAME        = 'name';
    public const DESCRIPTION = 'description';
    public const IS_PUBLIC   = 'is_public';
    public const SHARE_CODE  = 'share_code';
    public const IS_DEFAULT  = 'is_default';
    public const CREATED_AT  = 'created_at';
    public const UPDATED_AT  = 'updated_at';

    /**
     * Get wishlist ID
     *
     * @return string|null
     */
    public function getId(): ?string;


    /**
     * Get customer ID
     *
     * @return int
     */
    public function getCustomerId(): int;

    /**
     * Set customer ID
     *
     * @param int $customerId
     *
     * @return $this
     */
    public function setCustomerId(int $customerId): self;

    /**
     * Get name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Set name
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name): self;

    /**
     * Get description
     *
     * @return string|null
     */
    public function getDescription(): ?string;

    /**
     * Set description
     *
     * @param string|null $description
     *
     * @return $this
     */
    public function setDescription(?string $description): self;

    /**
     * Is wishlist public
     *
     * @return bool
     */
    public function isPublic(): bool;

    /**
     * Set is public
     *
     * @param bool $isPublic
     *
     * @return $this
     */
    public function setIsPublic(bool $isPublic): self;

    /**
     * Get share code
     *
     * @return string|null
     */
    public function getShareCode(): ?string;

    /**
     * Set share code
     *
     * @param string|null $shareCode
     *
     * @return $this
     */
    public function setShareCode(?string $shareCode): self;

    /**
     * Is default wishlist
     *
     * @return bool
     */
    public function isDefault(): bool;

    /**
     * Set is default
     *
     * @param bool $isDefault
     *
     * @return $this
     */
    public function setIsDefault(bool $isDefault): self;

    /**
     * Get created at
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string;


    /**
     * Get updated at
     *
     * @return string|null
     */
    public function getUpdatedAt(): ?string;

    /**
     * @return array
     */
    public function getIdentities(): array;
}
