<?php
$station='FG5ZBX';
$station_email='info@fg8oj.com';
$station_lon=-61.311395;
$station_lat=16.251922;

$url='https://ais.radioamateur.gp/post.php';
$address = '127.0.0.1';
$port = 4000;


require_once('base.ais.php');

// char* - AIS \r terminated string
function process_ais_raw($rawdata) { // return int
	static $num_seq; // 1 to 9
	static $seq; // 1 to 9
	static $pseq; // previous seq

	static $msg_sid = -1; // 0 to 9, indicate -1 at start state of device, do not process messages
	static $cmsg_sid; // current msg_sid
	static $itu; // buffer for ITU message

	$filler = 0; // fill bits (int)
	$chksum = 0;

	// raw data without the \n

	// calculate checksum after ! till *
	// assume 1st ! is valid
	
	// find * ensure that it is at correct position
	$end = strrpos ( $rawdata , '*' );
	if ($end === FALSE) return -1; // check for NULLS!!!
	
	$cs = substr( $rawdata, $end + 1 );
	if ( strlen($cs) != 2 ) return -1; // correct cs length
	$dcs = (int)hexdec( $cs );
	
	for ( $alias=1; $alias<$end; $alias++) $chksum ^= ord( $rawdata[$alias] ); // perform XOR for NMEA checksum

	if ( $chksum == $dcs ) // NMEA checksum pass
	{
		$pcs = explode(',', $rawdata);
		// !AI??? identifier
		$num_seq = (int)$pcs[1]; // number of sequences
		$seq = (int)$pcs[2]; // get sequence

		// get msg sequence id
		if ($pcs[3] == '') // non-multipart message, set to -1
		{
			$msg_sid = -1;
		}
		else // multipart message
		{
			$msg_sid = (int)$pcs[3];
		}
		$ais_ch = $pcs[4]; // get AIS channel

		// message sequence checking
		if ($num_seq < 1 || $num_seq > 9)
		{
			echo "ERROR,INVALID_NUMBER_OF_SEQUENCES ".time()." $rawdata\n";
			return -1;
		}
		else if ($seq < 1 || $seq > 9)
		{ // invalid sequences number
			echo "ERROR,INVALID_SEQUENCES_NUMBER ".time()." $rawdata\n";
			return -1;
		}
		else if ($seq > $num_seq)
		{
			echo "ERROR,INVALID_SEQUENCE_NUMBER_OR_INVALID_NUMBER_OF_SEQUENCES ".time()." $rawdata\n";
			return -1;
		}
		else
		{ // sequencing ok, handle single/multi-part messaging
			if ($seq == 1) // always init to 0 at first sequence
			{
				$filler = 0; // ?
				$itu = ""; // init message length
				$pseq = 0; // note previous sequence number
				$cmsg_sid = $msg_sid; // note msg_sid
			}
			if ($num_seq > 1) // for multipart messages
			{
				if ($cmsg_sid != $msg_sid // different msg_sid
					|| $msg_sid == -1 // invalid initial msg_sid
					|| ($seq - $pseq) != 1 // not insequence
					)
				{  // invalid for multipart message
					$msg_sid = -1;
					$cmsg_sid = -1;
					echo "ERROR,INVALID_MULTIPART_MESSAGE ".time()." $rawdata\n";
					return -1;
				}
				else 
				{
					$pseq++;
				}
			}

			$itu = $itu.$pcs[5]; // get itu message
			$filler += (int)$pcs[6][0]; // get filler

			if ($num_seq == 1 // valid single message
				|| $num_seq == $pseq // valid multi-part message
				)
			{
				if ($num_seq != 1) // test
				{
					//echo $rawdata;
				}
				return process_ais_itu($itu, strlen($itu), $filler /*, $ais_ch*/);
			}
		} // end process raw AIS string (checksum passed)
	}
	return -1;
}


function process_ais_itu($_itu, $_len, $_filler /*, $ais_ch*/) {
        global $vessel,$url,$station;
	GLOBAL $port; // tcpip port...
	static $debug_counter = 0;
	//DEBUG echo $_itu."\n";
	$x_a = array();
	for ($i = 0; $i<$_len; $i++) $x_a[] = ord($_itu[$i]); // convert string to array bytes

	//$debug_counter=$debug_counter+1;
	//echo ">>>> $debug_counter\n";
	$id = (int)ais2int($x_a, 6, 0); // msg id
	//echo $id."\n";
	$mmsi = 0;
	$name = '';
	$sog = -1.0;
	$cog = 0.0;
	$lon = 0.0;
	$lat = 0.0;
	$cls = 0; // class undefined
	if ($id >= 1 && $id <= 3) {
		$mmsi = ais2int($x_a, 30, 8); // mmsi
		$lon = make_lonf( ais2int($x_a, 28, 61) ); // lon
		$lat = make_latf( ais2int($x_a, 27, 89) ); // lat
		$sog = (float)ais2int($x_a, 10, 50) / 10.0; // sog
		$cog = (float)ais2int($x_a, 12, 116) / 10.0; // cog
		//$hdg = ais2int($x_a, 9, 128); // hdg
		$cls = 1; // class A
	}
	else if ($id == 18) {
		$mmsi = ais2int($x_a, 30, 8); // mmsi
		$lon = make_lonf( ais2int($x_a, 28, 57) ); // lon
		$lat = make_latf( ais2int($x_a, 27, 85) ); // lat
		$sog = (float)ais2int($x_a, 10, 46) / 10.0; // sog
		$cog = (float)ais2int($x_a, 12, 112) / 10.0; // cog
		//$hdg = ais2int($x_a, 9, 124); // hdg
		$cls = 2; // class B
	}
	else if ($id == 19) {
		$mmsi = ais2int($x_a, 30, 8); // mmsi
		$lon = make_lonf( ais2int($x_a, 28, 61) ); // lon
		$lat = make_latf( ais2int($x_a, 27, 89) ); // lat
		$sog = (float)ais2int($x_a, 10, 46) / 10.0; // sog
		$cog = (float)ais2int($x_a, 12, 112) / 10.0; // cog
		//$hdg = ais2int($x_a, 9, 124); // hdg
		$name = ais2char($x_a, 20, 143); // name
		$name = str_replace ( '@' , '', $name ); // sanitize name...
		$cls = 2; // class B
	}
	else if ($id == 5) {
		$mmsi = ais2int($x_a, 30, 8); // mmsi
		//echo ais2int($x_a, 30, 40)."\n"; // IMO Number
		//echo ais2char($x_a, 7, 70)."\n"; // callsign
		$name = ais2char($x_a, 20, 112); // name
		$name = str_replace ( '@' , '', $name ); // sanitize name...
		$cls = 1; // class A
	}
	else if ($id == 24) {
		$mmsi = ais2int($x_a, 30, 8); // mmsi
		$pn = ais2int($x_a, 2, 38); // mmsi
		if ($pn == 0)
		{
			$name = ais2char($x_a, 20, 40); // name
			$name = str_replace ( '@' , '', $name ); // sanitize name...
		}
		$cls = 2; // class B
	}
	if ($mmsi > 0 && $mmsi<1000000000) {// valid mmsi only...
		$utc = time();
		$lastutco=0;
		if (isset($vessel[$mmsi])) $lastutco=$vessel[$mmsi]['lastutc'];
		$vessel[$mmsi]['utc']=$utc;
		$vessel[$mmsi]['name']=$name;
		$vessel[$mmsi]['lon']=$lon;
		$vessel[$mmsi]['lat']=$lat;
		$vessel[$mmsi]['sog']=$sog;
		$vessel[$mmsi]['cog']=$cog;
		if ($utc-10>=$lastutco) {
	                $vessel[$mmsi]['lastutc']=$utc;
			$fields = $vessel[$mmsi];
			$fields['station']=$station;
			$fields['mmsi']=$mmsi;
print_r($fields);
			$fields_string = http_build_query($fields);
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL, $url);
			curl_setopt($ch,CURLOPT_POST, 1);
			curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
			$result = curl_exec($ch);
			curl_close($ch);

		}
	}
	return $id;
}

function process_ais_buf($ibuf) // from serial or IP comms
{
	static $cbuf = "";
	
	$cbuf = $cbuf.$ibuf;

	$last_pos = 0;
	while ( ($start = strpos($cbuf,"VDM",$last_pos)) !== FALSE)
	//while ( ($start = strpos($cbuf,"!AI",$last_pos)) !== FALSE)
	{
		//DEBUG echo $cbuf;
		if ( ($end = strpos($cbuf,"\r\n", $start)) !== FALSE) //TBD need to trim?
		{
			$tst = substr($cbuf, $start - 3, ($end - $start + 3));
			//DEBUG echo "[$start $end $tst]\n";
			process_ais_raw( $tst );
			$last_pos = $end + 1;
		}
		else
		{
			break;
		}
	}
	
	if ($last_pos > 0) $cbuf = substr($cbuf, $last_pos); // move...
	if (strlen($cbuf) > 1024) $cbuf = ""; // prevent overflow simple mode...
}


set_time_limit(0);
ob_implicit_flush();
$vessel=array();

$socket = fsockopen($address, $port);

if(!$socket)return;
stream_set_blocking($socket, 0);
stream_set_blocking(STDIN, 0);

$fields = array();
$fields['station']=$station;
$fields['lat']=$station_lat;
$fields['lon']=$station_lon;
$fields['email']=$station_email;
$fields['init']=1;


$fields_string = http_build_query($fields);
$ch = curl_init();
curl_setopt($ch,CURLOPT_URL, $url);
curl_setopt($ch,CURLOPT_POST, 1);
curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
$result = curl_exec($ch);
//print_r($result);
curl_close($ch);

while (true) {
	while($buf=fgets($socket)) {
//	    	echo $buf."\n";
		process_ais_buf($buf);
  	}
}
socket_close($socket);

?>
