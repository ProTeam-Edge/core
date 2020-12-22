<template>
  <div id="demo-modal">

    <h2>Doka Modal</h2>

    <button @click="enabled=true">Show Modal</button>

    <DokaModal 
      crop-aspect-ratio="1"
      :src="src" 
      v-if="enabled"
      @confirm="handleDokaConfirm"
      @cancel="handleDokaCancel"
      @close="enabled=false"/>
    
    <img v-if="result" :src="result" alt="" />
    
  </div>
</template>

<script>
/* eslint-disable */
import { DokaModal, toURL } from './vue-doka/';

export default {
  name: 'demo-modal',
  components: { DokaModal },
  data () {
    return {
      enabled: false,
      src: 'assets/photo.jpeg',
      result: null
    }
  },
  methods: {
    handleDokaConfirm(output) {
      console.log('Confirm crop!', output);
      this.result = toURL(output.file);
    },
    handleDokaCancel() {
      console.log('Cancel crop!');
    }
  }
}
</script>