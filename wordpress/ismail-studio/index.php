<?php
/* SPDX-License-Identifier: MIT
 * Copyright (c) 2026 Ismail Habib
 */
 get_header(); if(have_posts()): while(have_posts()): the_post(); ?><section class="pagehead"><div class="eyebrow">Ismail Habib</div><h1><?php the_title(); ?></h1></section><section class="section"><?php the_content(); ?></section><?php endwhile; else: ?><section class="pagehead"><h1>No content found.</h1></section><?php endif; get_footer(); ?>