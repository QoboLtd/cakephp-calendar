describe('Testing CalendarsController::index() method', function() {
    before(function(browser, done) {
      done();
    });

    after(function(browser, done) {
      browser.end(function() {
        done();
      });
    });

    afterEach(function(browser, done) {
      done();
    });

    beforeEach(function(browser, done) {
      done();
    });

    it('access via GET [unregistered]', function(browser) {
      var indexUrl = 'http://localhost:8000/calendars/calendars/';

      browser
        .url(indexUrl)
        .expect.element('body').present;
    });

});
