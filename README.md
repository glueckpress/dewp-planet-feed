# Micro.blog
* Contributors:      @glueckpress, @ocean90, @florianbrinkmann, @zodiac1978
* Requires at least: 3.8
* Tested up to:      4.7.4
* License:           GPLv3
* License URI:       http://www.gnu.org/licenses/gpl-3.0.html

## Description
WordPress plugin that generates a custom feed `microblog` for posts. Adds a checkbox to the _Publish_ meta box in order to add a post to the custom feed.

Provides a custom RSS template to adjust feed output for Micro.blog.

### Feed URLs:

```
http://example.com/feed/microblog/
http://example.com?feed=microblob
```

### Filters:

```php
apply_filters( 'microblog_feed__post_types', array $post_types )
```

Array of post types to generate the custom feed from.

**Parameters:**

* `$post_types (array)` – _default:_ `array( 'post' )`

```php
apply_filters( 'microblog_feed__capability', string $capability )
```

User capability required to add posts to the custom feed.

**Parameters:**

* `$capability (string)` – _default:_ `publish_posts`

```php
apply_filters( 'microblog__pub_section_label', string $label_text, object $post, string|boolean $value )
```

User capability required to add posts to the custom feed.

**Parameters:**

* `$label_text (string)` – _default:_ `Publish to Micro.blog`/`Published on Micro.blog`
* `$post` (object) – the current Post object
* `$value` (string|bool) – `1` when checkbox is checked, `false`when not checked

### Planned features:

- Allow 1 feed per WordPress user: add Micro.blog user name to WordPress profile and generate a dedicated feed if the current user has a Micro.blog account.
- Allow for custom feed templates.

---

## Changelog

### 0.1.0
* Initial fork from [DEWP Planet Feed](https://github.com/deworg/dewp-planet-feed)
