<?php
/**
 * Examples of how different modules can use the structured message format
 */

// Stock Import Example:
$messageStorage->addData(
    __('Stock level updated'),
    $warehouseId,
    MessageStorageInterface::STATUS_SUCCESS,
    [
        'entity_type' => 'stock',
        'action' => 'updated',
        'entity_ids' => [
            'plenty_warehouse' => $warehouseId,
            'magento_source' => $sourceCode
        ],
        'details' => [
            'sku' => $sku,
            'old_qty' => $oldQty,
            'new_qty' => $newQty,
            'difference' => $newQty - $oldQty
        ]
    ]
);

// Product Import Example:
$messageStorage->addData(
    __('Product imported'),
    $itemId,
    MessageStorageInterface::STATUS_SUCCESS,
    [
        'entity_type' => 'item',
        'action' => 'imported',
        'entity_ids' => [
            'plenty_item' => $itemId,
            'plenty_variation' => $variationId,
            'magento_product' => $productId
        ],
        'details' => [
            'sku' => $sku,
            'name' => $productName,
            'type' => $productType
        ]
    ]
);

// Category Sync Example:
$messageStorage->addData(
    __('Category synchronized'),
    $categoryId,
    MessageStorageInterface::STATUS_SUCCESS,
    [
        'entity_type' => 'category',
        'action' => 'synchronized',
        'entity_ids' => [
            'plenty_category' => $plentyCategoryId,
            'magento_category' => $magentoCategoryId
        ],
        'details' => [
            'name' => $categoryName,
            'level' => $categoryLevel,
            'path' => $categoryPath
        ]
    ]
);

// Customer Export Example:
$messageStorage->addData(
    __('Customer exported'),
    $customerId,
    MessageStorageInterface::STATUS_SUCCESS,
    [
        'entity_type' => 'customer',
        'action' => 'exported',
        'entity_ids' => [
            'magento_customer' => $customerId,
            'plenty_contact' => $contactId
        ],
        'details' => [
            'email' => $customerEmail,
            'group' => $customerGroup
        ]
    ]
);

// The summary renderer will automatically format these as:
// Stock: "Stock W123 | Source default"
// Product: "Item 456 | Variation 789 | Product 123"
// Category: "Category 111 | Category 222"
// Customer: "Customer 333 | Contact 444"