<?php
/***Shortcodes**/

function cse_show_events($atts, $content=null){
	extract( shortcode_atts( array(
		'num' => -1,
		'previous' => false,
		'short' => false
	), $atts ) );

	$events = ($short) ? cse_short_events($num, $previous) : cse_print_events($num, $previous);
	return $events;

}

add_shortcode('cse_events', 'cse_show_events');


function cse_load_scripts(){
    wp_enqueue_script('timepicker', CSEPATH.'scripts/jquery.timepicker.js', array('jquery'));
    wp_enqueue_script('cseadmin', CSEPATH.'scripts/cse-scripts.js', array('timepicker'));
    wp_register_style( 'timepicker-style', CSEPATH.'scripts/cse-styles.css');
    wp_enqueue_style( 'timepicker-style' );
}

add_action( 'admin_enqueue_scripts', 'cse_load_scripts' );



function cse_print_events( $numPosts=-1, $previous=false ) {

global $post;

$compare= ( $previous ) ? '<' : '>=';
$order= ( $previous ) ? 'DESC' : 'ASC';

$args = array( 'post_type' => 'speaking-events',
				'orderby' => 'meta_value',
				'meta_key' => 'unixdate',
				'meta_value' => date('Y-m-d'),
				'meta_compare' => $compare,
				'order' => $order,
				'posts_per_page' => $numPosts
		);

$events = get_posts( $args );

$output = '<dl class="cse-events">';

if ( ! empty( $events ) ) {

	$format = '<div>
			<dt class="cse-title">%1$s: %2$s</dt>
			<dd class="cse-location">%3$s - %4$s</dd>
			<dd class="cse-content">%5$s</dd>
			%6$s
		</div>';

	foreach( $events as $post ) {
		setup_postdata($post);
		$title= apply_filters( 'the_title', wp_kses_post( get_the_title() ) );
		$desc=  apply_filters( 'the_content', wp_kses_post( get_the_content() ) );
		$event_name= get_post_custom_values('eventname');
		$event_name= apply_filters( 'the_title', $event_name[0] );

		$loc= get_post_custom_values('location');
		$loc_phys= get_post_custom_values('physical');

		if ( ! empty( $loc ) ) {
			$loc = ( $loc_phys[0] ) ? $loc[0] : '<a href="https://www.google.com/maps/preview#!q='. $loc[0] .'">'. $loc[0] .'</a>';
		}

		$date= get_post_custom_values('eventdate');
		$date= $date[0];
		$pres= get_post_custom_values('preslink');
		$pres_text= get_post_custom_values('prestext');
		$pres_text = ( ! empty( $pres_text[0] ) ) ? $pres_text[0] : 'Slides and Resources';

		$pres= ($pres[0] != "") ? '<dd class="cse-slides"><a href="'.esc_url( $pres[0] ).'">'. esc_attr( $pres_text ) .'</a></dd>' : "";

		$output .= sprintf( $format,
			esc_html( $date ),
			$title,
			esc_html( $event_name ),
			wp_kses_post( $loc ),
			$desc,
			$pres
		);
	}
	wp_reset_postdata();

} else {
	$output .= '<div class="cse-no-events">
		<dd>Nothing scheduled for right now, but check back soon!</dd>
		</div>';
}

$output .= '</dl>';

return $output;

}


function cse_short_events($numPosts=-1, $previous=false){

global $post;
$events= "<table class=\"sessions\">\n";

$compare= ($previous) ? '<' : '>=';
$order= ($previous) ? 'DESC' : 'ASC';

$args = array( 'post_type' => 'speaking-events',
				'orderby' => 'meta_value',
				'meta_key' => 'unixdate',
				'meta_value' => date('Y-m-d'),
				'meta_compare' => $compare,
				'order' => $order,
				'posts_per_page' => $numPosts
		);

$myposts = get_posts( $args );

foreach( $myposts as $post ) : setup_postdata($post); ?>

		<?php
			$title= str_ireplace('"', '', trim(get_the_title()));
			$desc= str_ireplace('"', '', trim(get_the_content()));
			$eventName= get_post_custom_values('eventname');
			$eventName= $eventName[0];
			$loc= get_post_custom_values('location');
			$loc= $loc[0];
			$date= get_post_custom_values('eventdate');
			$date= $date[0];
			$events.="<tr>";
			$events.= "<td> $title</td>
						<td>$date</td>
						<td>$loc</td>";
			$events.="</tr>";


endforeach;

$events.="</table>";


return $events;

}


add_shortcode( 'cse_events', 'cse_show_events' );


?>
