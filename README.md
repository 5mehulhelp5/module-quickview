# Panth QuickView for Magento 2

Smart Quick View module for Magento 2 with full Hyva theme compatibility.
Lets shoppers preview product details (images, price, stock, description)
in a responsive modal without leaving the category page, and adds a
built-in admin Product View Tracker dashboard.

## Features

- **Quick View Modal** -- AJAX-powered modal with image gallery, price,
  stock status, short description, and Add to Cart for simple/virtual
  products.
- **Hyva + Luma support** -- Alpine.js-driven templates for Hyva,
  separate Luma-compatible templates included.
- **Product View Tracker** -- Admin dashboard with 30-day trend charts,
  hourly distribution, most-viewed products, top customers, and a
  detailed per-view drill-down page.
- **Recently Viewed persistence** -- Tracks views in a dedicated
  database table with duplicate-suppression (5-minute window).
- **Login / Register modal** -- Prompts guest users to sign in before
  performing account-required actions.
- **Toast notifications** -- Lightweight, stackable notification
  component for Add to Cart / Compare feedback.
- **Configurable** -- Enable/disable module, toggle image gallery,
  short description, SKU, stock status, and Add to Cart button from
  admin configuration.

## Requirements

- Magento Open Source / Adobe Commerce 2.4.4 -- 2.4.8
- PHP 8.1, 8.2, 8.3, or 8.4
- `mage2kishan/module-core` ^1.0

## Installation

```bash
composer require mage2kishan/module-quickview
bin/magento module:enable Panth_QuickView
bin/magento setup:upgrade
bin/magento cache:flush
```

## Configuration

Navigate to **Stores > Configuration > Panth Extensions > Quick View**
to enable/disable the module and control which product details are shown
in the modal.

## Support

- **Email:** kishansavaliyakb@gmail.com
- **Website:** https://kishansavaliya.com
- **WhatsApp:** +91 84012 70422
