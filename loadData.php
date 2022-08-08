<?php
    $servername = "localhost";
    $username="lussierp";
    $password="SWLMDB1";
    $dbname="wit_final_proj";
    $conn = mysqli_connect($servername,$username,$password,$dbname);
    if(!$conn){
        die(mysqli_error());
    }
    //path to csv file of all data from  python script
    $open = fopen('.\data\lake_data.txt','r');

    while(!feof($open)){
        $getTextLine = fgets($open);
        $explodeLine = explode(",",$getTextLine);

        list($RecordID,$Name,$Date,$SA) = $explodeLine;
        $GeoJSON = "./data/GeoJSONs/".$Name.".geojson";
        $imPath = "./data/Images/".$RecordID.".jpeg";
        $qry = "insert ignore into `swlm_db` (RecordID,Name,Date,SA,GeoJSONPath,imPath) values('".$RecordID."','".$Name."','".$Date."','".$SA."','".$GeoJSON."','".$imPath."')";
        mysqli_query($conn,$qry);
    }
    fclose($open)

?>
<?php header("Location: ./home.php"); ?>