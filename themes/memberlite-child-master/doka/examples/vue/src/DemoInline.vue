<template>
  <div id="demo-inline">

    <h2>Doka Inline</h2>
    
    <Doka 
      ref="doka"
      style="height:400px"
      crop-aspect-ratio="1"
      utils="crop,filter,color,markup,resize"
      :crop-aspect-ratio-options="cropOptions"
      :src="src"
      @confirm="handleDokaConfirm"
      @cancel="handleDokaCancel"/>
      
  </div>
</template>

<script>
/* eslint-disable */
import { Doka } from './vue-doka/';

export default {
  name: 'demo-modal',
  components: { Doka },
  data () {
    return {
      src: 'assets/photo.jpeg',
      cropOptions: [
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
  },
  methods: {
    handleDokaConfirm(output) {
      console.log('Confirm crop!', output);
    },
    handleDokaCancel() {
      console.log('Cancel crop!');
    }
  }
}
</script>