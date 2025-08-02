<?php
declare(strict_types=1);

namespace Romain\AdvancedWishList\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Romain\AdvancedWishList\Model\ResourceModel\WishList\CollectionFactory;
use Romain\AdvancedWishList\Model\WishList;
use Magento\Framework\UrlInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Data\Form\FormKey;

/**
 * WishList Data ViewModel
 */
class WishListData implements ArgumentInterface
{
    /**
     * @var CustomerSession
     */
    private $customerSession;
    
    /**
     * @var CollectionFactory
     */
    private $wishlistCollectionFactory;
    
    /**
     * @var UrlInterface
     */
    private $urlBuilder;
    
    /**
     * @var TimezoneInterface
     */
    private $timezone;
    
    /**
     * @var FormKey
     */
    private $formKey;
    
    /**
     * @var \Romain\AdvancedWishList\Model\ResourceModel\WishList\Collection|null
     */
    private $wishlistCollection;
    
    /**
     * Constructor
     *
     * @param CustomerSession $customerSession
     * @param CollectionFactory $wishlistCollectionFactory
     * @param UrlInterface $urlBuilder
     * @param TimezoneInterface $timezone
     * @param FormKey $formKey
     */
    public function __construct(
        CustomerSession $customerSession,
        CollectionFactory $wishlistCollectionFactory,
        UrlInterface $urlBuilder,
        TimezoneInterface $timezone,
        FormKey $formKey
    ) {
        $this->customerSession = $customerSession;
        $this->wishlistCollectionFactory = $wishlistCollectionFactory;
        $this->urlBuilder = $urlBuilder;
        $this->timezone = $timezone;
        $this->formKey = $formKey;
    }
    
    /**
     * Get customer wishlists
     *
     * @return \Romain\AdvancedWishList\Model\ResourceModel\WishList\Collection
     */
    public function getCustomerWishLists()
    {
        if ($this->wishlistCollection === null) {
            $customerId = (int) $this->customerSession->getCustomerId();
            
            $this->wishlistCollection = $this->wishlistCollectionFactory->create()
                ->addCustomerFilter($customerId)
                ->addItemsCount()
                ->addDefaultOrder();
        }
        
        return $this->wishlistCollection;
    }
    
    /**
     * Check if customer has wishlists
     *
     * @return bool
     */
    public function hasWishLists(): bool
    {
        return $this->getCustomerWishLists()->getSize() > 0;
    }
    
    /**
     * Get create wishlist URL
     *
     * @return string
     */
    public function getCreateUrl(): string
    {
        return $this->urlBuilder->getUrl('advancedwishlist/index/create');
    }
    
    /**
     * Get delete wishlist URL
     *
     * @param int $wishlistId
     * @return string
     */
    public function getDeleteUrl(int $wishlistId): string
    {
        return $this->urlBuilder->getUrl('advancedwishlist/index/delete', ['id' => $wishlistId]);
    }
    
    /**
     * Get share URL
     *
     * @param WishList $wishlist
     * @return string|null
     */
    public function getShareUrl(WishList $wishlist): ?string
    {
        if ($wishlist->isPublic() && $wishlist->getShareCode()) {
            return $this->urlBuilder->getUrl('advancedwishlist/index/share', ['code' => $wishlist->getShareCode()]);
        }
        
        return null;
    }
    
    /**
     * Get wishlist view URL
     *
     * @param int $wishlistId
     * @return string
     */
    public function getViewUrl(int $wishlistId): string
    {
        return $this->urlBuilder->getUrl('advancedwishlist/index/view', ['id' => $wishlistId]);
    }
    
    /**
     * Format date for display
     *
     * @param string $date
     * @return string
     */
    public function formatDate(string $date): string
    {
        return $this->timezone->formatDate($date, \IntlDateFormatter::MEDIUM);
    }
    
    /**
     * Get customer ID
     *
     * @return int
     */
    public function getCustomerId(): int
    {
        return (int) $this->customerSession->getCustomerId();
    }
    
    /**
     * Check if customer is logged in
     *
     * @return bool
     */
    public function isCustomerLoggedIn(): bool
    {
        return $this->customerSession->isLoggedIn();
    }
    
    /**
     * Get default wishlist for customer
     *
     * @return WishList|null
     */
    public function getDefaultWishList(): ?WishList
    {
        foreach ($this->getCustomerWishLists() as $wishlist) {
            if ($wishlist->isDefault()) {
                return $wishlist;
            }
        }
        
        return null;
    }
    
    /**
     * Get total items count across all wishlists
     *
     * @return int
     */
    public function getTotalItemsCount(): int
    {
        $total = 0;
        foreach ($this->getCustomerWishLists() as $wishlist) {
            $total += (int) ($wishlist->getData('items_count') ?: 0);
        }
        
        return $total;
    }
    
    /**
     * Get wishlist statistics
     *
     * @return array
     */
    public function getWishListStats(): array
    {
        $wishlists = $this->getCustomerWishLists();
        $publicCount = 0;
        $privateCount = 0;
        
        foreach ($wishlists as $wishlist) {
            if ($wishlist->isPublic()) {
                $publicCount++;
            } else {
                $privateCount++;
            }
        }
        
        return [
            'total' => $wishlists->getSize(),
            'public' => $publicCount,
            'private' => $privateCount,
            'total_items' => $this->getTotalItemsCount()
        ];
    }
    
    /**
     * Get form key
     *
     * @return string
     */
    public function getFormKey(): string
    {
        return $this->formKey->getFormKey();
    }
}