<template>
  <div id="app">

    <file-pond
        name="test"
        ref="pond"
        label-idle="Drop files here..."
        allow-multiple="true"
        instant-upload="false"
        accepted-file-types="image/jpeg, image/png"
        server="/api"
        imageEditInstantEdit="true"
        imageCropAspectRatio="1:1"
        v-bind:imageEditEditor="myDoka"
        v-bind:files="myFiles"
        v-on:init="handleFilePondInit"/>

  </div>
</template>

<script>
// Import Doka
import { create } from './vue-doka/lib/doka.esm.min';
import './vue-doka/lib/doka.min.css';

// Import Vue FilePond
import vueFilePond from 'vue-filepond';

// Import FilePond styles
import 'filepond/dist/filepond.min.css';

// Import plugin styles
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css';
import 'filepond-plugin-image-edit/dist/filepond-plugin-image-edit.min.css';

// Import plugins
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
import FilePondPluginImageExifOrientation from 'filepond-plugin-image-exif-orientation';
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import FilePondPluginImageEdit from 'filepond-plugin-image-edit';
import FilePondPluginImageCrop from 'filepond-plugin-image-crop';
import FilePondPluginImageResize from 'filepond-plugin-image-resize';
import FilePondPluginImageTransform from 'filepond-plugin-image-transform';

// Create component
const FilePond = vueFilePond(
    FilePondPluginFileValidateType,
    FilePondPluginImageExifOrientation,
    FilePondPluginImageCrop,
    FilePondPluginImageResize,
    FilePondPluginImagePreview,
    FilePondPluginImageEdit,
    FilePondPluginImageTransform
);

export default {
    name: 'app',
    data: function() {
        return {
            myFiles: [],
            myDoka: create({
                cropAspectRatioOptions: [
                    {
                        label: 'Free',
                        value: null
                    },
                    {
                        label: 'Portrait',
                        value: 1.25
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
        };
    },
    methods: {
        handleFilePondInit: function() {
            // FilePond instance methods are available on `this.$refs.pond`
        }
    },
    components: {
        FilePond
    }
};
</script>

<style>
@media (min-width: 40em) {
    .filepond--item {
        width: calc(50% - .5em);
    }
}

@media (min-width: 50em) {
    .filepond--item {
        width: calc(33.33% - .5em);
    }
}
</style>