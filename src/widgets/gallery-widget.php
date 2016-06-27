<?php

namespace NextCellent\Widgets;

use NextCellent\Models\Image_Collection;

/**
 * The NextCellent Gallery Widget
 */
class Gallery_Widget extends \WP_Widget {

	const NCG_WIDGET_GALLERY = 'ngg-images';

	/**
	 * Register the widget.
	 */
	public function __construct() {
		parent::__construct(self::NCG_WIDGET_GALLERY, __('NextCellent Gallery Widget', 'nggallery'), [
			'classname'   => 'ngg_images',
			'description' => __('Add recent or random images from the galleries', 'nggallery')
		]);
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update($new_instance, $old_instance) {
		$instance = $old_instance;

		$instance['title'] = sanitize_text_field($new_instance['title']);
		$instance['items'] = (int) $new_instance['items'];

		//Validate type
		if ($new_instance['type'] === 'random') {
			$instance['type'] = 'random';
		} else {
			$instance['type'] = 'recent';
		}

		$temp_array = explode(',', $new_instance['list']);
		array_walk($temp_array, 'intval');
		$instance['list'] = implode(',', $temp_array);

		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 *
	 * @return string Default return is 'noform'.
	 */
	public function form($instance) {

		//Defaults
		$instance = wp_parse_args($instance, [
			'title' => __('Gallery', 'nggallery'),
			'items' => 4,
			'type'  => 'random',
			'list'  => ''
		]);

		?>

		<p>
			<label for="<?= $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
			<input id="<?= $this->get_field_id('title'); ?>"
			       name="<?= $this->get_field_name('title'); ?>"
			       type="text"
			       class="widefat"
			       value="<?= esc_attr($instance['title']); ?>"
			>
		</p>
		<p>
			<label for="<?= $this->get_field_id('items') ?>"><?php _e('Show:', 'nggallery') ?></label>
			<br>
			<input style="width: 60px;" id="<?= $this->get_field_id('items') ?>"
			       name="<?= $this->get_field_name('items') ?>"
			       type="number"
			       min="0"
			       value="<?= $instance['items'] ?>"
			>
			<?php _e('images', 'nggallery') ?>
		</p>
		<p>
			<input id="<?= $this->get_field_id('type') ?>_random"
			       name="<?= $this->get_field_name('type') ?>"
			       type="radio"
			       value="random" <?php checked("random", $instance['type']) ?>
			>
			<label for="<?= $this->get_field_id('type') ?>_random">
				<?php _e('random', 'nggallery') ?>
			</label>
			<br>
			<input id="<?= $this->get_field_id('type') ?>_recent"
			       name="<?= $this->get_field_name('type') ?>"
			       type="radio"
			       value="recent" <?php checked("recent", $instance['type']); ?>
			>
			<label for="<?= $this->get_field_id('type') ?>_recent">
				<?php _e('recent added ', 'nggallery') ?>
			</label>
		</p>
		<p>
			<label for="<?= $this->get_field_id('list') ?>"><?php _e('Gallery ID:', 'nggallery'); ?></label>
			<input id="<?= $this->get_field_id('list') ?>"
			       name="<?= $this->get_field_name('list'); ?>"
			       type="text"
			       class="widefat"
			       value="<?= $instance['list']; ?>"
			>
			<label class="description" for="<?= $this->get_field_id('list'); ?>">
				<?php _e('Gallery IDs, separated by commas. Leave empty for all galleries.', 'nggallery'); ?>
			</label>
		</p>
		<?php
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see  WP_Widget::widget()
	 *
	 * @todo Needs to be better, without the mess to call to the database, but this requires a better database API.
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from the database.
	 */
	public function widget($args, $instance) {

		if (empty($instance['title'])) {
			$instance['title'] = __('Slideshow', 'nggallery');
		}

		//Title for the widget.
		$title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);

		//The galleries
		if($instance['list'] === '') {
			$list = null;
		} else {
			$list = explode(',', $instance['list']);
		}

		if($instance['type'] === 'random') {
			$out = \NextCellent\Rendering\render_random_shortcode($instance['items'], 'gallery', $list, false);
		} else {
			$out = \NextCellent\Rendering\render_recent_shortcode($instance['items'], 'gallery', $list, false);
		}


		//Do the actual output.
		echo $args['before_widget'];
		if ($title) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		echo '<div class="ncg_widget widget">' . $out . '</div>';
		echo $args['after_widget'];
	}
}