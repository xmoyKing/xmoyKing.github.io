---
title: JavaScript框架设计笔记-11-事件系统-2-jQuery事件系统
categories: js
tags:
  - js
  - js-framework
  - jquery.event
  - jquery
date: 2018-01-06 19:31:10
updated: 2018-01-06 19:31:10
---


#### jquery1.8事件模块概览
[源码地址](https://github.com/jquery/jquery/blob/1.8.3/src/event.js)

如下list为[jquery1.8的事件模块API](http://www.css88.com/jqapi-1.9/)，从参考API列表中可以看到API的演变和示例用法。

浏览器事件
.error() *弃用*
.resize()
.scroll()
文档加载
.holdReady()
.load() *弃用*
.ready()
.unload() *弃用*
事件绑定
.bind()
.delegate()
.die() *弃用*
.live() *弃用*
.off()
.on()
.one()
.trigger()
.triggerHandler()
.unbind()
.undelegate()

表单事件
.blur()
.change()
.focus()
.select()
.submit()
键盘事件
.keydown()
.keypress()
.keyup()
鼠标事件
.click()
.contextmenu()
.dblclick()
.focusin()
.focusout()
.hover()
.mousedown()
.mouseenter()
.mouseleave()
.mousemove()
.mouseout()
.mouseover()
.mouseup()
.toggle() *弃用*

#### jQuery.event.add源码解读
add方法的主要目的是将用户的所有传递参数，并成一个handleObj对象放到元素对应的缓存体中的events对象的某个队列中，然后绑定一个回调，这个回调会处理用户的所有回调，因此对于每一个元素每一种事件，它只绑定一次。
```js
add: function( elem, types, handler, data, selector ) {

  var elemData, eventHandle, events,
    t, tns, type, namespaces, handleObj,
    handleObjIn, handlers, special;
  // 若elem不能添加自定义属性（IE下访问文本节点会报错）因此事件源不能为文本节点
  // 注释节点本来就不应该绑定事件，而注释节点能被获取到是因为jquery的html方法
  // 若没有指定事件类型或回调则立即返回
  // Don't attach events to noData or text/comment nodes (allow plain objects tho)
  if ( elem.nodeType === 3 || elem.nodeType === 8 || !types || !handler || !(elemData = jQuery._data( elem )) ) {
    return;
  }

  // 取得用户回调与CSS表达式，handleObjeIn这种结构称为事件描述
  // 记录了用户绑定此回调时的各种信息，方便用于“事件copy”
  // Caller can pass in an object of custom data in lieu of the handler
  if ( handler.handler ) {
    handleObjIn = handler;
    handler = handleObjIn.handler;
    selector = handleObjIn.selector;
  }
  // 确保回调拥有UUID，用于查找或移除
  // Make sure that the handler has a unique ID, used to find/remove it later
  if ( !handler.guid ) {
    handler.guid = jQuery.guid++;
  }
  // 此元素在数据缓存系统中开辟一个叫event的空间来保存所有回调与事件处理器
  // Init the element's event structure and main handler, if this is the first
  events = elemData.events;
  if ( !events ) {
    elemData.events = events = {};
  }
  eventHandle = elemData.handle; // 事件处理器
  if ( !eventHandle ) {
    elemData.handle = eventHandle = function( e ) {
      // 用户在事件冒泡时，被二次fire或在页面unload后触发事件
      // Discard the second event of a jQuery.event.trigger() and
      // when an event is called after a page has unloaded
      return typeof jQuery !== "undefined" && (!e || jQuery.event.triggered !== e.type) ?
        jQuery.event.dispatch.apply( eventHandle.elem, arguments ) :
        undefined;
    };
    // 明确this的指向，防止IE下原生事件内存泄漏
    // Add elem as a property of the handle fn to prevent a memory leak with IE non-native events
    eventHandle.elem = elem;
  }
  // 通过空格隔开同时绑定多个事件，比如"mouseover mouseout"
  // Handle multiple events separated by a space
  // jQuery(...).bind("mouseover mouseout", fn);
  types = jQuery.trim( hoverHack(types) ).split( " " );
  for ( t = 0; t < types.length; t++ ) {

    tns = rtypenamespace.exec( types[t] ) || []; // 取得命名空间
    type = tns[1]; // 取得真正的事件
    namespaces = ( tns[2] || "" ).split( "." ).sort(); // 修正命名控件

    // 并不是所有事件都能直接使用，比如FF下没有mousewheel，需要用DOMMouseScroll模拟
    // If event changes its type, use the special event handlers for the changed type
    special = jQuery.event.special[ type ] || {};

    // 有时候需要在事件代理时进行模拟，比如FF下focus、blur
    // If selector defined, determine special event api type, otherwise given type
    type = ( selector ? special.delegateType : special.bindType ) || type;

    // Update special based on newly reset type
    special = jQuery.event.special[ type ] || {};

    // 构建一个事件描述对象
    // handleObj is passed to all event handlers
    handleObj = jQuery.extend({
      type: type,
      origType: tns[1],
      data: data,
      handler: handler,
      guid: handler.guid,
      selector: selector,
      needsContext: selector && jQuery.expr.match.needsContext.test( selector ),
      namespace: namespaces.join(".")
    }, handleObjIn );

    // 在events对象上分别存储事件描述，每种事件对应一个数组
    // 每种事件只绑定一次监听器
    // Init the event handler queue if we're the first
    handlers = events[ type ];
    if ( !handlers ) {
      handlers = events[ type ] = [];
      handlers.delegateCount = 0;

      // 若存在special.setup并且返回0时才直接使用多投事件API
      // Only use addEventListener/attachEvent if the special events handler returns false
      if ( !special.setup || special.setup.call( elem, data, namespaces, eventHandle ) === false ) {
        // Bind the global event handler to the element
        if ( elem.addEventListener ) {
          elem.addEventListener( type, eventHandle, false );

        } else if ( elem.attachEvent ) {
          elem.attachEvent( "on" + type, eventHandle );
        }
      }
    }

    // 处理自定义事件
    if ( special.add ) {
      special.add.call( elem, handleObj );

      if ( !handleObj.handler.guid ) {
        handleObj.handler.guid = handler.guid;
      }
    }

    // 若使用事件代理，则将事件描述放到数组最前面
    // Add to the element's handler list, delegates in front
    if ( selector ) {
      handlers.splice( handlers.delegateCount++, 0, handleObj );
    } else {
      handlers.push( handleObj );
    }

    // 用于jQuery.event.trigger，若此事件从未绑定过，也没必要进入trigger的真正处理逻辑
    // Keep track of which events have ever been used, for event optimization
    jQuery.event.global[ type ] = true;
  }
  // 防止IE内存泄漏
  // Nullify elem to prevent memory leaks in IE
  elem = null;
},
```
上述代码中，jquery的回调不直接与元素挂钩，而是通过UUID访问数据缓存系统，获取对应的events对象，在根据事件类型得到一组事件描述，并且，事件描述里没有事件源的记录，方便事件克隆。其中数据缓存系统是关键，事件代理对数据缓存依赖非常严重。

#### jQuery.event.remove的源码解读
remove方法根据用户传参，找到事件队列，从里面把匹配的handleObj对象移除，在参数不足的情况，可能移除多个甚至所有。当队列长度为零时移除事件，当events为空对象则清除UUID。
```js
// Detach an event or set of events from an element
remove: function( elem, types, handler, selector, mappedTypes ) {

  var t, tns, type, origType, namespaces, origCount,
    j, events, special, eventType, handleObj,
    elemData = jQuery.hasData( elem ) && jQuery._data( elem );
  // 若不支持添加自定义属性或没有缓存与事件有关的东西，立即返回
  if ( !elemData || !(events = elemData.events) ) {
    return;
  }

  // 按空格进行切割，方便移除多种事件类型，比如hover转换为“mouseenter mouseleave”，
  // Once for each type.namespace in types; type may be omitted
  types = jQuery.trim( hoverHack( types || "" ) ).split(" ");
  for ( t = 0; t < types.length; t++ ) {
    tns = rtypenamespace.exec( types[t] ) || [];
    type = origType = tns[1]; // 取得事件类型
    namespaces = tns[2]; // 取得命名空间

    // 若没有指定事件类型，则移除所有事件类型或移除所有与此命名空间有关的事件类型
    // Unbind all events (on this namespace, if provided) for the element
    if ( !type ) {
      for ( type in events ) {
        jQuery.event.remove( elem, type + types[ t ], handler, selector, true );
      }
      continue;
    }

    // 利用事件模拟，取得真正用于绑定的事件类型
    special = jQuery.event.special[ type ] || {};
    type = ( selector? special.delegateType : special.bindType ) || type;
    eventType = events[ type ] || []; // 取得装载事件描述对象的数组
    origCount = eventType.length;
    // 取得用于过滤命名空间的正则，没有则为null
    namespaces = namespaces ? new RegExp("(^|\\.)" + namespaces.split(".").sort().join("\\.(?:.*\\.|)") + "(\\.|$)") : null;

    // 移除所有符合条件的事件描述对象
    // Remove matching events
    for ( j = 0; j < eventType.length; j++ ) {
      handleObj = eventType[ j ];

      if ( ( mappedTypes || origType === handleObj.origType ) &&  // 比较事件类型是否一致
          ( !handler || handler.guid === handleObj.guid ) && // 若传了回调，判断UUID是否相同
          ( !namespaces || namespaces.test( handleObj.namespace ) ) &&
          // 若types含有命名空间，用正则看是否匹配
          // 若是事件代理则必须有css表达式，比较与事件描述对象中的是否相等
          ( !selector || selector === handleObj.selector || selector === "**" && handleObj.selector ) ) {
        eventType.splice( j--, 1 ); // 移除

        if ( handleObj.selector ) { // delegateCount减1
          eventType.delegateCount--;
        }
        if ( special.remove ) { // 处理个别事件的移除
          special.remove.call( elem, handleObj );
        }
      }
    }

    // 若已经移除所有此类型的回调，则卸载框架绑定的elemData.handle
    // origCount !== eventType.length是为了防止死循环
    // Remove generic event handler if we removed something and no more handlers exist
    // (avoids potential for endless recursion during removal of special event handlers)
    if ( eventType.length === 0 && origCount !== eventType.length ) {
      if ( !special.teardown || special.teardown.call( elem, namespaces, elemData.handle ) === false ) {
        jQuery.removeEvent( elem, type, elemData.handle );
      }

      delete events[ type ];
    }
  }
  // 若events为空，则从elemData中删除events与handler
  // Remove the expando if it's no longer used
  if ( jQuery.isEmptyObject( events ) ) {
    delete elemData.handle;

    // removeData also checks for emptiness and clears the expando if empty
    // so use it instead of delete
    jQuery.removeData( elem, "events", true );
  }
},
```
卸载部分是事件系统中最简单的部分，主要逻辑花在移除事件描述对象的匹配条件。

#### jQuery.event.dispatch源码解读
事件系统的核心，利用dispatch方法，从缓存体中的events对象获取对应队列，然后修复事件对象，逐个传入用户的回调中执行，根据返回值决定是否断开循环（stopImmediatePropagation）、阻止默认行为和事件传播。
```js
dispatch: function( event ) {
  // 创建一个伪事件对象（jQuery.Event实例），从真正的事件对象上抽取响应的属性附于其上
  // 若是IE，也可将它们转换成对应的W3C属性，弥补差异
  // Make a writable jQuery.Event from the native event object
  event = jQuery.event.fix( event || window.event );

  var i, j, cur, ret, selMatch, matched, matches, handleObj, sel, related,
    // 取得所有事件描述对象
    handlers = ( (jQuery._data( this, "events" ) || {} )[ event.type ] || []),
    delegateCount = handlers.delegateCount,
    args = core_slice.call( arguments ),
    run_all = !event.exclusive && !event.namespace,
    special = jQuery.event.special[ event.type ] || {},
    handlerQueue = [];

  // 重置第一个参数为jQuery.Event实例
  // Use the fix-ed jQuery.Event rather than the (read-only) native event
  args[0] = event;
  event.delegateTarget = this; // 添加一个人为属性，用于事件代理

  // 执行preDispatch回调，它与后面的postDIspatch构成一种类似AOP的机制
  // Call the preDispatch hook for the mapped type, and let it bail if desired
  if ( special.preDispatch && special.preDispatch.call( this, event ) === false ) {
    return;
  }

  // 若是事件代理，并且不是来自于非左键的点击事件
  // Determine handlers that should run if there are delegated events
  // Avoid non-left-click bubbling in Firefox (#3861)
  if ( delegateCount && !(event.button && event.type === "click") ) {
    // 从事件源开始，遍历其所有祖先一直到绑定事件的元素
    for ( cur = event.target; cur != this; cur = cur.parentNode || this ) {
      // 不触发disabled的元素的点击事件
      // Don't process clicks (ONLY) on disabled elements (#6911, #8165, #11382, #11764)
      if ( cur.disabled !== true || event.type !== "click" ) {
        selMatch = {}; // 每种CSS表达式只判断1次，
        matches = []; // 用于收集符合条件的事件描述对象
        // 使用事件代理的事件描述对象总是排在前面
        for ( i = 0; i < delegateCount; i++ ) {
          handleObj = handlers[ i ];
          sel = handleObj.selector;

          if ( selMatch[ sel ] === undefined ) {
            // 有多少个元素匹配就收集多少个事件描述对象
            selMatch[ sel ] = handleObj.needsContext ?
              jQuery( sel, this ).index( cur ) >= 0 :
              jQuery.find( sel, this, null, [ cur ] ).length;
          }
          if ( selMatch[ sel ] ) {
            matches.push( handleObj );
          }
        }
        if ( matches.length ) {
          handlerQueue.push({ elem: cur, matches: matches });
        }
      }
    }
  }

  // 取得其他直接榜单的事件描述对象
  // Add the remaining (directly-bound) handlers
  if ( handlers.length > delegateCount ) {
    handlerQueue.push({ elem: this, matches: handlers.slice( delegateCount ) });
  }

  // 循环从下到上执行
  // Run delegates first; they may want to stop propagation beneath us
  for ( i = 0; i < handlerQueue.length && !event.isPropagationStopped(); i++ ) {
    matched = handlerQueue[ i ];
    event.currentTarget = matched.elem;

    // 执行此元素的所有与event.type同类型的回调，除非调用了stopImmediatePropagation方法，
    // 其会导致isImmediatePropagationStopped返回true，从而中断循环
    for ( j = 0; j < matched.matches.length && !event.isImmediatePropagationStopped(); j++ ) {
      handleObj = matched.matches[ j ];

      // 最后的过滤条件为事件命名空间
      // Triggered event must either 1) be non-exclusive and have no namespace, or
      // 2) have namespace(s) a subset or equal to those in the bound event (both can have no namespace).
      if ( run_all || (!event.namespace && !handleObj.namespace) || event.namespace_re && event.namespace_re.test( handleObj.namespace ) ) {

        event.data = handleObj.data;
        event.handleObj = handleObj;

        // 执行用户回调，
        ret = ( (jQuery.event.special[ handleObj.origType ] || {}).handle || handleObj.handler )
            .apply( matched.elem, args );
        // 根据结果判断是否阻止事件传播与默认行为
        if ( ret !== undefined ) {
          event.result = ret;
          if ( ret === false ) {
            event.preventDefault();
            event.stopPropagation();
          }
        }
      }
    }
  }

  // 执行postDispatch回调
  // Call the postDispatch hook for the mapped type
  if ( special.postDispatch ) {
    special.postDispatch.call( this, event );
  }

  return event.result;
},
```
这个方法的难点在于如何模拟事件传播的机制，而jquery只模拟冒泡的阶段。

一般来说触发事件的顺序与绑定时无关，而与绑定事件的元素在DOM树中的顺序有关，事件代理中的绑定元素通常位于DOM树顶端，如document、html、body，因此执行较晚。在jquery.event.add方法中，有一个delegateCount变量，用于在绑定时把对应的事件描述对象放在前面，因此dispatch时就方便很多。

#### jQuery.event.trigger源码解读
jQuery追求兼容性，所以trigger方法（即fireEvent）非常的好用，兼容性非常强。
```js
trigger: function( event, data, elem, onlyHandlers ) {
  // 必须指定派发事件的对象，不能是文本节点或元素节点
  // Don't do events on text and comment nodes
  if ( elem && (elem.nodeType === 3 || elem.nodeType === 8) ) {
    return;
  }

  // Event object or event type
  var cache, exclusive, i, cur, old, ontype, special, handle, eventPath, bubbleType,
    type = event.type || event,
    namespaces = [];

  // focus/blur morphs to focusin/out; ensure we're not firing them right now
  if ( rfocusMorph.test( type + jQuery.event.triggered ) ) {
    return;
  }

  if ( type.indexOf( "!" ) >= 0 ) {
    // Exclusive events trigger only for the exact event (no namespaces)
    type = type.slice(0, -1);
    exclusive = true;
  }

  // 若事件类型带点号就分解出命名空间
  if ( type.indexOf( "." ) >= 0 ) {
    // Namespaced trigger; create a regexp to match event type in handle()
    namespaces = type.split(".");
    type = namespaces.shift();
    namespaces.sort();
  }

  // customEvent与global用于优化，若没有绑定过这种事件，就不需要继续执行了
  if ( (!elem || jQuery.event.customEvent[ type ]) && !jQuery.event.global[ type ] ) {
    // No jQuery handlers for this event type, and it can't have inline handlers
    return;
  }

  // 将用户传入的第一个参数都转换为jQuery.Event实例
  // Caller can pass in an Event, Object, or just an event type string
  event = typeof event === "object" ?
    // jQuery.Event object
    event[ jQuery.expando ] ? event : // JQuery.Event实例
    // Object literal
    new jQuery.Event( type, event ) : // 原生事件对象
    // Just the event type (string)
    new jQuery.Event( type ); // 事件类型

  event.type = type;
  event.isTrigger = true;
  event.exclusive = exclusive;
  event.namespace = namespaces.join( "." );
  event.namespace_re = event.namespace? new RegExp("(^|\\.)" + namespaces.join("\\.(?:.*\\.|)") + "(\\.|$)") : null;
  ontype = type.indexOf( ":" ) < 0 ? "on" + type : "";

  // 若没有指明触发者，只能将整个缓存系统寻找一遍
  // Handle a global trigger
  if ( !elem ) {

    // TODO: Stop taunting the data cache; remove global events and always attach to document
    cache = jQuery.cache;
    for ( i in cache ) {
      if ( cache[ i ].events && cache[ i ].events[ type ] ) {
        jQuery.event.trigger( event, data, cache[ i ].handle.elem, true );
      }
    }
    return;
  }

  // 清理result，方便重复使用
  // Clean up the event in case it is being reused
  event.result = undefined;
  if ( !event.target ) {
    event.target = elem; // 保持事件源不变
  }

  // data用于放置派发事件时的额外参数，方便apply整合为数组，并将event放在第一位
  // Clone any incoming data and prepend the event, creating the handler arg list
  data = data != null ? jQuery.makeArray( data ) : [];
  data.unshift( event );

  // 若此事件类型指定了它的trigger方法，就直接使用
  // Allow special events to draw outside the lines
  special = jQuery.event.special[ type ] || {};
  if ( special.trigger && special.trigger.apply( elem, data ) === false ) {
    return;
  }

  // 预先确定冒泡的路径，一直冒泡到window
  // Determine event propagation path in advance, per W3C events spec (#9951)
  // Bubble up to document, then to window; watch for a global ownerDocument var (#9724)
  eventPath = [[ elem, special.bindType || type ]];
  if ( !onlyHandlers && !special.noBubble && !jQuery.isWindow( elem ) ) {

    bubbleType = special.delegateType || type;
    cur = rfocusMorph.test( bubbleType + type ) ? elem : elem.parentNode;
    for ( old = elem; cur; cur = cur.parentNode ) {
      eventPath.push([ cur, bubbleType ]);
      old = cur;
    }

    // Only add window if we got to document (e.g., not plain obj or detached DOM)
    if ( old === (elem.ownerDocument || document) ) {
      eventPath.push([ old.defaultView || old.parentWindow || window, bubbleType ]);
    }
  }

  // 沿着规划好的路径把经过的元素节点的指定事件类型的回调逐一触发
  // Fire handlers on the event path
  for ( i = 0; i < eventPath.length && !event.isPropagationStopped(); i++ ) {

    cur = eventPath[i][0];
    event.type = eventPath[i][1];

    // handle其实是调用dispatch函数，因此trigger是把整个冒泡过程都人工实现
    handle = ( jQuery._data( cur, "events" ) || {} )[ event.type ] && jQuery._data( cur, "handle" );
    if ( handle ) {
      handle.apply( cur, data );
    }

    // 处理onXXX的绑定回调，无论是写在HTML标签内还是以无浸入方式
    // Note that this is a bare JS function and not a jQuery handler
    handle = ontype && cur[ ontype ];
    if ( handle && jQuery.acceptData( cur ) && handle.apply && handle.apply( cur, data ) === false ) {
      event.preventDefault(); // 若返回true则中断循环
    }
  }
  event.type = type;

  // 若用户没有调用preventDefault或return false，则模拟默认行为
  // 具体指：执行submit、blur、focus、select、reset、scroll等方法
  // 大其实没有模拟所有默认行为
  // 比如点击链接时的跳转，点击单选/复选框时元素的checked会被改变
  // If nobody prevented the default action, do it now
  if ( !onlyHandlers && !event.isDefaultPrevented() ) {
    // 若用户指定了默认行为，则只执行默认行为，并且跳过链接的点击事件
    if ( (!special._default || special._default.apply( elem.ownerDocument, data ) === false) &&
      !(type === "click" && jQuery.nodeName( elem, "a" )) && jQuery.acceptData( elem ) ) {

      // 若元素同时存在el['on'+type]回调与el[type]方法，则表示有默认行为
      // 对于el[type]属性的检测，jQuery不使用isFunction方法，因为typeof在IE6~8下返回object
      // jquery也不触发隐藏元素的focus或blur默认新闻，IE6~8下会报错
      // 同时不触发window的默认行为，防止触发window.scroll方法
      // 该方法在IE和标准浏览器下存在差异，IE下执行默认scroll()为scroll(0,0)
      // Call a native DOM method on the target with the same name name as the event.
      // Can't use an .isFunction() check here because IE6/7 fails that test.
      // Don't do default actions on window, that's where global variables be (#6170)
      // IE<9 dies on focus/blur to hidden element (#1486)
      if ( ontype && elem[ type ] && ((type !== "focus" && type !== "blur") || event.target.offsetWidth !== 0) && !jQuery.isWindow( elem ) ) {

        // onXXX回调已经在$.event.dispatch方法执行过后不再触发
        // Don't re-trigger an onFOO event when we call its FOO() method
        old = elem[ ontype ];

        if ( old ) {
          elem[ ontype ] = null;
        }

        // 标识正在触发此事件类型，防止后面的elem[type]()重复执行dispatch
        // Prevent re-triggering of the same event, since we already bubbled it above
        jQuery.event.triggered = type;
        elem[ type ](); // 执行默认行为
        jQuery.event.triggered = undefined; // 还原

        if ( old ) { // 还原
          elem[ ontype ] = old;
        }
      }
    }
  }

  // 与dispatch一样，返回event.result
  return event.result;
},
```
trigger算是dispatch的加强版，dispatch只触发当前元素与其底下元素（事件代理）的回调，trigger则模拟整个冒泡过程，除了自身还触发其祖先节点和window的同类型回调。从源码看，trigger比dispatch多做的是触发事件的默认行为，涉及很多判断。这一点上zepto就做的比较简洁，即在某一个元素触发一个回调（dispatch），生成一个事件对象，然后依次让其冒泡，触发其他回调（dispatch）。