<?php
/**
 * Publish box template
 * @since 0.1.0
 */

/**
 * Filterable label text for checkbox.
 * @since 0.1.0
 */
$label_text = $label_text = sprintf(
	_x( 'Publish to <a href="%s">Micro.blog</a>', 'checkbox label', 'microblog' ),
	esc_url( 'https://micro.blog' ) // @todo user account URL
);

if ( 'publish' === $post->post_status && '1' === $value ) {
	$label_text = sprintf(
		_x( '<strong>Published on <a href="%s">Micro.blog</a></strong>', 'checkbox label', 'microblog' ),
		esc_url( 'https://micro.blog' ) // @todo micro.blog permalink
	);
}

$label_text = apply_filters(
 	'microblog__pub_section_label',
	$label_text,
	$post,
	$value
);
?>
<div class="misc-pub-section microblog">
	<label for="microblog__post-to-feed">
		<input type="checkbox" id="microblog__post-to-feed" name="microblog__post-to-feed" class="microblog__post-to-feed" <?php checked( $value ); disabled( $maybe_enabled, false ); ?> value="1" />
		<span class="microblog__label-text">
			<?php echo $label_text; ?>
		</span>
	</label>
</div>
