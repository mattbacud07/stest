<template>
    <v-dialog v-model="leaveDialog" width="auto">
        <v-card :min-width="minWidth" :text="text" :title="title">
            <template v-slot:actions>
                <v-spacer></v-spacer>
                <v-btn class="text-none" variant="tonal" color="primary" :text="cancelText"
                    @click="cancel"></v-btn>
                <v-btn class="text-none" color="primary" :prepend-icon="confirmIcon" variant="flat" :text="confirmText"
                    @click="confirm"></v-btn>
            </template>
        </v-card>
    </v-dialog>
</template>
<script setup>

const props = defineProps({
    leaveDialog: {
        type: Boolean,
        default: false
    },
    title: {
        type: String,
        default: ''
    },
    text : {
        type: String,
        default: ''
    },
    cancelText: {
        type: String,
        default: 'Cancel'
    },
    confirmText: {
        type: String,
        default: 'Yes'
    },
    confirmIcon : {
        type: String,
        default: 'mdi-check'
    },
    minWidth : {
        type: String,
        default: '400px'
    }
})

const { leaveDialog, title, text, cancelText, confirmText, confirmIcon, minWidth } = props

const emit = defineEmits([
    'update:modelValue', 'confirm', 'cancel'
])

const cancel = () => {
    emit('update:modelValue', false)
    emit('cancel')
}

const confirm = () => {
    emit('update:modelValue', false)
    emit('confirm')
}
</script>