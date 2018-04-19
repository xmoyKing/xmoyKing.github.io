---
title: Node 技巧笔记3 - EventEmiiter
categories: Nodejs
tags:
  - JavaScript
  - Nodejs
  - EventEmiiter
date: 2018-03-25 10:40:37
updated: 2018-03-25 10:40:37
---

Node的事件模块目前只有一个类：EventEmiiter，这个类在Node的内置模块和第三方模块中使用非常频繁，是众多模块的基础。

### 基本用法
使用EventEmiiter，必须先继承它，同时可以融入其他基类。

#### 从EventEmiiter继承
假若希望通过事件驱动来解决一个问题，需要异步事件发生的时候执行一些操作。

常用的例如，通过emit来触发事件、通过on绑定监听器。

```js
const util = require('util');
const events = require('events');
const AudioDevice = {
  play: function(tract){
    // ...
  },
  stop: function(){
    // ...
  }
}

// 配置类的状态，会在EventEmitter的构造器中按需调用
function MusicPlayer(){
  this.playing = false;
  events.EventEmitter.call(this);
}

// inherits方法将方法从一个原型拷贝到另外一个原型，基于EventEmitter的创建类的通用模式
util.inherits(MusicPlayer, events.EventEmitter);

let musicplayer = new MusicPlayer();
musicplayer.on('play', function(tract){
  this.playing = true;
  AudioDevice.play(track);
});
musicplayer.on('stop', function(tract){
  this.playing = false;
  AudioDevice.stop();
});

musicplayer.emit('play', 'music list');

setTimeout(function(){
  musicplayer.emit('stop');
}, 1000);
```

类似浏览器的事件管理，可以添加多个同名监听器，触发顺序为定义的顺序，同时也能删除监听器，参数为事件名称和原事件函数。
```js
function play(track){
  this.playing = true;
}

musicplayer.on('play', play);

musicplayer.removeListener('play', play);
```

#### 混入EventEmitter
有时继承EventEmitter并不是最好的方式，这个时候可以通过混入EventEmitter解决问题。

通过for-in循环将属性从一个原型对象拷贝到另一个原型对象上。也可以通过ES6的keys方法遍历。

```js
const EventEmitter = require('events').EventEmitter;

function MusicPlayer(track){
  this.track = track;
  this.playing = false;

  for(let method in EventEmitter.prototype){
    this[method]  = EventEmitter.prototype[method];
  }
}

MusicPlayer.prototype = {
  toString: function(){
    if(this.playing){
      return 'Playing now:' + this.track;
    }else{
      return 'Stopped';
    }
  }
}

let mp = new MusicPlayer('some song name');

mp.on('play', function(){
  this.playing = true;
  console.log(this.toString());
})

mp.emit('play');
```

#### 异常管理
大多数的事件处理都是类似的，但error事件比较特殊，一般是通过对error时间添加监听器。

在异常发送时阻止异常抛出，只要在error事件上添加一个监听器，任何从EventEmitter继承的自定义类，或者标准类都可以通过这个方法来解决异常。

当一个EventEmitter实例发生错误时，通常会发出一个error事件，在Node中，error事件被当作特殊的情况，若没有监听器，那么模式会打印堆栈并退出。

#### 技巧23 反射
有时候需要动态响应一个EventEmitter实例的变化，或者查询它的监听器，比如需要知道一个监听器何时被添加到一个emitter上，或者查询现有的监听器。

可以追踪监听器何时被添加，EventEmitter会发出一个特殊的事件叫newListener，监听了这个事件的监听器会接收事件的名字及监听器方法。

```js
var util = require('util');
var events = require('events');

function EventTracker(){
  events.EventEmitter.call(this);
}

util.inherits(EventTracker, events.EventEmitter);

var eventTracker = new EventTracker();

eventTracker.on('newListener', function(name, listener){
  console.log(name + ' added');
})

eventTracker.on('a listener', function(){
  // 此处会触发newListener事件
});
```

#### 技巧24 探索EventEmitter
知道在什么地方使用EventEmitter并知道如何利用它是必备的技能。

假设正在开发一个大项目，多个模块之间需要相互通信。通常有一个主要的核心模块，例如Express的app对象就是这个核心模块，其就继承自EventEmitter，这样就可以利用事件使不同的组件相互通信。
```js
var express = require('express');
var app = express();

app.on('hello',function(){
  console.log('hello~');
});

app.get('/', function(req, res){
  res.app.emit('hello'); // app对象也可通过res.app获取，这样在其他模块，也能访问app对象了
  res.send('hello world');
});

app.listen(3000);
```

#### 组织事件名称
当项目有很多事件时，就需要统一组织事件名称，方便修改和扩展。最简单的方式为使用一个对象来存储所有的事件名。这样在项目就有一个统一的存放事件的地方。

```js
var util = require('util');
var events = require('events');

function MusicPlayer(){
  events.EventEmitter.call(this);
  this.on(MusicPlayer.events.play, this.play.bind(this));
}

var e = MusicPlayer.events = {
  play: 'play',
  pause: 'pause',
  stop: 'stop',
  ff: 'ff',
  rw: 'rw',
  addTrack: 'add-track'
}

util.inherits(MusicPlayer, events.EventEmitter);

MusicPlayer.prototype.play = function(){
  this.playing = true;
}

var musicplayer = new MusicPlayer();

musicplayer.on(e.play, function(){
  // ...
});

musicplayer.emit(e.play);
```

EventEmitter本质是一个观察者模式的实现，这种模式可以帮助Node在多个进程或网络中运行。类似的解决方案还有发布/订阅模式、AMQP、js-singals。