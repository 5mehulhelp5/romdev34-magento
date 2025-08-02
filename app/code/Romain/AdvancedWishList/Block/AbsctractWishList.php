<?php
/**
 * Copyright © 2025 Romain ITOFO. Tous droits réservés.
 *
 * @author  Romain ITOFO
 * @license Propriétaire
 */

declare(strict_types=1);

namespace Romain\AdvancedWishList\Block;

use Magento\Customer\Model\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\View\Element\Template;

/**
 * Abstract block
 */
class AbsctractWishList extends Template
{
    public function __construct(
        Template\Context                   $context,
        protected readonly CustomerSession $customerSession,
        protected readonly HttpContext     $httpContext,
        array                              $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Get current customer ID
     */
    protected function getCustomerId(): ?int
    {
        $is_logged_in = $this->httpContext->getValue(Context::CONTEXT_AUTH);

        if ($is_logged_in) {
            $customer_id = $this->httpContext->getValue('customer_id');

            if (!$customer_id) {
                if ($this->customerSession->isLoggedIn()) {
                    $customer_id = $this->customerSession->getCustomerId();
                }
            }

            if (!$customer_id) {
                $this->customerSession->regenerateId();
                $customer_id = $this->customerSession->getCustomerId();
            }

            return $customer_id ? (int)$customer_id : null;
        }

        return null;
    }

    /**
     * Check if customer is logged in
     */
    public function isCustomerLoggedIn(): bool
    {
        // Use HTTP Context for the blocks
        $is_logged_in_via_context = (bool)$this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);

        // Check if customer session is started
        $is_logged_in_via_session = $this->customerSession->isLoggedIn();

        return $is_logged_in_via_context || $is_logged_in_via_session;
    }
}
