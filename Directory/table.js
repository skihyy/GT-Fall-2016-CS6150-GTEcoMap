/**
 * Created by Yuyang He on 2016/10/21.
 */
/**
 * Check box select all elements.
 * @param obj current check box
 * @param name name of check box
 */
function selectAll(obj, name) {
    var checkboxs = document.getElementsByName(name);
    for (var i = 0; i < checkboxs.length; i++) {
        checkboxs[i].checked = obj.checked;
    }

    changeInput(false);
}

/**
 * After click check box, the filter event should start.
 * @param needSubmit do the form need to be submited
 */
function changeInput(needSubmit) {
    document.getElementById("role").value = getCheckBoxResults("roleChkList");
    document.getElementById("dept").value = getCheckBoxResults("deptChkList");
    document.getElementById("area").value = getCheckBoxResults("areaChkList");

    if (needSubmit) {
        var form = document.getElementById("wholeTable");
        form.submit();
    }

    changeColorToBlue();
}

/**
 * Decide whether to blue or grey.
 */
function changeColorToBlue() {
    if ("" != document.getElementById("searchInput").value) {
        color("blue");
        return;
    }

    if (!isSelectAll("roleChkList")) {
        color("blue");
        return;
    }

    if (!isSelectAll("deptChkList")) {
        color("blue");
        return;
    }

    if (!isSelectAll("areaChkList")) {
        color("blue");
        return;
    }

    color("grey");
}

/**
 * Is a checkbox all selected.
 * @param name name of checkbox
 * @returns {boolean} whether all selected
 */
function isSelectAll(name) {
    var checkboxs = document.getElementsByName(name);
    var result = 0;
    for (var i = 0; i < checkboxs.length; i++) {
        if (checkboxs[i].checked) {
            ++result;
        }
    }

    return result == checkboxs.length;
}

/**
 * This function will change filter and reset all button to blue background.
 * @param color color change to
 */
function color(color) {
    var filterButton = document.getElementById("filterButtonSubmit");
    var resetButton = document.getElementById("resetButton");
    var resetLink = document.getElementById("resetButtonSubmit");
    var searchButton = document.getElementById("searchSubmit");

    switch (color) {
        case "blue":
            changeOneButton(filterButton, "#0000CD", false);
            changeOneButton(resetButton, "#0000CD", false);
            changeOneButton(resetLink, "#0000CD", false);
            changeOneButton(searchButton, "#0000CD", false);
            break;
        case "grey":
        default:
            changeOneButton(filterButton, "#A9A9A9", true);
            changeOneButton(resetButton, "#A9A9A9", true);
            changeOneButton(resetLink, "#A9A9A9", true);
            changeOneButton(searchButton, "#A9A9A9", true);
            break;
    }
}

/**
 * Change button to a specific status.
 * @param button button element
 * @param newColor new color
 * @param isDisabled whether it should be disabled
 */
function changeOneButton(button, newColor, isDisabled) {
    button.style.background = newColor;

    button.disabled = isDisabled;
}

/**
 * Get all check box that have been checked.
 * @param name name of the checkbox.
 * @returns {string} a list of ID
 */
function getCheckBoxResults(name) {
    var checkboxs = document.getElementsByName(name);
    var result = "";
    var isAllSelected = true;
    for (var i = 0; i < checkboxs.length; i++) {
        if (checkboxs[i].checked) {
            result += checkboxs[i].value + ",";
        }
        else {
            isAllSelected = false;
        }
    }

    var selectAll = null;
    switch (name) {
        case "roleChkList":
            selectAll = document.getElementById("roleSelect0");
            break;
        case "deptChkList":
            selectAll = document.getElementById("deptSelect0");
            break;
        case "areaChkList":
            selectAll = document.getElementById("sustainSelectAll0");
            break;
    }

    if (isAllSelected) {
        selectAll.checked = true;
    }
    else {
        selectAll.checked = false;
    }

    return result;
}

/**
 * Add new tuple.
 * @returns {boolean} currently is false
 */
function addNew() {
    alert("Current this function is being developed." +
        "\nThank you for being interested in sustainability directory.");
    return false;
}

/**
 * Change the picture of search button when focused.
 */
function searchButton() {
    document.getElementById("searchSubmit").src = "./search_blue.png";
}

/**
 * Change the picture of search button when unfocused.
 */
function searchButtonUnfocused() {
    document.getElementById("searchSubmit").src = "./search_grey.png";
}

/**
 * Drop down / collapse the list.
 * @param listId id of list
 * @param imgID id of arrow image
 */
function dropDown(listId, imgID) {
    document.getElementById(listId).classList.toggle("show");

    toggleArrowImage(imgID);
}

/**
 * Change arrow image.
 * @param imgID id of arrow image
 */
function toggleArrowImage(imgID) {
    var image = document.getElementById(imgID);

    var imgName = image.getAttribute("src");

    if ("downArrow.png" == imgName) {
        image.setAttribute("src", "upArrow.png");
    }
    else {
        image.setAttribute("src", "downArrow.png");
    }
}

/**
 * A event trigger that collapse list when click some where else on the web page.
 * @param event click event
 */
window.onclick = function (event) {
	// do not click on drop down button since this is controlled by the dropDown method above
    if (!event.target.matches('.dropbtn')) {
		// do not click on drop down list since this is controlled by the dropDown method above
        if (!event.target.matches('.content-detail')) {
			// do not click on drop down image since this is controlled by the dropDown method above
			if(!event.target.matches('.dropImg')){
				var dropdowns = document.getElementsByClassName("dropdown-content");
				for (var i = 0; i < dropdowns.length; i++) {
					if (dropdowns[i].classList.contains('show')) {
						dropdowns[i].classList.remove('show');

						changeBackImage(dropdowns[i].id);
					}
				}
			}
        }
    }
}

/**
 * Change arror picture back to up arrow.
 * @param id id of content
 */
function changeBackImage(id) {
    var imgID = "";
    switch (id) {
        case "sustainDetails":
            imgID = "areaArrow";
            break;
        case "deptDetails":
            imgID = "deptArrow";
            break;
        case "roleDetails":
            imgID = "roleArrow";
            break;
    }

    document.getElementById(imgID).setAttribute("src", "downArrow.png");
}