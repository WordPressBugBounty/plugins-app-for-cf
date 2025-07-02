=== App for Cloudflare® ===
Contributors: digitalpoint
Donate link: https://appforcf.com/items/app-for-cloudflare%C2%AE-pro.1/?utm_source=readme&utm_medium=wordpress&utm_campaign=plugin
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html
Tags: cloudflare, caching, performance, security, SEO
Requires at least: 5.2
Tested up to: 6.8
Requires PHP: 5.4.0
Stable tag: 1.9.4

All things Cloudflare (caching, flexible SSL, Turnstile, settings, rules, analytics, media in R2, image transforms [AVIF, WebP], secure admin area).

== Description ==

Unlock advanced Cloudflare features without being a network administrator or developer. Works with any Cloudflare plan (including Free), no Automatic Platform Optimization (APO) subscription needed.

* Cache HTML at network edge
* Preload JavaScript and CSS
* View/set all Cloudflare settings
* Fixes Cloudflare Flexible SSL redirect loops
* Fixes situation when IPs are coming through as Cloudflare IPs rather than user IPs
* Cloudflare analytics on dashboard
* Purge cache
* Automatic image transformations (automatically serve AVIF/WebP versions to browsers that support them)
* Turnstile CAPTCHA system for registrations, logins, password reset and/or comments
* View Page rules, Cache rules, Firewall rules, IP Address rules, User Agent rules
* View Zero Trust Network Access setup
* View DMARC statistics
* Included tools: HTTP request trace, IP address details, domain details, WHOIS

**Directly cache HTML**

App for Cloudflare® can automatically cache your HTML pages in 330+ Cloudflare data centers around the world. "Standard" WordPress caching plugins can’t escape the laws of physics because **information can't travel faster than the speed of light** (even if the page is cached, the cache exists on your physical origin server, which can be **over 20,000 km from an end user**). Caching content in Cloudflare data centers makes your website faster by putting your website cache closer to end-users (95% of the world's population is within 50ms of a Cloudflare data center).

This can be done **without Cloudflare Workers or even a Page Rule** (done with a single Cache Rule on Cloudflare's side, and custom code in the plugin).

**Preload JavaScript and CSS**

Speed your site up by using the option that instructs browsers to preload JavaScript and CSS used to render the page being viewed. Can be used on its own, or in conjunction with Cloudflare's [Early Hints](https://blog.cloudflare.com/early-hints/) function.

**Manage all Cloudflare settings**

All Cloudflare settings can be changed directly within your WordPress admin area.

Includes **Easy config** function that will optimally set your Cloudflare zone settings for WordPress.

**Fixes Cloudflare Flexible SSL redirect loops**

Automatically fixes HTTPS redirect loops when using Cloudflare's Flexible SSL option (traffic between user and Cloudflare is encrypted, but traffic between Cloudflare and origin server is not).

**Handles user IP addresses**

Automatically handles the situation where your web server is passing Cloudflare IP addresses rather than the IP address of the user making the request.

**Turnstile CAPTCHA**

Cloudflare Turnstile CAPTCHA support for registration, login, password reset and/or comment forms. Single-click setup (done transparently via API call).

**Network analytics**

View network stats for your website directly within your WordPress admin area with a dashboard widget.

**View rules & firewall**

Quickly review your site's Cloudflare rules and firewall settings from within your WordPress admin area. Includes:

* Page rules
* Cache rules
* Firewall custom rules
* IP address rules
* User agent blocking

**DMARC management**

Track third parties that are sending email on your behalf (for example an email provider you have authorized like Gmail or Outlook). You can also see unauthorized email senders or spammers sending email on behalf of your domain.

**Multisite network support**

You can have a network-wide Cloudflare API token that can be overridden on a per site basis. In the case where a multisite network operator has the site domains in a single Cloudflare account, they can allow the site users to use Cloudflare features for their individual site without disclosing the underlying actual API token.

Additionally, a single Pro license for the main network site allows the media from all sites in the network to be stored in the cloud, within a single Cloudflare R2 bucket.

**Image Transformations**

Supports Cloudflare's Image Transformation service, which allows Media images to automatically be served in the best format that a browser supports (AVIF, WebP, etc). Additionally, smaller images can be automatically served to users on very slow network connections. No web server configuration required.

**Store media in the cloud [Premium]**

Easily and seamlessly store your WordPress media in the cloud with [Cloudflare R2](https://www.cloudflare.com/developer-platform/r2/). This allows you to offload resources (both bandwidth and disk space) from your server. The **first 10GB is free**, and only costs $0.015 per GB thereafter (ex. if you had 100GB of media, it would cost $1.35 per month to store it in the cloud).

Includes ability to migrate existing media from local filesystem to R2 (or from R2 to local filesystem). Works with individual media, or all media in bulk (includes web-based migration as well as a shell/WP-CLI option).

**Automatically convert uploaded JPG or PNG images to WebP [Premium]**

While this was intended for use with R2, it can be used without using R2 if you want.

**Protect admin area [Premium]**

Utilize [Zero Trust Network Access](https://www.cloudflare.com/zero-trust/products/access/) to authenticate users before they access your WordPress admin area.

**Manage rules & firewall [Premium]**

The premium version unlocks the ability to manage (create, delete, suspend and unsuspend) Cloudflare rules and firewall definitions. In addition to defining your own rules, you can deploy useful rules with a single click:

* Block traffic from certain countries (or Tor exit nodes widely used by spammers and hackers)
* Block AI scrapers & crawlers (block bots from scraping your content for AI applications like model training)
* Force a challenge before users can register (bot/spammer mitigation)
* Cache static content
* Automatically block the IP address(es) of spammers for a period of time

**Backup & restore [Premium]**

You can backup and restore some of your most important Cloudflare configuration settings:

* Zero Trust Access Policies
* Firewall Rules
* Firewall IP Access Rules
* Firewall User Agent Blocking
* Page Rules
* Cache Rules

Backups can be restored to different zones (for example if you had extensive configuration for a zone, you could give another zone the same configuration through a backup restore).

**Other features**

* API calls are done exclusively through API Tokens (with the [minimum required permissions](https://appforcf.com/threads/permissions-needed-for-app-for-cloudflare%C2%AE.3/?utm_source=readme&utm_medium=wordpress&utm_campaign=plugin)) and **not** a Global API Key. Global API Keys are an incredibly bad idea from a security standpoint.
* Ability to purge Cloudflare cache from WordPress admin.
* Cached pages are automatically purged when a post/page is edited (just the necessary pages, not all pages). Stale content is not served to users.
* Ability to designate an individual admin user to manage settings (maybe you don't want all admins to have the ability to change things in Cloudflare).
* Ability to use WordPress filters to add your own logic to things (for example maybe you don't want to cache a certain page or post for whatever reason).
* All JavaScript is native (no dependencies on jQuery or anything else)
* No third-party PHP libraries used (no dependencies on other libs)

== Installation ==

**From your WordPress dashboard**

1. Navigate to "Plugins" -> Add New
1. Search for `App for Cloudflare`
1. Activate App for Cloudflare® from your Plugins area

**Manual upload**

1. Download App for Cloudflare®
1. Upload the `app-for-cf` directory to your `/wp-content/plugins/` directory
1. Activate App for Cloudflare® from your Plugins area

== Frequently Asked Questions ==

= How does the Guest Page Caching work? =

It's done by using a Cloudflare Cache Rule that makes HTML pages eligible to be cached. Making a page eligible doesn't necessarily make it always cached (for example if a user is logged into the site or the request is a POST, you wouldn't want the page to be cached). No need for any kludgey cache busting mechanism that alters your URLs.

This allows it to be done in a "clean" way, without the need for Cloudflare's Automatic Platform Optimization (APO) plugin/subscription (normally $5/month for Cloudflare Free plans). It also does not require the added complexity or expense of Workers (Workers are billed per request). Additionally it does not require the use of a Page Rule slot on your Cloudflare zone.

**This plugin can do Guest Page Caching for free, for everyone.**

= How can I test improvements on my site when using Guest Page Caching? =

An easy way to test it is with a testing tool like [GTmetrix](https://gtmetrix.com/). Test it without Guest Page Caching enabled and test it again with Guest Page Caching enabled and compare the results.

If you are a little more technical, you can look at the `cf-cache-status` HTTP header on the page request. If you see a value of `HIT`, the request is being successfully being served from Cloudflare's edge cache.

If you want to compare the speed of WordPress sites from different cities, try these:

* [Matt Mullenweg](https://speedvitals.com/ttfb-test?url=https://ma.tt) (not using this plugin)
* [Shawn Hogan](https://speedvitals.com/ttfb-test?url=https://shawnhogan.com) (using this plugin)

= Does this work with multisite networks? =
Yes. Not only does it work as a standard plugin on any site within the network, it can be deployed as a "network activated" plugin (automatically available to all sites within the network). Additionally, there are settings available to the network admins where you can optionally set a default API token (that is used, but not disclosed to the individual sites). Individual sites can set a different API token to override the network-wide token if needed. This is useful in a scenario where all (or most) of the sites in the network are on the same Cloudflare account and can share the same API token.

The Pro version allows a single Cloudflare R2 bucket to be used for storing the media for all sites in the multisite network.

= I locked myself out of settings =

There is an option to allow settings to be changed only by one specific admin account. If you use that setting, and then lose or change the admin account you assigned to that role, it can be problematic. If you need to bypass that option, you can by adding the following to your WordPress **wp-config.php** file:

`define('CLOUDFLARE_BYPASS_USER_LOCK', true);`

= I have an idea for this plugin, can I make a suggestion? =

Yes, please do! You can find the suggestion area [over here](https://appforcf.com/support/suggestions.4/?utm_source=readme&utm_medium=wordpress&utm_campaign=plugin).

== Screenshots ==

1. See Cloudflare stats for your domain right on the WordPress dashboard.
2. Setup allows you to enter your Cloudflare API token with the minimum permissions required.
3. Control Cloudflare zone settings (SSL/TLS options shown here).
4. Control Cloudflare zone settings (Security options shown here).
5. Control Cloudflare zone settings (Speed options shown here).
6. Control Cloudflare zone settings (Caching options shown here).
7. Control Cloudflare zone settings (Network options shown here).
8. Control Cloudflare zone settings (Scrape Shield options shown here).
9. Guest page caching allows you to cache your HTML pages in Cloudflare data centers (this allows your site to deliver content to your users from the data center closest to them).
10. Cloudflare Firewall Rules, User Agent Rules and IP Address Rules are viewable within WordPress (the Pro version allows editing/creating/deleting as well).
11. An easy interface to block traffic by country before the request gets to your server.
12. Create User Agent Rule from within WordPress (Pro version).
13. Create IP Address Rule from within WordPress (Pro version).
14. Page Rules and Cache Rules are viewable within WordPress (the Pro version allows editing/creating/deleting as well).
15. Zero-Trust Access policies are viewable within WordPress (the Pro version allows simple auto-configuration to protect your WordPress admin area).
16. R2 object storage allows you to store your WordPress media in the cloud seamlessly (premium feature/part of Pro).
17. R2 configuration is simple/automatic.
18. DMARC reporting allows you to see entities sending email on your behalf.
19. Cloudflare Purge Cache is available from within the WordPress admin area.
20. An HTTP request trace tool allows you to simulate an HTTP request passing through Cloudflare's network.  This allows you to see which products and rules are triggering actions on a request.
21. An IP address lookup tool allows you to get some basic info about any IP address (works with IPv4 as well as IPv6 addresses).
22. Lookup info about any domain.
23. Get registration info about any domain.
24. The Pro version allows you to backup and restore some Cloudflare settings.
25. Media stored in R2 shows an orange cloud in the media browser.
26. Move individual media to/from R2.
27. Move all media in bulk to/from R2.
28. For multisite networks, you can use a single Cloudflare API token for all your sites in your network (which can be optionally overridden on a per site basis).
29. For multisite networks, you can optionally use a single R2 bucket to store the media across all your sites.

== Changelog ==
= 1.9.4 (2025-07-01) =
* Updated charting library (Chart.js) to 4.5.0
* Simplified internal function for converting IPs to/from binary representations
* Fixed issue where Purge Cache button in admin bar wouldn't work
* New option (Preload resources): automatically adds Link header that includes JavaScript and CSS URLs for the page (tells browsers to preload resources used by the page)

= 1.9.3.1 (2025-06-05) =
* Fixed issue where guest page caching could by disabled, but not enabled
* Added support for Cache Rules that contain browser bypass TTL

= 1.9.3 (2025-06-02) =
* All JavaScript rewritten to be native (no dependency on jQuery)
* Added support for Turnstile CAPTCHA for use on Registration, Login, Password Reset and/or Comment forms
* Purge cache option in admin bar displays icon, but not text when displayed on thin screens (for example, cell phones)
* Fixed cosmetic wrap issue on Settings page when site is within a multisite network

= 1.9.2 (2025-05-12) =
* Cloudflare Image Transformation support for WordPress Media
* Updated charting library (Chart.js) to 4.4.9

= 1.9.1 (2025-03-26) =
* Added new Cloudflare setting (under Security): AI Labyrinth

= 1.9.0 (2025-01-28) =
* Updated charting library (Chart.js) to 4.4.7
* Added new Cloudflare setting (under Security): AI Bots
* Fixed issue where you couldn't uncheck the `Only your user account` option

= 1.8.8.1 (2024-11-07) =
* When using guest page caching, decouple the purge cache mechanism from the HTTP request (the purge cache action is sent to WordPress' cron system)
* Updated charting library (Chart.js) to 4.4.6

= 1.8.8 (2024-09-30) =
* Added new Cloudflare setting (under Speed): Speed Brain
* Easy Config enables Speed Brain
* Added support for setting `SSL/TLS Encryption Mode` to `Strict (SSL-only origin pull)` (for Enterprise zones)
* Added new Cloudflare setting (under SSL/TLS): Encrypted Client Hello
* Added new Cloudflare setting (under Security): Leaked credentials

= 1.8.7 (2024-08-28) =
* Fixed issue with deleting a Page Cache rule (change to Cloudflare API)
* Updated charting library (Chart.js) to 4.4.4

= 1.8.6 (2024-08-02) =
* Added new Cloudflare setting (under Security): Replace insecure JavaScript libraries
* New option: Purge cache button in admin bar
* Fixed issue where "Block IP address on spam flag in comment queue" option couldn't be unchecked
* New option: Convert uploaded media to WebP [Premium]

= 1.8.5 (2024-06-25) =
* Removed Brotli compression setting (it's now always on in Cloudflare)
* Removed Minify settings (they have been deprecated and will be removed from Cloudflare soon)
* Removed Server-side Exclude setting (it has been deprecated and will be removed from Cloudflare soon)
* Added framework for new option to create Firewall Rule to block AI scrapers & crawlers
* Updated charting library (Chart.js) to 4.4.3

= 1.8.4 (2024-05-29) =
* Added deprecation notice for Auto-Minify setting

= 1.8.3 (2024-03-12) =
* When installing Pro plugin, replace the default activate plugin link with a link to enter license key (the plugin isn't "activated" in the traditional sense)
* Updated charting library (Chart.js) to 4.4.2

= 1.8.2 (2024-01-21) =
* Fixed typo when installation is the primary site in a multisite network
* If API token can't be verified, present an error about it being an invalid API token immediately
* Changed wording of the "API tokens & keys" button
* Updated charting library (Chart.js) to 4.4.1
* A few custom actions are intended to execute last, so PHP_INT_MAX was used as the priority, however it was causing issues on certain servers so the priority was lowered (but still very high)

= 1.8.1 (2023-12-01) =
* Cloudflare statistics charts on dashboard dynamically resize properly when resizing window
* Added ability for individual API calls to ignore multiple error codes instead of just one
* Existing media can be moved to/from R2 individually or in bulk [Premium]

= 1.8.0 (2023-11-13) =
* The API calls necessary to build the Cloudflare settings page are now run in parallel (it's currently 10 API calls that were previously made sequentially). Viewing (and editing) settings is significantly faster now (it's as fast as the single slowest API call, rather than as slow as all 10 API calls added together).
* Raised timeout for Cloudflare API calls from 5 seconds to 15 seconds
* For multisite/network installations, there is a new settings page that allows you to set some network-side settings (for example the API token to use) across all sites without specifically setting it for each site.  See Network Admin -> Settings -> Cloudflare
* If there's a multisite network API token set, an individual site can still override that API token (allows a situation where most sites are on a single Cloudflare account can use the network-side API token, but the ones that aren't can still specify a different one)
* Added info page about using R2 in a multisite setup (using a single bucket for all sites)
* Added sanity check (make sure it exists) when attempting to display an actioned template
* Added more sanity checks for unexpected Cloudflare API results
* The intel tools (IP address details, Domain details, WHOIS) can't be used (and are removed from the navigation) if a site doesn't have their own API token (in a scenario where they are using a default multisite network API token). This is because there is a *very* small monthly quota for API calls for the entire Cloudflare account/API token (around 100 per month).
* Fixed issue with HTTP Request Trace tool

= 1.7.7 (2023-11-03) =
* Added link for info about why each Cloudflare token permission is needed
* Increased the number of URLs being purged per API call from 8 to 30
* Suppress API rate limit error message when using guest page caching and the backend is purging individual URLs from Cloudflare's cache (if there are a ton of posts being added at once, or edited quickly over and over, a site might hit rate limits after about 40 new posts/edits/comments in a single minute). Since it's not catastrophic if a cache purge didn't successfully run, we are just going to ignore the failure and let the cache expire on its own.
* Better handling of situation where Cloudflare API is down/unavailable
* Changed how menus are built (old method was deprecated in PHP 8.x)
* Removed "Security -> Privacy Pass Support" setting (it's been deprecated by Cloudflare and is no longer used)

= 1.7.6.1 (2023-10-25) =
* Clean up some layout anomalies when using a right-to-left language
* The Cloudflare Fonts option ID has changed. This addresses that (it's what I get for giving the ability to toggle options that Cloudflare has deemed "beta"... they are subject to change).
* Added a sanity check so if future option IDs change, it won't throw an error (along with not being able to change them). Instead, that option won't change until the ID is updated.

= 1.7.6 (2023-10-22) =
* Fixed issue where boolean settings wouldn't always be parsed to boolean values
* Fixed issue where HTTP Strict Transport Security (HSTS) settings wouldn't always be changeable
* Added support for Super Bot Fight Mode settings: Likely Automated, Definitely Automated, Verified Bots, Static Resource Protection, Optimize For WordPress, JavaScript Detections

= 1.7.5.1 (2023-10-21) =
* Better handling of unexpected Cloudflare API changes

= 1.7.5 (2023-10-19) =
* Fixed issue with modal confirmation dialog not scrolling when it goes off the screen (mainly an issue when enabling R2 for media from a mobile devices)
* Cloudflare made a change to the bot management API (but only for paid plans), this version addresses that.

= 1.7.4 (2023-10-12) =
* Fixed issue where a settings page redirect would fail because WordPress had already output HTML in some cases
* Cosmetic clean-up here and there
* Notice in Media section about using/setting up R2 is dismissible
* Added support for new Cloudflare setting: Speed -> Optimization -> Content Optimization -> Cloudflare Fonts
* "Easy config" enables Cloudflare Fonts
* Settings that are deemed beta by Cloudflare, are shown with a beta label
* Better handling of cases where there's an error when making an API request

= 1.7.3 (2023-10-05) =
* Initial release of App for Cloudflare® for WordPress.

The WordPress version is kept in feature parity with the XenForo version. You can find the update changelog for older versions (if you are curious), [over here](https://xenforo.com/community/resources/digitalpoint-app-for-cloudflare%C2%AE.8750/update/45114/).

== Upgrade Notice ==

= 1.9.3 =
Turnstile support

= 1.9.2 =
Image transformation support

= 1.7.3 =
This version is the first version hosted on wordpress.org.