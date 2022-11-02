<?php
/*
 * References:
 * - https://docs.roadie.com/#create-an-estimate
 * - https://docs.gravityforms.com/meta-boxes-entry-detail-addon-framework/
 * - https://docs.gravityforms.com/using-entry-meta-with-add-on-framework/
 */

GFForms::include_feed_addon_framework();

class GFRoadie extends GFFeedAddOn {

	private $provider = 'Roadie';

	protected $_version = GF_ROADIE_VERSION;
	protected $_min_gravityforms_version = '1.9.16';
	protected $_slug = 'gf-roadie';
	protected $_path = 'gf-roadie/gf-roadie.php';
	protected $_full_path = __FILE__;
	protected $_title = 'Gravity Forms Roadie';
	protected $_short_title = 'Roadie';

	private static $_instance = null;

	/**
	 * Get an instance of this class.
	 *
	 * @return GFRoadie
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new GFRoadie();
		}

		return self::$_instance;
	}

	/**
	 * Plugin starting point. Handles hooks, loading of language files and PayPal delayed payment support.
	 */
	public function init() {

		parent::init();

        add_filter( 'gform_entry_detail_meta_boxes', [ $this, 'register_meta_box' ], 10, 3 );

        $this->states = [ [
            'label' => 'Alabama',
            'value' => 'AL'
        ], [
            'label' => 'Alaska',
            'value' => 'AK'
        ], [
            'label' => 'Arizona',
            'value' => 'AZ'
        ], [
            'label' => 'Arkansas',
            'value' => 'AR'
        ], [
            'label' => 'California',
            'value' => 'CA'
        ], [
            'label' => 'Colorado',
            'value' => 'CO'
        ], [
            'label' => 'Connecticut',
            'value' => 'CT'
        ], [
            'label' => 'Delaware',
            'value' => 'DE'
        ], [
            'label' => 'District of Columbia',
            'value' => 'DC'
        ], [
            'label' => 'Florida',
            'value' => 'FL'
        ], [
            'label' => 'Georgia',
            'value' => 'GA'
        ], [
            'label' => 'Hawaii',
            'value' => 'HI'
        ], [
            'label' => 'Idaho',
            'value' => 'ID'
        ], [
            'label' => 'Illinois',
            'value' => 'IL'
        ], [
            'label' => 'Indiana',
            'value' => 'IN'
        ], [
            'label' => 'Iowa',
            'value' => 'IA'
        ], [
            'label' => 'Kansas',
            'value' => 'KS'
        ], [
            'label' => 'Kentucky',
            'value' => 'KY'
        ], [
            'label' => 'Louisiana',
            'value' => 'LA'
        ], [
            'label' => 'Maine',
            'value' => 'ME'
        ], [
            'label' => 'Maryland',
            'value' => 'MD'
        ], [
            'label' => 'Massachusetts',
            'value' => 'MA'
        ], [
            'label' => 'Michigan',
            'value' => 'MI'
        ], [
            'label' => 'Minnesota',
            'value' => 'MN'
        ], [
            'label' => 'Mississippi',
            'value' => 'MS'
        ], [
            'label' => 'Missouri',
            'value' => 'MO'
        ], [
            'label' => 'Montana',
            'value' => 'MT'
        ], [
            'label' => 'Nebraska',
            'value' => 'NE'
        ], [
            'label' => 'Nevada',
            'value' => 'NV'
        ], [
            'label' => 'New Hampshire',
            'value' => 'NH'
        ], [
            'label' => 'New Jersey',
            'value' => 'NJ'
        ], [
            'label' => 'New Mexico',
            'value' => 'NM'
        ], [
            'label' => 'New York',
            'value' => 'NY'
        ], [
            'label' => 'North Carolina',
            'value' => 'NC'
        ], [
            'label' => 'North Dakota',
            'value' => 'ND'
        ], [
            'label' => 'Ohio',
            'value' => 'OH'
        ], [
            'label' => 'Oklahoma',
            'value' => 'OK'
        ], [
            'label' => 'Oregon',
            'value' => 'OR'
        ], [
            'label' => 'Pennsylvania',
            'value' => 'PA'
        ], [
            'label' => 'Rhode Island',
            'value' => 'RI'
        ], [
            'label' => 'South Carolina',
            'value' => 'SC'
        ], [
            'label' => 'South Dakota',
            'value' => 'SD'
        ], [
            'label' => 'Tennessee',
            'value' => 'TN'
        ], [
            'label' => 'Texas',
            'value' => 'TX'
        ], [
            'label' => 'Utah',
            'value' => 'UT'
        ], [
            'label' => 'Vermont',
            'value' => 'VT'
        ], [
            'label' => 'Virginia',
            'value' => 'VA'
        ], [
            'label' => 'Washington',
            'value' => 'WA'
        ], [
            'label' => 'Wyoming',
            'value' => 'WY'
        ], [
            'label' => 'West Virginia',
            'value' => 'WV'
        ], [
            'label' => 'Wisconsin',
            'value' => 'WI'
        ] ];

	}

    /**
     * Add the meta box to the entry detail page.
     *
     * @param array $meta_boxes The properties for the meta boxes.
     * @param array $entry The entry currently being viewed/edited.
     * @param array $form The form object used to process the current entry.
     *
     * @return array
     */
    public function register_meta_box( $meta_boxes, $entry, $form ) {
        $meta_boxes[ $this->_slug ] = array(
            'title'    => $this->get_short_title(),
            'callback' => array( $this, 'add_details_meta_box' ),
            'context'  => 'side',
        );

        return $meta_boxes;
    }

    /**
     * The callback used to echo the content to the meta box.
     *
     * @param array $args An array containing the form and entry objects.
     */
    public function add_details_meta_box( $args ) {
    
        $form  = $args['form'];
        $entry = $args['entry'];
    
        // @TODO: Get from Roadie
        $settings = $this->get_plugin_settings();

        $shipfrom_street = rgar( $settings, 'roadie_shipfrom_street' );
		$shipfrom_city = rgar( $settings, 'roadie_shipfrom_city' );
		$shipfrom_state = rgar( $settings, 'roadie_shipfrom_state' );
		$shipfrom_zip = rgar( $settings, 'roadie_shipfrom_zip' );

        $shipment_enabled = gform_get_meta( $entry['id'], 'shipment_enabled' );

        $checked = ( $shipment_enabled === '1' ) ? 'checked="checked"' : ''; ?>
        <h3>Get Estimate</h3>
        <div id="shiping_query" style="margin-bottom: 15px;">

            <h4 style="margin: 0;">Shipping From</h4>

            <div style="margin-bottom: 15px;">
            
            <?php if ( ! $shipfrom_street ) { ?>
                <p>Not set, please configure in <a href="<?php echo admin_url('admin.php?page=gf_settings&subview=gf-roadie'); ?>">GF Roadie Settings</a>.</p>
                </div><?php 
                return;
            } 
            
            echo $shipfrom_street . '<br/>' . $shipfrom_city . ', ' . $shipfrom_state . $shipfrom_zip; ?>

            <br />
            (<a href="<?php echo admin_url('admin.php?page=gf_settings&subview=gf-roadie'); ?>">change</a>)

            </div>

            <input type="hidden" id="shiping_query_from_street" value="<?php echo $shipfrom_street; ?>" />
            <input type="hidden" id="shiping_query_from_city" value="<?php echo $shipfrom_city; ?>" />
            <input type="hidden" id="shiping_query_from_state" value="<?php echo $shipfrom_state; ?>" />
            <input type="hidden" id="shiping_query_from_zip" value="<?php echo $shipfrom_zip; ?>" />

            <h4 style="margin: 0;">Shipping To</h4>

            <input type="text" id="shiping_query_to_street" placeholder="Street Address" />

            <br/>

            <input type="text" id="shiping_query_to_city" placeholder="City" />

            <br/>

            <select id="shiping_query_to_state">
                <?php foreach ($this->states as $state){
                    echo '<option value="' . $state['value'] . '">' . $state['label'] . '</option>' . "\r\n";
                } ?>
            </select>

            <br/>

            <input type="text" id="shiping_query_to_zip" placeholder="Zip" />

            <br/>
            <br/>

            <input type="button" value="<?php echo esc_attr__( 'Get Estimate', 'gf_roadie' ); ?>" class="button" id="button_address_estimate" />
        </div>

        <div id="shiping_estimate" style="display: none;">
            <h4 style="margin: 0; text-decoration: underline;">Roadie Estimate</h4>
            <div id="shiping_estimate_response" style="margin-left: 15px;"></div>
            <br/>
            <input type="text" id="shipping_order_name" placeholder="Order Name" />
            <br/>
            <br/>
            <input type="button" value="Add +" class="button" id="button_address_add" />
        </div>

        <br/>
        <hr/>
        <br/>

        <h3>Active Delivery Addresses</h3>
        <div id="shipment_addresses">
        </div>

        <br/>
        <hr/>
        <br/>

        <input type="hidden" name="shipment_entry_id" id="shipment_entry_id" value="<?php echo $entry['id']; ?>" />
        <input type="checkbox" name="shipment_enabled" id="shipment_enabled" value="true" <?php echo $checked; ?>/>
        <label for="shipment_enabled"><?php echo esc_attr__( 'Shipping Enabled ', 'gf_roadie' ); ?></label>

        <br/>
        <br/>

        <input type="button" value="<?php echo esc_attr__( 'Save Status', 'gf_roadie' ); ?>" class="button" id="gf_roadie_save" /><?php
    }

	public function plugin_page() {
		echo 'Buy me a coffee!';
	}

    public function scripts() {
        $scripts = [ [
            'handle'    => 'gf_roadie_js',
            'src'       => $this->get_base_url() . '/js/gf-roadie.js',
            'version'   => $this->_version,
            'deps'      => [ 'jquery' ],
            'in_footer' => false,
            // 'callback'  => [ $this, 'localize_scripts' ],
            // 'strings'   => [
            //     'first'  => __( 'First Choice', 'gf_roadie' ),
            //     'second' => __( 'Second Choice', 'gf_roadie' ),
            //     'third'  => __( 'Third Choice', 'gf_roadie' )
            // ],
            'enqueue'   => [ [
                'admin_page' => [ 'entry_view' ],
            ] ]
        ], ];
     
        return array_merge( parent::scripts(), $scripts );
    }

	/**
	 * Configures the settings which should be rendered on the add-on settings tab.
	 *
	 * @return array
	 */
	public function plugin_settings_fields() {
		return [ [
            'title'  => esc_html__( 'Roadie Settings', 'gf_roadie' ),
            'fields' => [ [
                'label'   => 'Roadie Environment',
                'type'    => 'select',
                'name'    => 'roadie_endpoint',
                'choices' => [ [
                    'label' => 'Sandbox',
                    'value' => 'https://connect-sandbox.roadie.com'
                ], [
                    'label' => 'Production',
                    'value' => 'https://connect.roadie.com'
                ] ] 
            ], [
                'name'    => 'roadie_token',
                'label'   => esc_html__( 'Access Token', 'gf_roadie' ),
                'type'    => 'text',
                'class'   => 'small',
            ], [
                'name'    => 'roadie_shipfrom_street',
                'label'   => esc_html__( 'Ship From Street', 'gf_roadie' ),
                'type'    => 'text',
                'class'   => 'small',
            ], [
                'name'    => 'roadie_shipfrom_city',
                'label'   => esc_html__( 'Ship From City', 'gf_roadie' ),
                'type'    => 'text',
                'class'   => 'small',
            ], [
                'name'    => 'roadie_shipfrom_state',
                'label'   => esc_html__( 'Ship From State', 'gf_roadie' ),
                'type'    => 'select',
                'class'   => 'small',
                'choices' => $this->states,
            ], [
                'name'    => 'roadie_shipfrom_zip',
                'label'   => esc_html__( 'Ship From Zip', 'gf_roadie' ),
                'type'    => 'text',
                'class'   => 'small',
            ], ],
        ], ];
	}

	/**
     * @see   parent
	 * @see   credit: [Heroicons](https://heroicons.com/)
     *
     * @since 1.6.0
     */
    public function get_menu_icon() {
		$icon = file_get_contents(plugin_dir_path( __FILE__) . 'img/icon.svg' );
        return apply_filters('roadie_icon', $icon, 1 );
    }
}
