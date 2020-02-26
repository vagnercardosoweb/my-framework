<template>
  <h1>{{ title || 'Test component '}}</h1>
</template>

<script>
  export default {
    name: 'vue-test',
    props: ['title'],
  };
</script>

<style scoped lang="scss">
  h1 {
    font-size: 50px;
    color: red;
  }
</style>
