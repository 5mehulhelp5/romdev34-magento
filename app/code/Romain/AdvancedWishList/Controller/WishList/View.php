<?php
/**
 * Copyright © 2025 Romain ITOFO. Tous droits réservés.
 *
 * @author  Romain ITOFO
 * @license Propriétaire
 */

declare(strict_types=1);

namespace Romain\AdvancedWishList\Controller\WishList;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;
use Romain\AdvancedWishList\Api\WishListRepositoryInterface;

/**
 * Controller view wishlist
 */
class View implements HttpGetActionInterface
{
    public function __construct(
        private readonly RequestInterface            $request,
        private readonly PageFactory                 $pageFactory,
        private readonly RedirectFactory             $redirectFactory,
        private readonly ManagerInterface            $messageManager,
        private readonly CustomerSession             $customerSession,
        private readonly WishListRepositoryInterface $wishlistRepository,
        private readonly LoggerInterface             $logger
    ) {
    }

    public function execute(): ResultInterface
    {
        if (!$this->customerSession->isLoggedIn()) {
            $this->messageManager->addErrorMessage(__('You must be logged in to view your wishlists.'));
            $redirect = $this->redirectFactory->create();

            return $redirect->setPath('customer/account/login');
        }

        $wishlist_id = (int)$this->request->getParam('id');

        if (!$wishlist_id) {
            $this->messageManager->addErrorMessage(__('Invalid wishlist ID.'));
            $redirect = $this->redirectFactory->create();

            return $redirect->setPath('advancedwishlist/wishlist/index');
        }

        try {
            $wishlist = $this->wishlistRepository->getById($wishlist_id);
            // Verify wishlist belongs to customer
            if ((int)$wishlist->getCustomerId() !== (int)$this->customerSession->getCustomerId()) {
                $this->messageManager->addErrorMessage(__('You do not have permission to view this wishlist.'));
                $redirect = $this->redirectFactory->create();

                return $redirect->setPath('advancedwishlist/wishlist/index');
            }

            // Create page and set wishlist data
            $page = $this->pageFactory->create();
            $page->getConfig()->getTitle()->set(__('Wishlist: %1', $wishlist->getName()));

            // Pass wishlist to block
            $block = $page->getLayout()->getBlock('advanced.wishlist.items');
            if ($block) {
                $block->setWishlist($wishlist);
            }

            return $page;
        } catch (\Exception $e) {
            $this->logger->error('Error viewing wishlist: ' . $e->getMessage());
            $this->messageManager->addErrorMessage(__('Could not load wishlist.' . $e->getMessage()));

            $redirect = $this->redirectFactory->create();

            return $redirect->setPath('advancedwishlist/wishlist/index');
        }
    }
}
