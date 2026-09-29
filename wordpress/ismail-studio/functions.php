<?php
/* SPDX-License-Identifier: MIT
 * Copyright (c) 2026 Ismail Habib
 */

add_action('after_setup_theme', function(){ add_theme_support('title-tag'); add_theme_support('post-thumbnails'); });
add_action('wp_enqueue_scripts', function(){ wp_enqueue_style('ismail-studio', get_stylesheet_uri(), array(), '1.0.0'); });
