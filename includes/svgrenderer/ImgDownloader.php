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

class ImgDownloader {

	private static $instance = false;

	public static function getInstance() {
		if ( self::$instance == false ) {
			self::$instance = new ImgDownloader();
		}
		return self::$instance;
	}

	private function __construct() {
		
	}

	public function getUrlContent( $url ) {
		$ch = curl_init();
		$timeout = 5;
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
		$data = curl_exec( $ch );
		curl_close( $ch );
		return $data;
	}

	public function getUrlAsDataUrl( $url ) {
		$data = $this->getUrlContent( $url );
		$urll = strtolower( $url );
		if ( strpos( $urll, ".png" ) !== false ) {
			$format = "png";
		}
		if ( strpos( $urll, ".jpg" ) !== false ) {
			$format = "jpg";
		}
		if ( strpos( $urll, ".jpeg" ) !== false ) {
			$format = "jpg";
		}
		$base64 = 'data:image/' . $format . ';base64,' . base64_encode( $data );
		return $base64;
	}

}