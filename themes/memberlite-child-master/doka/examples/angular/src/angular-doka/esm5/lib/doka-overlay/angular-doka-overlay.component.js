/**
 * @fileoverview added by tsickle
 * @suppress {checkTypes,constantProperty,extraRequire,missingOverride,missingReturn,unusedPrivateMembers,uselessCode} checked by tsc
 */
import * as tslib_1 from "tslib";
import { Component, ElementRef, EventEmitter, NgZone, Input, Output } from '@angular/core';
import { create, supported } from '../lib';
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
            var event = tslib_1.__assign({}, e.detail);
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
        if (!isSupported)
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
            _this.doka = create(inner, tslib_1.__assign({ src: _this.src }, _this.options, { styleLayoutMode: 'preview', outputData: true }));
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
        outputs.forEach((/**
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
export { AngularDokaOverlayComponent };
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
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiYW5ndWxhci1kb2thLW92ZXJsYXkuY29tcG9uZW50LmpzIiwic291cmNlUm9vdCI6Im5nOi8vYW5ndWxhci1kb2thLyIsInNvdXJjZXMiOlsibGliL2Rva2Etb3ZlcmxheS9hbmd1bGFyLWRva2Etb3ZlcmxheS5jb21wb25lbnQudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6Ijs7Ozs7QUFBQSxPQUFPLEVBQUUsU0FBUyxFQUFFLFVBQVUsRUFBRSxZQUFZLEVBQUUsTUFBTSxFQUFVLEtBQUssRUFBRSxNQUFNLEVBQWlCLE1BQU0sZUFBZSxDQUFDO0FBQ2xILE9BQU8sRUFBRSxNQUFNLEVBQUUsU0FBUyxFQUFFLE1BQU0sUUFBUSxDQUFDOzs7SUFJckMsV0FBVyxHQUFHLFNBQVMsRUFBRTs7O0lBR3pCLHdCQUF3QixHQUFhO0lBQ3pDLFlBQVk7SUFDWixJQUFJO0lBQ0osS0FBSztJQUNMLFFBQVE7SUFDUixVQUFVO0lBQ1YsYUFBYTtJQUNiLGNBQWM7SUFDZCxjQUFjO0lBQ2QsZ0JBQWdCO0lBQ2hCLGdCQUFnQjtJQUNoQixTQUFTO0NBQ1Y7O0lBRUssT0FBTyxHQUFhO0lBQ3hCLFFBQVE7SUFDUixXQUFXO0lBQ1gsVUFBVTtJQUNWLFNBQVM7SUFDVCxRQUFRO0lBQ1IsYUFBYTtJQUNiLFdBQVc7SUFDWCxVQUFVO0NBQ1g7QUFFRDtJQXdERSxxQ0FBWSxJQUFnQixFQUFFLElBQVk7UUFBMUMsaUJBR0M7UUF2Qk8sZ0JBQVc7Ozs7UUFBd0IsVUFBQyxDQUFhOztnQkFDakQsTUFBTSxHQUFHLEtBQUksQ0FBQyxPQUFLLENBQUMsQ0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBRyxDQUFDOztnQkFDMUMsS0FBSyx3QkFBTyxDQUFDLENBQUMsTUFBTSxDQUFDO1lBQzNCLE9BQU8sS0FBSyxDQUFDLElBQUksQ0FBQztZQUNsQixNQUFNLENBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFDO1FBQ3JCLENBQUMsRUFBQztRQUVPLFFB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TTt5QkFDTixNQUFNOzhCQUNOLE1BQU07NEJBQ04sTUFBTTsyQkFDTixNQUFNOztJQXVHVCxrQ0FBQztDQUFBLEFBN0pELElBNkpDO1NBOUhZLDJCQUEyQjs7Ozs7O0lBRXRDLDJDQUF5Qjs7Ozs7SUFDekIsMkNBQXFCOzs7OztJQUNyQiwyQ0FBNEI7Ozs7O0lBQzVCLGtEQUtFOztJQUVGLDBDQUF5RTs7SUFDekUsOENBQW9DOztJQUNwQyw4Q0FBa0M7O0lBRWxDLDZDQUF5RDs7SUFDekQsZ0RBQTREOztJQUM1RCwrQ0FBMkQ7O0lBQzNELDhDQUEwRDs7SUFDMUQsNkNBQXlEOztJQUN6RCxrREFBOEQ7O0lBQzlELGdEQUE0RDs7SUFDNUQsK0NBQTJEIiwic291cmNlc0NvbnRlbnQiOlsiaW1wb3J0IHsgQ29tcG9uZW50LCBFbGVtZW50UmVmLCBFdmVudEVtaXR0ZXIsIE5nWm9uZSwgT25Jbml0LCBJbnB1dCwgT3V0cHV0LCBTaW1wbGVDaGFuZ2VzIH0gZnJvbSAnQGFuZ3VsYXIvY29yZSc7XG5pbXBvcnQgeyBjcmVhdGUsIHN1cHBvcnRlZCB9IGZyb20gJy4uL2xpYic7XG5pbXBvcnQgeyBJRG9rYUluc3RhbmNlLCBJRG9rYU9wdGlvbnMgfSAgZnJvbSAnLi4vbGliL2Rva2EnO1xuXG4vLyBXZSB0ZXN0IGlmIERva2EgaXMgc3VwcG9ydGVkIG9uIHRoZSBjdXJyZW50IGNsaWVudFxuY29uc3QgaXNTdXBwb3J0ZWQgPSBzdXBwb3J0ZWQoKTtcblxuLy8gTWV0aG9kcyBub3QgbWFkZSBhdmFpbGFibGUgb24gdGhlIGNvbXBvbmVudFxuY29uc3QgZmlsdGVyZWRDb21wb25lbnRNZXRob2RzOiBzdHJpbmdbXSA9IFtcbiAgJ3NldE9wdGlvbnMnLFxuICAnb24nLFxuICAnb2ZmJyxcbiAgJ29uT25jZScsXG4gICdhcHBlbmRUbycsXG4gICdpbnNlcnRBZnRlcicsXG4gICdpbnNlcnRCZWZvcmUnLFxuICAnaXNBdHRhY2hlZFRvJyxcbiAgJ3JlcGxhY2VFbGVtZW50JyxcbiAgJ3Jlc3RvcmVFbGVtZW50JyxcbiAgJ2Rlc3Ryb3knXG5dO1xuXG5jb25zdCBvdXRwdXRzOiBzdHJpbmdbXSA9IFtcbiAgJ29uaW5pdCcsIFxuICAnb25jb25maXJtJywgXG4gICdvbmNhbmNlbCcsIFxuICAnb25jbG9zZScsXG4gICdvbmxvYWQnLCBcbiAgJ29ubG9hZGVycm9yJywgXG4gICdvbmRlc3Ryb3knLCBcbiAgJ29udXBkYXRlJ1xuXTtcblxuQENvbXBvbmVudCh7XG4gIHNlbGVjdG9yOiAnbGliLWRva2Etb3ZlcmxheScsXG4gIHRlbXBsYXRlOiBgXG4gICAgPG5nLWNvbnRlbnQ+PC9uZy1jb250ZW50PlxuICAgIDxkaXY+XG4gICAgICAgIDxkaXY+PC9kaXY+XG4gICAgPC9kaXY+XG4gIGAsXG4gIHN0eWxlczogW2BcbiAgICA6aG9zdCB7XG4gICAgICBkaXNwbGF5OiBibG9jaztcbiAgICAgIHBvc2l0aW9uOiByZWxhdGl2ZTtcbiAgICAgIG92ZXJmbG93OiBoaWRkZW47XG4gICAgfVxuICAgIFxuICAgIDpob3N0IC9kZWVwLyBpbWcge1xuICAgICAgZGlzcGxheTogYmxvY2s7XG4gICAgICB3aWR0aDogMTAwJTtcbiAgICAgIGhlaWdodDogYXV0bztcbiAgICB9XG4gICAgXG4gICAgOmhvc3QgPiBkaXYge1xuICAgICAgcG9zaXRpb246IGFic29sdXRlO1xuICAgICAgbGVmdDogMDtcbiAgICAgIHRvcDogMDtcbiAgICAgIHdpZHRoOiAxMDAlO1xuICAgICAgaGVpZ2h0OiAxMDAlO1xuICAgIH1cbiAgICBgXVxufSlcblxuZXhwb3J0IGNsYXNzIEFuZ3VsYXJEb2thT3ZlcmxheUNvbXBvbmVudCBpbXBsZW1lbnRzIE9uSW5pdCB7XG5cbiAgcHJpdmF0ZSByb290OiBFbGVtZW50UmVmO1xuICBwcml2YXRlIHpvbmU6IE5nWm9uZTtcbiAgcHJpdmF0ZSBkb2thOiBJRG9rYUluc3RhbmNlO1xuICBwcml2YXRlIGhhbmRsZUV2ZW50OiBFdmVudEhhbmRsZXJOb25OdWxsID0gKGU6Q3VzdG9tRXZlbnQpID0+IHtcbiAgICBjb25zdCBvdXRwdXQgPSB0aGlzW2BvbiR7ZS50eXBlLnNwbGl0KCc6JylbMV19YF07XG4gICAgY29uc3QgZXZlbnQgPSB7Li4uZS5kZXRhaWx9O1xuICAgIGRlbGV0ZSBldmVudC5kb2thO1xuICAgIG91dHB1dC5lbWl0KGV2ZW50KTtcbiAgfTtcblxuICBASW5wdXQoKSBzcmM6IHN0cmluZ3xGaWxlfEJsb2J8SFRNTEltYWdlRWxlbWVudHxIVE1MQ2FudmFzRWxlbWVudCA9IG51bGw7XG4gIEBJbnB1dCgpIG9wdGlvbnM6IElEb2thT3B0aW9ucyA9IHt9O1xuICBASW5wdXQoKSBlbmFibGVkOiBib29sZWFuID0gZmFsc2U7XG5cbiAgQE91dHB1dCgpIG9uaW5pdDogRXZlbnRFbWl0dGVyPGFueT4gPSBuZXcgRXZlbnRFbWl0dGVyKCk7XG4gIEBPdXRwdXQoKSBvbmNvbmZpcm06IEV2ZW50RW1pdHRlcjxhbnk+ID0gbmV3IEV2ZW50RW1pdHRlcigpO1xuICBAT3V0cHV0KCkgb25jYW5jZWw6IEV2ZW50RW1pdHRlcjxhbnk+ID0gbmV3IEV2ZW50RW1pdHRlcigpO1xuICBAT3V0cHV0KCkgb25jbG9zZTogRXZlbnRFbWl0dGVyPGFueT4gPSBuZXcgRXZlbnRFbWl0dGVyKCk7XG4gIEBPdXRwdXQoKSBvbmxvYWQ6IEV2ZW50RW1pdHRlcjxhbnk+ID0gbmV3IEV2ZW50RW1pdHRlcigpO1xuICBAT3V0cHV0KCkgb25sb2FkZXJyb3I6IEV2ZW50RW1pdHRlcjxhbnk+ID0gbmV3IEV2ZW50RW1pdHRlcigpO1xuICBAT3V0cHV0KCkgb25kZXN0cm95OiBFdmVudEVtaXR0ZXI8YW55PiA9IG5ldyBFdmVudEVtaXR0ZXIoKTtcbiAgQE91dHB1dCgpIG9udXBkYXRlOiBFdmVudEVtaXR0ZXI8YW55PiA9IG5ldyBFdmVudEVtaXR0ZXIoKTtcblxuICBjb25zdHJ1Y3Rvcihyb290OiBFbGVtZW50UmVmLCB6b25lOiBOZ1pvbmUpIHtcbiAgICB0aGlzLnJvb3QgPSByb290O1xuICAgIHRoaXMuem9uZSA9IHpvbmU7XG4gIH1cblxuICBuZ09uSW5pdCgpIHt9XG5cbiAgbmdBZnRlclZpZXdJbml0KCkge1xuXG4gICAgLy8gbm8gc3VmZmljaWVudCBmZWF0dXJlcyBzdXBwb3J0ZWQgaW4gdGhpcyBicm93c2VyXG4gICAgaWYgKCFpc1N1cHBvcnRlZCkgcmV0dXJuO1xuXG4gICAgdGhpcy51cGRhdGUoKTtcbiAgfVxuXG4gIG5nT25DaGFuZ2VzKGNoYW5nZXM6IFNpbXBsZUNoYW5nZXMpIHtcbiAgICAvLyBubyBuZWVkIHRvIGhhbmRsZSBmaXJzdCBjaGFuZ2VcbiAgICBpZiAoY2hhbmdlcy5maXJzdENoYW5nZSkgcmV0dXJuO1xuXG4gICAgLy8gdXBkYXRlIVxuICAgIHRoaXMudXBkYXRlKGNoYW5nZXMpO1xuICB9XG5cbiAgbmdPbkRlc3Ryb3koKSB7XG4gICAgdGhpcy5oaWRlKCk7XG4gIH1cblxuICBzaG93KGNoYW5nZXM/OiBTaW1wbGVDaGFuZ2VzKSB7XG5cbiAgICBpZiAodGhpcy5kb2thKSB7XG5cbiAgICAgIC8vIHVzZSBuZXcgb3B0aW9ucyBvYmplY3QgYXMgYmFzZSAoIG9yIGlmIG5vdCBhdmFpbGFibGUsIHVzZSBjdXJyZW50IG9wdGlvbnMgKVxuICAgICAgY29uc3Qgb3B0aW9ucyA9IGNoYW5nZXMub3B0aW9ucyA/IGNoYW5nZXMub3B0aW9ucy5jdXJyZW50VmFsdWUgOiB0aGlzLm9wdGlvbnM7XG4gICAgICBcbiAgICAgIC8vIHVwZGF0ZSBzb3VyY2VcbiAgICAgIGlmIChjaGFuZ2VzLnNyYykgb3B0aW9ucy5zcmMgPSBjaGFuZ2VzLnNyYy5jdXJyZW50VmFsdWU7XG5cbiAgICAgIC8vIHNldCBuZXcgb3B0aW9uc1xuICAgICAgdGhpcy5kb2thLnNldE9wdGlvbnMob3B0aW9ucyk7XG5cbiAgICAgIHJldHVybjtcbiAgICB9XG5cbiAgICAvLyB3aWxsIGJsb2NrIGFuZ3VsYXIgZnJvbSBsaXN0ZW5pbmcgdG8gZXZlbnRzIGluc2lkZSBkb2thXG4gICAgdGhpcy56b25lLnJ1bk91dHNpZGVBbmd1bGFyKCgpID0+IHtcblxuICAgICAgLy8gZ2V0IGhvc3QgY2hpbGQgPGRpdj5cbiAgICAgIGNvbnN0IGlubmVyID0gdGhpcy5yb290Lm5hdGl2ZUVsZW1lbnQucXVlcnlTZWxlY3RvcignZGl2JykuZmlyc3RDaGlsZDtcblxuICAgICAgLy8gY3JlYXRlIGluc3RhbmNlXG4gICAgICB0aGlzLmRva2EgPSBjcmVhdGUoaW5uZXIsIHtcbiAgICAgICAgLy8gc291cmNlIGZyb20gc2xvdFxuICAgICAgICBzcmM6IHRoaXMuc3JjLFxuXG4gICAgICAgIC8vIG91ciBvcHRpb25zXG4gICAgICAgIC4uLnRoaXMub3B0aW9ucyxcblxuICAgICAgICAvLyBhbHdheXMgcHJldmlldyBtb2RlXG4gICAgICAgIHN0eWxlTGF5b3V0TW9kZTogJ3ByZXZpZXcnLFxuICAgICAgICBvdXRwdXREYXRhOiB0cnVlXG4gICAgICB9KTtcblxuICAgIH0pO1xuXG4gICAgLy8gcm91dGUgZXZlbnRzXG4gICAgY29uc3QgZG9rYVJvb3Q6SFRNTEVsZW1lbnQgPSB0aGlzLmRva2EuZWxlbWVudDtcbiAgICBvdXRwdXRzLmZvckVhY2goZXZlbnQgPT4gZG9rYVJvb3QuYWRkRXZlbnRMaXN0ZW5lcihgRG9rYToke2V2ZW50LnN1YnN0cigyKX1gLCB0aGlzLmhhbmRsZUV2ZW50KSk7XG5cbiAgICAvLyBDb3B5IGluc3RhbmNlIG1ldGhvZCByZWZlcmVuY2VzIHRvIGNvbXBvbmVudCBpbnN0YW5jZVxuICAgIE9iamVjdC5rZXlzKHRoaXMuZG9rYSlcblxuICAgICAgLy8gcmVtb3ZlIHVud2FudGVkIG1ldGhvZHNcbiAgICAgIC5maWx0ZXIoa2V5ID0+IGZpbHRlcmVkQ29tcG9uZW50TWV0aG9kcy5pbmRleE9mKGtleSkgPT09IC0xKVxuICAgICAgXG4gICAgICAvLyBzZXQgbWV0aG9kIHJlZmVyZW5jZXMgZnJvbSB0aGUgY29tcG9uZW50IGluc3RhbmNlIHRvIHRoZSBkb2thIGluc3RhbmNlXG4gICAgICAuZm9yRWFjaChrZXkgPT4gdGhpc1trZXldID0gdGhpcy5kb2thW2tleV0pO1xuICB9XG5cbiAgaGlkZSgpIHtcblxuICAgIC8vIG5vIGRva2EgaW5zdGFuY2UgYXZhaWxhYmxlXG4gICAgaWYgKCF0aGlzLmRva2EpIHJldHVybjtcblxuICAgIC8vIGRldGFjaCBldmVudHNcbiAgICBjb25zdCBkb2thUm9vdDpIVE1MRWxlbWVudCA9IHRoaXMuZG9rYS5lbGVtZW50O1xuICAgIG91dHB1dHMuZm9yRWFjaChldmVudCA9PiBkb2thUm9vdC5yZW1vdmVFdmVudExpc3RlbmVyKGBEb2thOiR7ZXZlbnQuc3Vic3RyKDIpfWAsIHRoaXMuaGFuZGxlRXZlbnQpKTtcblxuICAgIC8vIHdlIGRvbmUhXG4gICAgdGhpcy5kb2thLmRlc3Ryb3koKTtcbiAgICB0aGlzLmRva2EgPSBudWxsO1xuICB9XG5cbiAgdXBkYXRlKGNoYW5nZXM/OiBTaW1wbGVDaGFuZ2VzKSB7XG4gICAgaWYgKHRoaXMuZW5hYmxlZCkge1xuICAgICAgICB0aGlzLnNob3coY2hhbmdlcyk7XG4gICAgfVxuICAgIGVsc2Uge1xuICAgICAgICB0aGlzLmhpZGUoKTtcbiAgICB9XG4gIH1cblxufVxuIl19