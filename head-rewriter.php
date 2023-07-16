<?php
/**
 * Plugin Name: Head Tags Reorder
 * Plugin URI: https://example.com/plugins/sort-head-tags
 * Description: This plugin sorts the tags inside the <head> section based on page speed optimization priorities.
 * Version: 0.1
 * Author: Mateusz Mazurek
 * Author URI: https://mateuszmazurek.pl/
 */

function sort_head_tags() {
    ob_start();
}
add_action( 'template_redirect', 'sort_head_tags', 1 );

function print_sorted_head_tags() {
    $page_contents = ob_get_clean();
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML($page_contents);
    libxml_clear_errors();

    $head = $dom->getElementsByTagName('head')->item(0);
    $new_head = $dom->createElement('head');

    $tags = [];
    foreach ($head->childNodes as $node) {
        if ($node instanceof DOMElement) {
            $weight = get_weight($node);
            $tags[] = [
                'weight' => $weight,
                'node' => $node,
            ];
        }
    }

    usort($tags, function ($a, $b) {
        return $b['weight'] - $a['weight'];
    });

    foreach ($tags as $tag) {
        $node = $tag['node']->cloneNode(true);
        $new_head->appendChild($node);

        $new_head->appendChild($dom->createTextNode("\n"));
    }

    $head->parentNode->replaceChild($new_head, $head);

    $dom->formatOutput = true;
    echo $dom->saveHTML();
}
add_action( 'shutdown', 'print_sorted_head_tags', 0 );


function get_weight(DOMElement $element) {
    $weights = [
        'meta' => 10,
        'title' => 9,
        'preconnect' => 8,
        'async_script' => 7,
        'import_styles' => 6,
        'sync_script' => 5,
        'sync_styles' => 4,
        'preload' => 3,
        'defer_script' => 2,
        'prefetch_prerender' => 1,
        'other' => 0,
    ];

    $tag_name = detect_tag_type($element);

    return $weights[$tag_name] ?? 0;
}

function detect_tag_type(DOMElement $element) {
    $tag_name = $element->tagName;

    if ($tag_name === 'meta') {
        if ($element->hasAttribute('charset') || 
            $element->hasAttribute('http-equiv') ||
            $element->getAttribute('name') === 'viewport') {
                return 'meta';
        }
    }

    if ($tag_name === 'title') {
        return 'title';
    }

    if ($tag_name === 'link' && $element->getAttribute('rel') === 'preconnect') {
        return 'preconnect';
    }

    if ($tag_name === 'script' && $element->hasAttribute('src') && 
        $element->hasAttribute('async')) {
            return 'async_script';
    }

    // Here you should check whether the CSS resource 
    // contains "@import" rule, but it's not easy in PHP

    if ($tag_name === 'script' && !($element->hasAttribute('src') && 
        ($element->hasAttribute('defer') || $element->hasAttribute('type')))) {
            return 'sync_script';
    }

    if (($tag_name === 'link' && $element->getAttribute('rel') === 'stylesheet') ||
        $tag_name === 'style') {
            return 'sync_styles';
    }

    if ($tag_name === 'link' && $element->getAttribute('rel') === 'preload') {
        return 'preload';
    }

    if ($tag_name === 'script' && $element->hasAttribute('src') && 
        ($element->hasAttribute('defer') || !$element->hasAttribute('async'))) {
            return 'defer_script';
    }

    if ($tag_name === 'link' && 
        in_array($element->getAttribute('rel'), ['prefetch', 'dns-prefetch', 'prerender'])) {
            return 'prefetch_prerender';
    }

    return 'other';
}

?>
