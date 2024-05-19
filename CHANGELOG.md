# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [v2.0.0] - 2024-05-19

### Changed

- Default arrays are declared as `[]`, not as `array()`. &#x1F937;
- Info page tweaks.
- QR-Code images are fetched if the content type is given in `$config`, instead of redirects.  *(Allows one to hide the QR-Code engine used.)*
- How the dialog is centered on the screen.

### Added

- **Plugins**! You can now extend the code with optional plugins, included in this rel. is:
    - vCard support
- New command - "`*`" - to generate a **`sitemap.xml`** file.
- Function - `http_get_remote_file()` - to fetch files remotely.
- Custom inline CSS to center dialog.
- `.htaccess` rule to generate `sitemap.xml`.
- Blocking AI bots in `robots.txt`.



## [v2.0.0-beta-2] - 2024-05-17

### Changed

- `$images` is now a native array, instead of a JSON string that needed to be re-read. &#x1F937;
- Settings are now loaded before the command processor.
- Moved a bunch of hard coded variables to a `$config` object that can now be overridden by the settings JSON
- Cleaned up the `.htaccess` file
- More edits to the `README.md` file

### Added

- Added better base URL processing, with a `$config->base_url` override that can be set in the settings JSON
- Added [Docs](https://github.com/vinorodrigues/ClickIt-URL-Shortener/tree/master/docs) to the repo
- Allow injection of custom CSS and JS, through the settings JSON

### Removed

- Background gradient CSS

### Fixed

- Use of `highlight.js` in the install page


## [v2.0.0-beta-1] - 2024-05-16

### Changed

- Un-retire the project
- Full re-write... no use writing a changelog, it's basically a new product :)


## [Abandoned] - Version not built

### Milestones

- 0.6 Beta - Plugin framework
- 0.7 Beta - Basic Reporting
- 0.9 RC1 - Basic cleanups for release candidate
- 1.0 - Feature freez
- 1.1 - Jason and XML API's
- (D7) - Drupal plugin
- (WP) - Wordpress plugin



## [v0.5.0-beta] - 2011-05-14

### Changed

- Mobile links (QR-Code) - prepend "@" to your short URL
- Copy to clipboard (using clippy.swf)



## [v0.4.0-beta] - 2011-05-9

### Changed

- ShortURL integration with Piwik (we could not get server-side GA to work)
- Home page integration with Piwik and Twitter
- Events table for extra debug info on some exceptions (for debugging)
- Automated update process



## [v0.3.0-beta] - 2011-05-04

### Changed

- User management & settings management



## [v0.2.0-beta] - 2011-04-21

### Changed

- Basic user functionality like sign-on and URL creation
- Hash-tag engine, internationalization
- Home page integration with Facebook and Google Analytics



## [v0.1.0-beta] - 2011-03-14

### Changed

- Basic site functionality, installer and template engine
- Database access external, you'll need to use something like phpMyAdmin


-----
> Made with &#x2665; by [Vino Rodrigues](https://github.com/vinorodrigues)


[v2.0.0]: https://github.com/vinorodrigues/ClickIt-URL-Shortener/compare/v2.0.0-beta-2...v2.0.0
[v2.0.0-beta-2]: https://github.com/vinorodrigues/ClickIt-URL-Shortener/compare/v2.0.0-beta-1...v2.0.0-beta-2
[v2.0.0-beta-1]: https://github.com/vinorodrigues/ClickIt-URL-Shortener/compare/v0.5.3...HEAD
[Abandoned]: https://github.com/vinorodrigues/ClickIt-URL-Shortener/releases/tag/v0.5.3
[v0.5.0-beta]: https://github.com/vinorodrigues/ClickIt-URL-Shortener
[v0.4.0-beta]: https://github.com/vinorodrigues/ClickIt-URL-Shortener
[v0.3.0-beta]: https://github.com/vinorodrigues/ClickIt-URL-Shortener
[v0.2.0-beta]: https://github.com/vinorodrigues/ClickIt-URL-Shortener
[v0.1.0-beta]: https://github.com/vinorodrigues/ClickIt-URL-Shortener
