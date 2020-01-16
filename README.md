# Split Order for Magento 2

This extension allows your Magento store to split the order into an order for each item in the cart. With different order IDs, customers can view all the order ids in their Order History and track each item separately. The Magento admin generate separate invoices and shipments for each splitted order. Shipping charges and tax are also split based on items. This extension Magento 2 default offline payment methods: Check / Money Order and Cash on Delivery.

[![Build Status](https://travis-ci.org/magestat/magento2-split-order.svg?branch=develop)](https://travis-ci.org/magestat/magento2-split-order) [![Packagist](https://img.shields.io/packagist/v/magestat/module-split-order.svg)](https://packagist.org/packages/magestat/module-split-order) [![Downloads](https://img.shields.io/packagist/dt/magestat/module-split-order.svg)](https://packagist.org/packages/magestat/module-split-order)


## Installation

### Install via composer (recommended)

Run the following command in Magento 2 root folder:
```sh
composer require magestat/module-split-order
```

### Using GIT clone

Run the following command in Magento 2 root folder:
```sh
git clone git@github.com:magestat/magento2-split-order.git app/code/Magestat/SplitOrder
```

## 2. Activation

Run the following command in Magento 2 root folder:
```sh
php bin/magento module:enable Magestat_SplitOrder
```
```sh
php bin/magento setup:upgrade
```

Clear the caches:
```sh
php bin/magento cache:clean
```

## 3. Configuration

1. Go to **STORES** > **Configuration** > **MAGESTAT** > **Split Order**.
2. Select **Enabled** option to enable module.
3. Change the options selecting the attribute to split the order just like you want.

## Contribution

Want to contribute to this extension? The quickest way is to open a [pull request on GitHub](https://help.github.com/articles/using-pull-requests).


## Support

If you encounter any problems or bugs, please open an issue on [GitHub](https://github.com/magestat/magento2-split-order/issues).

Need help setting up or want to customize this extension to meet your business needs? Please open an issue and if we like your idea we will add this feature for free.

## Known issues

1. [Doesn't work with Braintree, Paypal via Braintree, Paypal Express Checkout](https://github.com/magestat/magento2-split-order/issues/10)
