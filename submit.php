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
//$x1=$_POST['id'];
//$x2=$_POST['Name'];
//$x3=$_POST['Date'];
//$x4=$_POST['Latitude'];
//$x5=$_POST['Logitude'];
//$x6=$_POST['SA'];
//$x7=$_POST['GeoJSON'];
//$x8=$_POST['imPath'];


    $servername = "localhost";
    $username="lussierp";
    $password="SWLMDB1";
    $dbname="wit_final_proj";

$conn = new mysqli($servername,$username,$password,$dbname);

if($conn->connect_error){
    die("Connection Failed: ". $conn->connect_error);
}
echo "Connection Successful ";
//$sql = "INSERT INTO `swlm_db` (`ID`, `Name`, `Date`, `Latitude`, `Longitude`, `SA`, `GeoJSON`, `imPath`) VALUES ('$x1','$x2','$x3','$x4','$x5','$x6','$x7','$x8')";

//if ($conn->query($sql) === TRUE){
//    echo "New record successfully created";
//}else{
//   echo "Error: ". $sql . "<br>" . $conn->error;
//}

$sql = "SELECT id, Name, Date, Latitude, Longitude, SA, GeoJSON, imPath FROM swlm_db";
$result = $conn->query($sql);

if ($result->num_rows > 0){
    echo "<table>";
        while($row = $result->fetch_assoc()) {
            echo"<tr><td> Name: " . $row["Name"]. "</td><td> Date: " . $row["Date"]. " </td><td> Latitude: " . $row["Latitude"]. "</td><td> Longitude: ". $row["Longitude"]. "</td><td> Surface Area: ". $row["SA"]. "</td><td> GeoJSON: ". $row["GeoJSON"]."</td><td> imPath: ".$row["imPath"] ."</td>
            </tr>";
}
    echo "</table>";
}else{
    echo "0 results";
}

$conn->close();
?>
