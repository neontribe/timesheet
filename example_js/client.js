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
    var url = $("#host").val() + "/session/token";
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

    var url = $("#host").val() + "/timesheet/node/" + timesheet_id;

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
        $('#timesheet').text(JSON.stringify(data, null, 2));
      }
    });
  }

  // Read timesheets by uuid
  function fetchTimesheetsByUuid(uuid) {
    userpass = checkUsernamePassword();
    token = $('#token').text();

    var url = $("#host").val() + "/timesheet/byuuid/" + uuid + "?_format=json"

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
	      var jsonBeauty = JSON.stringify(data).trim();
//		  jsonBeauty = jsonBeauty.split('},{').join('},' + "\n" + '{');
		  $('#timesheets').text(jsonBeauty);
//		  Prism.highlightElement(document.getElementById('timesheets'));
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

  // Create or update timesheet entry
  function updateTimesheet(node, method) {
    userpass = checkUsernamePassword();
    token = $('#token').text();

    if (node["nid"] == undefined) {
      var url = $("#host").val() + "/timesheet/new";
    }
    else {
      var url = $("#host").val() + "/timesheet/update/" + node["nid"];
    }

    console.log(url);
    console.log(node);
    console.log(method);
    $.ajax({
      url: url,
      method: method,
      // contentType: "application/json; charset=utf-8",
      headers: {
        'X-CSRF-Token': token
      },
      data: node,
      username: userpass.username,
      password: userpass.password,
      success: function(data) {
    	  console.log(data);
        $('#timesheet').text(JSON.stringify(data, null, 2));
      }
    });
  }
  
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
      field_activity_type: parseInt(activity),
      field_date: date,
      field_duration: duration,
      field_issue_uuid: uuid,
      field_project: parseInt(project),
      field_user: parseInt(user),
      title:  detail
    }
    var method = "post";
    if (id != "") {
      node['nid'] =  id;
    }
    updateTimesheet(node, method);
  });

  userpass = checkUsernamePassword();
  // Populate users
  var users = $.ajax({
    url: $("#host").val() + "/timesheet/listUsers",
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
    url: $("#host").val() + "/timesheet/listProjects",
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
    url: $("#host").val() + "/timesheet/listActivities",
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
