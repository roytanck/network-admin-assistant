=== Network Admin Assistant ===
Contributors: roytanck
Tags: multisite, network, plugins, widgets, users, statistics, roles
Requires at least: 5.0
Tested up to: 6.5
Stable tag: 1.2.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Helps you manage a WordPress network by displaying useful statistics.

== Description ==

This plugin will help you:

1. Find plugins and themes that are not active on any blog.
1. Determine the impact in case a theme/plugin needs to be upgraded, removed or reconfigured.
1. See if a widget is in use on any blogs, and track down where.
1. Find users who do not have a role on any site (and are not network administrators).

This plugin currently does nothing on single-site WordPress installs.

== Installation ==

1. In WordPress, got to "Plugins->Add New".
1. In the search box, type "Network Admin Assistant".
1. Find the correct plugin, and click "Install Now".
1. When the installation has finished, go to the network admin plugins screen and "Network Activate" the plugin.

== Frequently Asked Questions ==

= Why would I use this? =

When you're managing a network of WordPress sites, it can be hard to determine whether a plugin or widgets is used by (m)any of your users. This in turn makes it hard to estimate the impact in case the plugin would (need to) be removed.

Also when a plugin is updated, sometimes it will need to be reconfigured. This plugin can help you find the sites this applies to, so you don't have to manually go through all of them.

The plugin also adds a 'view' to the Users screen, that filters the list to show only users with no role anywhere on the network. You can then decide whether they need to be removed at the network level.

= Is this going to slow down my site? =

On the front-end, no. On large networks, the admin page will likely be slow, especially on larger networks. Currently the plugin scans a maximum of 9999 sites.

== Changelog ==

= 1.2.1 (2020-07-21) =
* Improvements to the admin screens HTML markup.

= 1.2 (2020-07-21) =
* Smarter caching by only refreshing when data has expired or when a user requests fresh stats.

= 1.1 (2020-07-16) =
* Adds theme statistics, as suggested by @mamaduka.

= 1.0 (2020-07-16) =
* Initial release
* This plugin replaces (and improves on) the "RT Plugin Statistics" and "RT Widget Statistics" plugins.
