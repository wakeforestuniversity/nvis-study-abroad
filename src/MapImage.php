<?php

namespace InvisibleUs\StudyAbroad;

use \League\Geotools\Polygon\Polygon;

class MapImage {
    const MAX_WIDTH = 1280; 

    static function get_url(array $coords, array $args = []) {
        $token = Plugin::get_option('sap_mapbox_access_token');
    
        if (!$token || empty($coords)) {
            return false;
        }
    
        $defaults = [
            'width' => 600,
            'height' => 450,
            'zoom' => 4,
            'show_markers' => true,
            'hd' => true,
            'offset_long' => 0,
            'style' => self::get_map_style()
        ];
    
        $args = array_merge(
            $defaults,
            $args
        );
    
        $url = [];
    
        $url['base'] = sprintf(
            'https://api.mapbox.com/styles/v1/%1$s/static',
            $args['style']
        );
    
        if ($args['show_markers']) {
            $markers = []; 
            $color = str_replace('#', '', Plugin::get_option('sap_maps_marker_color', 'dd3333'));

            foreach ($coords as $c) {    
                $markers[] = sprintf(
                    'pin-s+%s(%s,%s)', 
                    $color,
                    $c['long'], 
                    $c['lat']
                ); 
            }

            $url['marker'] = !empty($markers) ? ('/' . implode(',',$markers)) : '';
        } 

        if (count($coords) > 1) {
            if ($args['show_markers']) {
                $url['box'] ='/auto';
            } else {
                $bounds = self::get_bounding_box($coords);

                $url['box'] = sprintf(
                    '/[%s,%s,%s,%s]',
                    $bounds[0]['long'],
                    $bounds[0]['lat'],
                    $bounds[1]['long'],
                    $bounds[1]['lat'],
                );
            }
        } else {
            if ($args['offset_long']) {

                $long = self::increase_long(
                    $coords[0]['long'],
                    absint($args['offset_long']) % 180,
                    $args['offset_long']
                );
            } else {
                $long = $coords[0]['long'];
            }

            $url['box'] = sprintf(
                '/%s,%s,%s,0',
                $long,
                $coords[0]['lat'],
                $args['zoom'],
            );
        }

        [$width, $height] = self::normalize_map_dimensions($args['width'], $args['height']);

        $url['size'] = sprintf(
            '/%sx%s%s', 
            $width,
            $height,
            $args['hd'] ? '@2x' : ''
        );
        $url['token'] = '?access_token=' . $token;
    
        return implode('', $url);
    }

    static function normalize_map_dimensions($width, $height) {
        if ($width > self::MAX_WIDTH) {
            $height = ($height / $width) * self::MAX_WIDTH;
            $width = self::MAX_WIDTH;
        }

        return [
            round($width),
            round($height),
        ];
    }

    static function get_map_style() {
        $style = Plugin::get_option('sap_maps_style', 'outdoors-v11');

        if ($style === 'custom') {
            $custom = Plugin::get_option('sap_maps_style_custom');

            if ($custom) {
                $style = str_replace('mapbox://styles/', '', $custom);
            }
        } else {
            $style = 'mapbox/' . $style;
        }

        return $style;
    }

    static function get_bounding_box($coords, $padding=0.75) {
        if (count($coords) === 1) {
            return $coords[0];
        }

        $coords = array_map(
            function($c) { return array_values($c);}, 
            $coords
        );

        $polygon = new Polygon($coords);
        $bbox = $polygon->getBoundingBox();

        $north = $bbox->getNorth();
        $south = $bbox->getSouth();
        $east = $bbox->getEast();
        $west = $bbox->getWest();

        $padding = abs(min(10, $padding));

        return [
            [
                'lat' => self::increase_lat($south, $padding, -1),
                'long' => self::increase_long($west, $padding, -1),
            ],
            [
                'lat' => self::increase_lat($north, $padding),
                'long' => self::increase_long($east, $padding),
            ],
        ];
    }

    static function increase_lat($lat, $increase, $direction=1) {
        $direction = $direction > 0 ? 1 : -1;
        $new_lat = $lat + ($increase * $direction);

        if ($direction > 0) {
            return min(90, $new_lat);
        }

        return max(-90, $new_lat);
    }

    static function increase_long($long, $increase, $direction=1) {
        $direction = $direction > 0 ? 1 : -1;
        $new_long = $long + ($increase * $direction);
        $wrap = 180 - abs($new_long);

        if ($wrap < 0) {
            $reverse = $long < 0 ? 1 : -1;
            $new_long = (180 - $wrap) * $reverse;
        }

        return $new_long;
    }
}
