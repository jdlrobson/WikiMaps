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

class Bounds {

    public $MIN_LAT = -85;
    public $MAX_LAT = 85;
    public $MIN_LNG = -180;
    public $MAX_LNG = 180;
    public $minLat = 85;
    public $minLng = 180;
    public $maxLat = -85;
    public $maxLng = -180;

    public function normalize() {
        $this->minLat = max($this->minLat, $this->MIN_LAT);
        $this->minLng = max($this->minLng, $this->MIN_LNG);
        $this->maxLat = min($this->maxLat, $this->MAX_LAT);
        $this->maxLng = min($this->maxLng, $this->MAX_LNG);
    }

    public function extend($scale) {
        $this->lenLan = $this->maxLat - $this->minLat;
        $this->lenLng = $this->maxLng - $this->minLng;
        $this->minLat = $this->minLat - ($this->lenLan * $scale);
        $this->maxLat = $this->maxLat + ($this->lenLan * $scale);
        $this->minLng = $this->minLng - ($this->lenLng * $scale);
        $this->maxLng = $this->maxLng + ($this->lenLng * $scale);
        $this->normalize();
    }

    public function addCoordinatesArray($coordsArr) {
        foreach ($coordsArr as $coords) {
            if (is_float($coords[0])) {
                $this->addCoordinates($coords);
            } else {
                if (is_array($coords[0])) {
                    $this->addCoordinatesArray($coords);
                }
            }
        }
    }

    public function addCoordinates($coords) {
        $lat = $coords[1];
        $lng = $coords[0];
        if ($lat < $this->minLat) {
            $this->minLat = $lat;
        }

        if ($lng < $this->minLng) {
            $this->minLng = $lng;
        }

        if ($lat > $this->maxLat) {
            $this->maxLat = $lat;
        }

        if ($lng > $this->maxLng) {
            $this->maxLng = $lng;
        }
    }

}

