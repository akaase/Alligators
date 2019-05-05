<?php

  $nav_selected = "PIPLANNING";
  $left_buttons = "YES";
  $left_selected = "TREND";

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

  ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <title id="Description">Agile Release Trains & Teams: Size </title>
   <!-- <link rel="stylesheet" href="styles/main_style.css" type="text/css"> -->
   <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
   <!-- <link rel="stylesheet" href="styles/custom_nav.css" type="text/css"> -->
   <link rel="stylesheet" type="text/css" href="css/pitable.css">
   <!-- <script src="scripts/piplanning_trend.js" defer></script> -->
   <script src="scripts/piplanning_trend.js"></script>
   <script type="text/javascript" src="https://www.google.com/jsapi"></script>
   <title>Summary Table Graphs</title>
</head>
<?php
    $piidDefault = $conn->query("SELECT PI_id,MIN(end_date) FROM cadence WHERE end_date >= CURDATE() GROUP BY PI_id LIMIT 1");
    if ($piidDefault->num_rows > 0) {
      while($row = $piidDefault->fetch_assoc()) {
        $DefaultPiid = $row["PI_id"];
      }
    } else {
      $DefaultPiid = "0 results";
    } 

    $query3= $conn->query("SELECT DISTINCT PI_id FROM cadence WHERE PI_id != ''"); //chab added
    while($line = mysqli_fetch_assoc($query3)){
      $piidResults[] = $line;
    }

 if (isset($_POST['piid'])) {
  $DefaultPiid = $_POST['piid'];
}
else {
  echo 'default pi not set';
}  

$left_table_qry= $conn->query("select program_increment, parent_name,
        sum(iteration_1 + iteration_2 + iteration_3 + iteration_4 + iteration_5 + iteration_6) AS total
        from capacity a,trains_and_teams b
        where a.team_id=b.team_id
        GROUP BY program_increment, parent_name
        ORDER BY parent_name;");

while($line = mysqli_fetch_assoc($left_table_qry)){
  $piArtResults[] = $line;
}   

//below line works
$left_chart_qry="Select parent_name, total
from
(select program_increment, parent_name,
sum(iteration_1 + iteration_2 + iteration_3 + iteration_4 + iteration_5 + iteration_6) AS total
from capacity a,trains_and_teams b
where a.team_id=b.team_id
and program_increment='".$DefaultPiid."'
GROUP BY program_increment, parent_name
ORDER BY parent_name
) a
;";

  $result = $db->query($left_chart_qry); 

  $rows = array();
  $table = array();
  $table['cols'] = array(

    array('label' => 'ART Name', 'type' => 'string'),
    array('label' => 'Size of ART', 'type' => 'number')

);

  $array = array();

  while ($row = mysqli_fetch_row($result)) {
    $array[$row[0]] = $row[1];
  }
//let the user see the values
  // foreach ($array as $key => $value) {
  //   echo $key." – ".$value."<br />";
  // }

  foreach($array as $key => $value) {
    $temp = array();
    // The following line will be used to slice the Pie chart
    $temp[] = array('v' => (string) $key);
    // Values of the each slice
    $temp[] = array('v' => (int) $value);
    $rows[] = array('c' => $temp);
  }
$table['rows'] = $rows;


// convert data into JSON format
$jsonTable = json_encode($table);

$right_table_qry= $conn->query("select program_increment as pi, 
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
while($line = mysqli_fetch_assoc($right_table_qry)){
  $ArtAtTotalResults[] = $line;
} 

$result->close();
$piidDefault->close();
?>
 
<h3 style = "color: #01B0F1;">Trend -> Grid:</h3>
<body>
  <div id="form_content">
<form action="#" method="post">
	<table id="input_table" border=1>
		<tr>
      <td>Select the Program Increment (PI):</td>	
      <td>
        <div id="divPISelector"></div>
			</td>
		</tr>
		<tr>
      <td valign="top">
        <div id="art_table"></div>
			</td>
      <td valign="top">
        <div id="team_table"></div>
			</td>
		</tr>
    <tr>
      <td>
        <div id="art_chart"></div>
			</td>
      <td>
        <div id="team_chart"></div>
			</td>
		</tr>      
  </table>
</form>

<script>
  var PiArtTotal = JSON.parse('<?php echo json_encode($piArtResults,JSON_HEX_TAG|JSON_HEX_APOS); ?>');
  var ArtAtTotal = JSON.parse('<?php echo json_encode($ArtAtTotalResults,JSON_HEX_TAG|JSON_HEX_APOS); ?>');
</script>

</body>
    <!--Load the Ajax API-->
  <script type="text/javascript" src="https://www.google.com/jsapi"></script>   
  <script type="text/javascript">
  // Load the Visualization API and the piechart package.
    google.load('visualization', '1', {'packages':['corechart']});
    google.setOnLoadCallback(drawChart);
    function drawChart() {
      var data = new google.visualization.DataTable(<?=$jsonTable?>);
      var options = {
          title: defaultPiID + ' ART Size by The Number of Teams',
          is3D: 'true',
          width: 800,
          height: 600,
          pieSliceText: 'value'
        };
        var chart = new google.visualization.BarChart(document.getElementById('art_chart'));
        chart.draw(data, options);
        google.visualization.events.addListener(chart, 'select', selectHandler);

        function selectHandler() {
        var selection = chart.getSelection();
        var message = '';
          for (var i = 0; i < selection.length; i++) {
            var item = selection[i];
            if (item.row != null && item.column != null) {
              var str = data.getFormattedValue(item.row, item.column);
              var category = data
              .getValue(chart.getSelection()[0].row, 0)
              var type
              if (item.column == 1) {
              type = "trains";
              } else if(item.column == 2){
              type = "Expense";
              }else{
              type = "Profit";
              }
              message += '{row:' + item.row + ',column:' + item.column
              + '} = ' + str + '  The Category is:' + category
              + ' it belongs to : ' + type + 'with PI of: '  +defaultPiID + '\n';
            } 
            else if (item.row != null) 
            {
              var str = data.getFormattedValue(item.row, 0);
              message += '{row:' + item.row
              + ', column:none}; value (col 0) = ' + str
              + '  The Category is:' + category + '\n';
            } else if (item.column != null) {
              var str = data.getFormattedValue(0, item.column);
              message += '{row:none, column:' + item.column
              + '}; value (row 0) = ' + str
              + '  The Category is:' + category + '\n';
            }
          }
          if (message == '') {
          message = 'nothing';
          }
          getClickValueForSecondChart(category, defaultPiID);
        }

        function getClickValueForSecondChart(category, defaultPiID)
        {
            var grandTotal=0;
            var rowArray = [];
            rowArray[0] = ['Agile Release Teams', 'Total Capacity for Teams (Story Points)'];
            var htmlTable = '<table id="output_table2">';
            htmlTable += '<tr>';
            for (i = 0; i <= rowArray[0].length - 1; i++) {
                htmlTable += '<th>' + rowArray[0][i] + '</th>';
            }
            htmlTable += '</tr>';
            var twoDimData =[];
            var grandTotal=0;
            ArtAtTotal.forEach(
              function(element) {
                if ( element.pi == defaultPiID && element.parent_name==category ) {                
                    var data3fields = [];               
                    data3fields.push(element.team_name.trim());      
                    data3fields.push(element.total.trim());            
                    twoDimData.push(data3fields);
                    grandTotal+=Number(element.total.trim());
                }
              } 
            );
            var row, col;
            for (row=0; row < twoDimData.length; ++row){
                htmlTable += '<tr>';
                for (col=0; col < twoDimData[row].length; ++col){
                    htmlTable += "<td title='click here to get teams'>" + twoDimData[row][col] + "</td>";
                }
                htmlTable += '</tr>';
            }
            htmlTable+="<tr><td>The Grand Total Capacity is:</td><td>"+grandTotal+"</td></tr>";
            htmlTable += '</table>';
            document.getElementById("team_table").innerHTML = htmlTable;         
            var cont = 1;
            var rowtbl = document.getElementById("output_table2").rows.length;
            rowtbl = rowtbl - 1;
            var data = new google.visualization.arrayToDataTable([
              [{type: 'string', label: 'Teams'}, {type: 'number', label: 'Total: '}]]);
            while(cont <= rowtbl) { 
              var nomi;
              var qnt;
              nomi = document.getElementById("output_table2").rows[cont].cells[0].innerHTML;
              qnt = document.getElementById("output_table2").rows[cont].cells[1].innerHTML;
              var info = {
                name: nomi,
                qn: qnt
              };
              data.addRow([
                info.name,
                parseFloat(info.qn)
              ]);
              cont = cont +1;
            }  
            var options = {
              title: category + ' Total number of points by Teams',
              is3D: 'true',
              width: 800,
              height: 600,
              pieSliceText: 'value'
            };
            var chart = new google.visualization.BarChart(document.getElementById('team_chart'));
                chart.draw(data, options);
            }
    } 
    var PI = JSON.parse('<?php echo json_encode($piidResults,JSON_HEX_TAG|JSON_HEX_APOS); ?>');
    var defaultPiID = '<?php echo $DefaultPiid; ?>'; 
  </script>
</html>
<?php include("./footer.php"); ?>



