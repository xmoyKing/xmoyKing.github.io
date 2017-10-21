---
title: JavaScript高级程序设计-23-H5
categories: js
tags:
  - js
  - js-pro
  - html5
date: 2016-09-11 09:54:45
updated:
---

为了简化创建动态Web界面的工作，H5规范定义了很多新HTML标记，同时也定义了很多API。

### 跨文档消息传递
跨文档消息传递（cross-document messaging, XDM），指来自不同域的页面间传递消息，例如www.xxx.com页面同iframe中的sub.xxx.com通信（或者弹出的新页面），XDM将通信机制规范化，能稳妥简单的实现通信。

XDM的核心时postMessage方法，该方法除了在XDM中有，在其他部分也有可能会有，但都是为了通信，postMessage接收2个参数：消息(只能是字符串格式)，和表示消息接收方的域的字符串：
```js
var iframeWin = document.getElementById('myframe').contentWindow;
iframeWin.postMessage('hi', 'http://www.xxx.com');
```
若将第二个参数设置为"*",则表示把消息发给任何域，但不推荐。

接收到XDM消息时，会出发window对象的message事件，此事件是异步的，传给message事件的处理程序的事件对象包含3个重要的属性：
- data， 作为postMessage方法的第一个参数，即传递过来的数据
- origin，发生消息的文档所在的域，主要用于验证身份
- source，发生消息的文档的window对象的代理，除了postMessage方法通过此对象不能访问其他信息，因为它不是实际的对象
```js
EventUtil.addHandler(window,'message',function(event){
  if(event.origin == 'http://www.xxx.com'){
    event.data; // 数据
    event.source.postMessage('Received', 'http://sub.xxx.com');
  }
})
```

### 原生拖放
H5也为拖放制定了规范（由IE最先提出并实现），拖放能够在框架间、窗口间、甚至在应用间拖放网页元素。

#### 拖放事件
通过拖放事件，能控制拖放相关的各个方面，其中最关键的地方在于确定哪里发生了拖放事件，拖动某元素时，依次触发下列3个事件：
1. dragstart,在被拖动元素上按下鼠标并开始移动时
2. drag，在元素被拖动期间持续触发，与mousemove类似
3. dragend，当拖动停止时触发

当某个元素被拖动到一个有效的放置目标上时（本身无效），会依次触发下列事件：
1. dragenter，类似mouseover
2. dragover，只要被拖动元素还在放置目标的范围内移动就会持续触发此事件
3. dragleave/drop，若被拖动元素离开放置目标的范围内则触发dragleave，若被拖动元素放到了放置目标上，则触发drog事件

#### 自定义放置目标
虽然所有元素都支持放置目标事件，但元素默认是不允许放置的，当拖动元素经过不允许放置的元素，则不会触发drop事件。只要重写dragenter和dragover事件在目标元素上的默认行为（即使用preventDefault阻止）即可将任何元素变成有效的放置目标。

#### dataTransfer对象
拖放一般是需要实现数据交换的（否则单纯拖放没什么用），dataTransfer对象是事件对象event的一个属性，用于从被拖放元素想放置目标传递字符串格式的数据，因为它是event的属性，所以只能在拖放事件的事件处理函数中访问。

dataTransfer对象有2个主要方法：getData和setData，setData的2个参数分别为：数据类型，第二个参数为数据。getData只有一个参数即setData时的第一个参数。
```js
event.dataTransfer.setData('text', '...');
event.dataTransfer.getData('text');

event.dataTransfer.setData('text/uri-list', '...');
event.dataTransfer.getData('text/uri-list');
```
事实上，IE只有text和URL两种有效类型，而H5则扩展为MIME类型，考虑到向后兼容，H5会将text和URL分别映射为text/plain和text/uri-list类型。

同时，dataTransfer可以为每种MIME类型都保存一个值，即可以同时在对象中保持文本和URL。若在drop事件中没有读取到数据则说明dataTransfer对象已经被销毁，数据已丢失。

除了显式设置setData的内容，默认情况下，拖动文本时浏览器会自动调用setData方法，将文本以text格式保存起来，而链接和图片会以URL格式保持起来。

#### dropEffect与effectAllowed
dataTransfer对象上有两个属性，dropEffect与effectAllowed，可以通过他们来确定被拖放元素以及作为放置目标的元素能够接收什么操作。

其中dropEffect属性可以知道被拖放的元素能够执行那种放置行为，其值为字符串类型，有4种可能值：
- 'none', 表示不能把拖动元素放在此处，所有元素的默认值（除了文本框之外）
- 'move', 表示应该把拖动元素移动到放置目标
- 'copy', 表示应该把拖动元素复制到放置目标
- 'link', 表示放置目标会打开被拖动元素（此时拖动元素必须是url）
要使用dropEffect，必须在ondragenter事件中针对放置目标来设置它，同时必须搭配effectAllowed才行，effectAllowed表示允许拖动元素的哪种dropEffect。而effectAllowed必须在dragstart中设置。其值可以有如下几种：
- uninitialized， 没有设置任何放置行为
- none
- copy
- move
- link
- copyLink，既可以copy也可以link
- copyMove
- linkMove
- all

默认情况下，只有文本、图片、链接可拖动，而其他元素不可拖动，H5为所有元素定义了一个draggable的属性，表示元素是否可拖动。该属性可更改默认设置。

dataTransfer还有如下3个属性：
- addElement(element), 为拖动操作添加一个元素，该元素只能影响数据，不影响外观
- clearData(format)
- setDragImage(element, x, y), 指定拖动时光标下的元素（此元素可以是图片，或任意其他元素）
- types，当前保持的数据类型，返回一个类数组集合。

### 媒体元素
H5新填了两个媒体元素，audio和video，分别用于嵌入音频和视频，允许通过js API自定义媒体控件。

不同的浏览器支持不同的编解码器，所有一般会指定多种格式的媒体来源。具体是否支持某个格式的媒体则可以通过canPlayType方法。

### 历史状态管理
除了hashchange事件，H5更新了History对象，为管理历史状态提供了更多功能。

通过状态管理API，能够在不加在新页面的情况下修改浏览器URL，只要使用history.pushState方法即可，该方法接收3个参数：状态对象、新状态的标题、可选的相对URL
```js
history.pushState({name: 'king'}, 'King Page', 'king.html')
```
第一个参数用于提供还原（初始化）页面状态所需的各种信息，第三个参数在执行方法会变成新的URL，同时浏览器不会发生请求到服务器，即使状态修改后查询location也会返回更新后的地址。

pushState会创建新的历史状态加入到历史状态栈中，同时后退按钮也可使用，当后退时会触发window对象的popstate事件，该事件对象event有一个state属性，该属性就包含pushState时的第一个参数，状态对象。没有历史状态时state为null。

因为浏览器不会自动将页面重置为以前pushState时的状态，所以需要通过状态对象中的数据来还原页面的状态。

更新当前状态可以食堂replaceState方法，传入的参数与pushState的前2个参数相同，此方法不会在历史状态栈中创建新状态，只会重写当前的状态。

注：状态对象中不能包含DOM元素。