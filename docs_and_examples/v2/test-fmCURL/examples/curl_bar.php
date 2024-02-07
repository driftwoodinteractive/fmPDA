<?php
// *********************************************************************************************************************************
//
// curl_bar.php
//
// This test shows you how to find a bar near DevCon.
//
// *********************************************************************************************************************************
//
// Copyright (c) 2017 - 2024 Mark DeNyse
//
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in
// all copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
// SOFTWARE.
//
// *********************************************************************************************************************************

require_once 'startup.inc.php';

// Put your own Google Maps Directions API key here
define('GOOGLEAPIKEY', 'YOUR-API-KEY-HERE');

$hotelName = 'Gaylord Texan Resort';
$hotel = '1501 Gaylord Trail, Grapevine, TX 76051';

$barName = 'Grapevine Craft Brewery';
$bar = '906 Jean St, Grapevine, TX 76051';


$mapURL = utGetDrivingRouteMapURL($hotel, $bar, GOOGLEAPIKEY);

echo '
      <img src="'. $mapURL .'&format=png&size=640x540&zoom=14&maptype=roadmap&scale=2" alt="map" width="640" height="540" style="float:right;" />
      <div style="float: left; line-height: 1.5em;">
         <div style="font-size: 1.5em;"><img src="examples/darkgreen_MarkerH.png"> <strong>'. $hotelName .'</strong></div>
         <div style="font-size: 1em;">'. $hotel .'</div>
         <br>
         <i>to</i><br>
         <br>
         <div style="font-size: 1.5em;"><img src="examples/red_MarkerB.png">  <strong>'. $barName .'</strong></div>
         <div style="font-size: 1em;">'. $bar .'</div>
         <br>
         <img src="examples/beer.jpg">
      </div>
      <div style="clear: both;"></div>
      ';

echo fmGetLog();

// *********************************************************************************************************************************
//	utGetDrivingRouteMapURL
//
// *********************************************************************************************************************************
function utGetDrivingRouteMapURL($start, $end, $apiKey)
{
   $polyline = '';

   $drivingInfo = utGetDrivingInfo($start, $end, $apiKey);

   $markers = array();
   $markers[] = 'markers=color:green' . urlencode('|') . urlencode('label:H|'. $start);
   $markers[] = 'markers=color:red' . urlencode('|') .urlencode('label:B|'. $end);
   $markers = implode('&', $markers);

	if ($drivingInfo['result'] == 0) {
      $polyline = $drivingInfo['overviewPolyLine'];
	}

   return 'https://maps.googleapis.com/maps/api/staticmap?&path=enc:'. $polyline .'&'. $markers .'&key='. $apiKey;
}

// *********************************************************************************************************************************
//	utGetDrivingInfo
//
// *********************************************************************************************************************************
function utGetDrivingInfo($start, $end, $apiKey)
{
	$drivingInfo = array('result' => -1, 'distance' => '');

   $url = 'https://maps.googleapis.com/maps/api/directions/json?origin='. urlencode($start) .'&destination='. urlencode($end) .'&key='. $apiKey;

   $options = array();
   $options[CURLOPT_CONNECTTIMEOUT]    = 1;
   $options['maxAttempts']             = 3;                    // Google's servers sometimes need a few attempts before responding
   $options['decodeAsJSON']            = true;
   $options['logCURLResult']           = false;

   $curl = new fmCURL();
   $json = $curl->curl($url, METHOD_GET, '', $options);

   if ($json == NULL) {
      $drivingInfo['result'] = -1;
      fmLogger('curl->curl() returned NULL', true);
   }
   else if (strtoupper($json['status']) == 'NOT_FOUND') {
      $drivingInfo['result'] = -2;
   }
   else if (strtoupper($json['status']) == 'OVER_QUERY_LIMIT') {
      $drivingInfo['result'] = -3;
   }
   else  if (strtoupper($json['status']) == 'OK') {
      $drivingInfo['result'] = 0;

         if (array_key_exists('routes', $json)) {
            foreach ($json['routes'] as $route) {
               $drivingInfo['overviewPolyLine'] = $route['overview_polyline']['points'];
               if (array_key_exists('legs', $route)) {
                  foreach ($route['legs'] as $leg) {
                     $drivingInfo['startAddress']	= $leg['start_address'];
                     $drivingInfo['endAddress']		= $leg['end_address'];
                     $drivingInfo['distance']		= $leg['distance']['value'] / 1609.34;			// Convert meters to miles
                     fmLogger('distance = '. round($drivingInfo['distance'], 1) .' miles');
                     break;
                  }
                  break;
               }
            }
         }
   }

   if ($drivingInfo['distance'] == '') {
      fmLogger('Could not get driving info for '. $start .' to '. $end .' result='. $drivingInfo['result']);
      if (isset($json)) {
         fmLogger($json, '', true);
      }
      $drivingInfo['status'] = '';
      if ($start == '') {
         $drivingInfo['status'] .= 'Starting address not specified.';
      }
      if ($end == '') {
         $drivingInfo['status'] .= ' Ending address not specified. ';
      }
   }

	return $drivingInfo;
}

?>
