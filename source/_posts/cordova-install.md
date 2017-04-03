---
title: cordova / ionic 安装配置出错解决办法集合
categories:
  - fe
tags:
  - fe
date: 2017-04-02 11:17:52
updated: 2017-04-02 11:17:52
---

此文中主要是对cordova的环境配置中遇到的坑进行记录。

### cordova

需要node， npm，若是安卓平台： android sdk、 jdk

使用官方教程，安装cordova后用`cordova create hello me.king.hello helloword`创建一个行项目。 第一个hello为项目名,也是目录名，`me.king.hello`为包名，最后的helloworld为app的名字

然后在项目中执行`cordova platform add android --save`添加android平台

然后对www文件夾下的js，css，html文件进行修改

cordova 安装过程中，发现在windows10下使用vscode的集成命令行工具中执行`cordova run andriod`就会报错
```shell
Error: Failed to find 'ANDROID_HOME' environment variable. Try setting setting it manually.
Failed to find 'android' command in your 'PATH'. Try update your 'PATH' to include path to valid SDK directory.
```
可以用windows自带的cmd执行命令，查看是否是sdk的问题，之外就是sdk环境变量配置确实有错误，

andriod SDK环境变量配置没问题，然后用cmd执行`cordova run andriod`命令之后

若发现报错：
`Error: Could not find gradle wrapper within Android SDK. Might need to update your Android SDK.` 
则可以选择使用下载官方的sdk tools压缩文件，覆盖原sdk目录下的tools文件夹即可。参考如下链接：[cordova gradle wrapper问题](http://stackoverflow.com/questions/31310182/error-could-not-find-gradle-wrapper-within-android-sdk-might-need-to-update-yo)

此时会下载大量的文件，耐心等待即可

然后继续运行`cordova run andriod`，若报没有模拟器，可以在命令后添加选项`--device`用已经开启debug模式连接到电脑上的真机运行。

其中，使用虚拟机调试前需要新建一个android虚拟设备，才能调试

最后就是虚拟设备能卡死人~ 建议使用真机。

### ionic
ionic 的安装过程中，按照[官方流程](http://ionicframework.com/getting-started/)

1. `npm install -g cordova ionic`
2. `ionic start --v2 myApp tabs`
3. `cd myApp`
4. `ionic serve`

在`ionic serve`输入后，一般第一次会让你选择使用什么地址进行访问，这时可以选择localhost， 然后就会在默认浏览器的界面中弹出一个8100端口的地址，然后就可以愉快玩耍了

但是若想要通过ip访问，需要加上`ionic serve --address 某地址` 或者直接使用`--all 或 -a` 选项表示允许所有地址进行访问，然后用`ip:port`的方式访问即可。

可以使用`ionic --help`来查看其他命令

使用cordova的添加平台命令添加android环境`cordova platform add android`

打包为apk可以使用`ionic build android`然后根据输出找到对应的apk即可

可以参考如下链接：[angular.js和ionic框架搭建一个webApp（适合对angular有基础）](http://www.jianshu.com/p/ea0dcf1d31c9)