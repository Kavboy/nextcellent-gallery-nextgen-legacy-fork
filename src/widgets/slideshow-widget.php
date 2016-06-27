<?php

namespace NextCellent\Widgets;

use NextCellent\Models\Image_Collection;
use NextCellent\Options\Options;

/**
 * The NextCellent Slideshow widget.
 */
class Slideshow_Widget extends \WP_Widget {

	const NCG_WIDGET_SLIDESHOW = 'slideshow';

	/**
	 * Register the widget.
	 */
	public function __construct() {
		parent::__construct(self::NCG_WIDGET_SLIDESHOW, __('NextCellent Slideshow', 'nggallery'), [
			'classname'   => 'widget_slideshow',
			'description' => __('Show a NextCellent Gallery Slideshow', 'nggallery')
		]);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
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

		//Get the dimensions
		if ($instance['autodim']) {
			$instance['width']  = null;
			$instance['height'] = null;
		}

		//The galleries
		if($instance['list'] === '') {
			$list = null;
		} else {
			$list = explode(',', $instance['list']);
		}

		if($instance['type'] === 'one') {
			if($list === null) {
				$out = __('[Please provide a gallery!]', 'nggallery');
			} else {
				$out = \NextCellent\Rendering\render_slideshow_shortcode($list[0], $instance['width'], $instance['height'], null, false);
			}
		} else{
			if ($instance['type'] === 'random') {
				$images = Image_Collection::random($instance['items'], $list);
			} else {
				$images = Image_Collection::recent($instance['items'], $list);
			}

			$auto = $instance['width'] === null && $instance['height'] === null;

			$out = \NextCellent\Rendering\render_slideshow($images, false, [
				'width'     => $instance['width'],
				'height'    => $instance['height'],
				'auto_dim'  => $auto,
				'nav'       => false,
			]);
		}

		//Do the actual output.
		echo $args['before_widget'];
		if ($title) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		echo '<div class="ngg_slideshow widget">' . $out . '</div>';
		echo $args['after_widget'];
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
		} elseif($new_instance['type'] === 'recent') {
			$instance['type'] = 'recent';
		} else {
			$instance['type'] = 'one';
		}

		$temp_array = explode(',', $new_instance['list']);
		array_walk($temp_array, 'intval');
		$instance['list'] = implode(',', $temp_array);

		$instance['height']  = (int) $new_instance['height'];
		$instance['width']   = (int) $new_instance['width'];
		$instance['autodim'] = (bool) $new_instance['autodim'];

		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 *
	 * @return string
	 */
	public function form($instance) {

		global $ngg;
		$options = $ngg->options;

		//Defaults
		$instance = wp_parse_args($instance, [
			'title'     => __('Slideshow', 'nggallery'),
			'width'     => $options[ Options::SLIDE_WIDTH ],
			'height'    => $options[ Options::SLIDE_HEIGHT ],
			'nav'       => false,
			'autoplay'  => true,
			'autodim'   => $options[ Options::SLIDE_FIT_SIZE ],
			'items'     => $options[Options::SLIDE_NR_OF_IMAGES],
			'list'      => '',
			'type'  => 'random',
		]);

		?>
		<p>
			<label for="<?= $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
			<input class="widefat"
			       id="<?= $this->get_field_id('title'); ?>"
			       name="<?= $this->get_field_name('title'); ?>" type="text"
			       value="<?php esc_attr_e($instance['title']); ?>"
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
			<br>
			<span class="description"><?php _e('When using the "one gallery" mode, the number of images is ignored.', 'nggallery'); ?></span>
		</p>
		<p>
			<input id="<?= $this->get_field_id('type') ?>_random"
			       name="<?= $this->get_field_name('type') ?>"
			       type="radio"
			       value="random" <?php checked('random', $instance['type']) ?>
			>
			<label for="<?= $this->get_field_id('type') ?>_random">
				<?php _e('random images', 'nggallery') ?>
			</label>
			<br>
			<input id="<?= $this->get_field_id('type') ?>_recent"
			       name="<?= $this->get_field_name('type') ?>"
			       type="radio"
			       value="recent" <?php checked('recent', $instance['type']); ?>
			>
			<label for="<?= $this->get_field_id('type') ?>_recent">
				<?php _e('recent images', 'nggallery') ?>
			</label>
			<br>
			<input id="<?= $this->get_field_id('type') ?>_one"
			       name="<?= $this->get_field_name('type') ?>"
			       type="radio"
			       value="one" <?php checked('one', $instance['type']); ?>
			>
			<label for="<?= $this->get_field_id('type') ?>_one">
				<?php _e('one gallery', 'nggallery') ?>
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
				<?php _e('Gallery IDs, separated by commas. Leave empty for all galleries. When the option "one gallery" is selected, only the first gallery will be used. If you leave it empty, it will not work.', 'nggallery'); ?>
			</label>
		</p>
		<p>
			<input id="<?= $this->get_field_id('autodim'); ?>"
			       name="<?= $this->get_field_name('autodim'); ?>"
			       type="checkbox"
			       value="true" <?php checked(true, $instance['autodim']); ?>
			>
			<label for="<?= $this->get_field_id('autodim'); ?>">
				<?php _e("Let the slideshow fit in the available space.", 'nggallery'); ?>
			</label>
			<br>
			<span class="description">
				<?php _e("The given width and height are ignored when this is selected.", 'nggallery'); ?>
			</span>
		</p>
		<table>
			<tr>
				<td>
					<label for="<?= $this->get_field_id('width'); ?>"><?php _e('Width:', 'nggallery'); ?></label>
				</td>
				<td>
					<input id="<?= $this->get_field_id('width'); ?>"
					       name="<?= $this->get_field_name('width'); ?>"
					       type="number"
					       min="0"
					       style="padding: 3px; width: 60px;"
					       value="<?= $instance['width']; ?>"/> px
				</td>
			</tr>
			<tr>
				<td>
					<label for="<?= $this->get_field_id('height'); ?>"><?php _e('Height:', 'nggallery'); ?></label>
				</td>
				<td>
					<input id="<?= $this->get_field_id('height'); ?>"
					       name="<?= $this->get_field_name('height'); ?>"
					       type="number"
					       min="0"
					       style="padding: 3px; width: 60px;"
					       value="<?= $instance['height'] ?>"/> px
				</td>
			</tr>
		</table>
		<?php
	}
}