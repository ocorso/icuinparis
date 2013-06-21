jQuery(document).ready(function() {
    
    var showUIDropdownClass  = "b-style-dropdown_state_open";
    var hideUIDropdownClass  = "b-style-dropdown_state_close";
    var UIDropdown = jQuery(".b-style-dropdown");
    
    jQuery('.b-style-dropdown__button').bind('click', trigger);

    function trigger() {
        isDropDownOpen() ? closeUIDropdown() : openUIDropdown();
        return false;
    }

    function openUIDropdown() {
        jQuery('body').bind('click', closeUIDropdown)
        UIDropdown.removeClass(hideUIDropdownClass)
        UIDropdown.addClass(showUIDropdownClass)
    }

    function closeUIDropdown() {
        jQuery('body').unbind('click')
        UIDropdown.addClass(hideUIDropdownClass)
        UIDropdown.removeClass(showUIDropdownClass)
    }

    function isDropDownOpen() {
        return UIDropdown.hasClass(showUIDropdownClass) || false;
    }

 });