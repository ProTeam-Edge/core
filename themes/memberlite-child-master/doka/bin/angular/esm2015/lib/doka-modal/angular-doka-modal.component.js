/**
 * @fileoverview added by tsickle
 * @suppress {checkTypes,constantProperty,extraRequire,missingOverride,missingReturn,unusedPrivateMembers,uselessCode} checked by tsc
 */
import { Component, ElementRef, EventEmitter, NgZone, Input, Output } from '@angular/core';
import { create, supported } from '../lib';
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
export class AngularDokaModalComponent {
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
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiYW5ndWxhci1kb2thLW1vZGFsLmNvbXBvbmVudC5qcyIsInNvdXJjZVJvb3QiOiJuZzovL2FuZ3VsYXItZG9rYS8iLCJzb3VyY2VzIjpbImxpYi9kb2thLW1vZGFsL2FuZ3VsYXItZG9rYS1tb2RhbC5jb21wb25lbnQudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6Ijs7OztBQUFBLE9BQU8sRUFBRSxTQUFTLEVBQUUsVUFBVSxFQUFFLFlBQVksRUFBRSxNQUFNLEVBQVUsS0FBSyxFQUFFLE1BQU0sRUFBaUIsTUFBTSxlQUFlLENBQUM7QUFDbEgsT0FBTyxFQUFFLE1BQU0sRUFBRSxTQUFTLEVBQUUsTUFBTSxRQUFRLENBQUM7OztNQUlyQyxXQUFXLEdBQUcsU0FBUyxFQUFFOzs7TUFHekIsd0JBQXdCLEdBQWtCO0lBQzlDLFlBQVk7SUFDWixJQUFJO0lBQ0osS0FBSztJQUNMLFFBQVE7SUFDUixVQUFVO0lBQ1YsYUFBYTtJQUNiLGNBQWM7SUFDZCxjQUFjO0lBQ2QsZ0JBQWdCO0lBQ2hCLGdCQUFnQjtJQUNoQixTQUFTO0NBQ1Y7O01BRUssT0FBTyxHQUFrQjtJQUM3QixRQUFRO0lBQ1IsV0FBVztJQUNYLFVBQVU7SUFDVixTQUFTO0lBQ1QsUUFBUTtJQUNSLGFBQWE7SUFDYixXQUFXO0lBQ1gsVUFBVTtDQUNYO0FBY0QsTUFBTSxPQUFPLHlCQUF5Qjs7Ozs7SUF3QnBDLFlBQVksSUFBZ0IsRUFBRSxJQUFZO1FBbkJsQyxnQkFBVzs7OztRQUF3QixDQUFDLENBQWEsRUFBRSxFQUFFOztrQkFDckQsTUFBTSxHQUFHLElBQUksQ0FBQyxLQUFLLENBQUMsQ0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUMsQ0FBQyxFQUFFLENBQUM7O2tCQUMxQyxLQUFLLHFCQUFPLENBQUMsQ0FBQyxNQUFNLENBQUM7WUFDM0IsT0FBTyxLQUFLLENBQUMsSUFBSSxDQUFDO1lBQ2xCLE1BQU0sQ0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUM7UUFD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Ozs7Y0FHakIsUUFBUSxHQUFlLElBQUksQ0FBQyxJQUFJLENBQUMsT0FBTztRQUM5QyxPQUFPLENBQUMsT0FBTzs7OztRQUFDLEtBQUssQ0FBQyxFQUFFLENBQUMsUUFBUSxDQUFDLG1CQUFtQixDQUFDLFFBQVEsS0FBSyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsRUFBRSxFQUFFLElBQUksQ0FBQyxXQUFXLENBQUMsRUFBQyxDQUFDO1FBRXBHLFdBQVc7UUFDWCxJQUFJLENBQUMsSUFBSSxDQUFDLE9BQU8sRUFBRSxDQUFDO1FBQ3BCLElBQUksQ0FBQyxJQUFJLEdBQUcsSUFBSSxDQUFDO0lBQ25CLENBQUM7OztZQTlHRixTQUFTLFNBQUM7Z0JBQ1QsUUFBUSxFQUFFLGdCQUFnQjtnQkFDMUIsUUFBUSxFQUFFOztHQUVUO3lCQUNROzs7O0dBSVI7YUFDRjs7OztZQTNDbUIsVUFBVTtZQUFnQixNQUFNOzs7a0JBeURqRCxLQUFLO3NCQUNMLEtBQUs7cUJBRUwsTUFBTTt3QkFDTixNQUFNO3VCQUNOLE1BQU07c0JBQ04sTUFBTTtxQkFDTixNQUFNOzBCQUNOLE1BQU07d0JBQ04sTUFBTTt1QkFDTixNQUFNOzs7Ozs7O0lBcEJQLHlDQUF5Qjs7Ozs7SUFDekIseUNBQXFCOzs7OztJQUNyQix5Q0FBNEI7Ozs7O0lBQzVCLGdEQUtFOztJQUVGLHdDQUFzQzs7SUFDdEMsNENBQW9DOztJQUVwQywyQ0FBeUQ7O0lBQ3pELDhDQUE0RDs7SUFDNUQsNkNBQTJEOztJQUMzRCw0Q0FBMEQ7O0lBQzFELDJDQUF5RDs7SUFDekQsZ0RBQThEOztJQUM5RCw4Q0FBNEQ7O0lBQzVELDZDQUEyRCIsInNvdXJjZXNDb250ZW50IjpbImltcG9ydCB7IENvbXBvbmVudCwgRWxlbWVudFJlZiwgRXZlbnRFbWl0dGVyLCBOZ1pvbmUsIE9uSW5pdCwgSW5wdXQsIE91dHB1dCwgU2ltcGxlQ2hhbmdlcyB9IGZyb20gJ0Bhbmd1bGFyL2NvcmUnO1xuaW1wb3J0IHsgY3JlYXRlLCBzdXBwb3J0ZWQgfSBmcm9tICcuLi9saWInO1xuaW1wb3J0IHsgSURva2FJbnN0YW5jZSwgSURva2FPcHRpb25zIH0gZnJvbSAnLi4vbGliL2Rva2EnO1xuXG4vLyBXZSB0ZXN0IGlmIERva2EgaXMgc3VwcG9ydGVkIG9uIHRoZSBjdXJyZW50IGNsaWVudFxuY29uc3QgaXNTdXBwb3J0ZWQgPSBzdXBwb3J0ZWQoKTtcblxuLy8gTWV0aG9kcyBub3QgbWFkZSBhdmFpbGFibGUgb24gdGhlIGNvbXBvbmVudFxuY29uc3QgZmlsdGVyZWRDb21wb25lbnRNZXRob2RzOiBBcnJheTxzdHJpbmc+ID0gW1xuICAnc2V0T3B0aW9ucycsXG4gICdvbicsXG4gICdvZmYnLFxuICAnb25PbmNlJyxcbiAgJ2FwcGVuZFRvJyxcbiAgJ2luc2VydEFmdGVyJyxcbiAgJ2luc2VydEJlZm9yZScsXG4gICdpc0F0dGFjaGVkVG8nLFxuICAncmVwbGFjZUVsZW1lbnQnLFxuICAncmVzdG9yZUVsZW1lbnQnLFxuICAnZGVzdHJveSdcbl07XG5cbmNvbnN0IG91dHB1dHM6IEFycmF5PHN0cmluZz4gPSBbXG4gICdvbmluaXQnLCBcbiAgJ29uY29uZmlybScsIFxuICAnb25jYW5jZWwnLCBcbiAgJ29uY2xvc2UnLFxuICAnb25sb2FkJywgXG4gICdvbmxvYWRlcnJvcicsIFxuICAnb25kZXN0cm95JywgXG4gICdvbnVwZGF0ZSdcbl07XG5cbkBDb21wb25lbnQoe1xuICBzZWxlY3RvcjogJ2xpYi1kb2thLW1vZGFsJyxcbiAgdGVtcGxhdGU6IGBcbiAgICA8bmctY29udGVudD48L25nLWNvbnRlbnQ+XG4gIGAsXG4gIHN0eWxlczogW2BcbiAgICA6aG9zdCB7XG4gICAgICBkaXNwbGF5OiBibG9jaztcbiAgICB9XG4gIGBdXG59KVxuXG5leHBvcnQgY2xhc3MgQW5ndWxhckRva2FNb2RhbENvbXBvbmVudCBpbXBsZW1lbnRzIE9uSW5pdCB7XG5cbiAgcHJpdmF0ZSByb290OiBFbGVtZW50UmVmO1xuICBwcml2YXRlIHpvbmU6IE5nWm9uZTtcbiAgcHJpdmF0ZSBkb2thOiBJRG9rYUluc3RhbmNlO1xuICBwcml2YXRlIGhhbmRsZUV2ZW50OiBFdmVudEhhbmRsZXJOb25OdWxsID0gKGU6Q3VzdG9tRXZlbnQpID0+IHtcbiAgICBjb25zdCBvdXRwdXQgPSB0aGlzW2BvbiR7ZS50eXBlLnNwbGl0KCc6JylbMV19YF07XG4gICAgY29uc3QgZXZlbnQgPSB7Li4uZS5kZXRhaWx9O1xuICAgIGRlbGV0ZSBldmVudC5kb2thO1xuICAgIG91dHB1dC5lbWl0KGV2ZW50KTtcbiAgfTtcblxuICBASW5wdXQoKSBzcmM6IHN0cmluZ3xGaWxlfEJsb2IgPSBudWxsO1xuICBASW5wdXQoKSBvcHRpb25zOiBJRG9rYU9wdGlvbnMgPSB7fTtcblxuICBAT3V0cHV0KCkgb25pbml0OiBFdmVudEVtaXR0ZXI8YW55PiA9IG5ldyBFdmVudEVtaXR0ZXIoKTtcbiAgQE91dHB1dCgpIG9uY29uZmlybTogRXZlbnRFbWl0dGVyPGFueT4gPSBuZXcgRXZlbnRFbWl0dGVyKCk7XG4gIEBPdXRwdXQoKSBvbmNhbmNlbDogRXZlbnRFbWl0dGVyPGFueT4gPSBuZXcgRXZlbnRFbWl0dGVyKCk7XG4gIEBPdXRwdXQoKSBvbmNsb3NlOiBFdmVudEVtaXR0ZXI8YW55PiA9IG5ldyBFdmVudEVtaXR0ZXIoKTtcbiAgQE91dHB1dCgpIG9ubG9hZDogRXZlbnRFbWl0dGVyPGFueT4gPSBuZXcgRXZlbnRFbWl0dGVyKCk7XG4gIEBPdXRwdXQoKSBvbmxvYWRlcnJvcjogRXZlbnRFbWl0dGVyPGFueT4gPSBuZXcgRXZlbnRFbWl0dGVyKCk7XG4gIEBPdXRwdXQoKSBvbmRlc3Ryb3k6IEV2ZW50RW1pdHRlcjxhbnk+ID0gbmV3IEV2ZW50RW1pdHRlcigpO1xuICBAT3V0cHV0KCkgb251cGRhdGU6IEV2ZW50RW1pdHRlcjxhbnk+ID0gbmV3IEV2ZW50RW1pdHRlcigpO1xuXG4gIGNvbnN0cnVjdG9yKHJvb3Q6IEVsZW1lbnRSZWYsIHpvbmU6IE5nWm9uZSkge1xuICAgIHRoaXMucm9vdCA9IHJvb3Q7XG4gICAgdGhpcy56b25lID0gem9uZTtcbiAgfVxuICBcbiAgbmdPbkluaXQoKSB7fVxuXG4gIG5nQWZ0ZXJWaWV3SW5pdCgpIHtcblxuICAgIC8vIG5vIHN1ZmZpY2llbnQgZmVhdHVyZXMgc3VwcG9ydGVkIGluIHRoaXMgYnJvd3NlclxuICAgIGlmICghaXNTdXBwb3J0ZWQpIHJldHVybjtcblxuICAgIC8vIHdpbGwgYmxvY2sgYW5ndWxhciBmcm9tIGxpc3RlbmluZyB0byBldmVudHMgaW5zaWRlIGRva2FcbiAgICB0aGlzLnpvbmUucnVuT3V0c2lkZUFuZ3VsYXIoKCkgPT4ge1xuXG4gICAgICAvLyBnZXQgaG9zdCBjaGlsZCA8ZGl2PlxuICAgICAgY29uc3QgaW5uZXIgPSB0aGlzLnJvb3QubmF0aXZlRWxlbWVudDtcblxuICAgICAgLy8gaWYgaW1hZ2Ugb3IgY2FudmFzIHN1cHBsaWVkXG4gICAgICBjb25zdCBzcmMgPSBpbm5lci5xdWVyeVNlbGVjdG9yKCdpbWcnKSB8fCBpbm5lci5xdWVyeVNlbGVjdG9yKCdjYW52YXMnKSB8fCB0aGlzLnNyYztcbiAgICAgIFxuICAgICAgLy8gY3JlYXRlIGluc3RhbmNlXG4gICAgICB0aGlzLmRva2EgPSBjcmVhdGUoe1xuICAgICAgICAvLyBzb3VyY2UgZnJvbSBzbG90XG4gICAgICAgIHNyYyxcblxuICAgICAgICAvLyBvdXIgb3B0aW9uc1xuICAgICAgICAuLi50aGlzLm9wdGlvbnNcbiAgICAgIH0pO1xuXG4gICAgfSk7XG5cbiAgICAvLyByb3V0ZSBldmVudHNcbiAgICBjb25zdCBkb2thUm9vdDpIVE1MRWxlbWVudCA9IHRoaXMuZG9rYS5lbGVtZW50O1xuICAgIG91dHB1dHMuZm9yRWFjaChldmVudCA9PiBkb2thUm9vdC5hZGRFdmVudExpc3RlbmVyKGBEb2thOiR7ZXZlbnQuc3Vic3RyKDIpfWAsIHRoaXMuaGFuZGxlRXZlbnQpKTtcblxuICAgIC8vIENvcHkgaW5zdGFuY2UgbWV0aG9kIHJlZmVyZW5jZXMgdG8gY29tcG9uZW50IGluc3RhbmNlXG4gICAgT2JqZWN0LmtleXModGhpcy5kb2thKVxuXG4gICAgICAvLyByZW1vdmUgdW53YW50ZWQgbWV0aG9kc1xuICAgICAgLmZpbHRlcihrZXkgPT4gZmlsdGVyZWRDb21wb25lbnRNZXRob2RzLmluZGV4T2Yoa2V5KSA9PT0gLTEpXG4gICAgICBcbiAgICAgIC8vIHNldCBtZXRob2QgcmVmZXJlbmNlcyBmcm9tIHRoZSBjb21wb25lbnQgaW5zdGFuY2UgdG8gdGhlIGRva2EgaW5zdGFuY2VcbiAgICAgIC5mb3JFYWNoKGtleSA9PiB0aGlzW2tleV0gPSB0aGlzLmRva2Fba2V5XSk7XG4gIH1cblxuICBuZ09uQ2hhbmdlcyhjaGFuZ2VzOiBTaW1wbGVDaGFuZ2VzKSB7XG4gICAgLy8gbm8gbmVlZCB0byBoYW5kbGUgZmlyc3QgY2hhbmdlXG4gICAgaWYgKGNoYW5nZXMuZmlyc3RDaGFuZ2UpIHJldHVybjtcblxuICAgIC8vIG5vIGRva2EgaW5zdGFuY2UgYXZhaWxhYmxlXG4gICAgaWYgKCF0aGlzLmRva2EpIHJldHVybjtcblxuICAgIC8vIHVzZSBuZXcgb3B0aW9ucyBvYmplY3QgYXMgYmFzZSAoIG9yIGlmIG5vdCBhdmFpbGFibGUsIHVzZSBjdXJyZW50IG9wdGlvbnMgKVxuICAgIGNvbnN0IG9wdGlvbnMgPSBjaGFuZ2VzLm9wdGlvbnMgPyBjaGFuZ2VzLm9wdGlvbnMuY3VycmVudFZhbHVlIDogdGhpcy5vcHRpb25zO1xuICAgIFxuICAgIC8vIHVwZGF0ZSBzb3VyY2VcbiAgICBpZiAoY2hhbmdlcy5zcmMpIG9wdGlvbnMuc3JjID0gY2hhbmdlcy5zcmMuY3VycmVudFZhbHVlO1xuXG4gICAgLy8gc2V0IG5ldyBvcHRpb25zXG4gICAgdGhpcy5kb2thLnNldE9wdGlvbnMob3B0aW9ucyk7XG4gIH1cblxuICBuZ09uRGVzdHJveSgpIHtcbiAgICAvLyBubyBkb2thIGluc3RhbmNlIGF2YWlsYWJsZVxuICAgIGlmICghdGhpcy5kb2thKSByZXR1cm47XG5cbiAgICAvLyBkZXRhY2ggZXZlbnRzXG4gICAgY29uc3QgZG9rYVJvb3Q6SFRNTEVsZW1lbnQgPSB0aGlzLmRva2EuZWxlbWVudDtcbiAgICBvdXRwdXRzLmZvckVhY2goZXZlbnQgPT4gZG9rYVJvb3QucmVtb3ZlRXZlbnRMaXN0ZW5lcihgRG9rYToke2V2ZW50LnN1YnN0cigyKX1gLCB0aGlzLmhhbmRsZUV2ZW50KSk7XG5cbiAgICAvLyB3ZSBkb25lIVxuICAgIHRoaXMuZG9rYS5kZXN0cm95KCk7XG4gICAgdGhpcy5kb2thID0gbnVsbDtcbiAgfVxuXG59Il19