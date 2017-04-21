---
title: Promise3-测试
categories:
  - fe
tags:
  - fe
  - promise
  - mocha
date: 2017-04-11 17:20:43
updated: 2017-04-21 17:20:43
---

上一篇学习了如何编写Promise，这篇学习如何对Promise进行测试，使用Mocha对Promise进行基本测试

### Mocha
[Mocha](http://mochajs.org/) 是基于Nodejs的一个测试框架, 先学一些基本的用法，具体的流程如下：
```shell
# 安装mocha
npm install mocha -g
```

然后使用Nodejs默认自带的assert模块，Mocha中的it指定done参数，在done函数被执行之前，该测试一直处于等待状态，这样就可以对异步进行测试。
```js
// basic.js
var assert = require('assert');
describe('basic test', function(){
  // 对一个回调风格的异步函数进行测试，
  context('When Callback(high-order function)', function(){
    it('should use `done` for test', function(done){
      // 1.回调函数的异步处理
      setTimeout(function(){
        assert(true);
        done(); // 2.调用done后测试结束
      },0);
    });
  });
  // 对Promise进行测试
  context('When promise object', function(){
    it('should use `done` for test?', function(done){
      var promise = Promise.resolve(1); // 1. 创建一个promise对象，状态为Fulfilled
      promise.then(function(value){
        assert(value === 1);
        done(); // 2.调用done后测试结束
      });
    });
  });
});
```
使用`mocha ./basic.js`执行：
![执行结果](1.png)


测试失败时的示例
```js
var assert = require('assert');
describe('basic test', function(){
  context('When Promise Timeout', function(){
    // 以下测试不会结束，直到超时
    it("should use `done` for test?", function (done) {
        var promise = Promise.resolve();
        promise.then(function (value) {
            assert(false);// => throw AssertionError
            done();
        });
    });
  });

  // 下段与上段代码不可同时执行
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
```
超时失败:
![超时失败](2.png)

正常失败:
![正常失败](3.png)

### Mocha & Promise
对Promise进行测试的时候，不使用done，而是返回一个promise对象,具体示例如下：
```js
var assert = require('assert');
describe('Promise Test',function(){
  it('should return a promise object', function(){
    var promise = Promise.resolve(1);
    return promise.then(function(value){
      assert(value === 1); // 测试成功
    });
  });

  it('should be fail', function(){
    return Promise.resolve().then(function(){
      assert(false); // 测试失败
    });
  });
});
```
采用这种方式，当assert失败的时候，测试也就是失败了，能从根本上省略如then(done, done)这样与测试逻辑无直接关系的代码。
![Promise Test](4.png)

但是上述的失败是预定的，若发生了非预定的失败的时候，还是按照Mocha的写法来测试就会有问题。
```js
// 对Error Object进行测试
function mayRejected(){
  return Promise.reject(new Error('woo'));
}

it('is bad pattern', function(){
  return mayRejected().catch(function(error){
    assert(error.message === 'woo');
  });
});
```
当上面测试代码中的promise对象变为Rejected的时候，会调用onRejceted中注册的函数，也就是`function(error){assert(error.message === 'woo');}`, 从而测试成功。

但是若mayRejected中的代码不是reject而是resolve时，测试会一直成功，因为promise会返回一个Fulfill状态，而catch中注册的onRejected函数并不会被调用，所以测试会一直通过，显示passed。
```js
// 对Error Object进行测试
function mayRejected(){
  return Promise.resolve();
}

it('is bad pattern', function(){
  return mayRejected().catch(function(error){
    assert(error.message === 'woo');
  });
});
```
为了解决这个问题，我们可以在catch前加入一个then调用，可以理解为若成功调用了then，那么测试就失败了。
```js
function failTest(){
  throw new Error('Expected promise to be rejected but it was fulfilled');
}
function mayRejected(){
  return Promise.resolve();
}
it('should bad pattern', function(){
  return mayRjected().then(failTest).catch(function(error){
    assert.deepEqual(error.message === 'woo');
  });
});
```
但是上述代码也有一个问题，若failTest本身出错了呢？ 它抛出的异常会被catch捕捉。

