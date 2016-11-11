<!DOCTYPE html>
<meta charset="utf-8">
<head>
    <script src="table.js"></script>
    <link rel="stylesheet" type="text/css" href="table.css">
    <title>Sustainability Directory</title>
    <link rel="shortcut icon" type="image/x-icon" href="titleIcon.ico"/>
</head>
<!-- after the page is loaded, if some buttons were blue, they should trun to blue again. -->
<body onload="">
<?php
/**
 * Created by PhpStorm.
 * User: Yuyang He
 * Date: 2016/11/07
 * Time: 下午 04:41
 */

// not showing notices or errors
//error_reporting(E_ALL ^ E_NOTICE);

// basic MySQL connection preparation
$con = mysqli_init();

if (!$con) {
    die("Database connection initialization failed.");
}

if (!mysqli_real_connect($con, "127.0.0.1", "root", "root", "gt_eco_map", 3306)) {
    die("Connect Error: " . mysqli_connect_error());
}

$areaFilter = queryAllSustainAreas($con);
$areaFilterSelected = null;

// if one filters something
if (!is_null($_REQUEST["todo"])) {
    $areaFilterSelected = $_REQUEST["area"];
}

$projectList = queryProject($areaFilterSelected);
$peopleList = queryPropleBasedOnProjects($projectList);

printingPage($areaFilterSelected, $areaFilter);

////////////////////////////////////////////////////////////////////
/////////////////            FUNCTIONS                //////////////
////////////////////////////////////////////////////////////////////

/**
 * Printing the web pages. Of course, the map will be printed by JS.
 */
function printingPage($areaFilterSelected, $areaFilter)
{
    printingFilters($areaFilterSelected, $areaFilter);
    printingMapAreas();
}

function printingMapAreas()
{
?>
<div class="div map" id="mapDiv">

</div>
<?php
}

/**
 * Printing the filters.
 */
function printingFilters($areaFilterSelected, $areaFilter){
?>
<div class="filters div" id="filterDiv">
    <?php
    foreach ($areaFilter as $area) {
        ?>
        <div class="div area filter" id="area<?php echo $area["id"]; ?>">
            <input class="input area filter" id="input<?php echo $area["id"]; ?>"
                   value="<?php echo $area["id"]; ?>" type="checkbox" name="areaChkList"
                <?php
                if (contain($areaFilterSelected, $area["id"])) {
                    echo " checked='checked'";
                }
                ?>
            >
        </div>
        <?php
    }
    ?>
</div>
<?php
}

/**
 * @param $projectAreaID
 * @return array
 */
function queryProject($projectAreaID)
{
    $query = "SELECT * FROM project ";

    if (!is_null($projectAreaID)) {
        $query .= " WHERE ";
        foreach ($projectAreaID as $aid) {
            $query .= " area = " . $aid . " ";
        }
    }

    $query .= ";";

    $queryResult = $con->query($query);

    if (false != $queryResult) {
        $result = [];
        while ($row = $queryResult->fetch_array()) {
            array_push($result, ["id" => $row["id"], "name" => $row["name"],
                "area" => $row["area"], "uID" => explode(",", $row["uID"]),
                "link" => $row["link"]]);
        }
    }

    return $result;
}

/**
 * Given a list of projects, return all related people.
 * @param $projectList $projectList project list from DB retrieve, @see queryProject
 */
function queryPropleBasedOnProjects($projectList)
{
    $pid = getPIDFromProjects($projectList);

    if (is_null($pid)) {
        $query = "SELECT * FROM person WHERE ";

        foreach ($pid as $uid) {
            $query .= " id = " . $uid . " ";
        }

        $query .= ";";

        $queryResult = $con->query($query);

        $result = [];
        while ($row = $queryResult->fetch_array()) {
            array_push($result, ["id" => $row["id"], "deptID" => $row["deptID"],
                "name" => $row["name"], "area" => $row["area"],
                "role" => $row["role"], "phone" => $row["phone"],
                "email" => $row["email"], "pLink" => $row["pLink"],
                "coID" => $row["coID"]]);
        }
    }
}

/**
 * Given a project list from DB retrieve, return a list of all user IDs without redundant.
 * @param $projectList project list from DB retrieve, @see queryProject
 * @return array|null a list of all user IDs without redundant or null if the project is null
 */
function getPIDFromProjects($projectList)
{
    $pid = null;

    if (!is_null($projectList)) {
        $pid = [];
        foreach ($projectList as $project) {
            $uids = $project["uid"];
            foreach ($uids as $uid) {
                if (!contain($pid, $uid)) {
                    array_push($pid, $uid);
                }
            }
        }
    }

    return $pid;
}

/**
 * Check whether one element in an array has a same object as given.
 * @param $list array to be checked
 * @param $needle given string
 * @return bool true if contains
 */
function contain($list, $needle)
{
    foreach ($list as $item) {
        if ($item == $needle) {
            return true;
        }
    }
    return false;
}

/**
 * Return all area ID and names.
 * @param $con database connection
 * @return array a list of area
 */
function queryAllSustainAreas($con)
{
    $query = "SELECT id, name, color FROM area;";
    $queryResult = $con->query($query);

    $result = [];
    while ($row = $queryResult->fetch_array()) {
        array_push($result, ["id" => $row["id"], "name" => $row["name"], "color" => $row["color"]]);
    }

    return $result;
}