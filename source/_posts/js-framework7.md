---
title: JavaScript框架设计笔记-7-数据缓存系统
categories: JavaScript
tags:
  - JavaScript
  - js-framework
date: 2016-12-30 15:24:04
updated: 2016-12-30 15:24:04
---

数据缓存系统最早应该是jquery1.2引入的，它是用来关联操作对象和与之相关的数据的一种机制。通常在DOM中操作3种数据，元素节点、文档对象、window对象。数据缓存系统能够规避循环引用的问题以及全局污染问题，（即事件的回调都放到一个EventTarget之上时会引发循环引用问题，当EventTarget是一个window对象时会引发全局污染问题），同时能够有效保存不同方法产生的中间变量，当这些中间变量对另一个模块的方法有用时，能解耦方法间的依赖。

对于后期的jquery，事件克隆、事件队列都离不开缓存系统，缓存系统主要分为4种形态：
1. 属性标记法
1. 数组索引法
1. valueOf重写法
1. WeakMap关联法

#### jQuery第一代缓存系统
jquery1.2在core模块新增了两个静态方法，data与removeData。data读写结合，jquery的缓存系统把所有的数据都放在$.cache仓库上，然后为每个要使用缓存系统的元素节点、文档对象、window对象分配一个UUID。

UUID的属性名为一个随机的自定义属性，`'jQuery'+(new Date()).getTime()`,值为整数，从零递增。但UUID总要附于一个对象上，当对象是window时，就会引发全局污染。因此jquery内部判断当对象为window时，则将其自动映射到windowData的空对象上，然后在windowData上附加UUID，有了UUID，在首次访问缓存系统时，就会在$.cache对象开辟一个空对象（缓存体），用于放置与目标对象有关的东西。removeData则会删除掉不再需要保存的数据，若到最后数据清除完了，没有任何键值对了，则成为一个空对象，此时jquery就从$.cahce中删掉此对象，并从目标对象移除UUID。

jquery1.2.3添加了两个同名的原型方法data与removeData，目的是方便链式操作与集化操作，并在data中添加getData、setData的自定义事件的触发逻辑。
```js
// jquery1.2.3
var expando = 'jQuery'+(new Date()).getTime(),
  uuid = 0,
  windowData = {};

jQuery.extend({
  cache: {},
  data: function(elem, name, data){
    elem = elem == window ? windowData : elem; // 对window对象进行处理
    var id = elem[expando];
    if(!id){ // 若没有UUID，则新建一个
      id = elem[expando] = ++uuid;
    }
    // 若没有在$.cache中，则先新建
    if(name && !jQuery.cache[id]){
      jQuery.cache[id] = {};
    }
    // 第三个参数不为undefined时为写操作
    if(data != undefined){
      jQuery.cache[id][name] = data;
    }
    // 若只有一个参数，则返回缓存对象，两个参数则返回目标数据
    return name ? jQuery.cache[id][name] : id;
  },

  removeData: function(elem, name){
    elem = elem == window ? windowData : elem;
    var id = elem[expando];
    if(name){// 移除目标数据
      if(jQuery.cache[id]){
        delete jQuery.cache[id][id];
        name = '';

        for(name in jQuery.cache[id]){
          break;
        }
        // 遍历缓存体，若不为空则改写name，若没有被改写，则!name为true
        // 从而引发再次调用此方法，此时只传入一个参数，移除缓存体
        if(!name){
          jQuery.removeData(elem);
        }
      }else{
        // 移除UUID，但IE下对元素使用delete会抛错
        try{
          delete elem[expando];
        }catch(e){
          if(elem.removeAttribute){
            elem.removeAttribute(expando);
          }
        }
        // 注销cache
        delete jQuery.cache[id];
      }
    }
  }
})
```

jquery1.3中，数据缓存系统独立成为一个data模块（内部划分使用），并在原型和命名空间中添加queue和dequeue方法。queue的作用是缓存一组数据，为动画模块服务，dequeue是从一组数据中删掉一个。

在元素上添加自定义属性时会引发一个问题，即对元素进行复制时会将此属性也复制过去，导致两个元素有相同的UUID值。jquery早期的解决方法为若元素cloneNode方法不会复制事件就使用cloneNode，否则使用元素的outerHTML或父节点的innerHTML，用clean方法解析一个新元素出来，然后用正则清除掉所有新元素上的显式属性。

jquery1.4中，对object、embed、applet进行特殊处理，因为这3个元素用于加载外部资源，在旧版IE中，元素节点是COM的包装，引入外部资源后就会变为资源的实例，可能会引发其他错误，所以对这三种元素不进行缓存处理。jquery1.4对$.data改进，允许第二个参数为对象，方便存储多个数据，UUID对应的自定义属性expando放入到命名控件下，queue和dequeue方法成为一个新模块。

面对自定义属性，H5有一种`data-*`的缓存机制，当用户设置的属性以data-开头，它们就会被保存到元素节点的dataset对象上，于是jquery允许人们通过设置`data-*`来配置UI组件，当用户第一次访问次元素节点，会遍历它所有data-开头的自定义属性，将它们放到jquery缓存中，用用户取数据时，优先从缓存系统中取，没有在使用setAttribute访问data-自定义属性，同时jquery还增强了这种data-缓存机制（H5只能保存字符串），会将其内数据还原为原始类型，如'null'还原为`null`,'false'还原为`false`，包裹在`{}`中的数据则尝试转换为一个对象。

jquery1.5着重改进性能（1.4打败了Prototype.js,用户暴增）,改进expand为基于版本号+随机数，因为用户可能在一个页面内引入多个版本的jquery。
是否有自定义数据的逻辑抽离为hasData方法，处理H5的`data-*`属性抽离为dataAttr内部方法。
jquery的数据缓存系统原本是为事件系统服务而分化出来的，到后来，成为内部众多模块的基础，而一旦公开到文档中，用户就可以用data方法来保存用户数据，因此用户数据和内部私有数据可能会有相互覆盖的问题，最后的解决方法是对缓存体进行改造，原本是一个对象，什么都能装，改为在内部开辟一个子对象，键名为随机的jQuery.expando值，若是私有数据就存到子对象里。events私有数据出于兼容目的，直接放到缓存体上。 区分私有数据的方式就是直接在data方法上添加第四个参数，真值为私有数据，removeData也提供第三个参数，用于删除私有数据，同时新设一个_data方法，专门用于操作私有数据。

jquery1.7改进为系统变量放在data对象中。

jquery1.8将UUID值改用jQuery.guid递增生成。

#### jQuery第二代缓存系统
目标如下：
1. 在接口与语义上兼容1.9.x分支
1. 通过简化存储路径为统一的方法来提高维护性
1. 使用相同的机制来实现私有和用户数据
1. 不再把私有数据与用户数据混在一起
1. 不再在用户对象上添加自定义属性
1. 方便以后可以平滑的利用WeakMap对象进行升级

第二代缓存系统的实现方法是valueOf重写，具体原理为：若目标对象的valueOf传入一个特殊的对象，那么它就返回一个UUID，然后通过UUID在Data实例的cache对象属性上开辟缓存，这样就不需要使用windowData来代替window了，也无需在意IE下embed、object、applet等特殊元素了。

第二代在框架内部添加Data类，它的实例有cache属性，私有数据与用户数据分别由一个Data实例来维护。

```js
function Data(){
    this.cache = {};
}

Data.uid = 1;

Data.prototype = {
    locker: function(owner){ // owner为元素节点、文档对象、window对象
        // 首先检查它们的valueOf方法是否被重写过
        // 对构造器进行原型重写成本太大，转而为每一个实例的valueOf方法进行重写
        // 检测方式为传入Data类，若返回object说明没有被重写，返回string则已被重写
        // 这个字符串就是UUID，用于在缓存仓库创建缓存体
        var ovalueOf,
            unlock = owner.valueOf(Data);

        // 重写使用Object.defineProperty方法
        // 其第三个参数为对象，若不显示设置enumerable、writable、configurable
        // 则默认设置false，这个过程被jquery称为开锁
        if(typeof unlock !== 'string'){
            unlock = jQuery.expando + Data.uid++;
            ovalueOf = owner.valueOf;

            Object.defineProperty(owner, 'valueOf', {
                value: function(pick){
                    if(pick === Data){
                        return unlock;
                    }
                    return ovalueOf.apply(owner);
                }
            });
        }

        // 接下来开辟缓存体
        if(!this.cache[unlock]){
            this.cache[unlock] = {};
        }

        return unlock;
    },

    set: function(owner, data, value){
        // 写方法
        var prop, cache, unlock;
        // UUID和缓存体
        unlock = this.locker(owner);
        cache = this.cache[unlock];
        // 若传入value，第2个为从字符串，则表示添加新键值对
        if(typeof data === 'string'){
            cache[data] = value;
        }else{
            // 传入2个参数，第二个为对象
            // 若缓存体没有添加过任何对象，则直接赋值，否则使用for in循环添加
            if(jQuery.isEmptyObject(cache)){
                cache = data;
            }else{
                for(prop in data){
                    cache[prop] = data[prop];
                }
            }
        }
        this.cache[unlock] = cache;
        return this;
    },

    get: function(owner, key){
        // 读方法
        var cache = this.cache[this.locker(owner)];
        // 若只有一个参数，则返回整个缓存体
        return key === undefined ? cache : cache[key];
    },

    access: function(owner, key, value){
        // 决定是读还是写
        if(key === undefined || ((key && typeof key === 'string') && value === undefined)){
            return this.get(owner, key);
        }
        this.set(owner, key, value);
        return value !== undefined ? value : key;
    },

    remove: function(owner, key){
        // 删除和第一代类似，略
    },

    hasData: function(owner){
        // 判断是否缓存了数据
        return !jQuery.isEmptyObject(this.cache[this.locker(owner)]);
    },

    discard: function(owner){
        // 删除owner相关的用户数据和私有数据
        delete this.cache[this.locker(owner)];
    }
};

var data_user = new Data(),
    data_priv = new Data();

function data_discard(owner){
    data_user.discard(owner);
    data_priv.discard(owner);
}

// 接下来暴露给用户调用的都是空壳，用来转交给data_user、data_priv这两个实例对象处理，
// 并且私有数据的处理也不通过用户数据的渠道了
jQuery.extend({
    // UUID
    expando: 'jQuery'+(core_version+Math.random()).replace(/\D/g, ''),
    // 用于向前兼容
    acceptData: function(){
        return true;
    },
    hasData: function(elem){
        return data_user.hasData(elem) || data_priv.hasData(elem);
    },
    data: function(elem, name, data){
        return data_user.access(elem, name, data);
    },
    removeData: function(elem, name){
        return data_user.remove(elem, name);
    },
    _data: function(elem, name, data){
        return data_priv.access(elem, name, data);
    },
    _removeData: function(elem, name){
        return data_priv.remove(elem, name);
    }
});
```

重写valueOf非常好，任何非纯空对象都有valueOf方法，通过闭包保存用于关联缓存仓库的UUID，但缺点是闭包非常耗内存。

#### mass Framework第一代缓存系统
mass Framework兼容jquery 90%的API，也存在数据缓存系统，改良自jquery，其关联方式不同。

为了建立目标对象与缓存体的联系，jquery选择目标对象上添加自定义属性，但旧版IE有特殊元素的问题，而mass Framework通过uniqueNumber属性（IE的私有属性）来解决。

#### mass Framework第二代缓存系统
属性标记法的缺点就是需要根据目标对象的不同或浏览器的不同做出不同的处理，且清理这些自定义属性非常麻烦，jquery第二代缓存系统采用空间换时间的方式将UUID内嵌到目标对象的valueOf方法中。mass Framework第二代采用数组索引法，建立两个数组，一个装目标对象，另一个数组在对应的位置上放缓存体，这样就不用对目标对象做任何修改，从而目标对象可以扩展到任何数据类型，并且即使目标对象使用了Object.preventExtensions、Object.seal、Object.freeze也可以得到UUID。

#### mass Framework第三代缓存6统
WeakMap是ES6带来的新集合对象，其特性刚好适合缓存系统的要求。所以利用WeakMap代替UUID做为目标对象和缓存体的联系，实在是一件在适合不过的事儿了。