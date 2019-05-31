# Changelog
All notable changes to this project will be documented in this file.

### [1.0.6](https://github.com/magestat/magento2-split-order/releases/tag/1.0.6) - 2019-05-31
#### Added
- Split Order Based on the Product Stock Status.
- Possibility to select Quantity attribute returnable value.

#### Changed
- Improved list of available attributes by removing unused ones.

#### Fixed
- Illegal offset type - QuoteHandler
- Warning: count(): Parameter must be an array or an object that implements Countable


### [1.0.5](https://github.com/magestat/magento2-split-order/releases/tag/1.0.5) - 2019-05-11
#### Fixed
- Improved and fixed PHPDocs methods.
- Fixed If multiple products in cart from same vendor giving wrong totals.

### [1.0.4](https://github.com/magestat/magento2-split-order/releases/tag/1.0.4) - 2019-02-06
#### Added
- Compatibility with PHP 7.2.


### [1.0.3](https://github.com/magestat/magento2-split-order/releases/tag/1.0.3) - 2019-01-31
#### Added
- Add Travis to validate code standard.

#### Fixed
- Change Session to be loaded by Proxy.
- [#11](https://github.com/magestat/magento2-split-order/issues/11) Only single OrderID returned in `V1/guest-carts/:cartID/order` or `V1/carts/:cartID/order` PUT endpoints

#### Changed
- Improved module architecture & code structure.


### [1.0.2](https://github.com/magestat/magento2-split-order/releases/tag/1.0.2) - 2018-08-21
#### Added
- Option to select a product attribute to define order split.
- Capability to split shipping totals into all orders or only one.

#### Fixed
- Split shipping totals into all orders or only one.
- Set shipping to one order only.

#### Changed
- Improved module architecture.
- Stable order submission.


### [1.0.1](https://github.com/magestat/magento2-split-order/releases/tag/1.0.1) - 2018-07-20
#### Added
- CMS module settings (Enable/Disable)

#### Fixed
- Compatibility with 2.1.* EE.

#### Changed
- Improved module architecture.
- Stable order submission.
- Expose methods to be intercepted.


### [1.0.0](https://github.com/magestat/magento2-split-order/releases/tag/1.0.0) - 2018-02-15
#### Added
- Release module.
