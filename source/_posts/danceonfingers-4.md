---
title: 指尖上行，移动前端开发进阶之路笔记-4
categories: WebApp
tags:
  - webapp
  - Webapp-API
date: 2018-01-15 16:28:08
updated: 2018-01-15 16:28:08
---

### 移动设备Web API详解
#### 视频（Video）
video标签是HTML5功能中使用频率非常高的标签，在各浏览器中的支持还不错。video标签有如下一些属性：

**autoplay**
由于移动操作系统对流量开销的优化，video标签的自动播放在iOS和Android的原生浏览器都是默认不支持的，但在某些浏览器或APP的webview中可能支持，如微信或手机QQ等，即，若页面的投放渠道在这些APP或浏览器中，就可以考虑设计video自动播放的效果。

在iOS10中，有一个新的video属性`playsinline`,此属性支持视频在原生Safari浏览器中的自动播放，但视频需要是无声音的。

```html
<video autoplay muted playsinline src="video.mp4">
```

而基于视频交互的H5在微信下可能会有问题。

**preload**
video的预加载属性在iOS不支持，Android下则是部分支持，若需要使用到video的预加载功能，则可以通过js实现，即先播放，然后监听video的canplay事件，在事件回调里面再暂停video的播放。

```html
<video preload="auto" src="video.mp4">
```

```js
video.play();
video.addEventListener('canpaly', function(){
  video.pause();
}, false);
```

**poster**
video的预览图属性在iOS中是支持的，而Android不支持，如需要统一使用预览图的效果，在Android系统中可以统一覆盖一张图片在视频层上面（图片为视频的第一帧），当video开始播放的时候，再隐藏这个图片来达到模拟poster的效果。

```html
<video poster="poster.png" src="video.mp4">
```

通过video标签属性和事件。可以尝试一些H5视频互动的页面。

具体参考[MDN Video](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/video)


#### 音频（Audio）
audio标签承载声音内容，如音乐、音效及其他音频流，移动设备对audio标签的支持深度不如PC，但广度却比PC更好，大部分功能都可以在移动端设备很好的被使用。

比如官方给出的支持音频格式：

| 格式 | iOS | Android | 特点 |
| - | - | - | - |
| mp3 | iOS 4.1+ | Android 2.3+ | 便于传播、压缩率高 |
| wav | iOS 4.1+ | Android 2.3+ | 体积大、不利于网络传播 |
| ogg | 不支持 | Android 2.3+ | 不兼容iOS |

而其他格式如wma、aac、flac、m4a等格式也有不同程度的支持，但大多因为体积大、设备兼容性不足、声音还原度差等原因不被移动设备广泛支持，总结而言还是mp3支持度比较好。

与视频类似，音频的自动播放功能和preload是被默认禁止的。

具体参考[MDN Audio](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/audio)

[检测当前设备支持那些audio事件](http://tgideas.qq.com/book/danceonfingers/chapter2/section2/audio_api/)


#### 媒体流（getUserMedia）
媒体捕获规范定义了方便开发者访问用户camera和mic的API，通过inputElement控件或js的getUserMedia实现。

系统要求：Android 4.4+、微信/手机QQ Webview，而有一些Webview需要https协议才能使用getUserMedia。

**摄像头捕获**
```js
navigator.webkitGetUserMedia({video: true}, function(stream){
  video.src = window.URL.createObjectURL(stream);
  localMediaStream = stream; // 存储流媒体
},function(err){
  // 捕获错误
});
```
[摄像头测试地址](http://tgideas.qq.com/book/danceonfingers/chapter2/section2/camera.html)

**从视频流中拍照**
```js
btnCapture.addEventListener('touchend', function(){
  if(localMediaStream){
    canvas.setAttribute('width', video.videoWidth);
    canvas.setAttribute('height', video.videoHeight);
    ctx.drawImage(video, 0, 0);
  }
},false);
```

**用户声音录制**
```js
navigator.getUserMedia({audio: true}, function(e){
  var context = new audioContext();
  var audioInput = context.createMediaStreamSource(e);
  var volume = context.createGain();
  var recorder = context.createScriptProcessor(2048, 2, 2);
  var recordingLength = 0;
  recorder.onaudioprocess = function(e){
    recordingLength += 2048;
    recorder.connect(context.destination);
  }
},function(err){
  // 捕获错误
});
```
[mic测试地址](http://tgideas.qq.com/book/danceonfingers/chapter2/section2/microphone-usermedia.html)

**保存声音**
```js
var buffer = new ArrayBuffer(44 + interleaved.length * 2);
var view = new DataView(buffer);
fileReader.readAsDataURL(blob); // android chrome audio不支持blob
audio.src = event.target.result;
```

#### Web Speech
Web Speech是一种只需要通过语音就能让用户与Web进行交互的技术，由W3C提出并实施推动的草案，所以作为一项新的标准，想要让所有浏览器都支持并实现一致还需要相当长的时间。

Web Speech API由3个部分构成：
- 通过SpeechRecognition对象实现语音识别
- 通过SpeechSynthesis对象实现文本转语音合成
- 通过SpeechGrammar对象实现自定义语法创建

Web Speech API能够让用户通过语音与Web进行交互，用户可以使用语音搜索（用Chrome打开Google，可以看到有一个麦克风图标），基于语音的网站交互模式，甚至可以直接跟浏览器对话，这些功能代表了Web领域的巨大飞跃，除了为用户提供良好的交互体验外，它们大大增强了Web辅助功能。

目前移动端设备对Web Speech API的支持并不理想，移动版Chrome支持较好，iOS只支持“SpeechSynthesis”的部分API，而Android 5.0+原生浏览器则支持SpeechSynthesisUtterance。

| 名称 | 说明 | iOS6.0+ | Android 5.0+ |
| - | - | - | - |
| SpeechRecognition | 实现语音识别 | 不支持 | 不支持 |
| SpeechSynthesisUtterance | 实现文本转语音合成 | 支持 | 支持 |
| SpeechGrammar | 实现自定义语法创建 | 不支持 | 不支持 |

[检测当前设备对Web Speech支持情况](http://tgideas.qq.com/book/danceonfingers/chapter2/section2/webspeech/)

#### Web Audio API
Audio对象（audio标签）提供的只是音频文件的播放，而Web Audio则是能够对音频数据进行分析，处理的能力，如混音和过滤，Web Audio API对系统的要求为iOS 6+， Android 4.4+。

[检测当前设备对Web Audio支持情况](http://sy.qq.com/brucewan/device-api/web-audio.html)

Web Audio API可以让CSS3动画跟随背景音乐舞动，甚至可以尝试H5音乐创作，制作变声应用等：
- [基于Web Aduio API的超声波互联技术](http://v.youku.com/v_show/id_XNTk0MjQyNDMy.html)
- [MDN: Web Audio API 基本用法](https://developer.mozilla.org/zh-CN/docs/Web/API/Web_Audio_API)
- [Web Audio API的使用相关的文章](https://segmentfault.com/a/1190000005715615)
- [利用HTML5 Web Audio API给网页JS交互增加声音](http://www.zhangxinxu.com/wordpress/2017/06/html5-web-audio-api-js-ux-voice/)

#### 地理定位（Geolocation API）
Geolocation API将用户当前地理位置信息共享给信任的站点，位置信息来源包括GPS、IP地址、RFID、WiFi、蓝牙的MAC地址、以及GSM/CDMS的ID等，规范中没有规定使用这些信息的优先级，一般而言，使用移动网络环境比WiFi更准确。

主流的移动设备都支持Geolocation API，兼容iOS6+， Android 2.3+，网页在请求获取用户地理相关信息时，需要用户授权，iOS10需要HTTPS协议才能使用Geolocation API。

通过Geolocation API结合腾讯地图，获取用户所在的城市，并在地图上标注出来（根据手机的网络情况，定位可能有偏差）[实例地址](https://tgideas.qq.com/book/danceonfingers/chapter2/section2/geolocation.html)

通过地理定位，可以做很多有意思事情，提升体验，通过getCurrentPosition()获取地址位置，自动帮用户选取所在的城市，减少不必要操作。还可以实现“周围的人”、“位置签到”、“在线人数地图”等功能，提升页面的互动性。通过watchPostion()实时获取用户的位置，实现实时导航和LBS地图增强互动。

#### 陀螺仪
**deviceOrientation API**
deviceOrientation封装了方向传感器数据的事件，可以获取用户设备静止状态下的方向数据，如手机所处的角度、方位、朝向等。

可以通过为window对象绑定时间处理函数，当加速计检测到设备方位发生改变时，就会被触发deviceOrientation事件，会返回deviceOrientationEvent Type的对象，包含如下3种属性：
- alpha，设置指向的方向，环绕z轴旋转的角度，alpha取值范围 0 ~ 360,是根据设备的指南针设定情况而定的，一般来说，设备指向正北方向时为0
- beta，设备环绕x轴旋转的角度，beta取值范围-180 ~ 180
- gamma，设备环绕y轴旋转的角度， gamma取值范围 -90 ~ 90

将设备仿造在水平面、屏幕顶端指向正北方，则其方向信息为alpha:0, beta:0, gamma:0

[通过deviceOrientation获取用户设备的坐标信息示例](http://tgideas.qq.com/book/danceonfingers/chapter2/section2/deviceorientation.htm)

deviceOrientation回调函数，主要根据event对象的3个方向参数来确定设备的旋转角度
```js
windo.addEventListener('deviceOrientation', function(event){
  // event.alpha / beta / gamma
}, true);
```

**deviceMotion API**
封装了运动传感器数据事件，可以获取手机运动状态下的运动加速度数据。

与deviceOrientation一样，可以对window绑定事件，当设备运动状态出现加速或减速时，就会触发deviceMotion事件，同时会返回deviceMotionEvent type的对象，具体包含如下3个属性：
- accelaration对象，值为(x,y,z),指定设备相对地球在x轴、y轴、z轴上的加速状态
- accelarationIncludeingGravity对象，值同上，但会考虑地球重力因数
- rotationRate，值为(alpha, beta, gamma)，指定设备在各个轴上每秒运动多少度，这个值与deviceOrientation事件的属性的差别在于，前者只是相对于初始状态的差值，表示的是角度，后者相对于之前的某个瞬间的差值时间比，表示的是变化的速度。

基于deviceMotion可以判断用户移动的角度实现时差效果（可参考Parallax.JS）、全景漫游（如地图的街景体验）、控制游戏（如控制车辆移动的方向或滚珠的移动方向）、可以监控设备的加速即一些手势识别（如摇一摇）。

[通过deviceMotion获取用户设备的坐标信息示例](http://tgideas.qq.com/book/danceonfingers/chapter2/section2/devicemotion.htm)

#### 设备震动（Vibration API）
大多数的移动设备都包含了可震动的装置，基于Web的Vibration API在W3C处于建议阶段，目前浏览器设备支持不足，iOS系统浏览器完全不支持，而Android 4.4+部分支持。

该API非常简单，参数duration代表时间毫秒或以毫秒为单位的数组，即震动时长。
```js
navigator.vibrate(duration);
navigator.vibrate(2000); // 震动2s，
navigator.vibrate([1000, 1000, 2000]); // 震动1s后，暂停1s,然后震动2s
```

[检测设备是否支持Vibration](http://tgideas.qq.com/book/danceonfingers/chapter2/section2/vibration/)

#### 电池状态（Battery API）
可获取用户移动设备的电池信息，目前该API没有得到主流支持。

[检测设备是否支持Battery](http://tgideas.qq.com/book/danceonfingers/chapter2/section2/Battery.htm)

#### 环境光（Ambient Light）
该API定义了一些事件，可以提供源于周围光亮程度的信息，这通过设备的关感应器来测量，该感应器会提取辉度信息。

[Android Firefox支持此功能](http://tgideas.qq.com/book/danceonfingers/chapter2/section2/ambient-light.htm)

#### 网络信息
想要获取移动设备的网络信息，一般通过两种方式：
- navigator的属性online/offline
- navigator.connection对象(Network Information API)

该API在移动设备上的支持程度不错，可以判断设备是否连接网络，若判断到没有链接网络，则可以先把请求放入队列等待，同时判断网络种类，进而请求不同的资源，达到访问速度和效果均衡。

[检测设备对网络信息的支持](http://tgideas.qq.com/book/danceonfingers/chapter2/section2/network/)

#### 基于平台的JSSDK
如微信，手机QQ，支付宝等，具体可以查看相关的开放文档。