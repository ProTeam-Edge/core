(window.webpackJsonp=window.webpackJsonp||[]).push([[62],{434:function(t,s,a){"use strict";a.r(s);var n=a(56),e=Object(n.a)({},(function(){var t=this,s=t.$createElement,a=t._self._c||s;return a("ContentSlotsDistributor",{attrs:{"slot-key":t.$parent.slotKey}},[a("h1",{attrs:{id:"自定义国际化词条资源"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#自定义国际化词条资源"}},[t._v("#")]),t._v(" 自定义国际化词条资源")]),t._v(" "),a("h2",{attrs:{id:"前提"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#前提"}},[t._v("#")]),t._v(" 前提")]),t._v(" "),a("p",[t._v("假设在你的网站根目录下有个 "),a("code",[t._v("asserts/")]),t._v(" 目录，这个目录除了放其它静态资源意外，同时也用来存放国际化词条资源。在后面的文档中，我们使用 "),a("code",[t._v("websiteRoot/assets/")]),t._v(" 来表示这个目录。")]),t._v(" "),a("h2",{attrs:{id:"配置"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#配置"}},[t._v("#")]),t._v(" 配置")]),t._v(" "),a("ol",[a("li",[a("p",[t._v("复制 SDK 的 "),a("code",[t._v("lib/locals")]),t._v(" 目录到 "),a("code",[t._v("websiteRoot/assets/")]),t._v(".")])]),t._v(" "),a("li",[a("p",[t._v("配置 I18N 加载词条的目录")]),t._v(" "),a("div",{staticClass:"language-js extra-class"},[a("pre",{pre:!0,attrs:{class:"language-js"}},[a("code",[a("span",{pre:!0,attrs:{class:"token keyword"}},[t._v("new")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token class-name"}},[t._v("UIExtension"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(".")]),t._v("PDFUI")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("(")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n    i18n"),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n        absolutePath"),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token string"}},[t._v("'websiteRoot/assets/locals'")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("}")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token comment"}},[t._v("// 已忽略其它无关参数...")]),t._v("\n"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("}")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(")")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n")])])])]),t._v(" "),a("li",[a("p",[t._v("添加不同语言的词条\n在 "),a("code",[t._v("websiteRoot/assets/locals")]),t._v(" 目录里面创建一个新目录. 这个目录名字必须是符合标准的语言标识，例如中文： "),a("code",[t._v("zh-CN")]),t._v(". 然后在再在语言目录中创建 "),a("code",[t._v("ui_.json")]),t._v(".")])]),t._v(" "),a("li",[a("p",[t._v("初始化时指定默认语言")]),t._v(" "),a("div",{staticClass:"language-js extra-class"},[a("pre",{pre:!0,attrs:{class:"language-js"}},[a("code",[a("span",{pre:!0,attrs:{class:"token keyword"}},[t._v("new")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token class-name"}},[t._v("UIExtension"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(".")]),t._v("PDFUI")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("(")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n    i18n"),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n        absolutePath"),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token template-string"}},[a("span",{pre:!0,attrs:{class:"token template-punctuation string"}},[t._v("`")]),a("span",{pre:!0,attrs:{class:"token string"}},[t._v("websiteRoot/assets/locals")]),a("span",{pre:!0,attrs:{class:"token template-punctuation string"}},[t._v("`")])]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n        lng"),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token string"}},[t._v("'zh-CN'")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("}")]),t._v("，\n    "),a("span",{pre:!0,attrs:{class:"token comment"}},[t._v("// 已忽略其他无关参数")]),t._v("\n"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("}")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(")")]),t._v("\n")])])])])]),t._v(" "),a("h2",{attrs:{id:"开发环境严重"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#开发环境严重"}},[t._v("#")]),t._v(" 开发环境严重")]),t._v(" "),a("ol",[a("li",[t._v("清理浏览器缓存，确保能够加载到最新的词条；")]),t._v(" "),a("li",[t._v("刷新浏览器，在浏览器开发这工具的 Network 面包可以看到 "),a("code",[t._v("ui_.json")]),t._v(" 已经是指向上面行政的目录，说明配置成功！")])])])}),[],!1,null,null,null);s.default=e.exports}}]);