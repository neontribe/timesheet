$( document ).ready(function() {

  // Check username, password
  function checkUsernamePassword() {
    var username = $("#username").val();
    var password = $("#password").val();
    if (! (username || password || token)) {
      alert("Username and password are required.");
    }

    var token = $('#token').text();
    if (!token) {
      fetchToken(username, password);
    }

    return { username: username, password: password };
  }

  // Fetch Token
  function fetchToken(username, password) {
    var url = "http://127.0.0.1:8888/session/token";
    $.ajax({
      url: url,
      contentType: "application/json; charset=utf-8",
      username: username,
      password: password,
      success: function(data) {
        $('#token').text(data);
      }
    });
  }

  // Read single timesheet entry by id
  function fetchTimesheet(timesheet_id) {
    userpass = checkUsernamePassword();
    token = $('#token').text();

    var url = "http://127.0.0.1:8888/node/" + timesheet_id + "?_format=json"

    $.ajax({
      url: url,
      contentType: "application/json; charset=utf-8",
      headers: {
        'Content-Type': 'application/hal+json',
        'X-CSRF-Token': token
      },
      username: userpass.username,
      password: userpass.password,
      success: function(data) {
        $('#timesheet').text("<pre>" + JSON.stringify(data, null, 2) + "</pre>");
      }
    });
  }

  // Create or update timesheet entry
  function updateTimesheet(node, method) {
    userpass = checkUsernamePassword();
    token = $('#token').text();

    if (method == 'POST') {
      var url = "http://127.0.0.1:8888/node?_format=json"
    }
    else {
      var url = "http://127.0.0.1:8888/node/" + node["nid"][0]["value"] + "?_format=json"
    }

    $.ajax({
      url: url,
      method: method,
      contentType: "application/json; charset=utf-8",
      headers: {
        'X-CSRF-Token': token
      },
      data: JSON.stringify(node),
      username: userpass.username,
      password: userpass.password,
      success: function(data) {
        $('#timesheet').text("<pre>" + JSON.stringify(data, null, 2) + "</pre>");
      }
    });
  }

  // Read timesheets by uuid
  function fetchTimesheetsByUuid(uuid) {
    userpass = checkUsernamePassword();
    token = $('#token').text();

    var url = "http://127.0.0.1:8888/timesheet/byuuid/" + uuid + "?_format=json"

    $.ajax({
      url: url,
      contentType: "application/json; charset=utf-8",
      headers: {
        'Content-Type': 'application/hal+json',
        'X-CSRF-Token': token
      },
      username: userpass.username,
      password: userpass.password,
      success: function(data) {
        $('#timesheets').text("<pre>" + JSON.stringify(data, null, 2) + "</pre>");
      }
    });
  }

  // Delete timesheet

  // Attach to get CRSF Token
  $('#token_button').on('click', function () {
    var username = $("#username").val();
    var password = $("#password").val();
    fetchToken(username, password);
  });

  // Attach to fetch timesheet button
  $('#timesheet_button').on('click', function () {
    var timesheet_id = $("#timesheet_id").val();
    if (! timesheet_id) {
      alert("A timesheet id is required.");
    }

    fetchTimesheet(timesheet_id);
  });

  // Attach to fetch by issue button
  $('#timesheets_button').on('click', function () {
    var issue_uuid = $("#issue_uuid").val();
    if (! issue_uuid) {
      alert("A issue_uuid is required.");
    }

    fetchTimesheetsByUuid(issue_uuid);
  });

  // Attach to create/update button
  $('#update_button').on('click', function () {
    var id = $("#id").val();
    var detail = $("#detail").val();
    var user = $("#user").val();
    var project = $("#project").val();
    var date = $("#date").val();
    var activity = $("#activity").val();
    var duration = $("#duration").val();
    var uuid = $("#uuid").val();

    var node = {
      "field_activity_type": [
        {
          "target_id": parseInt(activity),
          "target_type": "taxonomy_term",
          "url": "/taxonomy/term/" + activity
        }
      ],
      "field_date": [
        {
          "value": date
        }
      ],
      "field_duration": [{
        "value": "PT" + Math.floor(duration/60) + "H" + (duration % 60) + "M"
      }],
      "field_duration_minutes_": [
        {
          "value": duration
        }
      ],
      "field_issue_uuid": [
        {
          "value": uuid
        }
      ],
      "field_project": [
        {
          "target_id": parseInt(project),
          "target_type": "node",
          "url": "/node/" + project
        }
      ],
      "field_user": [
        {
          "target_id": parseInt(user),
          "target_type": "user",
          "url": "/user/" + user
        }
      ],
      "title": [
        {
          "value": detail
        }
      ],
      "type": [
        {
          "target_id": "time_sheet_entry",
          "target_type": "node_type",
        }
      ]
    };
    console.log(node);
    var method = "POST";
    if (id) {
      method = "PATCH";
      node['nid'] = [
        { "value": id }
      ];
    }
    updateTimesheet(node, method);
  });

  userpass = checkUsernamePassword();
  // Populate users
  var users = $.ajax({
    url: "http://127.0.0.1:8888/timesheet/listUsers",
    contentType: "application/json; charset=utf-8",
    headers: {
      'Content-Type': 'application/hal+json',
      'X-CSRF-Token': token
    },
    username: userpass.username,
    password: userpass.password,
    success: function(data) {
      html = "";
      $.each(data, function (key, thing) {
        html += "<option value='" + key + "'>" + thing + "</option>";
      });
      $('#user').html(html);
    }
  });

  // Populate projects
  var projects = $.ajax({
    url: "http://127.0.0.1:8888/timesheet/listProjects",
    contentType: "application/json; charset=utf-8",
    headers: {
      'Content-Type': 'application/hal+json',
      'X-CSRF-Token': token
    },
    username: userpass.username,
    password: userpass.password,
    success: function(data) {
      html = "";
      $.each(data, function (key, thing) {
        html += "<option value='" + key + "'>" + thing + "</option>";
      });
      $('#project').html(html);
    }
  });

  // Populate activities
  var activities = $.ajax({
    url: "http://127.0.0.1:8888/timesheet/listActivities",
    contentType: "application/json; charset=utf-8",
    headers: {
      'Content-Type': 'application/hal+json',
      'X-CSRF-Token': token
    },
    username: userpass.username,
    password: userpass.password,
    success: function(data) {
      html = "";
      $.each(data, function (key, thing) {
        html += "<option value='" + key + "'>" + thing + "</option>";
      });
      $('#activity').html(html);
    }
  });
});
