<?php
class WikiMapHelpers {
	/**
	 * Makes a feature from given coordinates
	 * @param array $coordinates a list of coordinates that describe a point
	 * @param array $props an array of properties that can be mapped to markers.
	 *        currently supports name and description properties.
	 * @param string $type the type of shape the coordinate describes.
	 * @return array The GeoJSON that is equivalent to the API result. If no geodata found returns empty array.
	 */
	public static function createFeature( $coordinates, $props = array(), $type = 'Point' ) {
		return array(
			"type" => "Feature",
			"properties" => $props,
			"geometry" => array(
				"type" => $type,
				"coordinates" => $coordinates,
			),
		);
	}

	/**
	 * Uses query string parameters on page to generate a GeoJSON
	 * @param WebRequest $request to build API query from
	 * @return array The GeoJSON that is equivalent to the API result. If no geodata found returns empty array.
	 */
	public static function makeGeoJSONFromRequest( $request ) {
		$features = array();
		$vals = $request->getValues();
		$vals['format'] = 'json';
		$vals['action'] = 'query';
		$api = new ApiMain(
			new DerivativeRequest( $request, $vals )
		);

		$api->execute();
		$result = $api->getResult()->getData();

		// FIXME: What if query is not format=json ?
		if ( isset( $result['query'] ) && isset( $result['query']['pages'] ) ) {
			$pages = $result['query']['pages'];

			foreach( $pages as $page ) {
				if ( isset( $page['coordinates'] ) ) {
					$props = array();
					if ( isset( $page['title'] ) ) {
						$props['name'] = $page['title'];
					}
					$point_coords = $page['coordinates'];
					foreach( $point_coords as $coord ) {
						$coords = array( $coord['lon'], $coord['lat'] );
						$features[] = WikiMapHelpers::createFeature( $coords, $props );
					}
				}
			}
		}

		$data = array(
			"type" => "FeatureCollection",
			"features" => $features,
		);
		return $data;
	}
}
