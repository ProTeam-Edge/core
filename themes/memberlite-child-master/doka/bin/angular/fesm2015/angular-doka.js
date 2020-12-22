import { EventEmitter, Component, ElementRef, NgZone, Input, Output, NgModule } from '@angular/core';

/**
 * @fileoverview added by tsickle
 * @suppress {checkTypes,constantProperty,extraRequire,missingOverride,missingReturn,unusedPrivateMembers,uselessCode} checked by tsc
 */
/** @type {?} */
const Doka = window['Doka'];
/** @type {?} */
const supported = Doka.supported;
/** @type {?} */
const create = Doka.create;

/**
 * @fileoverview added by tsickle
 * @suppress {checkTypes,constantProperty,extraRequire,missingOverride,missingReturn,unusedPrivateMembers,uselessCode} checked by tsc
 */
// We test if Doka is supported on the current client
/** @type {?} */
const isSupported = supported();
// Methods not made available on the component
/** @type {?} */
const filteredComponentMethods = [
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
const outputs = [
    'oninit',
    'onconfirm',
    'oncancel',
    'onclose',
    'onload',
    'onloaderror',
    'ondestroy',
    'onupdate'
];
class AngularDokaComponent {
    /**
     * @param {?} root
     * @param {?} zone
     */
    constructor(root, zone) {
        this.handleEvent = (/**
         * @param {?} e
         * @return {?}
         */
        (e) => {
            /** @type {?} */
            const output = this[`on${e.type.split(':')[1]}`];
            /** @type {?} */
            const event = Object.assign({}, e.detail);
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
    ngOnInit() { }
    /**
     * @return {?}
     */
    ngAfterViewInit() {
        // no sufficient features supported in this browser
        if (!isSupported)
            return;
        // will block angular from listening to events inside doka
        this.zone.runOutsideAngular((/**
         * @return {?}
         */
        () => {
            // get host child <div>
            /** @type {?} */
            const inner = this.root.nativeElement.firstChild;
            // if image or canvas supplied
            /** @type {?} */
            const src = inner.querySelector('img') || inner.querySelector('canvas') || this.src;
            // create instance
            this.doka = create(inner, Object.assign({ 
                // source from slot
                src }, this.options));
        }));
        // route events
        /** @type {?} */
        const dokaRoot = this.doka.element;
        outputs.forEach((/**
         * @param {?} event
         * @return {?}
         */
        event => dokaRoot.addEventListener(`Doka:${event.substr(2)}`, this.handleEvent)));
        // Copy instance method references to component instance
        Object.keys(this.doka)
            // remove unwanted methods
            .filter((/**
         * @param {?} key
         * @return {?}
         */
        key => filteredComponentMethods.indexOf(key) === -1))
            // set method references from the component instance to the doka instance
            .forEach((/**
         * @param {?} key
         * @return {?}
         */
        key => this[key] = this.doka[key]));
    }
    /**
     * @param {?} changes
     * @return {?}
     */
    ngOnChanges(changes) {
        // no need to handle first change
        if (changes.firstChange)
            return;
        // no doka instance available
        if (!this.doka)
            return;
        // use new options object as base ( or if not available, use current options )
        /** @type {?} */
        const options = changes.options ? changes.options.currentValue : this.options;
        // update source
        if (changes.src)
            options.src = changes.src.currentValue;
        // set new options
        this.doka.setOptions(options);
    }
    /**
     * @return {?}
     */
    ngOnDestroy() {
        // no doka instance available
        if (!this.doka)
            return;
        // detach events
        /** @type {?} */
        const dokaRoot = this.doka.element;
        outputs.forEach((/**
         * @param {?} event
         * @return {?}
         */
        event => dokaRoot.removeEventListener(`Doka:${event.substr(2)}`, this.handleEvent)));
        // we done!
        this.doka.destroy();
        this.doka = null;
    }
}
AngularDokaComponent.decorators = [
    { type: Component, args: [{
                selector: 'lib-doka',
                template: `
    <div>
      <ng-content></ng-content>
    </div>
  `,
                styles: [`
    :host {
      display: block;
    }
  `]
            }] }
];
/** @nocollapse */
AngularDokaComponent.ctorParameters = () => [
    { type: ElementRef },
    { type: NgZone }
];
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
const isSupported$1 = supported();
// Methods not made available on the component
/** @type {?} */
const filteredComponentMethods$1 = [
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
const outputs$1 = [
    'oninit',
    'onconfirm',
    'oncancel',
    'onclose',
    'onload',
    'onloaderror',
    'ondestroy',
    'onupdate'
];
class AngularDokaModalComponent {
    /**
     * @param {?} root
     * @param {?} zone
     */
    constructor(root, zone) {
        this.handleEvent = (/**
         * @param {?} e
         * @return {?}
         */
        (e) => {
            /** @type {?} */
            const output = this[`on${e.type.split(':')[1]}`];
            /** @type {?} */
            const event = Object.assign({}, e.detail);
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
    ngOnInit() { }
    /**
     * @return {?}
     */
    ngAfterViewInit() {
        // no sufficient features supported in this browser
        if (!isSupported$1)
            return;
        // will block angular from listening to events inside doka
        this.zone.runOutsideAngular((/**
         * @return {?}
         */
        () => {
            // get host child <div>
            /** @type {?} */
            const inner = this.root.nativeElement;
            // if image or canvas supplied
            /** @type {?} */
            const src = inner.querySelector('img') || inner.querySelector('canvas') || this.src;
            // create instance
            this.doka = create(Object.assign({ 
                // source from slot
                src }, this.options));
        }));
        // route events
        /** @type {?} */
        const dokaRoot = this.doka.element;
        outputs$1.forEach((/**
         * @param {?} event
         * @return {?}
         */
        event => dokaRoot.addEventListener(`Doka:${event.substr(2)}`, this.handleEvent)));
        // Copy instance method references to component instance
        Object.keys(this.doka)
            // remove unwanted methods
            .filter((/**
         * @param {?} key
         * @return {?}
         */
        key => filteredComponentMethods$1.indexOf(key) === -1))
            // set method references from the component instance to the doka instance
            .forEach((/**
         * @param {?} key
         * @return {?}
         */
        key => this[key] = this.doka[key]));
    }
    /**
     * @param {?} changes
     * @return {?}
     */
    ngOnChanges(changes) {
        // no need to handle first change
        if (changes.firstChange)
            return;
        // no doka instance available
        if (!this.doka)
            return;
        // use new options object as base ( or if not available, use current options )
        /** @type {?} */
        const options = changes.options ? changes.options.currentValue : this.options;
        // update source
        if (changes.src)
            options.src = changes.src.currentValue;
        // set new options
        this.doka.setOptions(options);
    }
    /**
     * @return {?}
     */
    ngOnDestroy() {
        // no doka instance available
        if (!this.doka)
            return;
        // detach events
        /** @type {?} */
        const dokaRoot = this.doka.element;
        outputs$1.forEach((/**
         * @param {?} event
         * @return {?}
         */
        event => dokaRoot.removeEventListener(`Doka:${event.substr(2)}`, this.handleEvent)));
        // we done!
        this.doka.destroy();
        this.doka = null;
    }
}
AngularDokaModalComponent.decorators = [
    { type: Component, args: [{
                selector: 'lib-doka-modal',
                template: `
    <ng-content></ng-content>
  `,
                styles: [`
    :host {
      display: block;
    }
  `]
            }] }
];
/** @nocollapse */
AngularDokaModalComponent.ctorParameters = () => [
    { type: ElementRef },
    { type: NgZone }
];
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
const isSupported$2 = supported();
// Methods not made available on the component
/** @type {?} */
const filteredComponentMethods$2 = [
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
const outputs$2 = [
    'oninit',
    'onconfirm',
    'oncancel',
    'onclose',
    'onload',
    'onloaderror',
    'ondestroy',
    'onupdate'
];
class AngularDokaOverlayComponent {
    /**
     * @param {?} root
     * @param {?} zone
     */
    constructor(root, zone) {
        this.handleEvent = (/**
         * @param {?} e
         * @return {?}
         */
        (e) => {
            /** @type {?} */
            const output = this[`on${e.type.split(':')[1]}`];
            /** @type {?} */
            const event = Object.assign({}, e.detail);
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
    ngOnInit() { }
    /**
     * @return {?}
     */
    ngAfterViewInit() {
        // no sufficient features supported in this browser
        if (!isSupported$2)
            return;
        this.update();
    }
    /**
     * @param {?} changes
     * @return {?}
     */
    ngOnChanges(changes) {
        // no need to handle first change
        if (changes.firstChange)
            return;
        // update!
        this.update(changes);
    }
    /**
     * @return {?}
     */
    ngOnDestroy() {
        this.hide();
    }
    /**
     * @param {?=} changes
     * @return {?}
     */
    show(changes) {
        if (this.doka) {
            // use new options object as base ( or if not available, use current options )
            /** @type {?} */
            const options = changes.options ? changes.options.currentValue : this.options;
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
        () => {
            // get host child <div>
            /** @type {?} */
            const inner = this.root.nativeElement.querySelector('div').firstChild;
            // create instance
            this.doka = create(inner, Object.assign({ src: this.src }, this.options, { styleLayoutMode: 'preview', outputData: true }));
        }));
        // route events
        /** @type {?} */
        const dokaRoot = this.doka.element;
        outputs$2.forEach((/**
         * @param {?} event
         * @return {?}
         */
        event => dokaRoot.addEventListener(`Doka:${event.substr(2)}`, this.handleEvent)));
        // Copy instance method references to component instance
        Object.keys(this.doka)
            // remove unwanted methods
            .filter((/**
         * @param {?} key
         * @return {?}
         */
        key => filteredComponentMethods$2.indexOf(key) === -1))
            // set method references from the component instance to the doka instance
            .forEach((/**
         * @param {?} key
         * @return {?}
         */
        key => this[key] = this.doka[key]));
    }
    /**
     * @return {?}
     */
    hide() {
        // no doka instance available
        if (!this.doka)
            return;
        // detach events
        /** @type {?} */
        const dokaRoot = this.doka.element;
        outputs$2.forEach((/**
         * @param {?} event
         * @return {?}
         */
        event => dokaRoot.removeEventListener(`Doka:${event.substr(2)}`, this.handleEvent)));
        // we done!
        this.doka.destroy();
        this.doka = null;
    }
    /**
     * @param {?=} changes
     * @return {?}
     */
    update(changes) {
        if (this.enabled) {
            this.show(changes);
        }
        else {
            this.hide();
        }
    }
}
AngularDokaOverlayComponent.decorators = [
    { type: Component, args: [{
                selector: 'lib-doka-overlay',
                template: `
    <ng-content></ng-content>
    <div>
        <div></div>
    </div>
  `,
                styles: [`
    :host {
      display: block;
      position: relative;
      overflow: hidden;
    }
    
    :host /deep/ img {
      display: block;
      width: 100%;
      height: auto;
    }
    
    :host > div {
      position: absolute;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
    }
    `]
            }] }
];
/** @nocollapse */
AngularDokaOverlayComponent.ctorParameters = () => [
    { type: ElementRef },
    { type: NgZone }
];
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
class AngularDokaModule {
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
