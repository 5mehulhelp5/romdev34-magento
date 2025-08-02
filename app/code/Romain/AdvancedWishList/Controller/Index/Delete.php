<?php
/**
 * Copyright © 2025 Romain ITOFO. Tous droits réservés.
 *
 * @author  Romain ITOFO
 * @license Propriétaire
 */

declare(strict_types=1);

namespace Romain\AdvancedWishList\Controller\Index;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Event\Manager as EventManager;
use Magento\Framework\Indexer\CacheContextFactory;
use Romain\AdvancedWishList\Api\WishListRepositoryInterface;
use Romain\AdvancedWishList\Controller\AbstractWishList;
use Romain\AdvancedWishList\Model\ResourceModel\WishList as WishListResource;
use Romain\AdvancedWishList\Model\WishListFactory;

/**
 * Delete WishList Controller
 */
class Delete extends AbstractWishList implements HttpGetActionInterface
{
    /**
     * Constructor
     *
     * @param JsonFactory                 $resultJsonFactory
     * @param RedirectFactory             $resultRedirectFactory
     * @param RequestInterface            $request
     * @param WishListFactory             $wishlistFactory
     * @param WishListResource            $wishlistResource
     * @param CacheContextFactory         $cacheContextFactory
     * @param EventManager                $eventManager
     * @param CacheInterface              $cache
     * @param TypeListInterface           $typeList
     * @param WishListRepositoryInterface $wishListRepository
     * @param CustomerSession             $customerSession
     */
    public function __construct(
        private readonly JsonFactory         $resultJsonFactory,
        private readonly RedirectFactory     $resultRedirectFactory,
        private readonly RequestInterface    $request,
        private readonly WishListFactory     $wishlistFactory,
        private readonly WishListResource    $wishlistResource,
        CacheContextFactory               $cacheContextFactory,
        EventManager                      $eventManager,
        CacheInterface                    $cache,
        TypeListInterface                 $typeList,
        WishListRepositoryInterface       $wishListRepository,
        CustomerSession                   $customerSession,
    ) {
        parent::__construct(            $cache,
            $typeList,
            $resultJsonFactory,
            $wishListRepository,
            $customerSession,
            $cacheContextFactory,
            $eventManager);
    }

    /**
     * Execute action
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $result_json = $this->resultJsonFactory->create();
        $result_redirect = $this->resultRedirectFactory->create();
        // Check if customer is logged in
        if (!$this->customerSession->isLoggedIn()) {
            return $result_json->setData([
                'success' => false,
                'message' => __('You must be logged in to delete a wishlist.')
            ]);
        }

        try {
            $wishlist_id = (int)$this->request->getParam('id');
            $customer_id = (int)$this->customerSession->getCustomerId();

            if (!$wishlist_id) {
                return $result_json->setData([
                    'success' => false,
                    'message' => __('Invalid wishlist ID.')
                ]);
            }

            // Load wishlist
            $wishlist = $this->wishlistFactory->create();
            $this->wishlistResource->load($wishlist, $wishlist_id);

            if (!$wishlist->getId()) {
                return $result_json->setData([
                    'success' => false,
                    'message' => __('WishList not found.')
                ]);
            }

            // Verify ownership
            if ($wishlist->getCustomerId() !== $customer_id) {
                return $result_json->setData([
                    'success' => false,
                    'message' => __('You can only delete your own wishlists.')
                ]);
            }

            // Prevent deletion of default wishlist
            if ($wishlist->isDefault()) {
                return $result_json->setData([
                    'success' => false,
                    'message' => __('Cannot delete the default wishlist.')
                ]);
            }
            $this->wishlistResource->delete($wishlist);

            return $result_redirect->setPath('advancedwishlist');
        } catch (\Exception $e) {
            return $result_json->setData([
                'success' => false,
                'message' => __('An error occurred while deleting the wishlist.')
            ]);
        }
    }

    /**
     * Create csrf validation exception
     *
     * @param RequestInterface $request
     *
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * Perform custom request validation
     *
     * @param RequestInterface $request
     *
     * @return bool|null
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
