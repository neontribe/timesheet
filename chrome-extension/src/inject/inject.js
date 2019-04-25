// TODO: is this the best way to check for page change 
var pathname = false;
var currentBoard = false;
var currentIssue = false;

// Watch for new card shown
var readyStateCheckInterval = setInterval(function () { 
	if (document.readyState === "complete") { 

		if (pathname != window.location.pathname) { 
			// issue/board has changed 
			pathname = window.location.pathname; 
			if (pathname.startsWith("/c")) { 
				// It's a new card 
				$('.window-sidebar').append("<div class='window-module u-clearfix' id='timesheet_target'></div>");
				currentIssue = pathname.substring(3, pathname.indexOf("/", 3));
        chrome.runtime.sendMessage({task: "cardChanged", uuid: currentIssue});
			} else if ("/b") {
				currentBoard = pathname.substring(3);
        chrome.runtime.sendMessage({task: "boardChanged", uuid: currentBoard});
      }
		} 

	} 
}, 1000); 

// Get message from backend to render issues
chrome.runtime.onMessage.addListener(
  function(request, sender, sendResponse) {
    if (request.action == "timesheets") {
      payload = JSON.parse(request.payload);
      entries = payload.data;
      html = "<h3>TIMESHEETS</h3>";
      $.each(entries, function(index, entry) {
        id = "timesheet_entry_" + entry.id;
        html += "<div class='timesheet_entry' id='" + id + "' data-entry='" + JSON.stringify(entry) + "'>";
        html += "<div class='timesheet_activity_type'>";
        html += entry.activity_type;
        html += "</div>";

        html += "<div class='timesheet_meta'>";
        html += entry.duration + " mins, " +  entry.date;
        html += "</div>";

        html += "<div class='timesheet_title'>";
        html += entry.title;
        html += "</div>";

        html += "<div class='timesheet_edit'>";
        html += "<a href='" + entry.editurl + "' target='neontimesheet'>EDIT</a>";
        html += "</div>";

        html += "</div>";
      });
      html += "<div class='timesheet_add'>";
      html += "<a href='" + payload.addurl + "?project=" + currentBoard + "&uuid=" + currentIssue + "' target='neontimesheet'>Log new</a>";
      html += "</div>";
      $("#timesheet_target").html(html);
    }
  });
