jQuery(document).ready(function ($) {


    /**
     * Use the ajax setup to intercept the response data and filter out those 
     * terms that do not apply to our project if selected.
     */
    $.ajaxSetup({
        dataFilter: function (response, type) {
            var _project = $('#edit-timesheets-project').val();
            var project = _project.substr(0, _project.lastIndexOf('[') -1)
            if (project.trim().length == 0) {
                return response;
            }

            var data = $.parseJSON(response)
            var cleaned = [];
            $.each(data, function (key, val) {
                console.log(val);
                if (val.startsWith(project)) {
                    cleaned.push(val);
                }
            });

            return JSON.stringify(cleaned);
        }
    });

});
