(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports, require('@angular/core')) :
    typeof define === 'function' && define.amd ? define('angular-doka', ['exports', '@angular/core'], factory) :
    (global = global || self, factory(global['angular-doka'] = {}, global.ng.core));
}(this, function (exports, core) { 'use strict';

    /*! *****************************************************************************
    Copyright (c) Microsoft Corporation. All rights reserved.
    Licensed under the Apache License, Version 2.0 (the "License"); you may not use
    this file except in compliance with the License. You may obtain a copy of the
    License at http://www.apache.org/licenses/LICENSE-2.0

    THIS CODE IS PROVIDED ON AN *AS IS* BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
    KIND, EITHER EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION ANY IMPLIED
    WARRANTIES OR CONDITIONS OF TITLE, FITNESS FOR A PARTICULAR PURPOSE,
    MERCHANTABLITY OR NON-INFRINGEMENT.

    See the Apache Version 2.0 License for specific language governing permissions
    and limitations under the License.
    ***************************************************************************** */
    /* global Reflect, Promise */

    var extendStatics = function(d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };

    function __extends(d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    }

    var __assign = function() {
        __assign = Object.assign || function __assign(t) {
            for (var s, i = 1, n = arguments.length; i < n; i++) {
                s = arguments[i];
                for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p)) t[p] = s[p];
            }
            return t;
        };
        return __assign.apply(this, arguments);
    };

    function __rest(s, e) {
        var t = {};
        for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p) && e.indexOf(p) < 0)
            t[p] = s[p];
        if (s != null && typeof Object.getOwnPropertySymbols === "function")
            for (var i = 0, p = Object.getOwnPropertySymbols(s); i < p.length; i++) if (e.indexOf(p[i]) < 0)
                t[p[i]] = s[p[i]];
        return t;
    }

    function __decorate(decorators, target, key, desc) {
        var c = arguments.length, r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc, d;
        if (typeof Reflect === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);
        else for (var i = decorators.length - 1; i >= 0; i--) if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
        return c > 3 && r && Object.defineProperty(target, key, r), r;
    }

    function __param(paramIndex, decorator) {
        return function (target, key) { decorator(target, key, paramIndex); }
    }

    function __metadata(metadataKey, metadataValue) {
        if (typeof Reflect === "object" && typeof Reflect.metadata === "function") return Reflect.metadata(metadataKey, metadataValue);
    }

    function __awaiter(thisArg, _arguments, P, generator) {
        return new (P || (P = Promise))(function (resolve, reject) {
            function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
            function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
            function step(result) { result.done ? resolve(result.value) : new P(function (resolve) { resolve(result.value); }).then(fulfilled, rejected); }
            step((generator = generator.apply(thisArg, _arguments || [])).next());
        });
    }

    function __generator(thisArg, body) {
        var _ = { label: 0, sent: function() { if (t[0] & 1) throw t[1]; return t[1]; }, trys: [], ops: [] }, f, y, t, g;
        return g = { next: verb(0), "throw": verb(1), "return": verb(2) }, typeof Symbol === "function" && (g[Symbol.iterator] = function() { return this; }), g;
        function verb(n) { return function (v) { return step([n, v]); }; }
        function step(op) {
            if (f) throw new TypeError("Generator is already executing.");
            while (_) try {
                if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done) return t;
                if (y = 0, t) op = [op[0] & 2, t.value];
                switch (op[0]) {
                    case 0: case 1: t = op; break;
                    case 4: _.label++; return { value: op[1], done: false };
                    case 5: _.label++; y = op[1]; op = [0]; continue;
                    case 7: op = _.ops.pop(); _.trys.pop(); continue;
                    default:
                        if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) { _ = 0; continue; }
                        if (op[0] === 3 && (!t || (op[1] > t[0] && op[1] < t[3]))) { _.label = op[1]; break; }
                        if (op[0] === 6 && _.label < t[1]) { _.label = t[1]; t = op; break; }
                        if (t && _.label < t[2]) { _.label = t[2]; _.ops.push(op); break; }
                        if (t[2]) _.ops.pop();
                        _.trys.pop(); continue;
                }
                op = body.call(thisArg, _);
            } catch (e) { op = [6, e]; y = 0; } finally { f = t = 0; }
            if (op[0] & 5) throw op[1]; return { value: op[0] ? op[1] : void 0, done: true };
        }
    }

    function __exportStar(m, exports) {
        for (var p in m) if (!exports.hasOwnProperty(p)) exports[p] = m[p];
    }

    function __values(o) {
        var m = typeof Symbol === "function" && o[Symbol.iterator], i = 0;
        if (m) return m.call(o);
        return {
            next: function () {
                if (o && i >= o.length) o = void 0;
                return { value: o && o[i++], done: !o };
            }
        };
    }

    function __read(o, n) {
        var m = typeof Symbol === "function" && o[Symbol.iterator];
        if (!m) return o;
        var i = m.call(o), r, ar = [], e;
        try {
            while ((n === void 0 || n-- > 0) && !(r = i.next()).done) ar.push(r.value);
        }
        catch (error) { e = { error: error }; }
        finally {
            try {
                if (r && !r.done && (m = i["return"])) m.call(i);
            }
            finally { if (e) throw e.error; }
        }
        return ar;
    }

    function __spread() {
        for (var ar = [], i = 0; i < arguments.length; i++)
            ar = ar.concat(__read(arguments[i]));
        return ar;
    }

    function __await(v) {
        return this instanceof __await ? (this.v = v, this) : new __await(v);
    }

    function __asyncGenerator(thisArg, _arguments, generator) {
        if (!Symbol.asyncIterator) throw new TypeError("Symbol.asyncIterator is not defined.");
        var g = generator.apply(thisArg, _arguments || []), i, q = [];
        return i = {}, verb("next"), verb("throw"), verb("return"), i[Symbol.asyncIterator] = function () { return this; }, i;
        function verb(n) { if (g[n]) i[n] = function (v) { return new Promise(function (a, b) { q.push([n, v, a, b]) > 1 || resume(n, v); }); }; }
        function resume(n, v) { try { step(g[n](v)); } catch (e) { settle(q[0][3], e); } }
        function step(r) { r.value instanceof __await ? Promise.resolve(r.value.v).then(fulfill, reject) : settle(q[0][2], r); }
        function fulfill(value) { resume("next", value); }
        function reject(value) { resume("throw", value); }
        function settle(f, v) { if (f(v), q.shift(), q.length) resume(q[0][0], q[0][1]); }
    }

    function __asyncDelegator(o) {
        var i, p;
        return i = {}, verb("next"), verb("throw", function (e) { throw e; }), verb("return"), i[Symbol.iterator] = function () { return this; }, i;
        function verb(n, f) { i[n] = o[n] ? function (v) { return (p = !p) ? { value: __await(o[n](v)), done: n === "return" } : f ? f(v) : v; } : f; }
    }

    function __asyncValues(o) {
        if (!Symbol.asyncIterator) throw new TypeError("Symbol.asyncIterator is not defined.");
        var m = o[Symbol.asyncIterator], i;
        return m ? m.call(o) : (o = typeof __values === "function" ? __values(o) : o[Symbol.iterator](), i = {}, verb("next"), verb("throw"), verb("return"), i[Symbol.asyncIterator] = function () { return this; }, i);
        function verb(n) { i[n] = o[n] && function (v) { return new Promise(function (resolve, reject) { v = o[n](v), settle(resolve, reject, v.done, v.value); }); }; }
        function settle(resolve, reject, d, v) { Promise.resolve(v).then(function(v) { resolve({ value: v, done: d }); }, reject); }
    }

    function __makeTemplateObject(cooked, raw) {
        if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
        return cooked;
    };

    function __importStar(mod) {
        if (mod && mod.__esModule) return mod;
        var result = {};
        if (mod != null) for (var k in mod) if (Object.hasOwnProperty.call(mod, k)) result[k] = mod[k];
        result.default = mod;
        return result;
    }

    function __importDefault(mod) {
        return (mod && mod.__esModule) ? mod : { default: mod };
    }

    /**
     * @fileoverview added by tsickle
     * @suppress {checkTypes,constantProperty,extraRequire,missingOverride,missingReturn,unusedPrivateMembers,uselessCode} checked by tsc
     */
    /** @type {?} */
    var Doka = window['Doka'];
    /** @type {?} */
    var supported = Doka.supported;
    /** @type {?} */
    var create = Doka.create;

    /**
     * @fileoverview added by tsickle
     * @suppress {checkTypes,constantProperty,extraRequire,missingOverride,missingReturn,unusedPrivateMembers,uselessCode} checked by tsc
     */
    // We test if Doka is supported on the current client
    /** @type {?} */
    var isSupported = supported();
    // Methods not made available on the component
    /** @type {?} */
    var filteredComponentMethods = [
        'setOptions',
        'on',
        'off',
        'onOnce',
        'appendTo',
        'insertAfter',
        'insertBefore',
        'isAttachedTo',
        'replaceElement',
        'restoreElement',
        'destroy'
    ];
    /** @type {?} */
    var outputs = [
        'oninit',
        'onconfirm',
        'oncancel',
        'onclose',
        'onload',
        'onloaderror',
        'ondestroy',
        'onupdate'
    ];
    var AngularDokaComponent = /** @class */ (function () {
        function AngularDokaComponent(root, zone) {
            var _this = this;
            this.handleEvent = (/**
             * @param {?} e
             * @return {?}
             */
            function (e) {
                /** @type {?} */
                var output = _this["on" + e.type.split(':')[1]];
                /** @type {?} */
                var event = __assign({}, e.detail);
                delete event.doka;
                output.emit(event);
            });
            this.src = null;
            this.options = {};
            this.oninit = new core.EventEmitter();
            this.onconfirm = new core.EventEmitter();
            this.oncancel = new core.EventEmitter();
            this.onclose = new core.EventEmitter();
            this.onload = new core.EventEmitter();
            this.onloaderror = new core.EventEmitter();
            this.ondestroy = new core.EventEmitter();
            this.onupdate = new core.EventEmitter();
            this.root = root;
            this.zone = zone;
        }
        /**
         * @return {?}
         */
        AngularDokaComponent.prototype.ngOnInit = /**
         * @return {?}
         */
        function () { };
        /**
         * @return {?}
         */
        AngularDokaComponent.prototype.ngAfterViewInit = /**
         * @return {?}
         */
        function () {
            var _this = this;
            // no sufficient features supported in this browser
            if (!isSupported)
                return;
            // will block angular from listening to events inside doka
            this.zone.runOutsideAngular((/**
             * @return {?}
             */
            function () {
                // get host child <div>
                /** @type {?} */
                var inner = _this.root.nativeElement.firstChild;
                // if image or canvas supplied
                /** @type {?} */
                var src = inner.querySelector('img') || inner.querySelector('canvas') || _this.src;
                // create instance
                _this.doka = create(inner, __assign({ 
                    // source from slot
                    src: src }, _this.options));
            }));
            // route events
            /** @type {?} */
            var dokaRoot = this.doka.element;
            outputs.forEach((/**
             * @param {?} event
             * @return {?}
             */
            function (event) { return dokaRoot.addEventListener("Doka:" + event.substr(2), _this.handleEvent); }));
            // Copy instance method references to component instance
            Object.keys(this.doka)
                // remove unwanted methods
                .filter((/**
             * @param {?} key
             * @return {?}
             */
            function (key) { return filteredComponentMethods.indexOf(key) === -1; }))
                // set method references from the component instance to the doka instance
                .forEach((/**
             * @param {?} key
             * @return {?}
             */
            function (key) { return _this[key] = _this.doka[key]; }));
        };
        /**
         * @param {?} changes
         * @return {?}
         */
        AngularDokaComponent.prototype.ngOnChanges = /**
         * @param {?} changes
         * @return {?}
         */
        function (changes) {
            // no need to handle first change
            if (changes.firstChange)
                return;
            // no doka instance available
            if (!this.doka)
                return;
            // use new options object as base ( or if not available, use current options )
            /** @type {?} */
            var options = changes.options ? changes.options.currentValue : this.options;
            // update source
            if (changes.src)
                options.src = changes.src.currentValue;
            // set new options
            this.doka.setOptions(options);
        };
        /**
         * @return {?}
         */
        AngularDokaComponent.prototype.ngOnDestroy = /**
         * @return {?}
         */
        function () {
            var _this = this;
            // no doka instance available
            if (!this.doka)
                return;
            // detach events
            /** @type {?} */
            var dokaRoot = this.doka.element;
            outputs.forEach((/**
             * @param {?} event
             * @return {?}
             */
            function (event) { return dokaRoot.removeEventListener("Doka:" + event.substr(2), _this.handleEvent); }));
            // we done!
            this.doka.destroy();
            this.doka = null;
        };
        AngularDokaComponent.decorators = [
            { type: core.Component, args: [{
                        selector: 'lib-doka',
                        template: "\n    <div>\n      <ng-content></ng-content>\n    </div>\n  ",
                        styles: ["\n    :host {\n      display: block;\n    }\n  "]
                    }] }
        ];
        /** @nocollapse */
        AngularDokaComponent.ctorParameters = function () { return [
            { type: core.ElementRef },
            { type: core.NgZone }
        ]; };
        AngularDokaComponent.propDecorators = {
            src: [{ type: core.Input }],
            options: [{ type: core.Input }],
            oninit: [{ type: core.Output }],
            onconfirm: [{ type: core.Output }],
            oncancel: [{ type: core.Output }],
            onclose: [{ type: core.Output }],
            onload: [{ type: core.Output }],
            onloaderror: [{ type: core.Output }],
            ondestroy: [{ type: core.Output }],
            onupdate: [{ type: core.Output }]
        };
        return AngularDokaComponent;
    }());
    if (false) {
        /**
         * @type {?}
         * @private
         */
        AngularDokaComponent.prototype.root;
        /**
         * @type {?}
         * @private
         */
        AngularDokaComponent.prototype.zone;
        /**
         * @type {?}
         * @private
         */
        AngularDokaComponent.prototype.doka;
        /**
         * @type {?}
         * @private
         */
        AngularDokaComponent.prototype.handleEvent;
        /** @type {?} */
        AngularDokaComponent.prototype.src;
        /** @type {?} */
        AngularDokaComponent.prototype.options;
        /** @type {?} */
        AngularDokaComponent.prototype.oninit;
        /** @type {?} */
        AngularDokaComponent.prototype.onconfirm;
        /** @type {?} */
        AngularDokaComponent.prototype.oncancel;
        /** @type {?} */
        AngularDokaComponent.prototype.onclose;
        /** @type {?} */
        AngularDokaComponent.prototype.onload;
        /** @type {?} */
        AngularDokaComponent.prototype.onloaderror;
        /** @type {?} */
        AngularDokaComponent.prototype.ondestroy;
        /** @type {?} */
        AngularDokaComponent.prototype.onupdate;
    }

    /**
     * @fileoverview added by tsickle
     * @suppress {checkTypes,constantProperty,extraRequire,missingOverride,missingReturn,unusedPrivateMembers,uselessCode} checked by tsc
     */
    // We test if Doka is supported on the current client
    /** @type {?} */
    var isSupported$1 = supported();
    // Methods not made available on the component
    /** @type {?} */
    var filteredComponentMethods$1 = [
        'setOptions',
        'on',
        'off',
        'onOnce',
        'appendTo',
        'insertAfter',
        'insertBefore',
        'isAttachedTo',
        'replaceElement',
        'restoreElement',
        'destroy'
    ];
    /** @type {?} */
    var outputs$1 = [
        'oninit',
        'onconfirm',
        'oncancel',
        'onclose',
        'onload',
        'onloaderror',
        'ondestroy',
        'onupdate'
    ];
    var AngularDokaModalComponent = /** @class */ (function () {
        function AngularDokaModalComponent(root, zone) {
            var _this = this;
            this.handleEvent = (/**
             * @param {?} e
             * @return {?}
             */
            function (e) {
                /** @type {?} */
                var output = _this["on" + e.type.split(':')[1]];
                /** @type {?} */
                var event = __assign({}, e.detail);
                delete event.doka;
                output.emit(event);
            });
            this.src = null;
            this.options = {};
            this.oninit = new core.EventEmitter();
            this.onconfirm = new core.EventEmitter();
            this.oncancel = new core.EventEmitter();
            this.onclose = new core.EventEmitter();
            this.onload = new core.EventEmitter();
            this.onloaderror = new core.EventEmitter();
            this.ondestroy = new core.EventEmitter();
            this.onupdate = new core.EventEmitter();
            this.root = root;
            this.zone = zone;
        }
        /**
         * @return {?}
         */
        AngularDokaModalComponent.prototype.ngOnInit = /**
         * @return {?}
         */
        function () { };
        /**
         * @return {?}
         */
        AngularDokaModalComponent.prototype.ngAfterViewInit = /**
         * @return {?}
         */
        function () {
            var _this = this;
            // no sufficient features supported in this browser
            if (!isSupported$1)
                return;
            // will block angular from listening to events inside doka
            this.zone.runOutsideAngular((/**
             * @return {?}
             */
            function () {
                // get host child <div>
                /** @type {?} */
                var inner = _this.root.nativeElement;
                // if image or canvas supplied
                /** @type {?} */
                var src = inner.querySelector('img') || inner.querySelector('canvas') || _this.src;
                // create instance
                _this.doka = create(__assign({ 
                    // source from slot
                    src: src }, _this.options));
            }));
            // route events
            /** @type {?} */
            var dokaRoot = this.doka.element;
            outputs$1.forEach((/**
             * @param {?} event
             * @return {?}
             */
            function (event) { return dokaRoot.addEventListener("Doka:" + event.substr(2), _this.handleEvent); }));
            // Copy instance method references to component instance
            Object.keys(this.doka)
                // remove unwanted methods
                .filter((/**
             * @param {?} key
             * @return {?}
             */
            function (key) { return filteredComponentMethods$1.indexOf(key) === -1; }))
                // set method references from the component instance to the doka instance
                .forEach((/**
             * @param {?} key
             * @return {?}
             */
            function (key) { return _this[key] = _this.doka[key]; }));
        };
        /**
         * @param {?} changes
         * @return {?}
         */
        AngularDokaModalComponent.prototype.ngOnChanges = /**
         * @param {?} changes
         * @return {?}
         */
        function (changes) {
            // no need to handle first change
            if (changes.firstChange)
                return;
            // no doka instance available
            if (!this.doka)
                return;
            // use new options object as base ( or if not available, use current options )
            /** @type {?} */
            var options = changes.options ? changes.options.currentValue : this.options;
            // update source
            if (changes.src)
                options.src = changes.src.currentValue;
            // set new options
            this.doka.setOptions(options);
        };
        /**
         * @return {?}
         */
        AngularDokaModalComponent.prototype.ngOnDestroy = /**
         * @return {?}
         */
        function () {
            var _this = this;
            // no doka instance available
            if (!this.doka)
                return;
            // detach events
            /** @type {?} */
            var dokaRoot = this.doka.element;
            outputs$1.forEach((/**
             * @param {?} event
             * @return {?}
             */
            function (event) { return dokaRoot.removeEventListener("Doka:" + event.substr(2), _this.handleEvent); }));
            // we done!
            this.doka.destroy();
            this.doka = null;
        };
        AngularDokaModalComponent.decorators = [
            { type: core.Component, args: [{
                        selector: 'lib-doka-modal',
                        template: "\n    <ng-content></ng-content>\n  ",
                        styles: ["\n    :host {\n      display: block;\n    }\n  "]
                    }] }
        ];
        /** @nocollapse */
        AngularDokaModalComponent.ctorParameters = function () { return [
            { type: core.ElementRef },
            { type: core.NgZone }
        ]; };
        AngularDokaModalComponent.propDecorators = {
            src: [{ type: core.Input }],
            options: [{ type: core.Input }],
            oninit: [{ type: core.Output }],
            onconfirm: [{ type: core.Output }],
            oncancel: [{ type: core.Output }],
            onclose: [{ type: core.Output }],
            onload: [{ type: core.Output }],
            onloaderror: [{ type: core.Output }],
            ondestroy: [{ type: core.Output }],
            onupdate: [{ type: core.Output }]
        };
        return AngularDokaModalComponent;
    }());
    if (false) {
        /**
         * @type {?}
         * @private
         */
        AngularDokaModalComponent.prototype.root;
        /**
         * @type {?}
         * @private
         */
        AngularDokaModalComponent.prototype.zone;
        /**
         * @type {?}
         * @private
         */
        AngularDokaModalComponent.prototype.doka;
        /**
         * @type {?}
         * @private
         */
        AngularDokaModalComponent.prototype.handleEvent;
        /** @type {?} */
        AngularDokaModalComponent.prototype.src;
        /** @type {?} */
        AngularDokaModalComponent.prototype.options;
        /** @type {?} */
        AngularDokaModalComponent.prototype.oninit;
        /** @type {?} */
        AngularDokaModalComponent.prototype.onconfirm;
        /** @type {?} */
        AngularDokaModalComponent.prototype.oncancel;
        /** @type {?} */
        AngularDokaModalComponent.prototype.onclose;
        /** @type {?} */
        AngularDokaModalComponent.prototype.onload;
        /** @type {?} */
        AngularDokaModalComponent.prototype.onloaderror;
        /** @type {?} */
        AngularDokaModalComponent.prototype.ondestroy;
        /** @type {?} */
        AngularDokaModalComponent.prototype.onupdate;
    }

    /**
     * @fileoverview added by tsickle
     * @suppress {checkTypes,constantProperty,extraRequire,missingOverride,missingReturn,unusedPrivateMembers,uselessCode} checked by tsc
     */
    // We test if Doka is supported on the current client
    /** @type {?} */
    var isSupported$2 = supported();
    // Methods not made available on the component
    /** @type {?} */
    var filteredComponentMethods$2 = [
        'setOptions',
        'on',
        'off',
        'onOnce',
        'appendTo',
        'insertAfter',
        'insertBefore',
        'isAttachedTo',
        'replaceElement',
        'restoreElement',
        'destroy'
    ];
    /** @type {?} */
    var outputs$2 = [
        'oninit',
        'onconfirm',
        'oncancel',
        'onclose',
        'onload',
        'onloaderror',
        'ondestroy',
        'onupdate'
    ];
    var AngularDokaOverlayComponent = /** @class */ (function () {
        function AngularDokaOverlayComponent(root, zone) {
            var _this = this;
            this.handleEvent = (/**
             * @param {?} e
             * @return {?}
             */
            function (e) {
                /** @type {?} */
                var output = _this["on" + e.type.split(':')[1]];
                /** @type {?} */
                var event = __assign({}, e.detail);
                delete event.doka;
                output.emit(event);
            });
            this.src = null;
            this.options = {};
            this.enabled = false;
            this.oninit = new core.EventEmitter();
            this.onconfirm = new core.EventEmitter();
            this.oncancel = new core.EventEmitter();
            this.onclose = new core.EventEmitter();
            this.onload = new core.EventEmitter();
            this.onloaderror = new core.EventEmitter();
            this.ondestroy = new core.EventEmitter();
            this.onupdate = new core.EventEmitter();
            this.root = root;
            this.zone = zone;
        }
        /**
         * @return {?}
         */
        AngularDokaOverlayComponent.prototype.ngOnInit = /**
         * @return {?}
         */
        function () { };
        /**
         * @return {?}
         */
        AngularDokaOverlayComponent.prototype.ngAfterViewInit = /**
         * @return {?}
         */
        function () {
            // no sufficient features supported in this browser
            if (!isSupported$2)
                return;
            this.update();
        };
        /**
         * @param {?} changes
         * @return {?}
         */
        AngularDokaOverlayComponent.prototype.ngOnChanges = /**
         * @param {?} changes
         * @return {?}
         */
        function (changes) {
            // no need to handle first change
            if (changes.firstChange)
                return;
            // update!
            this.update(changes);
        };
        /**
         * @return {?}
         */
        AngularDokaOverlayComponent.prototype.ngOnDestroy = /**
         * @return {?}
         */
        function () {
            this.hide();
        };
        /**
         * @param {?=} changes
         * @return {?}
         */
        AngularDokaOverlayComponent.prototype.show = /**
         * @param {?=} changes
         * @return {?}
         */
        function (changes) {
            var _this = this;
            if (this.doka) {
                // use new options object as base ( or if not available, use current options )
                /** @type {?} */
                var options = changes.options ? changes.options.currentValue : this.options;
                // update source
                if (changes.src)
                    options.src = changes.src.currentValue;
                // set new options
                this.doka.setOptions(options);
                return;
            }
            // will block angular from listening to events inside doka
            this.zone.runOutsideAngular((/**
             * @return {?}
             */
            function () {
                // get host child <div>
                /** @type {?} */
                var inner = _this.root.nativeElement.querySelector('div').firstChild;
                // create instance
                _this.doka = create(inner, __assign({ src: _this.src }, _this.options, { styleLayoutMode: 'preview', outputData: true }));
            }));
            // route events
            /** @type {?} */
            var dokaRoot = this.doka.element;
            outputs$2.forEach((/**
             * @param {?} event
             * @return {?}
             */
            function (event) { return dokaRoot.addEventListener("Doka:" + event.substr(2), _this.handleEvent); }));
            // Copy instance method references to component instance
            Object.keys(this.doka)
                // remove unwanted methods
                .filter((/**
             * @param {?} key
             * @return {?}
             */
            function (key) { return filteredComponentMethods$2.indexOf(key) === -1; }))
                // set method references from the component instance to the doka instance
                .forEach((/**
             * @param {?} key
             * @return {?}
             */
            function (key) { return _this[key] = _this.doka[key]; }));
        };
        /**
         * @return {?}
         */
        AngularDokaOverlayComponent.prototype.hide = /**
         * @return {?}
         */
        function () {
            var _this = this;
            // no doka instance available
            if (!this.doka)
                return;
            // detach events
            /** @type {?} */
            var dokaRoot = this.doka.element;
            outputs$2.forEach((/**
             * @param {?} event
             * @return {?}
             */
            function (event) { return dokaRoot.removeEventListener("Doka:" + event.substr(2), _this.handleEvent); }));
            // we done!
            this.doka.destroy();
            this.doka = null;
        };
        /**
         * @param {?=} changes
         * @return {?}
         */
        AngularDokaOverlayComponent.prototype.update = /**
         * @param {?=} changes
         * @return {?}
         */
        function (changes) {
            if (this.enabled) {
                this.show(changes);
            }
            else {
                this.hide();
            }
        };
        AngularDokaOverlayComponent.decorators = [
            { type: core.Component, args: [{
                        selector: 'lib-doka-overlay',
                        template: "\n    <ng-content></ng-content>\n    <div>\n        <div></div>\n    </div>\n  ",
                        styles: ["\n    :host {\n      display: block;\n      position: relative;\n      overflow: hidden;\n    }\n    \n    :host /deep/ img {\n      display: block;\n      width: 100%;\n      height: auto;\n    }\n    \n    :host > div {\n      position: absolute;\n      left: 0;\n      top: 0;\n      width: 100%;\n      height: 100%;\n    }\n    "]
                    }] }
        ];
        /** @nocollapse */
        AngularDokaOverlayComponent.ctorParameters = function () { return [
            { type: core.ElementRef },
            { type: core.NgZone }
        ]; };
        AngularDokaOverlayComponent.propDecorators = {
            src: [{ type: core.Input }],
            options: [{ type: core.Input }],
            enabled: [{ type: core.Input }],
            oninit: [{ type: core.Output }],
            onconfirm: [{ type: core.Output }],
            oncancel: [{ type: core.Output }],
            onclose: [{ type: core.Output }],
            onload: [{ type: core.Output }],
            onloaderror: [{ type: core.Output }],
            ondestroy: [{ type: core.Output }],
            onupdate: [{ type: core.Output }]
        };
        return AngularDokaOverlayComponent;
    }());
    if (false) {
        /**
         * @type {?}
         * @private
         */
        AngularDokaOverlayComponent.prototype.root;
        /**
         * @type {?}
         * @private
         */
        AngularDokaOverlayComponent.prototype.zone;
        /**
         * @type {?}
         * @private
         */
        AngularDokaOverlayComponent.prototype.doka;
        /**
         * @type {?}
         * @private
         */
        AngularDokaOverlayComponent.prototype.handleEvent;
        /** @type {?} */
        AngularDokaOverlayComponent.prototype.src;
        /** @type {?} */
        AngularDokaOverlayComponent.prototype.options;
        /** @type {?} */
        AngularDokaOverlayComponent.prototype.enabled;
        /** @type {?} */
        AngularDokaOverlayComponent.prototype.oninit;
        /** @type {?} */
        AngularDokaOverlayComponent.prototype.onconfirm;
        /** @type {?} */
        AngularDokaOverlayComponent.prototype.oncancel;
        /** @type {?} */
        AngularDokaOverlayComponent.prototype.onclose;
        /** @type {?} */
        AngularDokaOverlayComponent.prototype.onload;
        /** @type {?} */
        AngularDokaOverlayComponent.prototype.onloaderror;
        /** @type {?} */
        AngularDokaOverlayComponent.prototype.ondestroy;
        /** @type {?} */
        AngularDokaOverlayComponent.prototype.onupdate;
    }

    /**
     * @fileoverview added by tsickle
     * @suppress {checkTypes,constantProperty,extraRequire,missingOverride,missingReturn,unusedPrivateMembers,uselessCode} checked by tsc
     */
    var AngularDokaModule = /** @class */ (function () {
        function AngularDokaModule() {
        }
        AngularDokaModule.decorators = [
            { type: core.NgModule, args: [{
                        declarations: [
                            AngularDokaComponent,
                            AngularDokaModalComponent,
                            AngularDokaOverlayComponent
                        ],
                        imports: [],
                        exports: [
                            AngularDokaComponent,
                            AngularDokaModalComponent,
                            AngularDokaOverlayComponent
                        ]
                    },] }
        ];
        return AngularDokaModule;
    }());

    exports.AngularDokaComponent = AngularDokaComponent;
    exports.AngularDokaModule = AngularDokaModule;
    exports.ɵa = AngularDokaModalComponent;
    exports.ɵb = AngularDokaOverlayComponent;

    Object.defineProperty(exports, '__esModule', { value: true });

}));
//# sourceMappingURL=angular-doka.umd.js.map
