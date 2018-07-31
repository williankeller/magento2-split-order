<?php

/**
 * A Magento 2 module named Magestat/SplitOrder
 * Copyright (C) 2018 Magestat
 *
 * This file included in Magestat/SplitOrder is licensed under OSL 3.0
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

namespace Magestat\SplitOrder\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;

class Attributes implements ArrayInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    protected $collection;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collection = $collectionFactory;
    }

    /**
     * Get options as array
     *
     * @return array
     */
    public function toOptionArray($isMultiselect = false)
    {
        if (!$this->options) {
            $collection = $this->collection->create();

            foreach ($collection as $items) {
                $attributes[] = [
                    'value' => $items->getAttributeCode(),
                    'label' => $items->getFrontendLabel()
                ];
            }
            $this->options = $attributes;
        }

        $options = $this->options;
        if (!$isMultiselect) {
            array_unshift($options, ['value' => '', 'label' => __('--Please Select--')]);
        }
        return $options;
    }
}
