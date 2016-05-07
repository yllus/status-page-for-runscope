<?php
/*
 Plugin Name: Status Page for Runscope
 Plugin URI: https://github.com/yllus/status-page-for-runscope
 Description: Display a pretty status page at a URL on your WordPress website, with all data pulled from a Runscope bucket.
 Author: Sully Syed
 Version: 1.0
 Author URI: http://yllus.com/
*/
class RunscopeStatus {
    /**
     * A reference to an instance of this class.
     */
    private static $instance;

    /**
     * The array of templates that this plugin tracks.
     */
    protected $templates;

    /**
     * Returns an instance of this class. 
     */
    public static function get_instance() {
        if ( null == self::$instance ) {
            self::$instance = new RunscopeStatus();
        } 

        return self::$instance;
    } 

    /**
     * Initializes the plugin by setting filters and administration functions.
     */
    private function __construct() {
        $this->templates = array();

        // Add a filter to the attributes metabox to inject template into the cache.
        add_filter( 'page_attributes_dropdown_pages_args', array( $this, 'register_page_template' ) );

        // Add a filter to the save post to inject out template into the page cache
        add_filter( 'wp_insert_post_data', array( $this, 'register_page_template' ) );

        // Add a filter to the template include to determine if the page has our 
        // template assigned and return it's path
        add_filter( 'template_include', array( $this, 'view_page_template' ) );

        // Add your templates to this array.
        $this->templates = array(
            'page-RUNSCOPESTATUS.php'     => 'Status Page for Runscope',
        );

        // If we're viewing our custom page template, enqueue a specific stylesheet.
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );

        // Add our custom Runscope metabox for anyone editing a Page with this template set.
        add_action( 'admin_init', array( $this, 'add_metaboxes' ) );

        // Save data from our custom Runscope metabox.
        add_action( 'save_post', array( $this, 'save_metabox_data' ), 10, 2 );

        // Register a new AJAX responder to display a bucket's test results.
        add_action( 'wp_ajax_runscope_display_test_results', array( $this, 'ajax_display_test_results' ) );
        add_action( 'wp_ajax_nopriv_runscope_display_test_results', array( $this, 'ajax_display_test_results' ) );
    } 

    public static function metabox_runscope_settings( $post ) {
        $str_rsp_access_token         = get_post_meta( $post->ID, 'rsp_access_token', true );
        $str_rsp_bucket_key           = get_post_meta( $post->ID, 'rsp_bucket_key', true );
        $str_rsp_environment_name     = get_post_meta( $post->ID, 'rsp_environment_name', true );
        ?>
        <label><strong>Access Token</strong></label>
        <br>
        <input type="password" id="rsp_access_token" name="rsp_access_token" value="<?php echo $str_rsp_access_token; ?>" style="width: 80%;" />
        <br>
        If you don't already have an OAuth2 access token to use, visit <a target="_blank" href="https://www.runscope.com/applications">https://www.runscope.com/applications</a> and 
        create one. Once it's been created, grab the <strong>Personal Access Token > Access Token</strong> value and enter it into the field above.

        <br><br>

        <label><strong>Bucket Key</strong></label>
        <br>
        <input type="text" id="rsp_bucket_key" name="rsp_bucket_key" value="<?php echo $str_rsp_bucket_key; ?>" style="width: 80%;" />
        <br>
        Copy and paste in the Bucket Key value containing the Tests you wish to see displayed on this status page. You can grab the Bucket Key value by selecting 
        the Bucket you wish to use, clicking <strong>API Tests</strong>, then <strong>Bucket Settings</strong> and then looking at the URL. The Bucket Key is the 
        value found between radar/ and /configure (eg. <strong>r6jaa77r5ltx</strong> in the URL https://www.runscope.com/radar/r6jaa77r5ltx/configure ).

        <br><br>

        <label><strong>Environment Name</strong></label>
        <br>
        <input type="text" id="rsp_environment_name" name="rsp_environment_name" value="<?php echo $str_rsp_environment_name; ?>" style="width: 80%;" />
        <br>
        If you're running a test against multiple Environments and wish to only display results from a particular Environment, enter the <strong>Environment Name</strong> 
        above. If the field is left blank, the latest test result for each test will be displayed.

        <br><br>

        <strong>Note:</strong> Need additional status pages for each of your buckets? You can always create additional buckets in Runscope and then create a Page in WordPress to point to each of them!
        <?php
    }

    /**
     * ?
     *
     */
    public function add_metaboxes() {
        if ( is_admin() && isset($_GET['post']) ) {
            $post_id = $_GET['post'] ? (int) $_GET['post'] : 0;
            $template_file = get_post_meta( $post_id, '_wp_page_template', true );

            if ( $template_file == 'page-RUNSCOPESTATUS.php' ) {
                add_meta_box('runscope_metabox_text_box', 'Status Page for Runscope Settings', array( $this, 'metabox_runscope_settings'), 'page', 'normal', 'high');
            }
        }
    }

    /**
     * ?
     *
     */
    public static function save_metabox_data() {
        global $post;

        if ( !isset($_POST['rsp_access_token']) || !isset($_POST['rsp_bucket_key']) || !isset($_POST['rsp_environment_name']) ) {
            return;
        }

        update_post_meta( $post->ID,    'rsp_access_token',     $_POST['rsp_access_token'] );
        update_post_meta( $post->ID,    'rsp_bucket_key',       $_POST['rsp_bucket_key'] );
        update_post_meta( $post->ID,    'rsp_environment_name', $_POST['rsp_environment_name'] );
    }

    /**
     * Enqueue a bit of CSS only on our custom page template (and make sure jQuery is enqueued on the page).
     *
     */
    public function enqueue_styles() {
        if ( is_page_template('page-RUNSCOPESTATUS.php')  ) {
            wp_enqueue_script( 'jquery' );
            wp_enqueue_script( 'moment', plugin_dir_url( __FILE__ ) . '/js/moment.min.js' );
            wp_enqueue_script( 'livestamp', plugin_dir_url( __FILE__ ) . '/js/livestamp.min.js' );

            wp_enqueue_style( 'open-sans', '//fonts.googleapis.com/css?family=Open+Sans' );
            wp_enqueue_style( 'rsp-page-styles', plugin_dir_url( __FILE__ ) . '/runscope-status.css' );
        }
    }

    /**
     * Adds our template to the pages cache in order to trick WordPress
     * into thinking the template file exists where it doens't really exist.
     *
     */
    public function register_page_template( $atts ) {
        // Create the key used for the themes cache
        $cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

        // Retrieve the cache list. 
        // If it doesn't exist, or it's empty prepare an array
        $templates = wp_get_theme()->get_page_templates();
        if ( empty( $templates ) ) {
            $templates = array();
        } 

        // New cache, therefore remove the old one
        wp_cache_delete( $cache_key , 'themes');

        // Now add our template to the list of templates by merging our templates
        // with the existing templates array from the cache.
        $templates = array_merge( $templates, $this->templates );

        // Add the modified cache to allow WordPress to pick it up for listing
        // available templates
        wp_cache_add( $cache_key, $templates, 'themes', 1800 );

        return $atts;
    } 

    /**
     * Checks if the template is assigned to the page
     */
    public function view_page_template( $template ) {
        global $post;

        if ( is_null($post) ) {
            return $template;
        }

        $page_template = get_post_meta( $post->ID, '_wp_page_template', true );
        if ( !isset($this->templates[$page_template] ) ) {
            return $template;
        } 

        $file = plugin_dir_path(__FILE__) . get_post_meta( $post->ID, '_wp_page_template', true );
        
        // Just to be safe, we check if the file exist first
        if ( file_exists( $file ) ) {
            return $file;
        } 
        else { 
            echo $file; 
        }

        return $template;
    } 

    public static function ajax_display_test_results() {
        $post_id = $_GET['post_id'];

        $obj_response_bucket_details = RunscopeStatus::get_bucket_details($post_id);
        if ( $obj_response_bucket_details->rsp_success != '0' ) {
            echo "Sorry, there was an issue retrieving your Runscope tests (" . $obj_response_bucket_details->rsp_message . ").";

            exit;
        }

        $obj_response = RunscopeStatus::get_bucket_tests($post_id);
        if ( $obj_response->rsp_success != '0' ) {
            echo "Sorry, there was an issue retrieving your Runscope tests (" . $obj_response->rsp_message . ").";

            exit;
        }

        $arr_test_results = array();
        foreach ( $obj_response->data as $test ) {
            $obj_response_test = RunscopeStatus::get_test_results($post_id, $test->id, $test->name, $test->last_run);

            $arr_test_results[] = $obj_response_test;
        }

        RunscopeStatus::theme_test_results($obj_response_bucket_details, $arr_test_results);        
        exit;
    }

    public static function theme_test_results( $obj_response_bucket_details, $arr_test_results ) {
        $str_test_results = '';

        $arr_bucket_status = array('passed' => 0, 'total' => 0);        
        foreach ( $arr_test_results as $test_result ) {
            if ( sizeof($test_result->data) > 0 ) {

                // If all of our assertions passed, consider the Test passed.
                if ( $test_result->data[0]->assertions_failed == 0 ) {
                    $arr_bucket_status['passed'] = $arr_bucket_status['passed'] + 1;
                }

                // Theme the Test results and add it to our output string.
                ob_start();
                require plugin_dir_path(__FILE__) . 'partials/partial.test-result.php';
                $str_test_results = $str_test_results . ob_get_contents();
                ob_end_clean();

                $arr_bucket_status['total'] = $arr_bucket_status['total'] + 1;
            }
        }

        // Theme the bucket details, placing it at the top of our output string.
        ob_start();
        require plugin_dir_path(__FILE__) . 'partials/partial.bucket-details.php';
        $str_test_results = ob_get_contents() . $str_test_results;
        ob_end_clean();

        echo $str_test_results;
    }

    public static function get_test_results( $post_id, $test_id, $test_name, $last_run ) {
        $str_rsp_bucket_key           = get_post_meta( $post_id, 'rsp_bucket_key', true );
        $str_rsp_environment_name     = get_post_meta( $post_id, 'rsp_environment_name', true );

        $str_url = 'https://api.runscope.com/buckets/' . $str_rsp_bucket_key . '/tests/' . $test_id . '/results';
        
        $arr_test_results = RunscopeStatus::make_api_request( $post_id, $str_url, array('test_name' => $test_name, 'last_run' => $last_run) );

        // If an Environment Name has been specified, filter the test results to those that match that environment.
        if ( strlen($str_rsp_environment_name) > 0 ) {
            $arr_data_temp = array();

            for ( $i = 0; $i < sizeof($arr_test_results->data); $i++ ) {
                if ( $arr_test_results->data[$i]->environment_name == $str_rsp_environment_name ) {
                    $arr_data_temp[] = $arr_test_results->data[$i];
                }
            }

            $arr_test_results->data = $arr_data_temp;
        }
        
        return $arr_test_results;
    }

    public static function get_bucket_tests( $post_id ) {
        $str_rsp_bucket_key     = get_post_meta( $post_id, 'rsp_bucket_key', true );

        $str_url = 'https://api.runscope.com/buckets/' . $str_rsp_bucket_key . '/tests?count=1000';

        return RunscopeStatus::make_api_request( $post_id, $str_url );
    }

    public static function get_bucket_details( $post_id ) {
        $str_rsp_bucket_key     = get_post_meta( $post_id, 'rsp_bucket_key', true );

        $str_url = 'https://api.runscope.com/buckets/' . $str_rsp_bucket_key;

        return RunscopeStatus::make_api_request( $post_id, $str_url );
    }

    public static function make_api_request( $post_id, $str_url, $additional_params = array() ) {
        $cache_key = 'rsp-api-request-' . $post_id . '-' . md5($str_url . serialize($additional_params));
        if ( false === ( $obj_response = get_transient( $cache_key ) ) ) {
            $str_rsp_access_token   = get_post_meta( $post_id, 'rsp_access_token', true );

            $args = $args = array(
                'method'    => 'GET',
                'timeout'   => 10,
                'sslverify' => false,
                'headers'   => array(
                    'Authorization'     => 'Bearer ' . $str_rsp_access_token,
                )
            );

            $response = wp_remote_request( $str_url, $args );

            if ( is_wp_error($response) ) {
                $response_message = $response->get_error_message();

                $obj_response = new stdClass();
                $obj_response->rsp_success = '-1';
                $obj_response->rsp_message = $response_message;

                return $obj_response;
            }

            $response_code = wp_remote_retrieve_response_code( $response );
            if ( false === strstr( $response_code, '200' ) ) {  
                $response_message = wp_remote_retrieve_response_message( $response );

                $obj_response = new stdClass();
                $obj_response->rsp_success = '-2';
                $obj_response->rsp_message = $response_message;

                return $obj_response;
            }

            $obj_response = json_decode( wp_remote_retrieve_body( $response ) );
            $obj_response->rsp_success = '0';
            $obj_response->rsp_message = 'API successfully contacted and bucket tests retrieved.';

            if ( sizeof($additional_params) > 0 ) {
                foreach ( $additional_params as $key => $val ) {
                    $obj_response->$key = $val;
                }
            }

            set_transient($cache_key, $obj_response, 60); // Cache for 1 minute.
        }

        return $obj_response;
    }
} 
add_action( 'plugins_loaded', array( 'RunscopeStatus', 'get_instance' ) );
?>