import { Component, ViewChild } from '@angular/core';
import { DomSanitizer, SafeUrl } from '@angular/platform-browser';

@Component({
  selector: 'demo-inline',
  template: `
    <h2>Doka Inline</h2>

    <lib-doka #myDoka 
      style="height:400px"
      [src]="src"
      [options]="options"
      (oninit)="handleDokaInit()"
      (onconfirm)="handleDokaConfirm($event)"
      (oncancel)="handleDokaCancel()">
    </lib-doka>

    <img *ngIf="result" [src]="result" alt="" />
  `
})

export class DemoInlineComponent {

  constructor(private sanitizer:DomSanitizer) {}
  
  @ViewChild('myDoka', { static: false }) myDoka: any;

  // image source
  src: string = './assets/photo.jpeg';

  // image output
  result: SafeUrl = null;

  // doka configuration options
  options: Object = {
    utils: ['crop', 'filter', 'color', 'markup', 'resize'],
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
}
