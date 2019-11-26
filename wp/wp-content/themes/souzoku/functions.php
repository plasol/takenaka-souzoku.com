<?php

//カスタムポスト

//相続ブログ
add_action('init','create_top_post_type',0);
function create_top_post_type() {
  register_post_type(
    'top',
    array(
      'label' => '相続ブログ',
      'public' => true,
      'hierarchical' => false,
      'menu_position' => 5,
      'rewrite' => false,
      'query_var' => false,
      'supports' => array(
        'title',
        'editor',
				'thumbnail',
				'custom-fields'
      ),
    )
  );
}

//ニュース
add_action('init','create_news_post_type',0);
function create_news_post_type() {
  register_post_type(
    'news',
    array(
      'label' => 'ニュース',
      'public' => true,
      'hierarchical' => false,
      'menu_position' => 5,
      'rewrite' => false,
      'query_var' => false,
      'supports' => array(
        'title',
        'editor',
				'thumbnail',
				'custom-fields'
      ),
    )
  );
}


////////////////////////////////////////////////////////////////////


//相続ブログ
add_action('init','create_top_ct',0);
function create_top_ct() {
  register_taxonomy(
    'toptype',
    'top',
    array(
      'hierarchical' => true,
      'label' => 'トップカテゴリ',
      'singular_name' => 'トップカテゴリ',
      'query_var' => true,
      'rewrite' => true
    )
  );
}

//ニュース
add_action('init','create_news_ct',0);
function create_news_ct() {
  register_taxonomy(
    'newstype',
    'news',
    array(
      'hierarchical' => true,
      'label' => 'お知らせカテゴリ',
      'singular_name' => 'お知らせカテゴリ',
      'query_var' => true,
      'rewrite' => true
    )
  );
}

//カテゴリーのaタグに記事数を含める
add_filter( 'wp_list_categories', 'my_list_categories', 10, 2 );
function my_list_categories( $output, $args ) {
  $output = preg_replace('/<\/a>\s*\((\d+)\)/',' ($1)</a>',$output);
  return $output;
}
?>
