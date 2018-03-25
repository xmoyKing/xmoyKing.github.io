---
title: 【译】Storage Access API 介绍
categories: Translation
tags:
  - translation
date: 2018-03-13 19:23:23
updated: 2018-03-13 19:23:23
---

# [介绍Storage Access API][12]

2018.2.21

作者：John Wilander

[@johnwilander][13]

In June last year we introduced [Intelligent Tracking Prevention][14] (ITP). ITP is a privacy feature that detects which domains have the ability to track the user cross-site and either partitions the domain’s cookies or purges its website data all together.

去年6月，我们推出了 [Intelligent Tracking Prevention][14]（ITP）（智能防跟踪）。ITP 是一项隐私功能，可以检测一些站点域（domains），那些可以跨站追踪用户的域，并隔离该域的 cookie 或完全清除其站点数据。

The strongest developer feedback we got on ITP was that it needs to provide a way for embedded cross-site content to authenticate users who are already logged in to their first-party services. Today we are happy to provide a solution in the form of Storage Access API. It allows for authenticated embeds while continuing to protect customers’ privacy by default.

在 ITP 上我们获得的最多的开发者反馈意见是，它需要为嵌入的跨站点内容提供一种方式来验证用户，而该用户已登录当前站点。非常棒的是 Storage Access API 提供一个解决方案。它允许对嵌入内容通过验证的同时默认保护客户的隐私。

## Partitioned Cookies and Embedded Content
## 隔离 cookies 和嵌入内容

Let’s say that socialexample.org is embedded on multiple websites to facilitate commenting or “liking” content with the user’s socialexample ID. ITP will detect that such multi-page embeds gives socialexample.org the ability to track the user cross-site and therefore deny embedded content from socialexample.org access to its first-party cookies, providing only partitioned cookies. This breaks the user’s ability to comment and like content unless they have interacted with socialexample.org as first-party site in the last 24 hours. (Please see the [original ITP blog post][15] for the exact rules around partitioned cookies.)

假设 socialexample.org 嵌入到多个网站上，可以让用户通过 socialexample ID 对内容评论或“点赞”。ITP 将检测到此类多次页面嵌入的情况，同时让 socialexample.org 能够追踪跨站点的用户，并拒绝socialexample.org 的嵌入内容访问当前站点的 cookie，仅提供隔离的 cookie。这会禁止用户对内容评论和点赞，除非他们在过去 24 小时内曾直接登录过 socialexample.org 网站。（参阅 [ITP 原博客文章][15] ，了解有关隔离 cookie 的详细规则。）

The same goes for embedded third-party payment providers and embedded third-party videos from subscription services. As soon as ITP detects their tracking abilities, it denies them first-party cookie access outside the 24 hour window, and the embedded content treats the user as logged out even though they are logged in.

对嵌入的第三方支付提供商和订阅服务中嵌入的第三方视频也是如此。只要 ITP 检测到他们的跟踪行为，就会拒绝他们在24小时之外访问当前站点的 cookie，并且嵌入的内容会将用户视为注销状态即使他们已登录。

We’ve made tradeoffs for user privacy. But it would be even better if we could provide the benefits of being logged in to third party iframes, provided that the user is actually interested in using them, while still protecting privacy.

我们对用户隐私进行了权衡。如果我们能够能提供既登录第三方 iframe 的优势，用户又确实有兴趣使用它们的同时保护隐私，那就更好了。

## The Solution: Storage Access API
## 解决方案：Storage Access API

The solution is to allow third-party embeds to request access to their first-party cookies _when the user interacts with them_. To do this, we created the Storage Access API.
解决方案是当用户与当前网站交互时，允许第三方嵌入内容请求访问当前网站的 cookie 。为此，我们创造了 Storage Access API。

The Storage Access API offers two new functions to cross-origin iframes — document.hasStorageAccess() and document.requestStorageAccess(). It also offers the embedding top frame a new [iframe sandbox][16] token — “allow-storage-access-by-user-activation”.

Storage Access API 为跨域 iframe 提供了两项新功能 —— document.hasStorageAccess() 和document.requestStorageAccess()。它还为嵌入的顶级 frame 提供了一个新的  [iframe sandbox][16] token —— “allow-storage-access-by-user-activation”。


Storage access in this context means that the iframe has access to its first-party cookies, i.e. the same cookies it would have access to as a first-party site. Note that **storage access does not relax the same-origin policy in any way**. Specifically, this is not about third-party iframes getting access to the embedding website’s cookies and storage, or vice versa.

在这种情况下，storage access 意味着 iframe 可以访问宿主站点的 cookie，即它可以像宿主站点一样访问当前网站的 cookie。注意，**storage access 不会以任何方式放宽同源策略**。具体来说，这并不是指第三方 iframe 能访问宿主站点的 cookie 和存储，反之亦然。

WebKit’s implementation of the API only covers cookies for now. It does not affect the partitioning of other storage forms such as IndexedDB or LocalStorage.
现在，WebKit 对该 API 的实现仅包含 cookie。它不会影响其他存储形式的隔离，如 IndexedDB 或LocalStorage。

### Check For Storage Access
### 检查 Storage Access

A call to document.hasStorageAccess() returns a promise that resolves with a boolean indicating whether the document already has access to its first-party cookies or not. Should the iframe be same-origin as the top frame, the promise returns true.

调用 document.hasStorageAccess() 会返回一个 promise，该 promise 使用布尔值来指示文档是否已经访问过宿主网站的cookie。如果 iframe 与 顶级 frame 的同源，则 promise 将返回 true。

```
var promise = document.hasStorageAccess();
promise.then(
  function (hasAccess) {
    // 布尔值 hasAccess 表示当前 document 是否访问过
  },
  function (reason) {
    // 由于某些问题 promise 处于 rejected 状态
  }
);

```

### Request Storage Access
### 请求 Storage Access

A call to document.requestStorageAccess() upon a user gesture such as a tap or click returns a promise that is resolved if storage access was granted and is rejected if access was denied. If storage access was granted, a call to document.hasStorageAccess() will return true. The reason why iframes need to call this API explicitly is to offer developers control over when the document’s cookies change.

通过用户的交互（如 tap 或 click）调用 document.requestStorageAccess() 会返回一个 promise，如果允许 storage access，则 promise 将被接收，如果访问被拒绝，则 promise 将被拒绝。如果允许 storage access ，则对 document.hasStorageAccess() 的调用将返回 true。而 iframe 需要显式调用此 API 的原因是为了让开发人员在 cookie 修改时也能操作。

```
<script>
function makeRequestWithUserGesture() {
  var promise = document.requestStorageAccess();
  promise.then(
    function () {
      // 允许 storage access
    },
    function () {
      // 拒绝 storage access
    }
  );
}
</script>
<button onclick="makeRequestWithUserGesture()">Play video</button>

```

The iframe needs to adhere to a set of rules to be able to get storage access granted. The **basic rules** are:

iframe 需要遵循一组规则才能获得 storage access 的权限。**基本规则**是：

*   The iframe’s cookies need to be currently partitioned by ITP. If they’re not, the iframe either already has cookie access or cannot be granted access because its cookies have been purged.
*   The iframe needs to be a direct child of the top frame.
*   The iframe needs to be processing a user gesture at the time of the API call.

*   iframe 的 cookie 需要由 ITP 当场隔离。否则表示 iframe 已经具有 cookie 的访问权限，或由于 cookie 已被清除而不能获取权限。
*   iframe 是顶级 frame 的直系子项`（译注：非 iframe 嵌套的情况）`。
*   在 API 调用时，iframe 需要处理用户的交互。

Below are the **detailed rules** for the promise returned by a call to document.requestStorageAccess(). When we say eTLD+1 we mean effective top-level domain + 1. An eTLD is .com or .co.uk so an example of an eTLD+1 would be social.co.uk but not sub.social.co.uk (eTLD+2) or co.uk (just eTLD).

以下是调用 document.requestStorageAccess() 返回的 promise 的**详细规则**。当我们说 eTLD + 1时，指的是 effective top-level domain + 1（有效的顶级域名+1）。eTLD 指的是 .com 或 .co.uk，所以 social.co.uk 是 eTLD + 1 ，而 sub.social.co.uk （eTLD + 2）或 co.uk（只是 eTLD）不是。

1.  If the sub frame is sandboxed but doesn’t have the tokens “allow-storage-access-by-user-activation” and “allow-same-origin”, reject.
2.  If the sub frame’s parent is not the top frame, reject.
3.  If the browser is not processing a user gesture, reject.
4.  If the sub frames eTLD+1 is equal to the top frame’s eTLD+1, resolve. As an example, login.socialexample.co.uk has the same eTLD+1 as [www.socialexample.co.uk][17].
5.  If the sub frame’s origin’s cookies are currently blocked, reject. This means that ITP has either purged the origin’s website data or will do so in the near future. Thus there is no storage to get access to.
6.  If all the above has passed, resolve.

1. 如果子 frame 是 sandboxed `（译注：指的是处于沙盒隔离状态）`，但没有 “allow-storage-access-by-user-activation” 和 “allow-same-origin” token，则 reject。
1. 如果子 frame 的父级不是顶级 frame，则 reject。
1. 如果浏览器未处理用户的交互，则 reject。
1. 如果子 frame 的 eTLD + 1 等于顶级 frame 的 eTLD + 1，则 resolve。例如，login.socialexample.co.uk 与 [www.socialexample.co.uk][17] 具有相同的 eTLD + 1 。
1. 如果子 frame 的原始 cookie 已被屏蔽，则 reject。这意味着 ITP 已经清空了原始网站的数据，或不久的将来会清空。因此没有 storage 可以访问。
1. 如果以上全部通过了，则 resolve。

### Access Removal
### Access 移除

Storage access is granted for the life of the document as long as the document’s frame is attached to the DOM. This means:

只要文档的 frame 被 DOM 解析，那么在文档的整个生命周期内，storage access 都是允许的。即：
*   在子 frame 导航时 Access 移除。
*   子 frame 与 DOM 分离时， Access 移除。
*   顶级 frame 导航时 Access 移除。
*   当网页消失时，如关闭标签，Access 移除。

### Sandboxed Iframes
### 沙盒 iframe

If the embedding website has sandboxed the iframe, it cannot be granted storage access by default. The embedding website needs to add the sandbox token “allow-storage-access-by-user-activation” to allow successful storage access requests. The iframe sandbox also needs the tokens “allow-scripts” and “allow-same-origin” since otherwise it can’t call the API and doesn’t execute in an origin that can have cookies.

如果宿主网站对 iframe 进行了沙盒处理，则默认情况下不能无 storage access 权限。宿主网站需要添加沙盒“allow-storage-access-by-user-activation”token 来允许 storage access 的请求。iframe 沙盒还需要“allow-scripts”和“allow-same-origin” token，否则它不能调用 API 并且不能在需要 cookie 的宿主站点中执行。

```
<iframe sandbox="allow-storage-access-by-user-activation allow-scripts allow-same-origin"></iframe>

```

## A Note On Potential Abuse
## 关于潜在滥用的说明

We have decided not to prompt the user when an iframe calls the Storage Access API to make the user experience as smooth as possible. ITP’s rules are an effective gatekeeper for who can be granted access, and for the time being we rely on them.

我们决定在 iframe 调用 Storage Access API 时不提示用户，让用户体验尽可能流畅。ITP 规则是对谁可以获准访问的有效防护，并且目前我们依靠这些规则。

However, we will monitor the adoption of the API and make changes if we find widespread abuse where the user is clearly not trying to take some authenticated action in the calling iframe. Such API behavior changes may be prompts, abuse detection resulting in a rejected promise, rate limiting of API calls per origin, and more.

若我们对API的使用情况做监测，一旦发现有滥用的情况，即用户明显并不想在宿主 iframe 中获取认证操作，我们会对该 API 进行修改。此 API 更改后的行为可能是提示，滥用检测（会使 promise reject），站点 API 调用的速率限制等等。

## Availability
## 可用性

Storage Access API is available in Safari 11.1 on iOS 11.3 beta and macOS High Sierra 10.13.4 beta, as well as in Safari Technology Preview 47+. If you’re interested in cross-browser compatibility, please follow the [whatwg/html issue for Storage Access API][18].

Storage Access API 可在 iOS 11.3 beta 和 macOS High Sierra 10.13.4 beta 以及Safari Technology Preview 47+ 上的 Safari 11.1 中使用。如果您对跨浏览器兼容性感兴趣，请关注 [whatwg/html issue for Storage Access API][18]。

## Feedback
## 反馈

Please report bugs through [bugs.webkit.org][19], or send feedback on Twitter to the team [@webkit][20], or our evangelist [@jonathandavis][21]. If you have technical questions about how the Storage Access API works, you can find me on Twitter [@johnwilander][22].

在 [bugs.webkit.org][19] 报告错误，或者在 Twitter 上向团队 [@webkit][20] 或布道人 [@jonathandavis][21] 发送反馈。如果你对 Storage Access API 的工作方式有技术问题，可以在 Twitter [@johnwilander][22] 上找到我。

---

[1]: https://webkit.org/
[2]: https://webkit.org/blog/
[3]: https://webkit.org/downloads/
[4]: https://webkit.org/status/
[5]: https://webkit.org/reporting-bugs/
[6]: https://webkit.org/#nav-sub-menu
[7]: https://webkit.org/getting-started/
[8]: https://webkit.org/contributing-code/
[9]: https://webkit.org/testing-contributions/
[10]: https://webkit.org/policy-and-guidelines/
[11]: http://trac.webkit.org
[12]: https://webkit.org/blog/8124/introducing-storage-access-api/ "Permanent Link: Introducing Storage Access API"
[13]: https://twitter.com/johnwilander
[14]: https://webkit.org/blog/7675/intelligent-tracking-prevention/
[15]: https://webkit.org/blog/7675/intelligent-tracking-prevention/
[16]: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/iframe#attr-sandbox
[17]: http://www.socialexample.co.uk/
[18]: https://github.com/whatwg/html/issues/3338
[19]: http://bugs.webkit.org/
[20]: https://twitter.com/webkit
[21]: https://twitter.com/jonathandavis
[22]: https://twitter.com/johnwilander
[23]: https://webkit.org/blog/8121/release-notes-for-safari-technology-preview-50/
[24]: https://twitter.com/webkit
[25]: https://webkit.org/sitemap/
[26]: http://www.apple.com/legal/privacy/
[27]: https://webkit.org/licensing-webkit/
[28]: https://webkit.org/terms-of-use/