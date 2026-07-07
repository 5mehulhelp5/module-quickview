# Changelog

All notable changes to this extension are documented here. The format
is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/).

## [1.0.7]

### Changed
- Code cleanup: removed redundant inline comments and docblocks from the
  PHP source for a leaner codebase. No functional changes.

## [1.0.6]

### Changed
- README polish: pointed marketplace links to the product website, and
  redesigned the Quick Links section as a clean reference table.

## [1.0.5]

### Changed
- Rewrote the README for clarity and search visibility: plain English copy,
  a Quick Answer summary for answer engines, accurate configuration table
  matched to the real settings, and the live product page link.

## [1.0.0] -- Initial release

### Added
- **Quick View Modal** -- AJAX-powered, responsive modal with product
  image gallery, price, stock status, short description, and Add to
  Cart for simple/virtual products.
- **Hyva + Luma templates** -- Alpine.js-driven templates for Hyva
  theme, separate Luma-compatible templates included.
- **Product View Tracker** -- Admin dashboard with stat cards, 30-day
  trend chart, hourly distribution chart, most-viewed products, top
  customers, and recent views feed.
- **View Detail page** -- Drill-down admin page showing full product
  info, view metadata, and view history timelines.
- **Recently Viewed persistence** -- Dedicated database table with
  duplicate-suppression (5-minute window) and visitor fingerprinting.
- **Login / Register modal** -- Prompts guest users to sign in before
  performing account-required actions.
- **Toast notifications** -- Lightweight, stackable notification
  component for Add to Cart / Compare feedback.
- **Admin configuration** -- Enable/disable module, toggle image
  gallery, short description, SKU, stock status, and Add to Cart
  button from Stores > Configuration.
- **ACL** -- Dedicated admin ACL resource `Panth_QuickView::view_tracker`.

### Compatibility
- Magento Open Source / Adobe Commerce 2.4.4 -- 2.4.8
- PHP 8.1, 8.2, 8.3, 8.4

---

## Support

For all questions, bug reports, or feature requests:

- **Email:** kishansavaliyakb@gmail.com
- **Website:** https://kishansavaliya.com
- **WhatsApp:** +91 84012 70422
