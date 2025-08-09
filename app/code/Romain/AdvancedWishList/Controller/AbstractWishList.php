<?php
/**
 * Copyright © 2025 Romain ITOFO. Tous droits réservés.
 *
 * @author  Romain ITOFO
 * @license Propriétaire
 */

declare(strict_types=1);

namespace Romain\AdvancedWishList\Controller;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Indexer\CacheContextFactory;
use Romain\AdvancedWishList\Api\WishListRepositoryInterface;
use Romain\AdvancedWishList\Model\WishList;
use Magento\Framework\Event\Manager as EventManager;

/**
 * Create WishList Controller
 */
class AbstractWishList
{
    /**
     * @var CacheInterface
     */
    protected CacheInterface $cache;

    /**
     * @var TypeListInterface
     */
    protected TypeListInterface $cacheTypeList;

    /**
     * Constructor
     *
     * @param CacheInterface              $cache
     * @param TypeListInterface           $cacheTypeList
     * @param JsonFactory                 $jsonFactory
     * @param WishListRepositoryInterface $wishListRepository
     * @param CustomerSession             $customerSession
     * @param CacheContextFactory         $cacheContextFactory
     * @param EventManager                $eventManager
     */
    public function __construct(
        CacheInterface                        $cache,
        TypeListInterface                     $cacheTypeList,
        protected JsonFactory                 $jsonFactory,
        protected WishListRepositoryInterface $wishListRepository,
        protected CustomerSession             $customerSession,
        private readonly CacheContextFactory  $cacheContextFactory,
        private readonly EventManager         $eventManager
    ) {
        $this->cache = $cache;
        $this->cacheTypeList = $cacheTypeList;
    }

    /**
     * Clear wishlist related cache
     */
    protected function clearWishlistCache(): void
    {
        $cache_context = $this->cacheContextFactory->create();
        $ids = $this->wishListRepository->getAllWishlistIds();
        $cache_context->registerEntities(WishList::CACHE_TAG, $ids);
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $cache_context]);
    }

    /**
     * Clear wishlist related cache
     */
    protected function clearWishlistItemCache(): void
    {
        $cache_context = $this->cacheContextFactory->create();
        $ids = $this->wishListRepository->getAllWishlistIds();
        $cache_context->registerEntities(WishList::CACHE_TAG, $ids);
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $cache_context]);
    }

    /**
     * @param $wishlistId
     * @param $productId
     *
     * @return Json|void
     */
    protected function checkWishList($wishlistId, $productId)
    {
        $result = $this->jsonFactory->create();
        if (!$wishlistId || !$productId) {
            return $result->setData([
                'success' => false,
                'message' => __('Invalid wishlist or product ID.')
            ]);
        }

        // Verify wishlist belongs to customer
        $wishlist = $this->wishListRepository->getById($wishlistId);
        if ((int)$wishlist->getCustomerId() !== (int)$this->customerSession->getCustomerId()) {
            return $result->setData([
                'success' => false,
                'message' => __('You do not have permission to modify this wishlist.')
            ]);
        }
    }
}
