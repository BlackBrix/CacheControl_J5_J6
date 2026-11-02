# Changelog

## 5.0.3 — 2026-11-02

- Added per-PHP-request identifiers to diagnostic log messages.
- Successfully tested an A/B/A normal-cache bypass with dynamic article content on Joomla 5.4.10 and 6.2.0.

## 5.0.2 — 2026-11-01

- Support article IDs written as `123:alias` in non-SEF routes when an exclusion rule matches `option=com_content&view=article&id=123`.
- Include routed parameters in unmatched-rule diagnostic messages.

## 5.0.1 — 2026-11-01

- Removed the overly broad default exclusion rule `option=com_content` so a newly installed plugin starts with an empty rule list.

## 5.0.0 — 2026-11-01

- Modernized the plugin's namespaced extension and service-provider structure for Joomla 5 and Joomla 6 without the backward compatibility plugin.
- Ignore empty lines and comments (`#` and `;`) in rule lists.
- Added optional diagnostic logging and improved English/German translations.
- Preserve existing exclusion-rule and re-enable settings on compatible updates.

## Earlier releases

- Earlier Joomla 4/5 version (4.0.1) in [BlackBrix/CacheControl_J4](https://github.com/BlackBrix/CacheControl_J4).
- Original Joomla 3 implementation by Crosstec; Joomla 4 migration credited to SharkyKZ.
