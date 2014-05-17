<?php
class WikiMapHelpers {
	/**
	 * Get the config variables needed by JavaScript to render a map.
	 * @return array of config variables to set in mw.config
	 */
	public static function getSkinConfigVariables() {
		global $wgWikiMapsTileServer,
			$wgWikiMapsImagePath,
			$wgWikiMapsAttribution;

		return array(
			'wgWikiMapsTileServer' => $wgWikiMapsTileServer,
			'wgWikiMapsAttribution' => $wgWikiMapsAttribution,
			'wgWikiMapsImagePath' => $wgWikiMapsImagePath,
		);
	}

	/**
	 * @param Title $title A page which contains GeoJSON data as underlying data
	 * @param string @className additional classes to help style the map element
	 * @return string HTML representation of map
	 */
	public static function getMapHtmlFromTitle( $title, $className = '' ) {
		$page = WikiPage::factory( $title );
		if ( $page->exists() ) {
			$content = $page->getContent();
			$data = $content->getJsonData();
		} else {
			$data = array();
		}
		return self::getMapHtml( $data, $className );
	}

	/**
	 * @param array $data representation of a GeoJSON
	 * @param string @className additional classes to help style the map element
	 * @return string HTML representation of map
	 */
	public static function getMapHtml( $data, $className = '' ) {
		$attrs = array(
			"class" => "mw-wiki-map " . $className,
		);

		if ( $data ) {
			$data = json_encode( $data );
			$attrs['data-map'] = $data;
		}

		return Html::element( 'div',
			$attrs
		);
	}
	/**
	 * Probably needs linktable update
	 * <map title="Map:MyMap" />
	 * @param $input
	 * @param array $args array of options
	 *              title: Find the map at the given title and render it
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return string HTML representation of map
	 */
	public static function embedMapTag( $input, array $args, Parser $parser, PPFrame $frame ) {
		if ( isset( $args['title'] ) ) {
			$title = Title::newFromText( $args['title'], NS_MAP );
			$out = $parser->getOutput();
			$out->addJsConfigVars( self::getSkinConfigVariables() );
			$out->addModuleStyles( 'wikimaps.styles' );
			$out->addModules( 'wikimaps.view.scripts' );

			$className = isset( $args['class'] ) ? $args['class'] : '';
			return self::getMapHtmlFromTitle( $title, $className );
		} else {
			return '';
		}
	}

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
