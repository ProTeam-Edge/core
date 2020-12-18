(window.webpackJsonp=window.webpackJsonp||[]).push([[115],{470:function(t,s,a){"use strict";a.r(s);var n=a(55),e=Object(n.a)({},(function(){var t=this,s=t.$createElement,a=t._self._c||s;return a("ContentSlotsDistributor",{attrs:{"slot-key":t.$parent.slotKey}},[a("h1",{attrs:{id:"foxitpdfsdk-网页版集成示例-angular"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#foxitpdfsdk-网页版集成示例-angular"}},[t._v("#")]),t._v(" FoxitPDFSDK 网页版集成示例 - Angular")]),t._v(" "),a("p",[t._v("本指南提供了两个示例。一个示例介绍如何在FoxitPDFSDK for Web中快速运行Angular样板示例，另一个演示如何将FoxitPDFSDK for Web项目集成到您现有的Angular/cli应用程序中。")]),t._v(" "),a("h2",{attrs:{id:"快速运行angular样板示例"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#快速运行angular样板示例"}},[t._v("#")]),t._v(" 快速运行Angular样板示例")]),t._v(" "),a("p",[a("em",[t._v("备注:"),a("code",[t._v("FoxitPDFSDK for Web 根目录")]),t._v("以下简称"),a("code",[t._v("根目录")]),t._v("或"),a("code",[t._v("root")]),t._v(".")])]),t._v(" "),a("p",[t._v("FoxitPDFSDK for Web 为"),a("a",{attrs:{href:"https://www.npmjs.com/package/@angular/cli",target:"_blank",rel:"noopener noreferrer"}},[t._v("@angular/cli"),a("OutboundLink")],1),t._v(" 应用程序提供了样板项目示例。该示例可以在root/integrations/中找到。")]),t._v(" "),a("h3",{attrs:{id:"前提条件"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#前提条件"}},[t._v("#")]),t._v(" 前提条件")]),t._v(" "),a("ul",[a("li",[a("a",{attrs:{href:"https://nodejs.org/en/",target:"_blank",rel:"noopener noreferrer"}},[t._v("Nodejs"),a("OutboundLink")],1),t._v(" and "),a("a",{attrs:{href:"https://www.npmjs.com",target:"_blank",rel:"noopener noreferrer"}},[t._v("npm"),a("OutboundLink")],1)]),t._v(" "),a("li",[a("a",{attrs:{href:"https://developers.foxitsoftware.com/pdf-sdk/Web",target:"_blank",rel:"noopener noreferrer"}},[t._v("FoxitPDFSDK for Web"),a("OutboundLink")],1)])]),t._v(" "),a("h3",{attrs:{id:"开始"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#开始"}},[t._v("#")]),t._v(" 开始")]),t._v(" "),a("p",[t._v("在SDK的 "),a("code",[t._v("root/integratons/angular.js/")]),t._v(" 下，开启命令行终端，执行：")]),t._v(" "),a("div",{staticClass:"language-sh extra-class"},[a("pre",{pre:!0,attrs:{class:"language-sh"}},[a("code",[a("span",{pre:!0,attrs:{class:"token function"}},[t._v("npm")]),t._v(" i\n")])])]),a("p",[t._v("此步骤将执行以下操作：")]),t._v(" "),a("ul",[a("li",[t._v("在当前目录创建一个"),a("code",[t._v("node_modules")]),t._v("文件夹并安装相关依赖。")]),t._v(" "),a("li",[t._v("从SDK根目录复制"),a("code",[t._v("lib")]),t._v("目录到"),a("code",[t._v("root/integrations/angular/src")]),t._v("目录下，并自动把"),a("code",[t._v("lib")]),t._v("重命名为"),a("code",[t._v("foxit-lib")]),t._v("。")])]),t._v(" "),a("h3",{attrs:{id:"运行示例"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#运行示例"}},[t._v("#")]),t._v(" 运行示例")]),t._v(" "),a("p",[t._v("在命令行终端上，，执行以下命令来启动Web应用程序：")]),t._v(" "),a("div",{staticClass:"language-sh extra-class"},[a("pre",{pre:!0,attrs:{class:"language-sh"}},[a("code",[a("span",{pre:!0,attrs:{class:"token function"}},[t._v("npm")]),t._v(" start\n")])])]),a("p",[t._v("现在您可以启动该应用程序了。打开浏览器，在地址栏输入"),a("code",[t._v("<http://localhost:4200>")]),t._v(" 来加载示例。")]),t._v(" "),a("h2",{attrs:{id:"将foxitpdfsdk-for-web-集成到现有的angular项目中"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#将foxitpdfsdk-for-web-集成到现有的angular项目中"}},[t._v("#")]),t._v(" 将FoxitPDFSDK for web 集成到现有的Angular项目中")]),t._v(" "),a("p",[t._v("此集成过程假设您已安装应用程序"),a("code",[t._v("@Angular/cli")]),t._v("。")]),t._v(" "),a("h3",{attrs:{id:"前提条件-2"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#前提条件-2"}},[t._v("#")]),t._v(" 前提条件")]),t._v(" "),a("ul",[a("li",[a("a",{attrs:{href:"https://nodejs.org/en/",target:"_blank",rel:"noopener noreferrer"}},[t._v("Nodejs"),a("OutboundLink")],1),t._v(" and "),a("a",{attrs:{href:"https://www.npmjs.com",target:"_blank",rel:"noopener noreferrer"}},[t._v("npm"),a("OutboundLink")],1)]),t._v(" "),a("li",[a("a",{attrs:{href:"https://www.npmjs.com/package/@angular/cli",target:"_blank",rel:"noopener noreferrer"}},[t._v("@angular/cli"),a("OutboundLink")],1)]),t._v(" "),a("li",[a("a",{attrs:{href:"https://developers.foxitsoftware.com/pdf-sdk/Web",target:"_blank",rel:"noopener noreferrer"}},[t._v("FoxitPDFSDKforWeb"),a("OutboundLink")],1)])]),t._v(" "),a("h3",{attrs:{id:"基础配置"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#基础配置"}},[t._v("#")]),t._v(" 基础配置")]),t._v(" "),a("p",[t._v("让我们将您现有的Angular项目及"),a("code",[t._v("FoxitPDFSDK for Web")]),t._v(" 的根目录简称为 "),a("code",[t._v("AngularJS")]),t._v(" 和 "),a("code",[t._v("SDK")]),t._v(".")]),t._v(" "),a("ol",[a("li",[t._v("在SDK中找到 "),a("code",[t._v("lib")]),t._v(" 文件夹，将其复制到 "),a("code",[t._v("AngularJS/src/")]),t._v("，然后将其重命名为 "),a("code",[t._v("foxit-lib")]),t._v("。 此外，要正确引用字体库，还需要将SDK中的 "),a("code",[t._v("external")]),t._v(" 文件夹复制到 "),a("code",[t._v("AngularJS/src/foxit-lib/assets")]),t._v("。")])]),t._v(" "),a("p",[a("em",[t._v("在 AngularJS 中, 执行以下操作:")])]),t._v(" "),a("ol",{attrs:{start:"2"}},[a("li",[a("p",[t._v("在"),a("code",[t._v("angular.json")]),t._v("中, update "),a("code",[t._v("architect/build")]),t._v(" 选项 "),a("code",[t._v("assets")]),t._v(","),a("code",[t._v("styles")]),t._v(" 和 "),a("code",[t._v("extractCss")]),t._v(", 及"),a("code",[t._v("architect/lint")]),t._v("。 具体如下：")]),t._v(" "),a("div",{staticClass:"language-json extra-class"},[a("pre",{pre:!0,attrs:{class:"language-json"}},[a("code",[a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n  ...\n  "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"build"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"assets"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("[")]),t._v("\n      ..."),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n      "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n        "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"glob"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token string"}},[t._v('"**/*"')]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n        "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"input"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token string"}},[t._v('"src/foxit-lib"')]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n        "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"output"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token string"}},[t._v('"/foxit-lib"')]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n        "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"ignore"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("[")]),a("span",{pre:!0,attrs:{class:"token string"}},[t._v('"PDFViewCtrl.*"')]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token string"}},[t._v('"UIExtension.*"')]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("]")]),t._v("\n      "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("}")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("]")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n   "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"styles"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("[")]),t._v("\n       "),a("span",{pre:!0,attrs:{class:"token string"}},[t._v('"src/foxit-lib/UIExtension.css"')]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n       "),a("span",{pre:!0,attrs:{class:"token string"}},[t._v('"src/styles.css"')]),t._v("\n     "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("]")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n     "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"extractCss"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token boolean"}},[t._v("true")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n     ...\n  "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("}")]),t._v("\n"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("}")]),t._v("\n"),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"lint"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n      "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"builder"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v("..."),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n      "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"options"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n        "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"tsConfig"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("[")]),t._v("\n          "),a("span",{pre:!0,attrs:{class:"token comment"}},[t._v("//existing configuration can remain as they are")]),t._v("\n        "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("]")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n        "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"exclude"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("[")]),t._v("\n          "),a("span",{pre:!0,attrs:{class:"token comment"}},[t._v("// other dependencies you may have")]),t._v("\n          "),a("span",{pre:!0,attrs:{class:"token string"}},[t._v('"src/foxit-lib/**/*.*"')]),t._v("\n        "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("]")]),t._v("\n      "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("}")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("}")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n")])])])]),t._v(" "),a("li",[a("p",[t._v("在 "),a("code",[t._v("tsconfig.app.json")]),t._v(" 中，添加 "),a("code",[t._v('"src/foxit-lib/**/*.*"')]),t._v(" 到 exclude 列表.")]),t._v(" "),a("div",{staticClass:"language-json extra-class"},[a("pre",{pre:!0,attrs:{class:"language-json"}},[a("code",[a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n  ..."),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n  "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"exclude"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("[")]),t._v("\n    ...\n    ..."),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n    ..."),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token string"}},[t._v('"src/foxit-lib/**/*.*"')]),t._v("\n  "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("]")]),t._v("\n"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("}")]),t._v("\n\n")])])])])]),t._v(" "),a("h3",{attrs:{id:"创建组件"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#创建组件"}},[t._v("#")]),t._v(" 创建组件")]),t._v(" "),a("ol",[a("li",[a("p",[t._v("在 AngularJS 中, 启动命令行终端，运行：")]),t._v(" "),a("div",{staticClass:"language-sh extra-class"},[a("pre",{pre:!0,attrs:{class:"language-sh"}},[a("code",[t._v("ng generate component PDFViewer\n")])])])])]),t._v(" "),a("p",[t._v("此步骤将在"),a("code",[t._v("AngularJS/src/app")]),t._v("下创建"),a("code",[t._v("pdfviewer")]),t._v("文件夹及相关组件文件。现在，您需要在"),a("code",[t._v("AngularJS/src/app/")]),t._v("下执行以下步骤：")]),t._v(" "),a("ol",{attrs:{start:"2"}},[a("li",[a("p",[t._v("将 "),a("code",[t._v("license-key.js")]),t._v(" 放入 "),a("code",[t._v("../pdfviewer/")]),t._v(". 您可以在 "),a("code",[t._v("SDK/examples/")]),t._v("找到许可证信息.")])]),t._v(" "),a("li",[a("p",[t._v("更新 "),a("code",[t._v("../pdfviewer/component.ts")]),t._v(". 有关配置的详细信息，请参阅SDK中的对应文件")])]),t._v(" "),a("li",[a("p",[t._v("更新 "),a("code",[t._v("../component.html")]),t._v(" 来传递用于放置WebViewer容器的DOM元素")]),t._v(" "),a("div",{staticClass:"language-html extra-class"},[a("pre",{pre:!0,attrs:{class:"language-html"}},[a("code",[a("span",{pre:!0,attrs:{class:"token tag"}},[a("span",{pre:!0,attrs:{class:"token tag"}},[a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("<")]),t._v("div")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(">")])]),t._v("\n  "),a("span",{pre:!0,attrs:{class:"token tag"}},[a("span",{pre:!0,attrs:{class:"token tag"}},[a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("<")]),t._v("app-foxitpdfviewer")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token attr-name"}},[t._v("#pdfviewer")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token attr-name"}},[t._v("class")]),a("span",{pre:!0,attrs:{class:"token attr-value"}},[a("span",{pre:!0,attrs:{class:"token punctuation attr-equals"}},[t._v("=")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v('"')]),t._v("foxit-pdf-viewer-container"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v('"')])]),t._v("\n  "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(">")])]),a("span",{pre:!0,attrs:{class:"token tag"}},[a("span",{pre:!0,attrs:{class:"token tag"}},[a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("</")]),t._v("app-foxitpdfviewer")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(">")])]),t._v("\n"),a("span",{pre:!0,attrs:{class:"token tag"}},[a("span",{pre:!0,attrs:{class:"token tag"}},[a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("</")]),t._v("div")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(">")])]),t._v("\n")])])])]),t._v(" "),a("li",[a("p",[t._v("按您喜欢的样式更新 "),a("code",[t._v("component.css")]),t._v("。")]),t._v(" "),a("div",{staticClass:"language-css extra-class"},[a("pre",{pre:!0,attrs:{class:"language-css"}},[a("code",[a("span",{pre:!0,attrs:{class:"token selector"}},[t._v(".foxit-pdf-viewer-container")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n  "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v("display")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(":")]),t._v(" block"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n  "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v("margin")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(":")]),t._v(" 0 auto"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n  "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v("width")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(":")]),t._v(" 1280px"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n  "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v("height")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(":")]),t._v(" 1024px"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("}")]),t._v("\n")])])])])]),t._v(" "),a("h3",{attrs:{id:"引用addons"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#引用addons"}},[t._v("#")]),t._v(" 引用Addons")]),t._v(" "),a("p",[t._v("如果你是把SDK 集成到您现有的工程中，您需要先了解一下本章节以了解如何引用插件。 我们将介绍三种引用插件的方式, 您只要根据需要选择选择其中一种就可以了。 关于插件的更多细节，请参考"),a("RouterLink",{attrs:{to:"/zh/ui-extension/插件/introduction.html"}},[t._v("插件")]),t._v(" .")],1),t._v(" "),a("h4",{attrs:{id:"_1-引用-addon-碎片"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#_1-引用-addon-碎片"}},[t._v("#")]),t._v(" 1. 引用 Addon 碎片")]),t._v(" "),a("p",[t._v("碎片化引用是在7.2 及之前的版本中使用。这个方法无需任何配置，在 pdfviewer.component.ts 的onInit方法中构造 PDFUI 实例时按如下方式引用Addon：")]),t._v(" "),a("div",{staticClass:"language-js extra-class"},[a("pre",{pre:!0,attrs:{class:"language-js"}},[a("code",[a("span",{pre:!0,attrs:{class:"token keyword"}},[t._v("this")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(".")]),t._v("pdfui "),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v("=")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token keyword"}},[t._v("new")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token class-name"}},[t._v("UIExtension"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(".")]),t._v("PDFUI")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("(")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n    addons"),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("[")]),t._v("\n        the_path_to_foxit_lib "),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v("+")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token string"}},[t._v("'/uix-addons/file-property/addon.info.json'")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n        the_path_to_foxit_lib "),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v("+")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token string"}},[t._v("'/uix-addons/full-screen/addon.info.json'")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n        "),a("span",{pre:!0,attrs:{class:"token comment"}},[t._v("// .etc")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("]")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token comment"}},[t._v("// other options")]),t._v("\n"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("}")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(")")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n")])])]),a("p",[t._v("其中，the_path_to_foxit_lib 浏览器访问SDK的lib目录地址，和 angular.json的assets配置相关，参考 "),a("a",{attrs:{href:"#basic-setup"}},[t._v("Basic Setup")]),t._v("。")]),t._v(" "),a("h4",{attrs:{id:"_2-模块化导入-addon"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#_2-模块化导入-addon"}},[t._v("#")]),t._v(" 2. 模块化导入 Addon")]),t._v(" "),a("ol",[a("li",[a("p",[t._v("Install")]),t._v(" "),a("div",{staticClass:"language-sh extra-class"},[a("pre",{pre:!0,attrs:{class:"language-sh"}},[a("code",[a("span",{pre:!0,attrs:{class:"token function"}},[t._v("npm")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token function"}},[t._v("install")]),t._v(" -D gulp @foxitsoftware/gulp-merge-addon\n")])])])]),t._v(" "),a("li",[a("p",[t._v("编写合并 addon 的 gulp 任务， 参考 </integrations/angular/gulpfile.js>")])]),t._v(" "),a("li",[a("p",[t._v("修改 package.json 的 scripts 配置， 如下：")]),t._v(" "),a("div",{staticClass:"language-json extra-class"},[a("pre",{pre:!0,attrs:{class:"language-json"}},[a("code",[a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"scripts"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"postinstall"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token string"}},[t._v('"npm run update-sdk"')]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"update-sdk"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token string"}},[t._v('"node bin/setup.js"')]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"merge-addons"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token string"}},[t._v('"gulp merge-addons"')]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"start"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token string"}},[t._v('"npm run merge-addons && ng serve"')]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"build"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token string"}},[t._v('"npm run merge-addons && ng build"')]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"test"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token string"}},[t._v('"npm run merge-addons && ng test"')]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"lint"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token string"}},[t._v('"set NODE_OPTIONS=--max-old-space-size=8192 && ng lint"')]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token property"}},[t._v('"e2e"')]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token string"}},[t._v('"ng e2e"')]),t._v("\n"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("}")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n")])])]),a("p",[t._v("这样每次运行"),a("code",[t._v("npm start")]),t._v("启动服务前会自动合并插件。")])]),t._v(" "),a("li",[a("p",[t._v("引用方法请参考 /integrations/angular/src/app/pdfviewer/pdfviewer.component.ts;")])])]),t._v(" "),a("h4",{attrs:{id:"_3-引用-allinone-js"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#_3-引用-allinone-js"}},[t._v("#")]),t._v(" 3. 引用 allInOne.js")]),t._v(" "),a("p",[t._v("在"),a("code",[t._v("foxit-lib/uix-addons/")]),t._v("目录下有一个已经合并好的allInOne.js，在pdfviewer.component.ts中按下面的方法导入js：")]),t._v(" "),a("div",{staticClass:"language-js extra-class"},[a("pre",{pre:!0,attrs:{class:"language-js"}},[a("code",[a("span",{pre:!0,attrs:{class:"token comment"}},[t._v("// ...")]),t._v("\n"),a("span",{pre:!0,attrs:{class:"token keyword"}},[t._v("import")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v("*")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token keyword"}},[t._v("as")]),t._v(" UIExtension "),a("span",{pre:!0,attrs:{class:"token keyword"}},[t._v("from")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token string"}},[t._v("'path/to/foxit-lib/UIExtension.full.js'")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n"),a("span",{pre:!0,attrs:{class:"token keyword"}},[t._v("import")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v("*")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token keyword"}},[t._v("as")]),t._v(" Addons "),a("span",{pre:!0,attrs:{class:"token keyword"}},[t._v("from")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token string"}},[t._v("'path/to/foxit-lib/uix-addons/allInOne.js'")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n"),a("span",{pre:!0,attrs:{class:"token comment"}},[t._v("// ...")]),t._v("\n")])])]),a("p",[t._v("在 onInit 方法构造 PDFUI 实例时这样传参给PDFUI:")]),t._v(" "),a("div",{staticClass:"language-js extra-class"},[a("pre",{pre:!0,attrs:{class:"language-js"}},[a("code",[a("span",{pre:!0,attrs:{class:"token keyword"}},[t._v("this")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(".")]),t._v("pdfui "),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v("=")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token keyword"}},[t._v("new")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token class-name"}},[t._v("UIExtension"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(".")]),t._v("PDFUI")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("(")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n    addons"),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" Addons"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token comment"}},[t._v("// other options")]),t._v("\n"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("}")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(")")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n")])])]),a("h4",{attrs:{id:"三种方式对比"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#三种方式对比"}},[t._v("#")]),t._v(" 三种方式对比")]),t._v(" "),a("table",[a("thead",[a("tr",[a("th",[t._v("引用方式")]),t._v(" "),a("th",[t._v("配置")]),t._v(" "),a("th",[t._v("增加的浏览器请求")]),t._v(" "),a("th",[t._v("可修改(国际化词条,addon.info.json中的配置==)")])])]),t._v(" "),a("tbody",[a("tr",[a("td",[t._v("碎片化")]),t._v(" "),a("td",[t._v("无")]),t._v(" "),a("td",[t._v("n+")]),t._v(" "),a("td",[t._v("支持修改")])]),t._v(" "),a("tr",[a("td",[t._v("模块化")]),t._v(" "),a("td",[t._v("要配置gulp")]),t._v(" "),a("td",[t._v("0")]),t._v(" "),a("td",[t._v("支持修改，修改后需重新合并")])]),t._v(" "),a("tr",[a("td",[t._v("allInOne")]),t._v(" "),a("td",[t._v("无")]),t._v(" "),a("td",[t._v("一个")]),t._v(" "),a("td",[t._v("不支持修改")])])])]),t._v(" "),a("h3",{attrs:{id:"运行您的应用程序"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#运行您的应用程序"}},[t._v("#")]),t._v(" 运行您的应用程序")]),t._v(" "),a("p",[t._v("在命令行终端，运行：")]),t._v(" "),a("div",{staticClass:"language-sh extra-class"},[a("pre",{pre:!0,attrs:{class:"language-sh"}},[a("code",[a("span",{pre:!0,attrs:{class:"token function"}},[t._v("npm")]),t._v(" start\n")])])]),a("p",[t._v("太棒了，一切准备就绪。在浏览器中，输入 "),a("code",[t._v("<http://localhost:4200>")]),t._v(" 来加载您的应用程序。")])])}),[],!1,null,null,null);s.default=e.exports}}]);