<?php

$output = '<div class="row">';
foreach ($precincts as $row) {
    $output .= '<div class="precinct-listing">';
    $output .= '<div class="card">';
    $output .= '<div class="card-body">';
    $output .= '<h2>'.esc_html($row['precinct_number']).'</h2>';
    $output .= '<h3>'.esc_html($row['precinct_name']).'</h3>';
    $output .= '<p>Location:<br/>';
    $output .= '<a href="https://www.google.com/maps/place/'.$row['precinct_location'].' Alexandria VA/" target="_blank">'.esc_html($row['precinct_location']).'</a><br/>';
    $output .= 'Region: '.esc_html($row['precinct_region']).'</p>';
    $output .= '<p>Captain: '.esc_html($row['precinct_captain']).'<br/>';
    if($row['precinct_deputy'] != '') {
        $output .= 'Deputy: '.esc_html($row['precinct_deputy']).'</p>';
    }
    $output .= '</div>';
    $output .= '</div>';
    $output .= '</div>';
}
$output .= '</div>';

?>