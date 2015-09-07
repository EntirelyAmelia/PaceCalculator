<? 

if (!$_POST['submit']){
	prompt(null, null, false, false);
} else {
	$goal = $_POST['goal'];
	$distance = $_POST['distance'];
	$isKM = $_POST['isKM'] == 1 ? true : false;
	$showKM = $_POST['showKM'] == 1 ? true : false;

	prompt($goal, $distance, $isKM, $showKM);
	
	calculate($goal, $distance, $isKM, $showKM);
}

function calculate($goal, $distance, $isKM, $showKM) {
    if (!validate($goal)) {
        echo "Invalid time format. Please enter MM:SS or HH:MM:SS.";   
        return;
    }    
    
    $goalInSec = convertToSeconds($goal);
    $numSplits = getNumSplits($distance, $isKM, $showKM);
    $averagePaceInSec = getAveragePaceInSeconds($goalInSec, $numSplits);
    
    echo "Distance: ";
    echo $numSplits . ($showKM ? "km" : " miles");
    echo "<br/>";
    
    echo "Goal: " . $goal;
    echo "<br/>";
    echo "<br/>";
    
    echo "Average pace: " . convertToHHMMSS($averagePaceInSec);
    echo "/" . ($showKM ? "km" : "mile");
    echo "<br/>";
    
    getSplits($averagePaceInSec, $numSplits);
    
    if (floor($numSplits) != $numSplits) {
        echo "Finish: " . $goal;   
    }    
}

function getSplits($averagePaceInSec, $numSplits) {
    $clock = 0;
    
    //Starting at 1 instead of 0 because we're displaying mile 1 to start, not mile 0
    for ($x = 1; $x <= floor($numSplits); $x++){
        $clock += $averagePaceInSec;
        $clockInHHMMSS = convertToHHMMSS($clock);
        
        echo $x . ": " . $clockInHHMMSS . "<br/>";
    }

}

function convertToSeconds($time) {
    $timeSplit = explode(':', $time);
    
    $seconds;
    
    if (count($timeSplit) == 2) {
        $seconds = $timeSplit[0]*60;
        $seconds += $timeSplit[1];
    } else {
        $seconds = $timeSplit[0]*60*60;
        $seconds += $timeSplit[1]*60;
        $seconds += $timeSplit[2];
    }
    
    return $seconds;
}

function convertToHHMMSS($timeInSec) {
    $hours = floor($timeInSec/3600);
    $minutes = floor(($timeInSec-($hours*3600))/60);
    $seconds = $timeInSec - (($hours*3600) + ($minutes*60));
    
    $return = ""; 
    if($hours>0){
        $return = $hours . ":";
        $return .= str_pad($minutes, 2, "0", STR_PAD_LEFT) . ":";
    } else {
        $return = $minutes . ":";
    }
    $return .= str_pad($seconds, 2, "0", STR_PAD_LEFT);
    
    return $return;
}    

function validate($input) {
    $elements = count(explode(':', $input));
    if ($elements<2 || $elements>3){
        return false;
    } 
    
    $pattern;
    if ($elements == 2){
        $pattern = "/^([0-5][0-9]):([0-5][0-9])$/";
    } else if ($elements==3){
        $pattern = "/^(0[1-9]|1[0-2]):([0-5][0-9]):([0-5][0-9])$/";
    }
    
    if (preg_match($pattern, $input)) {
        return true;
    }
    return false;
}

function getNumSplits($distance, $isKM, $showInKM) {
    if ($showInKM){
        return getNumSplits_KM($distance, $isKM);
    } else {
        return getNumSplits_Miles($distance, $isKM);
    }
}    

function getNumSplits_Miles($distance, $isKM){
    $const_KMInMile = 1.60934;
    
    $splits;
    if ($isKM){
        $splits = $distance/$const_KMInMile;
    } else {
        $splits = $distance;
    }
    
    return round($splits, 1, PHP_ROUND_HALF_DOWN);
}

function getNumSplits_KM($distance, $isKM){
    $const_KMInMile = 1.60934;
    
    $splits;
    if (!$isKM){
        $splits = $distance*$const_KMInMile;
    } else {
        $splits = $distance;
    }
    
    return round($splits, 1, PHP_ROUND_HALF_DOWN);
}

function getAveragePaceInSeconds($goal, $numSplits) {
    $pace = $goal/$numSplits;
    
    return round($pace, 0, PHP_ROUND_HALF_UP);
}

function prompt($goal, $distance, $isKM, $showKM) {
	echo "Enter race distance and goal to get clock time for each mile/km.<br/>";

	echo "<form action='" . $_SERVER['PHP_SELF'] . "' method='post' id='form' name='form'>";
	echo "Distance: <input type='text' name='distance' size='20' onclick='this.value=\"\";' value='". htmlspecialchars($distance) . "'/>";
	echo "<input type='radio' name='isKM' value='0' ";
	if (!$isKM){ 
		echo " checked";
	}
	echo "/>miles ";
	echo "<input type='radio' name='isKM' value='1'";
	if ($isKM){ 
		echo " checked";
	}
	echo "/>km";	
		
	echo "<br/>Goal time: <input type='text' name='goal' size='20' value='";
	if (isset($goal)){
		echo $goal;
	} else {
		echo "HH:MM:SS or MM:SS";
	}
	echo "' onclick='this.value=\"\";'/>";
	
	echo "<br/>Show results in: ";
	
	echo "<input type='radio' name='showKM' value='0' ";
	if (!$showKM){ 
		echo " checked";
	}
	echo "/>miles ";
	echo "<input type='radio' name='showKM' value='1'";
	if ($showKM){ 
		echo " checked";
	}
	echo "/>km";	
	
	echo "<br/><input type='submit' name='submit' value='Get splits!'/>";
	echo "</form>";
}

?>