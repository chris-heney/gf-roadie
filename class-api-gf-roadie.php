<?php

class APIGFRoadie {

    public function __construct(){
        add_action( 'rest_api_init', [ &$this, 'at_rest_init' ] );
    }

    /**
     * at_rest_testing_endpoint
     * @return WP_REST_Response
     */
    public function delivery( WP_REST_Request $request ) {
        $params = $request->get_params();
        
        if ( !isset($params['entry_id']) || !isset($params['enabled']) ){
            return new WP_REST_Response(json_encode([ 'worked' => false, 'message' => 'Missing entry_id or enabled parameter' ]));
        }

        $entry_id = $params['entry_id'];
        $enabled = ( $params['enabled'] === 'false' ) ? false : true;

        gform_update_meta( $entry_id, 'shipment_enabled ', $enabled );

        return new WP_REST_Response(json_encode([ "worked" => true ]));
    }

    public function estimate() {
        // @todo: get from settings
        $endpoint = 'https://connect-sandbox.roadie.com/v1/estimates';
        $token = '1dca1632b210b3bd46c9537cc463b9b6aec04e53';

        $payload = [
            "items" => [ [
                    "length"   => 1.0,
                    "width"    => 1.0,
                    "height"   => 1.0,
                    "weight"   => 1.0,
                    "quantity" => 1,
                    "value"    => 20
            ] ],
            "pickup_location" => [
                "address" => [
                    "street1" => "2224 Tamarisk Dr",
                    "city"    => "Plano",
                    "state"   => "TX",
                    "zip"     => "75023"
                ]
            ],
            "delivery_location" => [
                "address" => [
                    "street1" => "4529 Aspen Glen Rd",
                    "city"    => "Plano",
                    "state"   => "TX",
                    "zip"     => "75024"
                ]
            ],
            "pickup_after" => "2022-10-12T13:00:00Z",
            "deliver_between" => [
                "start" => "2022-10-23T21:00:00Z",
                "end"   => "2022-10-23T23:00:00Z"
            ]
        ];

		try {
			$mimeType = 'text/html';

			$ch = curl_init($endpoint);

			curl_setopt( $ch, CURLOPT_URL, $endpoint );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $ch, CURLOPT_POST, 1 );
			curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "POST" );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, [
				'Content-Type: application/json',
                'Authorization: Bearer ' . $token
			]) ; 

			curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($payload) );

			$results = curl_exec( $ch );

			// Check the return value of curl_exec(), too
			if ($results === false) {
				throw new Exception(curl_error($ch), curl_errno($ch));
			}

			// Check HTTP return code, too; might be something else than 200
			$httpReturnCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            return new WP_REST_Response($results);

		} catch(Exception $e) {

			trigger_error(sprintf(
				'Curl failed with error #%d: %s',
				$e->getCode(), $e->getMessage()),
				E_USER_ERROR);

		} finally {
			// Close curl handle unless it failed to initialize
			if (is_resource($ch)) {
				curl_close($ch);
			}
		}
    }

    /**
     * at_rest_init
     */
    public function at_rest_init() {
        // route url: wholesomegrub.com/wp-json/roadie/v1/delivery/<gf-entry-id>/enable
        // route url: wholesomegrub.com/wp-json/roadie/v1/delivery/<gf-entry-id>/disable
        $namespace = 'gf-roadie/v1';

        register_rest_route( $namespace, 'delivery/(?P<entry_id>\d+)', [
            'methods'   => WP_REST_Server::READABLE,
            'callback'  => [ &$this, 'delivery' ]
        ] );

        register_rest_route( $namespace, 'estimate/(?P<entry_id>\d+)', [
            'methods'   => WP_REST_Server::READABLE,
            'callback'  => [ &$this, 'estimate' ]
        ] );
    }
}

$api_gf_roadie = new APIGFRoadie();