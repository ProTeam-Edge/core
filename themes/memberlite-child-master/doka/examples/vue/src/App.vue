<template>
  <div id="app">
    <h1>Doka Demos</h1>
    <DemoFilePond/>
    <DemoProfile/>
    <DemoModal/>
    <DemoInline/>
    <DemoPreview/>
  </div>
</template>

<script>
import DemoFilePond from './DemoFilePond.vue';
import DemoModal from './DemoModal.vue';
import DemoPreview from './DemoPreview.vue';
import DemoInline from './DemoInline.vue';
import DemoProfile from './DemoProfile.vue';

export default {
  name: 'app',
  components: { DemoFilePond, DemoModal, DemoInline, DemoPreview, DemoProfile }
}
</script>

<style>
#app {
  font-family: 'Avenir', Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
}
</style>
