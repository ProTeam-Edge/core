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
export class AngularDokaComponent {
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
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiYW5ndWxhci1kb2thLmNvbXBvbmVudC5qcyIsInNvdXJjZVJvb3QiOiJuZzovL2FuZ3VsYXItZG9rYS8iLCJzb3VyY2VzIjpbImxpYi9kb2thL2FuZ3VsYXItZG9rYS5jb21wb25lbnQudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6Ijs7OztBQUFBLE9BQU8sRUFBRSxTQUFTLEVBQUUsVUFBVSxFQUFFLFlBQVksRUFBRSxNQUFNLEVBQVUsS0FBSyxFQUFFLE1BQU0sRUFBaUIsTUFBTSxlQUFlLENBQUM7QUFDbEgsT0FBTyxFQUFFLE1BQU0sRUFBRSxTQUFTLEVBQUUsTUFBTSxRQUFRLENBQUM7OztNQUlyQyxXQUFXLEdBQUcsU0FBUyxFQUFFOzs7TUFHekIsd0JBQXdCLEdBQWtCO0lBQzlDLFlBQVk7SUFDWixJQUFJO0lBQ0osS0FBSztJQUNMLFFBQVE7SUFDUixVQUFVO0lBQ1YsYUFBYTtJQUNiLGNBQWM7SUFDZCxjQUFjO0lBQ2QsZ0JBQWdCO0lBQ2hCLGdCQUFnQjtJQUNoQixTQUFTO0NBQ1Y7O01BRUssT0FBTyxHQUFrQjtJQUM3QixRQUFRO0lBQ1IsV0FBVztJQUNYLFVBQVU7SUFDVixTQUFTO0lBQ1QsUUFBUTtJQUNSLGFBQWE7SUFDYixXQUFXO0lBQ1gsVUFBVTtDQUNYO0FBZ0JELE1BQU0sT0FBTyxvQkFBb0I7Ozs7O0lBd0IvQixZQUFZLElBQWdCLEVBQUUsSUFBWTtRQW5CbEMsZ0JBQVc7Ozs7UUFBd0IsQ0FBQyxDQUFhLEVBQUUsRUFBRTs7a0JBQ3JELE1BQU0sR0FBRyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDOztrQkFDMUMsS0FBSyxxQkFBTyxDQUFDLENBQUMsTUFBTSxDQUFDO1lBQzNCLE9BQU8sS0FBSyxDQUFDLElBQUksQ0FBQztZQUNsQixNQUFNLENBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFDO1FBQ3JCLENBQUMsRUFBQztRQUVPLFFBQUcsR0FBcUIsSUFBSSxDQUFDO1FBQzdCLFlBQU8sR0FBaUIsRUFBRSxDQUFDO1FBRTFCLFdBQU0sR0FBc0IsSUFBSSxZQUFZLEVBQUUsQ0FBQztRQUMvQyxjQUFTLEdBQXNCLElBQUksWUFBWSxFQUFFLENBQUM7UUFDbEQsYUFBUSxHQUFzQixJQUFJLFlBQVksRUFBRSxDQUFDO1FBQ2pELFlBQU8sR0FBc0IsSUFBSSxZQUFZLEVBQUUsQ0FBQztRQUNoRCxXQUFNLEdBQXNCLElBQUksWUFBWSxFQUFFLENBQUM7UUFDL0MsZ0JBQVcsR0FBc0IsSUFBSSxZQUFZLEVBQUUsQ0FBQztRQUNwRCxjQUFTLEdBQXNCLElBQUksWUFBWSxFQUFFLENBQUM7UUFDbEQsYUFBUSxHQUFzQixJQUFJLFlBQVksRUFBRSxDQUFDO1FBR3pELElBQUksQ0FBQyxJQUFJLEdBQUcsSUFBSSxDQUFDO1FBQ2pCLElBQUksQ0FBQyxJQUFJLEdBQUcsSUFBSSxDQUFDO0lBQ25CLENBQUM7Ozs7SUFFRCxRQUFRLEtBQUksQ0FBQzs7OztJQUViLGVBQWU7UUFFYixtREFBbUQ7UUFDbkQsSUFBSSxDQUFDLFdBQVc7WUFBRSxPQUFPO1FBRXpCLDBEQUEwRDtRQUMxRCxJQUFJLENBQUMsSUFBSSxDQUFDLGlCQUFpQjs7O1FBQUMsR0FBRyxFQUFFOzs7a0JBR3pCLEtBQUssR0FBRyxJQUFJLENBQUMsSUFBSSxDQUFDLGFBQWEsQ0FBQyxVQUFVOzs7a0JBRzFDLEdBQUcsR0FBRyxLQUFLLENBQUMsYUFBYSxDQUFDLEtBQUssQ0FBQyxJQUFJLEtBQUssQ0FBQyxhQUFhLENBQUMsUUFBUSxDQUFDLElBQUksSUFBSSxDQUFDLEdBQUc7WUFFbkYsa0JBQWtCO1lBQ2xCLElBQUksQ0FBQyxJQUFJLEdBQUcsTUFBTSxDQUFDLEtBQUs7Z0JBQ3RCLG1CQUFtQjtnQkFDbkIsR0FBRyxJQUdBLElBQUksQ0FBQyxPQUFPLEVBQ2YsQ0FBQztRQUVMLENBQUMsRUFBQyxDQUFDOzs7Y0FHRyxRQUFRLEdBQWUsSUFBSSxDQUFDLElBQUksQ0FBQyxPQUFPO1FBQzlDLE9BQU8sQ0FBQyxPQUFPOzs7O1FBQUMsS0FBSyxDQUFDLEVBQUUsQ0FBQyxRQUFRLENBQUMsZ0JBQWdCLENBQUMsUUFBUSxLQUFLLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFFLEVBQUUsSUFBSSxDQUFDLFdBQVcsQ0FBQyxFQUFDLENBQUM7UUFFakcsd0RBQXdEO1FBQ3hELE1BQU0sQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQztZQUVwQiwwQkFBMEI7YUFDekIsTUFBTTs7OztRQUFDLEdBQUcsQ0FBQyxFQUFFLENBQUMsd0JBQXdCLENBQUMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFDO1lBRTVELHlFQUF5RTthQUN4RSxPQUFPOzs7O1FBQUMsR0FBRyxDQUFDLEVBQUUsQ0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLEdBQUcsSUFBSSxDQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsRUFBQyxDQUFDO0lBQ2hELENBQUM7Ozs7O0lBRUQsV0FBVyxDQUFDLE9BQXNCO1FBQ2hDLGlDQUFpQztRQUNqQyxJQUFJLE9BQU8sQ0FBQyxXQUFXO1lBQUUsT0FBTztRQUVoQyw2QkFBNkI7UUFDN0IsSUFBSSxDQUFDLElBQUksQ0FBQyxJQUFJO1lBQUUsT0FBTzs7O2NBR2pCLE9BQU8sR0FBRyxPQUFPLENBQUMsT0FBTyxDQUFDLENBQUMsQ0FBQyxPQUFPLENBQUMsT0FBTyxDQUFDLFlBQVksQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLE9BQU87UUFFN0UsZ0JBQWdCO1FBQ2hCLElBQUksT0FBTyxDQUFDLEdBQUc7WUFBRSxPQUFPLENBQUMsR0FBRyxHQUFHLE9BQU8sQ0FBQyxHQUFHLENBQUMsWUFBWSxDQUFDO1FBRXhELGtCQUFrQjtRQUNsQixJQUFJLENBQUMsSUFBSSxDQUFDLFVBQVUsQ0FBQyxPQUFPLENBQUMsQ0FBQztJQUNoQyxDQUFDOzs7O0lBRUQsV0FBVztRQUNULDZCQUE2QjtRQUM3QixJQUFJLENBQUMsSUFBSSxDQUFDLElBQUk7WUFBRSxPQUFPOzs7Y0FHakIsUUFBUSxHQUFlLElBQUksQ0FBQyxJQUFJLENBQUMsT0FBTztRQUM5QyxPQUFPLENBQUMsT0FBTzs7OztRQUFDLEtBQUssQ0FBQyxFQUFFLENBQUMsUUFBUSxDQUFDLG1CQUFtQixDQUFDLFFBQVEsS0FBSyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsRUFBRSxFQUFFLElBQUksQ0FBQyxXQUFXLENBQUMsRUFBQyxDQUFDO1FBRXBHLFdBQVc7UUFDWCxJQUFJLENBQUMsSUFBSSxDQUFDLE9BQU8sRUFBRSxDQUFDO1FBQ3BCLElBQUksQ0FBQyxJQUFJLEdBQUcsSUFBSSxDQUFDO0lBQ25CLENBQUM7OztZQWhIRixTQUFTLFNBQUM7Z0JBQ1QsUUFBUSxFQUFFLFVBQVU7Z0JBQ3BCLFFBQVEsRUFBRTs7OztHQUlUO3lCQUNROzs7O0dBSVI7YUFDRjs7OztZQTdDbUIsVUFBVTtZQUFnQixNQUFNOzs7a0JBMkRqRCxLQUFLO3NCQUNMLEtBQUs7cUJBRUwsTUFBTTt3QkFDTixNQUFNO3VCQUNOLE1BQU07c0JBQ04sTUFBTTtxQkFDTixNQUFNOzBCQUNOLE1BQU07d0JBQ04sTUFBTTt1QkFDTixNQUFNOzs7Ozs7O0lBcEJQLG9DQUF5Qjs7Ozs7SUFDekIsb0NBQXFCOzs7OztJQUNyQixvQ0FBNEI7Ozs7O0lBQzVCLDJDQUtFOztJQUVGLG1DQUFzQzs7SUFDdEMsdUNBQW9DOztJQUVwQyxzQ0FBeUQ7O0lBQ3pELHlDQUE0RDs7SUFDNUQsd0NBQTJEOztJQUMzRCx1Q0FBMEQ7O0lBQzFELHNDQUF5RDs7SUFDekQsMkNBQThEOztJQUM5RCx5Q0FBNEQ7O0lBQzVELHdDQUEyRCIsInNvdXJjZXNDb250ZW50IjpbImltcG9ydCB7IENvbXBvbmVudCwgRWxlbWVudFJlZiwgRXZlbnRFbWl0dGVyLCBOZ1pvbmUsIE9uSW5pdCwgSW5wdXQsIE91dHB1dCwgU2ltcGxlQ2hhbmdlcyB9IGZyb20gJ0Bhbmd1bGFyL2NvcmUnO1xuaW1wb3J0IHsgY3JlYXRlLCBzdXBwb3J0ZWQgfSBmcm9tICcuLi9saWInO1xuaW1wb3J0IHsgSURva2FJbnN0YW5jZSwgSURva2FPcHRpb25zIH0gZnJvbSAnLi4vbGliL2Rva2EnO1xuXG4vLyBXZSB0ZXN0IGlmIERva2EgaXMgc3VwcG9ydGVkIG9uIHRoZSBjdXJyZW50IGNsaWVudFxuY29uc3QgaXNTdXBwb3J0ZWQgPSBzdXBwb3J0ZWQoKTtcblxuLy8gTWV0aG9kcyBub3QgbWFkZSBhdmFpbGFibGUgb24gdGhlIGNvbXBvbmVudFxuY29uc3QgZmlsdGVyZWRDb21wb25lbnRNZXRob2RzOiBBcnJheTxzdHJpbmc+ID0gW1xuICAnc2V0T3B0aW9ucycsXG4gICdvbicsXG4gICdvZmYnLFxuICAnb25PbmNlJyxcbiAgJ2FwcGVuZFRvJyxcbiAgJ2luc2VydEFmdGVyJyxcbiAgJ2luc2VydEJlZm9yZScsXG4gICdpc0F0dGFjaGVkVG8nLFxuICAncmVwbGFjZUVsZW1lbnQnLFxuICAncmVzdG9yZUVsZW1lbnQnLFxuICAnZGVzdHJveSdcbl07XG5cbmNvbnN0IG91dHB1dHM6IEFycmF5PHN0cmluZz4gPSBbXG4gICdvbmluaXQnLCBcbiAgJ29uY29uZmlybScsIFxuICAnb25jYW5jZWwnLCBcbiAgJ29uY2xvc2UnLFxuICAnb25sb2FkJywgXG4gICdvbmxvYWRlcnJvcicsIFxuICAnb25kZXN0cm95JywgXG4gICdvbnVwZGF0ZSdcbl07XG5cbkBDb21wb25lbnQoe1xuICBzZWxlY3RvcjogJ2xpYi1kb2thJyxcbiAgdGVtcGxhdGU6IGBcbiAgICA8ZGl2PlxuICAgICAgPG5nLWNvbnRlbnQ+PC9uZy1jb250ZW50PlxuICAgIDwvZGl2PlxuICBgLFxuICBzdHlsZXM6IFtgXG4gICAgOmhvc3Qge1xuICAgICAgZGlzcGxheTogYmxvY2s7XG4gICAgfVxuICBgXVxufSlcblxuZXhwb3J0IGNsYXNzIEFuZ3VsYXJEb2thQ29tcG9uZW50IGltcGxlbWVudHMgT25Jbml0IHtcblxuICBwcml2YXRlIHJvb3Q6IEVsZW1lbnRSZWY7XG4gIHByaXZhdGUgem9uZTogTmdab25lO1xuICBwcml2YXRlIGRva2E6IElEb2thSW5zdGFuY2U7XG4gIHByaXZhdGUgaGFuZGxlRXZlbnQ6IEV2ZW50SGFuZGxlck5vbk51bGwgPSAoZTpDdXN0b21FdmVudCkgPT4ge1xuICAgIGNvbnN0IG91dHB1dCA9IHRoaXNbYG9uJHtlLnR5cGUuc3BsaXQoJzonKVsxXX1gXTtcbiAgICBjb25zdCBldmVudCA9IHsuLi5lLmRldGFpbH07XG4gICAgZGVsZXRlIGV2ZW50LmRva2E7XG4gICAgb3V0cHV0LmVtaXQoZXZlbnQpO1xuICB9O1xuXG4gIEBJbnB1dCgpIHNyYzogc3RyaW5nfEZpbGV8QmxvYiA9IG51bGw7XG4gIEBJbnB1dCgpIG9wdGlvbnM6IElEb2thT3B0aW9ucyA9IHt9O1xuXG4gIEBPdXRwdXQoKSBvbmluaXQ6IEV2ZW50RW1pdHRlcjxhbnk+ID0gbmV3IEV2ZW50RW1pdHRlcigpO1xuICBAT3V0cHV0KCkgb25jb25maXJtOiBFdmVudEVtaXR0ZXI8YW55PiA9IG5ldyBFdmVudEVtaXR0ZXIoKTtcbiAgQE91dHB1dCgpIG9uY2FuY2VsOiBFdmVudEVtaXR0ZXI8YW55PiA9IG5ldyBFdmVudEVtaXR0ZXIoKTtcbiAgQE91dHB1dCgpIG9uY2xvc2U6IEV2ZW50RW1pdHRlcjxhbnk+ID0gbmV3IEV2ZW50RW1pdHRlcigpO1xuICBAT3V0cHV0KCkgb25sb2FkOiBFdmVudEVtaXR0ZXI8YW55PiA9IG5ldyBFdmVudEVtaXR0ZXIoKTtcbiAgQE91dHB1dCgpIG9ubG9hZGVycm9yOiBFdmVudEVtaXR0ZXI8YW55PiA9IG5ldyBFdmVudEVtaXR0ZXIoKTtcbiAgQE91dHB1dCgpIG9uZGVzdHJveTogRXZlbnRFbWl0dGVyPGFueT4gPSBuZXcgRXZlbnRFbWl0dGVyKCk7XG4gIEBPdXRwdXQoKSBvbnVwZGF0ZTogRXZlbnRFbWl0dGVyPGFueT4gPSBuZXcgRXZlbnRFbWl0dGVyKCk7XG5cbiAgY29uc3RydWN0b3Iocm9vdDogRWxlbWVudFJlZiwgem9uZTogTmdab25lKSB7XG4gICAgdGhpcy5yb290ID0gcm9vdDtcbiAgICB0aGlzLnpvbmUgPSB6b25lO1xuICB9XG5cbiAgbmdPbkluaXQoKSB7fVxuXG4gIG5nQWZ0ZXJWaWV3SW5pdCgpIHtcblxuICAgIC8vIG5vIHN1ZmZpY2llbnQgZmVhdHVyZXMgc3VwcG9ydGVkIGluIHRoaXMgYnJvd3NlclxuICAgIGlmICghaXNTdXBwb3J0ZWQpIHJldHVybjtcblxuICAgIC8vIHdpbGwgYmxvY2sgYW5ndWxhciBmcm9tIGxpc3RlbmluZyB0byBldmVudHMgaW5zaWRlIGRva2FcbiAgICB0aGlzLnpvbmUucnVuT3V0c2lkZUFuZ3VsYXIoKCkgPT4ge1xuXG4gICAgICAvLyBnZXQgaG9zdCBjaGlsZCA8ZGl2PlxuICAgICAgY29uc3QgaW5uZXIgPSB0aGlzLnJvb3QubmF0aXZlRWxlbWVudC5maXJzdENoaWxkO1xuXG4gICAgICAvLyBpZiBpbWFnZSBvciBjYW52YXMgc3VwcGxpZWRcbiAgICAgIGNvbnN0IHNyYyA9IGlubmVyLnF1ZXJ5U2VsZWN0b3IoJ2ltZycpIHx8IGlubmVyLnF1ZXJ5U2VsZWN0b3IoJ2NhbnZhcycpIHx8IHRoaXMuc3JjO1xuICAgICAgXG4gICAgICAvLyBjcmVhdGUgaW5zdGFuY2VcbiAgICAgIHRoaXMuZG9rYSA9IGNyZWF0ZShpbm5lciwge1xuICAgICAgICAvLyBzb3VyY2UgZnJvbSBzbG90XG4gICAgICAgIHNyYyxcblxuICAgICAgICAvLyBvdXIgb3B0aW9uc1xuICAgICAgICAuLi50aGlzLm9wdGlvbnNcbiAgICAgIH0pO1xuXG4gICAgfSk7XG5cbiAgICAvLyByb3V0ZSBldmVudHNcbiAgICBjb25zdCBkb2thUm9vdDpIVE1MRWxlbWVudCA9IHRoaXMuZG9rYS5lbGVtZW50O1xuICAgIG91dHB1dHMuZm9yRWFjaChldmVudCA9PiBkb2thUm9vdC5hZGRFdmVudExpc3RlbmVyKGBEb2thOiR7ZXZlbnQuc3Vic3RyKDIpfWAsIHRoaXMuaGFuZGxlRXZlbnQpKTtcblxuICAgIC8vIENvcHkgaW5zdGFuY2UgbWV0aG9kIHJlZmVyZW5jZXMgdG8gY29tcG9uZW50IGluc3RhbmNlXG4gICAgT2JqZWN0LmtleXModGhpcy5kb2thKVxuXG4gICAgICAvLyByZW1vdmUgdW53YW50ZWQgbWV0aG9kc1xuICAgICAgLmZpbHRlcihrZXkgPT4gZmlsdGVyZWRDb21wb25lbnRNZXRob2RzLmluZGV4T2Yoa2V5KSA9PT0gLTEpXG4gICAgICBcbiAgICAgIC8vIHNldCBtZXRob2QgcmVmZXJlbmNlcyBmcm9tIHRoZSBjb21wb25lbnQgaW5zdGFuY2UgdG8gdGhlIGRva2EgaW5zdGFuY2VcbiAgICAgIC5mb3JFYWNoKGtleSA9PiB0aGlzW2tleV0gPSB0aGlzLmRva2Fba2V5XSk7XG4gIH1cblxuICBuZ09uQ2hhbmdlcyhjaGFuZ2VzOiBTaW1wbGVDaGFuZ2VzKSB7XG4gICAgLy8gbm8gbmVlZCB0byBoYW5kbGUgZmlyc3QgY2hhbmdlXG4gICAgaWYgKGNoYW5nZXMuZmlyc3RDaGFuZ2UpIHJldHVybjtcblxuICAgIC8vIG5vIGRva2EgaW5zdGFuY2UgYXZhaWxhYmxlXG4gICAgaWYgKCF0aGlzLmRva2EpIHJldHVybjtcblxuICAgIC8vIHVzZSBuZXcgb3B0aW9ucyBvYmplY3QgYXMgYmFzZSAoIG9yIGlmIG5vdCBhdmFpbGFibGUsIHVzZSBjdXJyZW50IG9wdGlvbnMgKVxuICAgIGNvbnN0IG9wdGlvbnMgPSBjaGFuZ2VzLm9wdGlvbnMgPyBjaGFuZ2VzLm9wdGlvbnMuY3VycmVudFZhbHVlIDogdGhpcy5vcHRpb25zO1xuICAgIFxuICAgIC8vIHVwZGF0ZSBzb3VyY2VcbiAgICBpZiAoY2hhbmdlcy5zcmMpIG9wdGlvbnMuc3JjID0gY2hhbmdlcy5zcmMuY3VycmVudFZhbHVlO1xuXG4gICAgLy8gc2V0IG5ldyBvcHRpb25zXG4gICAgdGhpcy5kb2thLnNldE9wdGlvbnMob3B0aW9ucyk7XG4gIH1cblxuICBuZ09uRGVzdHJveSgpIHtcbiAgICAvLyBubyBkb2thIGluc3RhbmNlIGF2YWlsYWJsZVxuICAgIGlmICghdGhpcy5kb2thKSByZXR1cm47XG5cbiAgICAvLyBkZXRhY2ggZXZlbnRzXG4gICAgY29uc3QgZG9rYVJvb3Q6SFRNTEVsZW1lbnQgPSB0aGlzLmRva2EuZWxlbWVudDtcbiAgICBvdXRwdXRzLmZvckVhY2goZXZlbnQgPT4gZG9rYVJvb3QucmVtb3ZlRXZlbnRMaXN0ZW5lcihgRG9rYToke2V2ZW50LnN1YnN0cigyKX1gLCB0aGlzLmhhbmRsZUV2ZW50KSk7XG5cbiAgICAvLyB3ZSBkb25lIVxuICAgIHRoaXMuZG9rYS5kZXN0cm95KCk7XG4gICAgdGhpcy5kb2thID0gbnVsbDtcbiAgfVxuXG59XG4iXX0=