<template>
  <q-card class="fit" :style="{backgroundColor: '#F3F3F3', minHeight: '150px'}"  >
    <q-card-section>
      <slot></slot>
    </q-card-section>
  </q-card>
</template>
<script>
export default {
  name: 'CardBase',
}
</script>
