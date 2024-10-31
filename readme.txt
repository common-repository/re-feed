=== RE Feed ===
Contributors: customscripts, chrisbuck
Tags: real estate, custom post type, rss, feed, xml, atom, zillow, zillow interchange format, meta tags, mls, property
Requires at least: 4.1
Tested up to: 4.8.1
Requires PHP: 5
Stable tag: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Sync real estate listings data with Zillow via a custom ZIF (Zillow Interchange Format) feed. The Listings post type requires RE Lister.

== Description ==
Sync real estate listings data with Zillow via a custom ZIF (Zillow Interchange Format) feed. The Listings post type requires RE Lister.

== Installation ==
1. Install and activate the plugin from the Plugins admin page.
2. If RE Lister is not currently installed, you will be prompted to install/activate it.
3. Publish a Listings post type (see RE Lister documentation).
4. Navigate to [YOURSITE]/feed/zillow-listings/ to view your new ZIF feed.
5. Sync your site's listings with Zillow (no affiliation), by registering at their site (https://www.zillow.com/wikipages/About-Broker-Feeds/).

== Frequently Asked Questions ==
= **My site isn't showing a listing feed, why?** =
Go through the checklist:
1. Did you also install/activate RE-Lister? If not, install the latest version of RE-Lister from the WordPress repository.
2. Did you create and publish a new Listing post type? If not, see the documentation page in the Listings menu.
3. Did you add meta values to the Listing before publishing? If not, edit the Listing, and add meta values, then click Update.
4. Did you go to the correct feed url? Note: It will be [YOURSITE]/feed/zillow-listings. Don't forget "/feed/".

If you followed along with the checklist and are still having problems, please contact us for support at the re-feed page in the WordPress repository.

= **I see a lot of blank fields in the feed. Do I need to create a Listing with all fields filled out?** =
No. Per Zillow's documentation, link above, there are only a few feeds that must be included with each Listing entry in the feed XML. Those are:

1. Location -> StreetAddress
2. Location -> UnitNumber
3. Location -> City
4. Location -> State
5. Location -> Zip
6. ListingDetails -> Status
7. ListingDetails -> Price
8. BasicDetails -> PropertyType
9. BasicDetails -> Bedrooms
10. BasicDetails -> Bathrooms
11. Agent -> EmailAddress
12. Office -> BrokerPhone

You should therefore make sure that each listing that is published includes values in at least those required fields.

= **Is there any advantage to creating listings with more than just the required fields filled out?** =
Yes, depending on the quality of your listings. Per Zillow's documentation, higher quality feeds will be more likely to be synced with Zillow's listing service. Only including the minimum number of required fields would be lower "quality." But be sure to only fill out fields that actually apply to the listed property. Specifiying field values indiscriminately is not likely to help.

= **I would like to add another value for certain fields, like another "PictureUrl" and "Caption" within the "Pictures" tag. Can I do that?** =
Currently, no. The ability to add multiple values (both within RE Lister and RE Feed) is coming soon.

== Screenshots ==
1. Create a Listing post type and fill in the meta values.
2. Navigate to [YOURSITE]/feed/zillow-listings and check out your new Zillow Interchange Format feed.

== Changelog ==

= 1.1 =
* Stable, initial release

== Additional Info ==
**This plugin requires RE-Lister (available in the directory) as a dependency.
**Disclaimer:** "Zillow" is a registered trademark of Zillow Group, and any use of the name "Zillow" in this plugin is purely referential (e.g., referring to the "Zillow Interchange Format," "Zillow feed," and the like). The plugin author makes NO WARRANTY that the Zillow Broker Feed service will continue to be available, will continue to work with this plugin, that this plugin will continue to be available or will continue to produce a feed conforming to Zillow's guidelines, that Zillow's guidelines will not change at some future date (rendering this plugin ineffective), or that the plugin will achieve any particular outcome, such as having listings synced with, or published by, Zillow Group. This plugin is marketed "as is," and the author is under no obligation to troubleshoot, update, or edit the code in any way. This plugin is offered as a convenience for constructing a Zillow Interchange Format feed. You may not rely on this plugin to be available or to work for your business. If this plugin does not work for any reason, you are advised to consult with an experienced web developer to construct a substitute feed conforming to the Zillow Interchange Format guidelines.

By downloading and installing this plugin, you agree to the "Disclaimer," above.

*For more information, follow us online at customscripts.tech*