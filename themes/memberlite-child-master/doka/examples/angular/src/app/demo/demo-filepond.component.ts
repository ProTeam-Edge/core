import { Component, ViewChild } from '@angular/core';

const Doka = window['Doka'];

@Component({
  selector: 'demo-filepond',
  template: `
    <h2>Doka FilePond</h2>

    <file-pond #myPond 
        [options]="options" 
        [files]="files"
        (oninit)="handleFilePondInit()"
        (onaddfile)="handleFilePondAddFile($event)">
    </file-pond>
  `
})

export class DemoFilePondComponent {

  @ViewChild('myPond', { static: false }) myPond: any;

  options = {
    class: 'my-filepond',
    multiple: true,
    labelIdle: 'Drop files here',
    acceptedFileTypes: 'image/jpeg, image/png',
    imageEditEditor: Doka.create({
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
    })
  }

  files = [
    'assets/photo.jpeg'
  ]

  handleFilePondInit() {
    console.log('FilePond has initialised', this.myPond);
  }

  handleFilePondAddFile(event: any) {
    console.log('A file was added', event);
  }

}