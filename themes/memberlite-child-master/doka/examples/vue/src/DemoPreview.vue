<template>
  <div id="demo-preview">

    <h2>Doka Preview</h2>

    <button @click="enabled=true">Show Overlay</button>

    <DokaOverlay 
      utils="crop"
      :enabled="enabled"
      :src="imageSource" 
      :crop="imagePreviewCrop"
      @confirm="handleDokaConfirm"
      @cancel="handleDokaCancel">
      <img :src="imagePreviewSource" alt="">
    </DokaOverlay>
    
  </div>
</template>

<script>
/* eslint-disable */
import { DokaOverlay, toURL } from './vue-doka';

export default {
  name: 'demo-overlay',
  components: { DokaOverlay },
  data () {
    return {
      enabled: false,
      imagePreviewCrop: {
        aspectRatio: .5,
        rotation: -1.5707963268
      },
      imagePreviewSource: 'assets/photo-preview.jpeg',
      imageSource: 'assets/photo.jpeg'
    }
  },
  methods: {
    handleDokaConfirm(output) {
      console.log('Confirm crop!', output);
      this.enabled = false;
      this.imagePreviewSource = toURL(output.file);
      this.imagePreviewCrop = output.data.crop;
    },
    handleDokaCancel() {
      console.log('Cancel crop!');
      this.enabled = false;
    }
  }
}
</script>