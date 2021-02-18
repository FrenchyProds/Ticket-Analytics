<template>
  <q-card class="fit" :style="{backgroundColor: '#06519C', minHeight: '150px'}"  >
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
