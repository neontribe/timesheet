chrome.extension.sendMessage({}, function(response) {
  var pathname = false;
  var boardname = false;

  var readyStateCheckInterval = setInterval(function() {
    if (document.readyState === "complete") {
      clearInterval(readyStateCheckInterval);

      // ----------------------------------------------------------
      // This part of the script triggers when page is done loading
      console.log("Hello. This message was sent from scripts/inject.js");
      // ----------------------------------------------------------
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


    }
  }, 10);
});

function cardChanged(card, board) {
  var site = 'https://tobias.batch.org.uk/timeshite/web/';
  var platform = 'trello';
  var url = site + '?platform=' + platform + '&board=' + board + '&issue=' + card;
  $('.window-sidebar').append("<iframe id='timesheet' src='https://tobias.batch.org.uk/timeshite/web/' width='168' height='300'></iframe>");
}
