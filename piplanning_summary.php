<?php
$nav_selected = "PIPLANNING";
$left_buttons = "YES";
$left_selected = "SUMMARY";

include("./nav.php");
global $db;

date_default_timezone_set('America/Chicago');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ics325safedb";

// Create connection	
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

$piidDefault = $conn->query("SELECT PI_id,MIN(end_date) FROM cadence WHERE end_date >= CURDATE() GROUP BY PI_id LIMIT 1");
$piidResult = $conn->query("SELECT DISTINCT PI_id FROM cadence WHERE PI_id != ''");

if ($piidDefault->num_rows > 0) {
  while($row = $piidDefault->fetch_assoc()) {
    $DefaultPiid = $row["PI_id"];
  }
} else {
  $DefaultPiid = "0 results";
}

$query3 = $conn->query("SELECT * FROM preferences");
$preferences = array();
while($line = mysqli_fetch_assoc($query3)){
    $preferences[] = $line;
}
$baseURL = $preferences[27]['value'];     // The 28th DB record corresponds to 27 in the array
$_SESSION['baseURL'] = $baseURL;	// A kludge so it can be passed to the PHP form generator

$cookie_name = 'art';
if(isset($_COOKIE[$cookie_name])) {
    $defaultArtID = $_COOKIE[$cookie_name];
} else { 
    $defaultArtID = $preferences[28]['value'];  // The 29th DB record corresponds to 28 in the array
}

?>

<!DOCTYPE HTML>
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="description" content="Agile Release Train">
	<meta name="keywords" content="HTML,CSS,XML,JavaScript">
	<meta name="author" content="Aaron Kaase">
	<link rel="stylesheet" type="text/css" href="css/pitable.css">
	<title>Program Increment (PI) Summary Table</title>
</head>
<script src="scripts/piplanning_summary.js" defer></script>

<h3 style = "color: #01B0F1;">Summary -> Grid:</h3>
<body>
<div id="form_content">
<form action="#" method="post">
	<table id="input_table" border=1>
	<tr>			
      <td>Select the Program Increment (PI):</td>			
      <td><div id="divPISelector"></div></td>
	</tr>
	<tr>
		<td>
        <?php
        $query0 = $conn->query("SELECT * FROM trains_and_teams WHERE trim(type)='ART'");
        $query1 = $conn->query("SELECT * FROM trains_and_teams WHERE trim(type)='AT'");
        $query2 = $conn->query("SELECT PI_id,iteration_id FROM cadence WHERE PI_id != ''");
        $query3= $conn->query("SELECT DISTINCT PI_id FROM cadence WHERE PI_id != ''"); 
        
        $query4= $conn->query("select program_increment, parent_name,
        sum(iteration_1 + iteration_2 + iteration_3 + iteration_4 + iteration_5 + iteration_6) AS total
        from capacity a,trains_and_teams b
        where a.team_id=b.team_id
        GROUP BY program_increment, parent_name
        ORDER BY parent_name;"); 

        $query5= $conn->query("select program_increment as pi, 
        sum(iteration_1 + iteration_2 + iteration_3 + iteration_4 + iteration_5 + iteration_6) AS total,
        team_id,
        team_name,
        parent_name	
        from		
        (		
          select a.team_id,a.program_increment,iteration_1,iteration_2,iteration_3,
          iteration_4,iteration_5,iteration_6,
          b.team_name,b.parent_name,b.type	
          from capacity a, trains_and_teams b		
          where 1=1		
          and a.team_id=b.team_id		
        ) c
        group by program_increment, team_id	
        order by pi, parent_name,team_id");

        $query6= $conn->query("select parent_name,
        sum(iteration_1 + iteration_2 + iteration_3 + iteration_4 + iteration_5 + iteration_6) AS total
        from capacity a,trains_and_teams b
        where a.team_id=b.team_id
        GROUP BY parent_name
        ORDER BY parent_name;"); 


        $ARTresults = array();
        $ATresults = array();
        $PIterations = array();
        $piidResults = array(); 
        $piArtResults = array(); 

        while($line = mysqli_fetch_assoc($query0)){
          $ARTresults[] = $line;
        }

        while($line = mysqli_fetch_assoc($query1)){
          $ATresults[] = $line;
        }

        while($line = mysqli_fetch_assoc($query2)){
          $PIterations[] = $line;
        }

        while($line = mysqli_fetch_assoc($query3)){
          $piidResults[] = $line;
        }

        while($line = mysqli_fetch_assoc($query4)){
          $piArtResults[] = $line;
        } 

        while($line = mysqli_fetch_assoc($query5)){
          $ArtAtTotalResults[] = $line;
        } 

        while($line = mysqli_fetch_assoc($query6)){
          $artTotalTest[] = $line;
        }         
        $jsonTable = json_encode($artTotalTest);
        ?>
        <script>
        // This is all the PHP passing of vars to JS for client-side stuff.
          var PI = JSON.parse('<?php echo json_encode($piidResults,JSON_HEX_TAG|JSON_HEX_APOS); ?>');
          var PiArtTotal = JSON.parse('<?php echo json_encode($piArtResults,JSON_HEX_TAG|JSON_HEX_APOS); ?>');
          var ArtAtTotal = JSON.parse('<?php echo json_encode($ArtAtTotalResults,JSON_HEX_TAG|JSON_HEX_APOS); ?>');
          var artTotalTest = JSON.parse('<?php echo json_encode($artTotalTest,JSON_HEX_TAG|JSON_HEX_APOS); ?>');
          // convert data into JSON format

          var ART = JSON.parse('<?php echo json_encode($ARTresults,JSON_HEX_TAG|JSON_HEX_APOS); ?>');
          var AT = JSON.parse('<?php echo json_encode($ATresults,JSON_HEX_TAG|JSON_HEX_APOS); ?>');
          var PIterations = JSON.parse('<?php echo json_encode($PIterations,JSON_HEX_TAG|JSON_HEX_APOS); ?>');
          var Preferences = JSON.parse('<?php echo json_encode($PIterations,JSON_HEX_TAG|JSON_HEX_APOS); ?>');
          var baseURL = '<?php echo $baseURL; ?>';
          var defaultArtID = '<?php echo $defaultArtID; ?>';
          var defaultPiID = '<?php echo $DefaultPiid; ?>';
        </script>
			</td>
	</tr>
  <tr>
    <td valign="top"><div id="table_content1"></div></td>
    <td valign="top"><div id="table_content4"></div></td>
  </tr>   
	</table>
</form>
</div>
</body>
</html>
<?php
$conn->close();
?>