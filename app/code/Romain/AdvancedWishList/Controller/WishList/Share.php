<?php
/**
  * Copyright © 2025 Romain ITOFO. Tous droits réservés.
  *
  * @author Romain ITOFO
  * @license Propriétaire
 */

declare(strict_types=1);

namespace Romain\AdvancedWishList\Controller\WishList;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Romain\AdvancedWishList\Service\WishListShareService;

/**
 * Controller Share wishlist
 */
class Share implements HttpPostActionInterface
{
    public function __construct(
        private readonly RequestInterface     $request,
        private readonly JsonFactory          $jsonFactory,
        private readonly CustomerSession      $customerSession,
        private readonly WishListShareService $wishlistShareService,
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(): Json
    {
        $result = $this->jsonFactory->create();

        if (!$this->customerSession->isLoggedIn()) {
            return $result->setData([
                'success' => false,
                'message' => __('You must be logged in to share wishlists.')
            ]);
        }

        try {
            $wishlist_id = (int)$this->request->getParam('wishlist_id');
            $email = $this->request->getParam('email');
            $message = $this->request->getParam('message');

            if (!$wishlist_id || !$email) {
                return $result->setData([
                    'success' => false,
                    'message' => __('Wishlist ID and email are required.')
                ]);
            }

            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $result->setData([
                    'success' => false,
                    'message' => __('Please enter a valid email address.')
                ]);
            }

            $customer_id = $this->customerSession->getCustomerId();
            $share = $this->wishlistShareService->shareWishlist($wishlist_id, $email, $message, $customer_id);

            return $result->setData([
                'success'  => true,
                'message'  => __('Wishlist has been shared successfully.'),
                'share_id' => $share->getShareId()
            ]);
        } catch (LocalizedException $e) {
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error sharing wishlist: ' . $e->getMessage());

            return $result->setData([
                'success' => false,
                'message' => __('An error occurred while sharing the wishlist.')
            ]);
        }
    }
}
