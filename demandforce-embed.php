<?php

/*
Plugin Name: Demandforce Embed
Description: Embed Demandforce scripts with the shortcodes <code>[df-scheduler bid="123456789"]</code> and <code>[df-reviews bid="123456789"]</code>. Also includes a reviews widget.
Version: 1.0
Author: Brainstorm Media
Author URI: http://brainstormmedia.com
*/

/**
 * Copyright (c) 2013 Brainstorm Media. All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * **********************************************************************
 */

add_action( 'init', create_function('', 'new Storm_Demandforce_Embeds();') );

class Storm_Demandforce_Embeds {

	function __construct() {
		add_shortcode( 'df-scheduler', array( $this, 'schedule') );
		add_shortcode( 'df-schedule', array( $this, 'schedule') );
		add_shortcode( 'dfscheduler', array( $this, 'schedule') );
		add_shortcode( 'dfschedule', array( $this, 'schedule') );

		add_shortcode( 'df-reviews', array( $this, 'review') );
		add_shortcode( 'dfreviews', array( $this, 'review') );
	}

	/**
	 * Easy Scheduler Widget
	 * Appointment Page: http://fameautomotive.com/appointment/
	 */
	function schedule( $atts ) {
		$atts = extract( shortcode_atts( array(
			'bid' => '', // Business ID in DemandforceD3
			'source' => parse_url( get_site_url(), PHP_URL_HOST ), // Optional
			'returnpage' => '', // Optional
		), $atts ) );

		if ( empty( $bid ) ) {
			return '<div style="color:red;">Please set Demandforce Business ID in shortcode. For example: <pre>[df-schedule bid="123456789"]</pre></div>';
		}

		// Assume that if we set a return page, we want POST data
		$postdata = ( empty( $returnpage ) ) ? 'false' : 'true';

		ob_start();
		?>
			<link type="text/css" rel="stylesheet" href="//www.demandforce.com/widget/css/widget.css" />
			<style>
				.d3cp_df_seal_widget { display:none !important; }
				#d3cp_form_appointment { min-height: 1100px; } /* Pitcrew specific */
			</style>

			<script type="text/javascript">
				d3cp_bid = '<?php echo $bid ?>';
				<?php if( !empty($source) ): ?>d3cp_appt_source = '<?php echo $source ?>';<?php endif; ?>
				<?php if( !empty($returnpage) ): ?>d3cp_appt_returnpage = '<?php echo $returnpage ?>';<?php endif; ?>
				<?php if( !empty($postdata) ): ?>d3cp_appt_postdata = '<?php echo $postdata ?>';<?php endif; ?>
			</script>

			<script src="//www.demandforced3.com/b/fameautomotive/scheduler.widget" type="text/javascript"></script>
		<?php
		return ob_get_clean();
	}

	/**
	 * Customer Reviews Widget
	 * Customer Reviews Page: http://fameautomotive.com/contact/customer-reviews
	 */
	public function review( $atts ){
		$atts = extract( shortcode_atts( array(
			'bid' => '', // Business ID in DemandforceD3
			'reviewurl' => '',
			'schedulerurl' => '',
		), $atts ) );

		if ( empty( $bid ) ) {
			return '<div style="color:red;">Please set Demandforce Business ID in shortcode. For example: <pre>[df-review bid="123456789"]</pre></div>';
		}

		ob_start();
		?>
			<link type="text/css" rel="stylesheet" href="//www.demandforce.com/widget/css/widget.css" />

			<script type="text/javascript">
				d3cp_bid = '<?php echo $bid ?>';
				<?php if( !empty($reviewurl) ): ?>d3cp_link_addreview = '<?php echo $reviewurl ?>';<?php endif; ?>
				<?php if( !empty($schedulerurl) ): ?>d3cp_link_scheduler = '<?php echo $schedulerurl ?>';<?php endif; ?>
			</script>
			<script src="//www.demandforced3.com/b/fameautomotive/reviews.widget" type="text/javascript"></script>
		<?php
		return ob_get_clean();
	}

}

/**
 * Reviews Widget. Requires EXID, which is different from BID.
 */
class Storm_Demandforce_Reviews_Widget extends WP_Widget {

    /**
     * Constructor
     *
     * @return void
     **/
    function Storm_Demandforce_Reviews_Widget() {
        $widget_ops = array( 'classname' => 'df-reviews', 'description' => 'List reviews from Demandforce.' );
        $this->WP_Widget( 'df-reviews', 'Demandforce Reviews', $widget_ops );
    }

    /**
     * Outputs the HTML for this widget.
     *
     * @param array  An array of standard parameters for widgets in this theme
     * @param array  An array of settings for this widget instance
     * @return void Echoes it's output
     **/
    function widget( $args, $instance ) {
        extract( $args, EXTR_SKIP );

        $source = parse_url( get_site_url(), PHP_URL_HOST );

        echo $before_widget;
        // echo $before_title;
        // echo 'Title'; // Can set this with a widget option, or omit altogether
        // echo $after_title;
        ?>
   		<img 
   			src="//www.demandforced3.com/b/css/1.0/images/bttn_reviews120.png" 
   			style="cursor: pointer;" 
   			name="reviews_tile" 
   			id="reviews_tile_s" 
   			onclick="javascript:window.open('//www.demandforced3.com/b/etile_reviews_popup.jsp?d3cp_exid=<?php echo $instance['exid'] ?>&d3cp_source=<?php echo $source ?>','newwindow', 'width=790px, top=0, left=0, toolbar=no, menubar=no, scrollbars=1, resizable=1, location=no, status=0');"
   		/>
		<?php
	    echo $after_widget;
	}

    /**
     * Deals with the settings when they are saved by the admin. Here is
     * where any validation should be dealt with.
     *
     * @param array  An array of new settings as submitted by the admin
     * @param array  An array of the previous settings
     * @return array The validated and (if necessary) amended settings
     **/
    function update( $new_instance, $old_instance ) {
    	$instance = $old_instance;

		$instance['exid'] = strip_tags( $new_instance['exid'] );

        return $instance;
    }

    /**
     * Displays the form for this widget on the Widgets page of the WP Admin area.
     *
     * @param array  An array of the current settings for this widget
     * @return void Echoes it's output
     **/
    function form( $instance ) {
        ?>
		<p><label><?php _e( 'Dreamforce EXID:' ); ?>
			<input class="widefat" id="<?php esc_attr_e( $this->get_field_id( 'exid' ) ); ?>" name="<?php esc_attr_e( $this->get_field_name( 'exid' ) ); ?>" type="text" value="<?php esc_attr_e( $instance['exid'] ); ?>" />
		</label>
		<small>The EXID looks like a username. It is not the same as the BID, which is a number.</small>
		</p>
        <?php
    }
}

add_action( 'widgets_init', create_function( '', "register_widget( 'Storm_Demandforce_Reviews_Widget' );" ) );