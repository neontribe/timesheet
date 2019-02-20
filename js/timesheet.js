jQuery(document).ready(function ($) {

    function validateProject() {
        // Which is better, the jQuery or native function to get value?
        var customerId = $('#edit-timesheets-customer').val();
        var projectIds = drupalSettings.timesheet.tree[customerId];

        $('#edit-timesheets-project').empty();

        $.each(projectIds, function(projectId) {
            var projectName = drupalSettings.timesheet.project[projectId];
            $('#edit-timesheets-project').append('<option value="' + projectId + '">' + projectName + ' (' + projectId + ')</option>');
        });
    }

    function validateActivity() {
        // Which is better, the jQuery or native function to get value?
        var customerId = $('#edit-timesheets-customer').val();
        var projectId = $('#edit-timesheets-project').val();
        var activityIds = drupalSettings.timesheet.tree[customerId][projectId];

        $('#edit-timesheets-activity-type').empty();

        $.each(activityIds, function(key, activityId) {
            var activityName = drupalSettings.timesheet.activity_types[activityId];
            $('#edit-timesheets-activity-type').append('<option value="' + activityId + '">' + activityName + ' (' + activityId + ')</option>');
        });
    }

    $('#edit-timesheets-customer').on('change', function(event) {
        validateProject();
    });

    $('#edit-timesheets-project').on('change', function(event) {
        validateActivity();
    });

    validateProject();
    validateActivity();
});
