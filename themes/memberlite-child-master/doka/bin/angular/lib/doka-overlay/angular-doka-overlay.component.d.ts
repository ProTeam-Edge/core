import { ElementRef, EventEmitter, NgZone, OnInit, SimpleChanges } from '@angular/core';
import { IDokaOptions } from '../lib/doka';
export declare class AngularDokaOverlayComponent implements OnInit {
    private root;
    private zone;
    private doka;
    private handleEvent;
    src: string | File | Blob | HTMLImageElement | HTMLCanvasElement;
    options: IDokaOptions;
    enabled: boolean;
    oninit: EventEmitter<any>;
    onconfirm: EventEmitter<any>;
    oncancel: EventEmitter<any>;
    onclose: EventEmitter<any>;
    onload: EventEmitter<any>;
    onloaderror: EventEmitter<any>;
    ondestroy: EventEmitter<any>;
    onupdate: EventEmitter<any>;
    constructor(root: ElementRef, zone: NgZone);
    ngOnInit(): void;
    ngAfterViewInit(): void;
    ngOnChanges(changes: SimpleChanges): void;
    ngOnDestroy(): void;
    show(changes?: SimpleChanges): void;
    hide(): void;
    update(changes?: SimpleChanges): void;
}
