/**
 * Created by Yuyang He on 2016/11/10.
 */
/**
 * Check box select all elements.
 * @param obj current check box
 * @param name name of check box
 * @param idOfForm id of the form
 */
function selectAll(obj, name, idOfForm) {
    var checkboxs = document.getElementsByName(name);
    for (var i = 0; i < checkboxs.length; i++) {
        checkboxs[i].checked = obj.checked;
    }

    submitForm(idOfForm);
}

/**
 * After click on filter, check about the select all option.
 * @param idOfSelectAll id of select all checkbox
 * @param nameOfChkBox checkbox name
 * @param idOfForm id of the form
 */
function checkSelectAll(idOfSelectAll, nameOfChkBox, idOfForm) {
    var checkboxs = document.getElementsByName(nameOfChkBox);
    var isAllSelected = true;
    for (var i = 0; i < checkboxs.length; i++) {
        if (!checkboxs[i].checked) {
            isAllSelected = false;
        }
    }
    document.getElementById(idOfSelectAll).checked = isAllSelected;

    submitForm(idOfForm);
}

/**
 * Submit form.
 * @param id id of form
 */
function submitForm(id)
{
    document.getElementById(id).submit();
}
