// These need to come from config
var timesheet_user = "superadmin";
var timesheet_pass = "changeme";
var timesheet_base = "http://127.0.0.1:8888";

var boardid = false;

// Router
chrome.runtime.onMessage.addListener(function(request, sender, sendResponse) {
  if (request.task == "cardChanged") {
    getToken(request.uuid);
  }
  else if (request.task == "boardChanged") {
    boardid = request.uuid;
  }
});

function getToken(uuid) {
  var url =  timesheet_base + "/session/token";

  var xhr = new XMLHttpRequest();
  xhr.open("GET", url, true);
  xhr.setRequestHeader("Content-Type", "application/json; charset=utf-8");
  xhr.setRequestHeader("Authorization", "Basic " + btoa(timesheet_user + ":" + timesheet_pass))

  xhr.onload = function () {
    if (this.status >= 200 && this.status < 300) {
      token = xhr.response;
      fetchIssues(token, uuid);
    } else {
      console.log(xhr.response);
    }
  };

  xhr.onerror = function () {
    console.log(xhr.response);
  };
  xhr.send();
}

function fetchIssues(token, uuid) {
  var url =  timesheet_base + "/timesheet/byuuid/" + uuid + "?_format=json"

  var xhr = new XMLHttpRequest();
  xhr.open("GET", url, true);
  xhr.setRequestHeader("Content-Type", "application/json; charset=utf-8");
  xhr.setRequestHeader("Authorization", "Basic " + btoa(timesheet_user + ":" + timesheet_pass))

  xhr.onload = function () {
    if (this.status >= 200 && this.status < 300) {
      entries = xhr.response;
      sendMessage("timesheets", entries);
    } else {
      console.log(xhr.response);
    }
  };

  xhr.onerror = function () {
    console.log(xhr.response);
  };
  xhr.send();
}

function sendMessage(action, data) {
  chrome.tabs.query({active: true, currentWindow: true}, function(tabs){
    chrome.tabs.sendMessage(tabs[0].id, {action: action, payload: data});
  });
}
