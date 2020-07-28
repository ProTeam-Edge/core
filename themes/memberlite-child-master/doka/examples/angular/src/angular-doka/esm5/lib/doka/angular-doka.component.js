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
            _this.doka = create(inner, tslib_1.__assign({ 
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
export { AngularDokaComponent };
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
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiYW5ndWxhci1kb2thLmNvbXBvbmVudC5qcyIsInNvdXJjZVJvb3QiOiJuZzovL2FuZ3VsYXItZG9rYS8iLCJzb3VyY2VzIjpbImxpYi9kb2thL2FuZ3VsYXItZG9rYS5jb21wb25lbnQudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6Ijs7Ozs7QUFBQSxPQUFPLEVBQUUsU0FBUyxFQUFFLFVBQVUsRUFBRSxZQUFZLEVBQUUsTUFBTSxFQUFVLEtBQUssRUFBRSxNQUFNLEVBQWlCLE1BQU0sZUFBZSxDQUFDO0FBQ2xILE9BQU8sRUFBRSxNQUFNLEVBQUUsU0FBUyxFQUFFLE1BQU0sUUFBUSxDQUFDOzs7SUFJckMsV0FBVyxHQUFHLFNBQVMsRUFBRTs7O0lBR3pCLHdCQUF3QixHQUFrQjtJQUM5QyxZQUFZO0lBQ1osSUFBSTtJQUNKLEtBQUs7SUFDTCxRQUFRO0lBQ1IsVUFBVTtJQUNWLGFBQWE7SUFDYixjQUFjO0lBQ2QsY0FBYztJQUNkLGdCQUFnQjtJQUNoQixnQkFBZ0I7SUFDaEIsU0FBUztDQUNWOztJQUVLLE9BQU8sR0FBa0I7SUFDN0IsUUFBUTtJQUNSLFdBQVc7SUFDWCxVQUFVO0lBQ1YsU0FBUztJQUNULFFBQVE7SUFDUixhQUFhO0lBQ2IsV0FBVztJQUNYLFVBQVU7Q0FDWDtBQUVEO0lBc0NFLDhCQUFZLElBQWdCLEVBQUUsSUFBWTtRQUExQyxpQkFHQztRQXRCTyxnQkFBVzs7OztRQUF3QixVQUFDLENBQWE7O2dCQUNqRCxNQUFNLEdBQUcsS0FBSSxDQUFDLE9BQUssQ0FBQyxDQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFHLENBQUM7O2dCQUMxQyxLQUFLLHdCQUFPLENBQUMsQ0FBQyxNQUFNLENBQUM7WUFDM0IsT0FBTyxLQUFLLENBQUMsSUFBSSxDQUFDO1lBQ2xCLE1BQU0sQ0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUM7UUFDckIsQ0FBQyxFQUFDO1FBRU8sUUFBRyxHQUFxQixJQUFJLENBQUM7UUFD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QUEsaUJBV0M7UUFWQyw2QkFBNkI7UUFDN0IsSUFBSSxDQUFDLElBQUksQ0FBQyxJQUFJO1lBQUUsT0FBTzs7O1lBR2pCLFFBQVEsR0FBZSxJQUFJLENBQUMsSUFBSSxDQUFDLE9BQU87UUFDOUMsT0FBTyxDQUFDLE9BQU87Ozs7UUFBQyxVQUFBLEtBQUssSUFBSSxPQUFBLFFBQVEsQ0FBQyxtQkFBbUIsQ0FBQyxVQUFRLEtBQUssQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFHLEVBQUUsS0FBSSxDQUFDLFdBQVcsQ0FBQyxFQUF6RSxDQUF5RSxFQUFDLENBQUM7UUFFcEcsV0FBVztRQUNYLElBQUksQ0FBQyxJQUFJLENBQUMsT0FBTyxFQUFFLENBQUM7UUFDcEIsSUFBSSxDQUFDLElBQUksR0FBRyxJQUFJLENBQUM7SUFDbkIsQ0FBQzs7Z0JBaEhGLFNBQVMsU0FBQztvQkFDVCxRQUFRLEVBQUUsVUFBVTtvQkFDcEIsUUFBUSxFQUFFLDhEQUlUOzZCQUNRLGlEQUlSO2lCQUNGOzs7O2dCQTdDbUIsVUFBVTtnQkFBZ0IsTUFBTTs7O3NCQTJEakQsS0FBSzswQkFDTCxLQUFLO3lCQUVMLE1BQU07NEJBQ04sTUFBTTsyQkFDTixNQUFNOzBCQUNOLE1BQU07eUJBQ04sTUFBTTs4QkFDTixNQUFNOzRCQUNOLE1BQU07MkJBQ04sTUFBTTs7SUE4RVQsMkJBQUM7Q0FBQSxBQWxIRCxJQWtIQztTQXBHWSxvQkFBb0I7Ozs7OztJQUUvQixvQ0FBeUI7Ozs7O0lBQ3pCLG9DQUFxQjs7Ozs7SUFDckIsb0NBQTRCOzs7OztJQUM1QiwyQ0FLRTs7SUFFRixtQ0FBc0M7O0lBQ3RDLHVDQUFvQzs7SUFFcEMsc0NBQXlEOztJQUN6RCx5Q0FBNEQ7O0lBQzVELHdDQUEyRDs7SUFDM0QsdUNBQTBEOztJQUMxRCxzQ0FBeUQ7O0lBQ3pELDJDQUE4RDs7SUFDOUQseUNBQTREOztJQUM1RCx3Q0FBMkQiLCJzb3VyY2VzQ29udGVudCI6WyJpbXBvcnQgeyBDb21wb25lbnQsIEVsZW1lbnRSZWYsIEV2ZW50RW1pdHRlciwgTmdab25lLCBPbkluaXQsIElucHV0LCBPdXRwdXQsIFNpbXBsZUNoYW5nZXMgfSBmcm9tICdAYW5ndWxhci9jb3JlJztcbmltcG9ydCB7IGNyZWF0ZSwgc3VwcG9ydGVkIH0gZnJvbSAnLi4vbGliJztcbmltcG9ydCB7IElEb2thSW5zdGFuY2UsIElEb2thT3B0aW9ucyB9IGZyb20gJy4uL2xpYi9kb2thJztcblxuLy8gV2UgdGVzdCBpZiBEb2thIGlzIHN1cHBvcnRlZCBvbiB0aGUgY3VycmVudCBjbGllbnRcbmNvbnN0IGlzU3VwcG9ydGVkID0gc3VwcG9ydGVkKCk7XG5cbi8vIE1ldGhvZHMgbm90IG1hZGUgYXZhaWxhYmxlIG9uIHRoZSBjb21wb25lbnRcbmNvbnN0IGZpbHRlcmVkQ29tcG9uZW50TWV0aG9kczogQXJyYXk8c3RyaW5nPiA9IFtcbiAgJ3NldE9wdGlvbnMnLFxuICAnb24nLFxuICAnb2ZmJyxcbiAgJ29uT25jZScsXG4gICdhcHBlbmRUbycsXG4gICdpbnNlcnRBZnRlcicsXG4gICdpbnNlcnRCZWZvcmUnLFxuICAnaXNBdHRhY2hlZFRvJyxcbiAgJ3JlcGxhY2VFbGVtZW50JyxcbiAgJ3Jlc3RvcmVFbGVtZW50JyxcbiAgJ2Rlc3Ryb3knXG5dO1xuXG5jb25zdCBvdXRwdXRzOiBBcnJheTxzdHJpbmc+ID0gW1xuICAnb25pbml0JywgXG4gICdvbmNvbmZpcm0nLCBcbiAgJ29uY2FuY2VsJywgXG4gICdvbmNsb3NlJyxcbiAgJ29ubG9hZCcsIFxuICAnb25sb2FkZXJyb3InLCBcbiAgJ29uZGVzdHJveScsIFxuICAnb251cGRhdGUnXG5dO1xuXG5AQ29tcG9uZW50KHtcbiAgc2VsZWN0b3I6ICdsaWItZG9rYScsXG4gIHRlbXBsYXRlOiBgXG4gICAgPGRpdj5cbiAgICAgIDxuZy1jb250ZW50PjwvbmctY29udGVudD5cbiAgICA8L2Rpdj5cbiAgYCxcbiAgc3R5bGVzOiBbYFxuICAgIDpob3N0IHtcbiAgICAgIGRpc3BsYXk6IGJsb2NrO1xuICAgIH1cbiAgYF1cbn0pXG5cbmV4cG9ydCBjbGFzcyBBbmd1bGFyRG9rYUNvbXBvbmVudCBpbXBsZW1lbnRzIE9uSW5pdCB7XG5cbiAgcHJpdmF0ZSByb290OiBFbGVtZW50UmVmO1xuICBwcml2YXRlIHpvbmU6IE5nWm9uZTtcbiAgcHJpdmF0ZSBkb2thOiBJRG9rYUluc3RhbmNlO1xuICBwcml2YXRlIGhhbmRsZUV2ZW50OiBFdmVudEhhbmRsZXJOb25OdWxsID0gKGU6Q3VzdG9tRXZlbnQpID0+IHtcbiAgICBjb25zdCBvdXRwdXQgPSB0aGlzW2BvbiR7ZS50eXBlLnNwbGl0KCc6JylbMV19YF07XG4gICAgY29uc3QgZXZlbnQgPSB7Li4uZS5kZXRhaWx9O1xuICAgIGRlbGV0ZSBldmVudC5kb2thO1xuICAgIG91dHB1dC5lbWl0KGV2ZW50KTtcbiAgfTtcblxuICBASW5wdXQoKSBzcmM6IHN0cmluZ3xGaWxlfEJsb2IgPSBudWxsO1xuICBASW5wdXQoKSBvcHRpb25zOiBJRG9rYU9wdGlvbnMgPSB7fTtcblxuICBAT3V0cHV0KCkgb25pbml0OiBFdmVudEVtaXR0ZXI8YW55PiA9IG5ldyBFdmVudEVtaXR0ZXIoKTtcbiAgQE91dHB1dCgpIG9uY29uZmlybTogRXZlbnRFbWl0dGVyPGFueT4gPSBuZXcgRXZlbnRFbWl0dGVyKCk7XG4gIEBPdXRwdXQoKSBvbmNhbmNlbDogRXZlbnRFbWl0dGVyPGFueT4gPSBuZXcgRXZlbnRFbWl0dGVyKCk7XG4gIEBPdXRwdXQoKSBvbmNsb3NlOiBFdmVudEVtaXR0ZXI8YW55PiA9IG5ldyBFdmVudEVtaXR0ZXIoKTtcbiAgQE91dHB1dCgpIG9ubG9hZDogRXZlbnRFbWl0dGVyPGFueT4gPSBuZXcgRXZlbnRFbWl0dGVyKCk7XG4gIEBPdXRwdXQoKSBvbmxvYWRlcnJvcjogRXZlbnRFbWl0dGVyPGFueT4gPSBuZXcgRXZlbnRFbWl0dGVyKCk7XG4gIEBPdXRwdXQoKSBvbmRlc3Ryb3k6IEV2ZW50RW1pdHRlcjxhbnk+ID0gbmV3IEV2ZW50RW1pdHRlcigpO1xuICBAT3V0cHV0KCkgb251cGRhdGU6IEV2ZW50RW1pdHRlcjxhbnk+ID0gbmV3IEV2ZW50RW1pdHRlcigpO1xuXG4gIGNvbnN0cnVjdG9yKHJvb3Q6IEVsZW1lbnRSZWYsIHpvbmU6IE5nWm9uZSkge1xuICAgIHRoaXMucm9vdCA9IHJvb3Q7XG4gICAgdGhpcy56b25lID0gem9uZTtcbiAgfVxuXG4gIG5nT25Jbml0KCkge31cblxuICBuZ0FmdGVyVmlld0luaXQoKSB7XG5cbiAgICAvLyBubyBzdWZmaWNpZW50IGZlYXR1cmVzIHN1cHBvcnRlZCBpbiB0aGlzIGJyb3dzZXJcbiAgICBpZiAoIWlzU3VwcG9ydGVkKSByZXR1cm47XG5cbiAgICAvLyB3aWxsIGJsb2NrIGFuZ3VsYXIgZnJvbSBsaXN0ZW5pbmcgdG8gZXZlbnRzIGluc2lkZSBkb2thXG4gICAgdGhpcy56b25lLnJ1bk91dHNpZGVBbmd1bGFyKCgpID0+IHtcblxuICAgICAgLy8gZ2V0IGhvc3QgY2hpbGQgPGRpdj5cbiAgICAgIGNvbnN0IGlubmVyID0gdGhpcy5yb290Lm5hdGl2ZUVsZW1lbnQuZmlyc3RDaGlsZDtcblxuICAgICAgLy8gaWYgaW1hZ2Ugb3IgY2FudmFzIHN1cHBsaWVkXG4gICAgICBjb25zdCBzcmMgPSBpbm5lci5xdWVyeVNlbGVjdG9yKCdpbWcnKSB8fCBpbm5lci5xdWVyeVNlbGVjdG9yKCdjYW52YXMnKSB8fCB0aGlzLnNyYztcbiAgICAgIFxuICAgICAgLy8gY3JlYXRlIGluc3RhbmNlXG4gICAgICB0aGlzLmRva2EgPSBjcmVhdGUoaW5uZXIsIHtcbiAgICAgICAgLy8gc291cmNlIGZyb20gc2xvdFxuICAgICAgICBzcmMsXG5cbiAgICAgICAgLy8gb3VyIG9wdGlvbnNcbiAgICAgICAgLi4udGhpcy5vcHRpb25zXG4gICAgICB9KTtcblxuICAgIH0pO1xuXG4gICAgLy8gcm91dGUgZXZlbnRzXG4gICAgY29uc3QgZG9rYVJvb3Q6SFRNTEVsZW1lbnQgPSB0aGlzLmRva2EuZWxlbWVudDtcbiAgICBvdXRwdXRzLmZvckVhY2goZXZlbnQgPT4gZG9rYVJvb3QuYWRkRXZlbnRMaXN0ZW5lcihgRG9rYToke2V2ZW50LnN1YnN0cigyKX1gLCB0aGlzLmhhbmRsZUV2ZW50KSk7XG5cbiAgICAvLyBDb3B5IGluc3RhbmNlIG1ldGhvZCByZWZlcmVuY2VzIHRvIGNvbXBvbmVudCBpbnN0YW5jZVxuICAgIE9iamVjdC5rZXlzKHRoaXMuZG9rYSlcblxuICAgICAgLy8gcmVtb3ZlIHVud2FudGVkIG1ldGhvZHNcbiAgICAgIC5maWx0ZXIoa2V5ID0+IGZpbHRlcmVkQ29tcG9uZW50TWV0aG9kcy5pbmRleE9mKGtleSkgPT09IC0xKVxuICAgICAgXG4gICAgICAvLyBzZXQgbWV0aG9kIHJlZmVyZW5jZXMgZnJvbSB0aGUgY29tcG9uZW50IGluc3RhbmNlIHRvIHRoZSBkb2thIGluc3RhbmNlXG4gICAgICAuZm9yRWFjaChrZXkgPT4gdGhpc1trZXldID0gdGhpcy5kb2thW2tleV0pO1xuICB9XG5cbiAgbmdPbkNoYW5nZXMoY2hhbmdlczogU2ltcGxlQ2hhbmdlcykge1xuICAgIC8vIG5vIG5lZWQgdG8gaGFuZGxlIGZpcnN0IGNoYW5nZVxuICAgIGlmIChjaGFuZ2VzLmZpcnN0Q2hhbmdlKSByZXR1cm47XG5cbiAgICAvLyBubyBkb2thIGluc3RhbmNlIGF2YWlsYWJsZVxuICAgIGlmICghdGhpcy5kb2thKSByZXR1cm47XG5cbiAgICAvLyB1c2UgbmV3IG9wdGlvbnMgb2JqZWN0IGFzIGJhc2UgKCBvciBpZiBub3QgYXZhaWxhYmxlLCB1c2UgY3VycmVudCBvcHRpb25zIClcbiAgICBjb25zdCBvcHRpb25zID0gY2hhbmdlcy5vcHRpb25zID8gY2hhbmdlcy5vcHRpb25zLmN1cnJlbnRWYWx1ZSA6IHRoaXMub3B0aW9ucztcbiAgICBcbiAgICAvLyB1cGRhdGUgc291cmNlXG4gICAgaWYgKGNoYW5nZXMuc3JjKSBvcHRpb25zLnNyYyA9IGNoYW5nZXMuc3JjLmN1cnJlbnRWYWx1ZTtcblxuICAgIC8vIHNldCBuZXcgb3B0aW9uc1xuICAgIHRoaXMuZG9rYS5zZXRPcHRpb25zKG9wdGlvbnMpO1xuICB9XG5cbiAgbmdPbkRlc3Ryb3koKSB7XG4gICAgLy8gbm8gZG9rYSBpbnN0YW5jZSBhdmFpbGFibGVcbiAgICBpZiAoIXRoaXMuZG9rYSkgcmV0dXJuO1xuXG4gICAgLy8gZGV0YWNoIGV2ZW50c1xuICAgIGNvbnN0IGRva2FSb290OkhUTUxFbGVtZW50ID0gdGhpcy5kb2thLmVsZW1lbnQ7XG4gICAgb3V0cHV0cy5mb3JFYWNoKGV2ZW50ID0+IGRva2FSb290LnJlbW92ZUV2ZW50TGlzdGVuZXIoYERva2E6JHtldmVudC5zdWJzdHIoMil9YCwgdGhpcy5oYW5kbGVFdmVudCkpO1xuXG4gICAgLy8gd2UgZG9uZSFcbiAgICB0aGlzLmRva2EuZGVzdHJveSgpO1xuICAgIHRoaXMuZG9rYSA9IG51bGw7XG4gIH1cblxufVxuIl19