(window.webpackJsonp=window.webpackJsonp||[]).push([[72],{494:function(e,t,r){"use strict";r.r(t);var s=r(56),a=Object(s.a)({},(function(){var e=this,t=e.$createElement,r=e._self._c||t;return r("ContentSlotsDistributor",{attrs:{"slot-key":e.$parent.slotKey}},[r("h1",{attrs:{id:"快速运行demo"}},[r("a",{staticClass:"header-anchor",attrs:{href:"#快速运行demo"}},[e._v("#")]),e._v(" 快速运行demo")]),e._v(" "),r("p",[e._v("运行Foxit PDF SDK for Web demo之前，您需要首先准备一台web服务器。在本指南中，我们将会介绍Foxit PDF SDK for Web包中的demo，以及会以Nginx和Node.js服务为例来介绍如何快速运行Foxit PDF SDK for Web demo。")]),e._v(" "),r("h2",{attrs:{id:"使用nginx-运行demo示例"}},[r("a",{staticClass:"header-anchor",attrs:{href:"#使用nginx-运行demo示例"}},[e._v("#")]),e._v(" 使用Nginx 运行demo示例")]),e._v(" "),r("p",[e._v("以Windows为例，假设您系统已经安装 "),r("a",{attrs:{href:"http://nginx.org/en/download.html",target:"_blank",rel:"noopener noreferrer"}},[e._v("Nginx"),r("OutboundLink")],1),e._v(". 当您运行Nginx服务时，您可以直接修改conf 目录下的'nginx.conf'。在本示例中，我们直接编写一个配置文件来运行Foxit PDF SDK for Web Demo。请按照如下的步骤操作：")]),e._v(" "),r("ol",[r("li",[e._v("下载 FoxitPDFSDKForWeb_7_2_0.zip.")]),e._v(" "),r("li",[e._v("将下载的包解压到一个新的目录，比如解压到 'D:/' 下的 \"FoxitPDFSDKForWeb\"。")]),e._v(" "),r("li",[r("a",{attrs:{href:"https://docs.nginx.com/nginx/admin-guide/basic-functionality/managing-configuration-files/",target:"_blank",rel:"noopener noreferrer"}},[e._v("创建一个Nginx配置文件"),r("OutboundLink")],1),e._v(" 比如，在 'D:/FoxitPDFSDKForWeb' 下创建一个'webpdf.conf' 文件。")]),e._v(" "),r("li",[r("a",{attrs:{href:"https://docs.nginx.com/nginx/admin-guide/web-server/web-server/#locations",target:"_blank",rel:"noopener noreferrer"}},[e._v("设置和配置虚拟服务器"),r("OutboundLink")],1),e._v("。以下是一个配置示例。其中 'D:/FoxitPDFSDKForWeb/' 是SDK所在的路径。")])]),e._v(" "),r("div",{staticClass:"language-configuration extra-class"},[r("pre",{pre:!0,attrs:{class:"language-text"}},[r("code",[e._v('server {\n    listen 8080;\n    server_name 127.0.0.1;\n\n    location / {\n        alias "D:/FoxitPDFSDKForWeb/";\n        charset utf8;\n        index index.html;\n    }\n}\n')])])]),r("ol",{attrs:{start:"5"}},[r("li",[e._v("定位到Nginx的安装路径，在conf 目录下找到 'nginx.conf'，使用include指令来引用新配置文件中的内容。")])]),e._v(" "),r("div",{staticClass:"language- extra-class"},[r("pre",{pre:!0,attrs:{class:"language-text"}},[r("code",[e._v("include D:/FoxitPDFSDKForWeb/webpdf.conf;\n")])])]),r("ol",{attrs:{start:"6"}},[r("li",[e._v("为了使配置文件中的设置生效，您需要重启Nginx服务，或者使用 'nginx -s reload' 命令升级配置而且不需要中断当前请求的处理。")]),e._v(" "),r("li",[r("div",{staticClass:"language- extra-class"},[r("pre",[r("code",[e._v("7) 在浏览器中访问demo。\n")])])])])]),e._v(" "),r("p",[e._v("对于高级web viewer demo，请访问"),r("a",{attrs:{href:"http://127.0.0.1:8080/examples/UIExtension/advanced_webViewer/index.html",target:"_blank",rel:"noopener noreferrer"}},[e._v("index.html"),r("OutboundLink")],1),e._v(".")]),e._v(" "),r("p",[e._v("对于简单web viewer demo，请访问 "),r("a",{attrs:{href:"http://127.0.0.1:8080/examples/PDFViewCtrl/basic_webViewer/index.html",target:"_blank",rel:"noopener noreferrer"}},[e._v("index.html"),r("OutboundLink")],1),e._v(".")]),e._v(" "),r("p",[r("strong",[e._v("备注")]),e._v(" : 您可以按照如上的配置运行demo，但是此时截图(snapshot)功能是不能正常使用的。snapshot的图片不能被缓存到剪贴板，因此您不能根据需要将其粘贴到指定的位置。在这种情况下，请按照如下的步骤建立snapshot服务：")]),e._v(" "),r("ol",[r("li",[e._v("安装node.js 9.0或以上版本，如果已经安装，请跳过此步。")]),e._v(" "),r("li",[e._v('在命令行中，导航到根目录 ("D:/FoxitPDFSDKForWeb")')]),e._v(" "),r("li",[e._v('输入"npm install" 安装相关需要的依赖项')]),e._v(" "),r("li",[e._v('输入"npm run start-snapshot-server" 开启snapshot 服务 (默认端口是3002)。')])]),e._v(" "),r("p",[r("strong",[e._v("备注")]),e._v(" : 如果您需要指定snapshot 服务的端口，您可以在Foxit PDF SDK for Web包中的"),r("a",{attrs:{href:"../server/snapshot/package.json"}},[e._v("server/snapshot/package.json")]),e._v("文件中进行修改。找到默认端口3002，然后根据您的需要对其进行修改。")]),e._v(" "),r("ol",{attrs:{start:"5"}},[r("li",[e._v("在 'D:/FoxitPDFSDKForWeb' 文件夹下的 'webpdf.conf' 文件中配置Nginx 反向代理。")])]),e._v(" "),r("div",{staticClass:"language- extra-class"},[r("pre",{pre:!0,attrs:{class:"language-text"}},[r("code",[e._v('server {\n    listen 8080;\n    server_name 127.0.0.1;\n\n    location / {\n        alias "D:/FoxitPDFSDKForWeb/";\n        charset utf8;\n        index index.html;\n    }\n\n    location ~ ^/snapshot/(.+)$ {\n        proxy_pass http://127.0.0.1:3002/snapshot/$1$is_args$args;\n        proxy_redirect off;\n\n        proxy_request_buffering on;\n\n        proxy_set_header Host $host;\n        proxy_set_header X-Real-IP $remote_addr;\n        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;\n    }\n} \n')])])]),r("ol",{attrs:{start:"6"}},[r("li",[e._v("重启Nginx 服务，然后刷新您的浏览器，则snapshot功能就可以正常使用了。")])]),e._v(" "),r("h2",{attrs:{id:"使用node-js-运行demo示例"}},[r("a",{staticClass:"header-anchor",attrs:{href:"#使用node-js-运行demo示例"}},[e._v("#")]),e._v(" 使用Node.js 运行demo示例")]),e._v(" "),r("p",[e._v("假设您的系统已经安装Node.js 9.0 或者更高版本。请按照如下的步骤运行Foxit PDF SDK for Web demo:")]),e._v(" "),r("ol",[r("li",[e._v("下载FoxitPDFSDKForWeb_7_2_0.zip。")]),e._v(" "),r("li",[e._v("将下载的包解压到一个新的目录，比如解压到 'D:/' 下的 \"FoxitPDFSDKForWeb\"。")]),e._v(" "),r("li",[e._v('在命令行中，导航到上述解压的目录 ("D:/FoxitPDFSDKForWeb")，输入"npm install" 安装相关需要的依赖项，然后输入"npm start" 开启http-server。')]),e._v(" "),r("li",[e._v("在浏览器中访问demo。\n对于高级web viewer demo，请访问"),r("a",{attrs:{href:"http://127.0.0.1:8080/examples/UIExtension/advanced_webViewer/index.html",target:"_blank",rel:"noopener noreferrer"}},[e._v("index.html"),r("OutboundLink")],1),e._v(".")])]),e._v(" "),r("p",[e._v("对于简单web viewer demo，请访问"),r("a",{attrs:{href:"http://127.0.0.1:8080/examples/PDFViewCtrl/basic_webViewer/index.html",target:"_blank",rel:"noopener noreferrer"}},[e._v("index.html"),r("OutboundLink")],1),e._v(".")]),e._v(" "),r("p",[r("strong",[e._v("备注")]),e._v(': 使用这种方法，您不需要配置代理，snapshot功能就可以正常使用。如果您需要指定http-server 和 snapshot服务的端口，您可以在Foxit PDF SDK for Web包中的 "'),r("a",{attrs:{href:"../package.json"}},[e._v("package.json")]),e._v('" 文件中进行端口修改。')]),e._v(" "),r("p",[r("em",[e._v("修改http-server端口，定位到默认端口8080，如下所示，然后根据您的需要进行修改：")])]),e._v(" "),r("div",{staticClass:"language-json extra-class"},[r("pre",{pre:!0,attrs:{class:"language-json"}},[r("code",[r("span",{pre:!0,attrs:{class:"token property"}},[e._v('"serve"')]),r("span",{pre:!0,attrs:{class:"token operator"}},[e._v(":")]),e._v(" "),r("span",{pre:!0,attrs:{class:"token punctuation"}},[e._v("{")]),e._v("\n    "),r("span",{pre:!0,attrs:{class:"token property"}},[e._v('"port"')]),r("span",{pre:!0,attrs:{class:"token operator"}},[e._v(":")]),e._v(" "),r("span",{pre:!0,attrs:{class:"token number"}},[e._v("8080")]),r("span",{pre:!0,attrs:{class:"token punctuation"}},[e._v(",")]),e._v(" \n    "),r("span",{pre:!0,attrs:{class:"token property"}},[e._v('"public"')]),r("span",{pre:!0,attrs:{class:"token operator"}},[e._v(":")]),e._v(" "),r("span",{pre:!0,attrs:{class:"token string"}},[e._v('"/"')]),r("span",{pre:!0,attrs:{class:"token punctuation"}},[e._v(",")]),e._v("\n    "),r("span",{pre:!0,attrs:{class:"token property"}},[e._v('"proxy"')]),r("span",{pre:!0,attrs:{class:"token operator"}},[e._v(":")]),e._v(" "),r("span",{pre:!0,attrs:{class:"token punctuation"}},[e._v("{")]),e._v("\n        "),r("span",{pre:!0,attrs:{class:"token property"}},[e._v('"target"')]),r("span",{pre:!0,attrs:{class:"token operator"}},[e._v(":")]),e._v(" "),r("span",{pre:!0,attrs:{class:"token string"}},[e._v('"http://127.0.0.1:3002"')]),r("span",{pre:!0,attrs:{class:"token punctuation"}},[e._v(",")]),e._v("\n        "),r("span",{pre:!0,attrs:{class:"token property"}},[e._v('"changeOrigin"')]),r("span",{pre:!0,attrs:{class:"token operator"}},[e._v(":")]),e._v(" "),r("span",{pre:!0,attrs:{class:"token boolean"}},[e._v("true")]),e._v("\n    "),r("span",{pre:!0,attrs:{class:"token punctuation"}},[e._v("}")]),e._v("\n"),r("span",{pre:!0,attrs:{class:"token punctuation"}},[e._v("}")]),e._v("\n")])])]),r("p",[r("em",[e._v("修改snapshot服务端口，定位到默认端口3002，如下所示，然后根据您的需要进行修改：(有两处需要修改)")])]),e._v(" "),r("p",[r("code",[e._v('"start-snapshot-server": "node ./server/snapshot/src/index -p 3002",')])]),e._v(" "),r("div",{staticClass:"language-json extra-class"},[r("pre",{pre:!0,attrs:{class:"language-json"}},[r("code",[r("span",{pre:!0,attrs:{class:"token property"}},[e._v('"serve"')]),r("span",{pre:!0,attrs:{class:"token operator"}},[e._v(":")]),e._v(" "),r("span",{pre:!0,attrs:{class:"token punctuation"}},[e._v("{")]),e._v("\n    "),r("span",{pre:!0,attrs:{class:"token property"}},[e._v('"port"')]),r("span",{pre:!0,attrs:{class:"token operator"}},[e._v(":")]),e._v(" "),r("span",{pre:!0,attrs:{class:"token number"}},[e._v("8080")]),r("span",{pre:!0,attrs:{class:"token punctuation"}},[e._v(",")]),e._v(" \n    "),r("span",{pre:!0,attrs:{class:"token property"}},[e._v('"public"')]),r("span",{pre:!0,attrs:{class:"token operator"}},[e._v(":")]),e._v(" "),r("span",{pre:!0,attrs:{class:"token string"}},[e._v('"/"')]),r("span",{pre:!0,attrs:{class:"token punctuation"}},[e._v(",")]),e._v("\n    "),r("span",{pre:!0,attrs:{class:"token property"}},[e._v('"proxy"')]),r("span",{pre:!0,attrs:{class:"token operator"}},[e._v(":")]),e._v(" "),r("span",{pre:!0,attrs:{class:"token punctuation"}},[e._v("{")]),e._v("\n        "),r("span",{pre:!0,attrs:{class:"token property"}},[e._v('"target"')]),r("span",{pre:!0,attrs:{class:"token operator"}},[e._v(":")]),e._v(" "),r("span",{pre:!0,attrs:{class:"token string"}},[e._v('"http://127.0.0.1:3002"')]),r("span",{pre:!0,attrs:{class:"token punctuation"}},[e._v(",")]),e._v("\n        "),r("span",{pre:!0,attrs:{class:"token property"}},[e._v('"changeOrigin"')]),r("span",{pre:!0,attrs:{class:"token operator"}},[e._v(":")]),e._v(" "),r("span",{pre:!0,attrs:{class:"token boolean"}},[e._v("true")]),e._v("\n    "),r("span",{pre:!0,attrs:{class:"token punctuation"}},[e._v("}")]),e._v("\n"),r("span",{pre:!0,attrs:{class:"token punctuation"}},[e._v("}")]),e._v("\n")])])]),r("h2",{attrs:{id:"demo简介"}},[r("a",{staticClass:"header-anchor",attrs:{href:"#demo简介"}},[e._v("#")]),e._v(" Demo简介")]),e._v(" "),r("p",[r("strong",[e._v("以下所示demo均假设运行于8080端口")])]),e._v(" "),r("p",[e._v('"'),r("a",{attrs:{target:"_blank",href:"/examples"}},[e._v("examples")]),e._v('" 文件夹下提供了多种demo以便用户参考。在启动http服务后，您可以通过在浏览器中输入相应的地址来访问和体验demo。')]),e._v(" "),r("ul",[r("li",[r("strong",[e._v("高级web viewer demo")])])]),e._v(" "),r("p",[e._v('该demo集成了Foxit PDF SDK for Web提供的大多数功能，使用了从视图到文档解析的全功能 "UIExtension.full.js"包 (在"lib" 文件夹下)。Demo的源码路径："'),r("a",{attrs:{target:"_blank",href:"/examples/UIExtension/advanced_webViewer/index.html"}},[e._v("examples/UIExtension/advanced_webViewer/index.html")]),e._v('"。\n在浏览器中，通过如下的地址快速访问该demo： '),r("a",{attrs:{href:"http://localhost:8080/examples/UIExtension/advanced_webViewer/index.html",target:"_blank",rel:"noopener noreferrer"}},[e._v("http://localhost:8080/examples/UIExtension/advanced_webViewer/index.html"),r("OutboundLink")],1)]),e._v(" "),r("ul",[r("li",[r("strong",[e._v("简单web viewer demo")]),e._v(" "),r("a",{attrs:{href:"http://localhost:8080/examples/PDFViewCtrl/basic_webViewer/index.html",target:"_blank",rel:"noopener noreferrer"}},[e._v("点击打开demo"),r("OutboundLink")],1)])]),e._v(" "),r("p",[e._v('该demo阐述了如何调用Foxit PDF SDK for Web API 加载PDF文档，以及放大和缩小文档。该demo使用 "lib" 文件夹下的 "PDFViewCtrl.full.js"包。')]),e._v(" "),r("ul",[r("li",[r("strong",[e._v("离线demo")])])]),e._v(" "),r("p",[e._v('该demo阐述了如何注册 "examples/PDFViewCtrl/service-worker" 文件夹下"service-worker.js"，以便在service worker支持的浏览器中更好的缓存核心依赖文件"gsdk.js" 和字体文件，以加快文件二次打开的速度以及用于离线模式。'),r("a",{attrs:{target:"_blank",href:"/examples/PDFViewCtrl/service-worker/cache.html"}},[e._v("源码路径")]),e._v("。")]),e._v(" "),r("p",[r("a",{attrs:{href:"http://localhost:8080/examples/PDFViewCtrl/service-worker/cache.html",target:"_blank",rel:"noopener noreferrer"}},[e._v("点击打开demo"),r("OutboundLink")],1)]),e._v(" "),r("ul",[r("li",[r("strong",[e._v("内嵌DIV应用demo")])])]),e._v(" "),r("p",[e._v("该demo将Foxit PDF SDK for Web的simple UI渲染到指定大小的div容器内。")]),e._v(" "),r("p",[r("a",{attrs:{href:"http://localhost:8080/examples/PDFViewCtrl/div/index.html",target:"_blank",rel:"noopener noreferrer"}},[e._v("点击打开demo"),r("OutboundLink")],1)]),e._v(" "),r("p",[r("RouterLink",{attrs:{to:"/zh/main/examples/PDFViewCtrl/div/index.html"}},[e._v("源码路径")]),e._v("。")],1)])}),[],!1,null,null,null);t.default=a.exports}}]);