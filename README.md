Split Order for Magento 2
==================

This extension allows your online store to split the order into an order
for each item in the cart. With different order ids, customers can view all the 
order ids in their Order History and track each item separately. 
The admin generate separate invoices and shipments for each splitted order. 
Shipping charges and tax are also split based on items.


Installation
-------------
**Using Composer**


Install all dependencies via [Composer](https://getcomposer.org) (from root of 
your Magento2 Installation):
```sh
composer config repositories.magestat-module-split-order git git@github.com:magestat/magento2-split-order.git
composer require magestat/module-split-order:dev-master
```

**Using GIT Clone**

Run the following series of command (from root of your Magento2 Installation):
```sh
mkdir -p app/code/Magestat && git clone git@github.com:magestat/magento2-split-order.git app/code/Magestat/SplitOrder
```

**Enabling module**

After installation by either means, enable the extension by running following 
commands (again from root of Magento2 installation):
```sh
php bin/magento module:enable Magestat_SplitOrder --clear-static-content
php bin/magento setup:upgrade
```

Go to *Stores* > *Configuration* > *Magestat* > *Split Order*:
And just Enable module.

Clear the caches:
```sh
php bin/magento cache:clean
```

Let us know if you have any suggestions for changing the price for the text,
contact@magestat.com or open an issue under this repository.


Uninstall
-------------

You need to remove the module.
```sh
composer remove magestat/module-split-order
```