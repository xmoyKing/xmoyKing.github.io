---
title: Angular2入门-依赖注入-1
categories: Angular
tags:
  - JavaScript
  - typescript
  - angular
date: 2017-10-17 09:30:04
updated:
---

依赖注入（Dependency Injection）是Angular实现重要功能的一种设计模式。大型应用通常涉及到很多组件和服务，这些组件和服务之间联系复杂多变，如何很好的管理它们之间的依赖关系是一个大问题，同时，也是一个框架是否优秀的硬指标。

传统的开发模式中，调用者负责管理所有对象的依赖，其中的循环依赖简直是噩梦，而在依赖注入模式中，这个管理权就交给了注入器（Injector），它在应用运行时（而不是发生在编译时）负责替换依赖对象，这称为控制反转（Inversion of Control，简写IoC），是依赖注入的关键。

### 依赖注入
控制反转的概念是针对面向对象设计不断复杂化而提出的一种设计原则，是一种利用面向对象编程法则来降低应用耦合的设计模式。IoC强调的是对代码引用的控制权由调用方转移到外部容器，在运行时通过某种方式（比如反射）注入进来，实现控制翻转，降低服务类之间的耦合度。

依赖注入是最常用的一种实现IoC的方式，另一种是依赖查找。

在依赖注入模式中，应用组件无需关注所依赖对象的创建或初始化过程，可以认为框架已经初始化好了，只管调用即可。依赖注入有利于应用中各模块之间的解耦，使代码更易维护。随着项目复杂度增加，各模块、组件、第三方服务等相互调用更频繁，依赖注入优势越明显。开发者专注所依赖对象的消费，无需关注这些依赖的生产过程，提高了开发效率。

以创建一个Robot类为示例展示依赖注入的好处。
```ts
// 不使用依赖注入
export class Robot {
  public head: Head;
  public arms: Arms;

  constructor(){
    this.head = new Head();
    this.arms = new Arms();
  }

  // 移动
  move(){}
}
```
Robot类现在仅包含Head、Arms等组件，行为也不复杂，但上面代码的问题在于，Robot在它自身的构造函数中创建并引用了Head和Arms的实例，这使得Robot类的扩展性差且难以测试。具体在于：
- 扩展性差
  Robot类通过Head和Arms创建了自己需要的组件，但若此时Head类的构造函数需要一个参数呢？只能通过`this.head = new Head(parameter);`的方式修改Robot类，或者需要给Robot类换一个OtherHead类。
- 难以测试
  当需要测试Robot类时，需要考虑Robot类隐藏的其他依赖，比如Head组件本身是否依赖于其他组件，且它依赖的组件是否又依赖另外的组件，Head组件的实例是否需要发送异步请求等。这些都是Robot不能控制的隐藏依赖，所以Robot很难被测试。
那么，如何才能使Robot类变得容易扩展且易于测试呢？用依赖注入的方式改造：
```ts
// 使用依赖注入
export class Robot {
  public head: Head;
  public arms: Arms;
  // 注意，此处注入的是Head和Arms的实例，不是Robot自身new出来的
  constructor(public head: Head, public arms: Arms){
    this.head =  head;
    this.arms =  arms;
  }

  // 移动
  move(){}
}
```
调用时只需要把创建好的Head和Arms实例传入即可，如此，实现了Robot类和Head、Arms类的解耦，可以注入任何Head和Arms实例到Robot类的构造函数。无论是什么类型的Head和Arms
```ts
var robot = new Robot(new Head(), new Arms());
```

依赖注入的一个典型应用场景就是测试，使用依赖注入方式编写的代码，测试人员在做场景覆盖测试时，基本上不需要修改被测试的程序，只需要注入依赖对象到被测试程序中即可。以测试Robot组件为例，将Head和Arms的mock对象传入到Robot类的构造函数中：
```ts
class MockHead extends Head{
  head = '头部';
}

class MockArms extends Arms{
  arms = '手臂';
}

var robot = new Robot(new MockHead(), new MockArms());
```
依赖注入通过注入服务的方式替代了组件里初始化所依赖的对象，从而避免了组件之间的紧耦合，但还不够彻底，因为使用Robot类时还需要手动创建Head、Arms以及Robot的实例，为了减少重复操作，可以通过创建一个Robot的工厂类来解决(工厂类并不是依赖注入的常用做法，仅仅为了引出注入器Injector的概念)：
```ts
// Robot工厂类
import { Head, Arms, Robot } from './robot';

export class RobotFactory {
  createRobot(){
    let robot = new Robot(this.createHead(), this.createArms());
    return robot;
  }

  createHead(){
    return new Head();
  }
  createArms(){
    return new Arms();
  }
}
```
Angular的依赖注入框架（Dependency Injection Framework）就像上面的工厂类一样，替开发者完成了各种实例化的过程，这样开发者不需要去关心需要定义那些依赖、以及把这些依赖注入给谁，因为依赖注入提供了注入器（Injector），它会帮助创建需要的类实例,例如要创建一个Robot类实例：
```ts
var injector = new Injector();
var robot = injector.get(Robot);
```
有了注入器，Robot就不需要知道如何创建它所依赖的Head和Arms，用户也不需要知道如何生产一个Robot，同时也不需要维护一个巨大的工厂类。

### Angular中的依赖注入
通过上面Robot的例子了解了依赖注入的概念，了解Angular的依赖注入需了解3个概念：
- 注入器（Injector）：就像工厂，提供了一系列的接口用于创建依赖对象的实例
- Provider：用于配置注入器，注入器通过它来创建被依赖对象的实例，Provider把标识（Token）映射到工厂方法，被依赖的对象就是通过该方法来创建的
- 依赖（Dependence）：指定了被依赖对象的类型，注入器会根据此类型创建对应的对象
在依赖注入中，注入器是粘合剂，它链接了调用方和被依赖方，注入器根据Provider的配置来生成依赖对象，调用方根据Provider提供的标识告诉注入器来获取被依赖的对象。

以Angular中如何使用依赖注入获取一个Robot实例为例：
```ts
var injector = new Injector(...);
var robot = injector.get(Robot);
robot.move(); // 调用robot方法
```
Injector()的实现如下，该类暴露一些静态方法用来创建injector注入器：
```ts
import { ReflectiveInjector } from '@angular/core';
var injector = ReflectiveInjector.resolveAndCreate([
  Robot,
  Head,
  Arms
]);
```
resolveAndCreate()是一个工厂方法，它通过接收Provider数组来创建injector注入器，Provider数组说明了如何创建这些依赖，事实上，上面的Provider数组相当于下面代码的简写：
```ts
var injector = ReflectiveInjector.resolveAndCreate([
  {provide: Robot, useClass: Robot},
  {provide: Head, useClass: Head},
  {provide: Arms, useClass: Arms}
]);
```
Provider对象字面量（形如`{provide: Robot, useClass: Robot}`）把一个标识映射到一个可配置的对象，这个标识可以是一个类名，也可以是字符串。有了Provider, Angular不仅知道使用了那些依赖，也知道这些依赖是如何被创建的。
那么注入器到底是如何知道初始化Robot需要的依赖是Head和Arms呢？ 其实，在Robot的构造函数声明中，它的参数说明了Robot类需要的依赖：
```ts
constructor(public head: Head, public arms: Arms){}
```
在Angular中，上述关于创建注入器（即`var injector = ReflectiveInjector.resolveAndCreate([...])`）是不需要开发者编写的，Angular的依赖注入框架已经完成了注入器的生成和调用。

#### 在组件中注入服务
Angular在底层做了大量初始化工作，这大大简化了创建依赖注入的过程，在组件中使用依赖注入需要完成下面3步：
1. 通过import导入被依赖对象的服务
2. 在组件中配置注入器，在启动组件时，Angular会读取@Component装饰器里的providers元数据，它是一个数组，配置了该组件需要使用到的所有依赖，Angular的依赖注入框架会根据这个列表去创建对应对象的实例
3. 在组件构造函数中声明需要注入的依赖，注入器会根据构造函数上的声明，在组件初始化时通过第二步中providers元数据配置依赖，为构造函数提供对应的依赖服务，最终完成注入的过程。

以通讯录为例，展示如何实现在组件里注入服务：
```ts
// app.component.ts
import { Component } from '@angular/core';
// 1. 导入被依赖的服务
import { ContactService } from './shared/contact.service';
import { LoggerService } from './shared/logger.service';
import { UserService } from './shared/user.service';

@Component({
  moduleId: module.id,
  selector: 'contact-app',
  // 2. 在组件中配置注入器
  providers: [ContactService, LoggerService, UserService],
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class ContactAppComponent {
  // 3.在组件构造函数中声明需要注入的依赖
  constructor(
    contactService: ContactService,
    logger: LoggerService,
    userService: UserService
  ){}
}
```
在ContactAppComponent这个根组件中配置了providers元数据，这使得ContactAppComponent及其所有子组件，都能共享由根组件注入器创建的实例。

但每个组件都可以有自己的注入器，通过依赖注入到该组件的每个服务都维持单例，若某个组件不希望复用从根组件注入器获取的服务，可以在自己的注入器中以新的配置重新注入，这被称为“**层级注入**”，是Angular依赖注入的另一个特性。

如下例，CollectionComponent是ContactAppComponent的子组件，它并没有在@Component中添加providers元数据来注入ContactService服务，但是依然可以在构造函数中获取到ContactService服务的实例：
```ts
// collection.component.ts
import { Component, OnInit } from '@angular/core';
import { ContactService } from './shared/contact.service';

@Component({
  // 注：此处没有声明providers元数据
  selector: 'call-record',
  templateUrl: 'app/collection/collection.component.html',
  styleUrls: ['app/collection/collection.component.css']
})
export class CollectionComponent implements OnInit {
  collections: any = [];
  contacts: any = {};

  constructor(private _contactService: ContactService) {}
}
```
ContactService更新是整个模块内通用的服务，适合在全局注入，整个模块共享一个实例即可，无需在具体组件中创建相应的实例。

#### 在服务中注入服务
除了组件依赖服务，服务间的相互调用也很常见。例如在ContactService服务中，若希望在服务异常时记录错误信息，可以创建一个单独的日志服务来处理。

先定义一个简单的日志服务：
```ts
import { Injectable } from '@angular/core';

@Injectable()
export class LoggerService {
  log(message: string){
    console.log(message);
  }
}
```
在ContactService服务中注入LoggerService:
```ts
// contact.service.ts
import { Injectable } from '@angular/core';
import { LoggerService } from './logger.service';
import { UserService } from './user.service';

@Injectable() // 1.添加@Injectable装饰器
export class ContactService{
  // 2. 构造函数中注入所依赖的服务
  constructor( _logger: LoggerService, _userService: UserService ){}

  getCollections(){
    this._logger.log('Getting contacts ... ');
  }
}
```
然后需要在组件中注册这个日志服务，日志服务可被多个模块调用，一般在根模块的providers元数据中注册它。
```ts
// 3. 在providers元数据中注册服务
providers: [ContactService, LoggerService, UserService]
```
注意ContactService, LoggerService虽然都使用了@Injectable装饰器，但LoggerService的装饰器不是必须的（但推荐在服务定义时都写上），因为只有当一个服务依赖其他服务的时候才需要用@Injectable显示装饰。而LoggerService没有依赖其他服务，所以它可以不用@Injectable装饰，而ContactService服务依赖了其他服务，@Injectable是必须的。

#### 在模块中注入服务
在根组件中注入的服务，所有的子组件都能共享这个服务，当然通过模块注入服务也可以达到一样的效果。

但模块中注入服务和之前的注入场景不同，Angular在启动程序时会启动一个根模块，并加载它所依赖的其他模块，此时会生成一个全局的根注入器，由该注入器创建的依赖注入对象在整个应用级别都可见，并共享一个实例。
同理，根模块会指定一个根组件并启动，由该根组件添加的依赖注入对象是组件树级别可见的，在根组件以及子组件中共享一个实例。

在模块中添加依赖注入如下例：
```ts
// app.module.ts
import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { AppComponent } from './app.component';
import { LoggerService } from './shared/logger.service';
import { UserService } from './shared/user.service';

// 假设有单独的通讯录模块
import { ContactModule } from './contact.module';

@NgModule({
  imports: [
    BrowserModule,
    ContactModule
  ],
  declarations: [AppComponent],
  providers: [LoggerService, UserService],
  bootstrap: [AppComponent]
})
export class AppModule{}
```
Angular倡导模块化开发，所以将通讯录功能作为独立的功能模块封装到ContactModule模块中，并通过import导入，实现模块间的松耦合。在providers元数据中注册了LoggerService、UserService两个服务，可以在应用级别内的其他模块都共用这两个服务。

Angular中没有模块级作用域这个概念，只有应用级别作用域和组件级别作用域，这是为了模块的扩展性，一个应用通常由多个模块合并而成，在@NgModule里注册的服务默认就可在整个应用内可用。

**注：**延迟加载模块是例外，模块的延迟加载使得应用在启动时不被载入，而是结合路由配置，在需要时才动态加载相应模块。Angular会对延迟加载模块初始化一个新的执行上下文，并创建一个新的注入器，在该注入器注入的依赖只在该模块内部可见，算是模块级作用域的特例。

若在多个模块中都注入相同标识的服务怎么办？假设在根模块先后导入了ContactModule和MsgModule两个模块`import: [ContactModule, MsgModule]`。
而在ContactModule和MsgModule模块都注入了相同Token标识的服务，因为根注入器只有一个，后面初始化的模块服务会覆盖前面初始化的模块服务。MsgModule初始化的服务会覆盖ContactModule中初始化的服务，而且即使是ContactModule模块里的组件，这些组件若引入的是同一个Token标识的服务，那么这些组件引入的服务还依然会是MsgModule模块里注入的那个服务实例。

另外一个情况，假如ContactModule导入MsgModule，这种情况下，应用里使用的服务会是ContactModule中的注入服务，而不是MsgModule里的，按这种结论延伸，在根模块里注入的服务始终都是有最高优先级的，所以根模块里注入的服务可以放心使用。

推荐是在根模块中集中管理其他模块的导入，通过providers元数据完成配置；另外，可以利用模块延迟加载的特性，在延迟模块中注入依赖；或者在模块的根组件中注入，这些都可以避免多模块的同标识污染问题。

而服务是在模块中注入还是在根组件中注入？
主要取决于该服务的应用场景，在模块中注入的服务的作用域是应用级的，比如日志等工具类服务可能会在多个模块中调用，更适合模块中注入。而类似ContactService、MgsService等与业务场景相关的服务，可在相关模块的根组件中注入。由于存在延迟加载模块调用得不到组件级作用域里服务的情况，若一个服务需要被延迟加载的模块调用，也应该在根模块中注入。最后，若不确定一个服务将来是否会被外部模块调用，可以优先考虑在模块中注册。

#### 层级注入
Angular以组件为基础，而项目中组件会有层层嵌套的情况，这种组织关系组成了组件树。根组件下面是各层级的子组件，被注入的依赖对象就像是每棵树上的果实，可以出现在任何层级的任何组件中，每个组件可以拥有一个或多个依赖对象的注入，每个依赖对象对于注入器而言都是单例。更深入的是，依赖注入可以传递到子孙组件中去，子组件可以共享父组件中注入的实例，无需再创建。

每个组件都有自己对应的注入器(但不是每个组件都为自己创建独立的注入器，也有可能是共享其他组件的注入器)，由这个注入器创建的Provider都是单例，这是组件级别的单例，跟AngularJS1不一样，AngularJS1只有全局单例。

以如下通讯录别表为例，假设通讯录需要给个通讯录子项生成一个唯一标识，可以在子组件中注入随机数服务来实现：
```ts
// 生成唯一标识的服务
@Injectable()
export class Random {
  constructor(){
    this.num = Math.random();
  }
}

// 子组件A
@Component({
  selector: 'contact-a',
  providers: [Random], // 单例
  template:`<div>ContactA: {{ random.num }}</div>`
})
export class ContactAComponent {
  constructor(r: Random){
    this.random = r;
  }
}

// 子组件B
@Component({
  selector: 'contact-b',
  providers: [Random], // 单例
  template:`<div>ContactB: {{ random.num }}</div>`
})
export class ContactBComponent {
  constructor(r: Random){
    this.random = r;
  }
}

// 父组件
@Component({
  selector: 'contact-list',
  template:`
  Contact List:
  <contact-a></contact-a>
  <contact-b></contact-b>
  `
})
export class ContactListComponent {
  constructor(){ }
}
```
上述代码的ContactA和ContactB输出的随机数完全不同，说明每个子组件都创建了自己独立的注入器，也就是说通过依赖注入的Random服务都是独立的，若将providers元数据配置改一下，把注入器提升到父组件中，那么两个子组件输出的随机数结果是一样的。

Angular这种灵活的设计引出了一个问题：是在根组件还是在子组件中进行服务注入？
这取决于想让注入的依赖服务具有全局性还是局部性，由于每个注入器总是将它提供的服务维持单例，因此，若不需要针对每个组件都提供独立的服务单例，就可以在根组件注入，整个组件树共享根注入器提供的服务实例，如日志工具类等。反之，就应该在各子组件中配置providers元数据来注入服务。

另一个问题：Angular是如何查找到合适的服务实例的呢？在组件的构造函数试图注入某个服务的时候，Angular会先从当前组件的注入器查找，找不到就继续往父组件的注入器查找，然后直到根组件注入器，最后到应用根注入器（即模块注入器），此时若还是找不到则报错。
**注：**限定依赖注入是例外，它可以控制查找的范围，即使找不到也不会报错。

注入服务的查找路径如下图：
![注入服务的查找路径](1.png)

#### 注入到派生组件
一个组件可以派生（Inherit）自另外一个组件，对应有继承关系的组件，当父类组件和派生类组件有相同的依赖注入时，若父类组件注入了这些依赖，派生组件也需要注入这些相同的依赖，并在派生类组件的构造函数中通过super()往上传递。

组件本质上是一个类，而类有继承的关系，所以一个组件可以继承另外一个组件。但派生类组件不能继承父类组件的注入器，二者的注入器对象并没有任何关系，而且需要注意的是，因为父类组件的运行可能需要依赖注入某些服务，所以派生类组件也必须注入父类组件依赖的服务，然后调用super()将对应的注入服务传递到父类。
父组件跟子组件，父类组件跟派生类组件的称谓，前者是聚合关系，后则是继承关系。

假设需要对返回的通讯录列表排序，可以创建一个实现排序功能的派生组件：
```ts
// 父类组件
@Component({
  selector: 'contact-app',
  providers: [ContactService],
  templateUrl: 'app/contact-app.component.html'
})
export class ContactAppComponent implements OnInit {
  collections: any = {};
  constructor(protected _contactService: ContactService){}

  ngOnInit(){
    this._contactService.getCollections().subscribe(data => {
      this.collections = data;
      this.afterGetContacts();
    });
  }
  protected afterGetContacts(){}
}
```
实现排序功能的派生组件：
```ts
// 派生组件
@Component({
  selector: 'contact-app',
  providers: [ContactService],
  templateUrl: 'app/contact-app.component.html'
})
// 继承ContactAppComponent组件
export class SortedContactAppComponent extends ContactAppComponent {
  // 在派生组件中注入
  constructor(protected _contactService: ContactService){
    super(_contactService); // 往父类组件传递
  }

  ngOnInit(){}

  protected afterGetContacts(){
    this.collections = this.collections.sort((h1,h2)=>{
      return h1.name < h2.name ? -1 : (h1.name > h2.name ? 1 : 0);
    });
  }
}
```
在派生组件的providers注入是必须的，因为注入器不能从父类继承，但若把providers配置ContactService放到模块中，那么父类和派生类都不需要配置了，因为它们都共享一个ContactService实例。

Angular推荐的最佳实践是构造函数越简单越好，尽量只负责类似变量初始化这样的操作，业务逻辑应移到ngOnInit()中处理，这有利于组件的单元测试。

#### 限定方式的依赖注入
一般的，注入都是假定依赖对象是存在的，但实际上并非如此，比如上层提供的Provider被移除，导致了之前注入的依赖可能已经不存在了，此时按照普通的依赖注入方式进行相关服务的调用会出错。所以Angular依赖注入框架提供了@Optional和@Host装饰器来解决这个问题。

Angular的限定注入方式使得开发者能够修改默认的依赖查找规则，@Optional可以兼容依赖不存在的情况，提供系统健壮性，@Host可以限定查找规则，明确实例初始化的位置，避免一些莫名的共享对象问题。

在Angular中实现可选注入很简单，在宿主组件（Host Component）的构造函数中增加@Optional()装饰器即可：
```ts
import { Optional } from '@angular/core';
import { LoggerService } from './shared/logger.service';

//...
export class ContactListComponent {
  constructor(
    @Optional()
    private logger: LoggerService
  ){
    if(this.logger){
      this.logger.log('hello');
    }
  }
}
```
这样就能兼容LoggerService服务不能存在的情况了（实际上，LoggerService这个类的定义还是存在的，不存在是指这个类虽然定义了，但没有准备好，没有在相应的组件或模块中通过providers元数据来配置它）。

另外，依赖查找的规则是按照注入器从当前组件向父级组件查找，直到找到要注入的依赖为止，但当需要限制这种查找规则，比如限定查找的路径截止在宿主组件不再继续向上查找时，就可以使用@Host装饰器了。
所谓的宿主组件是指若一个组件注入了依赖项，那么该组件就是这个依赖项的宿主组件，但若这个组件通过ng-content被嵌入到父组件，那么这个父组件就是该依赖项的宿主组件。

##### 宿主组件是当前组件
在通讯录例子中，因为LoggerService服务已经在顶层组件里通过providers元数据配置，根据查找规则，最终会在顶层组件里查找到依赖，代码可以运行。

若加入@Host装饰器来限定查找规则只停止于当前组件，那么Angular在当前组件中找不到时就会报错，当然，若结合@Optional则可以跳过此检查。
```ts
@Component({
  selector: 'contact-list'
  template: `<contact-a></contact-a>`
})
export class ContactListComponent {
  constructor(
    @Host() // 出错！因为在当前宿主租价那种找不到LoggerService实例
    logger: LoggerService
  ){}
}
```
结合@Optional跳过检查：
```ts
export class ContactListComponent {
  constructor(
    @Host()
    @Optional() // 为LoggerService加上可选参数
    logger: LoggerService
  ){}
}
```

##### 宿主组件是父组件
修改父组件ContactListComponent和子组件ContactAComponent的代码，在子组件中注入LoggerService服务，并将子组件通过ng-content的方式嵌入到父组件中。

组件定义如下：
```ts
// ContactListComponent组件
@Component({
  selector: 'contact-list',
  providers: [LoggerService],
  template: `<ng-content></ng-centent>` //ContactAComponent组件模板的内容将会被嵌入到此处
})
export class ContactListComponent {}

// ContactAComponent组件
@Component({
  selector: 'contact-a',
  template: `<div>ContactA</div>`
})
export class ContactAComponent {
  constructor(
    @Host()
    @Optioanl()
    logger: LoggerService
  )
}
```
因为子组件通过ng-content的方式嵌入到父组件中，所以父组件是宿主，最终在子组件中注入的LoggerService服务会向上找到父组件配置的LoggerService服务。
