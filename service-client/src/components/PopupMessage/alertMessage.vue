<template>
    <v-snackbar :color="props.details.color" v-model="alertMessage" location="right bottom" :timeout="5000">
        <v-icon>mdi-alert-circle-outline</v-icon> {{ props.details.text }}.
    </v-snackbar>
</template>
<script setup>
import { ref, onMounted } from 'vue'

const alertMessage = ref(true)
const text = ref('')
const color = ref('')

const props = defineProps({
    details : {
        type : Object,
    }
})
</script>   