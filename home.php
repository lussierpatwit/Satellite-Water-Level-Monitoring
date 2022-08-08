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
<body>
    <form action='getData.php' method='get'>
        Get the change in surface area for records: <br>
        Rercord ID 1: <br>
        <input type='number' name='id1'> <br>
        Record ID 2: <br>
        <input type='number' name='id2'>
        <br>
        <input type='submit' name='Submit'>
    </form>

    <form action="data/lake_data.txt" method="get">
        <button type="submit">See Raw Data</button>
    </form>

</body>
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
//$sql = "INSERT INTO `swlm_db` (`ID`, `Name`, `Date`, `Latitude`, `Longitude`, `SA`, `GeoJSON`, `imPath`) VALUES ('$x1','$x2','$x3','$x4','$x5','$x6','$x7','$x8')";

//if ($conn->query($sql) === TRUE){
//    echo "New record successfully created";
//}else{
//   echo "Error: ". $sql . "<br>" . $conn->error;
//}

$sql = "SELECT RecordID, Name, Date, SA, GeoJSONPath, imPath FROM swlm_db";
$result = $conn->query($sql);

if ($result->num_rows > 0){
    echo "<table>";
        echo"<tr><td>Record ID</td><td>Name</td><td>Date</td><td>Surface Area (km<sup>2</sup>)</td><td>GeoJSON Link</td><td>Maksed Image Link</td></tr>";
        while($row = $result->fetch_assoc()) {
            echo"<tr><td>".$row["RecordID"]."</td><td>".$row["Name"]."</td><td>".$row["Date"]."</td><td>".round($row["SA"],2)." km<sup>2</sup></td><td><a href='".$row["GeoJSONPath"]."'>GeoJSON</a></td><td><a href='".$row["imPath"]."'>Masked Image</a></td>
            </tr>";
}
    echo "</table>";
}else{
    echo "0 results";
}

$conn->close();
?>