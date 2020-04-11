<?php

/**
 * Plugin Name: Susantokun - Recent Posts Tailwind CSS
 * Plugin URI: https://github.com/susantokun/wp-plugin-recent-posts-tailwindcss.git
 * Description: Recent Posts Custom with Tailwind CSS.
 * Version: 1.0
 * Author: Susantokun
 * Author URI: https://www.susantokun.com/
 */

class WP_Widget_Recent_Posts_Custom extends WP_Widget
{
    public function __construct()
    {
        $widget_ops = array(
            'classname'                   => 'widget_recent_entries',
            'description'                 => __('Your site&#8217;s most recent Posts Custom.'),
            'customize_selective_refresh' => true,
        );
        parent::__construct('recent-posts', __('Susantokun - Recent Posts Custom'), $widget_ops);
        $this->alt_option_name = 'widget_recent_entries';

        add_action('save_post', array($this, 'flush_widget_cache'));
        add_action('deleted_post', array($this, 'flush_widget_cache'));
        add_action('switch_theme', array($this, 'flush_widget_cache'));
    }

    public function widget($args, $instance)
    {
        if (! isset($args['widget_id'])) {
            $args['widget_id'] = $this->id;
        }

        $title = (! empty($instance['title'])) ? $instance['title'] : __('Recent Posts');

        /** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
        $title = apply_filters('widget_title', $title, $instance, $this->id_base);

        $number = (! empty($instance['number'])) ? absint($instance['number']) : 5;
        if (! $number) {
            $number = 5;
        }
        $show_date = isset($instance['show_date']) ? $instance['show_date'] : false;

        $r = new WP_Query(
            apply_filters(
                'widget_posts_args',
                array(
                    'posts_per_page'      => $number,
                    'no_found_rows'       => true,
                    'post_status'         => 'publish',
                    'ignore_sticky_posts' => true,
                ),
                $instance
            )
        );

        if (! $r->have_posts()) {
            return;
        } ?>
		<?php echo $args['before_widget']; ?>
		<?php
        if ($title) {
            echo $args['before_title'] . $title . $args['after_title'];
        } ?>
			<?php foreach ($r->posts as $recent_post) : ?>
				<?php
                $post_title   = get_the_title($recent_post->ID);
        $title        = (! empty($post_title)) ? $post_title : __('(no title)');
        $aria_current = '';

        if (get_queried_object_id() === $recent_post->ID) {
            $aria_current = ' aria-current="page"';
        } ?>
				<div class="w-full text-sm truncate">
					<a class="font-medium hover:text-blue-light" title="<?php echo $title; ?>" href="<?php the_permalink($recent_post->ID); ?>"<?php echo $aria_current; ?>><?php echo $title; ?></a>
					<?php if ($show_date) : ?>
						<div class="w-full text-xs flex items-center">
              <svg class="h-3 w-auto inline-block pr-1" aria-hidden="true" focusable="false" data-prefix="far" data-icon="calendar" class="svg-inline--fa fa-calendar fa-w-14" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M400 64h-48V12c0-6.6-5.4-12-12-12h-40c-6.6 0-12 5.4-12 12v52H160V12c0-6.6-5.4-12-12-12h-40c-6.6 0-12 5.4-12 12v52H48C21.5 64 0 85.5 0 112v352c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V112c0-26.5-21.5-48-48-48zm-6 400H54c-3.3 0-6-2.7-6-6V160h352v298c0 3.3-2.7 6-6 6z"></path></svg>
              <?php echo get_the_date('', $recent_post->ID); ?>
            </div>
					<?php endif; ?>
				</div>
        <div class="h-px mx-auto bg-body w-full opacity-75 my-2"></div>
			<?php endforeach; ?>
		<?php
        echo $args['after_widget'];
    }

    public function update($new_instance, $old_instance)
    {
        $instance              = $old_instance;
        $instance['title']     = sanitize_text_field($new_instance['title']);
        $instance['number']    = (int) $new_instance['number'];
        $instance['show_date'] = isset($new_instance['show_date']) ? (bool) $new_instance['show_date'] : false;
        return $instance;
    }

    public function form($instance)
    {
        $title     = isset($instance['title']) ? esc_attr($instance['title']) : '';
        $number    = isset($instance['number']) ? absint($instance['number']) : 5;
        $show_date = isset($instance['show_date']) ? (bool) $instance['show_date'] : false; ?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of posts to show:'); ?></label>
		<input class="tiny-text" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="number" step="1" min="1" value="<?php echo $number; ?>" size="3" /></p>

		<p><input class="checkbox" type="checkbox"<?php checked($show_date); ?> id="<?php echo $this->get_field_id('show_date'); ?>" name="<?php echo $this->get_field_name('show_date'); ?>" />
		<label for="<?php echo $this->get_field_id('show_date'); ?>"><?php _e('Display post date?'); ?></label></p>
		<?php
    }
}

function register_WP_Widget_Recent_Posts_Custom()
{
    register_widget('WP_Widget_Recent_Posts_Custom');
}
add_action('widgets_init', 'register_WP_Widget_Recent_Posts_Custom');

// function callback_for_style()
// {
//     wp_register_style('recent-posts-tailwindcss', plugins_url('recent-posts-tailwindcss.css', __FILE__));
//     wp_enqueue_style('recent-posts-tailwindcss');
// }
// add_action('wp_enqueue_scripts', 'callback_for_style');
