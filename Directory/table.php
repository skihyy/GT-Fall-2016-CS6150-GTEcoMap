<!--
This one has only one-level filter and works well.
-->

<!DOCTYPE html>
<meta charset="utf-8">
<head>
    <script src="table.js"></script>
    <link rel="stylesheet" type="text/css" href="table.css">
    <title>Sustainability Directory</title>
    <link rel="shortcut icon" type="image/x-icon" href="titleIcon.ico"/>
</head>
<!-- after the page is loaded, if some buttons were blue, they should trun to blue again. -->
<body onload="changeColorToBlue()">
<?php
// not showing notices or errors
error_reporting(E_ALL ^ E_NOTICE);

// basic MySQL connection preparation
$con = mysqli_init();

if (!$con) {
    die("Database connection initialization failed.");
}

if (!mysqli_real_connect($con, "127.0.0.1", "root", "root", "gt_eco_map", 3306)) {
    die("Connect Error: " . mysqli_connect_error());
}

// start to handle query and retrieve data from the DB
$basicQUery = "SELECT * FROM ";
$whereQuery = "SELECT * FROM person";
$searchCondition = "";
$whereCondition = "";
$totalTuples = 0;
$tuplePerPage = 10;
$currentPage = 1;
$totalPage = 0;
$search = "";
$areaFilter = "";
$deptFilter = "";
$roleFilter = "";

$deptList = queryAllDepartment($con);
$sustainList = queryAllSustainAreas($con);

//$anyOperationAfterLastTime = false; // for changing color

if (!is_null($_REQUEST["todo"])) {
    switch ($_REQUEST["todo"]) {
        case "frommap":
            $whereQuery .= " WHERE id = " . $_REQUEST["id"];
            $areaFilter = "all";
            $deptFilter = "all";
            $roleFilter = "all";
            break;
        default:
            $search = $_REQUEST["search"];
            $areaFilter = explode(",", $_REQUEST["area"]);
            $deptFilter = explode(",", $_REQUEST["dept"]);
            $roleFilter = explode(",", $_REQUEST["role"]);

            $currentPage = 1; // need to change
            $searchCondition = getSearchCondition($search, $con);
            $whereCondition = getWhereCondition($areaFilter,
                $deptFilter, $roleFilter, $sustainList, $deptList, $con);
            $whereQuery .= $whereCondition;

        //$anyOperationAfterLastTime = true;
    }
} // if it's null, then it can be the first time go to this page
else {
    // filters showing / selecting all
    $areaFilter = "all";
    $deptFilter = "all";
    $roleFilter = "all";
}

$orderQuery = "ORDER BY name ASC";

$finalQuery = $basicQUery . " (" . $whereQuery . ") AS temp " . $searchCondition .
    " " . $orderQuery . ";";

//print_r("<br><br>" . $finalQuery . "<br><br>");

// query information
$peopleList = queryPerson($finalQuery, $con);

// printing the table
printTable($peopleList, $deptList, $sustainList, $search, $areaFilter, $deptFilter, $roleFilter);

// close database connection
$con->close();

?>
<!-- if need to change the color of button
<input id="anyOperationAfterLastTime" hidden="hidden" value="<?php //echo $anyOperationAfterLastTime; ?>">
-->
<?php

//////////////////////////////////////////////////////////////////////////
//                      END OF THE PAGE
//////////////////////////////////////////////////////////////////////////
/**
 * Return query for search box. This function should also concern about sustainability areas in projects.
 * @param $search search box input
 * @param $con database connection
 * @return string query for search box
 */
function getSearchCondition($search, $con)
{
    if ("" == $search) {
        return "";
    }

    $result = " WHERE name LIKE '%" . $search . "%' OR phone LIKE '%" . $search .
        "%' OR email LIKE '%" . $search . "%'" .
        "OR pLink LIKE '%" . $search . "%'";


    $uid = getUIDInSearch($search, $con);
    if (0 < count($uid)) {
        foreach ($uid as $id) {
            if ("" != $id) {
                $result .= " OR id = " . $id;
            }
        }
    }

    return $result;
}

/**
 * If one types something in search box, it should not only match something in person's details,
 * but also her projects' details. This function will return author IDs if search matches any project's
 * fields.
 * @param $search search string
 * @param $con database connection
 * @return array authors' ID list
 */
function getUIDInSearch($search, $con)
{
    $query = "SELECT uID FROM project ";
    if ("" != $search) {
        $query .= " WHERE name LIKE '%" . $search . "%'
                OR link LIKE '%" . $search . "%';";
    }

    $queryResult = $con->query($query);

    $result = [];
    while ($row = $queryResult->fetch_array()) {
        $tmpIDs = explode(",", $row["uID"]);

        if (0 < count($tmpIDs)) {
            foreach ($tmpIDs as $tmpID) {
                if (!contain($result, $tmpID)) {
                    array_push($result, $tmpID);
                }
            }
        }
    }

    return $result;
}

/**
 * Generate search query condition based on conditions.
 * @param $areaFilter sustainability area filters
 * @param $deptFilter department filters
 * @param $roleFilter role filters
 * @param $sustainList sustainability list
 * @param $deptList department list
 * @param $con database connection
 * @return string search query
 */
function getWhereCondition($areaFilter, $deptFilter, $roleFilter, $sustainList, $deptList, $con)
{
    $result = "";

    // filter out unselected area of a person
    $areaNegation = getNegation($areaFilter, $sustainList);
    if (0 < count($areaNegation)) {
        foreach ($areaNegation as $id) {
            if ("" != $id) {
                if ("" == $result) {
                    $result .= " WHERE ";
                }
                if (" WHERE " == $result) {
                    $result .= " area <> " . $id;
                    continue;
                }
                $result .= " AND area <> " . $id;
            }
        }
    }

    // also need to filter out person who only has one project that the area matches this
    //$uID = getUnqualifiedUIDFromSustainArea($con, $areaNegation);

    $deptNagation = getNegation($deptFilter, $deptList);
    if (0 < count($deptNagation)) {
        foreach ($deptNagation as $id) {
            if ("" != $id) {
                if ("" == $result) {
                    $result .= " WHERE ";
                }
                if (" WHERE " == $result) {
                    $result .= " deptID <> " . $id;
                    continue;
                }
                $result .= " AND deptID <> " . $id;
            }
        }
    }

    $roleNegation = getRoleNegation($roleFilter);
    if (0 < count($roleNegation)) {
        foreach ($roleNegation as $id) {
            if ("" != $id) {
                if ("" == $result) {
                    $result .= " WHERE ";
                }
                if (" WHERE " == $result) {
                    $result .= " role <> " . $id;
                    continue;
                }
                $result .= " AND role <> " . $id;
            }
        }
    }

    return $result;
}

/**
 * Given a list, find IDs in database that is not in the list.
 * @param $smallList a list of areas
 * @param $allList all areas
 * @return array IDs in database that is not in the list
 */
function getNegation($smallList, $allList)
{
    $result = [];

    foreach ($allList as $item) {
        if (!contain($smallList, $item["id"])) {
            array_push($result, $item["id"]);
        }
    }

    return $result;
}

/**
 * Given a list of roles, find IDs in database that is not in the list.
 * @param $roleFilter a list of roles
 * @return array IDs in database that is not in the list
 */
function getRoleNegation($roleFilter)
{
    $result = [];

    for ($i = 1; $i < 4; ++$i) {
        if (!contain($roleFilter, $i)) {
            array_push($result, $i);
        }
    }

    return $result;
}

/**
 * Print search bar.
 */
function printSearchBar($search)
{
    ?>
    <!-- search -->
    <div class="div searchBar input">
        <input id="searchInput" class="input search" type="text" name="search"
               value="<?php echo $search; ?>" oninput="changeColorToBlue()"
               placeholder="Type something ..." onfocus="searchButton()"
               onblur="searchButtonUnfocused()">
        <input id="searchSubmit" type="image" src="search_grey.png" class="input search submit"
               onclick="changeInput()" height="30" width="30">
    </div>
    <?php
}

/**
 * Printing the table.
 * @param $peopleList a list of persons. @see queryPerson for details.
 * @param $deptList list of departments. @see getAllDepartments for details.
 * @param $sustainList list of sustainability areas. @see getAllSustainArea for details.
 * @param $search search info
 * @param $areaFilter sustainability area filter chosen
 * @param $deptFilter department filter chosen
 * @param $roleFilter role filter chosen
 */
function printTable($peopleList, $deptList, $sustainList, $search, $areaFilter, $deptFilter, $roleFilter)
{
    ?>
    <!-- add -->
    <div id="addNewPerson" class="div addNew">
        <span class="span addNew">
            <form id="addNew" class="form addNew" method="get" action="" onsubmit="return addNew()">
                <input id="addNewImage" class="button image addNew"
                       type="image" src="add.png" height="16" width="16">
                <input id="addNewButton" class="button addNew submit"
                       type="submit" value="Add Yourself to the Directory">
            </form>
        </span>
    </div>

    <!-- title -->
    <div id="tableTitle" class="div title">
        <p id="tableTitleText" class="p title">Campus Sustainability Directory</p>
    </div>

    <!-- discription -->
    <div id="tableDescription" class="div description">
        <p id="tableDescriptionText1" class="p description">
            Campus sustainability directory consistes of various stakeholders on Georgia Tech's campus,
            who are involved in different projects and activities related to sustainability.
            You are more than welcome to search for people or projects of your interest,
            as well as add yourself in order to increase visibility of your work.
        </p>
    </div>

    <!-- reset
    <form id="resetTable" class="resetForm" method="get" action="">
        <input id="resetSubmit" type="submit" class="input reset submit" value="Reset All">
    </form>-->

    <!-- form is used for filtering and page issue -->
    <form id="wholeTable" class="tableForm" method="get" action="">
        <input id="todo" type='hidden' name='todo' value='table'/>

        <?php
        //print search bar
        printSearchBar($search);
        ?>

        <!-- filters & pages -->
        <?php printFunctionArea($deptList, $sustainList, $areaFilter, $deptFilter, $roleFilter); ?>

        <?php
        if (0 != count($peopleList)) {
            ?>
            <!-- table -->
            <table class="table" id="mainTable" border="1">
                <?php printTableHeader(); ?>

                <!-- table body -->
                <?php printTableBody($peopleList, $deptList, $sustainList, $areaFilter); ?>

                <?php //printTableHeader(); ?>
            </table>
            <?php
        } else {
            ?>
            <div class="div table empty">
                <p>We apologize, but we couldn't find results, which fit your filtering...<br>
                    Reset, and try again.</p>
            </div>
            <?php
        }
        ?>
    </form>
    <?php
}

/**
 * Print filters and page selection.
 * @param $deptList list of departments. @see getAllDepartments for details.
 * @param $sustainList list of sustainability areas. @see getAllSustainArea for details.
 * @param $areaFilter sustainability area filter chosen
 * @param $deptFilter department filter chosen
 * @param $roleFilter role filter chosen
 */
function printFunctionArea($deptList, $sustainList, $areaFilter, $deptFilter, $roleFilter)
{
    ?>
    <div class="div functionArea main" id="mainFunctionArea">
        <div class="div functionArea susArea filter dropdown" id="susAreaFilter">
            <?php printSustainAreaFilter($sustainList, $areaFilter);
            ?>
        </div>
        <div class="div functionArea dept filter dropdown" id="deptFilter">
            <?php printDeptFilter($deptList, $deptFilter); ?>
        </div>
        <div class="div functionArea role filter dropdown" id="roleFilter">
            <?php printRoleFilter($roleFilter);
            ?>
        </div>
        <div class="div functionArea button" id="filterButton">
            <input type="submit" class="input button" id="filterButtonSubmit" value="Filter"
                   onclick="changeInput(true)">
        </div>
        <div class="div functionArea button reset" id="resetButton">
            <a class="input button href" id="resetButtonSubmit"
               href="table.php" style="vertical-align: middle; line-height: 30px; text-decoration:none">
                Reset All
            </a>
        </div>
    </div>
    <!--
                <div class="input button href" id="resetAllText">
                    Reset All
                </div>
                -->
    <?php
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
 * Print role filter.
 * @param $roleFilter role filter chosen
 */
function printRoleFilter($roleFilter)
{
    ?>
    <input id="role" name="role" type="hidden" value="">
    <div class="div functionArea role filter name dropbtn" onclick="dropDown('roleDetails', 'roleArrow')">
        Role
        <span class="span functionArea role filter name"></span>
        <img id="roleArrow" class="img functionArea role filter name dropImg"
             src="downArrow.png" width="22" height="22">
    </div>
    <!-- printing select all -->
    <div class="dropdown-content" id="roleDetails">
        <div class=" div functionArea role filter content-detail" id="roleSelectAll">
            <input class="input filter selectAll content-detail" id="roleSelect0" value="" type="checkbox"
                   onclick="selectAll(this, 'roleChkList')"
                <?php
                if ("all" === $roleFilter || 4 == count($roleFilter)) {
                    echo " checked='checked'";
                }
                ?>
            >
            <label for="roleSelect0" class="content-detail">All</label>
        </div>

        <div class="div functionArea role filter content-detail" id="roleSelectS">
            <input class="input filter select content-detail" id="roleSelect1" value="1" type="checkbox"
                   name="roleChkList"
                   onclick="changeInput(false)"
                <?php
                if ("all" === $roleFilter || contain($roleFilter, "1")) {
                    echo " checked='checked'";
                }
                ?>
            >
            <label for="roleSelect1" class="content-detail">Student</label>
        </div>
        <div class="div functionArea role filter content-detail" id="roleSelectF">
            <input class="input filter select content-detail" id="roleSelect2" value="2" type="checkbox"
                   name="roleChkList"
                   onclick="changeInput(false)"
                <?php
                if ("all" === $roleFilter || contain($roleFilter, "2")) {
                    echo " checked='checked'";
                }
                ?>
            >
            <label for="roleSelect2" class="content-detail">Faculty</label>
        </div>
        <div class="div functionArea role filter content-detail" id="roleSelectSt">
            <input class="input filter select content-detail" id="roleSelect3" value="3" type="checkbox"
                   name="roleChkList"
                   onclick="changeInput(false)"
                <?php
                if ("all" === $roleFilter || contain($roleFilter, "3")) {
                    echo " checked='checked'";
                }
                ?>
            >
            <label for="roleSelect3" class="content-detail">Staff</label>
        </div>
    </div>
    <?php
}

/**
 * Print department filter.
 * @param $deptList list of departments. @see getAllDepartments for details.
 * @param $deptFilter department chosen filter
 */
function printDeptFilter($deptList, $deptFilter)
{
    ?>
    <div class="div functionArea role filter name dropbtn" onclick="dropDown('deptDetails', 'deptArrow')">
        College
        <span class="span functionArea role filter name"></span>
        <img id="deptArrow" class="img functionArea role filter name dropImg"
             src="downArrow.png" width="22" height="22">
    </div>
    <input id="dept" name="dept" type="hidden" value="">

    <div class="dropdown-content" id="deptDetails">
        <!-- printing select all -->
        <div class="div functionArea dept filter content-detail" id="deptSelectAll">
            <input class="input filter selectAll content-detail" id="deptSelect0" value="" type="checkbox"
                   onclick="selectAll(this, 'deptChkList')"
                <?php
                if ("all" === $deptFilter || count($deptList) == count($deptFilter) - 1) {
                    echo " checked='checked'";
                }
                ?>
            >
            <label for="deptSelect0" class="content-detail">All</label>
        </div>
        <?php
        foreach ($deptList as $dept) {
            ?>
            <div class="div functionArea dept filter content-detail" id="<?php echo "dept" . $dept["id"]; ?>">
                <input class="input filter select content-detail" id="<?php echo "deptSelect" . $dept["id"]; ?>"
                       value="<?php echo $dept["id"]; ?>" type="checkbox" name="deptChkList"
                       onclick="changeInput(false)"
                    <?php
                    if ("all" === $deptFilter || contain($deptFilter, $dept["id"])) {
                        echo " checked='checked' ";
                    }
                    ?>
                >
                <?php echo "<label class='content-detail' for='deptSelect" . $dept["id"] . "'>" . $dept["name"] . "</label>"; ?>
            </div>
            <?php
        }
        ?>
    </div>
    <?php
}

/**
 * Print sustainability area filter.
 * @param $sustainList list of areas. @see getAllSustainArea for details.
 * @param $areaFilter area filter
 */
function printSustainAreaFilter($sustainList, $areaFilter)
{
    ?>
    <div class="div functionArea role filter name dropbtn" onclick="dropDown('sustainDetails', 'areaArrow')">
        Sustainability Area
        <span class="span functionArea role filter name"></span>
        <img id="areaArrow" class="img functionArea role filter name dropImg"
             src="downArrow.png" width="22" height="22">
    </div>
    <input id="area" name="area" type="hidden" value="">

    <div class="dropdown-content" id="sustainDetails">

        <!-- printing select all -->
        <div class="div functionArea sustain filter content-detail" id="sustainSelectAll">
            <input class="input filter selectAll content-detail" id="sustainSelectAll0" value="" type="checkbox"
                   onclick="selectAll(this, 'areaChkList')"
                <?php
                if ("all" === $areaFilter || count($sustainList) == count($areaFilter) - 1) {
                    echo " checked='checked'";
                }
                ?>
            >
            <label for="sustainSelectAll0" class="content-detail">All</label>
        </div>
        <?php
        foreach ($sustainList as $area) {
            ?>
            <div class="div functionArea sustain filter content-detail" id="<?php echo "sustain" . $area["id"]; ?>">
                <input class="input filter select content-detail" id="<?php echo "sustainSelect" . $area["id"]; ?>"
                       value="<?php echo $area["id"]; ?>" type="checkbox" name="areaChkList"
                       onclick="changeInput()"
                    <?php
                    if ("all" === $areaFilter || contain($areaFilter, $area["id"])) {
                        echo " checked='checked'";
                    }
                    ?>
                >
                <?php echo "<label class='content-detail' for='sustainSelect" . $area["id"] . "'>" . $area["name"] . "</label>"; ?>
            </div>
            <?php
        }
        ?>
    </div>
    <?php
}

/**
 * Query one person based on the given query from database.
 * It will also call queryProject to add all related project into this person.
 * @param $query the finished SQL query
 * @param $con database connection, must be alive
 * @return persons a list of array. Every element is a person, containing id, department ID,
 *          name, sustainability area, role, phone number, email, personal web page link,
 *          and a list of project she has, if applicable. For project @see queryAllProject for details.
 */
function queryPerson($query, $con)
{
    $queryResult = $con->query($query);

    $result = [];
    while ($row = $queryResult->fetch_array()) {
        array_push($result, ["id" => $row["id"], "deptID" => $row["deptID"],
            "name" => $row["name"], "area" => $row["area"],
            "role" => $row["role"], "phone" => $row["phone"],
            "email" => $row["email"], "pLink" => $row["pLink"]]);
    }

    return queryAllProject($result, $con);
}

/**
 * Query all projects from a list of person. And add those projects to the proper person.
 * @param $result a list of array. Every element is a person. @see queryPerson for details.
 * @param $con database connection, must be alive
 * @return persons a list of array. Every element is a person, containing id, department ID,
 *          name, sustainability area, role, phone number, email, personal web page link,
 *          and a list of project she has, if applicable.
 */
function queryAllProject($result, $con)
{
    foreach ($result as $id => $person) {
        $result[$id]["projects"] = queryProject($person["id"], $con);
    }

    return $result;
}

/**
 * Query one person's related all projects.
 * @param $id the person's id
 * @param $con database connection, must be alive
 * @return array projects which is a list of array.
 *          Every element is a project, containing id, name,
 *          users' IDs, sustainability area, link, if applicable.
 */
function queryProject($id, $con)
{
    $query = "SELECT * FROM project WHERE uID LIKE '%" . $id . "%';";
    $queryResult = $con->query($query);

    if (false != $queryResult) {
        $result = [];
        while ($row = $queryResult->fetch_array()) {
            array_push($result, ["id" => $row["id"], "name" => $row["name"],
                "area" => $row["area"], "uID" => $row["uID"],
                "link" => $row["link"]]);
        }
    }

    return $result;
}

/**
 * Print the header of the table.
 */
function printTableHeader()
{
    ?>
    <thead id="tableHead">
    <tr id="tableHeadRow" class="tableRow, tableHead">
        <th class="tableHead columnName">Name</th>
        <th class="tableHead columnArea">Sustainability Area</th>
        <th class="tableHead columnProject">Project Name</th>
        <th class="tableHead columnDept">College / Department</th>
        <th class="tableHead columnRole">Role</th>
        <th class="tableHead columnContact">Contact Info</th>
    </tr>
    </thead>
    <?php
}

/**
 * Print the body of the table.
 * @param $peopleList The list of people. @see queryPerson for details.
 * @param $deptList list of departments. @see getAllDepartments for details.
 * @param $sustainList list of sustainability areas. @see getAllSustainArea for details.
 * @param $areaFilter sustainability filter currently shown
 */
function printTableBody($peopleList, $deptList, $sustainList, $areaFilter)
{
    echo "<tbody>";

    foreach ($peopleList as $person) {
        ?>
        <tr>
            <td class="tableBody columnName"><?php echo $person["name"]; ?></td>
            <td class="tableBody columnArea">
                <?php
                echo getSustainArea($person["projects"], $person["area"], $sustainList, $areaFilter);
                ?>
            </td>
            <td class="tableBody columnProject"><?php echo getProject($person["projects"], $areaFilter); ?></td>
            <td class="tableBody columnDept"><?php echo getDepartment($person["deptID"], $deptList); ?></td>
            <td class="tableBody columnRole"><?php echo getRole($person["role"]); ?></td>
            <td class="tableBody columnContact">
                <?php echo getContact($person["phone"], $person["email"], $person["pLink"]); ?>
            </td>
        </tr>
        <?php
    }

    echo "</tbody>";
}

/**
 * Return the formatted project names and links, if applicable.
 * @param $projects project list, @see queryProject for details
 * @param $areaFilter sustainability filter currently shown
 * @return string the formatted project detail
 */
function getProject($projects, $areaFilter)
{
    if (0 == count($projects)) {
        return "None";
    }

    $result = "";
    foreach ($projects as $project) {
        if ("all" == $areaFilter || contain($areaFilter, $project["area"])) {
            if (null != $project["link"]) {
                $result .= "<a target='_blank' href='http://" . $project["link"] . "'>" . $project["name"] . "</a>";
            } else {
                $result .= $project["name"];
            }
            $result .= "<br>";
        }
    }

    if ("" == $result) {
        $result .= "None";
    }

    return $result;
}

/**
 * Return sustainability areas of a person's list of projects.
 * If she does not have any, her own sustainability area will be shown.
 * @param $projects project list, @see queryProject for details.
 * @param $areaID area ID of one person
 * @param $sustainList sustainability area list, @see queryProject for details.
 * @param $areaFilter sustainability filter currently shown
 * @return string sustainability areas
 */
function getSustainArea($projects, $areaID, $sustainList, $areaFilter)
{
    $result = "";

    foreach ($projects as $project) {
        foreach ($sustainList as $area) {
            if ("all" == $areaFilter || contain($areaFilter, $area["id"])) {
                if ($project["area"] == $area["id"]) {
                    $result .= "<div class='colorBlock' style='float: left; background-color: "
                        . $area["color"] . "'>&nbsp&nbsp&nbsp</div>&nbsp&nbsp" . $area["name"] . "<br>";
                    break;
                }
            }
        }
    }

    if ("" == $result) {
        foreach ($sustainList as $area) {
            if ($area["id"] == $areaID) {
                return "<div class='colorBlock' style='float: left; background-color: " .
                    $area["color"] . "'>&nbsp&nbsp&nbsp</div>&nbsp&nbsp" . $area["name"];
            }
        }
        return "Unknown";
    }

    return $result;
}

/**
 * Return the contact information after formatting.
 * The tricky part is when some information is null or empty.
 * @param $phone phone
 * @param $email email
 * @param $link personal web site
 * @return string the contact information after formatting
 */
function getContact($phone, $email, $link)
{
    $len0 = 0;
    $len1 = 0;
    $len2 = 0;

    if (null != $phone) {
        $len0 = strlen($phone);
    }

    if (null != $email) {
        $len1 = strlen($email);
        $email = "<a href='mailto:" . $email . "'>" . $email . "</a>";
    }

    if (null != $link) {
        $len2 = strlen($link);
        $link = "<a target='_blank' href='http://" . $link . "'>" . $link . "</a>";
    }

    if (0 != $len0) {
        if (0 != $len1) {
            if (0 != $len2) {
                return $phone . "<br/>" . $email . "<br/>" . $link;
            } else {
                return $phone . "<br/>" . $email;
            }
        } else {
            if (0 != $len2) {
                return $phone . "<br/>" . $link;
            } else {
                return $phone;
            }
        }
    } else {
        if (0 != $len1) {
            if (0 != $len2) {
                return $email . "<br/>" . $link;
            } else {
                return $email;
            }
        } else {
            if (0 != $len2) {
                return $link;
            } else {
                return "None";
            }
        }
    }
}

/**
 * Return all college ID and names.
 * @param $con database connection
 * @return array a list of departments
 */
function queryAllDepartment($con)
{
    // if parent = 0, then it is the college, not department
    $query = "SELECT id, name FROM department;";
    $queryResult = $con->query($query);

    $result = [];
    while ($row = $queryResult->fetch_array()) {
        array_push($result, ["id" => $row["id"], "name" => $row["name"]]);
    }

    return $result;
}

/**
 * Get all department ID and names.
 * @param $con database connection
 * @param $id parent ID
 * @return array an array of department
 */
function getSubDepartment($con, $id)
{
    $query = "SELECT id, name FROM department WHERE parent = " . $id . ";";
    $queryResult = $con->query($query);

    $result = [];
    while ($row = $queryResult->fetch_array()) {
        array_push($result, ["id" => $row["id"], "name" => $row["name"]]);
    }

    return $result;
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

/**
 * Return one's department based on her department ID.
 * @param $deptID department ID
 * @param $deptList list of departments. @see getAllDepartments for details.
 * @return department name
 */
function getDepartment($deptID, $deptList)
{
    foreach ($deptList as $dept) {
        if ($dept["id"] == $deptID) {
            return $dept["name"];
        }
    }

    return "Unknown";
}

/**
 * Return one's role based on her role ID.
 * @param $roleID role ID
 * @return string role
 */
function getRole($roleID)
{
    switch ($roleID) {
        case 1:
        case "1":
            return "Student";
        case 2:
        case "2":
            return "Faculty";
        case 3:
        case "3":
            return "Staff";
        // if nothing found
        default:
            return "Unknown";
    }
}

?>
</body>
</html>