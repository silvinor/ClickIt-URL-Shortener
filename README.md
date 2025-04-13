# <img src="docs/assets/img/icon.min.svg" height="32"> ClickIt-URL-Shortener

## Introduction

[c1k.it](http://c1k.it), *pronounced* as "click it," was started back in 2011 as a URL shortening service.

<img src="docs/assets/img/logo.min.svg" height="48">

... This is its source code.

### What is a URL shortening service?

A URL shortening service is a tool that converts a long URL into a shorter, more manageable version. This shortened URL redirects users to the original, longer URL. URL shortening is often used to make links easier to share, especially on platforms with character limits, like Twitter. Additionally, shortened URLs can be useful for tracking clicks and gathering analytics.

Key features of URL shortening services include:

1. **Shortened URLs** - Converts long URLs into shorter versions.
2. **Redirection** - When the shortened URL is clicked, users are redirected to the original URL.
3. **Customisable Links** - Some services allow users to customise the shortened URL to make it more recognisable or relevant.
4. **Analytics** - Many services provide data on the number of clicks, geographic location of clicks, and other usage statistics.
5. **Expiration** - Some services offer the option to set expiration dates for links, making them inactive after a certain period.
6. **Security** - URL shortening services may include features to protect users from malicious links, such as previewing the destination URL before clicking.

Popular URL shortening services include the grandfather of shorteners [tinyurl.com](http://tinyurl.com), the father [bit.ly](http://bit.ly) and the plethora of descendants, like [goo.gl](http://goo.gl) *(now defunct)*, [ow.ly](ttp://ow.ly), [is.gd](http://is.gd) and others.

### How is this one different?

In 2011 this was useful as a toolchain for my online marketing clients. But over a decade later, it's less so.

Besides, one of the ancillary reasons for this project was to enable people to self-host their own URL shortening services, but even that is now better maintained in other projects than this was, like [yourls.org](yourls.org/docs), which was started as far back as 2009 *(before I started this but after I found out it existed - ironically, in a classic case of [multiple discovery](https://en.wikipedia.org/wiki/multiple_discovery))*.

The main reason, however, was a set-and-forget redirector that was easy to install and maintain ... and thus do simple, self-serving redirection, like Harvard Business Review does with [s.hbr.org](http://s.hbr.org) or The Economist does with [econ.st](http://econ.st).  With a self-configured URL shortener, you can change the long URL, so over time your short URL can point to different content, like an updated price list, without having to change your short URL link and republishing.

### Past Versions

Version 1 ... actually, I never moved beyond version 0.5.2 ... was a fully UI-ed interface. Most of the work I did on this was UI backend stuff - user management, complexity of showing long lists, navigating around data, configuration management etc.  The actual redirection is two lines of code *(or can be)*.

### What's new in Version 2?

I realised that this could just be a single-file solution<sup>*</sup>. So I rewrote it from scratch to be so. **Version 2** is one file; install it on your PHP-enabled host, create a shorts "database" - a JSON file - and... it works. Done.

To edit, add, remove shorts, just edit the JSON file. If you need to do this daily... then this is not the tool for you.

If you're looking for a *(REST)* API to automate short generation, it's also not the tool for you.

## Resources

- **Releases** - See the [CHANGELOG.md](CHANGELOG.md) file.
- **Docs** - *(i.e. how to install and run.)* See the [docs](docs/README.md) folder.
- **To Do** - See the [TODO.md](TODO.md) file.

## Get Involved

Co-maintainers are most welcome (actually needed).
Please reach me via a PR to the core repo.

## Thank you

I hope you enjoy learning from, or even using, this source.

> Made with &#x2665; by [Silvino R.](https://github.com/silvinor)
