(window.webpackJsonp=window.webpackJsonp||[]).push([[92],{462:function(t,e,s){"use strict";s.r(e);var a=s(55),n=Object(a.a)({},(function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("ContentSlotsDistributor",{attrs:{"slot-key":t.$parent.slotKey}},[s("h1",{attrs:{id:"component-selector"}},[s("a",{staticClass:"header-anchor",attrs:{href:"#component-selector"}},[t._v("#")]),t._v(" Component selector")]),t._v(" "),s("p",[t._v("UIExtension provides a css-selector like syntax to make easier to search components.\nIt's usually used to configure the "),s("code",[t._v("target")]),t._v(" property of "),s("RouterLink",{attrs:{to:"/zh/ui-extension/basics/fragments.html"}},[t._v("fragments")]),t._v(" and component search;")],1),t._v(" "),s("h2",{attrs:{id:"syntax"}},[s("a",{staticClass:"header-anchor",attrs:{href:"#syntax"}},[t._v("#")]),t._v(" syntax")]),t._v(" "),s("table",[s("thead",[s("tr",[s("th",[t._v("selector name")]),t._v(" "),s("th",[t._v("example")]),t._v(" "),s("th",[t._v("description")])])]),t._v(" "),s("tbody",[s("tr",[s("td",[t._v("name selector")]),t._v(" "),s("td",[s("code",[t._v("'componentName', 'component_name','component-name', 'component-name1', '1component'")])]),t._v(" "),s("td",[t._v("component name selectors can only includes single-letter, number , underscore or minus charactor")])]),t._v(" "),s("tr",[s("td",[t._v("type selector")]),t._v(" "),s("td",[s("code",[t._v("'@div','@dropdown-menu', '@print:print-dialog'")])]),t._v(" "),s("td",[t._v("component type means the tag name defined in layout template, a type selector should start with "),s("code",[t._v("@")]),t._v(" charactor and single-letter, number, underscore or minus. some time including the component module name sparated with colon charactor.")])]),t._v(" "),s("tr",[s("td",[t._v("star selector")]),t._v(" "),s("td",[s("code",[t._v("'*'")])]),t._v(" "),s("td",[t._v("Select all components")])]),t._v(" "),s("tr",[s("td",[t._v("children selector")]),t._v(" "),s("td",[s("code",[t._v("'selector1>selector2'")])]),t._v(" "),s("td",[t._v("Selects all component which matches "),s("code",[t._v("selector2")]),t._v(" where the parent is "),s("code",[t._v("selector1")])])]),t._v(" "),s("tr",[s("td",[t._v("descendants")]),t._v(" "),s("td",[s("code",[t._v("'selector1 selector2'")])]),t._v(" "),s("td",[t._v("Selects all "),s("code",[t._v("selector2")]),t._v(" components inside "),s("code",[t._v("selector1")])])]),t._v(" "),s("tr",[s("td",[t._v("attribute selector")]),t._v(" "),s("td",[s("code",[t._v("[attr=value]")])]),t._v(" "),s("td",[t._v("Selects all components with property or attribute name of "),s("code",[t._v("attr")]),t._v(" whose value equals to "),s("code",[t._v("value")])])]),t._v(" "),s("tr",[s("td",[t._v("attribute selector")]),t._v(" "),s("td",[s("code",[t._v("[attr^=value]")])]),t._v(" "),s("td",[t._v("Selects all components with property or attribute name of "),s("code",[t._v("attr")]),t._v(" whose value begins with "),s("code",[t._v("value")])])]),t._v(" "),s("tr",[s("td",[t._v("attribute selector")]),t._v(" "),s("td",[s("code",[t._v("[attr$=value]")])]),t._v(" "),s("td",[t._v("Selects all components with property or attribute name of "),s("code",[t._v("attr")]),t._v(" whose value ends with "),s("code",[t._v("value")])])]),t._v(" "),s("tr",[s("td",[t._v("attribute selector")]),t._v(" "),s("td",[s("code",[t._v("[attr*=value]")])]),t._v(" "),s("td",[t._v("Selects all components with property or attribute name of "),s("code",[t._v("attr")]),t._v(" whose value contains with "),s("code",[t._v("value")])])]),t._v(" "),s("tr",[s("td",[t._v("attribute selector")]),t._v(" "),s("td",[s("code",[t._v("[attr!=value]")])]),t._v(" "),s("td",[t._v("Selects all components with property or attribute name of "),s("code",[t._v("attr")]),t._v(" whose value not equals to "),s("code",[t._v("value")])])]),t._v(" "),s("tr",[s("td",[t._v("method selector")]),t._v(" "),s("td",[s("code",[t._v("selector1::childAt(index)")])]),t._v(" "),s("td",[t._v("Selects all components that are all the child at "),s("code",[t._v("index")]),t._v(" of their parents selected by "),s("code",[t._v("selector1")])])]),t._v(" "),s("tr",[s("td",[t._v("method selector")]),t._v(" "),s("td",[s("code",[t._v("selector1::parent()")])]),t._v(" "),s("td",[t._v("Selects all components that are all the parent component of their children slected by "),s("code",[t._v("selector1")])])]),t._v(" "),s("tr",[s("td",[t._v("method selector")]),t._v(" "),s("td",[s("code",[t._v("selector1::allAfter()")])]),t._v(" "),s("td",[t._v("Selects all components of the same level that after the component set selected by "),s("code",[t._v("selector1")])])]),t._v(" "),s("tr",[s("td",[t._v("method selector")]),t._v(" "),s("td",[s("code",[t._v("selector1::allBefore()")])]),t._v(" "),s("td",[t._v("Selects all components of the same level that before the component set selected by "),s("code",[t._v("selector1")])])]),t._v(" "),s("tr",[s("td",[t._v("index-related selector")]),t._v(" "),s("td",[s("code",[t._v("selector1::eq(index)")])]),t._v(" "),s("td",[t._v("Selects the component by index value in components set selected by "),s("code",[t._v("selector1")])])]),t._v(" "),s("tr",[s("td",[t._v("index-related selector")]),t._v(" "),s("td",[s("code",[t._v("selector1::last()")])]),t._v(" "),s("td",[t._v("Selects the last one component of the components set selected by "),s("code",[t._v("selector1")])])]),t._v(" "),s("tr",[s("td",[t._v("index-related selector")]),t._v(" "),s("td",[s("code",[t._v("selector1::first()")])]),t._v(" "),s("td",[t._v("Selects the first one component of the components set selected by "),s("code",[t._v("selector1")]),t._v(", It's equivalent to "),s("code",[t._v("selector1:eq(0)")])])])])]),t._v(" "),s("h2",{attrs:{id:"examples"}},[s("a",{staticClass:"header-anchor",attrs:{href:"#examples"}},[t._v("#")]),t._v(" Examples")]),t._v(" "),s("div",{staticClass:"vuepress-plugin-demo-block__wrapper",staticStyle:{display:"none"},attrs:{"data-config":"%7B%0A%20%20%20%20%22iframeOptions%22%3A%20%7B%0A%20%20%20%20%20%20%20%20%22style%22%3A%20%22height%3A%20500px%22%0A%20%20%20%20%7D%0A%7D%0A","data-type":"vanilla","data-code":"%3Chtml%3E%0A%3C%2Fhtml%3E%0A%3Cscript%3E%0A%20%20%20%20UIExtension.PDFUI.module('custom'%2C%5B%5D)%0A%20%20%20%20%20%20%20%20.controller('customController'%2C%20%7B%0A%20%20%20%20%20%20%20%20%20%20%20%20handle%3A%20function()%20%7B%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20const%20root%20%3D%20this.component.getRoot()%3B%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20const%20contextmenuItems%20%3D%20root.querySelectorAll('fv--page-contextmenu%3E%40contextmenu-item')%3B%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20contextmenuItems.forEach(function(contextmenu)%20%7B%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20contextmenu.element.style.cssText%20%2B%3D%20'color%3A%20red'%3B%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%7D)%0A%20%20%20%20%20%20%20%20%20%20%20%20%7D%0A%20%20%20%20%20%20%20%20%7D)%0A%20%20%20%20var%20CustomRibbonAppearance%20%3D%20UIExtension.appearances.RibbonAppearance.extend(%7B%0A%20%20%20%20%20%20%20%20getDefaultFragments()%20%7B%0A%20%20%20%20%20%20%20%20%20%20%20%20%2F%2F%20remove%20the%20export%20comment%20dropdown%20menu!%0A%20%20%20%20%20%20%20%20%20%20%20%20return%20%5B%7B%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20target%3A%20'home-tab-group-hand%3A%3AchildAt(0)'%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20action%3A%20'after'%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20template%3A%20%60%3Cxbutton%20class%3D%22fv__ui-toolbar-show-text-button%22%3EClick%20me!%3C%2Fxbutton%3E%60%0A%20%20%20%20%20%20%20%20%20%20%20%20%7D%2C%7B%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20target%3A%20'commentlist-export-comment%3A%3Aparent()'%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20action%3A%20'remove'%0A%20%20%20%20%20%20%20%20%20%20%20%20%7D%5D%3B%0A%20%20%20%20%20%20%20%20%7D%0A%20%20%20%20%7D)%3B%0A%0A%20%20%20%20var%20libPath%20%3D%20window.top.location.origin%20%2B%20'%2Flib'%3B%0A%20%20%20%20var%20pdfui%20%3D%20new%20UIExtension.PDFUI(%7B%0A%20%20%20%20%20%20%20%20%20%20%20%20viewerOptions%3A%20%7B%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20libPath%3A%20libPath%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20jr%3A%20%7B%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20licenseSN%3A%20licenseSN%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20licenseKey%3A%20licenseKey%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%7D%0A%20%20%20%20%20%20%20%20%20%20%20%20%7D%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20renderTo%3A%20document.body%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20appearance%3A%20CustomRibbonAppearance%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20addons%3A%20%5B%5D%0A%20%20%20%20%7D)%3B%0A%3C%2Fscript%3E%0A"}},[s("div",{staticClass:"vuepress-plugin-demo-block__display"},[s("div",{staticClass:"vuepress-plugin-demo-block__app"})]),t._v(" "),s("div",{staticClass:"vuepress-plugin-demo-block__code"},[s("div",{staticClass:"language-html extra-class"},[s("pre",{pre:!0,attrs:{class:"language-html"}},[s("code",[s("span",{pre:!0,attrs:{class:"token tag"}},[s("span",{pre:!0,attrs:{class:"token tag"}},[s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("<")]),t._v("html")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(">")])]),t._v("\n"),s("span",{pre:!0,attrs:{class:"token tag"}},[s("span",{pre:!0,attrs:{class:"token tag"}},[s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("</")]),t._v("html")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(">")])]),t._v("\n"),s("span",{pre:!0,attrs:{class:"token tag"}},[s("span",{pre:!0,attrs:{class:"token tag"}},[s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("<")]),t._v("script")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(">")])]),s("span",{pre:!0,attrs:{class:"token script"}},[s("span",{pre:!0,attrs:{class:"token language-javascript"}},[t._v("\n    UIExtension"),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(".")]),s("span",{pre:!0,attrs:{class:"token constant"}},[t._v("PDFUI")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(".")]),s("span",{pre:!0,attrs:{class:"token function"}},[t._v("module")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("(")]),s("span",{pre:!0,attrs:{class:"token string"}},[t._v("'custom'")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("[")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("]")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(")")]),t._v("\n        "),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(".")]),s("span",{pre:!0,attrs:{class:"token function"}},[t._v("controller")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("(")]),s("span",{pre:!0,attrs:{class:"token string"}},[t._v("'customController'")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v(" "),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n            "),s("span",{pre:!0,attrs:{class:"token function-variable function"}},[t._v("handle")]),s("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),s("span",{pre:!0,attrs:{class:"token keyword"}},[t._v("function")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("(")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(")")]),t._v(" "),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n                "),s("span",{pre:!0,attrs:{class:"token keyword"}},[t._v("const")]),t._v(" root "),s("span",{pre:!0,attrs:{class:"token operator"}},[t._v("=")]),t._v(" "),s("span",{pre:!0,attrs:{class:"token keyword"}},[t._v("this")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(".")]),t._v("component"),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(".")]),s("span",{pre:!0,attrs:{class:"token function"}},[t._v("getRoot")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("(")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(")")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n                "),s("span",{pre:!0,attrs:{class:"token keyword"}},[t._v("const")]),t._v(" contextmenuItems "),s("span",{pre:!0,attrs:{class:"token operator"}},[t._v("=")]),t._v(" root"),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(".")]),s("span",{pre:!0,attrs:{class:"token function"}},[t._v("querySelectorAll")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("(")]),s("span",{pre:!0,attrs:{class:"token string"}},[t._v("'fv--page-contextmenu>@contextmenu-item'")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(")")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n                contextmenuItems"),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(".")]),s("span",{pre:!0,attrs:{class:"token function"}},[t._v("forEach")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("(")]),s("span",{pre:!0,attrs:{class:"token keyword"}},[t._v("function")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("(")]),s("span",{pre:!0,attrs:{class:"token parameter"}},[t._v("contextmenu")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(")")]),t._v(" "),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n                    contextmenu"),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(".")]),t._v("element"),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(".")]),t._v("style"),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(".")]),t._v("cssText "),s("span",{pre:!0,attrs:{class:"token operator"}},[t._v("+=")]),t._v(" "),s("span",{pre:!0,attrs:{class:"token string"}},[t._v("'color: red'")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n                "),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("}")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(")")]),t._v("\n            "),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("}")]),t._v("\n        "),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("}")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(")")]),t._v("\n    "),s("span",{pre:!0,attrs:{class:"token keyword"}},[t._v("var")]),t._v(" CustomRibbonAppearance "),s("span",{pre:!0,attrs:{class:"token operator"}},[t._v("=")]),t._v(" UIExtension"),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(".")]),t._v("appearances"),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(".")]),t._v("RibbonAppearance"),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(".")]),s("span",{pre:!0,attrs:{class:"token function"}},[t._v("extend")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("(")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n        "),s("span",{pre:!0,attrs:{class:"token function"}},[t._v("getDefaultFragments")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("(")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(")")]),t._v(" "),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n            "),s("span",{pre:!0,attrs:{class:"token comment"}},[t._v("// remove the export comment dropdown menu!")]),t._v("\n            "),s("span",{pre:!0,attrs:{class:"token keyword"}},[t._v("return")]),t._v(" "),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("[")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n                target"),s("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),s("span",{pre:!0,attrs:{class:"token string"}},[t._v("'home-tab-group-hand::childAt(0)'")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n                action"),s("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),s("span",{pre:!0,attrs:{class:"token string"}},[t._v("'after'")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n                template"),s("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),s("span",{pre:!0,attrs:{class:"token template-string"}},[s("span",{pre:!0,attrs:{class:"token template-punctuation string"}},[t._v("`")]),s("span",{pre:!0,attrs:{class:"token string"}},[t._v('<xbutton class="fv__ui-toolbar-show-text-button">Click me!</xbutton>')]),s("span",{pre:!0,attrs:{class:"token template-punctuation string"}},[t._v("`")])]),t._v("\n            "),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("}")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n                target"),s("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),s("span",{pre:!0,attrs:{class:"token string"}},[t._v("'commentlist-export-comment::parent()'")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n                action"),s("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),s("span",{pre:!0,attrs:{class:"token string"}},[t._v("'remove'")]),t._v("\n            "),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("}")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("]")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n        "),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("}")]),t._v("\n    "),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("}")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(")")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n\n    "),s("span",{pre:!0,attrs:{class:"token keyword"}},[t._v("var")]),t._v(" libPath "),s("span",{pre:!0,attrs:{class:"token operator"}},[t._v("=")]),t._v(" window"),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(".")]),t._v("top"),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(".")]),t._v("location"),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(".")]),t._v("origin "),s("span",{pre:!0,attrs:{class:"token operator"}},[t._v("+")]),t._v(" "),s("span",{pre:!0,attrs:{class:"token string"}},[t._v("'/lib'")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n    "),s("span",{pre:!0,attrs:{class:"token keyword"}},[t._v("var")]),t._v(" pdfui "),s("span",{pre:!0,attrs:{class:"token operator"}},[t._v("=")]),t._v(" "),s("span",{pre:!0,attrs:{class:"token keyword"}},[t._v("new")]),t._v(" "),s("span",{pre:!0,attrs:{class:"token class-name"}},[t._v("UIExtension"),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(".")]),t._v("PDFUI")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("(")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n            viewerOptions"),s("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n                libPath"),s("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" libPath"),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n                jr"),s("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n                    licenseSN"),s("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" licenseSN"),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n                    licenseKey"),s("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" licenseKey\n                "),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("}")]),t._v("\n            "),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("}")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n            renderTo"),s("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" document"),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(".")]),t._v("body"),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n            appearance"),s("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" CustomRibbonAppearance"),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n            addons"),s("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("[")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("]")]),t._v("\n    "),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("}")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(")")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n")])]),s("span",{pre:!0,attrs:{class:"token tag"}},[s("span",{pre:!0,attrs:{class:"token tag"}},[s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("</")]),t._v("script")]),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(">")])]),t._v("\n")])])]),s("div",{staticClass:"language-json extra-class"},[s("pre",{pre:!0,attrs:{class:"language-json"}},[s("code",[s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n    "),s("span",{pre:!0,attrs:{class:"token property"}},[t._v('"iframeOptions"')]),s("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("{")]),t._v("\n        "),s("span",{pre:!0,attrs:{class:"token property"}},[t._v('"style"')]),s("span",{pre:!0,attrs:{class:"token operator"}},[t._v(":")]),t._v(" "),s("span",{pre:!0,attrs:{class:"token string"}},[t._v('"height: 500px"')]),t._v("\n    "),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("}")]),t._v("\n"),s("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("}")]),t._v("\n")])])])]),t._v(" "),s("div",{staticClass:"vuepress-plugin-demo-block__footer"})])])}),[],!1,null,null,null);e.default=n.exports}}]);