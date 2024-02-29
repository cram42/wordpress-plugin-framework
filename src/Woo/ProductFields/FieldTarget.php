<?php

namespace WPPluginFramework\Woo\ProductFields;

class FieldTarget
{
    public const DEFAULT = FieldTarget::GENERAL;

    // General
    public const GENERAL = 'woocommerce_product_options_general_product_data';
    public const GENERAL_DOWNLOADS = 'woocommerce_product_options_downloads';
    public const GENERAL_EXTERNAL = 'woocommerce_product_options_external';
    public const GENERAL_PRICING = 'woocommerce_product_options_pricing';
    public const GENERAL_TAX = 'woocommerce_product_options_tax';

    // Inventory
    public const INVENTORY = 'woocommerce_product_options_inventory_product_data';
    public const INVENTORY_SKU = 'woocommerce_product_options_sku';
    public const INVENTORY_STOCK = 'woocommerce_product_options_stock';
    public const INVENTORY_STOCK_FIELDS = 'woocommerce_product_options_stock_fields';
    public const INVENTORY_STOCK_STATUS = 'woocommerce_product_options_stock_status';
    public const INVENTORY_SOLD_INDIVIDUALLY = 'woocommerce_product_options_sold_individually';

    // Shipping
    public const SHIPPING = 'woocommerce_product_options_shipping_product_data';
    public const SHIPPING_DIMENSIONS = 'woocommerce_product_options_dimensions';
    public const SHIPPING_OPTIONS = 'woocommerce_product_options_shipping';

    // Advanced
    public const ADVANCED = 'woocommerce_product_options_advanced';
    public const ADVANCED_REVIEWS = 'woocommerce_product_options_reviews';
}
