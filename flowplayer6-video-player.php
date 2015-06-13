<?php
/*
  Plugin Name: Flowplayer 6 Video Player
  Version: 1.0.1
  Plugin URI: http://wphowto.net/flowplayer-6-video-player-for-wordpress-813
  Author: naa986
  Author URI: http://wphowto.net/
  Description: Easily embed videos in WordPress using Flowplayer 6
 */

if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists('FLOWPLAYER6_VIDEO_PLAYER')) {

    class FLOWPLAYER6_VIDEO_PLAYER {

        var $plugin_version = '1.0.1';

        function __construct() {
            define('FLOWPLAYER6_VIDEO_PLAYER_VERSION', $this->plugin_version);
            $this->plugin_includes();
        }

        function plugin_includes() {
            add_action('wp_enqueue_scripts', 'flowplayer6_enqueue_scripts');
            add_action('wp_head', 'flowplayer6_video_player_header');
            add_shortcode('flowplayer', 'flowplayer6_video_shortcode');
            //allows shortcode execution in the widget, excerpt and content
            add_filter('widget_text', 'do_shortcode');
            add_filter('the_excerpt', 'do_shortcode', 11);
            add_filter('the_content', 'do_shortcode', 11);
        }

        function plugin_url() {
            if ($this->plugin_url)
                return $this->plugin_url;
            return $this->plugin_url = plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__));
        }
    }

    $GLOBALS['flowplayer6_video_player'] = new FLOWPLAYER6_VIDEO_PLAYER();
}

function flowplayer6_enqueue_scripts() {
    if (!is_admin()) {
        $plugin_url = plugins_url('', __FILE__);
        wp_enqueue_script('jquery');
        wp_register_script('flowplayer-js', $plugin_url . '/lib/flowplayer.min.js');
        wp_enqueue_script('flowplayer-js');
        wp_register_style('flowplayer-css', $plugin_url . '/lib/skin/all-skins.css');
        wp_enqueue_style('flowplayer-css');
    }
}

function flowplayer6_video_player_header() {
    if (!is_admin()) {
        $fp_config = '<!-- This content is generated with the Flowplayer 6 Video Player plugin -->';
        $fp_config .= '<script>';
        $fp_config .= 'flowplayer.conf.embed = false;';
        $fp_config .= 'flowplayer.conf.keyboard = false;';
        $fp_config .= '</script>';
        $fp_config .= '<!-- Flowplayer 6 Video Player plugin -->';
        echo $fp_config;
    }
}

function flowplayer6_video_shortcode($atts) {
    extract(shortcode_atts(array(
                'src' => '',
                'webm' => '',
                'width' => '',
                'height' => '',
                'ratio' => '0.417',
                'autoplay' => 'false',
                'poster' => '',
                'loop' => '',
                'class' => '',
                    ), $atts));
    //autoplay
    if ($autoplay == "true") {
        $autoplay = " autoplay";
    } else {
        $autoplay = "";
    }
    //loop
    if ($loop == "true") {
        $loop = " loop";
    }
    else{
        $loop = "";
    }
    //video src
    $src = '<source type="video/mp4" src="'.$src.'"/>';
    //add webm video if specified in the shortcode
    if (!empty($webm)) {
        $webm = '<source type="video/webm" src="'.$webm.'"/>';
        $src = $src.$webm; 
    }
    $player = "fp" . uniqid();
    //poster
    $color = '';
    if (!empty($poster)) {
        $color = 'background: #000 url('.$poster.') 0 0 no-repeat;background-size: 100%;';
    } else {
        $color = 'background-color: #000;';
    }
    //video size
    $size_attr = "";
    if (!empty($width)) {
        $size_attr = "max-width: {$width}px;max-height: auto;";
    }
    //video skin
    $class_array = array('flowplayer', 'minimalist');
    if(!empty($class)){
        $shortcode_class_array = array_map('trim', explode(' ', $class));
        $shortcode_class_array = array_filter( $shortcode_class_array, 'strlen' );
        $shortcode_class_array = array_values($shortcode_class_array);
        if(in_array("functional", $shortcode_class_array) || in_array("playful", $shortcode_class_array)){
            $class_array = array_diff($class_array, array('minimalist'));
        }
        $class_array = array_merge($class_array, $shortcode_class_array);
        $class_array = array_unique($class_array);
        $class_array = array_values($class_array);
    }

    $classes = implode(" ", $class_array);
    $styles = <<<EOT
    <style>
        #$player {
            $size_attr
            $color    
        }
    </style>
EOT;
    
    $output = <<<EOT
        <div id="$player" data-ratio="$ratio" class="{$classes}">
            <video{$autoplay}{$loop}>
               $src   
            </video>
        </div>
        $styles
EOT;
    return $output;
}
