<template>
  <h1>{{ title || 'Example component '}}</h1>
</template>

<script>
  export default {
    name: 'vue-example',
    props: ['title'],
  };
</script>

<style scoped lang="scss">
  h1 {
    font-size: 50px;
    color: red;
  }
</style>
