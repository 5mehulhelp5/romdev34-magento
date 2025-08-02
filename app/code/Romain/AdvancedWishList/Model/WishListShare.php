<?php
/**
 * Copyright © 2025 Romain ITOFO. Tous droits réservés.
 *
 * @author  Romain ITOFO
 * @license Propriétaire
 */

declare(strict_types=1);

namespace Romain\AdvancedWishList\Model;

use Magento\Framework\Data\Collection\AbstractDb as AbstractDbCollection;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Romain\AdvancedWishList\Api\Data\WishListShareInterface;
use Romain\AdvancedWishList\Api\WishListRepositoryInterface;

/**
 *  Model WishListShare
 */
class WishListShare extends AbstractModel implements WishListShareInterface,
                                                     \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * Cache tag
     */
    public const CACHE_TAG = 'advanced_wishlist_share';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * @var string
     */
    protected $_eventPrefix = 'advanced_wishlist_share';

    public function __construct(
        Context                                      $context,
        Registry                                     $registry,
        private readonly WishListRepositoryInterface $wishListRepository,
        ?AbstractResource                            $resource = null,
        ?AbstractDbCollection                        $resourceCollection = null,
        array                                        $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct(): void
    {
        $this->_init(\Romain\AdvancedWishList\Model\ResourceModel\WishListShare::class);
    }

    /**
     * @return string[]
     */
    public function getIdentities(): array
    {
        $identities = [
            self::CACHE_TAG . '_' . $this->getId()
        ];

        // Ajouter les tags liés à la wishlist partagée
        if ($this->getWishlistId()) {
            $identities[] = 'advanced_wishlist_' . $this->getWishlistId();
            $identities[] = 'wishlist_share_' . $this->getWishlistId();

            // Récupérer le customer ID via la wishlist
            try {
                $wishlist = $this->wishListRepository->getById($this->getWishlistId());
                $customer_id = $wishlist->getCustomerId();

                $identities[] = 'customer_wishlist_' . $customer_id;
                $identities[] = 'advanced_wishlist_customer_' . $customer_id;
                $identities[] = 'wishlist_share_customer_' . $customer_id;
            } catch (\Exception $e) {
                // Ignore errors in identity generation
            }
        }

        return $identities;
    }

    /**
     * @return int|null
     */
    public function getShareId(): ?int
    {
        return $this->getData(self::SHARE_ID) ? (int)$this->getData(self::SHARE_ID) : null;
    }

    /**
     * @param int $shareId
     *
     * @return WishListShareInterface
     */
    public function setShareId(int $shareId): WishListShareInterface
    {
        return $this->setData(self::SHARE_ID, $shareId);
    }

    /**
     * @return int
     */
    public function getWishlistId(): int
    {
        return (int)$this->getData(self::WISHLIST_ID);
    }

    /**
     * @param int $wishlistId
     *
     * @return WishListShareInterface
     */
    public function setWishlistId(int $wishlistId): WishListShareInterface
    {
        return $this->setData(self::WISHLIST_ID, $wishlistId);
    }

    /**
     * @return string|null
     */
    public function getSharedWithEmail(): ?string
    {
        return $this->getData(self::SHARED_WITH_EMAIL);
    }

    /**
     * @param string|null $email
     *
     * @return WishListShareInterface
     */
    public function setSharedWithEmail(?string $email): WishListShareInterface
    {
        return $this->setData(self::SHARED_WITH_EMAIL, $email);
    }

    /**
     * @return string|null
     */
    public function getShareMessage(): ?string
    {
        return $this->getData(self::SHARE_MESSAGE);
    }

    /**
     * @param string|null $message
     *
     * @return WishListShareInterface
     */
    public function setShareMessage(?string $message): WishListShareInterface
    {
        return $this->setData(self::SHARE_MESSAGE, $message);
    }

    /**
     * @return string
     */
    public function getSharedAt(): string
    {
        return (string)$this->getData(self::SHARED_AT);
    }

    /**
     * @param string $sharedAt
     *
     * @return WishListShareInterface
     */
    public function setSharedAt(string $sharedAt): WishListShareInterface
    {
        return $this->setData(self::SHARED_AT, $sharedAt);
    }

    /**
     * @return int
     */
    public function getViewCount(): int
    {
        return (int)$this->getData(self::VIEW_COUNT);
    }

    /**
     * @param int $viewCount
     *
     * @return WishListShareInterface
     */
    public function setViewCount(int $viewCount): WishListShareInterface
    {
        return $this->setData(self::VIEW_COUNT, $viewCount);
    }

    /**
     * @return WishListShareInterface
     */
    public function incrementViewCount(): WishListShareInterface
    {
        $current_count = $this->getViewCount();

        return $this->setViewCount($current_count + 1);
    }
}
