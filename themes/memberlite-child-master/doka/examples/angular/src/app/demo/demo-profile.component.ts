import { Component } from '@angular/core';
import { DomSanitizer, SafeUrl } from '@angular/platform-browser';

@Component({
  selector: 'demo-profile',
  styles: [`
    .demo-profile .banner > button,
    .demo-profile .profile > button {
        position: absolute;
        left: 50%;
        top: 50%;
        font-size: 1.25em;
        transform: translateX(-50%) translateY(-50%);
        background: #fff;
        color: #333;
        border: none;
        line-height: 1;
        cursor: pointer;
        padding: .25em .75em;
        border-radius: 9999em;
        z-index: 2;
    }

    .demo-profile .banner > input,
    .demo-profile .profile > input {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: copy;
        padding: 0;
        margin: 0;
        min-width: auto;
        z-index: 1;
    }

    .demo-profile .profile {
        position:relative;
        transform: translateY(-50%);
        margin-left: 10%;
        width: 0%;
        border-radius: 9999em;
        overflow: hidden;
        z-index: 1;
    }

    .demo-profile .profile img {
        display: block;
        width: 100%;
    }
  `
  ],
  template: `
    <h2>Doka Profile</h2>

    <div class="demo-profile">

        <lib-doka-overlay
            class="banner"
            [enabled]="bannerEnabled"
            [src]="bannerSrc"
            [options]="bannerOptions"
            (onconfirm)="handleDokaConfirmBanner($event)"
            (oncancel)="handleDokaCancelBanner()">
            <button *ngIf="!bannerEnabled" (click)="handleToggleBannerEditor()">Edit</button>
            <input *ngIf="!bannerEnabled" type="file" accept="image/*" (change)="handleFileChangeBanner()"/>
            <img [src]="bannerPreviewSrc" alt=""/>
        </lib-doka-overlay>

        <div class="profile">
            <button *ngIf="!profileEnabled" (click)="handleToggleProfileEditor()">Edit</button>
            <input *ngIf="!profileEnabled" type="file" accept="image/*" (change)="handleFileChangeProfile()"/>
            <img [src]="profilePreviewSrc" alt=""/>
        </div>

    </div>

    <lib-doka-modal
      *ngIf="profileEnabled"
      [src]="profileSrc"
      [options]="profileOptions"
      (onconfirm)="handleDokaConfirmProfile($event)"
      (oncancel)="handleDokaCancelProfile()"
      (onclose)="handleDokaClose()">
    </lib-doka-modal>

  `
})

export class DemoProfileComponent {

  constructor(private sanitizer:DomSanitizer) {}

  bannerPreviewSrc: SafeUrl = './assets/profile-banner-preview.jpeg'
  bannerSrc: SafeUrl = './assets/profile-banner.jpeg'
  bannerSrcPrev: SafeUrl = null
  bannerEnabled: boolean = false
  bannerOptions: Object = {
    crop: {
        aspectRatio: 0.223,
        center: {
            x: 0.5,
            y: 0.543
        }
    }
  }
  
  profileSrc: SafeUrl = './assets/profile-picture.jpeg'
  profileSrcPrev: SafeUrl = null
  profilePreviewSrc: SafeUrl = './assets/profile-picture-preview.jpeg'
  profileEnabled: boolean = false
  profileOptions: Object = {
    outputData: true,
    crop: {
        aspectRatio: 1,
        center: {
            x: 0.5378,
            y: 0.355
        }
    },
    cropMask: (root, setInnerHTML) => {
        // https://pqina.nl/doka/docs/patterns/api/doka-instance/#setting-the-crop-mask
        setInnerHTML(root, `
            <mask id="my-mask">
                <rect x="0" y="0" width="100%" height="100%" fill="white"/>
                <circle cx="50%" cy="50%" r="50%" fill="black"/>
            </mask>
            <rect fill="rgba(255,255,255,.3125)" x="0" y="0" width="100%" height="100%" mask="url(#my-mask)"/>
            <circle cx="50%" cy="50%" r="50%" fill="transparent" stroke-width="1" stroke="#fff"/>
        `);
    },
  }


  handleToggleBannerEditor() {

    console.log('Toggle Doka Banner Overlay');

    this.bannerEnabled = !this.bannerEnabled;
  }

  handleFileChangeBanner(e) {

    if (!e.target.files.length) return;

    console.log('File Change Doka Banner Overlay');

    this.bannerSrcPrev = this.bannerSrc;
    this.bannerSrc = e.target.files[0];
    this.bannerEnabled = true;

  }
  
  handleDokaConfirmBanner($event) {
    
    console.log('Confirmed Doka Banner Overlay', $event);

    const url = URL.createObjectURL($event.output.file);

    this.bannerPreviewSrc = this.sanitizer.bypassSecurityTrustUrl(url);
    this.bannerSrcPrev = null;
    this.bannerEnabled = false;

    this.bannerOptions = {
      ...this.bannerOptions,
      crop: {
        ...$event.output.data.crop
      }
    }
  }
  
  handleDokaCancelBanner() {
    console.log('Cancelled Doka Banner Overlay');
    this.bannerEnabled = false;
    this.bannerSrc = this.bannerSrcPrev || this.bannerSrc;
  }

  handleToggleProfileEditor() {
    console.log('Toggle Doka Profile Modal');
    this.profileEnabled = !this.profileEnabled;
  }
  
  handleFileChangeProfile(e) {

    if (!e.target.files.length) return;

    console.log('File Change Doka Profile');

    this.profileSrcPrev = this.profileSrc;
    this.profileSrc = e.target.files[0];
    this.profileEnabled = true;

  }
  handleDokaConfirmProfile($event) {
    console.log('Confirmed Doka Profile Modal', $event);

    const url = URL.createObjectURL($event.output.file);

    this.profileSrcPrev = null;
    this.profilePreviewSrc = this.sanitizer.bypassSecurityTrustUrl(url);
    this.profileOptions = {
      ...this.profileOptions,
      crop: {
        ...$event.output.data.crop
      }
    }
  }
  
  handleDokaCancelProfile() {
    console.log('Cancelled Doka Profile Modal');
    this.profileSrc = this.profileSrcPrev || this.profileSrc;
  }

  handleDokaClose() {
    this.profileEnabled = false;
  }

}