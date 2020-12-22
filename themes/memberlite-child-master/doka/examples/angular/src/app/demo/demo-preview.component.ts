import { Component, ViewChild } from '@angular/core';
import { DomSanitizer, SafeUrl } from '@angular/platform-browser';

@Component({
  selector: 'demo-preview',
  template: `
    <h2>Doka Overlay</h2>

    <button (click)="handleShowOverlay()">Show Overlay</button>

    <lib-doka-overlay #myDoka 
      [enabled]="enabled"
      [src]="imageSrc"
      [options]="options"
      (oninit)="handleDokaInit()"
      (onconfirm)="handleDokaConfirm($event)"
      (oncancel)="handleDokaCancel()">
      <img [src]="imagePreviewSource" alt="" />
    </lib-doka-overlay>
  `
})

export class DemoPreviewComponent {

  constructor(private sanitizer: DomSanitizer) { }

  @ViewChild('myDoka', { static: false }) myDoka: any;

  // image preview settings
  imageSrc: string = './assets/photo.jpeg';
  imagePreviewSource: SafeUrl = './assets/photo-preview.jpeg';

  // overlay state
  enabled: boolean = false;

  // doka configuration options
  options: any = {
    crop: {
      aspectRatio: .5,
      rotation: -1.5707963268
    },
    utils: ['crop']
  }

  handleShowOverlay = () => {
    this.enabled = true;
  }

  handleDokaConfirm = ($event) => {
    console.log('Doka confirm button clicked', $event);

    this.enabled = false;
    const url = URL.createObjectURL($event.output.file);
    this.imagePreviewSource = this.sanitizer.bypassSecurityTrustUrl(url);
    this.options = {
      ...this.options,
      crop: {
        ...$event.output.data.crop
      }
    }
  }

  handleDokaInit = () => {
    console.log('Doka has initialised', this.myDoka);
  }

  handleDokaCancel = () => {
    console.log('Doka cancel button clicked');
  }

}
