<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <?php
    // No <title> here on purpose. functions.php already declares
    // add_theme_support('title-tag'), so WordPress prints one from wp_head()
    // below and Rank Math filters it. Printing a second one here put two
    // <title> tags on every page of the site, and the hardcoded one came first
    // — so search engines read that one: wp_title() followed by a bare
    // bloginfo('name'), which rendered as
    // "IPTV Service Provider: 40,000 Channels, Instant Setup        quebeciptv.co"
    // with no separator and 72 characters, while Rank Math's correct 52-character
    // title sat below it being ignored.
    ?>
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>