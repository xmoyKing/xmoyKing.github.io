---
title: JavaScript框架设计笔记-5-Sizzle引擎
categories: js
tags:
  - js
  - js-framework
  - sizzle
date: 2017-12-26 18:57:46
updated: 2017-12-26 18:57:46
---

jQuery最大的特点就是其选择器，jQuery从1.3开始使用Sizzle引擎。其与其他的选择器引擎（当时也没什么选择器引擎）相比，速度非常快。

Sizzle在当时的几大特点：
- 允许以关系选择器开头
- 允许取反选择器嵌套
- 大量自定义伪类，比如`:eq :first :even :contains :has :radio :input :text :file :hidden :visible`等
- 对结果去重，以元素在DOM树的位置进行排序，这样与querySelector行为一致

到jQuery/Sizzle1.8时，其开始走编译函数的风格，正则通过编译得到，更加准确，结构也更加复杂，同时通过多种缓存手段提高查找速度和匹配速度。

在[Sizzle1.7.2](https://github.com/jquery/sizzle/blob/1.7.2/sizzle.js)中，其整体结构如下：
1. Sizzle主函数，里面包含选择符的切割，内部循环调用主查找函数，主过滤函数，最后时去重过滤
1. 其他辅助函数，如uniqueSort,matches,matchesSelector
1. Sizzle.find主查找函数
1. Sizzle.filter主过滤函数
1. Sizzle.selectors包含各种匹配用的正则，过滤用的正则，分解用的正则，预处理函数，过滤函数
1. 根据浏览器特征设计makeArray，sortOrder，contains等方法
1. 根据浏览器特征重写Sizzle.selctors中的部分查找函数，过滤函数，查找次序
1. 若浏览器支持querySelectorAll，那么用它重写Sizzle，将原来的Sizzle作为后备包裹在新Sizzle里
1. 其他辅助函数，如isXML，posProcess

```js
var Sizzle = function( selector, context, results, seed ) {
	results = results || [];
	context = context || document;
  // 备份context，由于后面context会被动态改写，所以当出现并联选择器时，需要区别当前节点对应的context
	var origContext = context;
  // context对象必须是元素节点或文档对象
	if ( context.nodeType !== 1 && context.nodeType !== 9 ) {
		return [];
	}
  // 选择符必须是非空字符串
	if ( !selector || typeof selector !== "string" ) {
		return results;
	}

	var m, set, checkSet, extra, ret, cur, pop, i,
		prune = true,
		contextXML = Sizzle.isXML( context ),
		parts = [],
		soFar = selector;

	// Reset the position of the chunker regexp (start from head)
  // 如下是切割器的实现，每次只处理到并联选择器，其他留给下次递归自身时使用
  // 切成选择器组与关系选择器的集合
  // 比如 body div > div:not(.aaa), title 会得到parts数组：
  // ['body','div','>', 'div:not(.aaa)'];
  // 后代选择器会被忽略，但循环parts数组时默认每两个选择器组中一定夹杂一个关系选择器
  // 不存在的则放一个后代选择器到该位置上
	do {
		chunker.exec( "" ); // 将chunker的lastIndex重置
		m = chunker.exec( soFar );

		if ( m ) {
			soFar = m[3];

			parts.push( m[1] );

			if ( m[2] ) { // 若存在并联选择器就中断循环
				extra = m[3];
				break;
			}
		}
	} while ( m );
  //  略... 后面的就是对ID与位置伪类进行优化的if-else判断
}
```

Sizzle的一些概念：
查找函数就是Sizzle.selectors.find下的几种函数，通常有ID，TAG，NAME三个，如浏览器支持getElementsByClassName，则还有CLASS函数。

种子集，即候选集，就是通过最右边的选择器组得到的元素集合，Sizzle中的变量名为seed，比如`div.aaa span.bbb`最右边的选择器组就是`span.bbb`，此时引擎会根据浏览器支持情况选择getElementsByTagName或getElementsClassName，然后通过className或tagName进行过滤，然后得到的集合就是种子集。

映射集，也叫影子集，Sizzle中的变量名为checkSet，其实就是当取得种子集后，先复制一份，复制出来的就叫映射集。种子集是由一个选择器组选出的，若此时选择符不为空，则往左就是关系选择器。关系选择器会让引擎去选取其兄长或父亲（具体操作参考Sizzle.selectors.relative下的四个函数），把这些元素置换到候选集对等的位置上，然后到下一个选择器组时，就是纯过滤操作。主过滤函数Sizzle.filer会调用Sizzle.selectors下的过滤函数对这些元素进行检查，间不符合的元素替换为false，到最后去重排序时，映射集就是一个包含布尔值和元素节点的数组。

种子集是分两步筛选出来的，首先，通过Sizzle.find得到一个大概结果，然后通过Sizzle.filter，传入最右的那个选择器组剩余的部分做参数，减少范围。
```js
// 针对最左边的选择器组存在ID时的优化
ret = Sizzle.find( parts.shift(), context, contextXML );
context = ret.expr ?
  Sizzle.filter( ret.expr, ret.set )[0] :
  ret.set[0];

  // ...

// 下面会对~和+进行优化，直接取其上一级做上下文，只对一个上下文进行操作
ret = seed ?
  { expr: parts.pop(), set: makeArray(seed) } :
  Sizzle.find( parts.pop(), parts.length === 1 && (parts[0] === "~" || parts[0] === "+") && context.parentNode ?
  context.parentNode : context, contextXML );

set = ret.expr ?
  Sizzle.filter( ret.expr, ret.set ) :
  ret.set;
```

那个针对`span.aaa`是先取`span`还是`.aaa`呢？准则就是，确保最后的映射集的最小化（映射集里元素越少，那么调用过滤函数的次数就少，调用函数的次数越少，进入另一个函数作用域消耗就越少，引擎的选择速度越高）。为了这个目标，对浏览器进行优化，原生选择器的调用顺序被放到一个叫Sizzle.selectors.order的数组中，对老的浏览器，顺序为ID、NAME、TAG，对支持支持getElementsByClassName的浏览器，顺序为ID、CLASS、NAME、TAG。因为ID最多只会返回一个元素节点（同名ID不报错，但只返回第一个），className与样式相关，不是每个元素都有类名，name属性的限制比className更大，用到的几率较少，而tagName能排除的就更少了。所以，Sizzle.find会根据上面的数组，取得它的名字依次调用Sizzle.leftMatch下对应的正则，从最右的选择器组中切下需要的部分，将换行符处理掉，通过四大查找函数得到一个粗糙的节点集合。若遇到`[href=aaa]:visible`这样的选择符，那么只能将页面所有匹配节点作为结果返回。
```js
Sizzle.find = function( expr, context, isXML ) {
	var set, i, len, match, type, left;

	if ( !expr ) {
		return [];
	}

	for ( i = 0, len = Expr.order.length; i < len; i++ ) {
		type = Expr.order[i];
    // 取得正则，匹配出需要的ID、CLASS、NAME、TAG
		if ( (match = Expr.leftMatch[ type ].exec( expr )) ) {
			left = match[1];
			match.splice( 1, 1 );
      // 处理换行符
			if ( left.substr( left.length - 1 ) !== "\\" ) {
				match[1] = (match[1] || "").replace( rBackslash, "" );
				set = Expr.find[ type ]( match, context, isXML );
        // 若不为undefined，那么去掉选择器组中用过的部分
				if ( set != null ) {
					expr = expr.replace( Expr.match[ type ], "" );
					break;
				}
			}
		}
	}
  // 没有，则寻找该上下文对象的所有子孙
	if ( !set ) {
		set = typeof context.getElementsByTagName !== "undefined" ?
			context.getElementsByTagName( "*" ) :
			[];
	}

	return { set: set, expr: expr };
};
```

经过主查找函数处理后，就得到一个初步结果，这时最右边的选择器组可能还有残余，比如`div span.aaa`可能遇到`div span`， `div .aaa.bbb`可能余下`div .bbb`,此时就转交主过滤函数Sizzle.filter处理。filter有两个作业，一是不断缩小集合的个数，构成种子集返回，二是根据第三个参数inplace而定，将原集合中不匹配的元素置为false。
```js
// expr 选择符
// set 元素数组
// inplace 当为true时进入映射集模式，false则进入种子集模式
// not 来源自取反选择器的布尔值
Sizzle.filter = function( expr, set, inplace, not ) {
	var match, anyFound,
		type, found, item, filter, left,
		i, pass,
		old = expr,
		result = [],
		curLoop = set,
		isXMLFilter = set && set[0] && Sizzle.isXML( set[0] );

	while ( expr && set.length ) {
		for ( type in Expr.filter ) { // ID、TAG、CLASS、ATTR、CHILD、POS、PESUDO
			if ( (match = Expr.leftMatch[ type ].exec( expr )) != null && match[2] ) {
        // 切割出相应的字符串，放入filter里
				filter = Expr.filter[ type ];
				left = match[1];

				anyFound = false;

				match.splice(1,1);

				if ( left.substr( left.length - 1 ) === "\\" ) {
					continue;
				}

				if ( curLoop === result ) {
					result = [];
				}

				if ( Expr.preFilter[ type ] ) {
					match = Expr.preFilter[ type ]( match, curLoop, inplace, result, not, isXMLFilter );

					if ( !match ) {
						anyFound = found = true;

					} else if ( match === true ) {
						continue;
					}
				}

				if ( match ) {
					for ( i = 0; (item = curLoop[i]) != null; i++ ) {
						if ( item ) {
							found = filter( item, match, i, curLoop );
							pass = not ^ found;

							if ( inplace && found != null ) {
								if ( pass ) {
									anyFound = true;

								} else {
									curLoop[i] = false;
								}

							} else if ( pass ) {
								result.push( item );
								anyFound = true;
							}
						}
					}
				}

				if ( found !== undefined ) {
					if ( !inplace ) {
						curLoop = result;
					}

					expr = expr.replace( Expr.match[ type ], "" );

					if ( !anyFound ) {
						return [];
					}

					break;
				}
			}
		}

		// Improper expression
		if ( expr === old ) {
			if ( anyFound == null ) {
				Sizzle.error( expr );

			} else {
				break;
			}
		}

		old = expr;
	}

	return curLoop;
};
```