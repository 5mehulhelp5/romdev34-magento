<?php
/**
 * Copyright © 2025 Romain ITOFO. Tous droits réservés.
 *
 * @author  Romain ITOFO
 * @license Propriétaire
 */

declare(strict_types=1);

namespace Romain\AdvancedWishList\Block\WishListItem;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\View\Element\Template\Context;
use Romain\AdvancedWishList\Api\Data\WishListInterface;
use Romain\AdvancedWishList\Api\WishListItemRepositoryInterface;
use Romain\AdvancedWishList\Block\AbsctractWishList;

/**
 * Block WishListItems to handle the display of the content of a wishlist
 */
class WishListItems extends AbsctractWishList implements IdentityInterface
{
    /**
     * @var WishListInterface
     */
    private WishListInterface $wishlist;

    public function __construct(
        Context                                          $context,
        private readonly WishListItemRepositoryInterface $wishlistItemRepository,
        private readonly ProductRepositoryInterface      $productRepository,
        private readonly ImageHelper                     $imageHelper,
        private readonly PriceHelper                     $priceHelper,
        CustomerSession                                  $customerSession,
        HttpContext                                      $httpContext,
        array                                            $data = []
    ) {
        parent::__construct($context, $customerSession, $httpContext, $data);
    }

    /**
     * Get identities from the wishlist and its items - CRUCIAL pour l'invalidation automatique
     *
     * @return array
     */
    public function getIdentities(): array
    {
        $tags = [];

        if (!isset($this->wishlist)) {
            return $tags;
        }

        // ⭐ Tags génériques pour les items
        $tags[] = 'advanced_wishlist_items';

        // ⭐ Tags pour la wishlist elle-même
        $tags[] = 'advanced_wishlist_' . $this->wishlist->getId();

        // ⭐ Tags customer-specific
        if ($this->wishlist->getCustomerId()) {
            $tags[] = 'customer_wishlist_items_' . $this->wishlist->getCustomerId();
            $tags[] = 'customer_wishlists_' . $this->wishlist->getCustomerId();
        }

        // ⭐ Tags pour chaque item et produit affiché
        try {
            $items = $this->getWishlistItemsWithProducts();
            foreach ($items as $itemData) {
                $item = $itemData['item'];
                $product = $itemData['product'];

                // Tag pour l'item spécifique
                $tags[] = 'advanced_wishlist_item_' . $item->getId();

                // Tag pour le produit
                $tags[] = 'catalog_product_' . $product->getId();
            }
        } catch (\Exception $e) {
            // En cas d'erreur, garder au moins les tags de base
        }

        return array_unique($tags);
    }

    /**
     * Set wishlist
     *
     * @param WishListInterface $wishlist
     *
     * @return $this
     */
    public function setWishlist(WishListInterface $wishlist): static
    {
        $this->wishlist = $wishlist;

        return $this;
    }

    /**
     * Get wishlist
     *
     * @return WishListInterface|null
     */
    public function getWishlist(): ?WishListInterface
    {
        return $this->wishlist;
    }

    /**
     * Get wishlist items with product data
     *
     * @return array
     */
    public function getWishlistItemsWithProducts(): array
    {
        $items = $this->wishlistItemRepository->getByWishlistId((int)$this->wishlist->getId());
        $items_with_products = [];

        foreach ($items as $item) {
            try {
                /** @var Product $product */
                $product = $this->productRepository->getById($item->getProductId());
                $items_with_products[] = [
                    'item'      => $item,
                    'product'   => $product,
                    'image_url' => $this->imageHelper->init($product, 'product_thumbnail_image')->getUrl(),
                    'price'     => $this->priceHelper->currency($product->getPrice(), true, false),
                    'url'       => $product->getProductUrl()
                ];
            } catch (\Exception $e) {
                // Skip items with invalid products
                continue;
            }
        }

        return $items_with_products;
    }

    /**
     * Get remove from wishlist URL
     *
     * @return string
     */
    public function getRemoveFromWishlistUrl(): string
    {
        return $this->getUrl('advancedwishlist/item/remove');
    }

    /**
     * Check if current customer can modify this wishlist
     *
     * @return bool
     */
    public function canModifyWishlist(): bool
    {
        return $this->isCustomerLoggedIn() &&
            (int)$this->getCustomerId() === (int)$this->wishlist->getCustomerId();
    }

    /**
     * Format wishlist date
     */
    public function formatWishListDate(string $date): string
    {
        return $this->formatDate($date, \IntlDateFormatter::MEDIUM);
    }

    /**
     * Get wishlist share URL
     *
     * @return string
     */
    public function getShareUrl(): string
    {
        return $this->getUrl('advancedwishlist/wishlist/share');
    }

    /**
     * Check if wishlist is public
     *
     * @return bool
     */
    public function isPublicWishlist(): bool
    {
        return $this->wishlist->isPublic();
    }
}
