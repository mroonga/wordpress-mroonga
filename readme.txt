=== Mroonga ===
Contributors: komainu8, ktou
Tags: full-text-search
Requires at least: 4.8.1
Tested up to: 4.9.4
Stable tag: 4.8.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Fast and rich full text search features for WordPress!

== Description ==

This plugin provides fast and rich full text search features based on [Mroonga](http://mroonga.org/). Mroonga is a MySQL/MariaDB plugin. You don't need to introduce a new server only for full text search. You can use existing MySQL/MariaDB server. It reduces maintainance cost.

Here are features:

* Fast full text search
* Google like query syntax such as `(KEYWORD1 OR KEYWORD2) -KEYWORD3`
* Sort by relevance

Here are features to be implemented:

* Keyword highlight
* Snippet
* Related posts
* Auto complete
* Synonym
* ...

== Installation ==

This section describes how to install the plugin and get it working.

1. [Install Mroonga](http://mroonga.org/docs/install.html) to your MySQL/MariaDB
1. Upload the plugin files to the `/wp-content/plugins/mroonga` directory, or install the plugin through the WordPress plugins screen directly
1. Activate the plugin through the 'Plugins' screen in WordPress
<!-- 1. Use the Settings->Mroonga screen to configure the plugin -->

Now, the search box uses Mroonga's full text search features instead of the default `LIKE` based full text search.

== Frequently Asked Questions ==

= QUESTION PLACEHOLDER =

ANSWER PLACEHOLDER

== Screenshots ==

Not yet.

<!--
1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Note that the screenshot is taken from
the /assets directory or the directory that contains the stable readme.txt (tags or trunk). Screenshots in the /assets
directory take precedence. For example, `/assets/screenshot-1.png` would win over `/tags/4.3/screenshot-1.png`
(or jpg, jpeg, gif).
2. This is the second screen shot
-->

== Changelog ==

= 0.1.1 - 2018-02-09 =

* Stopped to index needless contents.
  [GitHub#2][Patch by Yuya Tajima]

* Added Japanese error messages.
  [GitHub#2][Patch by Yuya Tajima]

= 0.1.0 - 2017-08-16 =

* Initial release.

== Upgrade Notice ==

= 0.1.1 =

None.

= 0.1.0 =

None.
