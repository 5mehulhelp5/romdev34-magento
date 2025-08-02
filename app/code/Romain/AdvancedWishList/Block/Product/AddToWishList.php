<?php
/**
 * Copyright © 2025 Romain ITOFO. Tous droits réservés.
 *
 * @author  Romain ITOFO
 * @license Propriétaire
 */

declare(strict_types=1);

namespace Romain\AdvancedWishList\Block\Product;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Romain\AdvancedWishList\Api\WishListItemRepositoryInterface;
use Romain\AdvancedWishList\Api\WishListRepositoryInterface;

/**
 * Class AddToWishList
 */
class AddToWishList extends Template implements IdentityInterface
{
    private ?Product $product;
    private ?int $customer_id;

    public function __construct(
        Context                                          $context,
        private readonly CustomerSession                 $customerSession,
        private readonly WishListRepositoryInterface     $wishlistRepository,
        private readonly WishListItemRepositoryInterface $wishlistItemRepository,
        private readonly HttpContext                     $httpContext,
        private readonly ProductRepositoryInterface      $productRepository,
        private readonly ImageHelper                     $imageHelper,
        private readonly PriceHelper                     $priceHelper,
        private readonly RequestInterface                $request,
        array                                            $data = []
    ) {
        $this->product = $data['product'] ?? null;
        $this->customer_id = $data['customer_id'] ?? null;
        parent::__construct($context, $data);
    }

    /**
     * Get identities from the current product model
     *
     * @return array
     */
    public function getIdentities(): array
    {
        if (!$this->customer_id) {
            return [];
        }
        $identities = [];
        $wishlists = $this->getAllWishLists();
        foreach ($wishlists as $wishlist) {
            if ($wishlist instanceof IdentityInterface) {
                $identities = array_merge($identities, $wishlist->getIdentities());
            }

            $items = $this->getWishlistItemsWithProducts($wishlist);
            foreach ($items as $itemdatas) {
                $item = $itemdatas['item'];
                if ($item instanceof IdentityInterface) {
                    $identities = array_merge($identities, $item->getIdentities());
                }
            }
        }

        return array_unique($identities);
    }

    /**
     * Get wishlist items with product data
     *
     * @param $wishList
     *
     * @return array
     */
    public function getWishlistItemsWithProducts($wishList): array
    {
        $items = $this->wishlistItemRepository->getByWishlistId((int)$wishList->getWishlistId());
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
            } catch (Exception) {
                // Skip items with invalid products
                continue;
            }
        }

        return $items_with_products;
    }

    /**
     * @return array
     */
    public function getAllWishLists(): array
    {
        return $this->wishlistRepository->getByCustomerId($this->customer_id);
    }

    /**
     * Get cache tags - maintenant basé sur getIdentities()
     *
     * @return array
     */
    protected function getCacheTags(): array
    {
        $tags = parent::getCacheTags();

        return array_merge($tags, $this->getIdentities());
    }

    /**
     * Get current product
     *
     * @return ProductInterface|null
     */
    public function getProduct(): ?ProductInterface
    {
        $product_id = $this->request->getParam('id');
        if ($product_id) {
            try {
                return $this->productRepository->getById($product_id);
            } catch (Exception) {
                return null;
            }
        }

        return $this->product;
    }

    /**
     * Check if customer is logged in
     *
     * @return bool
     */
    public function isCustomerLoggedIn(): bool
    {
        // Use HTTP Context for the blocks
        $is_logged_in_via_context = (bool)$this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);

        // Double check if customer session is available
        $is_logged_in_via_session = $this->customerSession->isLoggedIn();

        return $is_logged_in_via_context || $is_logged_in_via_session;
    }

    /**
     * Get customer wishlists
     *
     * @return array
     */
    public function getCustomerWishlists(): array
    {
        if (!$this->isCustomerLoggedIn()) {
            return [];
        }

        try {
            return $this->wishlistRepository->getByCustomerId($this->customer_id);
        } catch (Exception) {
            return [];
        }
    }

    /**
     * Get add to wishlist URL
     *
     * @return string
     */
    public function getAddToWishlistUrl(): string
    {
        return $this->getUrl('advancedwishlist/item/add');
    }

    /**
     * Check if product is in wishlist
     *
     * @param int $wishlistId
     *
     * @return bool
     */
    public function isProductInWishlist(int $wishlistId): bool
    {
        $product = $this->getProduct();
        if (!$product) {
            return false;
        }

        try {
            return $this->wishlistItemRepository->isProductInWishlist($wishlistId, (int)$product->getId());
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Get login URL
     *
     * @return string
     */
    public function getLoginUrl(): string
    {
        return $this->getUrl('customer/account/login');
    }
}
