(window.webpackJsonp=window.webpackJsonp||[]).push([[76],{475:function(e,a,t){"use strict";t.r(a);var c=t(55),i=Object(c.a)({},(function(){var e=this,a=e.$createElement,t=e._self._c||a;return t("ContentSlotsDistributor",{attrs:{"slot-key":e.$parent.slotKey}},[t("h1",{attrs:{id:"cache-management"}},[t("a",{staticClass:"header-anchor",attrs:{href:"#cache-management"}},[e._v("#")]),e._v(" Cache management")]),e._v(" "),t("p",[e._v("webViewer Server uses "),t("code",[e._v("S8_WEBPDF_CACHE_DIRS")]),e._v("to specify a share storage for caching data. The cached contents contains PDF documents, and the parsed document data such as page, annotation, form and ect. In distributed deployments, commonly a file share (i.e. Samba, k8s ceph ) is created and mounted as a cache directory for transmitting and sharing data over nodes.")]),e._v(" "),t("h2",{attrs:{id:"cache-clear-policy"}},[t("a",{staticClass:"header-anchor",attrs:{href:"#cache-clear-policy"}},[e._v("#")]),e._v(" Cache clear policy")]),e._v(" "),t("p",[e._v("Cache clear is controlled by two variables, "),t("code",[e._v("S8_WEBPDF_CACHE_AGE")]),e._v(" and "),t("code",[e._v("S8_WEBPDF_CACHE_MB")]),e._v(", which represents the minimum cache retention time (mins) and the maximum capacity allowed (MB). Additionally, a file is not removed when its last access time is less than 30 minutes.If one or both of these two variables are set, they will be used according to their priority:")]),e._v(" "),t("ul",[t("li",[e._v("If the cache time is <30 mins: No Clear")]),e._v(" "),t("li",[e._v("If the cache duration is > S8_WEBPDF_CACHE_AGE: Clear")]),e._v(" "),t("li",[e._v("If the cache volume is > 0.8*S8_WEBPDF_CACHE_MB: Clear")])]),e._v(" "),t("p",[e._v("When do cache clearing, webViewer Server will sort by the earliest access time and delete item by item until conditions are met.")])])}),[],!1,null,null,null);a.default=i.exports}}]);