<?php
declare(strict_types=1);

namespace SillyTools\WarehouseStockStatus\ViewModel;

use SillyTools\WarehouseStockStatus\Helper\Config;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Store\Model\StoreManagerInterface;

class Stock implements ArgumentInterface
{

    /**
     * @param Config $configHelper
     * @param ProductRepositoryInterface $productRepository
     * @param SourceItemRepositoryInterface $sourceItems
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        private readonly Config                        $configHelper,
        private readonly ProductRepositoryInterface    $productRepository,
        private readonly SourceItemRepositoryInterface $sourceItems,
        private readonly SearchCriteriaBuilder         $searchCriteriaBuilder,
        private readonly StoreManagerInterface         $storeManager
    )
    {
    }

    /**
     * @param $productId
     * @return array
     */
    public function getStockQuantityFromSource($productId): array
    {

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('sku', $productId)
            ->create();
        $sourceItemData = $this->sourceItems->getList($searchCriteria);
        $new_arr = array();
        if ($this->getStoreId() == "1" || $this->getStoreId() == "17") {
            foreach ($sourceItemData->getItems() as $item) {
                if (in_array($item["source_code"], array("5", "18", "19", "22", "33", "40", "70", "75", "76", "77", "78", "93"))) {
                    $item["quantity"] = intval($item["quantity"]);

                    if (in_array($item["source_code"], ["33", "93", "70", "5"])) {
                        $item["state"] = "NSW";
                    } elseif (in_array($item["source_code"], ["19", "78"])) {
                        $item["state"] = "SA";
                    } elseif (in_array($item["source_code"], ["18", "77"])) {
                        $item["state"] = "WA";
                    } elseif (in_array($item["source_code"], ["40", "76"])) {
                        $item["state"] = "QLD";
                    } elseif (in_array($item["source_code"], ["22", "75"])) {
                        $item["state"] = "VIC";
                    }
                    $new_arr[] = $item;
                }
            }
        } elseif ($this->getStoreId() == "6" || $this->getStoreId() == "18") {
            foreach ($sourceItemData->getItems() as $item) {
                if (in_array($item["source_code"], array("6", "21"))) {
                    $item["quantity"] = intval($item["quantity"]);

                    if (in_array($item["source_code"], ["6"])) {
                        $item["state"] = "Auckland";
                    } elseif (in_array($item["source_code"], ["21"])) {
                        $item["state"] = "Christchurch";
                    }
                    $new_arr[] = $item;
                }
            }
        }

        $filtered_array = array();
        $seen_states = array();
        foreach ($new_arr as $item) {
            if (in_array($item['state'], $seen_states) && $item['quantity'] == 0) {
                continue;
            }
            if (in_array($item['state'], $seen_states) && $item['quantity'] > 0) {
                $matching_key = null;
                foreach ($filtered_array as $key => $filtered_item) {
                    if ($filtered_item['state'] == $item['state']) {
                        $matching_key = $key;
                        break;
                    }
                }
                if ($matching_key !== null && $filtered_array[$matching_key]['quantity'] == 0) {
                    $filtered_array[$matching_key] = $item;
                }
                continue;
            }
            $filtered_array[] = $item;
            $seen_states[] = $item['state'];
        }
        return $filtered_array;
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getStoreId():string
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->configHelper->isEnabled();
    }
}

