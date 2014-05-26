<?php

use ShareMapPhp\SVGRenderer as SVGRenderer;

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
			$attrs[ 'data-map' ] = $data;
		}

		return Html::element( 'div',
			$attrs
		);
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
		$vals[ 'format' ] = 'json';
		$vals[ 'action' ] = 'query';
		$api = new ApiMain(
			new DerivativeRequest( $request, $vals )
		);

		$api->execute();
		$result = $api->getResult()->getData();

		// FIXME: What if query is not format=json ?
		if ( isset( $result[ 'query' ] ) && isset( $result[ 'query' ][ 'pages' ] ) ) {
			$pages = $result[ 'query' ][ 'pages' ];

			foreach ( $pages as $page ) {
				if ( isset( $page[ 'coordinates' ] ) ) {
					$props = array();
					if ( isset( $page[ 'title' ] ) ) {
						$props[ 'name' ] = $page[ 'title' ];
					}
					$point_coords = $page[ 'coordinates' ];
					foreach ( $point_coords as $coord ) {
						$coords = array( $coord[ 'lon' ], $coord[ 'lat' ] );
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

	/**
	 * Probably needs linktable update
	 * <map title="Map:MyMap" type="interactive|static" position="left|right" staticwidth="" staticheight=""/>
	 * @param $input
	 * @param array $args array of options
	 *              title: Find the map at the given title and render it
	 *              type: Interactive or Static map (default: interactive) [interactive|static]
	 *              width: Width of map box in pixels
	 *              height: Height of map box in pixels
	 *              fit: box|extend, in box mode rendered image has to fit inside the box but
	 *                can have on dimension smaller, in extend mode renderer try to fit entire box, even with empty space
	 *              position: Where the map should be located. Omit to make it full screen [left|right]
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return string HTML representation of map
	 */
	public static function embedMapTag($input, array $args, Parser $parser, PPFrame $frame) {
		if ( isset( $args['type'] ) ) {
			if ( $args['type'] === "static" ) {
				return self::embedStaticMapTag( $input, $args, $parser, $frame );
			}
		}
		return self::embedInteractiveMap( $input, $args, $parser, $frame );
	}

	/**
	 * @param $input
	 * @param array $args array of options
	 *              title: Find the map at the given title and render it
	 *              position: Where the map should be located. Omit to make it full screen [left|right]
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return string HTML representation of map
	 *
	 * TODO: CC-SA attribution have to handled either by including in SVG or overlaying HTML attribution over SVG
	 */
	public static function embedInteractiveMap( $input, array $args, Parser $parser, PPFrame $frame ) {
		$className = isset( $args[ 'class' ] ) ? $args[ 'class' ] : '';
		if ( isset( $args[ 'position' ] ) ) {
			$pos = $args[ 'position' ];
			if ( $pos === 'left' ) {
				$className .= ' side-map side-map-left';
			} else if ( $pos === 'right') {
				$className .= ' side-map side-map-right';
			}
		}
		if ( isset( $args[ 'title' ] ) ) {
			$title = Title::newFromText( $args['title'], NS_MAP );
			$out = $parser->getOutput();
			$out->addJsConfigVars( self::getSkinConfigVariables() );
			$out->addModuleStyles( 'wikimaps.styles' );
			$out->addModules( 'wikimaps.view.scripts' );

			return self::getMapHtmlFromTitle( $title, $className );
		} else {
			return self::getMapHtml( array(), $className );
		}
	}

	/**
	 * @param $input
	 * @param array $args array of options
	 *  title: Find the map at the given title and render it
	 *  staticwidth: Width of map in pixels - only used in case of static map, this is maximum dimension, smaller can be in output because of viewport proportions
	 *  staticheight: Width of map in pixels - only used in case of static map, this is maximum dimension, smaller can be in output because of viewport proportions
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return string HTML representation of map
	 */
	public static function embedStaticMapTag($input, array $args, Parser $parser, PPFrame $frame) {
		$parser->disableCache();
		$title = Title::newFromText($args['title'], NS_MAP);
		$out = $parser->getOutput();
		$page = WikiPage::factory($title);
		$content = $page->getContent();
		$data = $content->getJsonData();
		$svgRenderer = new SVGRenderer();
		global $wgWikiMapsTileServer;
		$svgRenderer->tileUrlPattern = $wgWikiMapsTileServer;
		$svgRenderer->viewportWidth = isset($args['staticwidth'])?$args['staticwidth']:800;
		$svgRenderer->viewportHeight = isset($args['staticheight'])?$args['staticheight']:600;
		// For testing it is better to turn this off, in case of caching or rasterizing image embeding is suggested
		$svgRenderer->embedImg = false;
		$svgStr = $svgRenderer->renderSVG($data);
		return array($svgStr, "markerType" => 'nowiki');
	}
}
