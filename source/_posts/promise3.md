---
title: Promise-3-测试
categories:
  - JavaScript
tags:
  - Promise
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
但是上述代码也有一个问题，若failTest本身出错了呢？ 它抛出的异常会被catch捕捉，同时传递给catch的Error对象是AssertionError类型。

所以此时，需要在测试代码中明确指定Fulfilled和Rejected两种状态下的处理流程，这样的话，就能在promise变为Fulfilled状态的时候编写失败的测试代码了。
```js
function mayRejected(){
  return Promise.resolve();
}
it('catch -> then', function(){
  return mayRejected().then(failTest, function(err){
    assert(error.message === 'woo');
  });
});
```

前面说过，推荐then - catch的方式编写promise，而不是then(onFulfilled, onRejected), 因为Promise提供了强大的错误处理机制，但是在测试环境下，Promise的错误机制反而限制了测试失败的代码，所以不得不使用then的写法，这样才能明确promise在各种状态下进行何种处理。

总结：
1. 在普通的使用情况中，采用then - catch的流程比较容易理解，在处理错误的时候能带来很多方便
2. 在测试时，将测试代码集中到then中处理，能将AssertionError对象传递到测试框架中

### 可控测试（controllable tests）
什么是可控测试？ 对一个待测的Promise对象，若能实现如下2点，则称为可控测试，
1. 若编写预期为Fulfilled状态的测试的话，
  - Rejected的时候要Fail
  - assertion的结果不一致的时候要Fail
2. 若预期为Rejected状态的话
  - 结果为Fulfilled测试为Fail
  - assertion的结果不一致的时候要Fail
也就是说，一个测试用例应该包含下面的测试内容，
1. 结果满足Fulfilled / Rejected之一
2. 对传递给assertion的值进行检查

比如，如下的then代码就是一个期望结果为Rejected的测试
```js
promise.then(failTest, function(error){
  // 通过assertion验证error对象
  assert(error instanceof Error);
});
```

由于在编写测试代码时，需要明确指定promise状态为Fulfilled或Rejected之一，而then在调用的时候可以省略参数，有时可能忘记加入使测试失败的条件，此时，可以定义一个helper函数，用来设置promise为期望的状态, 关于helper函数，可以查看[azu/promise-test-helper](https://github.com/azu/promise-test-helper)
```js
var assert = require('assert');
describe('Promise Test',function(){

  // 一个名为shouldRejected的helper函数
  function shouldRejected(promise){
    return {
      'catch': function(fn){
        return promise.then(function(){
          throw new Error('Expected promise to be reject but it was fulfilled');
        }, function(reason){
          fn.call(promise, reason);
        });
      }
    };
  }

  it('should be rejected', function(){
    var promise = Promise.reject(new Error('human error'));
    return shouldRejected(promise).catch(function(error){
      assert(error.message === 'human error');
    });
  });

});
```
上述代码中，shouldRejected函数接收一个promise对象作为参数，并且返回一个带有catch方法的对象，在这个catch中可以使用和onRejected里一样的代码，因此可以在catch使用基于assertion方法的测试代码。

在shouldRejected外部，是和普通promise处理相同流程的代码：
1. 将需要测试promise对象传递给shouldRejected方法
2. 在返回的对象里catch方法中编写进行onRejected处理的代码
3. 在onRejected里使用assertion进行判断

在使用shouldRejected函数的时候，若Fulfilled被调用了的话，则会throw一个异常，测试会显示失败。类似的，可以编写一个shouldFulfilled的helper函数, 结构类似。
```js
var assert = require('assert');
describe('Promise Test',function(){

  // 一个名为shouldFulfilled的helper函数
  function shouldFulfilled(promise){
    return {
      'then': function(fn){
        return promise.then(function(value){
          fn.call(promise, value);
        }, function(reason){
          throw new Error(reason);
        });
      }
    };
  }

  it('should be resolve', function(){
    var promise = Promise.resolve('value');
    return shouldFulfilled(promise).then(function(value){
      assert(value === 'value');
    });
  });

});
```