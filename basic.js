var assert = require('assert');
describe('Promise Test',function(){
  it('should return a promise object', function(){
    var promise = Promise.resolve(1);
    return promise.then(function(value){
      assert(value === 1);
    });
  });

  it('should be fail', function(){
    return Promise.resolve().then(function(){
      assert(false); // 测试失败
    });
  });

});