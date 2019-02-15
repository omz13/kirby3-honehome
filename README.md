# Kirby3 HoneHome

**Requirement:** Kirby 3.0

## Coffee, Beer, etc.

This plugin was developed because I had an itch that needed scratching. I wanted a multi-language site to switch to the best-matching language indicated by a client's `Accept-Language` instead of the default one set in the site. The code was nastier to do than I thought, it had some evil edge cases, and after being refactored became something quite elegant. That the format for languages in HTML and HTTP are subtly different was just what I needed make developing this more difficult than it should be (and if you look at the code, that is why there are tortuous substitutions between hyphens and underscores all over the place). The `utf8` suffix was similarly a thing to work around.

A bit of trivia. When I started to work on this plugin, it was called _HomeHome_ (because it made the home page twice as nice). And then one day I did a typo and called it _HoneHome_. But "hone" is a nice work because the plugin really "hones" the homepage. So it became _HoneHome_.

This plugin is free but if you use it in a commercial project to show your support you are welcome to:
- [make a donation 🍻](https://www.paypal.me/omz13/10) or
- [buy me ☕☕☕](https://buymeacoff.ee/omz13) or
- [buy a Kirby license using this affiliate link](https://a.paddle.com/v2/click/1129/36191?link=1170)

## Documentation

### Purpose

For a kirby3 site, this plugin [omz13/honehome](https://github.com/omz13/kirby3-honehome) does magical things to a site's homepage.

When would you use this plugin?

- You are running a multi-language site and you want:
  - the homepage (default or replaced) to _automagically_ switch to the language set by a browser's `Accept-Language` header (instead of defaulting to the site's default language). This is seriously cool.
  - pages to have the correct `lang` attribute (because it contains a helper function to do that what has to be done).
- You want to replace the homepage with a different page (e.g. for a blog-based site you would set this to the parent page for the blog posts).

The functional specification:

- In a multi-language installation, the homepage returns the localized homepage based on the best-match against a client's `Accept-Langauge` request.
- Provides a page method (`honehomeLang`) to set the HTML `lang` attribute (which for multi-language installations is a very good thing to do).
- A site's homepage can be set to a page specified from either a configuration file (`site/content/content.php`) or a panel content field (`content/site.txt` via `site/blueprints/site.yml`).

#### Roadmap

The non-binding list of planned features and implementation notes are:

- [x] MVP
- [x] debug headers only in debug mode
- [ ] stan to level 7

### Installation

Pick one of the following per your epistemological model:

- `composer require --no-dev omz13/kirby3-honehome`; the plugin will automagically appear in `site/plugins`.
- Download a zip of the latest release - [master.zip](https://github.com/omz13/kirby3-honehome/archive/master.zip) - and copy the contents to your `site/plugins/kirby3-honehome`.
- `git submodule add https://github.com/omz13/kirby3-honehome.git site/plugins/kirby3-honehome`.

### Configuration

The following mechanisms can be used to modify the plugin's behavior and your kirby site.

#### site/snippets/header.php

If you are running kirby as a multi-language system, for the multi-language to work nicely, you needed pages to indicate what language they are in. This plugin contains a page method - `honehomeLang( string $default = 'en' ) : string` - to return the locale in the correct format for use as an `<html>` elements `lang` attribute.

TL;DR: change your `site/snippets/header.php` so the opening `<html>` sets the `lang` attribute:

```
<html lang="<?= $page->honehomeLang('en') ?>">
```

#### via `site/config/config.php`

- `omz13.honehome.disable` - optional - default `false` - a boolean which, if `true`, disables the plugin (except for the `honehomeLang` page method.

- `omz13.honehome.homelanding` - optional - a string which is the name of the page to be used for the homepage. This setting takes priority over that specified in _c.f._ `homelanding` content field.

#### Content fields in `content/site.txt` (via blueprint `site/blueprints/site.yml`)

The plugin uses the following content fields. These are all optional; if missing or empty, they are assumed to be not applicable vis-à-via their indicated functionality.

- `homelanding` - text - optional - the name of the page to be used when the homepage is to be replaced by a different landing page. This is subservient to c.f. `omz13.honehome.homelanding` in `config.php`.

#### Blueprints

Here is a sample snippet that you could use in `site/blueprints/site.yml` so you could change the homepage to any visible children in the root. Clearly you would want to be more flexible, by perhaps filtering on a template, but it gives an idea.

```yaml
fields:
  homelanding:
    label:
      en: Home Override
      de: Überschreiben Sie die Homepage mit
      fr: Ignorer la page d'accueil avec
      nl: Negeer Homepage met
      sv: Åsidosätt hemsida med
    type: select
    options: query
    query: site.children.visible
    width: 1/3

```

### Use

1. Install and Configure as above.

2. Use a web browser or whatever to access the home page.

3. If it works, see _Coffee, Beer, etc_ above.

4. If it doesn't work... file an issue.

#### Debug mode

If the kirby site is in debug mode:

- Page requests to the homepage will have a header `X-omz13-hh-...` that contains debugging information. Yes, it outputs a lot of debugging information, but it does help locate where my code is a pile of stinking bits.

## Disclaimer

This plugin is provided "as is" with no guarantee. Use it at your own risk and always test it yourself before using it in a production environment. If you find any issues, please [create a new issue](https://github.com/omz13/kirby3-wellknown/issues/new).

## License

[BSD-3-Clause](https://opensource.org/licenses/BSD-3-Clause)
