<template>
  <div id="app">
    <h1>The app, from Vue</h1>
    <siri-upload />
  </div>
</template>

<script>
  import SiriUpload from './SiriUpload.vue';
  export default {
      components: { SiriUpload },
  };
</script>
