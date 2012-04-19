<?php

/**
 * Plugin Name: Simple News Widget
 * Plugin URI: https://github.com/lavoiesl/wp-simple-youtube-widget
 * Description: Adds a sidebar widget to show simple Titles/Links to act as news.
 * Author: Sébastien Lavoie
 * Version: 1.0
 * Author URI: http://sebastien.lavoie.sl/
 */

require_once 'widget.php';

add_action('widgets_init', 'simple_news_widget_load_widgets');

/* Function that registers our widget. */
function simple_news_widget_load_widgets() {
  register_widget('Simple_News_Widget');
}
