<?php
/*
Plugin Name: WeWorkRemotely Job Listings
Plugin URI: https://www.angusokereafor.com/fun-projects/wwr-plugin/
Description: Displays remote job openings provided by weworkremotely.com
Version: 1.0.0
Author: Angus Okereafor
Author URI: https://www.angusokereafor.com/
License: MIT
*/

if( ! defined( 'ABSPATH' ) ) { exit; }
define( 'MTC_WWR_PLUGIN_FILE', __FILE__ );

// The widget class
class mtc_weworkremotely extends WP_Widget {
	public function __construct() {
        parent::__construct('mtc_weworkremotely_widget', __( 'weworkremotely.com Jobs', 'mtc_wwr' ), array( 'customize_selective_refresh' => true));
        $this->getdata();
        $this->loadcss();
    }

	// The widget form (for the backend )
	public function form( $instance ) {
		// Set widget defaults
		$defaults = array(
			'title'    => 'weworkremotely.com Jobs',
			'page_size' => 10
		);
		
		// Parse current settings with defaults
        extract( wp_parse_args( ( array ) $instance, $defaults ) ); ?>
        
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Widget Title', 'mtc_text' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'page_size' ) ); ?>"><?php _e( 'Page Size:', 'mtc_text' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'page_size' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'page_size' ) ); ?>" type="number" min="1" value="<?php echo esc_attr( $page_size ); ?>" />
		</p>
	<?php }

	// Update widget settings
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title']  = isset( $new_instance['title'] ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
		$instance['page_size'] = isset( $new_instance['page_size'] ) ? wp_strip_all_tags( $new_instance['page_size'] ) : '';
		return $instance;
	}

	// Display the widget
	public function widget( $args, $instance ) {
		extract( $args );
		// Check the widget options
		$title    = isset( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'] ) : '';
		$page_size     = isset( $instance['page_size'] ) ? $instance['page_size'] : '';
        
        // WordPress core before_widget hook
		echo $before_widget;
        
        // Display the widget title
        echo '<div class="mtc_wwr">';
        empty($title) ?: print '<div class="mtc_wwr__header">'.$args['before_title'] . apply_filters('widget_title', $title) . $args['after_title'].'</div>';
        
        //Footer
        $wwr_footer = '
            <div class="mtc_wwr__footer">
                <div class="mtc_wwr__footer__pagination mtc_wwr__footer__pagination--left"><a data-control="previous" href="#"><i class="dashicons dashicons-arrow-left-alt2"></i></a></div>
                <div class="mtc_wwr__footer__logo"><a href="https://weworkremotely.com" target="_blank"><img src="'.plugins_url('assets/images/logo.svg', MTC_WWR_PLUGIN_FILE).'"></a></div>
                <div class="mtc_wwr__footer__pagination mtc_wwr__footer__pagination--right"><a data-control="next" href="#"><i class="dashicons dashicons-arrow-right-alt2"></i></a></div>
                <input name="wwr_page" id="wwr_page" value="1" type="hidden">
                <input name="wwr_page_length" id="wwr_page_length" value="'.$page_size.'" type="hidden">
            </div>
        ';

        // Display the widget body
        echo $wwr_footer.'<div class="mtc_wwr__body"></div><div class="mtc_wwr__prefooter"></div>'.$wwr_footer;

        echo '</div>';

        // WordPress core after_widget hook
		echo $after_widget;
	}

    public function getdata(){
        $url = 'https://weworkremotely.com/remote-jobs.rss';
        $fileContents= file_get_contents($url);
        $fileContents = str_replace(array("\n", "\r", "\t"), '', $fileContents);
        $fileContents = trim(str_replace('"', "'", $fileContents));
        $simpleXml = simplexml_load_string($fileContents);
        $data = json_encode($simpleXml);

        try{
            $fileContents= file_get_contents($url);
            $fileContents = str_replace(array("\n", "\r", "\t"), '', $fileContents);
            $fileContents = trim(str_replace('"', "'", $fileContents));
            $simpleXml = simplexml_load_string($fileContents);
            $data = json_encode($simpleXml);
            $data = json_decode($data, true);
            $data = json_encode(array("status"=>"successful", "message" => "Data successfully retrieved", "data"=>array_values($data)));
        }catch(Exception $ex){
            $data = json_encode(array("status" => "error", "message" => $err));
        }

        wp_enqueue_script('wwr_js', plugins_url('assets/js/wwr.js?t='.time(), MTC_WWR_PLUGIN_FILE), array('jquery'), '1.0.0', true );
        $reshuffled_data = array('l10n_print_after' => 'wwr_data = ' . $data . ';');
        wp_localize_script( 'wwr_js', 'wwr_filler_data', $reshuffled_data);
    }

    public function loadcss(){
        wp_enqueue_style( 'dashicons' );
        wp_enqueue_style('wwr_css', plugins_url('assets/css/wwr.css', MTC_WWR_PLUGIN_FILE), array(), '1.0.0', 'all'); 
    }
}

// Register the widget
function mtc_weworkremotely_widget() {
	register_widget('mtc_weworkremotely');
}
add_action( 'widgets_init', 'mtc_weworkremotely_widget' );