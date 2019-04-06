<?php

$nav_selected = "PIPLANNING";
$left_buttons = "YES"; 
$left_selected = "CAPACITY";

include("./nav.php");
global $db;

date_default_timezone_set('America/Chicago');

?>



<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.js"></script>

<style>
table#input_data  {
	table-layout: fixed;
	width: 100%
	border-collapse: collapse;
}

table#input_data td {
	width: 100%
	padding-top: 5px;
  padding-right: 5px;
  padding-bottom: 5px;
  padding-left: 5px;
}

#iteration_containers  {
	width: 60%
}

table#iteration_header  {
	width: 100%;
  padding-top: 20px;
  padding-right: 0px;
  padding-bottom: 0px;
  padding-left: 0px;
  border-collapse: collapse;
  border: 1px solid black;
}

#iteration_footer {
	width: 100%;
	padding-top: 10px;
  padding-right: 0px;
  padding-bottom: 50px;
  padding-left: 0px;
}

div#cap_total
{
	background-color: steelblue;
	width: 150px;
	height: 100px;
	border: 10px solid steelblue;
	border-radius: 20px;
	margin: 0px;
	text-align: center;
	color: white;
	font-size: 4em;
}

div#cap_total_iteration
{
	background-color: steelblue;
	width: 3em;
	height: 2em;
	border: 10px solid steelblue;
	border-radius: 10px;
	margin: 10 em;
	text-align: center;
	vertical-align: center;
	color: white;
	font-size: 2em;
	padding-bottom: 1.5em;
}
</style>


<!-- <link rel="stylesheet" type="text/css" href="css/piplanning_capacity.css"> -->

<h2>Capacity Calculator</h2>

<table id="input_data">
	<tr>
		<td style="text-align:right"><label>Agile Release Train:</label></td><td><div id="artSelectorHTML"></div></td>
		<td style="text-align:center" rowspan="3">Total Capacity:<div id="cap_total"></div></td>
	</tr>
	<tr>
		<td style="text-align:right"> <label>Agile Team:</label> </td><td><div id="atSelectorHTML"></div></td>
	</tr>
		
<?php
// Check for the existence of the cookie 'art', and if it exists, populate
// the $defaultArtID var with its contents. If it doesn't exist, use the field from
// the 'preferences' database.

$sql = "SELECT *
			FROM `preferences`;";
$result = $db->query($sql);

if ($result->num_rows > 0) {
	while ($row = $result->fetch_assoc()) {
		 $preferences[] = $row;
	}
}

$cookie_name = 'art';
if(isset($_COOKIE[$cookie_name])) {
    $defaultArtID = $_COOKIE[$cookie_name];
} else { 
    $defaultArtID = $preferences[28]['value'];  // The 29th DB record corresponds to 28 in the array
}

// Fetch the rows in `teams_and_trains` table matching the Agile Release Train (ART) type,
// and place it as an array in the $ARTresults variable.

$sql = "SELECT *
			FROM `trains_and_teams`
			WHERE trim(type)='ART';";
$result = $db->query($sql);

if ($result->num_rows > 0) {
	while ($row = $result->fetch_assoc()) {
		 $ARTresults[] = $row;
	}
}

// Fetch the rows in `teams_and_trains` table matching the Agile Tean (AT) type,
// and place it as an array in the $ATresults variable.

$sql = "SELECT *
			FROM `trains_and_teams`
			WHERE trim(type)='AT';";
$result = $db->query($sql);

if ($result->num_rows > 0) {
	while ($row = $result->fetch_assoc()) {
		 $ATresults[] = $row;
	}
}
?>

<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" onsubmit="return formVars()">

<script>
var totals = new Object();

// Creates the drop-down for the ART selection

function getFormVars() {
  var formVars = getCookie("formVars");
  if (formVars != '') {
    return formVars;
	} else {
		return '';
	}
}

function selectART() {
	if ( getFormVars() != '' ) {
		var defaults = JSON.parse( getFormVars() );
		var defaultArtID = defaults.chosenART;
	} else {
	  var defaultArtID = JSON.parse('<?php echo json_encode($defaultArtID,JSON_HEX_TAG|JSON_HEX_APOS); ?>');
	}
  var ARTresults = JSON.parse('<?php echo json_encode($ARTresults,JSON_HEX_TAG|JSON_HEX_APOS); ?>');
  var artSelectorHTML = '';
  artSelectorHTML += '<select id="artList" name="artList" onchange="selectAT()">\n';
  ARTresults.forEach(function(element) {
    if (defaultArtID == element.team_id) {
      artSelectorHTML += '<option value="' + element.team_id.trim() + '" selected="selected">' + element.team_name.trim() + '</option>\n';
    } else {
      artSelectorHTML += '<option value="' + element.team_id.trim() + '">' + element.team_name.trim() + '</option>\n';
    }
  });
  artSelectorHTML += '</select>\n';
  document.getElementById("artSelectorHTML").innerHTML = artSelectorHTML;
}

// Creates the drop-down for the AT selection

function selectAT() {
	if ( getFormVars() != '' ) {
		var defaults = JSON.parse( getFormVars() );
		var defaultAtID = defaults.chosenAT;
		}
  var ATresults = JSON.parse('<?php echo json_encode($ATresults,JSON_HEX_TAG|JSON_HEX_APOS); ?>');
  var atSelectorHTML = '';
  var artTeamID = document.getElementById("artList").value;
  var childrenATs = [];
  atSelectorHTML += '<select id="atList" name="atList">\n';
  ATresults.forEach(function(element) {
    if (element.parent_name == artTeamID) {
    	if (element.team_id.trim() == defaultAtID) {
    	  atSelectorHTML += '<option value="' + element.team_id.trim() + '" selected="selected">' + element.team_name.trim() + '</option>\n';
    	} else { 
      	atSelectorHTML += '<option value="' + element.team_id.trim() + '">' + element.team_name.trim() + '</option>\n';
      }
    }
  });
  atSelectorHTML += '</select>\n';
  document.getElementById("atSelectorHTML").innerHTML = atSelectorHTML;
}

function formVars() {
  var obj = { chosenART: document.getElementById("artList").value,
  						chosenAT: document.getElementById("atList").value,
  						chosenPID: document.getElementById("pidList").value }; 
  var jsonStr = JSON.stringify(obj);
	setCookie('formVars', jsonStr, 365);
}

function calcStoryPoints(velocity,daysoff) {
	if (velocity === undefined) {
    velocity = 0;
  }
  if (daysoff === undefined) {
    daysoff = 0;
  } 
	result = Math.round( 8 * (velocity / 100) - daysoff);
	if ( result <= 0 ) {
		return 0;
	} else {
		return result;
	}
}



// COOKIES.............

function getCookie(cname) {
  var name = cname + "=";
  var decodedCookie = decodeURIComponent(document.cookie);
  var ca = decodedCookie.split(';');
  for(var i = 0; i <ca.length; i++) {
    var c = ca[i];
    while (c.charAt(0) == ' ') {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
}

function setCookie(cname, cvalue, exdays) {
  var d = new Date();
  d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
  var expires = "expires="+d.toUTCString();
  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}
</script> 
 
	</tr>
	<tr>
		<td style="text-align:right"><label>Program Increment ID:</label></td>
		<td> 
		<select id='pidList' name='pidList'>
<?php
// Grab the default Program Increment ID (PI ID) based on the current date
// determined by the system time of the SQL server. The PI_id chosen will
// have its end date equal to or greater than the current date.

$cookie_name = 'formVars';
if(isset($_COOKIE[$cookie_name])) {
	$formVars = $_COOKIE[$cookie_name];
  $selection = json_decode($formVars, true);
  $DefaultPiid = $selection['chosenPID'];
  } else {
	$sql = "SELECT PI_id,MIN(end_date)
					FROM `cadence`
					WHERE end_date >= CURDATE()
					GROUP BY PI_id
					LIMIT 1;";

	$result = $db->query($sql);

	if ($result->num_rows > 0) {
		while ($row = $result->fetch_assoc()) {
			$DefaultPiid = $row["PI_id"];
		}
	}
}

// Grab the Program Increment IDs from the `cadence` table, and render
// an HTML drop-down for it.

$sql = "SELECT DISTINCT PI_id
				FROM `cadence`
				WHERE PI_id != '';";
				
$result = $db->query($sql);

if ($result->num_rows > 0) {
	while ($row = $result->fetch_assoc()) {
	
		echo '<option value="'. $row["PI_id"] . '"';
			if ($row["PI_id"] == $DefaultPiid) {
				echo ' selected="selected">';
				} else {
				echo '>';
			}
		echo $row["PI_id"] . '</option>' . "\n";
	}
}

?>
		</select>
		</td>
	</tr>
	<tr><td></td><td><input type="submit" name="submit" value="Generate"></td></tr>
</table>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}
?>
</form>


<div id="iterationTables"></div>

<?php
if(isset($_POST['submit'])) {
    // Enter the code you want to execute after the form has been submitted
    // Display Success or Failure message (if any) 
	$cookie_name = 'formVars';
	if(isset($_COOKIE[$cookie_name])) {
    $formVars = $_COOKIE[$cookie_name];
    }
		$selection = json_decode($formVars, true);

// *************************************************************************************
// WHERE ALL THE TABLE DATA GETS GENERATED
// *************************************************************************************	

$sql = "SELECT *
			FROM `cadence`
			WHERE PI_id = '" . $selection['chosenPID'] . "';";

$result = $db->query($sql);

if ($result->num_rows > 0) {	
	while ($row = $result->fetch_assoc()) {
		 $PIDiteration[] = $row;
		 
	}
}

$sql = "SELECT DISTINCT a.number,a.last_name,a.first_name,b.team_name,b.role
				FROM employees a
				JOIN membership b
				ON a.number = b.polarion_id
				WHERE b.team_name = '" . $selection['chosenAT'] . "';";

$result = $db->query($sql);

if ($result->num_rows > 0) {	
	while ($row = $result->fetch_assoc()) {
		 $ATiteration[] = $row;	 
	}
}


$sql = "SELECT team_id,iteration_1,iteration_2,iteration_3,iteration_4,iteration_5,iteration_6,
								sum(iteration_1 + iteration_2 + iteration_3 + iteration_4 + iteration_5 + iteration_6)
								AS total
				FROM capacity
				WHERE program_increment = 'PI-1902'
				AND team_id='AT-800';";
								
if ($result->num_rows > 0) {	
	while ($row = $result->fetch_assoc()) {
		 $ATcapacities[] = $row;	 
	}
}


if (isset($ATiteration)) {


foreach ($PIDiteration as $element) {

echo <<< EOT
<div id="iteration_containers">
<form>
<table "iteration_header">
	<tr>
		<td style="width: 30%"><h4>Iteration: <strong>{$element['iteration_id']}</strong> ({$element['duration']})</h4></td>
		<td style="width: 50%; text-align:right"><h4>Iteration Capacity:</h4></td>
		<td style="width: 20%" align="right"><div id="cap_total_iteration"><strong>40<strong></div></td>
	</tr>
</table>


<table class="iteration cell-border compact">
	<thead>
		<tr>	
			<th>Last Name</th>
			<th>First Name</th>
			<th>Role</th>
			<th>% Velocity<br />Available</th>
			<th>Days<br />Off</th>
			<th>Story<br />Points</th>
		</tr>
	</thead>
	<tbody>
EOT;
	foreach ($ATiteration as $element1) {
		switch ($element1['role']) {
			case "SM":
				$velocity=$preferences[3]['value'];
				break;
			case "PO":
				$velocity=$preferences[2]['value'];
				break;
			case "Dev":
				$velocity=$preferences[1]['value'];
				break;
			default:
				$velocity=100;
		}

echo <<< EOT
<tr>
		<td>{$element1['last_name']}</td>
		<td>{$element1['first_name']}</td>
		<td>{$element1['role']}</td>
		<td><input id="velocity.{$element1['number']}.{$element['iteration_id']}" name="velocity.{$element1['number']}.{$element['iteration_id']}" type="text" size="5" value="{$velocity}"></td>
		<td><input id="daysoff.{$element1['number']}.{$element['iteration_id']}" name="daysoff.{$element1['number']}.{$element['iteration_id']}" type="text" size="5" value="0"></td>
		<td><div id="{$element1['number']}-{$element['iteration_id']}-SP"></div></td>
</tr>

<script>

// When the velocity field is changed...

document.getElementById("velocity.{$element1['number']}.{$element['iteration_id']}").addEventListener('focusout', function (evt) {
	document.getElementById("{$element1['number']}-{$element['iteration_id']}-SP").innerHTML =
		calcStoryPoints(document.getElementById("velocity.{$element1['number']}.{$element['iteration_id']}").value,
		document.getElementById("daysoff.{$element1['number']}.{$element['iteration_id']}").value);
}
);

// When the daysoff field is changed...

document.getElementById("daysoff.{$element1['number']}.{$element['iteration_id']}").addEventListener('focusout', function (evt) {
	document.getElementById("{$element1['number']}-{$element['iteration_id']}-SP").innerHTML =
		calcStoryPoints(document.getElementById("velocity.{$element1['number']}.{$element['iteration_id']}").value,
		document.getElementById("daysoff.{$element1['number']}.{$element['iteration_id']}").value);
}
);

// Execute at pageload
totals['.{$element1['number']}.{$element['iteration_id']}.SP'] = calcStoryPoints(document.getElementById("velocity.{$element1['number']}.{$element['iteration_id']}").value,document.getElementById("daysoff.{$element1['number']}.{$element['iteration_id']}").value);
document.getElementById("{$element1['number']}-{$element['iteration_id']}-SP").innerHTML=totals['.{$element1['number']}.{$element['iteration_id']}.SP'];
</script>

EOT;
	}
echo <<< EOT
	</tbody>
</table>
<div id="iteration_footer">
<table>
	<tr>
		<td><button id="submit" name="submit">Submit</button></td>
		<td><button id="restoreDefaults" name="restoreDefaults">Restore Defaults</button></td>
	</tr>
</table>
<p>
</div>
</form>
</div>


EOT;
}
}	
 		
// *************************************************************************************
// *************************************************************************************	
		
  } else {
    // Display the Form and the Submit Button AND NOTHING ELSE (Leave this area alone)
}  

?>

<script>
$(document).ready(function() {
    $('table.iteration').DataTable();
} );

$('table.iteration').DataTable( {
    "paging": false,
    "searching": false,
    "bInfo": false,
    "lengthChange": false,
    "order": [[ 0, "asc" ]],
		"columnDefs": [
			{
				targets: [2, 3, 4, 5],
				className: "dt-body-center"
			},
			{
				targets: [2, 3, 4, 5],
				className: "dt-head-center"
			},
			{
				targets: [3, 4, 5],
				orderable: false
			}
		]
} );



document.addEventListener("DOMContentLoaded", selectART);
document.addEventListener("DOMContentLoaded", selectAT);
console.log(totals);
/* 
window.onbeforeunload = function() {
  document.cookie = "formVars=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
};
 */
</script>

<?php
$db->close();
?>
