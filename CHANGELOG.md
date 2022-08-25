# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## 2.3.2

### Fixed

- Remove invalid LESS extend, reported via issue [#6](https://github.com/netresearch/module-shipping-ui/issues/6).
- Render services with "radioset" input type

## 2.3.1

### Fixed

- Replace deprecated XHR callback in checkout, reported via issue [#5](https://github.com/netresearch/module-shipping-ui/issues/5).

## 2.3.0

Magento 2.4.4 compatibility release

### Added

- Support for Magento 2.4.4

### Removed

- Support for PHP 7.1

## 2.2.1

### Fixed

- Display selected shipping services in batch emails, reported via issue [#4](https://github.com/netresearch/module-shipping-ui/issues/4).

## 2.2.0

### Added

- Display return shipment labels in customer account.
- Display config validation results.

### Fixed

- Render delivery location address in checkout progress sidebar.

## 2.1.1

### Changed

- Use public shipping core interface.

## 2.1.0

### Added

- Admin panel returns feature for Magento Open Source.

## 2.0.3

### Fixed

- Prevent CSP violations when loading the location finder map in checkout.
- Prevent JS error in checkout, contributed by [falkone](https://github.com/falkone) via [PR #2](https://github.com/netresearch/module-shipping-ui/pull/2).
- Prevent empty country dropdown in location finder.

## 2.0.2

### Fixed

- Print service fee total on PDF sales documents.

## 2.0.1

### Fixed

- Unset service selection when cart changes to contain only virtual products.

## 2.0.0

### Changed

- Upgrade `netresearch/module-shipping-core` package to major version 2.
- Work around [Dotdigitalgroup_Sms issue #1](https://github.com/dotmailer/dotmailer-magento2-extension-sms/issues/1)
  by reading the input's error message instead of accessing the now unavailable validation result.
- Refresh service box data when entering or refreshing the checkout page.
- Add class attribute to service box markup for carrier identification.

## 1.0.0

Initial release

