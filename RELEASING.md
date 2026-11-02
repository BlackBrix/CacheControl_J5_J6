# Publishing the release on GitHub

1. Create a new, empty public GitHub repository (for example, `CacheControl_J5_J6`).
2. Extract the **repository ZIP** locally and upload its *contents* into the repository root (including the `src`, `services`, and `language` folders). The repository ZIP itself is **not** a Joomla installer.
3. Commit to the `main` branch and check that `cachecontrol.xml`, `README.md`, `CHANGELOG.md`, and `LICENSE` appear at the repository root.
4. Open **Releases → Draft a new release**, create tag `v5.0.3`, and set the title to `Cache Control 5.0.3`.
5. Upload the **separate** `plg_system_cachecontrol_5.0.3.zip` file under release **Assets**. This is the direct Joomla install/update ZIP.
6. In release notes, mention Joomla 5.4.10 and Joomla 6.2.0 tested (Joomla 6 without the backward compatibility plugin); normal caching only; and the A/B/A cache verification.
7. Publish the release. Users should download the installer asset, **not** GitHub's auto-generated 'Source code (zip)'.

The installer is the same tested plugin package as `plg_system_cachecontrol_5.0.3_test.zip`, with a release-friendly filename. No PHP code was changed for packaging.
