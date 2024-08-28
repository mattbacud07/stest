<template>
    <v-dialog  max-width="500" persistent>
        <template v-slot:activator="{ props: activatorProps }">
            <slot name="activator" :activatorProps="activatorProps"></slot>
        </template>
        <v-card>
            Are you sure?
        </v-card>
        <v-card-actions>
            <v-spacer></v-spacer>
            <slot name="actions"></slot>
        </v-card-actions>
    </v-dialog>
</template>
<script setup>
import { defineEmits, getCurrentInstance, inject } from 'vue';
const pubDialog = ref(inject('pubDialog'),false)
</script>