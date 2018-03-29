---
title: Angular2入门-依赖注入-2
categories: Angular
tags:
- JavaScript
- typescript
- angular
date: 2017-10-17 18:13:49
updated:
---

### Provider
Provider这个概念其实是一个设计模式。
Provider模式是指实现逻辑操作或数据操作的封装，以接口的方式提供给调用方使用，Provider模式有很好的可扩展性和灵活性。

在Angular中，Provider描述了注入器如何初始化标识（Token）所对应的依赖服务，它最终用于注入到组件或者其他服务中。Provider提供了一个运行时所需的依赖，注入器依靠它来创建服务对象的实例。
这个过程好比厨师（注入器）根据菜谱（Provider）制作一道名为LoggerService(标识)的菜（整个过程就是完成了一次依赖注入服务）。

其中Providers有简写形式和完整形式两种,具体如下：
```ts
// 简写形式，于完整形式等价
providers: [LoggerService]

// 完整形式
providers: [{provide: LoggerService, useClass: LoggerService}]
```
完整形式采用对象字面量来描述一个Provider的构成要素，其中provide属性可以理解为这个Provider的唯一标识（即在应用中使用的服务名），用于定位依赖值和注册Provide，而useClass属性则代表使用哪个服务类去创建实例。

Angular中Provider引进标识机制解决了AngularJS1版本的几个问题：
1. 标识是字符串，作为唯一标识（Angular会有一个标识映射表来保证唯一性）不再依赖于具体的类，可避免命名空间污染
2. 代码只依赖一个抽象的标识，不再依赖具体的实现，可以在运行时动态替换

实际上，标识既可以是字符串也可以是其他数据类型，当多个字符串同时映射到同一个标识时，会以最后一个为准，这可能会导致一些隐含的问题，为了解决标识名冲突问题，Angular引入了OpaqueToken（不透明标识），可以保证生成的标识都是唯一的。

#### Provider注册方式
Provider的主要作用是注册并返回合适的服务对象，Angular有4种常见的Provider注册形式：
- 类Provider
- 值Provider
- 别名Provider
- 工厂Provider

##### 类Provider
类Provider基于标识来指定依赖项，这种方式可以使得依赖项能够被动态指定为其他不同的具体实现，只要接口不变，对于使用方就是透明的。
典型场景就是数据渲染服务（Render），Render服务对上层应用提供的接口是固定的，但底层可以用DOM渲染方式（DomRender），也可以用Canvas渲染方式（CanvasRender），还可以用Angular Universal实现服务端渲染（ServerRender）。通过useClass属性来指定用那种渲染模式，因为渲染服务的最终接口没有变化（provide属性还是Render），对于调用者来说，业务代码也就不需修改：
```ts
// 渲染方式
var injector = Injector.resolveAndCreate([
  {provide: Render, useClass: DomRender} // DOM渲染方式
  // {provide: Render, useClass: CanvasRender} // Canvas渲染方式
  // {provide: Render, useClass: ServerRender} // 服务端渲染方式
]);

// 调用方
class ApplicationComponent {
  constructor(_render: Render){
    _render.render(); // 渲染
  }
}
```

##### 值Provider
在实际开发中，依赖的对象不一定是类，也可以是常量、字符串、对象到呢个其他数据类型，这可以方便在全局变量、系统相关参数配置等场景中，在创建Provider对象时，只需要使用useValue就可以声明一个值Provider。
```ts
// ...
let globalSetting = {
  env: 'production',
  getHost: ()=>{return 'https://www.xxxx.com'}
};

@Component({
  selector: 'some-component',
  template: `<div> ... </div>`,
  providers: [
     {provide: 'urlSetting', useValue: globalSetting}, // 对象
     {provide: 'NAME', useValue: 'SOME_NAME'}, // 常量
  ]
})
export class SomeComponent{}
```
值Provider依赖的值（通过useValue指定的值）必须在当前或者providers元数据配置之前定义，标识为NAME依赖的值在Provider中直接给出了定义（即`'SOME_NAME'`）。

##### 别名Provider
useExisting用来指定一个别名Provider，有了别名Provider就可以在一个Provider中配置多个标识，其对应的对象指向同一个实例，从而实现多个依赖，一个对象实例的作用。

假如有一个日志服务OldLoggerService，现有一个相同接口的新版服务NewLoggerService,考虑到重构代价等问题，并不去替换OldLoggerService服务被使用的地方，此时为了新旧服务同时可用，用于用useClass来积极这个问题：
```ts
providers: [
    {provide: NewLoggerService, useClass: NewLoggerService},
    {provide: OldLoggerService, useClass: NewLoggerService},
]
```
但是两个NewLoggerService却是不同的实例，而通过useExisting就可以将多个标识指向同一个实例。
```ts
providers: [
    {provide: NewLoggerService, useClass: NewLoggerService},
    {provide: OldLoggerService, useExisting: NewLoggerService},
]
```

##### 工厂Provider
有时依赖对象是不明确且动态变化的，可能需要根据运行环境、执行权限来生成，Provider需要一种动态生成依赖对象的能力，工厂Provider就用来解决这个问题，它暴露一个工厂方法，返回最终依赖的对象。

假设这样的场景，有些联系人的信息是保密的，只有特定权限的人才能看到，所以需要对每个登录用户进行鉴权。要达成目的，可以在构造函数中通过一个布尔值来判断是否有权限并返回对应的服务，在返回的服务中可以根据这个布尔值来判断是否显示联系人信息。

使用工厂Provider的注册方式需要用useFactory来声明Provider是一个工厂方法，deps是一个数组属性，指定了所需的依赖，可以注入到这个工厂方法中。
```ts
let contactServiceFactory = (_logger: LoggerService, _userService: UserService) => {
  return new contactService(_logger, _userService.user.isAuthorized);
}

export let contactServiceProvider = {
  provide: ContactService,
  useFactory: contactServiceFactory,
  deps: [LoggerService, UserService]
};
```

### 依赖注入在组件间通讯的运用
组件间通讯的几种方式中包括父组件获取子组件引用的方法（通过ViewChildren的方式），反过来，在子组件获取父组件的实例就相对麻烦一些，但每个组件里的树立都会添加到注入器的容器里，因此可通过依赖注入来找到父组件的实例。

假设有一个父组件ParentComponent和一个子组件ChildComponent：
```ts
// parent.component.ts
@Component({
  selector: 'parent',
  template: `
  <div>
    {{ name }}
    <child></child>
  </div>
  `
})
export class ParentComponent{
  name = '父组件'
}
```
现在要在子组件中获取父组件的引用，分两种情况：
**已知父组件的类型**
这种情况可以直接通过在钩子函数中注入ParentComponent来获取已知类型的父组件引用。
```ts
// child.component.ts
import { ParentComponent } from './parent.component';

@Component({
  selector: 'child',
  template: `
  <div>
    {{ name }}
    <div>{{ parent ? '获取父组件引用' : ''}}</div>
  </div>
  `
})
export class ChildComponent{
  name = '子组件';
  constructor(public parent: ParentComponent) {}
}
```

**未知父组件的类型**
一个组件可能是多个组件的子组件，有时无法直接知道父组件的类型，在Angular中，可以通过“类-接口（Class-Interface）”的方式来查找，即让父组件通过一个与“类-接口”标识同名的别名来协助查找。

“类-接口”其实是一个抽象类，但被当做接口来使用，因为接口是TypeScript里才有的概念，编译后并不存在，因此Provider的标识不能是接口，只能是JS原生支持的对象（函数、字符串、对象等），所以“类-接口”既能提供接口的强类型约束，又能当做Provider的标识来用。

首先，创建Parent抽象类，它只声明了name属性，没有实现（赋值）：
```ts
export abstract class Parent{
  name: string;
}
```
然后在ParentComponent组件的providers元数据中定义一个别名Provider，用useExisting来注入ParentComponent组件的实例：
```ts
// parent.component.ts
@Component({
  selector: 'parent',
  template: `
  <div>
    {{ name }}
    <child></child>
  </div>
  `,
  providers: [{provide: Parent, useExisting: ParentComponent}]
})
export class ParentComponent implements Parent{
  name = '父组件'
}
```
若还有多层组件，建议任何父组件都应该实现一个单独的抽象类（如上的Parent抽象类），这样子组件就可以通过注入来找到父组件的实例，比如ParentComponent组件类。子组件通过Parent这个标识找到父组件的实例：
```ts
export class ChildComponent {
  constructor(public parent: Parent){ }
}
```