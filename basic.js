var assert = require('assert');
describe('basic test', function(){
  // context('When Promise Timeout', function(){
  //   // 以下测试不会结束，直到超时
  //   it("should use `done` for test?", function (done) {
  //       var promise = Promise.resolve();
  //       promise.then(function (value) {
  //           assert(false);// => throw AssertionError
  //           done();
  //       });
  //   });
  // });

  context('When Promise error', function(){
    // 正常失败的测试
    it("should use `done` for test?", function (done) {
        var promise = Promise.resolve();
        promise.then(function (value) {
            assert(false);// => throw AssertionError
        }).then(done,done);
        // 由于后面接了一个then(done,done), assert 通过会调用done，失败也会调用done(error);
    });
  });
});