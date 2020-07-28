import { Component, ViewChild } from '@angular/core';
import { DomSanitizer, SafeUrl } from '@angular/platform-browser';

@Component({
  selector: 'demo-modal',
  template: `
    <h2>Doka Modal</h2>

    <button (click)="handleShowModal()">Show Modal</button>

    <lib-doka-modal #myDoka 
      *ngIf="enabled"
      [src]="src"
      [options]="options"
      (oninit)="handleDokaInit()"
      (onconfirm)="handleDokaConfirm($event)"
      (oncancel)="handleDokaCancel()"
      (onclose)="handleDokaClose()">
    </lib-doka-modal>

    <img *ngIf="result" [src]="result" alt="" />
    
  `
})

export class DemoModalComponent {

  constructor(private sanitizer:DomSanitizer) {}
  
  @ViewChild('myDoka', { static: false }) myDoka: any;

  // modal state
  enabled: boolean = false;

  // image source
  src: string = './assets/photo.jpeg';

  // image output
  result: SafeUrl = null;

  // doka configuration options
  options: Object = {
    cropAspectRatio: 1,
    cropAspectRatioOptions: [
      {
        label: 'Free',
        value: null
      },
      {
        label: 'Portrait',
        value: 1.5
      },
      {
        label: 'Square',
        value: 1
      },
      {
        label: 'Landscape',
        value: .75
      }
    ]
  }

  handleShowModal = () => {
    this.enabled = true;
  }

  handleDokaInit = () => {
    console.log('Doka has initialised', this.myDoka);
  }

  handleDokaConfirm = ($event) => {
    console.log('Doka confirm button clicked', $event);
    const url = URL.createObjectURL($event.output.file);

    this.result = this.sanitizer.bypassSecurityTrustUrl(url);
  }

  handleDokaCancel = () => {
    console.log('Doka cancel button clicked');
  }

  handleDokaClose = () => {
    this.enabled = false;
  }
}
