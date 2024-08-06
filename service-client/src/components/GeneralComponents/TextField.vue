<template>
    <v-text-field 
    color="primary" 
    density="compact" 
    :label="label"
    placeholder=""
    variant="outlined"
    readonly
    >{{ text }}</v-text-field>
</template>
<script setup>
    import {defineProps, toRefs} from 'vue'

    const props = defineProps({
        label : {
            type : String,
            default : ''
        },
        text : {
            type : String,
            dafault : ''
        }
    })

    const { label, text } = toRefs(props)
</script>