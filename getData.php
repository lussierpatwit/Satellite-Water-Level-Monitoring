<head>
<style>
    table{
        font-family: arial, sans-serif;
        border-collapse: collapse;
        width: 100%;
    }
    td, th {
        border: 1px solid #dddddd;
        text-align: left;
        padding: 8px;
    }
    tr:nth-child(even) {
        background-color: #dddddd;
    }
</style>
</head>
<?php
$servername = "localhost";
$username="lussierp";
$password="SWLMDB1";
$dbname="wit_final_proj";

    $conn = mysqli_connect($servername, $username,$password,$dbname);
    if(!$conn){
        die(mysqli_error());
    }
    $id1 = $_GET["id1"];
    $id2 = $_GET["id2"];
//    save query for each recordid and use the waterbody and date for the rest of the search
    $qry1 = "SELECT * FROM `swlm_db` WHERE RecordID='$id1'";
    $result1 = mysqli_query($conn,$qry1);
    $qry2 = "SELECT * FROM `swlm_db` WHERE RecordID='$id2'";
    $result2 = mysqli_query($conn,$qry2);
    $record1 = $result1->fetch_assoc();
    $waterbody = $record1["Name"];
    $date1 = $record1["Date"];
    $record2 = $result2->fetch_assoc();
    $date2 = $record2["Date"];
    $sa1 = null;
    $sa2 = null;
    $qry = "SELECT * FROM `swlm_db` WHERE Name='$waterbody' AND Date='$date1' OR Date='$date2'";
    $result = mysqli_query($conn,$qry);
    echo "Waterbody: ".$waterbody.", Between Dates: ".$date1.", and ".$date2;
    echo "<br>";
    if ($result->num_rows > 0){
        echo "<table>";
        echo"<tr><td>Record ID</td><td>Name</td><td>Date</td><td>Surface Area (km<sup>2</sup>)</td><td>GeoJSON Link</td><td> Masked Image Link</td></tr>";
        while($row = $result->fetch_assoc()) {
            echo"<tr><td>".$row["RecordID"]."</td><td>".$row["Name"]."</td><td>".$row["Date"]."</td><td>".round($row["SA"],2)." km<sup>2</sup></td><td><a href='".$row["GeoJSONPath"]."'>GeoJSON</a></td><td><a href='".$row["imPath"]."'>Masked Image</a></td>
            </tr>";
            }
        echo "</table>";
        $sa1 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SA FROM `swlm_db` WHERE Name='$waterbody' AND Date='$date1'"));
        $sa2 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SA FROM `swlm_db` WHERE Name='$waterbody' AND Date='$date2'"));  


    }else{
        echo "0 results";
    }
    $deltaSA = round($sa2["SA"]-$sa1["SA"],2);
    echo "The change in surface area for ".$waterbody." between ".$date1." and ".$date2." was ".$deltaSA." km<sup>2</sup>.";
?>  
