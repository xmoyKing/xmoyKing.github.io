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