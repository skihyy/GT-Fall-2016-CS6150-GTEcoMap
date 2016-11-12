/**
 * Created by Yuyang He on 2016/11/10.
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
}

/**
 * After click on filter, check about the select all option.
 * @param idOfSelectAll id of select all checkbox
 * @param nameOfChkBox checkbox name
 */
function checkSelectAll(idOfSelectAll, nameOfChkBox) {
    var checkboxs = document.getElementsByName(nameOfChkBox);
    var isAllSelected = true;
    for (var i = 0; i < checkboxs.length; i++) {
        if (!checkboxs[i].checked) {
            isAllSelected = false;
        }
    }
    document.getElementById(idOfSelectAll).checked = isAllSelected;
}
