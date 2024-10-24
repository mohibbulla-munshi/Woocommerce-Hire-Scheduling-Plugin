jQuery(document).ready(function($) {
    $("#hire_start_date, #hire_end_date").datepicker({
        dateFormat: "yy-mm-dd",
        minDate: 0 // Prevent selecting past dates
    });
});
