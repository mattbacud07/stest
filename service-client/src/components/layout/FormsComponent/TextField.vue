<template>
    <v-text-field
        color="primary"
        density="compact"
        variant="outlined"
        hide-details
        v-bind="attrs"
    />
</template>

<script setup>
import { useAttrs } from 'vue';
const attrs = useAttrs()
</script>