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
export class AngularDokaOverlayComponent {
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
        if (!isSupported)
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
     * @return {?}
     */
    hide() {
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
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiYW5ndWxhci1kb2thLW92ZXJsYXkuY29tcG9uZW50LmpzIiwic291cmNlUm9vdCI6Im5nOi8vYW5ndWxhci1kb2thLyIsInNvdXJjZXMiOlsibGliL2Rva2Etb3ZlcmxheS9hbmd1bGFyLWRva2Etb3ZlcmxheS5jb21wb25lbnQudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6Ijs7OztBQUFBLE9BQU8sRUFBRSxTQUFTLEVBQUUsVUFBVSxFQUFFLFlBQVksRUFBRSxNQUFNLEVBQVUsS0FBSyxFQUFFLE1BQU0sRUFBaUIsTUFBTSxlQUFlLENBQUM7QUFDbEgsT0FBTyxFQUFFLE1BQU0sRUFBRSxTQUFTLEVBQUUsTUFBTSxRQUFRLENBQUM7OztNQUlyQyxXQUFXLEdBQUcsU0FBUyxFQUFFOzs7TUFHekIsd0JBQXdCLEdBQWE7SUFDekMsWUFBWTtJQUNaLElBQUk7SUFDSixLQUFLO0lBQ0wsUUFBUTtJQUNSLFVBQVU7SUFDVixhQUFhO0lBQ2IsY0FBYztJQUNkLGNBQWM7SUFDZCxnQkFBZ0I7SUFDaEIsZ0JBQWdCO0lBQ2hCLFNBQVM7Q0FDVjs7TUFFSyxPQUFPLEdBQWE7SUFDeEIsUUFBUTtJQUNSLFdBQVc7SUFDWCxVQUFVO0lBQ1YsU0FBUztJQUNULFFBQVE7SUFDUixhQUFhO0lBQ2IsV0FBVztJQUNYLFVBQVU7Q0FDWDtBQWlDRCxNQUFNLE9BQU8sMkJBQTJCOzs7OztJQXlCdEMsWUFBWSxJQUFnQixFQUFFLElBQVk7UUFwQmxDLGdCQUFXOzs7O1FBQXdCLENBQUMsQ0FBYSxFQUFFLEVBQUU7O2tCQUNyRCxNQUFNLEdBQUcsSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQzs7a0JBQzFDLEtBQUsscUJBQU8sQ0FBQyxDQUFDLE1BQU0sQ0FBQztZQUMzQixPQUFPLEtBQUssQ0FBQyxJQUFJLENBQUM7WUFDbEIsTUFBTSxDQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsQ0FB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O0lBQ3pCLDJDQUFxQjs7Ozs7SUFDckIsMkNBQTRCOzs7OztJQUM1QixrREFLRTs7SUFFRiwwQ0FBeUU7O0lBQ3pFLDhDQUFvQzs7SUFDcEMsOENBQWtDOztJQUVsQyw2Q0FBeUQ7O0lBQ3pELGdEQUE0RDs7SUFDNUQsK0NBQTJEOztJQUMzRCw4Q0FBMEQ7O0lBQzFELDZDQUF5RDs7SUFDekQsa0RBQThEOztJQUM5RCxnREFBNEQ7O0lBQzVELCtDQUEyRCIsInNvdXJjZXNDb250ZW50IjpbImltcG9ydCB7IENvbXBvbmVudCwgRWxlbWVudFJlZiwgRXZlbnRFbWl0dGVyLCBOZ1pvbmUsIE9uSW5pdCwgSW5wdXQsIE91dHB1dCwgU2ltcGxlQ2hhbmdlcyB9IGZyb20gJ0Bhbmd1bGFyL2NvcmUnO1xuaW1wb3J0IHsgY3JlYXRlLCBzdXBwb3J0ZWQgfSBmcm9tICcuLi9saWInO1xuaW1wb3J0IHsgSURva2FJbnN0YW5jZSwgSURva2FPcHRpb25zIH0gIGZyb20gJy4uL2xpYi9kb2thJztcblxuLy8gV2UgdGVzdCBpZiBEb2thIGlzIHN1cHBvcnRlZCBvbiB0aGUgY3VycmVudCBjbGllbnRcbmNvbnN0IGlzU3VwcG9ydGVkID0gc3VwcG9ydGVkKCk7XG5cbi8vIE1ldGhvZHMgbm90IG1hZGUgYXZhaWxhYmxlIG9uIHRoZSBjb21wb25lbnRcbmNvbnN0IGZpbHRlcmVkQ29tcG9uZW50TWV0aG9kczogc3RyaW5nW10gPSBbXG4gICdzZXRPcHRpb25zJyxcbiAgJ29uJyxcbiAgJ29mZicsXG4gICdvbk9uY2UnLFxuICAnYXBwZW5kVG8nLFxuICAnaW5zZXJ0QWZ0ZXInLFxuICAnaW5zZXJ0QmVmb3JlJyxcbiAgJ2lzQXR0YWNoZWRUbycsXG4gICdyZXBsYWNlRWxlbWVudCcsXG4gICdyZXN0b3JlRWxlbWVudCcsXG4gICdkZXN0cm95J1xuXTtcblxuY29uc3Qgb3V0cHV0czogc3RyaW5nW10gPSBbXG4gICdvbmluaXQnLCBcbiAgJ29uY29uZmlybScsIFxuICAnb25jYW5jZWwnLCBcbiAgJ29uY2xvc2UnLFxuICAnb25sb2FkJywgXG4gICdvbmxvYWRlcnJvcicsIFxuICAnb25kZXN0cm95JywgXG4gICdvbnVwZGF0ZSdcbl07XG5cbkBDb21wb25lbnQoe1xuICBzZWxlY3RvcjogJ2xpYi1kb2thLW92ZXJsYXknLFxuICB0ZW1wbGF0ZTogYFxuICAgIDxuZy1jb250ZW50PjwvbmctY29udGVudD5cbiAgICA8ZGl2PlxuICAgICAgICA8ZGl2PjwvZGl2PlxuICAgIDwvZGl2PlxuICBgLFxuICBzdHlsZXM6IFtgXG4gICAgOmhvc3Qge1xuICAgICAgZGlzcGxheTogYmxvY2s7XG4gICAgICBwb3NpdGlvbjogcmVsYXRpdmU7XG4gICAgICBvdmVyZmxvdzogaGlkZGVuO1xuICAgIH1cbiAgICBcbiAgICA6aG9zdCAvZGVlcC8gaW1nIHtcbiAgICAgIGRpc3BsYXk6IGJsb2NrO1xuICAgICAgd2lkdGg6IDEwMCU7XG4gICAgICBoZWlnaHQ6IGF1dG87XG4gICAgfVxuICAgIFxuICAgIDpob3N0ID4gZGl2IHtcbiAgICAgIHBvc2l0aW9uOiBhYnNvbHV0ZTtcbiAgICAgIGxlZnQ6IDA7XG4gICAgICB0b3A6IDA7XG4gICAgICB3aWR0aDogMTAwJTtcbiAgICAgIGhlaWdodDogMTAwJTtcbiAgICB9XG4gICAgYF1cbn0pXG5cbmV4cG9ydCBjbGFzcyBBbmd1bGFyRG9rYU92ZXJsYXlDb21wb25lbnQgaW1wbGVtZW50cyBPbkluaXQge1xuXG4gIHByaXZhdGUgcm9vdDogRWxlbWVudFJlZjtcbiAgcHJpdmF0ZSB6b25lOiBOZ1pvbmU7XG4gIHByaXZhdGUgZG9rYTogSURva2FJbnN0YW5jZTtcbiAgcHJpdmF0ZSBoYW5kbGVFdmVudDogRXZlbnRIYW5kbGVyTm9uTnVsbCA9IChlOkN1c3RvbUV2ZW50KSA9PiB7XG4gICAgY29uc3Qgb3V0cHV0ID0gdGhpc1tgb24ke2UudHlwZS5zcGxpdCgnOicpWzFdfWBdO1xuICAgIGNvbnN0IGV2ZW50ID0gey4uLmUuZGV0YWlsfTtcbiAgICBkZWxldGUgZXZlbnQuZG9rYTtcbiAgICBvdXRwdXQuZW1pdChldmVudCk7XG4gIH07XG5cbiAgQElucHV0KCkgc3JjOiBzdHJpbmd8RmlsZXxCbG9ifEhUTUxJbWFnZUVsZW1lbnR8SFRNTENhbnZhc0VsZW1lbnQgPSBudWxsO1xuICBASW5wdXQoKSBvcHRpb25zOiBJRG9rYU9wdGlvbnMgPSB7fTtcbiAgQElucHV0KCkgZW5hYmxlZDogYm9vbGVhbiA9IGZhbHNlO1xuXG4gIEBPdXRwdXQoKSBvbmluaXQ6IEV2ZW50RW1pdHRlcjxhbnk+ID0gbmV3IEV2ZW50RW1pdHRlcigpO1xuICBAT3V0cHV0KCkgb25jb25maXJtOiBFdmVudEVtaXR0ZXI8YW55PiA9IG5ldyBFdmVudEVtaXR0ZXIoKTtcbiAgQE91dHB1dCgpIG9uY2FuY2VsOiBFdmVudEVtaXR0ZXI8YW55PiA9IG5ldyBFdmVudEVtaXR0ZXIoKTtcbiAgQE91dHB1dCgpIG9uY2xvc2U6IEV2ZW50RW1pdHRlcjxhbnk+ID0gbmV3IEV2ZW50RW1pdHRlcigpO1xuICBAT3V0cHV0KCkgb25sb2FkOiBFdmVudEVtaXR0ZXI8YW55PiA9IG5ldyBFdmVudEVtaXR0ZXIoKTtcbiAgQE91dHB1dCgpIG9ubG9hZGVycm9yOiBFdmVudEVtaXR0ZXI8YW55PiA9IG5ldyBFdmVudEVtaXR0ZXIoKTtcbiAgQE91dHB1dCgpIG9uZGVzdHJveTogRXZlbnRFbWl0dGVyPGFueT4gPSBuZXcgRXZlbnRFbWl0dGVyKCk7XG4gIEBPdXRwdXQoKSBvbnVwZGF0ZTogRXZlbnRFbWl0dGVyPGFueT4gPSBuZXcgRXZlbnRFbWl0dGVyKCk7XG5cbiAgY29uc3RydWN0b3Iocm9vdDogRWxlbWVudFJlZiwgem9uZTogTmdab25lKSB7XG4gICAgdGhpcy5yb290ID0gcm9vdDtcbiAgICB0aGlzLnpvbmUgPSB6b25lO1xuICB9XG5cbiAgbmdPbkluaXQoKSB7fVxuXG4gIG5nQWZ0ZXJWaWV3SW5pdCgpIHtcblxuICAgIC8vIG5vIHN1ZmZpY2llbnQgZmVhdHVyZXMgc3VwcG9ydGVkIGluIHRoaXMgYnJvd3NlclxuICAgIGlmICghaXNTdXBwb3J0ZWQpIHJldHVybjtcblxuICAgIHRoaXMudXBkYXRlKCk7XG4gIH1cblxuICBuZ09uQ2hhbmdlcyhjaGFuZ2VzOiBTaW1wbGVDaGFuZ2VzKSB7XG4gICAgLy8gbm8gbmVlZCB0byBoYW5kbGUgZmlyc3QgY2hhbmdlXG4gICAgaWYgKGNoYW5nZXMuZmlyc3RDaGFuZ2UpIHJldHVybjtcblxuICAgIC8vIHVwZGF0ZSFcbiAgICB0aGlzLnVwZGF0ZShjaGFuZ2VzKTtcbiAgfVxuXG4gIG5nT25EZXN0cm95KCkge1xuICAgIHRoaXMuaGlkZSgpO1xuICB9XG5cbiAgc2hvdyhjaGFuZ2VzPzogU2ltcGxlQ2hhbmdlcykge1xuXG4gICAgaWYgKHRoaXMuZG9rYSkge1xuXG4gICAgICAvLyB1c2UgbmV3IG9wdGlvbnMgb2JqZWN0IGFzIGJhc2UgKCBvciBpZiBub3QgYXZhaWxhYmxlLCB1c2UgY3VycmVudCBvcHRpb25zIClcbiAgICAgIGNvbnN0IG9wdGlvbnMgPSBjaGFuZ2VzLm9wdGlvbnMgPyBjaGFuZ2VzLm9wdGlvbnMuY3VycmVudFZhbHVlIDogdGhpcy5vcHRpb25zO1xuICAgICAgXG4gICAgICAvLyB1cGRhdGUgc291cmNlXG4gICAgICBpZiAoY2hhbmdlcy5zcmMpIG9wdGlvbnMuc3JjID0gY2hhbmdlcy5zcmMuY3VycmVudFZhbHVlO1xuXG4gICAgICAvLyBzZXQgbmV3IG9wdGlvbnNcbiAgICAgIHRoaXMuZG9rYS5zZXRPcHRpb25zKG9wdGlvbnMpO1xuXG4gICAgICByZXR1cm47XG4gICAgfVxuXG4gICAgLy8gd2lsbCBibG9jayBhbmd1bGFyIGZyb20gbGlzdGVuaW5nIHRvIGV2ZW50cyBpbnNpZGUgZG9rYVxuICAgIHRoaXMuem9uZS5ydW5PdXRzaWRlQW5ndWxhcigoKSA9PiB7XG5cbiAgICAgIC8vIGdldCBob3N0IGNoaWxkIDxkaXY+XG4gICAgICBjb25zdCBpbm5lciA9IHRoaXMucm9vdC5uYXRpdmVFbGVtZW50LnF1ZXJ5U2VsZWN0b3IoJ2RpdicpLmZpcnN0Q2hpbGQ7XG5cbiAgICAgIC8vIGNyZWF0ZSBpbnN0YW5jZVxuICAgICAgdGhpcy5kb2thID0gY3JlYXRlKGlubmVyLCB7XG4gICAgICAgIC8vIHNvdXJjZSBmcm9tIHNsb3RcbiAgICAgICAgc3JjOiB0aGlzLnNyYyxcblxuICAgICAgICAvLyBvdXIgb3B0aW9uc1xuICAgICAgICAuLi50aGlzLm9wdGlvbnMsXG5cbiAgICAgICAgLy8gYWx3YXlzIHByZXZpZXcgbW9kZVxuICAgICAgICBzdHlsZUxheW91dE1vZGU6ICdwcmV2aWV3JyxcbiAgICAgICAgb3V0cHV0RGF0YTogdHJ1ZVxuICAgICAgfSk7XG5cbiAgICB9KTtcblxuICAgIC8vIHJvdXRlIGV2ZW50c1xuICAgIGNvbnN0IGRva2FSb290OkhUTUxFbGVtZW50ID0gdGhpcy5kb2thLmVsZW1lbnQ7XG4gICAgb3V0cHV0cy5mb3JFYWNoKGV2ZW50ID0+IGRva2FSb290LmFkZEV2ZW50TGlzdGVuZXIoYERva2E6JHtldmVudC5zdWJzdHIoMil9YCwgdGhpcy5oYW5kbGVFdmVudCkpO1xuXG4gICAgLy8gQ29weSBpbnN0YW5jZSBtZXRob2QgcmVmZXJlbmNlcyB0byBjb21wb25lbnQgaW5zdGFuY2VcbiAgICBPYmplY3Qua2V5cyh0aGlzLmRva2EpXG5cbiAgICAgIC8vIHJlbW92ZSB1bndhbnRlZCBtZXRob2RzXG4gICAgICAuZmlsdGVyKGtleSA9PiBmaWx0ZXJlZENvbXBvbmVudE1ldGhvZHMuaW5kZXhPZihrZXkpID09PSAtMSlcbiAgICAgIFxuICAgICAgLy8gc2V0IG1ldGhvZCByZWZlcmVuY2VzIGZyb20gdGhlIGNvbXBvbmVudCBpbnN0YW5jZSB0byB0aGUgZG9rYSBpbnN0YW5jZVxuICAgICAgLmZvckVhY2goa2V5ID0+IHRoaXNba2V5XSA9IHRoaXMuZG9rYVtrZXldKTtcbiAgfVxuXG4gIGhpZGUoKSB7XG5cbiAgICAvLyBubyBkb2thIGluc3RhbmNlIGF2YWlsYWJsZVxuICAgIGlmICghdGhpcy5kb2thKSByZXR1cm47XG5cbiAgICAvLyBkZXRhY2ggZXZlbnRzXG4gICAgY29uc3QgZG9rYVJvb3Q6SFRNTEVsZW1lbnQgPSB0aGlzLmRva2EuZWxlbWVudDtcbiAgICBvdXRwdXRzLmZvckVhY2goZXZlbnQgPT4gZG9rYVJvb3QucmVtb3ZlRXZlbnRMaXN0ZW5lcihgRG9rYToke2V2ZW50LnN1YnN0cigyKX1gLCB0aGlzLmhhbmRsZUV2ZW50KSk7XG5cbiAgICAvLyB3ZSBkb25lIVxuICAgIHRoaXMuZG9rYS5kZXN0cm95KCk7XG4gICAgdGhpcy5kb2thID0gbnVsbDtcbiAgfVxuXG4gIHVwZGF0ZShjaGFuZ2VzPzogU2ltcGxlQ2hhbmdlcykge1xuICAgIGlmICh0aGlzLmVuYWJsZWQpIHtcbiAgICAgICAgdGhpcy5zaG93KGNoYW5nZXMpO1xuICAgIH1cbiAgICBlbHNlIHtcbiAgICAgICAgdGhpcy5oaWRlKCk7XG4gICAgfVxuICB9XG5cbn1cbiJdfQ==