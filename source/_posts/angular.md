---
title: Angular2快速入门-Hello World
categories: Angular
tags:
  - TypeScript
  - Angular
  - 揭秘 Angular2
date: 2017-10-01 17:04:53
updated:
---

一步一步的学习手动搭建简单的Angular2开发环境。

本系列笔记来自[《揭秘 Angular2》](https://github.com/angular-programming)一书。

[DEMO源码](https://github.com/xmoyKing/Angular2-Demo-Hello-World)

### Hello World
先从Hello World开始，一个字母一个字母的码~

现有大局观，从项目目录结构开始：
```ts
// 项目根目录
- package.json // 项目依赖包配置文件
- tsconfig.json // 配置TypeScript编译器的编译参数
- webpack.config.json //Webpack的配置文件
- index.html // 宿主页面，应用访问页面
- src
  - app.component.html // 组件对应的模版文件
  - app.component.ts // 定义组件
  - app.module.ts // 定义AppModule模块，用来组织其他一些功能紧密的相关代码块
  - main.ts // main.ts文件作为项目入口文件
```

#### package.json
项目依赖包配置文件,描述npm包的所有的相关信息。
其中scripts配置用npm调用的一些脚本，或封装一些命令。
```ts
{
  "name": "HelloWorld",
  "version": "1.0.0",
  "description": "Hello-world project for Angular2",
  "scripts": {
    "server": "webpack-dev-server --inline --colors --progress --port 3000",
    "start": "npm run server"
  },
  "license": "MIT",
  "devDependencies": {
    "@types/core-js": "~0.9.0",
    "ts-loader": "~1.2.0",
    "typescript": "~2.0.0",
    "webpack": "~1.12.0",
    "webpack-dev-server": "~1.14.0"
  },
  "dependencies": {
    "@angular/common": "2.0.0",
    "@angular/compiler": "2.0.0",
    "@angular/core": "2.0.0",
    "@angular/platform-browser": "2.0.0",
    "@angular/platform-browser-dynamic": "2.0.0",
    "core-js": "~2.4.1",
    "reflect-metadata": "~0.8.1",
    "rxjs": "5.0.0-bata.12",
    "zone.js": "~0.6.26"
  }
}
```
reflect-metadata和zone.js作为Angular项目依赖的ployfill。

**提前注明测试运行结果：**
> 非常遗憾，上述代码由于Angular依赖包的版本问题，虽然npm start后webpack会报错，提示找不到'Promise','IterableIterator','PropertyKey'等东西，但localhost:3000却可以运行，且webpack-dev-sever也可以实现动态修改代码，同步更新到浏览器。

官方源码中的fix方式为锁定依赖包版本号, 具体如下：
```ts
{
  "name": "HelloWorld",
  "version": "1.0.0",
  "description": "Hello-world project for Angular 2",
  "scripts": {
    "server": "webpack-dev-server --inline --colors --progress --port 3000",
    "start": "npm run server"
  },
  "license": "MIT",
  "devDependencies": {
    "@types/core-js": "0.9.34",
    "ts-loader": "1.2.0",
    "typescript": "2.0.0",
    "webpack": "1.12.9",
    "webpack-dev-server": "1.14.0"
  },
  "dependencies": {
    "@angular/common": "2.0.0",
    "@angular/compiler": "2.0.0",
    "@angular/core": "2.0.0",
    "@angular/platform-browser": "2.0.0",
    "@angular/platform-browser-dynamic": "2.0.0",
    "core-js": "2.4.1",
    "reflect-metadata": "0.1.8",
    "rxjs": "5.0.0-beta.12",
    "zone.js": "0.6.26"
  }
}
```

#### tsconfig.json文件
tsconfig.json放在根目录下，配置TypeScript编译器的编译参数。主要的配置参数说明如下：
- module 组织代码的方式
- target 编译的目标平台（ES3/ES5/ES6）
- sourceMap 把ts文件编译成js文件时，是否生成对应的SourceMap文件
- emitDecoratorMetadata 让TypeScript支持为带有装饰器的声明生成元数据
- experimentalDecorators 是否启用实验性装饰器特性
- typeRoots 指定第三方库的类型定义文件的存放位置，一般为node_modules/@types文件夹
```ts
{
  "compilerOptions": {
    "module": "commonjs",
    "target": "es5",
    "moduleResolution": "node",
    "sourceMap": true,
    "emitDecoratorMetadata": true,
    "experimentalDecorators": true,
    "removeComments": false,
    "noImplicitAny": true,
    "suppressImplicitAnyIndexErrors": true,
    "typeRoots": [
      "./node_modules/@types/"
    ]
  },
  "compileOnSave": true,
  "exclude": [
    "node_modules"
  ]
}
```

#### 源文件

src/app.component.ts文件中为创建组件的代码
```ts
// app.component.ts
import { Component } from '@angular/core'; //从Angular基础包@Angular/core中引入组件模块

// 通过@Component装饰器来告诉Angular怎么创建这个组件
@Component({
  selector: 'hello-world', // 定义该组件的DOM元素名称
  templateUrl: 'src/app.component.html' // 定义组件引入所需的模版
})

export class AppComponent {} // 定义组件类并对外输出该类，这样其他文件就可以通过这个类名引用本组件
```

src/app.component.html文件为对应组件的模版文件，内容为html组件模版
```html
<h3> Hello World</h3>
```

Angular应用需要用模块来组织一些功能紧密相关的代码块，每个应用至少有一个模块，习惯上把其称为AppModule，定义在src目录下app.module.ts。
```ts
// app.module.ts
import { NgModule } from '@angular/core'; // NgModule用于定义模块的装饰器
import { BrowserModule } from '@angular/platform-browser';
import { AppComponent } from './app.component';

@NgModule({
  declarations: [AppComponent], // declarations 导入模块依赖的组件、指令等
  imports: [BrowserModule], // imports导入其他所需的模块，在imports属性中配置，作为公用模块供全局调用。几乎每个应用都需要导入BrowserModule模块、其内注册了关键的Provider等通用指令
  bootstrap: [AppComponent] // bootstrap标记出引导组件，在Angular启动应用时，将被标记的组件渲染到模版中
})

export class AppModule{}
```

main.ts文件作为项目入口文件，通过这个文件来串联整个项目，在src目录下创建。
启动应用主要依赖于Angular自带的platformBrowserDynamic函数和应用模块AppModule，然后调用bootstrapModule方法来编译启动AppModule模块。
```ts
// main.ts
// import 'reflect-metadata';
import 'core-js';
import 'zone.js';
import { platformBrowserDynamic } from '@angular/platform-browser-dynamic';
import { AppModule } from './app.module';

platformBrowserDynamic()
  .bootstrapModule(AppModule)
  .catch( (err: any) => console.error(err));
```

宿主页面index.html,
```html
<!DOCTYPE html>

<html lang="en">
    <head>
        <title>Angular2 Hello World</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <base href="/">
    </head>
    <body>
        <hello-world>加载中...</hello-world>
        <script src="bundle.js"></script>
    </body>
</html>
```
其中bundle.js是Webpack打包命令运行后生成的文件，hello-world标签就是在根组件app.component.ts中定义的selector。

以上Hello World项目就搭建完成了，但运行需通过打包工具（Webpack/Gulp/Grunt/FIS3等）打包编译后才能运行。此处采用的打包工具是在devDependencies中定义的webpack，webpack-dev-server是一个小型服务器工具，项目在开发阶段可以在这个服务器上运行，Webpack的配置文件为webpack.config.js，文件放在根目录下。
```ts
// webpack.config.js
var webpack = require('webpack');
var path = require('path');

module.exports = {
  entry: './src/main.ts', // 页面入口文件配置，可以是一个或多个入口文件
  output: {
    // 指定打包后的输出文件，这个文件会被引入到index.html中
    filename: './bundle.js'
  },

  resolve: { // 定义了解析模块路径
    root: [path.join(__dirname, 'src')],
    extensions: ['', '.ts', '.js'] // extensions用来指定模块的后缀，这样就可以在引入模块时不需要写后缀了，会自动补全
  },

  module: {
    loaders: [ // 最关键的配置项，表明Webpack每一类文件需要使用加载器处理
      {
        test: /\.ts$/,
        loader: 'ts-loader'
      }
    ]
  }
};
```