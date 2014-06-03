<?php

/*
 * ShareMap PHP library https://github.com/ShareMap/ShareMap-php
 * Developed under ShareMap project http://sharemap.org/
 * Copyright (c) 2014, ShareMap Project, All rights reserved.
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3.0 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library.
 */

namespace ShareMapPhp;

use ShareMapPhp\ShareMapLog as Log;

require_once("Point.php");
require_once("Bounds.php");
require_once("StringBuilder.php");
require_once("ImgDownloader.php");
require_once("ShareMapLog.php");

class SVGRenderer {

	public $backgroundDownloader;
	private $xOffset = 0;
	private $yOffset = 0;
	private $detectBounds = false;
	private $bounds;
	private $idGenerator = 1;
	private $output;
	private $resultScale = 1;
	private $markerDataUrl = null;
	private $tileReqCounter = 0;
	/*
	 * Public configuration variables
	 * 
	 * TODO: Should be moved to separate class
	 */
	// Height of marker
	public $markerImgHeight = 41;
	// Width of marker
	public $markerImgWidth = 25;
	// Anchor X of Marker
	public $markerImgAnchorX;
	// Anchor Y of Marker
	public $markerImgAnchorY;
	// URL or marker image
	public $markerImgUrl;
	// URL pattern of tile server (format same like for Leaflet
	public $tileUrlPattern;
	// Default line style
	public $defaultLineStyle = [ ];
	// Maximum viewport width (result may be smaller because of keeping proportions)
	public $viewportWidth = 600;
	// Maximum viewport height (result may be smaller because of keeping proportions)
	public $viewportHeight = 600;
	// Embed images as Base64
	public $embedImg = false;

	public function __construct() {
		$this->imgDownloader = ImgDownloader::getInstance();
		$this->markerImgUrl = "http://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.2/images//marker-icon.png";
		$this->markerImgHeight = 41;
		$this->markerImgWidth = 25;
		$this->markerImgAnchorX = $this->markerImgWidth / 2;
		$this->markerImgAnchorY = $this->markerImgHeight;
		$this->tileUrlPattern = "http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png";
		$dls = [ ];
		$dls[ "stroke-linejoin" ] = "round";
		$dls[ "stroke-linecap" ] = "round";
		$dls[ "stroke" ] = "#0033ff";
		$dls[ "stroke-opacity" ] = "0.5";
		$dls[ "stroke-width" ] = "5";
		$dls[ "fill" ] = "none";
		$this->defaultLineStyle = $dls;
	}

	protected function projectMercator( $lam, $phi, $out ) {
		if ( (!isset( $out )) || ($out == null) ) {
			$out = new Point();
		}
		$out->x = $lam;
		$out->y = log( tan( M_PI / 4.0 + 0.5 * $phi ) );
		return $out;
	}

	protected function projectInverse( $x, $y, $out ) {
		$out->y = M_PI / 2 - 2.0 * atan( exp( -$y ) );
		$out->x = $x;
	}

	private function projectPoint( $lat, $lng ) {
		$dimension = 256;
		$DTR = M_PI / 180.0;
		$res = new Point();
		$this->projectMercator( $lng * $DTR, $lat * $DTR, $res );
		$res->x = ($res->x / M_PI + 1) / 2 * $dimension;
		$res->y = ((-$res->y) / M_PI + 1) / 2 * $dimension;
		if ( $res->x < 0 ) {
			$res->x = 0;
		}
		if ( $res->x > $dimension ) {
			$res->x = $dimension;
		}
		if ( $res->y < 0 ) {
			$res->y = 0;
		}
		if ( $res->y > $dimension ) {
			$res->y = $dimension;
		}
		$res->x = ($res->x - $this->xOffset) * $this->resultScale;
		$res->y = ($res->y - $this->yOffset) * $this->resultScale;
		return $res;
	}

	private function renderBackground() {
		$res = new StringBuilder();
		$minX = $this->minX;
		$minY = $this->minY;
		$maxX = $this->maxX;
		$maxY = $this->maxY;
		$width = $maxX - $minX;
		$height = $maxY - $minY;
		$resultScale = $this->resultScale;
		$resultWidth = $this->resultWidth;
		$resultHeight = $this->resultHeight;
		if ( $width * $resultScale < $resultWidth ) {
			$width = $resultWidth / $resultScale;
		}
		if ( $height * $resultScale < $resultHeight ) {
			$height = $resultHeight / $resultScale;
		}

		$mult = 1;
		$maxZoom = 1;
		$maxZoomMult = 1;
		for ( $i = 1; $i < 100; $i++ ) {
			$mult = pow( 2, $i );
			Log::logVar( "maxZoomMult", $mult );
			if ( ($width * $mult > $resultWidth) || ($height * $mult > $resultHeight) ) {
				$maxZoom = $i;
				$maxZoomMult = pow( 2, $maxZoom );
				Log::logVar( "$maxZoomMult", $maxZoomMult );
				break;
			}
		}

		$tileOffsetX = $minX * $maxZoomMult % 256;
		$tileOffsetY = $minY * $maxZoomMult % 256;

		$upperTileX = floor( $minX * $maxZoomMult / 256 );
		$upperTileY = floor( $minY * $maxZoomMult / 256 );

		$xTilesCount = ceil( ($width * $maxZoomMult - (256 - $tileOffsetX)) / 256 ) + 1;
		$yTilesCount = ceil( ($height * $maxZoomMult - (256 - $tileOffsetY)) / 256 ) + 1;

		$bgContentScale = min( ($resultWidth / $width ), ($resultHeight / $height ) );
		$bgScale = $bgContentScale / $maxZoomMult;
		$maxTile = pow( 2, $maxZoom ) - 1;
		$filterStr = "";
		$res->append( '<g %s transform=" scale(%f) translate(%f,%f)" style="opacity:%f">', [$filterStr, $bgScale, (0 - $tileOffsetX), (0 - $tileOffsetY), 1 ] );
		for ( $tileY = $upperTileY; $tileY < $upperTileY + $yTilesCount; $tileY++ ) {
			for ( $tileX = $upperTileX; $tileX < $upperTileX + $xTilesCount; $tileX++ ) {
				if ( ($tileX >= -$maxTile) && ($tileX <= $maxTile) && ($tileY <= $maxTile) ) {
					if ( $tileX < 0 ) {
						$tileX = $maxTile + $tileX;
					}
					$tileUrl = $this->getTileUrl( $maxZoom, $tileX, $tileY );
					if ( (isset( $tileUrl )) && ($tileUrl != "") ) {
						if ( $this->embedImg ) {
							$tileDataUrl = $this->imgDownloader->getUrlAsDataUrl( $tileUrl );
							$tileUrl = $tileDataUrl;
						}
						$tileParams = [
							(255 * ($tileX - $upperTileX)),
							(255 * ($tileY - $upperTileY)),
							$tileUrl
						];
						$res->append( '<image x="%f" y="%f" width="256" height="256" xlink:href="%s"/>', $tileParams );
					}
				}
			}
		}
		$res->append( "</g>" );

		return $res->toString();
	}

	private function getTileUrl( $zoom, $tileX, $tileY ) {
		$tileUrl = $this->tileUrlPattern;
		$letter = "a";
		switch ( $this->tileReqCounter ) {
			case 1: {
					$letter = "b";
					break;
				}
			case 2: {
					$letter = "c";
					break;
				}
		}
		$this->tileReqCounter++;
		if ( $this->tileReqCounter >= 3 ) {
			$this->tileReqCounter = 0;
		}
		$tileUrl = str_replace( "{s}", $letter, $tileUrl );
		$tileUrl = str_replace( "{z}", $zoom, $tileUrl );
		$tileUrl = str_replace( "{x}", $tileX, $tileUrl );
		$tileUrl = str_replace( "{y}", $tileY, $tileUrl );
		return $tileUrl;
	}

	private function renderComponent( $json ) {
		$type = $json[ "type" ];
		if ( (isset( $type ) ) ) {
			$type = strtolower( $type );
			$coordinates = null;
			if ( array_key_exists( "coordinates", $json ) ) {
				$coordinates = $json[ "coordinates" ];
			}
			if ( ($this->detectBounds == true) && (isset( $coordinates )) && (count( $coordinates ) > 0) ) {
				$el = $coordinates[ 0 ];
				if ( is_array( $el ) ) {
					$this->bounds->addCoordinatesArray( $coordinates );
				} else {
					//  $this->bounds->addCoordinates($coordinates);
				}
				return;
			}

			switch ( $type ) {
				case "featurecollection": {
						$this->renderFeatureCollection( $json );
						break;
					}
				case "feature": {
						$this->renderFeature( $json );
						break;
					}
				case "polygon": {
						$this->renderLine( $json, true );
						break;
					}
				case "linestring": {
						$this->renderLine( $json, false );
						break;
					}
				case "point": {
						$this->renderPoint( $json );
						break;
					}
			}
		}
		// foreach ($json as $key => $value){
		//   echo $key;
		//  var_dump($value);
		//}
	}

	private function renderFeatureCollection( $json ) {
		$features = $json[ "features" ];
		if ( isset( $features ) ) {
			foreach ( $features as $key => $feature ) {
				$this->renderComponent( $feature );
			}
		}
	}

	private function renderFeature( $json ) {
		$geometry = $json[ "geometry" ];
		if ( isset( $geometry ) ) {
			$this->renderComponent( $geometry );
		}
	}

	private function renderPoint( $json ) {
		$coordinates = $json[ "coordinates" ];
		$lng = $coordinates[ 0 ];
		$lat = $coordinates[ 1 ];
		$point = $this->projectPoint( $lat, $lng );
		$pointId = "point_" . ($this->idGenerator++);
		$output = $this->output;
		$output->append( '<g id="%s" transform="translate(%f,%f)">', [ $pointId, $point->x, $point->y ] );
		if ( $this->embedImg === true ) {
			if ( !isset( $this->markerDataUrl ) ) {
				$this->markerDataUrl = $this->imgDownloader->getUrlAsDataUrl( $markerUrl );
			}
			$markerUrl = $this->markerDataUrl;
		}
		$output->append( '<image x="%f" y="%f" width="%f" height="%f" xlink:href="%s"/>', [(0 - $this->markerImgAnchorX), (0 - $this->markerImgAnchorY), $this->markerImgWidth, $this->markerImgHeight, $this->markerImgUrl ]
		);
		/* $output->append('<circle r="6" stroke="black" stroke-width="1" fill="red" />'); */
		$output->append( '</g>' );
	}

	private function renderLine( $json, $polygon ) {
		if ( $polygon === true ) {
			$coordsArr = $json[ "coordinates" ][ 0 ];
		} else {
			$coordsArr = $json[ "coordinates" ];
		}
		$first = true;
		$data = "";
		$minX = 1000000;
		$minY = 1000000;
		foreach ( $coordsArr as $coords ) {
			$point = $this->projectPoint( $coords[ 1 ], $coords[ 0 ] );
			$px = $point->x;
			$py = $point->y;
			if ( $first ) {
				$first = false;
				$data.=sprintf( " M %f %f", $px, $py );
			} else {
				$data.=sprintf( " L %f %f", $px, $py );
			}
			if ( $px < $minX ) {
				$minX = $px;
			}
			if ( $py < $minY ) {
				$minY = $py;
			}
		}
		$output = $this->output;
		$styleAttrs = "";
		foreach ( $this->defaultLineStyle as $key => $val ) {
			$styleAttrs .= $key . '="' . $val . '" ';
		}
		$output->append( '<path %s d="%s" />', [$styleAttrs, $data ] );
	}

	/**
	 * Creates SVG rendition from GeoJSON file
	 * Mercator projection is used
	 * @param $json GeoJSON object
	 * @return string SVG XML string
	 */
	public function renderSVG( $json ) {
		$this->bounds = new Bounds();
		$this->detectBounds = true;
		$this->renderComponent( $json );
		$this->bounds->extend( 0.1 );
		$this->detectBounds = false;
		$bounds = $this->bounds;
		$point1 = $this->projectPoint( $bounds->minLat, $bounds->minLng );
		$point2 = $this->projectPoint( $bounds->maxLat, $bounds->maxLng );
		$minX = min( $point1->x, $point2->x );
		$minY = min( $point1->y, $point2->y );
		$maxX = max( $point1->x, $point2->x );
		$maxY = max( $point1->y, $point2->y );
		$this->minX = $minX;
		$this->minY = $minY;
		$this->maxX = $maxX;
		$this->maxY = $maxY;
		$scaleX = $this->viewportWidth / ($maxX - $minX);
		$scaleY = $this->viewportHeight / ($maxY - $minY);
		$this->resultScale = min( $scaleX, $scaleY );
		$this->viewportHeight = ($maxY - $minY) * $this->resultScale;
		$this->viewportWidth = ($maxX - $minX) * $this->resultScale;
		$this->resultWidth = $this->viewportWidth;
		$this->resultHeight = $this->viewportHeight;
		$this->xOffset = $minX;
		$this->yOffset = $minY;

		Log::logVar( "xOffset", $this->xOffset );
		Log::logVar( "yOffset", $this->yOffset );

		Log::logVar( "resultScale", $this->resultScale );
		Log::logVar( "resultWidth", $this->resultWidth );
		Log::logVar( "resultHeight", $this->resultHeight );
		$this->contentScale = 1;
		$this->realHeight = $this->resultHeight * $this->contentScale;
		$this->realWidth = $this->resultWidth * $this->contentScale;
		$this->output = new StringBuilder();
		$output = $this->output;
		$output->append( '<svg' );
		$output->append( 'xmlns:svg="http://www.w3.org/2000/svg" ' );
		$output->append( 'xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" ' );
		$output->append( 'xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#" ' );
		$output->append( 'xmlns:dc="http://purl.org/dc/elements/1.1" ' );
		$output->append( 'xmlns:xlink="http://www.w3.org/1999/xlink" ' );
		$output->append( 'xmlns="http://www.w3.org/2000/svg" ' );
		$output->append( 'width="%fpx" height="%fpx" viewBox="0 0 %f %f">', [$this->realWidth, $this->realHeight, $this->realWidth, $this->realHeight ] );
		$output->append( '<g id="background" ' );
		if ( $this->contentScale != 1 ) {
			$output->append( ' transform="scale(%f)" ', [$this->contentScale ] );
		}
		$output->append( '>' );
		//$output->append('<rect x="0" y="0" width="' . $this->resultWidth . '" height="' . $this->resultHeight . '" style="fill:blue;"/>');
		$bgStr = $this->renderBackground();
		$output->append( $bgStr );
		$output->append( '</g>' );
		$output->append( '<g id="shapes" ' );
		if ( $this->contentScale != 1 ) {
			$output->append( ' transform="scale(%f)" ', [$this->contentScale ] );
		}
		$output->append( '>' );
		$this->renderComponent( $json );
		$output->append( '</g>' );
		$output->append( '</svg>' );
		return $output->toString();
	}

}