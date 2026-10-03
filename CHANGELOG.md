# Changelog

All notable changes to this extension are documented here. The format
is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/).

## [1.0.19] - 2026-10-03

### Fixed
- View Tracker dashboard: unique visitors no longer count a logged-in customer twice (once as a customer and once as a visitor).
- View Tracker hourly chart: views in the last second of each hour (hh:59:59) are now counted.
- The dashboard URL helper points to the existing View Tracker page, the quick view URL helper adds the product id when one is passed, and the quick view response reports the real stock quantity for simple and virtual products instead of 0.
