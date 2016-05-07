=== Status Page for Runscope ===
Contributors: yllus
Donate link: https://github.com/yllus/api-status-page
Tags: 
Requires at least: 3.5.2
Tested up to: 4.3
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Status Page for Runscope lets you display a pretty status page containing test results from a Runscope bucket at a URL on your WordPress website.

== Description ==

**Status Page for Runscope** is, at its core, simply a Page template that allows you to input a Runscope API token and Bucket key. With that information, 
the plug-in generates a moderately attractive status page that allows you to view at a glance the state of your bucket's tests (pass or fail).

This is great for organizations that wish to expose a basic level of information about system status to the public (or private, if you choose to make 
the page in WordPress set that way, or password protected) without having to give people access to your Runscope account.

== Installation ==

1. Enable **Status Page for Runscope** within the **Plugins** > **Installed Plugins** interface.

2. Create a new Page by navigating to **Pages** > **Add New**.

3. Give the new Page a quick name (eg. "Status"). Next, change the page template by selecting **Page Attributes** > **Template** and selecting the 
   "Status Page for Runscope" template.

4. In the **Publish** metabox, click the **Publish** button to save and create the new Page.

5. On the edit screen of your new Page, a metabox called **Status Page for Runscope Settings** will appear. Following the instructions provided in the 
   metabox, enter a **Access Token** and **Bucket Key**, and save those settings by clicking the **Update** button.

6. View your Page. If your Runscope settings were entered correctly, the page should pause briefly, and then show the last state of each Test in the 
   bucket, as well as a summary of the bucket at the top of the page. Done!

= Tips & Tricks =

* If you've got object caching turned on in WordPress, the page will automatically cache the last response it got from Runscope for 60 seconds. It'll mean 
  the results of your page will be slightly out of date (by up to 60 seconds), but the results will display much quicker.

* It's totally possible to display multiple status pages for different buckets in Runscope! Just create a new Page per each bucket and make sure to enter
  the right bucket key into each.

== Screenshots ==

1. The settings metabox for Status Page for Runscope allows you to enter a Runscope API **Access Token** and **Bucket Key**, which will direct the Page being 
   edited in which Tests to display on the page.

2. A sample of what the Status Page for Runscope appears like: A list of each Test and their pass/fail state, and a summary box at the top with the overall 
   status of the bucket.

== Changelog ==

= 1.0 =
* Initial release; tested compatibility with WordPress v4.2.4.

== Upgrade Notice ==

= 1.0 =
* Initial release.