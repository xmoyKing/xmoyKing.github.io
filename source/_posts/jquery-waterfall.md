---
title: jQuery实现图片瀑布流展示
categories:
  - jquery
tags:
  - js
  - jquery
date: 2016-11-01 16:21:34
updated: 2016-11-01 16:21:34
---

首先给出一个瀑布流的基本要求所有图片限宽，高度自适应，横向排列，第二排第一张自动放在在第一排的最短处。

![preview](./preview.png)

HTML结构：
```html
<div class="container">
  <div class="box">
    <div class="content">
      <img src="1.jpg">
    </div>
  </div>
  <div class="box">
    <div class="content">
      <img src="2.jpg">
    </div>
  </div>
  <div class="box">
    <div class="content">
      <img src="3.jpg">
    </div>
  </div>

  ...

</div>
```

基本CSS样式:
```css
.box{
  position: relative;
  float:left;
}
.content{
  padding: 10px;
  border: 1px solid #ccc;
  box-shadow: 0 0 5px #ccc;
}
.content>img{
  with: 190px;
  height: auto;
}

```


jquery实现摆放逻辑：
```js
$(document).ready(function(){
  $(window).on('load', function(){
    imgLocation();
  });
});

// 计算所有图片的位置
function imgLocation(){
  var box = $('.box'); // 获取所有图片容器
  var boxWidth = box.eq(0).width(); // 获取图片容器宽度（不是图片宽度）
  var num = Math.floor($(window).width/boxWidth); // 计算当前窗口横排摆放图片数量
  var boxArr = []; // 容器列数数组，每个数用于保存当前列的图片总高度

  // 对每一个容器遍历
  box.each(function(i, e){
      var boxHeight = box.eq(i).height(); // 获取当前容器的高度

      if(i < num){ // 保存第一排的容器高度到boxArr中
          boxArr[i] = boxHeight;
      }else{
          var minBoxHeight = Math.min.apply(null, boxArr); // 从所有列中找到高度最小的
          var minBoxIndex = $.inArray(minBoxHeight, boxArr); // 得到最小高度所在的索引

          // 设置当前容器的位置，使用绝对定位，高为最小列高度，左为最小列左边位置
          $(e).css({
              position: 'absolute',
              top: minBoxHeight,
              left: box.eq(minBoxIndex).position().left  
          });

          // 更新添加图片后的当前列（最小高度列）的高度，加上本次容器的高度
          boxArr[minBoxIndex] += box.eq(i).height();
      }
  })
}

```

滚动加载：监听window的scroll事件
```js
// 判断滚动位置，是否需要加载
function scrollSide(){
 var box = $('.box');
 // 获取最后一个box容器的位置（其实是设定一个加载图片的基准线）
 var documentHeight = $(document).height();
 var lastboxHeight = box.last().get(0).offsetTop + Math.floor(box.last().height()/2);
 var scrollHeight = $(window).scrollTop(); // 获取滚动条高度
 // 返回true/false
 return (lastboxHeight < scrollHeight + documentHeight) ? true : false;
}

// 模拟获取到的img图片数据
var imgdata = {} 
window.onscroll = function(){
    if(scrollSide()){
        // 对图片数据遍历，构造图片html字符串
        $.each(imgdata.data, function (i, e) {
            // ...
        });
        // 然后统一加载至dom内即可（可减少dom操作次数）
        // 最后对所有图片进行位置更新即可
    }
}
```
