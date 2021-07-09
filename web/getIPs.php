<?php

	set_time_limit(90);
	
	class ipCoordinates{
		public $ipAddress;
		public $latitude;
		public $longitude;
		
		function __construct($ipAddress, $latitude, $longitude) {
			$this->ipAddress = $ipAddress;
			$this->latitude = $latitude;
			$this->longitude = $longitude;
			$this->ipsFound = 1;
		}
		
		public function getIPAddress() {
			return $this->ipAddress;
		}
	
		public function getLatitude() {
			return $this->latitude;
		}
	
		public function getLongitude() {
			return $this->longitude;
		}
		
		public function appendIPAddress($newIP){
			$this->ipAddress = $this->ipAddress.', '.$newIP;
			$this->ipsFound = $this->ipsFound + 1;
		}
	}

	//Grab latitude, longitude
	$latMax = $_POST['latMax'];
	$latMin = $_POST['latMin'];
	$longMax = $_POST['longMax'];
	$longMin = $_POST['longMin'];
	
	$ipFoundList = [];
	
	//Find file, initiate generator
	$fileData = function () {
		$ipLocationFile = getcwd().'/GeoLite2-City-Blocks-IPv4.csv';
		$file = fopen($ipLocationFile, 'r');
		
	if (!$file){
		http_response_code(404);
        die('File does not exist or cannot be opened: '.$ipLocationFile);
	}

    while (($line = fgetcsv($file)) !== false) {
        yield $line;
    }

    fclose($file);
};
	$currLatIndex = "";
	$currLongIndex = "";
	$currIPCoordinates = "";
	$i=0;
	//Look at each line
	foreach ($fileData() as $line) {
		$currIP = $line[0];
       	$currLat = $line[7];
   		$currLong =$line[8];
   		if(($currLat <= $latMax) && ($currLat >= $latMin) && ($currLong <= $longMax) && ($currLong >= $longMin)){
			$currLatIndex = array_search($currLat, array_column($ipFoundList, 'latitude'));
			if($currLatIndex !== false){
				//Only do second search if first one found
				$currLongIndex = array_search($currLong, array_column($ipFoundList, 'longitude'));
			}
			//See if existing coordinates already exist, if so append IP to list
 			if(($currLatIndex !== false) && ($currLongIndex !== false) && ($currLatIndex == $currLongIndex)) {
				$currIPCoordinates = $ipFoundList[$currLongIndex];
				$currIPCoordinates->appendIPAddress($currIP);
			}
			//Append new object to array
			else{
				$currIPCoordinates = new ipCoordinates($currIP, $currLat, $currLong);
				array_push($ipFoundList, $currIPCoordinates);
			}
   		}
		
		if(($i/10000)==0){
			flush();
			ob_flush();
			$i=0;
		}
		$currLatIndex = false;
		$currLongIndex = false;
		$i++;
	}
	
	
	if(!empty($ipFoundList)){
		echo (json_encode($ipFoundList));
	}
	else{
		echo ('');
	}
?>