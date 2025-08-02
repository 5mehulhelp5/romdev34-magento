<?php
/**
  * Copyright © 2025 Romain ITOFO. Tous droits réservés.
  *
  * @author Romain ITOFO
  * @license Propriétaire
 */

declare(strict_types=1);

namespace Romain\AdvancedWishList\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Random\RandomException;
use Romain\AdvancedWishList\Api\Data\WishListInterface;
use Romain\AdvancedWishList\Model\ResourceModel\WishList as ResourceModel;

/**
 * Advanced WishList Model
 */
class WishList extends AbstractModel implements WishListInterface, IdentityInterface
{
    public const CACHE_TAG = 'advanced_wishlist';
    public const ENTITY_ID = 'wishlist_id';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * @var string
     */
    protected $_eventPrefix = 'advanced_wishlist';

    /**
     * @var DateTime
     */
    private DateTime $dateTime;

    public function __construct(
        Context          $context,
        Registry         $registry,
        DateTime         $dateTime,
        AbstractResource $resource = null,
        AbstractDb       $resourceCollection = null,
        array            $data = []
    ) {
        $this->dateTime = $dateTime;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @throws LocalizedException
     */
    protected function _construct(): void
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get ID
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Get customer ID
     *
     * @return int
     */
    public function getCustomerId(): int
    {
        return (int)$this->getData('customer_id');
    }

    /**
     * Set customer ID
     *
     * @param int $customerId
     *
     * @return $this
     */
    public function setCustomerId(int $customerId): self
    {
        return $this->setData('customer_id', $customerId);
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName(): string
    {
        return (string)$this->getData('name');
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name): self
    {
        return $this->setData('name', $name);
    }

    /**
     * Get description
     *
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->getData('description');
    }

    /**
     * Set description
     *
     * @param string|null $description
     *
     * @return $this
     */
    public function setDescription(?string $description): self
    {
        return $this->setData('description', $description);
    }

    /**
     * Is public
     *
     * @return bool
     */
    public function isPublic(): bool
    {
        return (bool)$this->getData('is_public');
    }

    /**
     * Set is public
     *
     * @param bool $isPublic
     *
     * @return $this
     */
    public function setIsPublic(bool $isPublic): self
    {
        return $this->setData('is_public', $isPublic);
    }

    /**
     * Get share code
     *
     * @return string|null
     */
    public function getShareCode(): ?string
    {
        return $this->getData('share_code');
    }

    /**
     * Set share code
     *
     * @param string|null $shareCode
     *
     * @return $this
     */
    public function setShareCode(?string $shareCode): self
    {
        return $this->setData('share_code', $shareCode);
    }

    /**
     * Is default wishlist
     *
     * @return bool
     */
    public function isDefault(): bool
    {
        return (bool)$this->getData('is_default');
    }

    /**
     * Set is default
     *
     * @param bool $isDefault
     *
     * @return $this
     */
    public function setIsDefault(bool $isDefault): self
    {
        return $this->setData('is_default', $isDefault);
    }

    /**
     * Generate unique share code
     *
     * @return string
     * @throws RandomException
     */
    public function generateShareCode(): string
    {
        $share_code = bin2hex(random_bytes(16));
        $this->setShareCode($share_code);

        return $share_code;
    }

    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt(): string
    {
        return (string)$this->getData('created_at');
    }

    /**
     * Get updated at
     *
     * @return string
     */
    public function getUpdatedAt(): string
    {
        return (string)$this->getData('updated_at');
    }

    /**
     * Set updated at
     *
     * @param string $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(string $createdAt): self
    {
        return $this->setData('created_at', $createdAt);
    }

    /**
     * Set updated at
     *
     * @param string $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt(string $updatedAt): self
    {
        return $this->setData('updated_at', $updatedAt);
    }

    /**
     * Before save processing
     *
     * @return $this
     * @throws RandomException
     */
    public function beforeSave(): static
    {
        $now = $this->dateTime->gmtDate();

        if (!$this->getId()) {
            $this->setCreatedAt($now);
        }

        $this->setUpdatedAt($now);

        // Generate share code if public and no code exists
        if ($this->isPublic() && !$this->getShareCode()) {
            $this->generateShareCode();
        }

        return parent::beforeSave(); //
    }
}
