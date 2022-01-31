<?php

/*
Plugin Name: Today Facts In History
Plugin URI: https://github.com/mcdavidsh
Description: A plugin that displays events in history according to current date.
Version: 1.1
Author: David Musk
Author URI: https://github.com/mcdavidsh
License: A "Slug" license name e.g. GPL2
Text Domain: today-facts-in-history
*/



//require_once(plugin_dir_path(__FILE__)."check-updates.php");
wp_enqueue_style('bootstrap_css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css');
wp_enqueue_script('bootstrap_js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js');
wp_register_script('js_main', plugin_dir_url(__FILE__).'include/main.js');
wp_enqueue_script('js_includes');
wp_enqueue_script('bootstrap_js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js');




function summary($slug){

	$url = "https://en.wikipedia.org/api/rest_v1/page/summary/";

	$request = wp_remote_get($url.$slug);

	return json_decode(wp_remote_retrieve_body( $request ));

}

function build_events(){


	$day = (int) date("d"); $month = (int) date("m");

	$url = 'https://byabbe.se/on-this-day/';

	$channel = $url . $month . '/' . $day.'/events.json';

	$mnt = date("M");

	$result =  wp_remote_get($channel);
	$res = json_decode(wp_remote_retrieve_body( $result ));


	$events = !empty($res->events) ? $res->events : "";


	$html = "<div class='container'>";
	$html = "<div class='row'>";

	$html.= "
 <!-- wp:heading -->
<h3>Today, $day $mnt in history</h3>
 <!--/wp:heading -->
";
	foreach ($events as $item):

		$wikiped = $item->wikipedia[0];

		$path = $wikiped->wikipedia;
		$parse = parse_url($path);
		$titleslug = basename($parse["path"]);
		$extra = summary($titleslug);
		$thumb = null;
		$descript = null;

		if (!empty($extra) && $extra->title !== "Not found." && !empty($extra->title) && $extra->type !==  "https://mediawiki.org/wiki/HyperSwitch/errors/not_found" ){
			$descript = (!empty($extra->extract)) ? $extra->extract : "";
			$thumb = (!empty($extra->thumbnail->source) || !empty($extra->originalimage->source)  ) ? $extra->thumbnail->source : $extra->originalimage->source;

		}




		$html .= "<div class='col-lg-12 col-md-6'>";
		$html .= "<div class='card border-0'>";


		$html .= " <div class='card-header'>

             <!-- wp:heading -->
<div class='card-title'><h5 class=''> $res->date $item->year - $item->description</h5></div>
<!-- /wp:heading -->


  <!-- wp:image {'id':3,'sizeSlug':'large','linkDestination':'none'} -->

<figure class='wp-block-image size-large'><img src='$thumb' alt='' class='wp-image-3'/></figure>

<!-- /wp:image -->
</div>
            ";


		$html.= "
<div class='card-body'>

<!-- wp:paragraph -->
<p>
            $descript
</p>
<!-- /wp:paragraph -->

</div>
";





		$html .= "</div>";

	endforeach;

	$html.= "</div>";
	$html.= "</div>";

	return $html;



}




add_shortcode("tdfacts", "build_events");

add_filter( 'auto_update_plugin' , '__return_true' );