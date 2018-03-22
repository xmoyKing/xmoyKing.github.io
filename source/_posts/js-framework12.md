---
title: JavaScript框架设计笔记-12-异步处理
categories: js
tags:
  - js
  - js-framework
  - deferred
  - promise
date: 2017-01-07 14:53:49
updated: 2017-01-07 14:53:49
---

浏览器环境与后端的nodejs存在各种消耗巨大或堵塞线程的行为，对于JS这样的单线程环境唯一的解耦方法就是提供异步API。预设浏览器首先提供了两个异步API，setTimeout和setInterval，后来就开始出现各种事件回调，只有用户执行了某些操作后才会触发的回调函数，再然后，XMLHttpRequest、postMessage、WebWorker、setImmediate、requestAnimationFrame等相继出现，它们的共同特点就是有用回调函数，有的异步API会提供对应的中断API，如clearTimeout、clearInterval、clearImmediate、cancelAnimateionFrame。

当然不是一律是向好的方面发展的，随着Ajax的运用，以及Web应用和业务的复杂度不断增加，出现了“回调地狱callback hell”这种东西，即层层嵌套的回调函数，以及复杂的跳转。同时，由于业务流程复杂，跳转增加，对于js这种单线程环境来说，一个函数出错就是非常致命的，必须try...catch。而try...catch只能捕捉当前抛出的错误，对回调函数执行的代码无效，这就是需要特殊处理和注意的地方了。

#### setTimeout与setInerval
深入学习这两个API之前，需要了解一下它们的特殊情况：
1. 若回调的执行时间大于间隔时间，那么浏览器会继续执行它们，导致真正的间隔事件比原来大
1. 它们存在一个最小的时钟间隔，在IE6~8中为15.6ms，IE10等现代浏览器为4ms
1. 关于零秒延迟（立即执行回调函数），此回调将会放到一个能立即执行的时段进行触发，JS代码大体是由上向下执行的，但若中间穿插有关DOM的渲染，事件回调等异步代码，它们会组成一个队列，零秒延迟将会插队操作
1. 省略第二个参数时，浏览器会自动分配事件，第一次分配时在IE、FF下可能为很大的数字，其他浏览器则一般是在10ms左右，FF下，setInterval省略第二个参数会当做setTimeout处理
1. 标准浏览器与IE10，支持额外参数（从第三个算起），作为回调的传参传入
1. setTimeout方法的事件参数若为极端值时（0、负数、极大值等），则浏览器会各自处理，大部分现代浏览器是立即执行

#### AJAX
请参考[Ajax知识体系大梳理](http://louiszhai.github.io/2016/11/02/ajax/)

虽然现在ajax依然是主流，但可以预料，AJAX的未来应该是fetch。


#### Deferred 和 Promise
Deferred是一个著名的异步模型，Deferred是一个双链参数加工的流水线模型，双链是指它内部把回调分成两种，一个是成功回调，用于正常时执行，一个是错误回调，用于出错时执行。各自组成两个队列，可看作是成功队列和错误队列。添加回调时是以组为单位添加的，每组回调的参数都是上一组回调的处理结果，其中第一组的参数是用户传入的。之所以说是流水线，是指每个回调可能不是紧挨着顺序执行的，有时可能会是同步的，有时可能是异步的。若出错了则由后一组的错误回调捕捉错误处理，若处理没问题了则试着转回成功队列。

它最初是Python的Twisted框架的一个类，后来被Mochikit框架引入，但是Mochikit的推广不足，没能发展起来。

JSDeferred是cho45开发出的，其易用性远胜于Mochikit Deferred，它的实现形态基本奠定了“Promise/A”的范式，是js异步编程的一个里程碑式的库。其源码地址：[JSDeferred](https://github.com/cho45/jsdeferred/blob/master/jsdeferred.js)*非常建议查看，注释详细且带示例*

到后来jquery1.5也引入[jQuery Deferred](http://www.css88.com/jqapi-1.9/category/deferred-object/)了,但存在感一直很低，受众也很小，一开始是因为其API如then、promise、resolve、reject其实不是很好懂，同时promise是一个学术化的东西，若没有官方讲解，会让开发人员一头雾水。

整个Deferred的实现，其实是将一系列异步操作以优雅的形态链起来，然后在某个时刻一下子执行，其中关键是必须保证异步链提前建立完成。
保存回调很重要，而且需要每次成组的保存，比如Mochikit Deferred就是使用2维数组，内层数组每次保存两个回调。JSDeferred则是在每个实例上有ng、ok两个回调。

jQuery的实现方式为构造Deferred的子结构_Deferred，利用闭包、实现返回的那个对象能够通过它的某些方法操作其引入一个数组或一些回调，这个数组会不断添加回调。
如此在合体之后，一个_Deferred负责添加成功回调，另一个负责添加错误回调，但Deferred对象不负责添加回调，而负责执行，添加回调的任务给Promise（由promise方法生成）。
jQuery保证每个Deferred每次调用promise总是返回单例对象，即一个Deferred只能有一个Promise，Promise拥有Deferred出resolve、reject、resolveWith、rejectWith外的一切成员。所以其实Promise是一个只读的Deferred，目的是防止在构造Deferred链的过程中就执行回调。
但jQuery Deferred的初代实现有很多不足，首先，没有对异常进行处理，在异步中捕捉异常是非常重要的，其次，没有对原始参数进行流水化加工，而对参数进行加工处理是非常普遍的需求。

Promise/A属于Promise规范，Promise规范属于CommonJS。可参考[ES6 Promise对象](http://es6.ruanyifeng.com/#docs/promise)

Promise/A规范大致如下：一个带有then方法的对象，它拥有3个状态，pending、fulfilled、rejected。一开始是pending、执行then后，当前回调被执行时，会进入fulfilled或rejected状态。

then方法可传入两个函数，一个是成功时的执行，一个是失败时执行，分别叫onFulfill、onReject。then还有第三个参数叫onNotify，它不会改变对象的状态，这3个函数都是可选的，非函数会被忽略。
then方法在添加了onFulfill或onReject会返回一个新的Promise对象，这样就能形成一个Promise链。

后来在Promise/A规范上添加了更多的细则，形成了Promise/A+规范。添加了all，any等方法，并归并结果或处理竞态状态。现在一般而言有3种Promise/A+库，分别是Q、RSVP、When。其中Q的微缩版被整合进angular.js，RSVP被整合进ember.js。这两个库都是MVVM库。

**js异步前景：建议直接学习并运用**
现在ES6已经支持yield（generator生成器）了，目标是以同步的方式写异步代码。只要将它们放入某个函数体内即可。

参考[ES6 Generator](http://es6.ruanyifeng.com/#docs/generator)