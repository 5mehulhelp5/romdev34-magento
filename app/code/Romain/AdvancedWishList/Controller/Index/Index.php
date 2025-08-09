<?php
/**
 * Copyright © 2025 Romain ITOFO. Tous droits réservés.
 *
 * @author  Romain ITOFO
 * @license Propriétaire
 */

declare(strict_types=1);

namespace Romain\AdvancedWishList\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Theme\Block\Html\Breadcrumbs;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\UrlInterface;

/**
 * Advanced WishList Index Controller
 */
class Index implements HttpGetActionInterface
{
    /**
     * Constructor
     *
     * @param PageFactory      $resultPageFactory
     * @param ManagerInterface $messageManager
     * @param RedirectFactory  $resultRedirectFactory
     * @param CustomerSession  $customerSession
     * @param UrlInterface     $urlBuilder
     */
    public function __construct(
        private readonly PageFactory      $resultPageFactory,
        private readonly ManagerInterface $messageManager,
        private readonly RedirectFactory  $resultRedirectFactory,
        private readonly CustomerSession  $customerSession,
        private readonly UrlInterface     $urlBuilder
    ) {
    }

    /**
     * Execute action
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        // Check if customer is logged in
        if (!$this->customerSession->isLoggedIn()) {
            $this->messageManager->addError(__('You must be logged in to access your wishlists.'));
            $result_redirect = $this->resultRedirectFactory->create();

            return $result_redirect->setUrl(
                $this->urlBuilder->getUrl('customer/account/login')
            );
        }

        $result_page = $this->resultPageFactory->create();
        $result_page->getConfig()->getTitle()->set(__('Module personnalisé WishList'));

        // Add breadcrumbs
        /** @var Breadcrumbs $breadcrumbs */
        $breadcrumbs = $result_page->getLayout()->getBlock('breadcrumbs');
        $breadcrumbs->addCrumb(
            'home',
            [
                'label' => __('Home'),
                'title' => __('Home'),
                'link'  => '/'
            ]
        );
        $breadcrumbs->addCrumb(
            'account',
            [
                'label' => __('My Account'),
                'title' => __('My Account'),
                'link'  => '/customer/account'
            ]
        );
        $breadcrumbs->addCrumb(
            'wishlists',
            [
                'label' => __('My advanced WishLists'),
                'title' => __('My advanced WishLists'),
            ]
        );

        return $result_page;
    }
}
