# Split Order for Magento 2

[![Packagist](https://img.shields.io/packagist/v/magestat/module-split-order.svg)](https://packagist.org/packages/magestat/module-split-order)

This extension allows your online store to split the order into an order
for each item in the cart. With different order ids, customers can view all the 
order ids in their Order History and track each item separately. 
The admin generate separate invoices and shipments for each splitted order. 
Shipping charges and tax are also split based on items.


## 1. Installation

### Install via composer (recommend)


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
php bin/magento module:enable Magestat_SplitOrder --clear-static-content
php bin/magento setup:upgrade
```

Clear the caches:
```sh
php bin/magento cache:clean
```

## 3. Configuration

1. Go to **Stores** > **Configuration** > **Magestat** > **Split Order**.
2. Select **Enabled** option to enable module.

## Contribution

Want to contribute to this extension? The quickest way is to open a [pull request on GitHub](https://help.github.com/articles/using-pull-requests).


## Support

If you encounter any problems or bugs, please open an issue on [GitHub](https://github.com/magestat/magento2-split-order/issues).

Need help setting up or want to customize this extension to meet your business needs? Please email support@magestat.com and if we like your idea we will add this feature for free or at a discounted rate.

© Magestat. | [www.magestat.com](http:/www.magestat.com)
