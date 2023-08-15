<?php
declare(strict_types=1);

namespace SillyTools\WarehouseStockStatus\Helper;

use Magento\Directory\Helper\Data;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;


class Config extends AbstractHelper
{
    /**
     * @param Data $directoryHelper
     * @param Context $context
     */
    public function __construct(
        private readonly Data    $directoryHelper,
        private readonly Context $context,
    )
    {
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            'warehousestockstatus/general/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}



