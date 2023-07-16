# Head Tags Reorder WordPress Plugin

The Sort Head Tags plugin is a highly specialized tool designed for performance optimization of your WordPress site. It strategically rearranges tags within the `<head>` section based on predefined priorities, enhancing not only the actual, but also the perceived performance of your page. However, this tool should be used with caution, as the incorrect ordering of tags and scripts may result in breaking your site functionality.

Inspired by [Harry Roberts](https://twitter.com/csswizardry)' work on [ct.css](https://csswizardry.com/ct/) and [Vitaly Friedman](https://twitter.com/smashingmag)'s [Nordic.js 2022 presentation](https://youtu.be/uqLl-Yew2o8?t=2873) and [capo.js script](https://github.com/rviscomi/capo.js).

Warning: **This plugin modifies the layout of your page's `<head>` section considerably. The order of tags and scripts is often critical to a site's functionality, as they may need to be loaded in a specific sequence. Therefore, thorough testing on a staging version of your site before deploying it on a live environment is highly recommended.**

##  Performance Implications
The arrangement of elements within the `<head>` section has substantial impact on your page's performance. While actual performance is crucial, perceived performance also plays a significant role in user experience. This is because browsers parse HTML documents from top to bottom - having key resources and styles defined earlier can make your site appear faster to the user, even if the total load time remains the same.

## How it works?
1. Starting Output Buffering: The plugin triggers PHP output buffering at the 'template_redirect' action hook with priority 1. This process ensures that PHP begins storing the output rather than immediately dispatching it to the client's browser.
3. Parsing and Prioritizing Tags: At the 'shutdown' action hook (with priority 0), the plugin fetches the buffered output and interprets it as a DOMDocument object. All the child nodes of the `<head>` element are accumulated, and a 'weight' is assigned to each tag based on its type via the get_weight function.
4. Weight Assignment: The get_weight function allocates weights to tags correlating to their type. This function makes use of the detect_tag_type method, which assesses each tag's nature (including, but not limited to meta, title, link with a 'preconnect' relation, asynchronous script, synchronous script, synchronous style or link with a 'stylesheet' relation, link with a 'preload' relation, deferred script, and link with 'prefetch', 'dns-prefetch', or 'prerender' relations).
5. Rebuilding and Replacing: After tags have been sorted according to their weights, a fresh `<head>` element is created, and the ordered tags are appended to it. The existing `<head>` element is replaced with the new one, and the updated HTML is dispatched to the browser.

## Limitations
One of the current limitations of the plugin is its inability to check whether a CSS resource contains an "@import" rule. Since the "@import" rule can import a style sheet into another style sheet, it's crucial to place it at the start of your CSS document to prevent any overwriting of styles. It's recommended to manually manage CSS files that use "@import".
