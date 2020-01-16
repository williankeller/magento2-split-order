<?php

namespace Magestat\SplitOrder\Model;

use Magestat\SplitOrder\Helper\Data as HelperData;
use Magestat\SplitOrder\Api\ExtensionAttributesInterface;

/**
 * Class ExtensionAttributes
 * Responsible to load products attributes.
 */
class ExtensionAttributes implements ExtensionAttributesInterface
{
    /**
     * @var HelperData
     */
    private $helperData;

    /**
     * @param HelperData $helperData
     */
    public function __construct(
        HelperData $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @inheritdoc
     */
    public function loadValue($product, $attributeCode)
    {
        /** @var \Magento\Catalog\Api\Data\ProductExtensionInterface $attributes */
        $attributes = $product->getExtensionAttributes();
        if ($attributeCode === self::QUANTITY_AND_STOCK_STATUS) {
            return (string) $this->quantityAndStockStatus($attributes);
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function quantityAndStockStatus($attributes)
    {
        if ($this->helperData->getQtyType() === 'qty') {
            return $attributes->getStockItem()->getQty();
        }
        if ($this->helperData->getBackorder() && $attributes->getStockItem()->getQty() < 1) {
            return 'out';
        }
        return ($attributes->getStockItem()->getIsInStock()) ? 'in' : 'out';
    }
}
