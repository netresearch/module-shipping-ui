# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## Unreleased

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

