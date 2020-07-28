import { __assign } from 'tslib';
import { EventEmitter, Component, ElementRef, NgZone, Input, Output, NgModule } from '@angular/core';

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
        this.oninit = new EventEmitter();
        this.onconfirm = new EventEmitter();
        this.oncancel = new EventEmitter();
        this.onclose = new EventEmitter();
        this.onload = new EventEmitter();
        this.onloaderror = new EventEmitter();
        this.ondestroy = new EventEmitter();
        this.onupdate = new EventEmitter();
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
        { type: Component, args: [{
                    selector: 'lib-doka',
                    template: "\n    <div>\n      <ng-content></ng-content>\n    </div>\n  ",
                    styles: ["\n    :host {\n      display: block;\n    }\n  "]
                }] }
    ];
    /** @nocollapse */
    AngularDokaComponent.ctorParameters = function () { return [
        { type: ElementRef },
        { type: NgZone }
    ]; };
    AngularDokaComponent.propDecorators = {
        src: [{ type: Input }],
        options: [{ type: Input }],
        oninit: [{ type: Output }],
        onconfirm: [{ type: Output }],
        oncancel: [{ type: Output }],
        onclose: [{ type: Output }],
        onload: [{ type: Output }],
        onloaderror: [{ type: Output }],
        ondestroy: [{ type: Output }],
        onupdate: [{ type: Output }]
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
        this.oninit = new EventEmitter();
        this.onconfirm = new EventEmitter();
        this.oncancel = new EventEmitter();
        this.onclose = new EventEmitter();
        this.onload = new EventEmitter();
        this.onloaderror = new EventEmitter();
        this.ondestroy = new EventEmitter();
        this.onupdate = new EventEmitter();
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
        { type: Component, args: [{
                    selector: 'lib-doka-modal',
                    template: "\n    <ng-content></ng-content>\n  ",
                    styles: ["\n    :host {\n      display: block;\n    }\n  "]
                }] }
    ];
    /** @nocollapse */
    AngularDokaModalComponent.ctorParameters = function () { return [
        { type: ElementRef },
        { type: NgZone }
    ]; };
    AngularDokaModalComponent.propDecorators = {
        src: [{ type: Input }],
        options: [{ type: Input }],
        oninit: [{ type: Output }],
        onconfirm: [{ type: Output }],
        oncancel: [{ type: Output }],
        onclose: [{ type: Output }],
        onload: [{ type: Output }],
        onloaderror: [{ type: Output }],
        ondestroy: [{ type: Output }],
        onupdate: [{ type: Output }]
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
        this.oninit = new EventEmitter();
        this.onconfirm = new EventEmitter();
        this.oncancel = new EventEmitter();
        this.onclose = new EventEmitter();
        this.onload = new EventEmitter();
        this.onloaderror = new EventEmitter();
        this.ondestroy = new EventEmitter();
        this.onupdate = new EventEmitter();
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
        { type: Component, args: [{
                    selector: 'lib-doka-overlay',
                    template: "\n    <ng-content></ng-content>\n    <div>\n        <div></div>\n    </div>\n  ",
                    styles: ["\n    :host {\n      display: block;\n      position: relative;\n      overflow: hidden;\n    }\n    \n    :host /deep/ img {\n      display: block;\n      width: 100%;\n      height: auto;\n    }\n    \n    :host > div {\n      position: absolute;\n      left: 0;\n      top: 0;\n      width: 100%;\n      height: 100%;\n    }\n    "]
                }] }
    ];
    /** @nocollapse */
    AngularDokaOverlayComponent.ctorParameters = function () { return [
        { type: ElementRef },
        { type: NgZone }
    ]; };
    AngularDokaOverlayComponent.propDecorators = {
        src: [{ type: Input }],
        options: [{ type: Input }],
        enabled: [{ type: Input }],
        oninit: [{ type: Output }],
        onconfirm: [{ type: Output }],
        oncancel: [{ type: Output }],
        onclose: [{ type: Output }],
        onload: [{ type: Output }],
        onloaderror: [{ type: Output }],
        ondestroy: [{ type: Output }],
        onupdate: [{ type: Output }]
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
        { type: NgModule, args: [{
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

/**
 * @fileoverview added by tsickle
 * @suppress {checkTypes,constantProperty,extraRequire,missingOverride,missingReturn,unusedPrivateMembers,uselessCode} checked by tsc
 */

/**
 * @fileoverview added by tsickle
 * @suppress {checkTypes,constantProperty,extraRequire,missingOverride,missingReturn,unusedPrivateMembers,uselessCode} checked by tsc
 */

export { AngularDokaComponent, AngularDokaModule, AngularDokaModalComponent as ɵa, AngularDokaOverlayComponent as ɵb };
//# sourceMappingURL=angular-doka.js.map
