(window.webpackJsonp=window.webpackJsonp||[]).push([[52],{445:function(e,t,r){"use strict";r.r(t);var s=r(56),a=Object(s.a)({},(function(){var e=this,t=e.$createElement,r=e._self._c||t;return r("ContentSlotsDistributor",{attrs:{"slot-key":e.$parent.slotKey}},[r("h1",{attrs:{id:"get-started"}},[r("a",{staticClass:"header-anchor",attrs:{href:"#get-started"}},[e._v("#")]),e._v(" Get Started")]),e._v(" "),r("p",[e._v("Foxit PDF SDK for Web Server (webViewer Server ) is a Linux-based server container.  It is a back-end for our Foxit-powered apps such as webViewer to connect to and provides a server-side rendering solution.")]),e._v(" "),r("h2",{attrs:{id:"get-trial-version"}},[r("a",{staticClass:"header-anchor",attrs:{href:"#get-trial-version"}},[e._v("#")]),e._v(" Get trial version")]),e._v(" "),r("p",[e._v("You can pull Foxit PDF SDK for Web Server (webPDF Server) trial image from our docker hub below:")]),e._v(" "),r("div",{staticClass:"language-sh extra-class"},[r("pre",{pre:!0,attrs:{class:"language-sh"}},[r("code",[e._v("docker pull harbor-us.cpdf.io:4430/websdk-sr/master:latest\n")])])]),r("p",[e._v("Get Foxit PDF SDK for Web (webViewer) zip package from our "),r("a",{attrs:{href:"https://developers.foxitsoftware.com/pdf-sdk/free-trial/",target:"_blank",rel:"noopener noreferrer"}},[e._v("web site"),r("OutboundLink")],1),e._v(".")]),e._v(" "),r("h2",{attrs:{id:"run-demo"}},[r("a",{staticClass:"header-anchor",attrs:{href:"#run-demo"}},[e._v("#")]),e._v(" Run Demo")]),e._v(" "),r("p",[e._v("In the webViewer SDK package, navigate to "),r("a",{attrs:{target:"_blank",href:"/examples/UIExtension/complete_webViewer_sr"}},[e._v("complete_webViewer_sr")]),e._v(".")]),e._v(" "),r("p",[e._v("You can also try our online demo at : https://webviewer-demo.foxitsoftware.com/")]),e._v(" "),r("h2",{attrs:{id:"license"}},[r("a",{staticClass:"header-anchor",attrs:{href:"#license"}},[e._v("#")]),e._v(" License")]),e._v(" "),r("p",[e._v("A version with trial license always prints a watermark on any rendered page. To remove the watermark, you should request a formal license from your sales representative. The license contains two files as follows:")]),e._v(" "),r("ul",[r("li",[e._v("websdkserver_key.txt")]),e._v(" "),r("li",[e._v("websdkserver_sn.txt")])]),e._v(" "),r("p",[e._v("You need to copy the whole text string  after "),r("code",[e._v("Sign=")]),e._v(" in the above two files,  and paste into the respectively required field in the "),r("code",[e._v("docker-compose.yml")]),e._v(" file.")]),e._v(" "),r("h2",{attrs:{id:"license-validation-check"}},[r("a",{staticClass:"header-anchor",attrs:{href:"#license-validation-check"}},[e._v("#")]),e._v(" License Validation Check")]),e._v(" "),r("p",[e._v("If you have trouble to getting webPDF Server up, you may check if the license is valid by running the following command on  your  Docker terminal screen:")]),e._v(" "),r("div",{staticClass:"language-sh extra-class"},[r("pre",{pre:!0,attrs:{class:"language-sh"}},[r("code",[r("span",{pre:!0,attrs:{class:"token comment"}},[e._v("# list existing docker containers in running state")]),e._v("\ndocker "),r("span",{pre:!0,attrs:{class:"token function"}},[e._v("ps")]),e._v("\n"),r("span",{pre:!0,attrs:{class:"token comment"}},[e._v("# list the specific container log information")]),e._v("\ndocker logs "),r("span",{pre:!0,attrs:{class:"token string"}},[e._v("'dockerid'")]),e._v(" \n"),r("span",{pre:!0,attrs:{class:"token comment"}},[e._v("# output your log into your current folder on host.")]),e._v("\ndocker logs "),r("span",{pre:!0,attrs:{class:"token string"}},[e._v("'dockerid'")]),e._v(" "),r("span",{pre:!0,attrs:{class:"token operator"}},[e._v(">>")]),e._v(" dockerlog.log\n"),r("span",{pre:!0,attrs:{class:"token comment"}},[e._v('# Filter your log to check if it contains “Library Initialize Error: 7". If yes, the license is invalid. ')]),e._v("\n"),r("span",{pre:!0,attrs:{class:"token function"}},[e._v("cat")]),e._v(" dockerlog.log "),r("span",{pre:!0,attrs:{class:"token operator"}},[e._v("|")]),e._v(" "),r("span",{pre:!0,attrs:{class:"token function"}},[e._v("grep")]),e._v(" "),r("span",{pre:!0,attrs:{class:"token string"}},[e._v("'Library Initialize Error'")]),e._v(" \n")])])]),r("p",[e._v("If you get the return like "),r("code",[e._v("java.lang.Exception: Library Initialize Error: 7'")]),e._v(" which means the license is incorrect.  Double check if the license string you input is valid.")]),e._v(" "),r("p",[e._v("If you get the return like "),r("code",[e._v("INFO [com.foxit.webpdf.gsdk.GsdkDllLoader] - <GSDK Library: 7.2.0.0603>")]),e._v("  which shows your license is working happily.")])])}),[],!1,null,null,null);t.default=a.exports}}]);