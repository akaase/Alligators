document.addEventListener("DOMContentLoaded", selectPIID);//after html loaded, run this function
document.addEventListener("DOMContentLoaded", trainArray2Col);

function selectPIID() {
	var piSelectorHTML = '';
    piSelectorHTML += '<select id="piid" name="piid" value="defaultPiID" onchange="trainArray2Col();this.form.submit()">\n';
        
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
//console.log(piSelectorHTML);

    document.getElementById("divPISelector").innerHTML = piSelectorHTML;
};

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
    document.getElementById("art_table").innerHTML = htmlTable;
 }