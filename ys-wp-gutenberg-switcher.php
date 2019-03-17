<?php
/**
 * Plugin Name:     YS WP Gutenberg Switcher
 * Plugin URI:      https://github.com/yosiakatsuki/ys-wp-gutenberg-switcher
 * Description:     既存サイトを段階的にGutenberg対応させるためのプラグイン
 * Author:          yosiakatsuki
 * Author URI:      https://yosiakatsuki.net/blog
 * Text Domain:     ys-wp-gutenberg-switcher
 * Domain Path:     /languages
 * Version:         1.0.1
 *
 * @author          yosiakatuki
 * @package         Ys_Wp_Gutenberg_Switcher
 * @license         GPL-2.0+
 */

/**
 * Autoload
 */
require_once( __DIR__ . '/vendor/autoload.php' );

/**
 * 定数
 */
define( 'YSWPGS_PATH', plugin_dir_path( __FILE__ ) );
define( 'YSWPGS_USE_BLOCK_META_KEY', '_yswpgs_use_block' );

/**
 * Gutenbergを使用するか判断
 *
 * @param bool    $use_block_editor Gutenbergを使うか.
 * @param WP_Post $post             投稿オブジェクト.
 *
 * @return bool
 */
function yswpgs_use_block_editor_for_post( $use_block_editor, $post ) {

	$post_id = $post->ID;
	if ( '1' == get_post_meta( $post_id, YSWPGS_USE_BLOCK_META_KEY, true ) ) {
		$use_block_editor = true;
	}
	/**
	 * 新規作成する投稿はGutenbergにする
	 */
	if ( '' == $post->post_name ) {
		$use_block_editor = true;
	}

	return $use_block_editor;
}

add_filter( 'use_block_editor_for_post', 'yswpgs_use_block_editor_for_post', 10, 2 );

/**
 * 設定用のメタボックス作成
 */
function yswpgs_add_meta_box() {
	add_meta_box(
		'ys-wp-gutenberg-switcher',
		'Gutenbergの使用設定',
		'yswpgs_meta_box_html',
		'post',
		'advanced'
	);
	add_meta_box(
		'ys-wp-gutenberg-switcher',
		'Gutenbergの使用設定',
		'yswpgs_meta_box_html',
		'page',
		'advanced'
	);
}

add_action( 'admin_menu', 'yswpgs_add_meta_box' );

/**
 * メタボックスの中身
 */
function yswpgs_meta_box_html() {
	global $post;
	$checked = get_post_meta( get_the_ID(), YSWPGS_USE_BLOCK_META_KEY, true );
	/**
	 * 新規作成する投稿はGutenbergにする
	 */
	if ( '' == $post->post_name ) {
		$checked = '1';
	}
	?>
	<label for="yswpgs_use_block">
		<input type="checkbox" id="yswpgs_use_block" name="yswpgs_use_block" value="1" <?php checked( $checked, '1', true ); ?> />Gutenbergを使用する
	</label>
	<?php
}

/**
 * 設定更新
 *
 * @param int $post_id Post ID.
 */
function yswpgs_save_post( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}
	/**
	 * 設定更新
	 */
	if ( isset( $_POST['yswpgs_use_block'] ) ) {
		update_post_meta( $post_id, YSWPGS_USE_BLOCK_META_KEY, $_POST['yswpgs_use_block'] );
	} else {
		delete_post_meta( $post_id, YSWPGS_USE_BLOCK_META_KEY );
	}
}

add_action( 'save_post', 'yswpgs_save_post' );


/**
 * 自動アップデート
 */
function yswpgs_activate_autoupdate() {
	new Inc2734\WP_GitHub_Plugin_Updater\Bootstrap(
		plugin_basename( __FILE__ ),
		'yosiakatsuki',
		'ys-wp-gutenberg-switcher'
	);
}

add_action( 'init', 'yswpgs_activate_autoupdate' );
