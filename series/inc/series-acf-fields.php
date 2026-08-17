<?php
/**
 * ACF Field Group: Series Details
 * Registers custom fields for the Series custom post type.
 *
 * DISABLED: Field group is managed via ACF Pro UI (already created by user).
 * The acf-json/ file in this folder is the source of truth.
 * Re-enable the PHP registration below by removing the early return if needed.
 */

if (!defined('ABSPATH'))
    exit;

// Field group managed by ACF UI — PHP registration disabled to avoid conflicts
return;

add_action('acf/init', 'iptv_register_series_acf_fields');

function iptv_register_series_acf_fields()
{
    if (!function_exists('acf_add_local_field_group'))
        return;

    acf_add_local_field_group([
        'key' => 'group_series_details',
        'title' => 'Series Details',
        'fields' => [

            // ── Series Genre ──────────────────────────────────────────────────
            [
                'key' => 'field_series_genre',
                'label' => 'Series Genre',
                'name' => 'series_genre',
                'type' => 'select',
                'instructions' => 'What genre is this series?',
                'required' => 0,
                'choices' => [
                    'Drama' => '🎭 Drama',
                    'Comedy' => '😂 Comedy',
                    'Thriller' => '😱 Thriller',
                    'Sci-Fi' => '🚀 Sci-Fi',
                    'Documentary' => '🎞️ Documentary',
                    'Action' => '💥 Action',
                    'Horror' => '👻 Horror',
                    'Romance' => '❤️ Romance',
                    'Fantasy' => '🧙 Fantasy',
                    'Animation' => '🎨 Animation',
                ],
                'default_value' => 'Drama',
                'allow_null' => 0,
                'ui' => 1,
            ],

            // ── Series Tagline ────────────────────────────────────────────────
            [
                'key' => 'field_series_tagline',
                'label' => 'Series Tagline',
                'name' => 'series_tagline',
                'type' => 'text',
                'instructions' => 'Short marketing line shown under the title in the hero.',
                'required' => 0,
                'placeholder' => 'e.g. The show that broke the internet',
                'maxlength' => 100,
            ],

            // ── Short Description ─────────────────────────────────────────────
            [
                'key' => 'field_series_short_description',
                'label' => 'Short Description',
                'name' => 'series_short_description',
                'type' => 'textarea',
                'instructions' => 'Short description shown below the headline in the hero section. Keep it under 200 characters.',
                'required' => 0,
                'placeholder' => 'e.g. Stream Breaking Bad with Quebec IPTV. All 5 seasons available.',
                'maxlength' => 250,
                'rows' => 3,
                'new_lines' => 'br',
            ],

            // ── Number of Seasons ─────────────────────────────────────────────
            [
                'key' => 'field_series_seasons',
                'label' => 'Number of Seasons',
                'name' => 'series_seasons',
                'type' => 'number',
                'instructions' => 'Total number of seasons (used in schema markup).',
                'required' => 0,
                'placeholder' => 'e.g. 5',
                'min' => 1,
                'max' => 100,
            ],

            // ── Number of Episodes ────────────────────────────────────────────
            [
                'key' => 'field_series_episodes',
                'label' => 'Number of Episodes',
                'name' => 'series_episodes',
                'type' => 'number',
                'instructions' => 'Total number of episodes (used in schema markup).',
                'required' => 0,
                'placeholder' => 'e.g. 62',
                'min' => 1,
            ],

            // ── Official Website ──────────────────────────────────────────────
            [
                'key' => 'field_series_website',
                'label' => 'Official Website',
                'name' => 'series_website',
                'type' => 'url',
                'instructions' => 'Link to the official series website (used in schema markup).',
                'required' => 0,
                'placeholder' => 'https://imdb.com/title/...',
            ],

        ],

        // Location: show only on Series CPT
        'location' => [
            [
                [
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'series',
                ],
            ],
        ],

        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'active' => true,
    ]);
}
