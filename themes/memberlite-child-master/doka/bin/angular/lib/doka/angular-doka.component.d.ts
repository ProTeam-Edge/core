import { ElementRef, EventEmitter, NgZone, OnInit, SimpleChanges } from '@angular/core';
import { IDokaOptions } from '../lib/doka';
export declare class AngularDokaComponent implements OnInit {
    private root;
    private zone;
    private doka;
    private handleEvent;
    src: string | File | Blob;
    options: IDokaOptions;
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
}
