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
            var event = tslib_1.__assign({}, e.detail);
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
        if (!isSupported)
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
            _this.doka = create(tslib_1.__assign({ 
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
        outputs.forEach((/**
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
export { AngularDokaModalComponent };
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
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiYW5ndWxhci1kb2thLW1vZGFsLmNvbXBvbmVudC5qcyIsInNvdXJjZVJvb3QiOiJuZzovL2FuZ3VsYXItZG9rYS8iLCJzb3VyY2VzIjpbImxpYi9kb2thLW1vZGFsL2FuZ3VsYXItZG9rYS1tb2RhbC5jb21wb25lbnQudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6Ijs7Ozs7QUFBQSxPQUFPLEVBQUUsU0FBUyxFQUFFLFVBQVUsRUFBRSxZQUFZLEVBQUUsTUFBTSxFQUFVLEtBQUssRUFBRSxNQUFNLEVBQWlCLE1BQU0sZUFBZSxDQUFDO0FBQ2xILE9BQU8sRUFBRSxNQUFNLEVBQUUsU0FBUyxFQUFFLE1BQU0sUUFBUSxDQUFDOzs7SUFJckMsV0FBVyxHQUFHLFNBQVMsRUFBRTs7O0lBR3pCLHdCQUF3QixHQUFrQjtJQUM5QyxZQUFZO0lBQ1osSUFBSTtJQUNKLEtBQUs7SUFDTCxRQUFRO0lBQ1IsVUFBVTtJQUNWLGFBQWE7SUFDYixjQUFjO0lBQ2QsY0FBYztJQUNkLGdCQUFnQjtJQUNoQixnQkFBZ0I7SUFDaEIsU0FBUztDQUNWOztJQUVLLE9BQU8sR0FBa0I7SUFDN0IsUUFBUTtJQUNSLFdBQVc7SUFDWCxVQUFVO0lBQ1YsU0FBUztJQUNULFFBQVE7SUFDUixhQUFhO0lBQ2IsV0FBVztJQUNYLFVBQVU7Q0FDWDtBQUVEO0lBb0NFLG1DQUFZLElBQWdCLEVBQUUsSUFBWTtRQUExQyxpQkFHQztRQXRCTyxnQkFBVzs7OztRQUF3QixVQUFDLENBQWE7O2dCQUNqRCxNQUFNLEdBQUcsS0FBSSxDQUFDLE9BQUssQ0FBQyxDQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFHLENBQUM7O2dCQUMxQyxLQUFLLHdCQUFPLENBQUMsQ0FBQyxNQUFNLENBQUM7WUFDM0IsT0FBTyxLQUFLLENBQUMsSUFBSSxDQUFDO1lBQ2xCLE1BQU0sQ0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUM7UUFDckIsQ0FBQyxFQUFDO1FBRU8sUUFBRyxH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LGlCQVdDO1FBVkMsNkJBQTZCO1FBQzdCLElBQUksQ0FBQyxJQUFJLENBQUMsSUFBSTtZQUFFLE9BQU87OztZQUdqQixRQUFRLEdBQWUsSUFBSSxDQUFDLElBQUksQ0FBQyxPQUFPO1FBQzlDLE9BQU8sQ0FBQyxPQUFPOzs7O1FBQUMsVUFBQSxLQUFLLElBQUksT0FBQSxRQUFRLENBQUMsbUJBQW1CLENBQUMsVUFBUSxLQUFLLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBRyxFQUFFLEtBQUksQ0FBQyxXQUFXLENBQUMsRUFBekUsQ0FBeUUsRUFBQyxDQUFDO1FBRXBHLFdBQVc7UUFDWCxJQUFJLENBQUMsSUFBSSxDQUFDLE9BQU8sRUFBRSxDQUFDO1FBQ3BCLElBQUksQ0FBQyxJQUFJLEdBQUcsSUFBSSxDQUFDO0lBQ25CLENBQUM7O2dCQTlHRixTQUFTLFNBQUM7b0JBQ1QsUUFBUSxFQUFFLGdCQUFnQjtvQkFDMUIsUUFBUSxFQUFFLHFDQUVUOzZCQUNRLGlEQUlSO2lCQUNGOzs7O2dCQTNDbUIsVUFBVTtnQkFBZ0IsTUFBTTs7O3NCQXlEakQsS0FBSzswQkFDTCxLQUFLO3lCQUVMLE1BQU07NEJBQ04sTUFBTTsyQkFDTixNQUFNOzBCQUNOLE1BQU07eUJBQ04sTUFBTTs4QkFDTixNQUFNOzRCQUNOLE1BQU07MkJBQ04sTUFBTTs7SUE4RVQsZ0NBQUM7Q0FBQSxBQWhIRCxJQWdIQztTQXBHWSx5QkFBeUI7Ozs7OztJQUVwQyx5Q0FBeUI7Ozs7O0lBQ3pCLHlDQUFxQjs7Ozs7SUFDckIseUNBQTRCOzs7OztJQUM1QixnREFLRTs7SUFFRix3Q0FBc0M7O0lBQ3RDLDRDQUFvQzs7SUFFcEMsMkNBQXlEOztJQUN6RCw4Q0FBNEQ7O0lBQzVELDZDQUEyRDs7SUFDM0QsNENBQTBEOztJQUMxRCwyQ0FBeUQ7O0lBQ3pELGdEQUE4RDs7SUFDOUQsOENBQTREOztJQUM1RCw2Q0FBMkQiLCJzb3VyY2VzQ29udGVudCI6WyJpbXBvcnQgeyBDb21wb25lbnQsIEVsZW1lbnRSZWYsIEV2ZW50RW1pdHRlciwgTmdab25lLCBPbkluaXQsIElucHV0LCBPdXRwdXQsIFNpbXBsZUNoYW5nZXMgfSBmcm9tICdAYW5ndWxhci9jb3JlJztcbmltcG9ydCB7IGNyZWF0ZSwgc3VwcG9ydGVkIH0gZnJvbSAnLi4vbGliJztcbmltcG9ydCB7IElEb2thSW5zdGFuY2UsIElEb2thT3B0aW9ucyB9IGZyb20gJy4uL2xpYi9kb2thJztcblxuLy8gV2UgdGVzdCBpZiBEb2thIGlzIHN1cHBvcnRlZCBvbiB0aGUgY3VycmVudCBjbGllbnRcbmNvbnN0IGlzU3VwcG9ydGVkID0gc3VwcG9ydGVkKCk7XG5cbi8vIE1ldGhvZHMgbm90IG1hZGUgYXZhaWxhYmxlIG9uIHRoZSBjb21wb25lbnRcbmNvbnN0IGZpbHRlcmVkQ29tcG9uZW50TWV0aG9kczogQXJyYXk8c3RyaW5nPiA9IFtcbiAgJ3NldE9wdGlvbnMnLFxuICAnb24nLFxuICAnb2ZmJyxcbiAgJ29uT25jZScsXG4gICdhcHBlbmRUbycsXG4gICdpbnNlcnRBZnRlcicsXG4gICdpbnNlcnRCZWZvcmUnLFxuICAnaXNBdHRhY2hlZFRvJyxcbiAgJ3JlcGxhY2VFbGVtZW50JyxcbiAgJ3Jlc3RvcmVFbGVtZW50JyxcbiAgJ2Rlc3Ryb3knXG5dO1xuXG5jb25zdCBvdXRwdXRzOiBBcnJheTxzdHJpbmc+ID0gW1xuICAnb25pbml0JywgXG4gICdvbmNvbmZpcm0nLCBcbiAgJ29uY2FuY2VsJywgXG4gICdvbmNsb3NlJyxcbiAgJ29ubG9hZCcsIFxuICAnb25sb2FkZXJyb3InLCBcbiAgJ29uZGVzdHJveScsIFxuICAnb251cGRhdGUnXG5dO1xuXG5AQ29tcG9uZW50KHtcbiAgc2VsZWN0b3I6ICdsaWItZG9rYS1tb2RhbCcsXG4gIHRlbXBsYXRlOiBgXG4gICAgPG5nLWNvbnRlbnQ+PC9uZy1jb250ZW50PlxuICBgLFxuICBzdHlsZXM6IFtgXG4gICAgOmhvc3Qge1xuICAgICAgZGlzcGxheTogYmxvY2s7XG4gICAgfVxuICBgXVxufSlcblxuZXhwb3J0IGNsYXNzIEFuZ3VsYXJEb2thTW9kYWxDb21wb25lbnQgaW1wbGVtZW50cyBPbkluaXQge1xuXG4gIHByaXZhdGUgcm9vdDogRWxlbWVudFJlZjtcbiAgcHJpdmF0ZSB6b25lOiBOZ1pvbmU7XG4gIHByaXZhdGUgZG9rYTogSURva2FJbnN0YW5jZTtcbiAgcHJpdmF0ZSBoYW5kbGVFdmVudDogRXZlbnRIYW5kbGVyTm9uTnVsbCA9IChlOkN1c3RvbUV2ZW50KSA9PiB7XG4gICAgY29uc3Qgb3V0cHV0ID0gdGhpc1tgb24ke2UudHlwZS5zcGxpdCgnOicpWzFdfWBdO1xuICAgIGNvbnN0IGV2ZW50ID0gey4uLmUuZGV0YWlsfTtcbiAgICBkZWxldGUgZXZlbnQuZG9rYTtcbiAgICBvdXRwdXQuZW1pdChldmVudCk7XG4gIH07XG5cbiAgQElucHV0KCkgc3JjOiBzdHJpbmd8RmlsZXxCbG9iID0gbnVsbDtcbiAgQElucHV0KCkgb3B0aW9uczogSURva2FPcHRpb25zID0ge307XG5cbiAgQE91dHB1dCgpIG9uaW5pdDogRXZlbnRFbWl0dGVyPGFueT4gPSBuZXcgRXZlbnRFbWl0dGVyKCk7XG4gIEBPdXRwdXQoKSBvbmNvbmZpcm06IEV2ZW50RW1pdHRlcjxhbnk+ID0gbmV3IEV2ZW50RW1pdHRlcigpO1xuICBAT3V0cHV0KCkgb25jYW5jZWw6IEV2ZW50RW1pdHRlcjxhbnk+ID0gbmV3IEV2ZW50RW1pdHRlcigpO1xuICBAT3V0cHV0KCkgb25jbG9zZTogRXZlbnRFbWl0dGVyPGFueT4gPSBuZXcgRXZlbnRFbWl0dGVyKCk7XG4gIEBPdXRwdXQoKSBvbmxvYWQ6IEV2ZW50RW1pdHRlcjxhbnk+ID0gbmV3IEV2ZW50RW1pdHRlcigpO1xuICBAT3V0cHV0KCkgb25sb2FkZXJyb3I6IEV2ZW50RW1pdHRlcjxhbnk+ID0gbmV3IEV2ZW50RW1pdHRlcigpO1xuICBAT3V0cHV0KCkgb25kZXN0cm95OiBFdmVudEVtaXR0ZXI8YW55PiA9IG5ldyBFdmVudEVtaXR0ZXIoKTtcbiAgQE91dHB1dCgpIG9udXBkYXRlOiBFdmVudEVtaXR0ZXI8YW55PiA9IG5ldyBFdmVudEVtaXR0ZXIoKTtcblxuICBjb25zdHJ1Y3Rvcihyb290OiBFbGVtZW50UmVmLCB6b25lOiBOZ1pvbmUpIHtcbiAgICB0aGlzLnJvb3QgPSByb290O1xuICAgIHRoaXMuem9uZSA9IHpvbmU7XG4gIH1cbiAgXG4gIG5nT25Jbml0KCkge31cblxuICBuZ0FmdGVyVmlld0luaXQoKSB7XG5cbiAgICAvLyBubyBzdWZmaWNpZW50IGZlYXR1cmVzIHN1cHBvcnRlZCBpbiB0aGlzIGJyb3dzZXJcbiAgICBpZiAoIWlzU3VwcG9ydGVkKSByZXR1cm47XG5cbiAgICAvLyB3aWxsIGJsb2NrIGFuZ3VsYXIgZnJvbSBsaXN0ZW5pbmcgdG8gZXZlbnRzIGluc2lkZSBkb2thXG4gICAgdGhpcy56b25lLnJ1bk91dHNpZGVBbmd1bGFyKCgpID0+IHtcblxuICAgICAgLy8gZ2V0IGhvc3QgY2hpbGQgPGRpdj5cbiAgICAgIGNvbnN0IGlubmVyID0gdGhpcy5yb290Lm5hdGl2ZUVsZW1lbnQ7XG5cbiAgICAgIC8vIGlmIGltYWdlIG9yIGNhbnZhcyBzdXBwbGllZFxuICAgICAgY29uc3Qgc3JjID0gaW5uZXIucXVlcnlTZWxlY3RvcignaW1nJykgfHwgaW5uZXIucXVlcnlTZWxlY3RvcignY2FudmFzJykgfHwgdGhpcy5zcmM7XG4gICAgICBcbiAgICAgIC8vIGNyZWF0ZSBpbnN0YW5jZVxuICAgICAgdGhpcy5kb2thID0gY3JlYXRlKHtcbiAgICAgICAgLy8gc291cmNlIGZyb20gc2xvdFxuICAgICAgICBzcmMsXG5cbiAgICAgICAgLy8gb3VyIG9wdGlvbnNcbiAgICAgICAgLi4udGhpcy5vcHRpb25zXG4gICAgICB9KTtcblxuICAgIH0pO1xuXG4gICAgLy8gcm91dGUgZXZlbnRzXG4gICAgY29uc3QgZG9rYVJvb3Q6SFRNTEVsZW1lbnQgPSB0aGlzLmRva2EuZWxlbWVudDtcbiAgICBvdXRwdXRzLmZvckVhY2goZXZlbnQgPT4gZG9rYVJvb3QuYWRkRXZlbnRMaXN0ZW5lcihgRG9rYToke2V2ZW50LnN1YnN0cigyKX1gLCB0aGlzLmhhbmRsZUV2ZW50KSk7XG5cbiAgICAvLyBDb3B5IGluc3RhbmNlIG1ldGhvZCByZWZlcmVuY2VzIHRvIGNvbXBvbmVudCBpbnN0YW5jZVxuICAgIE9iamVjdC5rZXlzKHRoaXMuZG9rYSlcblxuICAgICAgLy8gcmVtb3ZlIHVud2FudGVkIG1ldGhvZHNcbiAgICAgIC5maWx0ZXIoa2V5ID0+IGZpbHRlcmVkQ29tcG9uZW50TWV0aG9kcy5pbmRleE9mKGtleSkgPT09IC0xKVxuICAgICAgXG4gICAgICAvLyBzZXQgbWV0aG9kIHJlZmVyZW5jZXMgZnJvbSB0aGUgY29tcG9uZW50IGluc3RhbmNlIHRvIHRoZSBkb2thIGluc3RhbmNlXG4gICAgICAuZm9yRWFjaChrZXkgPT4gdGhpc1trZXldID0gdGhpcy5kb2thW2tleV0pO1xuICB9XG5cbiAgbmdPbkNoYW5nZXMoY2hhbmdlczogU2ltcGxlQ2hhbmdlcykge1xuICAgIC8vIG5vIG5lZWQgdG8gaGFuZGxlIGZpcnN0IGNoYW5nZVxuICAgIGlmIChjaGFuZ2VzLmZpcnN0Q2hhbmdlKSByZXR1cm47XG5cbiAgICAvLyBubyBkb2thIGluc3RhbmNlIGF2YWlsYWJsZVxuICAgIGlmICghdGhpcy5kb2thKSByZXR1cm47XG5cbiAgICAvLyB1c2UgbmV3IG9wdGlvbnMgb2JqZWN0IGFzIGJhc2UgKCBvciBpZiBub3QgYXZhaWxhYmxlLCB1c2UgY3VycmVudCBvcHRpb25zIClcbiAgICBjb25zdCBvcHRpb25zID0gY2hhbmdlcy5vcHRpb25zID8gY2hhbmdlcy5vcHRpb25zLmN1cnJlbnRWYWx1ZSA6IHRoaXMub3B0aW9ucztcbiAgICBcbiAgICAvLyB1cGRhdGUgc291cmNlXG4gICAgaWYgKGNoYW5nZXMuc3JjKSBvcHRpb25zLnNyYyA9IGNoYW5nZXMuc3JjLmN1cnJlbnRWYWx1ZTtcblxuICAgIC8vIHNldCBuZXcgb3B0aW9uc1xuICAgIHRoaXMuZG9rYS5zZXRPcHRpb25zKG9wdGlvbnMpO1xuICB9XG5cbiAgbmdPbkRlc3Ryb3koKSB7XG4gICAgLy8gbm8gZG9rYSBpbnN0YW5jZSBhdmFpbGFibGVcbiAgICBpZiAoIXRoaXMuZG9rYSkgcmV0dXJuO1xuXG4gICAgLy8gZGV0YWNoIGV2ZW50c1xuICAgIGNvbnN0IGRva2FSb290OkhUTUxFbGVtZW50ID0gdGhpcy5kb2thLmVsZW1lbnQ7XG4gICAgb3V0cHV0cy5mb3JFYWNoKGV2ZW50ID0+IGRva2FSb290LnJlbW92ZUV2ZW50TGlzdGVuZXIoYERva2E6JHtldmVudC5zdWJzdHIoMil9YCwgdGhpcy5oYW5kbGVFdmVudCkpO1xuXG4gICAgLy8gd2UgZG9uZSFcbiAgICB0aGlzLmRva2EuZGVzdHJveSgpO1xuICAgIHRoaXMuZG9rYSA9IG51bGw7XG4gIH1cblxufSJdfQ==