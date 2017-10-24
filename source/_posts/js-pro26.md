---
title: JavaScript高级程序设计-26-高级技巧-2
categories: js
tags:
  - js
  - js-pro
date: 2016-09-17 13:49:47
updated:
---

接上篇[JavaScript高级程序设计-25-高级技巧-1](/2016/09/17/js-pro26)

### 高级定时器
使用setTimeout和setInterval创建的定时器可以用于实现一些有用的功能，由于js是运行于单线程的环节中，而定时器仅仅只是计划在未来的某个时间执行，执行时机不能保证，因为页面在生命周期内，不同时间可能有其他代码在控制js进程。页面下载完后的代码运行、事件处理程序、Ajax回调函数都必须使用同样的线程来执行。实际上，浏览器负责进行调度和排序、指派某段代码在某个时间点运行的优先级。

将js想象成在时间上运行，当页面载入时，首先执行的是任何包含在script元素中的代码，通常是页面生命周期后面要用到的一些简单的函数和变量的声明，不过有时候也包含一些初始数据的处理，在此之后，js进程将等待更多代码执行。当进程空闲时，下一个代码会被立刻触发并立即运行。例如，当点击按钮时，只要js进程处于空闲状态，则onclick事件处理程序会立刻执行。

除了主js执行进程外，还有一个需要在进程下一次空闲时执行的代码队列，随着页面在其生命周期中的推移，代码会按照执行顺序添加到队列中，例如，当按下按钮，它的事件处理程序代码会被添加到队列中，并在下一个可能的时间执行。当接收到ajax响应时，回调函数的代码会被添加到队列中。在js中没有任何代码是立刻执行的，但一旦进程空闲则尽快执行。

定时器对队列的工作方式为当特定时间过去后将代码插入，注意，给队列添加代码并不意味立刻执行代码，而只能表示它会尽快执行。比如，设定一个150ms后执行的定时器不代表到150ms时立刻执行，它仅仅表示代码会在150ms后被加入到队列中。若在这个时间点上，队列中没有其他东西，那么这段代码就会被执行，表面上看上去好像代码就在精确指定的时间点上执行了。其他情况下则有可能等待更长的时间才执行。

#### 重复定时器
使用setInterval创建的重复定时器能周期性的插入代码到队列中，但问题在于，重复定时器代码可能在代码再次被添加到队列之前还没完成，结果就会导致重复定时器代码连续允许好几次而之间没有间隔。js引擎对此进行了优化，避免此问题，即当使用重复定时器时，仅当没有该定时器的任何其他代码实例时，才将定时器代码添加到队列中，这样确保定时器代码加入到队列中的最小时间间隔为指定间隔。

但还有有问题，比如某些间隔可能会被跳过，又比如多个定时器的代码执行之间的间隔可能会比预期的小。

为了避免重复定时器的问题，考虑使用setTimeout模拟替代setInterval。没次函数执行的时候会创建一个新的计时器，这样的好处是在前一个定时器代码执行之前，不会向队列插入新的定时器代码，确保不会有任何缺失的间隔，而且可以保证在下一次定时器代码执行之前，至少要等待指定的间隔，避免连续的运行。

#### Yielding processes
脚本运行时间长通常有2种原因，一是过长过深的嵌套调用，一是进行大量计算或处理的循环。因为js的执行是阻塞操作，脚本运行越长，用户无法与页面交互的时间就越长。

若是第二种循环的情况，同时既不需要同步完成，也不需要按照指定顺序完成，那么可以考虑使用是定时器将循环分割，这种技术叫数组分块（array chunking），小块小块的处理数组。基本思路就是为要处理的项目建立一个队列，然后使用定时器取出下一个要处理的项目进行处理，然后接着再设置另一个定时器。

数组分块的好处在于它可以将多个项目的处理在执行队列上分开，在每个项目处理之后，给与其他浏览器处理机会运行，这样就可能避免长时间运行脚本的错误。

#### 函数节流
浏览器中某些计算和处理要比其他的昂贵很多，比如过多DOM操作甚至会导致浏览器崩溃。又比如onresize、onscroll等事件，高频的连续触发也可能导致浏览器崩溃。为了绕开此问题，可以使用定时器对函数进行节流。

函数节流的思想是让代码不在没有间隔的情况下连续重复执行。第一次调用函数，创建一个定时器，在指定的时间间隔之后运行代码，当第二次调用该函数时，它会清除前一次的定时器并设置另一个，若前一个定时器已经执行过了，则忽略操作，若前一个定时器尚未执行，那么将其替换为一个新的定时器。目的就是只有在执行函数的请求停止了一段时间之后才执行。
```js
var processor = {
  timeoutId: null,
  performProcessing: function(){ // 实际进程处理的代码
    // ...
  },
  process: function(){ // 初始调用方法
   clearTimeout(this.timeoutId);
   var that = this;
   this.timeoutId = setTimeout(function(){
     that.proformProcessing();
   }, 100)
  }
}

processor.process(); // 开始执行

// 上述代码可简化如下：
function throttle(method, context){
  clearTimeout(method.tId);
  method.tId = setTimeout(function(){
    method.call(context);
  }, 100);
}

// 使用
function resizeDiv(){
  // ...
  div.style.height = div.offsetWidth + 'px';
}

window.onresize = function(){
  throttle(resizeDiv);
}
```

### 自定义事件
事件是js与浏览器交互的主要途径，事件是一种叫观察者模式的设计模式，该模式能创建松散耦合的代码。对象可以发布事件，用来表示在该对象生命周期中某个确定时刻到达，然后其他对象可以观察该对象，等待这些时刻到达并运行代码来响应。

观察者模式由2类对象组成：主体和观察者。主体负责发布事件，同时观察者通过订阅这些时间来观察该主体。该模式的一个关键概念是主体并不知道观察者的任何事情，也就是说它一独立存在并正常运行即使没有观察者。从个另一方面，观察者知道主体并能注册事件的回调函数，涉及DOM时，DOM元素便是主体，事件处理代码就是观察者。

事件是与DOM交互的最常见方式，但也可以用非DOM代码，即通过实现自定义事件来交互。自定义事件背后的思想是创建一个管理事件的对象，让其他对象监听那些事件。
```js
function EventTarget(){
  this.handlers = {};
}

EventTarget.prototype = {
  constructor: EventTarget,
  addHandler: function(type, handler){
    if(typeof this.handlers[type] == 'undefined'){
      this.handlers[type] = [];
    }
    this.handlers[type].push(handler);
  },

  fire: function(event){
    if(!event.target){
      event.target = this;
    }
    if(this.handlers[event.type] instanceof Array){
      var handlers = this.handlers[event.type];
      for(var i = 0, len = handlers.length; i < len; i++){
        handlers[i](event);
      }
    }
  },

  removeHandler: function(type, handler){
    if(this.handlers[type] instanceof Array){
      var handlers = this.handlers[type];
      for(var i = 0, len = handlers.length; i < len; i++){
        if(handlers[i] === handler){
          break;
        }
      }
      handlers.splice(i, 1);
    }
  },
};
```