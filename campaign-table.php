<?php
/**
* Plugin Name: Campaign Admin Table Line
* Text Domain: campaign-table
* Plugin URI: https://dooh.mall-cockpit.de/
* Description: Dooh Mall-cockpit
* Version: 1.6
**/

define( 'CAMPAIGN_TABLE_LINE_POSTS_NAME', wp_basename(__FILE__) );
define( 'CAMPAIGN_TABLE_LINE_POSTS_URL', plugin_dir_url( __FILE__ ) );
define( 'CAMPAIGN_TABLE_LINE_POSTS_ENTRY', __FILE__ );
define( 'CAMPAIGN_TABLE_LINE_POSTS_DIR', WP_PLUGIN_DIR . '/campaign-table-line' );
define( 'CAMPAIGN_TABLE_LINE_POSTS_VERSION', '1.0.0' );
define( 'CAMPAIGN_TABLE_LINE_POSTS_DOMAIN', 'campaign-table-line' );
define( 'CAMPAIGN_TABLE_LINE_POSTS_PREFIX', 'campaign_table_line_' );
define( 'CAMPAIGN_TABLE_LINE_POSTS_NONCE', 'campaign_table_line_jYUvaCEPCrKN4LMQ' );

include_once "autoload.php";

\goldbach\CampaignTableLine\Bootstrap\Bootstrap::instance()->init();

register_activation_hook(__FILE__, 'campaign_table_line_activation_logic');

function campaign_table_line_activation_logic() {
    //if dependent plugin is not active
    if (!is_plugin_active('campaign-table/campaign-table.php') )
    {
        deactivate_plugins(plugin_basename(__FILE__));
    }
}

/* Custom Meta Box */
/**
 * Add a new dashboard widget.
 */
function add_goldbach_dashboard_widgets() {
    wp_register_style( 'campaign_table_line', plugins_url('css/style.css',__FILE__ ));
    wp_enqueue_style('campaign_table_line');
}
add_action( 'wp_dashboard_setup', 'add_goldbach_dashboard_widgets' );

function register_first_custom_dashboard_widget() {
 global $wp_meta_boxes;

 $dashboard = $wp_meta_boxes['dashboard']['normal']['core'];

 $goldbach_widget = array( 'add_goldbach_dashboard_widgets' => $dashboard['goldbach_campaign_widget'] );
 unset( $dashboard['goldbach_campaign_widget'] );
 unset($wp_meta_boxes['dashboard']['normal']['core']['goldbach_campaign_widget']);

 $sorted_dashboard = array_merge( $goldbach_widget, $dashboard );
	//print_r($sorted_dashboard);
 $wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
}
add_action( 'wp_dashboard_setup', 'register_first_custom_dashboard_widget' );




function custom_number_dashboard_columns()
{
   global $pagenow;
   if ( 'index.php' === $pagenow || ( isset($_GET['post']) && 'playlist' === get_post_type( $_GET['post'] ) ) ) {
    add_screen_option('layout_columns', array(
        'max' => 4,
        'default' => 1
    ));
}
}
add_action('admin_head', 'custom_number_dashboard_columns');


function custom_campaign_style() {
    global $pagenow;
    if ( 'post.php' === $pagenow && isset($_GET['post']) && 'playlist' === get_post_type( $_GET['post'] ) ) {
      echo '<style>
  #wpbody-content {padding-bottom: 30px;}
      .wp-adminify #wpbody-content #poststuff{padding-top:0}
  #poststuff #post-body.columns-1{margin-right:0;display:flex;flex-direction:column;}
  #post-body.columns-1 #postbox-container-1{order:2}
  #poststuff #post-body.columns-1 #side-sortables{width:100%;order:1}
      </style>';
  }
}
add_action('admin_head', 'custom_campaign_style');


function set_screen_layout_campaign() {
    return 1;
}
add_filter( 'get_user_option_screen_layout_playlist', 'set_screen_layout_campaign' );



function create_custom_kampagnen_posttype() {
    register_post_type( 'kampagnen',
        array(
            'labels' => array(
                'name' => __( 'Kampagnen' ),
                'singular_name' => __( 'Kampagne' )
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'kampagnen'),
            'supports' => array('title','thumbnail'),
            'menu_position' => 7
        )
    );
}
//add_action( 'init', 'create_custom_kampagnen_posttype' );


function get_user_restricted_shops() {
    // get the current user id
    $user_id = get_current_user_id();
    //echo $user_id;
    // get a field from the user
    $limit_posts = get_field('assign_shops_to_user', 'user_'.$user_id);
    if(!empty($limit_posts)){
        foreach ($limit_posts as $shop) {
            $assigned_shops_ids[] = (int) $shop->ID;
        }
        $limit_posts = $assigned_shops_ids;
        return $limit_posts;
    }else{
        return;
    }
    
}


function filter_main_query_with_cpt( $query ) {
    //echo '1';
    if ( !function_exists( 'get_current_screen' ) ) { 
        require_once ABSPATH . '/wp-admin/includes/screen.php'; 
    } 
    $screen = get_current_screen();
    if ( is_admin() && $query->is_main_query() && $screen->post_type == 'center' ) {
        $query->set( 'post__in', get_user_restricted_shops() );
    }
}
add_action( 'pre_get_posts', 'filter_main_query_with_cpt' );