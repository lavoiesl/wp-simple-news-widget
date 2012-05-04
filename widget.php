<?php

class Simple_News_Widget extends WP_Widget {
  private static $default_options = array(
    'title'        => false,
    'show_title'   => true,
    'items'        => array(),
    'max_items'    => 5,
    'icl_language' => 'multilingual',
  );
  private static $valid_id = '/^[a-z0-9\-_]+$/i';

  public function __construct() {
    $options = array(
    'classname' => 'simple-news-widget',
    'description' => __('Simple Titles/Links to act as news', 'simple-news-widget'),
    );
    $control = array(
    'id_base' => 'simple-news-widget'
    );
    $this->WP_Widget('simple-news-widget', 'Simple News Widget', $options, $conrol);
  }

  public function widget($args, $instance) {
    if (empty($instance['items'])) return;

    /* Multilingual feature */
    if (defined("ICL_LANGUAGE_CODE")) {
      if (!in_array($instance['icl_language'], array('multilingual', ICL_LANGUAGE_CODE)))
        return;
    }

    extract($args);

    /* User-selected settings. */
    $title = apply_filters('widget_title', $instance['title'] );
    $title = __($title, get_template()); //Localize with current theme

    echo $before_widget;

    if ($title && $instance['show_title'])
      echo $before_title . $title . $after_title;

    if (!empty($before_content)) echo $before_content;

    echo "<ul>\n";
    foreach ($instance['items'] as $i => $item) {
      echo "<li>\n";
      if (empty($item['url'])) {
        echo "  <span>{$item['text']}</span>\n";
      } else {
        $url = self::replace_var_by_url($item['url']);
        echo "  <a href=\"$url\">{$item['text']}</a>\n";
      }
      echo "</li>\n";
    }
    echo "</ul>\n";

    if (!empty($after_content)) echo $after_content;

    echo $after_widget;
  }

  public function update($new_instance, $old_instance) {
    $new_instance['title'] = filter_var($new_instance['title'], FILTER_SANITIZE_STRIPPED);
    $new_instance['max_items'] = filter_var($new_instance['max_items'], FILTER_SANITIZE_NUMBER_INT);
    $new_instance['show_title'] = filter_var($new_instance['show_title'], FILTER_VALIDATE_BOOLEAN);

    if (!$new_instance['max_items']) $new_instance['max_items'] = self::$default_options['max_items'];

    foreach ($new_instance['items'] as $i => $item) {
      $text = filter_var($item['text'], FILTER_SANITIZE_STRIPPED);
      $url = filter_var($item['url'], FILTER_SANITIZE_URL);
      $url = self::replace_url_by_var($url);

      if (empty($text)) {
        unset($new_instance['items'][$i]);
      } else {
        $new_instance['items'][$i] = array(
          'text' => $text,
          'url'  => $url,
        );
      }
    }

    // Re-index
    $new_instance['items'] = array_values($new_instance['items']);

    return $new_instance;
  }

  private static function replace_url_by_var($url) {
    $base_url = get_site_url();
    return preg_replace("!^($base_url|/)!", '@BASE_URL@', $url);
  }

  private static function replace_var_by_url($url) {
    $base_url = '/' . parse_url(get_site_url(), PHP_URL_PATH);
    return str_replace('@BASE_URL@', $base_url, $url);
  }

  /**
   * Widget form in backend
   */
  public function form($instance) {
    $instance = wp_parse_args((array) $instance, self::$default_options);

    $show_title = checked($instance['show_title'], true, false);

    $inputs = array();
    foreach ($instance as $key => $value) {
      $inputs[$key] = array(
        'id'    => $this->get_field_id($key),   // This is to ensure multi instance works,
        'name'  => $this->get_field_name($key), // See http://justintadlock.com/archives/2009/05/26/the-complete-guide-to-creating-widgets-in-wordpress-28
        'title' => __(ucwords(str_replace('_', ' ', $key)), 'simple-news-widget'),
        'value' => attribute_escape($value), // Be sure you format your options to be valid HTML attributes.
      );
    }

    // Notice that we don't need a complete form as it is embedded into the existing form.
    echo <<<HTML

    <p>
    <label for="{$inputs['title']['id']}">
        {$inputs['title']['title']}:
        <input class="widefat" id="{$inputs['title']['id']}" name="{$inputs['title']['name']}" type="text" value="{$inputs['title']['value']}">
      </label>
    </p>
    <p>
      <label for="{$inputs['max_items']['id']}">
        {$inputs['max_items']['title']}:
        <input style="width: 50px" id="{$inputs['max_items']['id']}" name="{$inputs['max_items']['name']}" type="number" min="1" max="20" value="{$inputs['max_items']['value']}">
      </label>
      <label for="{$inputs['show_title']['id']}">
        {$inputs['show_title']['title']}:
        <input id="{$inputs['show_title']['id']}"  name="{$inputs['show_title']['name']}" value="1" type="checkbox" $show_title>
      </label>
    </p>
HTML;

    $text_title = __('Text', 'simple-news-widget');
    $url_title  = __('URL', 'simple-news-widget');
    for ($i=0; $i < $instance['max_items']; $i++) {
      $text_value = attribute_escape($instance['items'][$i]['text']);
      $url_value = attribute_escape(self::replace_var_by_url($instance['items'][$i]['url']));

      $name  = $inputs['items']['name'] . "[$i]";

      echo <<<HTML

    <p>
      <label>
        $text_title
        <input class="widefat" name="{$name}[text]" type="text" value="{$text_value}">
      </label>
      <label>
        $url_title
        <input class="widefat" name="{$name}[url]" type="text" value="{$url_value}">
      </label>
    </p>
    <hr>
HTML;

    }

    /* Multilingual feature */
    if (function_exists('icl_widget_text_language_selectbox')) {
      icl_widget_text_language_selectbox($instance['icl_language'], $this->get_field_name('icl_language'));
    }
  }
}
