document.addEventListener("DOMContentLoaded", selectPIID);
document.addEventListener("DOMContentLoaded", selectPiArt);
document.addEventListener("DOMContentLoaded", trainArray2Col); 
document.addEventListener("DOMContentLoaded", teamsCapacity); 
document.addEventListener("click", loadSecondTable); 

function selectPIID() {

	var piSelectorHTML = '';
    piSelectorHTML += '<select id="piid" name="piid" value="defaultPiID" onchange="trainArray2Col();teamsCapacity()">\n';
	PI.forEach(
        function(element) {
            if ( defaultPiID == element.PI_id ) {
                piSelectorHTML += '<option value="' + element.PI_id.trim() + '" selected="selected">' + element.PI_id.trim() + '</option>\n';
            } else {
                piSelectorHTML += '<option value="' + element.PI_id.trim() + '">' + element.PI_id.trim() + '</option>\n';
            }		
        }
	);
    piSelectorHTML += '</select>';
    document.getElementById("divPISelector").innerHTML = piSelectorHTML;
};

function loadSecondTable() {
    var table = document.getElementById("output_table");
    if (table != null) {
        for (var i = 0; i < table.rows.length; i++) { 
            table.rows[i].cells[0].onclick = function () {
                tableText(this);
            };
        }
    }
}

function tableText(tableCell) {
    selectPiArt(tableCell.innerHTML);
}

function selectPiArt(art_in) {
    var PI_passed = document.getElementById("piid").value;
    var grandTotal=0;
    var rowArray = [];
    rowArray[0] = ['Agile Release Teams', 'Total Capacity for Teams (Story Points)'];
    var htmlTable = '<table id="output_table">';
    htmlTable += '<tr>';
    for (i = 0; i <= rowArray[0].length - 1; i++) {
        htmlTable += '<th>' + rowArray[0][i] + '</th>';
    }
    htmlTable += '</tr>';
    var twoDimData =[];
    var grandTotal=0;
	ArtAtTotal.forEach(
        function(element) {
            if ( element.pi == PI_passed && element.parent_name==art_in ) {                
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
    document.getElementById("table_content4").innerHTML = htmlTable;
 }

function teamsCapacity() {
    var PI_passed = document.getElementById("piid").value;
    var grandTotal=0;
    var rowArray = [];
    rowArray[0] = ['Agile Release Teams', 'Total Capacity for Teams (Story Points)'];
    var htmlTable = '<table id="output_table">';
    htmlTable += '<tr>';
    for (i = 0; i <= rowArray[0].length - 1; i++) {
        htmlTable += '<th>' + rowArray[0][i] + '</th>';
    }
    htmlTable += '</tr>';
    var twoDimData =[];
    var grandTotal=0;
	ArtAtTotal.forEach(
        function(element) {
            if ( element.pi == PI_passed) {                
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
    document.getElementById("table_content4").innerHTML = htmlTable;
 }

function trainArray2Col() {
    var PI_passed = document.getElementById("piid").value;
    var grandTotal=0;
    var rowArray = [];
    rowArray[0] = ['Agile Release Trains', 'Total Capacity for PI (Story Points)'];
    var htmlTable = '<table id="output_table">';
    htmlTable += '<tr>';
    for (i = 0; i <= rowArray[0].length - 1; i++) {
        htmlTable += '<th>' + rowArray[0][i] + '</th>';
    }
    htmlTable += '</tr>';
    var twoDimData =[];
    var grandTotal=0;
	PiArtTotal.forEach(
        function(element) {
            if ( element.program_increment == PI_passed ) {                
                var data3fields = [];               
                data3fields.push(element.parent_name.trim());      
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
    document.getElementById("table_content1").innerHTML = htmlTable;
 }






