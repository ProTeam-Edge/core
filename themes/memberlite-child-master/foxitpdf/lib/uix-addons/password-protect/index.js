!function(e,t){"object"==typeof exports&&"object"==typeof module?module.exports=t(require("UIExtension")):"function"==typeof define&&define.amd?define(["UIExtension"],t):"object"==typeof exports?exports.PasswordProtectAddon=t(require("UIExtension")):e.PasswordProtectAddon=t(e.UIExtension)}(self,(function(e){return function(e){var t={};function r(o){if(t[o])return t[o].exports;var n=t[o]={i:o,l:!1,exports:{}};return e[o].call(n.exports,n,n.exports,r),n.l=!0,n.exports}return r.m=e,r.c=t,r.d=function(e,t,o){r.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:o})},r.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},r.t=function(e,t){if(1&t&&(e=r(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var o=Object.create(null);if(r.r(o),Object.defineProperty(o,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var n in e)r.d(o,n,function(t){return e[t]}.bind(null,n));return o},r.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return r.d(t,"a",t),t},r.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},r.p="",r(r.s=3)}([function(t,r){t.exports=e},function(e,t,r){"use strict";e.exports=r(7)},function(e,t,r){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var o=r(9);Object.defineProperty(t,"MARKUP_ANNOTATION_STATE",{enumerable:!0,get:function(){return(e=o,e&&e.__esModule?e:{default:e}).default;var e}});t.BORDER_STYLE={SOLID:0,DASHED:1,UNDERLINE:2,BEVELED:3,INSET:4,CLOUDY:5},t.POINT_TYPE={MOVE_TO:1,LINE_TO:2,LINE_TO_CLOSE_FIGURE:3,BEZIER_TO:4,BEZIER_TO_CLOSE_FIGURE:5},t.Intents={LINE_ARROW:"LineArrow",LINE_DIMENSION:"LineDimension",POLYGON_CLOUD:"PolygonCloud",POLYGON_DIMENSION:"PolygonDimension",POLYLINE_DIMENSION:"PolyLineDimension",CIRCLE_DIMENSION:"CircleDimension",FREETEXT_TYPEWRITER:"FreeTextTypewriter",FREETEXT_CALLOUT:"FreeTextCallout",STRIKEOUT_TEXTEDIT:"StrikeOutTextEdit"};var n=t.LineEndingName={0:"None",1:"Square",2:"Circle",3:"Diamond",4:"OpenArrow",5:"ClosedArrow",6:"Butt",7:"ROpenArrow",8:"RClosedArrow",9:"Slash"};t.LineEndingStyle=Object.keys(n).reduce((function(e,t){return e[n[t]]=Number(t),e}),{}),t.LINE_CAPTION_POSITION={POS_INLINE:0,POS_TOP:1},t.AnnotFlagsName={0:"invisible",1:"hidden",2:"print",3:"nozoom",4:"norotate",5:"noview",6:"readonly",7:"locked",8:"togglenoview"},t.PDFDocPermission={PrintLowQuality:4,ModifyDocument:8,Extract:16,AnnotForm:32,FillForm:256,ExtractAccess:512,Assemble:1024,PrintHighQuality:2048},t.MeasureType={MeasureTypeX:0,MeasureTypeY:1,MeasureTypeD:2,MeasureTypeA:3,MeasureTypeT:4,MeasureTypeS:5},t.STORE_NAMES={SELECTION_INFO:"viewer--store-selection-info"},t.CREATING_MEASUREMENT_CONTEXTMENU="creating-measurement-contextmenu",t.HIGHLIGHTING_MODE={NONE:0,INVERT:1,OUTLINE:2,PUSH:3,TOGGLE:4},t.RESOLVED_PROMISE_VOID=Promise.resolve(),t.DISTANCE_TOLERANCE=2,t.POINT_TOLERANCE=.01,t.POS_TYPE={FIRST:0,LAST:1,AFTER:2,BEFORE:3},t.STANDARD_HTML_ELEMENTS=["html","a","abbr","address","area","article","aside","audio","b","base","bdi","bdo","blockquote","body","br","button","canvas","caption","cite","code","col","colgroup","data","datalist","dd","del","details","dfn","dialog","div","dl","dt","em","embed","fieldset","figure","footer","form","h1","h2","h3","h4","h5","h6","head","header","hgroup","hr","html","i","iframe","img","input","ins","kbd","keygen","label","legend","li","link","main","map","mark","menu","menuitem","meta","meter","nav","noscript","object","ol","optgroup","option","output","p","param","pre","progress","q","rb","rp","rt","rtc","ruby","s","samp","script","section","select","small","source","span","strong","style","sub","summary","sup","table","tbody","td","template","textarea","tfoot","th","thead","time","title","tr","track","u","ul","var","video","wbr"],t.EMPTY_STR=""},function(e,t,r){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var o=function(e){if(e&&e.__esModule)return e;var t={};if(null!=e)for(var r in e)Object.prototype.hasOwnProperty.call(e,r)&&(t[r]=e[r]);return t.default=e,t}(r(0)),n=d(r(4));r(10);var s=d(r(11)),a=d(r(12)),i=d(r(13)),c=d(r(14)),p=d(r(15));function d(e){return e&&e.__esModule?e:{default:e}}function l(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function u(e,t){if(!e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return!t||"object"!=typeof t&&"function"!=typeof t?e:t}function f(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function, not "+typeof t);e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,enumerable:!1,writable:!0,configurable:!0}}),t&&(Object.setPrototypeOf?Object.setPrototypeOf(e,t):function(e,t){for(var r=Object.getOwnPropertyNames(t),o=0;o<r.length;o++){var n=r[o],s=Object.getOwnPropertyDescriptor(t,n);s&&s.configurable&&void 0===e[n]&&Object.defineProperty(e,n,s)}}(e,t))}var v=(0,c.default)(),w=(0,p.default)(),h=function(e){function t(){return l(this,t),u(this,e.apply(this,arguments))}return f(t,e),t.getName=function(){return"password-protect"},t.getLoader=function(){return i.default},t.initOnLoad=function(){var e=o.modular.module("password-protect",[]),t=e.getRegistry();this.module=e,t.registerComponent(n.default),e.registerPreConfiguredComponent("password-protect-button",{template:v,config:[{target:"password-protect-btn",tooltip:{title:"password-protect:buttons.title"},callback:s.default}]}),e.registerPreConfiguredComponent("remove-protect-button",{template:w,config:[{target:"remove-protect-btn",tooltip:{title:"password-protect:buttons.removeTitle"},callback:a.default}]})},t.prototype.fragments=function(){return[]},t}(o.UIXAddon);t.default=h},function(e,t,r){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var o=function(e){if(e&&e.__esModule)return e;var t={};if(null!=e)for(var r in e)Object.prototype.hasOwnProperty.call(e,r)&&(t[r]=e[r]);return t.default=e,t}(r(0));r(5);var n,s=r(6),a=(n=s)&&n.__esModule?n:{default:n},i=r(2);function c(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function, not "+typeof t);e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,enumerable:!1,writable:!0,configurable:!0}}),t&&(Object.setPrototypeOf?Object.setPrototypeOf(e,t):function(e,t){for(var r=Object.getOwnPropertyNames(t),o=0;o<r.length;o++){var n=r[o],s=Object.getOwnPropertyDescriptor(t,n);s&&s.configurable&&void 0===e[n]&&Object.defineProperty(e,n,s)}}(e,t))}var p=void 0,d=function(e){function t(r,o,n){!function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,t);var s=function(e,t){if(!e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return!t||"object"!=typeof t&&"function"!=typeof t?e:t}(this,e.call(this,r,o,n));return s.userPasswordChanged=!1,s.destroyDataHooks=[],p=n.i18next,s.addDestroyHook((function(){s.destroyDataHooks.forEach((function(e){return e()}))})),s}return c(t,e),t.getName=function(){return"password-protect-popup"},t.prototype.bindEvent=function(e,t,r){var o=this,n=this.element.querySelectorAll(e);0!=n.length&&n.forEach((function(e){e.addEventListener(t,r),o.destroyDataHooks.push((function(){e.removeEventListener(t,r)}))}))},t.prototype.render=function(){e.prototype.render.call(this),this.element.classList.add("fv__password-protect-popup")},t.prototype.setSubmitCallback=function(e){this.submitCallback=e},t.prototype.getFormDataAndCheckDataValid=function(){var e=document.querySelectorAll("input[name='algorithm']"),t=void 0,r={userPassword:!1,ownerPassword:!1,permission:!1,algorithm:!1,encryptMetadata:!1};e.forEach((function(e){e.checked&&(t=e.value)}));var o=void 0;if(this.element.querySelector(".fv__user-password-checkbox").checked){o=this.element.querySelector(".fv__user-password1").value;var n=this.element.querySelector(".fv__user-password2").value;if(o!==n){if(this.element.querySelector(".fv__password-btn").setAttribute("disabled",""),""!==o&&""!==n){var s=p.t("password-protect:error.open-pwd-not-match");this.showError(s)}return!1}}else o=this.userPasswordChanged?i.EMPTY_STR:"aes256"==t?-1:this.originData.userPassword;var a=void 0,c=void 0,d=void 0,l=void 0,u=void 0;if(this.element.querySelector(".fv__owner-password-checkbox").checked){a=this.element.querySelector(".fv__owner-password1").value;var f=this.element.querySelector(".fv__owner-password2").value;if(a!==f){if(this.element.querySelector(".fv__password-btn").setAttribute("disabled",""),""!==a&&""!==f){var v=p.t("password-protect:error.permission-pwd-not-match");this.showError(v)}return!1}c=this.element.querySelector(".fv__permission-print").value,d=this.element.querySelector(".fv__permission-accessibility").checked,l=this.element.querySelector(".fv__permission-copy").checked,u=this.element.querySelector(".fv__permission-change").value}else a=this.originData.ownerPassword,c=this.originData.print,d=this.originData.accessibility,l=this.originData.copy,u=this.originData.change;if(!(o&&-1!==o||a))return this.element.querySelector(".fv__password-btn").setAttribute("disabled",""),this.hideError(),!1;if(o===a){var w=p.t("password-protect:error.open-owner-pwd-cannot-same");return this.showError(w),this.element.querySelector(".fv__password-btn").setAttribute("disabled",""),!1}var h=!this.element.querySelector(".fv__encrypt-not-metadata").checked,_=65532;return c<2&&(_^=2048,c<1&&(_^=4)),d||(_^=512),l||(_^=16),4===(u=+u)?_^=1024:3===u?(_^=8,_^=1024):2===u?(_^=8,_^=32,_^=1024):1===u?(_^=8,_^=32,_^=256):0===u&&(_^=8,_^=32,_^=256,_^=1024),_+=4294901760,this.hideError(),this.element.querySelector(".fv__password-btn").removeAttribute("disabled"),this.userPasswordChanged&&(r.userPassword=!0),a!==this.originData.ownerPassword&&(r.ownerPassword=!0),[o,a,_,t,h,r]},t.prototype.updateData=function(e){var t=this;this.userPasswordChanged=!1,this.originData=e,this.destroyDataHooks.forEach((function(e){return e()})),this.element.querySelector(".fv__ui-layer-panel").innerHTML=(0,a.default)(e),this.localize(),this.bindEvent(".fv__show-password","click",(function(e){var t=e.srcElement;t.classList.contains("fv__hide-password")?(t.classList.remove("fv__hide-password"),t.previousElementSibling.type="password"):(t.classList.add("fv__hide-password"),t.previousElementSibling.type="text")})),this.bindEvent(".fv__password","keyup",(function(e){e.srcElement.value=e.srcElement.value.replace(/[^\x00-\x80]/g,""),t.getFormDataAndCheckDataValid()})),this.bindEvent(".fv__password-btn","click",(function(e){t.submit()})),this.bindEvent(".fv__password-cancel","click",(function(e){t.hide()}));var r=this.element.querySelector(".fv__user-password-checkbox"),o=this.element.querySelectorAll(".fv__user-password-input input"),n=e.userPassword&&e.userPassword.length>0;r.checked=n,o.forEach((function(e){return e.disabled=!n})),this.bindEvent(".fv__user-password-checkbox","change",(function(e){t.element.querySelectorAll(".fv__user-password-container input").forEach((function(r){r!==e.target&&(r.disabled=!e.target.checked,r.disabled&&(r.value=""),t.userPasswordChanged=!0)})),t.getFormDataAndCheckDataValid()})),this.bindEvent(".fv__owner-password-checkbox","change",(function(e){t.element.querySelectorAll(".fv__owner-password-container input,.fv__owner-password-container select").forEach((function(t){t!==e.target&&(t.disabled=!e.target.checked,t.disabled&&(t.value=""))})),t.getFormDataAndCheckDataValid()}));var s=!0;this.bindEvent(".fv__algorithm-radio","change",(function(e){if(s&&e.target.checked&&"aes128"===e.target.value&&""===t.originData.userPassword&&""!==t.originData.ownerPassword&&"aes256"===t.originData.cipherType){var r=t.element.querySelector(".fv__user-password1").value,o=t.element.querySelector(".fv__user-password2").value;if(""===r&&""===o){t.element.querySelector(".fv__user-password-checkbox").checked=!0,t.element.querySelector(".fv__user-password1").removeAttribute("disabled"),t.element.querySelector(".fv__user-password2").removeAttribute("disabled");var n=p.t("password-protect:error.security-reset-pwd");t.showError(n,!0),s=!1}}}))},t.prototype.submit=function(){var e=this.getFormDataAndCheckDataValid();return this.submitCallback.apply(this,function(e){if(Array.isArray(e)){for(var t=0,r=Array(e.length);t<e.length;t++)r[t]=e[t];return r}return Array.from(e)}(e))},t.prototype.showError=function(e){var t=arguments.length>1&&void 0!==arguments[1]&&arguments[1];t?(this.element.querySelector(".fv__password-protect-error .fv__password-protect-error-header").innerHTML=p.t("password-protect:dialog.warn"),this.element.querySelector(".fv__password-protect-error").classList.add("fv__warn")):(this.element.querySelector(".fv__password-protect-error .fv__password-protect-error-header").innerHTML=p.t("password-protect:dialog.error"),this.element.querySelector(".fv__password-protect-error").classList.remove("fv__warn")),this.element.querySelector(".fv__password-protect-error").classList.remove("fv__hide"),this.element.querySelector(".fv__password-protect-error .fv__password-protect-error-msg").innerHTML=e},t.prototype.hideError=function(){this.element.querySelector(".fv__password-protect-error").classList.add("fv__hide")},t}(o.widgets.LayerComponent);t.default=d},function(e,t,r){},function(e,t,r){var o=r(1);e.exports=function(e){"use strict";e=e||{};var t="",r=o.$escape;return t+='<div class="fv__ui-password-protect-popup-body">\n    <div class="fv__password-protect-error fv__hide">\n        <div class="fv__password-protect-error-header"></div>\n        <div class="fv__password-protect-error-msg"></div>\n    </div>\n    <div class="fv__password-caption" data-i18n="password-protect:dialog.doc-open-caption"></div>\n    <div class="fv__user-password-container fv__password-container">\n        <label class="check-box-wrapper">\n            <input type="checkbox" class="fv__user-password-checkbox checkbox-input" ',(e.userPassword||e.ownerPassword)&&(t+="checked"),t+=' />\n            <span class="checkbox-inner"></span>\n            <span data-i18n="password-protect:dialog.password-check-hint"></span>\n        </label>\n        <div class="fv__user-password-input">\n            <div class="fv__password-input">\n                <span data-i18n="password-protect:dialog.password-label"></span>\n                <input type="password" class="fv__password fv__user-password1" value="',t+=r(e.userPassword),t+='" ',e.userPassword||e.ownerPassword||(t+="disabled"),t+=' onpaste="return false;" />\n                <div class="fv__show-password fv__show-password-user1"></div>\n            </div>\n            <div class="fv__password-input">\n                <span data-i18n="password-protect:dialog.password-confirm-label"></span>\n                <input type="password" class="fv__password fv__user-password2" value="',t+=r(e.userPassword),t+='" ',e.userPassword||e.ownerPassword||(t+="disabled"),t+=' onpaste="return false;" />\n                <div class="fv__show-password fv__show-password-user2"></div>\n            </div>\n            <div style="clear:both"></div>\n        </div>\n    </div>\n    <div class="fv__password-caption" data-i18n="password-protect:dialog.document-restriction-caption"></div>\n    <div class="fv__owner-password-container fv__password-container document-permission-wrapper">\n        <label class="check-box-wrapper">\n            <input type="checkbox" class="fv__owner-password-checkbox checkbox-input" ',e.ownerPassword&&(t+="checked"),t+=' />\n            <span class="checkbox-inner"></span>\n            <span data-i18n="password-protect:dialog.add-document-restriction"></span>\n        </label>\n        <div class="fv__owner-password-input">\n            <div class="fv__password-input">\n                <span data-i18n="password-protect:dialog.password-label"></span>\n                <input type="password" class="fv__password fv__owner-password1" value="',t+=r(e.ownerPassword),t+='" ',e.ownerPassword||(t+="disabled"),t+=' onpaste="return false;" />\n                <div class="fv__show-password fv__show-password-owner1"></div>\n            </div>\n            <div class="fv__password-input">\n                <span data-i18n="password-protect:dialog.password-confirm-label"></span>\n                <input type="password" class="fv__password fv__owner-password2" value="',t+=r(e.ownerPassword),t+='" ',e.ownerPassword||(t+="disabled"),t+=' onpaste="return false;" />\n                <div class="fv__show-password fv__show-password-owner2"></div>\n            </div>\n            <div style="clear:both"></div>\n        </div>\n        <div class="permission-detail-wrapper">\n            <div class="fv__password-caption" data-i18n="password-protect:dialog.permission-specific-caption"></div>\n            <div class="fv__password-permissions fv__password-container online-password-permissions">\n                \x3c!-- 访问权限 --\x3e\n                <label class="check-box-wrapper">\n                    <div class="read-checkbox-wrapper">\n                        <input type="checkbox" class="fv__permission-accessibility checkbox-input" ',e.ownerPassword||(t+="disabled"),t+=" ",e.accessibility&&(t+=" checked"),t+=' />\n                        <span class="checkbox-inner"></span>\n                        <span class="fv__permission-label" data-i18n="password-protect:dialog.perm-access-label"></span>\n                    </div>\n                </label>\n                \x3c!-- 访问权限 --\x3e\n\n                \x3c!-- 复制权限 --\x3e\n                <label class="check-box-wrapper">\n                    <div>\n                        <input type="checkbox" class="fv__permission-copy checkbox-input" ',e.ownerPassword||(t+="disabled"),t+=" ",e.copy&&(t+=" checked"),t+=' />\n                        <span class="checkbox-inner"></span>\n                        <span class="fv__permission-label" data-i18n="password-protect:dialog.perm-copy-label"></span>\n                    </div>\n                </label>\n                \x3c!-- 复制权限 --\x3e\n\n                <div class="print-change-wrapper">\n                    \x3c!-- 打印 --\x3e\n                    <div class="print-wrapper">\n                        <span class="fv__permission-label online-label" data-i18n="password-protect:dialog.printing-label"></span>\n                        <select class="fv__permission-print online-print-select" ',e.ownerPassword||(t+="disabled"),t+='>\n                            <option value="0" ',0==e.print&&(t+=" selected"),t+=' data-i18n="password-protect:dialog.printing-none"></option>\n                            <option value="1" ',1==e.print&&(t+=" selected"),t+=' data-i18n="password-protect:dialog.printing-low"></option>\n                            <option value="2" ',2==e.print&&(t+=" selected"),t+=' data-i18n="password-protect:dialog.printing-high"></option>\n                        </select>\n                    </div>\n                    \x3c!-- 打印 --\x3e\n\n                    \x3c!-- 更改 --\x3e\n                    <div class="change-wrapper">\n                        <span class="fv__permission-label online-label" data-i18n="password-protect:dialog.perm-change-label"></span>\n                        <select class="fv__permission-change online-change-select" ',e.ownerPassword||(t+="disabled"),t+='>\n                            <option value="0" ',0==e.change&&(t+=" selected"),t+=' data-i18n="password-protect:dialog.perm-changes-none"></option>\n                            <option value="1" ',1==e.change&&(t+=" selected"),t+=' data-i18n="password-protect:dialog.perm-changes-1"></option>\n                            <option value="2" ',2==e.change&&(t+=" selected"),t+=' data-i18n="password-protect:dialog.perm-changes-2"></option>\n                            <option value="3" ',3==e.change&&(t+=" selected"),t+=' data-i18n="password-protect:dialog.perm-changes-3"></option>\n                            <option value="4" ',4==e.change&&(t+=" selected"),t+=' data-i18n="password-protect:dialog.perm-changes-4"></option>\n                        </select>\n                    </div>\n                    \x3c!-- 更改 --\x3e\n                </div>\n            </div>\n        </div>\n    </div>\n    <div class="encrypt-config-wrapper">\n        <div class="fv__password-caption" data-i18n="password-protect:dialog.encrypt-caption"></div>\n        <div class="fv__encrypt-setting-container fv__password-container">\n            <div>\n                <label class="check-box-wrapper">\n                    <input type="checkbox" class="fv__encrypt-not-metadata checkbox-input" ',e.isEncryptMetadata||(t+="checked"),t+=' />\n                    <span class="checkbox-inner"></span>\n                    <span data-i18n="password-protect:dialog.enc-no-metadata"></span>\n                </label>\n            </div>\n            <div>\n                <span data-i18n="password-protect:dialog.enc-alg-label"></span>\n                <label style="margin-right: 22px">\n                    <input name="algorithm" class="fv__algorithm-radio online-radio-input" type="radio" value="aes128" ',"aes128"==e.cipherType&&(t+="checked"),t+=' />\n                    <span class="online-radio-inner"></span>128-bit AES\n                </label>\n                <label>\n                    <input name="algorithm" class="fv__algorithm-radio online-radio-input" type="radio" value="aes256" ',"aes256"==e.cipherType&&(t+="checked"),t+=' />\n                    <span class="online-radio-inner"></span>256-bit AES\n                </label>\n            </div>\n        </div>\n    </div>\n</div>\n<div class="fv__button-container">\n    <button type="button" class="fv__password-cancel fv__ui-dialog-cancel-button online-btn" data-i18n="password-protect:dialog.cancel"></button>\n    <button type="button" class="fv__password-btn fv__ui-dialog-ok-button online-btn" ',e.ownerPassword||e.userPassword||(t+="disabled"),t+=' data-i18n="password-protect:dialog.ok"></button>\n</div>'}},function(e,t,r){"use strict";(function(t){
/*! art-template@runtime | https://github.com/aui/art-template */
var r="undefined"!=typeof self?self:"undefined"!=typeof window?window:void 0!==t?t:{},o=Object.create(r),n=/["&'<>]/;o.$escape=function(e){return function(e){var t=""+e,r=n.exec(t);if(!r)return e;var o="",s=void 0,a=void 0,i=void 0;for(s=r.index,a=0;s<t.length;s++){switch(t.charCodeAt(s)){case 34:i="&#34;";break;case 38:i="&#38;";break;case 39:i="&#39;";break;case 60:i="&#60;";break;case 62:i="&#62;";break;default:continue}a!==s&&(o+=t.substring(a,s)),a=s+1,o+=i}return a!==s?o+t.substring(a,s):o}(function e(t){"string"!=typeof t&&(t=null==t?"":"function"==typeof t?e(t.call(t)):JSON.stringify(t));return t}(e))},o.$each=function(e,t){if(Array.isArray(e))for(var r=0,o=e.length;r<o;r++)t(e[r],r);else for(var n in e)t(e[n],n)},e.exports=o}).call(this,r(8))},function(e,t){var r;r=function(){return this}();try{r=r||new Function("return this")()}catch(e){"object"==typeof window&&(r=window)}e.exports=r},function(e,t,r){"use strict";Object.defineProperty(t,"__esModule",{value:!0});t.default={MARKED:"marked",UNMARKED:"unmarked",ACCEPTED:"accepted",REJECTED:"rejected",CANCELLED:"cancelled",COMPLETED:"completed",NONE:"none"}},function(e,t,r){},function(e,t,r){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var o=function(e){if(e&&e.__esModule)return e;var t={};if(null!=e)for(var r in e)Object.prototype.hasOwnProperty.call(e,r)&&(t[r]=e[r]);return t.default=e,t}(r(0)),n=r(2);function s(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function, not "+typeof t);e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,enumerable:!1,writable:!0,configurable:!0}}),t&&(Object.setPrototypeOf?Object.setPrototypeOf(e,t):function(e,t){for(var r=Object.getOwnPropertyNames(t),o=0;o<r.length;o++){var n=r[o],s=Object.getOwnPropertyDescriptor(t,n);s&&s.configurable&&void 0===e[n]&&Object.defineProperty(e,n,s)}}(e,t))}var a=o.PDFViewCtrl.Events,i=function(e){function t(r){return function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,t),function(e,t){if(!e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return!t||"object"!=typeof t&&"function"!=typeof t?e:t}(this,e.call(this,r))}return s(t,e),t.prototype.handle=function(){var e=this,t=function(t){return e.getPDFUI().promptPassword("","password-protect:buttons.inputOwnerPassword","password-protect:buttons.title","body",t?"error":void 0,t)};if(this.needPassword){t().then((function r(o){if(!o)return t("password-protect:error.admin-pwd-cannot-blank").then(r,(function(){}));e.currentDoc.checkPassword(o).then((function(n){if(3!==n)return t("password-protect:warning.incorrect-owner-pwd").then(r,(function(){}));e.data.ownerPassword=o,e.needPassword=!1,e.currentDoc.setPasswordType(n),e.handle()}))}),(function(){}))}else this.popup.updateData(this.data),this.popup.show()},t.prototype.setDefaultData=function(){var e={userPassword:n.EMPTY_STR,ownerPassword:n.EMPTY_STR,isEncryptMetadata:!0,cipherType:"aes256",print:2,accessibility:!0,copy:!0,change:4};this.data=e},t.prototype.mounted=function(){var e=this;this.popup=this.getComponentByName("password-protect-popup");var t,r=this.getPDFUI();r.i18n.on("languageChanged",t=function(){e.popup.localize()}),this.addDestroyHook((function(){r.i18n.off("languageChanged",t)}),r.addViewerEventListener(a.openFileSuccess,(function(t){e.currentDoc=t,e.needPassword=!1,t.hasSignature()&&e.component.disable(),e.setDefaultData(),e.popup.setSubmitCallback((function(t,r,o,n,s,a){e.currentDoc.setPasswordAndPermission(t,r,o,n,s,a).then((function(a){a?(-1!==t&&(e.data.userPassword=t),e.data.ownerPassword=r,e.data.cipherType=n,e.data.isEncryptMetadata=s,e.data.permission=o,e.data=e.convertPermission(e.data),e.getComponentByName("remove-protect-btn").controller.activeBtn(),e.currentDoc.setPasswordType(3),e.popup.hide()):e.popup.showError()})).catch((function(t){e.popup.showError(t)}))})),t.getPasswordType().then((function(r){return 2==r&&(e.needPassword=!0),2==r?t.getStdCipherOptions():{}})).then((function(t){void 0===t.permission?e.setDefaultData():(e.data=e.convertPermission(t),e.data.userPassword=n.EMPTY_STR,e.data.ownerPassword=n.EMPTY_STR)}))})))},t.prototype.convertPermission=function(e){var t=e.permission;return e.print=2048&t?2:4&t?1:0,e.accessibility=!!(512&t),e.copy=!!(16&t),e.change=8&t&&32&t&&256&t?4:32&t&&256&t?3:256&t?2:1024&t?1:0,e},t}(o.Controller);t.default=i},function(e,t,r){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var o=function(e){if(e&&e.__esModule)return e;var t={};if(null!=e)for(var r in e)Object.prototype.hasOwnProperty.call(e,r)&&(t[r]=e[r]);return t.default=e,t}(r(0));function n(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function, not "+typeof t);e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,enumerable:!1,writable:!0,configurable:!0}}),t&&(Object.setPrototypeOf?Object.setPrototypeOf(e,t):function(e,t){for(var r=Object.getOwnPropertyNames(t),o=0;o<r.length;o++){var n=r[o],s=Object.getOwnPropertyDescriptor(t,n);s&&s.configurable&&void 0===e[n]&&Object.defineProperty(e,n,s)}}(e,t))}var s=o.PDFViewCtrl.Events,a=function(e){function t(r){return function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,t),function(e,t){if(!e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return!t||"object"!=typeof t&&"function"!=typeof t?e:t}(this,e.call(this,r))}return n(t,e),t.prototype.handle=function(){var t=this;if(e.prototype.handle.call(this),this.active){var r=function(e){return t.getPDFUI().promptPassword("","password-protect:buttons.inputOwnerPassword","password-protect:buttons.title","body",e?"error":void 0,e)};if(this.needPassword){r().then((function e(o){if(!o)return r("password-protect:error.admin-pwd-cannot-blank").then(e,(function(){}));t.currentDoc.checkPassword(o).then((function(n){if(3!==n)return r("password-protect:warning.incorrect-owner-pwd").then(e,(function(){}));t.data.ownerPassword=o,t.needPassword=!1,t.currentDoc.setPasswordType(n),t.handle()}))}),(function(){}))}else this.getPDFUI().confirm("password-protect:dialog.remove.confirmation").then((function(){t.currentDoc.removeSecurity().then((function(e){e&&t.removeSecurityEvent()})).catch((function(e){console.log(e)}))}),(function(){}))}},t.prototype.mounted=function(){var e=this;this.getPDFUI().addViewerEventListener(s.openFileSuccess,(function(t){e.currentDoc=t,t.getPasswordType().then((function(t){2==t?(e.activeBtn(),e.needPassword=!0):3==t?(e.activeBtn(),e.needPassword=!1):e.inActiveBtn()}))}))},t.prototype.activeBtn=function(){this.component.element.classList.remove("fv__password-disabled-cursor"),this.active=!0},t.prototype.inActiveBtn=function(){this.component.element.classList.add("fv__password-disabled-cursor"),this.active=!1},t.prototype.removeSecurityEvent=function(){this.inActiveBtn();var e=this.getComponentByName("password-protect-btn");e.controller.setDefaultData(),e.controller.needPassword=!1},t}(o.Controller);t.default=a},function(e,t){e.exports=null},function(e,t,r){r(1);e.exports=function(e){"use strict";e=e||{};return'<div name="password-protect">\n    <xbutton name="password-protect-btn" icon-class="fv__icon-password-protect" @tooltip></xbutton>\n    <password-protect:password-protect-popup name="password-protect-popup" append-to="body" class="center" modal backdrop>\n        <layer-header title="password-protect:buttons.title" @draggable="{type:\'parent\'}"></layer-header>\n        <layer-view>\n        </layer-view>\n    </password-protect:password-protect-popup>\n</div>','<div name="password-protect">\n    <xbutton name="password-protect-btn" icon-class="fv__icon-password-protect" @tooltip></xbutton>\n    <password-protect:password-protect-popup name="password-protect-popup" append-to="body" class="center" modal backdrop>\n        <layer-header title="password-protect:buttons.title" @draggable="{type:\'parent\'}"></layer-header>\n        <layer-view>\n        </layer-view>\n    </password-protect:password-protect-popup>\n</div>'}},function(e,t,r){r(1);e.exports=function(e){"use strict";e=e||{};return'<div name="password-protect">\n    <xbutton name="remove-protect-btn" icon-class="fv__icon-remove-protect" @tooltip></xbutton>\n</div>','<div name="password-protect">\n    <xbutton name="remove-protect-btn" icon-class="fv__icon-remove-protect" @tooltip></xbutton>\n</div>'}}]).default}));