var pathname = false;
var boardname = window.location.pathname;
console.log('HERE');

chrome.extension.sendMessage({}, function(response) {
  var readyStateCheckInterval = setInterval(function() {
    // if (document.readyState === "complete") {
    // clearInterval(readyStateCheckInterval);

    if (pathname != window.location.pathname) {
      // issue/board has changed
      pathname = window.location.pathname;
      if (pathname.startsWith("/c")) {
        // It's a new card
        cardChanged(pathname, boardname);
      }
      else if (pathname.startsWith("/b")) {
        boardname = pathname;
      }
    }


    // }
  }, 5000);
});

function cardChanged(fullcard, fullboard) {
  console.log(fullcard, fullboard);
  var site = 'https://tobias.batch.org.uk/timeshite/web/timesheet/';
  var platform = 'trello';

  var card = fullcard.substring(3, fullcard.indexOf("/", 3));
  var board = fullboard.substring(3, fullcard.indexOf("/", 3));

  var url = site + "list/" + board + "/" + card + "?platform=" + platform + "&board=" + fullboard + "&card=" + fullcard;
  console.log(url);
  $('.window-sidebar').append('<iframe id="timesheet" src="' + url + '" width="168" height="600"></iframe>');
}
