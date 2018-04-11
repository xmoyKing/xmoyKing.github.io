---
title: 精通CSS笔记-Roma-Italia
date: 2017-02-10 09:47:36
updated: 2017-02-10
categories: css
tags: [css, CSS-Mastery, Note]
---

学习内容：
1. 1080布局和网格
2. 高级CSS2和CSS3特性
3. 字体链接和更好的Web排版
4. 用Ajax和jQuery增加交互

## 1080布局和网格 ##

960像素是理想的宽度，对于800x600以上的分辨率，960是一个相当神奇的数字，可以被2，3，4，5，6，8，10，12，15和16整除，为分割网格提供多种选择。  
而1080除了不能被16整除，其他都可以。

网格包含行，列，外边距，隔离带，流水线和其他部分。网格主要用于垂直分割。


## 高级CSS2和CSS3特性 ##
1. 相邻同胞选择器
2. 属性选择器
3. box-shadow
4. opacity
5. RGBa
6. content
7. 多栏
8. text-overflow
9. 多背景
10. @font-face
11. min-/max-width和height
12. PNG图像中的alpha透明度

## 标点符号悬挂 ##
标点符号悬挂把标点符号放到文本块的外边，从而避免影响文本的视觉连贯性。
![标点符号悬挂][./1.png]

HTML使用实体`&ldquo; &rsquo; &rdquo;` 表示引号。CSS代码使用`text-indent: -.3em;`实现

```css
#featurettel p {
    text-indent: -.3em;
}
```

## 多栏文本布局 ##
使用`colume-count: 2; colume-gap: 20px;`设置多栏，`colume-rule: 1px solide #ccc;`可以设置栏之间的边框。

## @font-face ##
@font-face能让字体显示HTML文本，而不需要考虑用户的机器上是否安装了这种字体，通常称为字体链接或字体嵌入，而不用在文档开头使用
```css
body {
    font-family: xxxx;
}
```
可以这样使用@font-face
```css
@font-face {
    font-family: "Xxxx font name"; /* 这里的名称可以自定义 */
    src: url(xxx/xxxx.otf);
}
/* 然后在其他地方使用font-family引用即可 */
h1 {
    font-family: "Xxxx font name";
}
```

Cufon可以当做sIFR和@font-face之间的过渡手段，使用程序如下：
1. 下载Cufon脚本文件
2. 使用Cufon生成器上传所选字体，然后得到第二个脚本文件
3. 在文档头添加对Cufon脚本和生成器脚本的引用
4. 最后在body标签结束前添加如下代码，防止IE闪烁。`<script type="text/javascript">Cufon.now();</script>`  
在文档头中指定那些HTML元素或选择器应替换为你选择的字体：
```HTML
<script type="text/javascript">
Cufon.replace('h1')('h2')('p');
/* 支持使用jQuery等库的选择器 */
Cufon.replace('#header h2, #header ul a');
</script>
```
5. Cufon替换的文本也可以在CSS中修改

<iframe src="./roma/index.html" frameborder="0" width="100%" height="500"></iframe>
