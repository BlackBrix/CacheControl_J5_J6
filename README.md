# Cache Control for Joomla 5 and 6

**Version 5.0.3** — Joomla system plugin for selectively disabling normal Joomla caching on configured requests.

Tested on **Joomla 5.4.10** and **Joomla 6.2.0**, including Joomla 6 with its backward compatibility plugin disabled. These are the tested versions, not a guarantee of compatibility with every future Joomla release.

## What it does

The plugin compares routed request parameters with a list of rules, and sets Joomla's application `caching` configuration to `0` for matching front-end requests. For example, dynamic articles, traffic feeds or calendar menu items can bypass Joomla's normal caching while unrelated pages keep it enabled.

This is **not** a replacement for, or an exclusion mechanism for, Joomla's separate **System – Page Cache** plugin. Keep System – Page Cache disabled for the use case described here. Independent browser/CDN/proxy/extension caches are not controlled by this plugin.

## Requirements

- Joomla 5.4.10+ or Joomla 6 (tested with 6.2.0; no compatibility plugin required)
- PHP version supported by the corresponding Joomla release (PHP 8+, including `str_starts_with()`)
- Joomla normal caching may be enabled under **Global Configuration → System → Cache Settings**

## Installation

1. Download the **`plg_system_cachecontrol_5.0.3.zip`** asset from the GitHub release. **Do not install the repository/source ZIP.**
2. In Joomla administrator, open **System → Install → Extensions**, and upload the ZIP.
3. Open **System → Plugins**, find **System – Cache Control**, and enable it.
4. Place the plugin early in the ordering of system plugins if other plugins may use cache settings in `onAfterRoute`.
5. Add your own exclusion rules. A new installation intentionally has **no default rule**.

Install the ZIP over an existing compatible Cache Control installation to update it; keep a backup beforehand. The existing `definitions` and `reenable_afterdispatch` parameters are retained on an ordinary update. Test updates in staging when migrating from older releases.

## Rules

One line per rule, with ampersand-separated request parameters; **all** parameters listed on a line must match. Extra request parameters are ignored. Blank lines and lines beginning with `#` or `;` are ignored.

```ini
# Disable caching for specific Joomla articles
option=com_content&view=article&id=1689
option=com_content&view=article&id=2486

# Disable caching for a specific menu item
Itemid=2088

# Match any existing article ID (broad rule; use with care)
option=com_content&view=article&id=?
```

Avoid broad rules such as `option=com_content` unless you intend to disable normal caching for **all** matching component requests.

For `option=com_content&view=article`, a numeric article ID rule such as `id=2486` also matches Joomla's non-SEF `id=2486:article-alias` representation. Other parameters are compared as strings. Routing is evaluated after Joomla's `onAfterRoute` event, so rules normally work with both SEF enabled and disabled. The numeric ID normalization is specific to `com_content` article rules.

For best results, use one of the standard article-ID or menu-ID rules above and verify matches using diagnostic logging.

## Settings

**Exclusion rules:** one per line; may be empty (an empty list excludes nothing).

**Re-enable caching after component dispatch:**
- **No (default):** keep `caching=0` for the rest of the matched front-end request. Recommended when the whole request should avoid Joomla's normal cache setting.
- **Yes:** restore the original caching setting on `onAfterDispatch`, after main component dispatch, allowing later operations (e.g. modules) to use caching again. Existing saved values are not overwritten when upgrading.

**Diagnostic logging:** when enabled **and** Joomla's global **Debug System** is enabled, records matches and non-matches to `plg_system_cachecontrol.php` in Joomla's configured log directory. Each entry has a `[req:...]` identifier. Multiple entries with the same identifier came from the same PHP request. Disable both diagnostic logging and global Debug System on production sites after testing.

## How to verify cache behavior (A/B/A test)

With Joomla's normal caching on and System – Page Cache off, create a test article that produces dynamic server-side output (e.g. PHP execution timestamp/random number through Sourcerer, if installed):

1. **A:** Comment out the article rule, clear Joomla's cache and reload repeatedly. Cached output should remain unchanged.
2. **B:** Enable the article rule, save, clear the cache and reload repeatedly. Dynamic output should now change each time.
3. **A:** Comment out the rule again, save, clear the cache and reload repeatedly. Cached output should again remain unchanged.

This A/B/A test was successfully performed on Joomla 5.4.10 and Joomla 6.2.0 with the v5.0.3 code.

## Source layout

`cachecontrol.xml` is the Joomla plugin manifest; `services/provider.php` is the dependency injection service provider; `src/Extension/CacheControl.php` implements the event listeners and rule matching; `language/` contains English and German backend translations.

## History and credits

This plugin descends from the Cache Control plugin originally developed by **Crosstec GmbH & Co. KG** (Joomla 3), adapted for Joomla 4 by **SharkyKZ**, and previously distributed for Joomla 4/5 by **BlackBrix** at [CacheControl_J4](https://github.com/BlackBrix/CacheControl_J4). Version 5.0.3 modernizes the implementation for Joomla 5/6 and adds diagnostic logging and robust rule parsing.

## License

GPL-2.0-or-later. See [LICENSE](LICENSE). Keep attribution to the original authors and contributors when redistributing.
