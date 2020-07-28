import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';

import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
import FilePondPluginImageExifOrientation from 'filepond-plugin-image-exif-orientation';
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import FilePondPluginImageEdit from 'filepond-plugin-image-edit';
import FilePondPluginImageCrop from 'filepond-plugin-image-crop';
import FilePondPluginImageResize from 'filepond-plugin-image-resize';
import FilePondPluginImageTransform from 'filepond-plugin-image-transform';

import { FilePondModule, registerPlugin } from 'ngx-filepond';

registerPlugin(
  FilePondPluginFileValidateType,
  FilePondPluginImageExifOrientation,
  FilePondPluginImagePreview,
  FilePondPluginImageEdit,
  FilePondPluginImageCrop,
  FilePondPluginImageResize,
  FilePondPluginImageTransform
);

import { AppComponent } from './app.component';

import { AngularDokaModule } from 'angular-doka';
import { DemoInlineComponent } from './demo/demo-inline.component';
import { DemoModalComponent } from './demo/demo-modal.component';
import { DemoPreviewComponent } from './demo/demo-preview.component';
import { DemoFilePondComponent } from './demo/demo-filepond.component';
import { DemoProfileComponent } from './demo/demo-profile.component';

@NgModule({
  declarations: [
    AppComponent,
    DemoInlineComponent,
    DemoModalComponent,
    DemoPreviewComponent,
    DemoFilePondComponent,
    DemoProfileComponent
  ],
  imports: [
    AngularDokaModule,
    BrowserModule,
    FilePondModule
  ],
  providers: [],
  bootstrap: [AppComponent]
})
export class AppModule { }
