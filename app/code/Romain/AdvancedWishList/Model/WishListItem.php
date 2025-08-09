<?php
/**
 * Copyright © 2025 Romain ITOFO. Tous droits réservés.
 *
 * @author  Romain ITOFO
 * @license Propriétaire
 */

declare(strict_types=1);

namespace Romain\AdvancedWishList\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Romain\AdvancedWishList\Api\Data\WishListItemInterface;

/**
 *  WishListItem model
 */
class WishListItem extends AbstractModel implements WishListItemInterface, IdentityInterface
{
    /**
     * Cache tag
     */
    public const CACHE_TAG = 'advanced_wishlist_item';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * @var string
     */
    protected $_eventPrefix = 'advanced_wishlist_item';

    /**
     * @throws LocalizedException
     */
    protected function _construct()
    {
        $this->_init(\Romain\AdvancedWishList\Model\ResourceModel\WishListItem::class);
    }

    public function getIdentities(): array
    {
        $identities = [
            // ⭐ Cache spécifique à cet item
            self::CACHE_TAG . '_' . $this->getId(),

            // ⭐ Cache des items de cette wishlist
            'advanced_wishlist_items_' . $this->getWishlistId(),

            // ⭐ Cache général des items
            'advanced_wishlist_items',

            // ⭐ Cache de la wishlist parente
            'advanced_wishlist_' . $this->getWishlistId(),
        ];

        // ⭐ Si on peut récupérer le customer_id, l'ajouter
        if ($this->getCustomerId()) {
            $identities[] = 'customer_wishlist_items_' . $this->getCustomerId();
        }

        return $identities;
    }

    public function getItemId(): ?int
    {
        return $this->getData(self::ITEM_ID) ? (int)$this->getData(self::ITEM_ID) : null;
    }

    public function setItemId(int $itemId): WishListItemInterface
    {
        return $this->setData(self::ITEM_ID, $itemId);
    }

    public function getWishlistId(): int
    {
        return (int)$this->getData(self::WISHLIST_ID);
    }

    public function setWishlistId(int $wishlistId): WishListItemInterface
    {
        return $this->setData(self::WISHLIST_ID, $wishlistId);
    }

    public function getProductId(): int
    {
        return (int)$this->getData(self::PRODUCT_ID);
    }

    public function setProductId(int $productId): WishListItemInterface
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    public function getStoreId(): int
    {
        return (int)$this->getData(self::STORE_ID);
    }

    public function setStoreId(int $storeId): WishListItemInterface
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    public function getAddedAt(): string
    {
        return (string)$this->getData(self::ADDED_AT);
    }

    public function setAddedAt(string $addedAt): WishListItemInterface
    {
        return $this->setData(self::ADDED_AT, $addedAt);
    }

    public function getDescription(): ?string
    {
        return $this->getData(self::DESCRIPTION);
    }

    public function setDescription(?string $description): WishListItemInterface
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    public function getQty(): float
    {
        return (float)$this->getData(self::QTY);
    }

    public function setQty(float $qty): WishListItemInterface
    {
        return $this->setData(self::QTY, $qty);
    }

    public function isPriceAlert(): bool
    {
        return (bool)$this->getData(self::PRICE_ALERT);
    }

    public function setPriceAlert(bool $priceAlert): WishListItemInterface
    {
        return $this->setData(self::PRICE_ALERT, $priceAlert);
    }

    public function getTargetPrice(): ?float
    {
        $price = $this->getData(self::TARGET_PRICE);

        return $price !== null ? (float)$price : null;
    }

    public function setTargetPrice(?float $targetPrice): WishListItemInterface
    {
        return $this->setData(self::TARGET_PRICE, $targetPrice);
    }

    /**
     * Get customer ID (peut être récupéré via la wishlist parente)
     */
    public function getCustomerId(): ?int
    {
        return $this->getData('customer_id') ? (int)$this->getData('customer_id') : null;
    }
}
