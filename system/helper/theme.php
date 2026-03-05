<?php
    namespace Opencart\System\Helper\Extension\Theme;
    
    class Theme {

        public static function toRgba($hex, $alpha = 0.9) {
            $hex = str_replace('#', '', $hex);
    
            if (strlen($hex) == 3) {
                $r = hexdec(str_repeat(substr($hex,0,1),2));
                $g = hexdec(str_repeat(substr($hex,1,1),2));
                $b = hexdec(str_repeat(substr($hex,2,1),2));
            } else {
                $r = hexdec(substr($hex,0,2));
                $g = hexdec(substr($hex,2,2));
                $b = hexdec(substr($hex,4,2));
            }
            return "rgba($r, $g, $b, $alpha)";
        }

    }