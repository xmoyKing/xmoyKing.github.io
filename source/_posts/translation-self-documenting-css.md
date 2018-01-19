---
title: 【译】关于CSS自文档的思考
categories: Translation
tags:
  - css
  - translation
date: 2018-01-19 21:34:42
updated: 2018-01-19 21:34:42
---

*未翻译完全*

本译文翻译自[Thoughts on Self-Documenting CSS](https://keithjgrant.com/posts/2017/06/self-documenting-css/)，将会同时发布在[众成翻译](http://zcfy.cc)上。

作者：[Keith J. Grant](https://keithjgrant.com/)

===============================================================================================

2017.1.9

Robert C. Martin所写的[《Clean Code》](https://www.amazon.com/Clean-Code-Handbook-Software-Craftsmanship/dp/0132350882/)是我所读过的最好的编程书籍之一，若你没有读过，那么推荐你将它加入书单。

> 注释就意味着代码无法自说明—— Robert C. Martin

在文中，Martin讨论了代码注释，我不会完全重复他的话，简而言之他的意思就是，这些注释是注定会过时的。程序执行时会忽视注释，所以无法保证这些说明注释会准确的描述代码作用。所以最好的方式是让代码自说明，如此，按照代码逻辑，程序员和程序获取到的信息是一致的。


思考如下代码：

    // Check to see if the employee is eligible for full benefits
    // 检查员工是否有资格获取全部福利
    if ((employee.flags & HOURLY_FLAG) && (employee.age > 65)) {
      …
    }


注释有用么？当然有用，但下面的方式可能更好：

    if (employee.isEligibleForFullBenefits()) {
      …
    }


代码“言行一致”.长久以来，注释是能够被命名良好的函数或变量取代的。Martin的意思并不是说永不使用注释，而是应该尽量避免写注释。让代码自说明的注释代表着失败。

那么对CSS而言呢？
---------------

我非常赞同Martin关于注释的讨论。当涉及到声明式的语言如CSS时，他的这种想法引发了许多有趣的问题。声明必须完全符合格式，选择器基本是由HTML结构决定的。而对这个代码结构，能做的不多，这是否意味着CSS代码必须注释满天飞？

额，也许吧。有很多的理由使用注释，且注释的方法也很多。让我们来看一些注释，思考这些注释是否应该添加。先从答案显然的开始吧，然后一步步深入到不那么好判断的。

不好：显而易见注释

---------------------

任何语言，显而易见的注释都是多余的，如下的示例出自Bootstrap3的早期版本：


    // Addresses
    address {…}


显然，那是关于地址的选择器


    // Unordered and Ordered lists
    ul,
    ol {…}


还有？

    // Blockquotes
    blockquote {…}


赶紧暂停！

千万不要写那种注释，赶紧删掉这些low货。它仅仅是在重复代码而已。还好，新版本的Bootstrap已经删除掉大部分的无用注释了。

不好： 段落注释
-----------------------

对CSS而言，段落注释是非常少减的，如下：

    /* -----------------
     * TOOLTIPS
     * ----------------- */


这种注释非常让人头疼，能把人逼疯。我能想到为什么会写下这种注释：有时候我们的CSS会写得非常长，当在超过千行的文件内查找时，就需要这种带特殊标志的注释来帮助快速搜索。

但事实上，很长很长的CSS文件已经不再流行了。若你的项目确实需要这种很大的CSS文件，它应该是由多个小的部分，通过CSS预处理工具将它们组合而成的。


不好：语法解释
----------------------------

又要用Bootstrap举例了，以下代码出自 [_tooltips.scss](https://github.com/twbs/bootstrap/blob/v4-dev/scss/_tooltip.scss#L11):

    // Allow breaking very long words so they don't overflow the tooltip's bounds
    // 设置长单词换行
    word-wrap: break-word;


这种方式和“显而易见注释”类似，注释解释`word-wrap`属性的作用。这种注释违反了另外一个注释规则[Why, not what](https://blog.codinghorror.com/code-tells-you-how-comments-tell-you-why/), 所以不需要。

此处有一个例外，由于CSS有很多属性，也许有些属性是你完全不知道的，那么你用这种注释是正常的。

不好：对库进行介绍
---------------------------

如下是Bootstrap tooltips.scss文件的另一段注释：

    // Our parent element can be arbitrary since tooltips are by default inserted as a
    // sibling of their target element. So reset our font and text properties to avoid
    // inheriting weird values.
    // 由于提示框会被默认插入到目标元素后作为一个兄弟元素，
    // 所以需要重置提示框的字体属性避免从父元素继承样式影响。
    @include reset-text();
    font-size: $font-size-sm;


这条注释很有意思，看起来似乎并不违反“说明原因而不是说明作用？”规则，它说明了，由于可能会被一些意料之外的继承字体属性影响，所以用导入的方式来重置字体属性。

但进一步来看，显然在文件头导入重置样式的唯一的解释就是担心被继承样式影响。

所以，我认为这种注释也是不需要的，因为导入函数名字已经说明用途了，尽量让函数名切合作用，如`reset-inherited-font`或类似不仅清晰说明了用途还是说明了原因。这个是一个函数调用，函数名已经足够解释了。优先用这种方式来说明用途可以替代一些注释。

CSS预处理器让CSS更接近传统编程语言。尽可能使用命名良好且有意义的变量和函数，这样能让代码更清晰。

不好: 过时的注释
-----------------

    .dropdown-header {
      …
      white-space: nowrap; // as with > li > a
    }

[“as with > li > a”](https://github.com/twbs/bootstrap/blob/620257456ed0685cae6b6ff51d2ab1e37f02a4fa/scss/_dropdown.scss#L122)是什么意思？我第一反应就是也许在文件中还有一个`> li > a`的选择器，而这行代码就是指那个选择器。也许文件中有一段注释会专门解释为何这样写，但我将文件重头到尾都看了一边，发现并没有这个选择器。文件只有一个`.dropdown-item`选择器下有一个`nowrap`属性，也许是就是指这个？或者也许这段注释是指某行已经被删除的代码或引入其他文件中的代码？若想要彻底弄清楚这个注释的作用，唯一的方法就是翻遍整个git记录了吧。

这是一个过时的注释，也许它以前是有用的，但却长时间没有用到，所以过时了。这也许就是为什么Robert Martin对注释的看法：注释的代码更新了注释就没用了，甚至更糟糕，注释可能会将你引到错误的方向。若你发现这样的注释，一定要删掉它。它完全没用，而且会浪费时间去思考到底有啥用？


有时非常有用的：有用的注释
---------------------------------

如下是一段带注释的代码：

    .dropdown-item {
      display: block;
      width: 100%; // For `<button>`s
      padding: $dropdown-item-padding-y $dropdown-item-padding-x;
      clear: both;
      font-weight: $font-weight-normal;
      color: $dropdown-link-color;
      text-align: inherit; // For `<button>`s
      white-space: nowrap;
      background: none; // For `<button>`s
      border: 0; // For `<button>`s
    }


这样的注释就是有用的，它们能告诉我们，这些特定的属性是为覆盖`<button>`的样式而写的。这样的注释就是有用的，因为有时候代码的意图不是那么显而易见的。

但此时也需要问一个问题：有什么办法能让代码自说明呢？需要可以考虑将这些特定的属性移到第二个选择器中，专门为这些按钮设置的选择器。

    .dropdown-item {
      display: block;
      padding: $dropdown-item-padding-y $dropdown-item-padding-x;
      clear: both;
      font-weight: $font-weight-normal;
      color: $dropdown-link-color;
      white-space: nowrap;
    }

    button.dropdown-item {
      width: 100%;
      text-align: inherit;
      background: none;
      border: 0;
    }

这样就非常清晰且易于理解，但副作用就是：专门增加了一个特殊的选择器。

而相反，我认为这种方式非常利于使用mixin混入模式。重构为一个函数，该函数能在其他地方定义，并且让代码更清晰。考虑如下代码：

    .dropdown-item {
      @include remove-button-styles;

      display: block;
      width: 100%;
      padding: $dropdown-item-padding-y $dropdown-item-padding-x;
      clear: both;
      font-weight: $font-weight-normal;
      color: $dropdown-link-color;
      white-space: nowrap;
    }


这段代码没有用任何注释，但其功用很清晰，因为它使用的公用函数在其他模块也能用到。我将`width:100%`保留下来而不是移到函数中，因为若将函数混和代码时，`width:100%`可能会引起一些其他问题。

在我开始发现[“代码异味”](https://en.wikipedia.org/wiki/Code_smell)之前，一开始`.dropdown-item`代码有十行，我非常喜欢用mixin，mixin函数是一个能极大减少代码行数的好东西，它能让我们快速的知道代码的大致用途。

虽然使用函数重构代码并不是都这样有效，但尽力。

好：注解难懂的修复性的代码
-------------------------------

我对注释也不是总那么苛刻的，比如我就很难找到下面的注释的问题，若你曾看过[normalize.css](https://github.com/necolas/normalize.css/blob/master/normalize.css)的源码，你一定会注意到它满满的注释，不得不说，真是“极好的”注释。

欣赏一番：

    /**
     * 1. Add the correct box sizing in Firefox.
     * FF下正常的盒子模型
     * 2. Show the overflow in Edge and IE.
     * 在Edge和IE下overflow为visble
     */
    hr {
      box-sizing: content-box; /* 1 */
      height: 0; /* 1 */
      overflow: visible; /* 2 */
    }


若没有这些注释，你永远不知道为何这样写。修复特定浏览器bug的代码往往是晦涩难懂的，常常会被当做无用代码删掉。

由于Normalize库的目标是提供一个完全一致样式环境，所以需要很多这些注释。选择器都是类型和属性选择器，没有任何class，由于不是命名的class，所以自文档非常困难。


如下为另一段Bootstrap的注释：

    /* Chrome (OSX) fix for https://github.com/twbs/bootstrap/issues/11245 */
    select {
      background: #fff !important;
    }


一个Github链接，非常有用。即使不打开连接也能知道这儿是一个bug，而且有可能是一个非常难定位的bug。若有需要，可以通过链接获取更多信息。最棒的是，因为没有大段大段的文本去解释bug，所以它并不会打乱代码，并且告诉我们哪里可以获取更多信息。若使用项目与事务跟踪工具如JIRA，那么可以直接在注释中与编号关联起来。

当然，不是每个修复bug的代码都要这样注释，但若bug不是那么容易发现，而且与浏览器怪癖有关，那么还是这样注释吧。

好：指令式注释
------------------------


一些工具如[KSS](https://github.com/kss-node/kss-node) , 会在CSS文件中创建一些样式规范。如下：

    /*
    Alerts
    提示信息：
    An alert box requires a contextual class to specify its importance.
    一个警告信息框需要与语境有关的的类来指定其重要性

    Markup:
    标记：
    <div>
      Take note of this important alert message.
    </div>

    alert-success   - Something good or successful 好的或成功的
    alert-info      - Something worth noting, but not super important 不那么重要的
    alert-warning   - Something to note, may require attention 需要被提示并记录，需要引起注意的
    alert-danger    - Something important. Usually signifies an error. 非常重要的，常用于错误

    Styleguide Alerts
    提示信息样式规范
    */


这不仅仅是注释，这是规范，它能被KSS解析并用于生成HTML。这是文档的一部分，而且不得不说，这比手动创建一个分离的HTML文件要好很多，因为其在同一个文件内且始终与代码相匹配。

另外一种命令式注释为许可信息，当使用第三方库并在注释中注明许可信息时，一般都需要包含指令式。

而我贴出Robert Martin关于注释的话时 [Robert Martin 的话](https://twitter.com/keithjgrant/status/867803638026035200) ，似乎应该加上许可注释，但没有那么做。因为我认为这是一句容易理解的话，若你还在你的代码中到处写注释，那么请先思考是否在做反事儿。

